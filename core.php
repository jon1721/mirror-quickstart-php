<?php

require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
require_once 'util.php';

// Obtenemos las credenciales de todos los usuarios desde la base de datos

$credentials = list_credentials();
foreach ($credentials as $credential) {
	$client = get_google_api_client();
	$client->setAccessToken($credential['credentials']);
	$mirror_service = new Google_MirrorService($client);
	$cards = getCards(getNews());
	foreach ($cards as $card) {
		insert_timeline_item($mirror_service, $card, null, null);
	}
}

function getCards($news){
	//Creamos un array para almacenar las tarjetas

	$cards = array();

	//Generamos el identificador del bundle de forma aleatoria
	$bundleId= rand(1,100)+123456789;
	//Creamos el bundle que va a contener todas las noticias
  $cardBundle = new Google_TimelineItem();
  $notification = new Google_NotificationConfig();
  $notification->setLevel("DEFAULT");
  $cardBundle->setNotification($notification);
  $cardBundle->setIsBundleCover(true);
  $cardBundle->setBundleId($bundleId);
  $cardBundle->setText('UPV News');
  array_push($cards, $cardBundle);

	foreach ($news as $new) {
		$card = new Google_TimelineItem();
		//Añadimos los datos de las noticias
		$card->setSpeakableText($new['description']);
		$card->setBundleId($bundleId);
		$card->setHtml('<article>
				<img src="'.$new['image'].'" width="100%" height="100%">
				<div class="overlay-gradient-tall-dark"/>
				<section>
					<p class="text-auto-size">
					'.$new['title'].'
					</p>
				</section>
				</article>');
		//Añadinos el tipo de notificación
		$notificacion = new Google_NotificationConfig();
		$notificacion->setLevel("DEFAULT");
		$card->setNotification($notificacion);

		//Añadimos el menú

		//Creamos un array para contener todas las opciones de forma conjunta.
		$menuItems = array();
		$menu_item = new Google_MenuItem();
		$menu_item->setAction("DELETE");
		array_push($menuItems, $menu_item);

		$menu_item = new Google_MenuItem();
		$menu_item->setAction("REPLY");
		array_push($menuItems, $menu_item);

		$menu_item = new Google_MenuItem();
	   $menu_item->setAction("SHARE");
	   array_push($menuItems, $menu_item);

		$menuItem = new Google_MenuItem();
		//Para que el usuario pueda escuchar el texto de la descripción
		$menuItem->setAction("READ_ALOUD");
		array_push($menuItems, $menuItem);
		$menuItem = new Google_MenuItem();
		$menuItem->setAction("PLAY_VIDEO");
		$menuItem->setPayload("https://www.youtube.com/watch?v=v1uyQZNg2vE");
		array_push($menuItems, $menuItem);

		$menuItem = new Google_MenuItem();
		$menuValue = new Google_MenuValue();
		$menuValue->setDisplayName("Eliminar noticia");
		$menuValue->setIconUrl("http://www.junipero.com.ar/upvnews/static/images/ic_delete_50.png");
		$menuItem->setValues(array($menuValue));
		$menuItem->setAction("CUSTOM");
		$menuItem->setId("eliminar-noticia");
		array_push($menuItems, $menuItem);
		$card->setMenuItems($menuItems);
		array_push($cards, $card);
	}
	return $cards;
}
function getNews(){
	$news = array();
	for ($i=0; $i < 3; $i++) {
		$new = array();
		$new['title'] = "Noticia de ejemplo: " . $i;
		$new['description'] = "Cuerpo de la noticia " . $i;
		$new['image'] = "http://www.upv.es/contenidos/IIICONG/info/786432C.jpg";
		array_push($news, $new);
	}
	return $news;
}
