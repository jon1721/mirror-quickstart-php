<?php
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
require_once 'util.php';

$client = get_google_api_client();

// Verificamos si el usuario tiene iniciada la sesion o no
if(!isset($_SESSION['userid']) || get_credentials($_SESSION['userid'])
 == null) {
  header('Location: ' . $base_url . '/oauth2callback.php');
  exit;
} else {
    // Verificamos que las credenciales aun son validas
  verify_credentials(get_credentials($_SESSION['userid']));
  $client->setAccessToken(get_credentials($_SESSION['userid']));
}

 // Servicio para interactuar con las Glass
$mirror_service = new Google_MirrorService($client);


$card = new Google_TimelineItem();

$html =
'<article>
	<figure>
		<img width="100%" height="100%"
		src="http://www.junipero.com.ar/upvnews/static/images/UPV.png">
	</figure>
	<section>
		<p class="text-auto-size">Welcome to <span class="blue">UPV News</span></p>
	</section>
</article>';


//$card->setText("Glassware UPV News Instalado con éxito");
$card->setHtml($html);

$notification = new Google_NotificationConfig();
$notification->setLevel("DEFAULT");
$card->setNotification($notification);


insert_timeline_item($mirror_service, $card, null, null);
//Insertamos el contacto que representa la aplicación
insert_contact($mirror_service, "upv-news-contacID", "Juan Carlos Fernández",
"http://www.junipero.com.ar/upvnews/static/images/SanLuis.png");

//Activamos la suscripción al timeline
subscribe_to_notifications($mirror_service, "timeline", $_SESSION['userid'],
"https://mirrornotifications.appspot.com/forward?url=http://www.junipero.com.ar/upvnews/notify.php");
