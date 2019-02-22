<?php
	require_once 'sms.secrets';
	define('DEBUG', true);
	define('PARAMS', array('to', 'from', 'body', 'is_mms'));
	define('PARAMS_MMS', array('url', 'file_name', 'mime_type', 'file_size'));
	define('DEFAULT_NUM', '8002221222');
	define('MAX_ATTACHMENT_SIZE', 25*(1024*1024));

	$file = null;
	if (DEBUG) {
		$file = @tempnam('/var/tmp', 'sms.');
		if (!$file) {
			fail('STORE_INIT');
		}
		debug_log('File: ' . $file);
	}

	debug_log('INIT');
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	require_once 'PHPMailer/src/Exception.php';
	require_once 'PHPMailer/src/PHPMailer.php';
	require_once 'PHPMailer/src/SMTP.php';

	debug_log('READ');
	$raw = @file_get_contents('php://input');
	if (!$raw) {
		fail('READ');
	}
	if (DEBUG) {
		if (!@file_put_contents($file . '.raw', print_r($raw, true))) {
			fail('STORE_RAW');
		}
	}

	debug_log('JSON');
	$data = json_decode($raw);
	if (!$data || !is_object($data)) {
		fail('JSON');
	}
	unset($raw);
	if (DEBUG) {
		if (!@file_put_contents($file . '.decoded', print_r($data, true))) {
			fail('STORE_DECODED');
		}
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
	cleanNum($sms, 'to');
	cleanNum($sms, 'from');
	if (!$sms['body']) {
		$sms['body'] = ' ';
	}

	debug_log('FIND_MMS');
	if ($sms['is_mms']) {
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
	unset($data);
	if (DEBUG) {
		debug_log(print_r($sms, true));
		if (!@file_put_contents($file, print_r($sms, true))) {
			fail('STORE_PARSED');
		}
	}

	debug_log('MAIL');
	$mail = new PHPMailer();
	$mail->setFrom($sms['from'] . '@' . FROM_DOMAIN, 'SMS ' . $sms['from_pretty']);
	$mail->addAddress(TO_ADDR, TO_NAME);
	$mail->Subject = 'SMS Message ' . $sms['from_pretty'] . ' => ' . $sms['to_pretty'];
	$mail->Body = $sms['body'];
	$files = array();
	foreach ($sms['media'] as $file) {
		debug_log('MEDIA');
		if ($file['file_size'] < 1 || $file['file_size'] > MAX_ATTACHMENT_SIZE) {
			debug_log('FILE SIZE: ' . $file['file_size']);
			$mail->Body .= "\nWill not fetch " .
				'(' . $file['file_size'] . '/' . MAX_ATTACHMENT_SIZE . '): ' .
				$file['url'] . "\n";
			continue;
		}
		$path = download($file['url']);
		if (!$path) {
			debug_log('DOWNLOAD FAILED: ' . $file['url']);
			$mail->Body .= "\nCould not fetch: " . $file['url'] . "\n";
			continue;
		}
		debug_log('ADDING: ' . $path);
		$files[] = $path;
		$mail->addAttachment($path, $file['file_name'], 'base64', $file['mime_type'], 'attachment');
	}
	if (!$mail->send()) {
		debug_log('Mail error: ' . $mail->ErrorInfo);
		fail('MAIL');
	}
	foreach ($files as $path) {
		debug_log('REMOVING: ' . $path);
		@unlink($path);
	}
	unset($mail);

	debug_log('CLEANUP');
	unset($sms);

	function cleanNum(&$sms, $field) {
		if (preg_match('/\d*(\d{3})(\d{3})(\d{4})\D*$/', $sms[$field], $matches)) {
			$sms[$field] = $matches[1] . $matches[2] . $matches[3];
			$sms[$field . '_pretty'] = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
		} else {
			debug_log($field . ' number format error: ' . $sms[$field]);
			$sms[$field] = DEFAULT_NUM;
		}
	}

	function download($url) {
		$file = @tempnam(sys_get_temp_dir(), 'SMS_media.');
		if (!$file) {
			return false;
		}
		$fp = @fopen($file, 'w+');
		if (!$fp) {
			return false;
		}
	
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_MAX_DEFAULT');
		curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		fclose($fp);

		if ($code == 200) {
			return $file;
		} else {
			unlink($file);
			return false;
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
