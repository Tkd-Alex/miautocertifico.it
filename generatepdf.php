<?php

	function generateRandomString($length=10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	require 'vendor/autoload.php';
	use Dompdf\Dompdf;
	// instantiate and use the dompdf class
	$dompdf = new Dompdf();


	if ($_SERVER['REQUEST_METHOD'] != 'POST') {
		exit;
	} else {
		$args = array(
			"fullname" => 70,
			"born" => 10,
			"born-town" => 60,
			"current-town" => 31,
			"current-address" => 41,
			"domicile" => 20,
			"domicile-address" => 25,
			"identified-on" => 30,
			"released-by" => 55,
			"release-data" => 10,
			"document-number" => 37,
			"telephone-number" => 25,
			"from" => 45,
			"destination" => 40,
			"reason" => 0,
			"text-box" => 0
		);

		$text = file_get_contents('template.html');
		$text = $text = str_replace("{{today}}", date("d/m/Y H:i:s"), $text);

		foreach ($args as $key => $val) {
			if(!isset($_POST[$key])) header('Location: http://www.miautocertifico.it/');
			if ($key == "born" || $key == "release-data" ){
				$date = date_create($_POST[$key]);
				$_POST[$key] = date_format($date,"d/m/Y");
			}
			if($key == "reason") $text = str_replace($_POST[$key], $_POST[$key] . '" checked', $text);
			else $text = str_replace("{{" . $key . "}}" , $_POST[$key] , $text);
		}

		$fname = generateRandomString(36);
		$data_uri = $_POST["imageData"];
		$decoded_image = base64_decode($data_uri);
		file_put_contents($fname . ".png", $decoded_image);

		$text = str_replace("{{urlimage}}", $fname . ".png", $text);

		$dompdf->loadHtml($text);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->set_option('defaultFont', 'Courier');
		$dompdf->render();
		unlink($fname . ".png");

		$dompdf->stream("AUTOCERTIFICAZIONE-".$_POST["fullname"]."-".date("dmY").".pdf");
		// $dompdf->stream("AUTOCERTIFICAZIONE-".$_POST["fullname"]."-".date("dmY").".pdf", array("Attachment" => false));
	}

?>