<?php

function translateDate($date){

	$french = array('Aujourd\'hui', 'Hier');
	$english = array('Today', 'Yesterday');
	$date = str_replace($french, $english, $date);
	$date = strtotime($date);

	return $date;

};

include('simple_html_dom.php');

$url = 'http://www.leboncoin.fr/locations/offres/lorraine/meurthe_et_moselle/?f=p&th=1&mre=550&ret=1&ret=2&location=Nancy%2054000';
$html = file_get_html($url);

$annonces = array();

foreach($html->find('.list-lbc a') as $annonce){

	$annonce_id = parse_url($annonce->href);
	$annonce_id = $annonce_id['path'];
	$annonce_id = preg_match_all('!\d+!', $annonce_id, $matches);
	$annonce_id = $matches[0][0];

	$annonces[] = array(

		'id' => $annonce_id,
		'title' => trim($annonce->find('.lbc .detail .title', 0)->plaintext),
		'url' => $annonce->href,
		'timestamp' => translateDate(trim(strip_tags($annonce->find('.lbc .date', 0)->innertext))),
		'price' => trim($annonce->find('.lbc .detail .price', 0)->plaintext)	

		);

}

$last_annonce = $annonces[0]['timestamp'];

$annonces_new = $annonces;
$annonces_old = unserialize(file_get_contents('datas/annonces.txt'));

function udiffCompare($a, $b){

	return $a['id'] - $b['id'];

}

$diff = array_udiff($annonces_new, $annonces_old, 'udiffCompare');

if (!empty($diff)){

	$i = 1;

	foreach ($diff as $result) {

		$message = '#'.$i++.' Publiée le '.date("d/m/y à H:i", $result['timestamp']).' : <a href="'.$result['url'].'" target="_blank">'.$result['title'].' ('.$result['price'].')</a><hr>';

	}

	file_put_contents('datas/annonces.txt', serialize($annonces));

} else {

	$message = 'Pas de nouvelles annonces depuis le '.date('d/m/y à H:i', $last_annonce).' !';

}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>

<?php 

if (isset($message)){

	echo $message;

}


?>

</body>
</html>