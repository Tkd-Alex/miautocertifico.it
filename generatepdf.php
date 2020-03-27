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

	function post_captcha($user_response) {
		$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
		$dotenv->load();

		$fields_string = '';
		$fields = array(
			'secret' => getenv('CAPTCHA'),
			'response' => $user_response
		);
		foreach($fields as $key=>$value)
		$fields_string .= $key . '=' . $value . '&';
		$fields_string = rtrim($fields_string, '&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);

		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}

	// https://stackoverflow.com/questions/39720230/php-antiflood-how-to-limit-2-requests-per-second
	$time_interval = 1; #In seconds
	$max_requests = 5;
	$fast_request_check = ($_SESSION['last_session_request'] > time() - $time_interval);

	if (!isset($_SESSION)) {
		# This is fresh session, initialize session and its variables
		session_start();
		$_SESSION['last_session_request'] = time();
		$_SESSION['request_cnt'] = 1;
	} elseif ($fast_request_check && ($_SESSION['request_cnt'] < $max_requests)) {
		# This is fast, consecutive request, but meets max requests limit
		$_SESSION['request_cnt']++;
	} elseif ($fast_request_check) {
		# This is fast, consecutive request, and exceeds max requests limit - kill it
		die();
	} else {
		# This request is not fast, so reset session variables
		$_SESSION['last_session_request'] = time();
		$_SESSION['request_cnt'] = 1;
	}

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

	//Import PHPMailer classes into the global namespace
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\OAuth;

	// Alias the League Google OAuth2 provider class
	use League\OAuth2\Client\Provider\Google;

	//SMTP needs accurate times, and the PHP time zone MUST be set
	//This should be done in your php.ini, but this is how to do it if you don't have access to that
	date_default_timezone_set('Etc/UTC');

	require 'vendor/autoload.php';
	use Dompdf\Dompdf;

	$dompdf = new Dompdf();

	if ($_SERVER['REQUEST_METHOD'] != 'POST') header('Location: https://www.miautocertifico.it/');
	else {
		if(isset($_POST['email']) && $_POST['email'] != "") $fileDownload = false;
		else $fileDownload = true;

		if(!isset($_POST['g-recaptcha-response'])){
			if(!$fileDownload){
				echo json_encode(array(
					'status' => false,
					'message' => 'Perfavore risolvi il captcha per proseguire.'
				));
				return;
			} else header('Location: https://www.miautocertifico.it/');
		}

		$res = post_captcha($_POST['g-recaptcha-response']);
		if (!$res['success']){
			if(!$fileDownload){
				echo json_encode(array(
					'status' => false,
					'message' => 'Perfavore risolvi il captcha per proseguire.'
				));
				return;
			} else header('Location: https://www.miautocertifico.it/');
		}

		sleep(2);

		$args = array(
			"fullname",
			"born",
			"born-town",
			"current-town",
			"current-address",
			"domicile",
			"domicile-address",
			"identified-on",
			"released-by",
			"rel-dt",
			"document-number",
			"telephone-number",
			"from",
			"destination",
			"region-1",
			"region-2",
			"measure",
			"reason",
			"text-box"
		);

		$text = file_get_contents('template.html');
		$text = $text = str_replace("{{today}}", date("d/m/Y H:i:s"), $text);

		foreach ($args as $key) {
			if(!isset($_POST[$key]) || trim($_POST[$key]) == ""){
				if(!$fileDownload){
					echo json_encode(array(
						'status' => false,
						'message' => 'Assicurati di aver inserito tutti i campi'
					));
					return;
				} else header('Location: https://www.miautocertifico.it/');
			}
			if($key == "reason") $text = str_replace($_POST[$key], $_POST[$key] . '" checked', $text);
			else $text = str_replace("{{" . $key . "}}" , trim($_POST[$key]) , $text);
		}

		// $fname = "tmp/" . generateRandomString(36);
		$fname = generateRandomString(36);
		$data_uri = $_POST["imageData"];
		$decoded_image = base64_decode($data_uri);
		file_put_contents($fname . ".png", $decoded_image);

		$text = str_replace("{{urlimage}}", $fname . ".png", $text);

		$dompdf->loadHtml($text);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->set_option('defaultFont', 'Times New Roman');
		$dompdf->render();
		unlink($fname . ".png");

		$fname = "AUTOCERTIFICAZIONE-".$_POST["fullname"]."-".date("dmY");
		if(!$fileDownload){
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) header('Location: http://www.miautocertifico.it/');

			$mail = new PHPMailer;
			$mail->isSMTP();
			// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 587;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->SMTPAuth = true;

			$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
			$dotenv->load();

			//Set AuthType to use XOAUTH2
			$mail->AuthType = 'XOAUTH2';

			//Fill in authentication details here
			//Either the gmail account owner, or the user that gave consent
			$email = getenv('EMAIL');
			$clientId = getenv('GMAILCLIENTID');
			$clientSecret = getenv('GMAILSECRET');

			//Obtained by configuring and running get_oauth_token.php
			//after setting up an app in Google Developer Console.
			$refreshToken = getenv('GMAILREFRESH');

			//Create a new OAuth2 provider instance
			$provider = new Google(
				[
					'clientId' => $clientId,
					'clientSecret' => $clientSecret,
				]
			);

			//Pass the OAuth provider instance to PHPMailer
			$mail->setOAuth(
				new OAuth(
					[
						'provider' => $provider,
						'clientId' => $clientId,
						'clientSecret' => $clientSecret,
						'refreshToken' => $refreshToken,
						'userName' => $email,
					]
				)
			);

			$mail->setFrom('info@miautocertifico.it', 'miautocertifico.it');
			$mail->addReplyTo('info@miautocertifico.it', 'miautocertifico.it');

			$mail->addAddress($_POST['email'], $_POST['fullname']);

			$mail->Subject = $fname;

			$mail->CharSet = PHPMailer::CHARSET_UTF8;

			$html = file_get_contents('email.html');
			$html = $html = str_replace("{{fullname}}", $_POST['fullname'], $html);
			$mail->msgHTML($html);
			$mail->AltBody = 'Grazie per aver usufruito del servizio gratuito di miautocertifico.it. Con questa email, Le confermiamo l’avvenuta ricezione della sua autodichiarazione che può trovare in allegato e che può facilmente stampare. L’autodichiarazione per lo spostamente presente sul nostro sito è quella in corso di validità monitorando gli aggiornamenti da parte del Governo Italiano.';

			$pdfString = $dompdf->output();
			$mail->addStringAttachment($pdfString, $fname.".pdf");
			// $mail->addAttachment('images/phpmailer_mini.png');

			$mail->send();
			// header('Location: https://www.miautocertifico.it/');
			echo json_encode(array(
				'status' => true,
				'message' => 'Grazie per aver usufruito di miautocertifico.it. Verifica la tua posta in arrivo o la cartella spam.'
			));
			return;

		} else $dompdf->stream($fname.".pdf");
		// $dompdf->stream("$fname".pdf", array("Attachment" => false));
	}

?>
