<?php
  require_once 'config.php';
  require_once 'mirror-client.php';
  require_once 'google-api-php-client/src/Google_Client.php';
  require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
  require_once 'util.php';
  $client = get_google_api_client();
  // Verificamos si el usuario tiene iniciada la sesión o no
  if (!isset($_SESSION['userid']) || get_credentials($_SESSION['userid'])
                                                              == null) {
    header('Location: ' . $base_url . '/oauth2callback.php');
    exit;
  } else {
    //Verificamos que las credenciales aún son validas
    verify_credentials(get_credentials($_SESSION['userid']));
    $client->setAccessToken(get_credentials($_SESSION['userid']));
  }
  // Servicio para interactuar con las Glass
  $mirror_service = new Google_MirrorService($client);
  switch ($_POST['operacion']) {
    case "eliminarSuscripcion":
      $mirror_service->subscriptions->delete($_POST['suscripcionId']);
      break;
    case "insertarSuscripcion":
      subscribe_to_notifications($mirror_service,
         $_POST['subscriptionId'],
        $_SESSION['userid'],
        "https://mirrornotifications.appspot.com/forward?url=http://www.junipero.com.ar/upvnews/notify.php");
      break;
  }
  $lista = null;
  try {
    $lista = $mirror_service->subscriptions->listSubscriptions();
    $lista = $lista->getItems();
  } catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
  }
?>
  <!doctype html>
  <html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de suscripciones</title>
    <link href="./static/bootstrap/css/bootstrap.min.css"
                                        rel="stylesheet" media="screen">
    <link href="./static/bootstrap/css/bootstrap-responsive.min.css"
                                        rel="stylesheet" media="screen">
    <link href="./static/main.css" rel="stylesheet" media="screen">
  </head>
  <body>
  <div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <a class="brand" href="#">Gestión de suscripciones</a>
      </div>
    </div>
  </div>
  <form method="post">
    <input type="hidden" name="operacion" value="insertarSuscripcion">
      <input type="hidden" name="subscriptionId" value="timeline">
      <button class="btn btn-block btn-success" type="submit">
          Insertar Suscripción al timeline
      </button>
  </form>
  <form method="post">
   <input type="hidden" name="operacion" value="insertarSuscripcion">
     <input type="hidden" name="subscriptionId" value="locations">
     <button class="btn btn-block btn-success" type="submit">
         Insertar Suscripción a ubicación
     </button>
 </form>
 <form method="post">
   <input type="hidden" name="operacion" value="insertarSuscripcion">
     <input type="hidden" name="subscriptionId" value="settings">
     <button class="btn btn-block btn-success" type="submit">
         Insertar Suscripción sobre los cambios de ajustes
     </button>
 </form>
 <div style="margin-top: 5px;">
       <?php if (count($lista)>0) { ?>
         <?php foreach ($lista as $suscripcion) { ?>
         <div class="span4">
           <table class="table table-bordered">
             <tbody>
               <tr>
                 <th>Collection</th>
                 <td><?php echo $suscripcion->getCollection(); ?></td>
               </tr>
               <tr>
                 <th>User Token</th>
                 <td><?php echo $suscripcion->getUserToken(); ?></td>
               </tr>
               <tr>
                 <th>Verify Token</th>
                 <td><?php echo $suscripcion->getVerifyToken(); ?></td>
               </tr>
               <tr>
                 <th>URL Callback</th>
                 <td>
                   <?php echo $suscripcion->getCallbackUrl(); ?>
                 </td>
               </tr>
               <tr>
                 <td colspan="2">
                   <form class="form-inline" method="post">
                     <input type="hidden" name="suscripcionId"
                            value="<?php echo $suscripcion->getId(); ?>">
                     <input type="hidden" name="operacion"
                            value="eliminarSuscripcion">
                     <button class="btn btn-danger btn-block"
                             type="submit">Eliminar Suscripción</button>
                   </form>
                 </td>
               </tr>
             </tbody>
           </table>
         </div>
         <?php }}?>
      </div>
 </body>
 </html>
