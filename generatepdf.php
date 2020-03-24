<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	require 'vendor/autoload.php';
	use Dompdf\Dompdf;
	// instantiate and use the dompdf class
	$dompdf = new Dompdf();


	// if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// 	exit;
	// } else {
		// $mpdf = new \Mpdf\Mpdf();
		$mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tempdir']);
		// $_POST["name"];

		$args = array(
			"fullname" => 70, 
			"born" => 10, 
			"born-town" => 65, 
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

		foreach ($args as $key => $val) {
			if(!isset($_POST[$key])) header('Location: http://www.miautocertifico.it/');
			if ($key == "born" || $key == "release-data" ){
				$date = date_create($_POST[$key]);
				$_POST[$key] = date_format($date,"d/m/Y");
			}
			if($key == "reason"){
				$text = str_replace($_POST[$key], $_POST[$key] . '" checked', $text);
			}else{
				$text = str_replace("{{" . $key . "}}" , "<u>" . $_POST[$key] . "</u>" . ( strlen($_POST[$key]) < $val ? str_repeat("_", $val - strlen($_POST[$key])) : "" ), $text);
			}
		}

		$dompdf->loadHtml($text);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->set_option('defaultFont', 'Courier');
		$dompdf->render();
		// $dompdf->stream();
		$dompdf->stream("dompdf_out.pdf", array("Attachment" => false));
	// }


?>