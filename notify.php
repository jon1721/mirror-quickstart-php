<?php
// Solo se admite el método POST
if ($_SERVER['REQUEST_METHOD'] != "POST") {
  header("HTTP/1.0 405 Method not supported");
  echo("Method not supported");
  exit();
}
header("Content-length: 0");
// Evitamos que el servidor pare la ejecución del script por desconexión del cliente.
ignore_user_abort(true);
// Evitamos que el servidor pare la ejecución del script por exceder del tiempo
máximo permitido.
// Al establecer el valor 0, no hay límite de tiempo.
set_time_limit(0);
//Gestión de procesos en PHP:
//La gestión de procesos solo funciona en servidores UNIX. No es soportada en Windows
//Primero se verifica que el servidor tiene activada la gestión de procesos.
if (function_exists('pcntl_fork')) {
  //Creamos un nuevo proceso hijo
  $pid = pcntl_fork();
  if ($pid == -1) {
    //Habido algún error al crear el proceso hijo
    error_log("Error: función pcntl_fork");
    exit();
  } else if ($pid) {
    //Estamos en el proceso padre, terminamos la ejecución.
    exit();
  }
}
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';
$request_bytes = @file_get_contents('php://input');
$request = json_decode($request_bytes, true);
//Recuperamos el identificador del usuario origen de la notificación
$userId = $request['userToken'];
$access_token = get_credentials($userId);
$client = get_google_api_client();
$client->setAccessToken($access_token);
// Crea el servicio para interactuar con las gafas
$mirrorService = new Google_MirrorService($client);
if($request['collection'] == "timeline") {
  foreach ($request['userActions'] as $i => $user_action) {
    //Recupera el id de la tarjeta origen de la notificación
    $itemId = $request['itemId'];
    //Obtiene la tarjeta desde el timeline
    $card = $mirrorService->timeline->get($itemId);
    //Verificamos el tipo de la acción
    switch ($user_action['type']) {
      case 'SHARE':
        //Modifica el texto para indicar que ha sido recibida.
        $card->setText("Tarjeta recibida:".$card->getText());
        //Actualiza la tarjeta en el timeline
        $mirrorService->timeline->update($card->getId(), $card);
        break;
      case 'CUSTOM':
        //La notificación ha sido generado por una opción de menú personalizada
        //Se utiliza la variable payload para recuperar el identificador único.
        if($user_action['payload']=="eliminar-noticia"){
          //Una vez identificada la opción de menú
          //Procesamos la acción: en este caso se elimina la tarjeta origen
          delete_timeline_item($mirrorService, $card->getId());
        }
        break;
    }
  }
}
