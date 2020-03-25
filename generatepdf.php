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

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

	//Import PHPMailer classes into the global namespace
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;

	require 'vendor/autoload.php';
	use Dompdf\Dompdf;

	$dompdf = new Dompdf();

	if ($_SERVER['REQUEST_METHOD'] != 'POST') header('Location: https://www.miautocertifico.it/');
	else {
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
			/*
			if ($key == "born" || $key == "release-data" ){
				$date = date_create($_POST[$key]);
				$_POST[$key] = date_format($date,"d/m/Y");
			}
			*/
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

		$fname = "AUTOCERTIFICAZIONE-".$_POST["fullname"]."-".date("dmY");
		if(isset($_POST['email'])){
			$mail = new PHPMailer;
			$mail->isSMTP();
			// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 587;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->SMTPAuth = true;

			$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
			$dotenv->load();
			$mail->Username = getenv('EMAIL');
			$mail->Password = getenv('PASSWORD');

			$mail->setFrom('info@miautocertifico.it', 'miautocertifico.it');
			$mail->addReplyTo('info@miautocertifico.it', 'miautocertifico.it');

			$mail->addAddress($_POST['email'], $_POST['fullname']);

			$mail->Subject = $fname;

			$html = file_get_contents('email.html');
			$html = $html = str_replace("{{fullname}}", $_POST['fullname'], $html);
			$mail->msgHTML($html);
			$mail->AltBody = 'Grazie per aver usufruito del servizio gratuito di miautocertifico.it. Con questa email, Le confermiamo l’avvenuta ricezione della sua autodichiarazione che può trovare in allegato e che può facilmente stampare. L’autodichiarazione per lo spostamente presente sul nostro sito è quella in corso di validità monitorando gli aggiornamenti da parte del Governo Italiano.';

			$pdfString = $dompdf->output();	
			$mail->addStringAttachment($pdfString, $fname.".pdf");
			// $mail->addAttachment('images/phpmailer_mini.png');

			$mail->send();
			header('Location: https://www.miautocertifico.it/');

		} else $dompdf->stream($fname.".pdf");
		// $dompdf->stream("$fname".pdf", array("Attachment" => false));
	}

?>