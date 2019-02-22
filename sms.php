<?php
	require_once 'sms.secrets';
	define('DEBUG', true);
	define('PARAMS', array('to', 'from', 'body', 'is_mms'));
	define('PARAMS_MMS', array('url', 'file_name', 'mime_type', 'file_size'));
	define('DEFAULT_NUM', '8002221222');

	debug_log('INIT');
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	require_once 'PHPMailer/src/Exception.php';
	require_once 'PHPMailer/src/PHPMailer.php';
	require_once 'PHPMailer/src/SMTP.php';

	debug_log('READ');
	$data = @file_get_contents('php://input');
	if (!$data) {
		fail('READ');
	}

	debug_log('JSON');
	$data = @json_decode($data);
	if (!$data || !is_object($data)) {
		fail('JSON');
	}

	debug_log('FIND');
	if (!property_exists($data, 'data') || !is_object($data->{'data'}) || 
		!property_exists($data->{'data'}, 'attributes') ||
		!is_object($data->{'data'}->{'attributes'})
	) {
		fail('FIND');
	}
	$msg = $data->{'data'}->{'attributes'};

	debug_log('PARSE');
	foreach (PARAMS as $param) {
		if (!property_exists($msg, $param)) {
			fail('PARSE: ' . $param);
		}
	}
	$sms = array();
	foreach (PARAMS as $param) {
		$sms[$param] = $msg->{$param};
	}
	unset($msg);

	if ($sms['is_mms']) {
		debug_log('FIND_MMS');
		if (!property_exists($data, 'included') || !is_array($data->{'included'})) {
			fail('FIND_MMS');
		}
		$msg = $data->{'included'};
		foreach ($msg as $part) {
			if (!property_exists($part, 'attributes') || !is_object($part->{'attributes'})) {
				fail('FIND_MMS_PART');
			}
			$part = $part->{'attributes'};

			debug_log('PARSE_MMS');
			foreach (PARAMS_MMS as $param) {
				if (!property_exists($part, $param)) {
					fail('PARSE_MMS: ' . $param);
				}
			}
			$media = array();
			foreach (PARAMS_MMS as $param) {
				$media[$param] = $part->{$param};
			}
			$sms['media'][] = $media;
			unset($msg);
		}
	}

	debug_log('CLEANUP');
	if (preg_match('/\d*(\d{10})\D*$/', $sms['from'], $matches)) {
		$sms['from'] = $matches[1];
	} else {
		$sms['from'] = DEFAULT_NUM;
	}
	if (preg_match('/\d*(\d{10})\D*$/', $sms['to'], $matches)) {
		$sms['to'] = $matches[1];
	} else {
		$sms['to'] = DEFAULT_NUM;
	}
	unset($matches);

	debug_log('MAIL');
	$mail = new PHPMailer();
	if (DEBUG) {
		$mail->SMTPDebug = 2;
	}
	$mail->setFrom($sms['from'] . '@' . FROM_DOMAIN, 'SMS ' . $sms['from']);
	$mail->addAddress(TO_ADDR, TO_NAME);
	$mail->Subject = 'SMS Message ' . $sms['to'] . ' => ' . $sms['from'];
	$mail->Body = $sms['body'];
	foreach ($sms['media'] as $file) {
		$mail->Body .= "\n\n" . $file['file_name'] . '(' . $file['mime_type'] . '): ' . $file['url'] . "\n";
	}
	if (!$mail->send()) {
		debug_log('Mail error: ' . $mail->ErrorInfo);
		fail('MAIL');
	}
	unset($mail);

	if (DEBUG) {
		debug_log('DEBUG');
		debug_log(print_r($sms, true));
		$file = @tempnam('/var/tmp', 'sms.');

		if (!$file) {
			fail('STORE_INIT');
		}
		debug_log('File: ' . $file);

		if (!@file_put_contents($file . '.raw', print_r($data, true))) {
			fail('STORE_RAW');
		}
		if (!@file_put_contents($file, print_r($sms, true))) {
			fail('STORE_PARSED');
		}
	}

	function fail($msg = 'Unknown error', $code = 500) {
		http_response_code($code);
		echo 'Unable to process request: ' . $msg . "\n";
		exit(0);
	}

	function debug_log($msg) {
		if (! DEBUG) {
			return;
		}
		echo $msg . "\n";
	}
?>
