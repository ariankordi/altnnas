<?php

function nn_compatible_passwd($pid, $password) {
return hash('sha256', (pack('I*', $pid)) . hex2bin('02654346') . mb_convert_encoding($password, 'ascii'));
}

function nn_date($time = 0) {
	if(!is_int($time)) {
	$time = strtotime($time);
	}
return strftime("%FT%X", $time);
}

function nn_validator($type, $input = null) {
/* Types
1 - User ID validate
2 - User ID existence
3 - E-mail check
4 - Device check
5 - Client ID/secret check
*/
	switch($type) {
	case 1:
		$ok = preg_match('/^[0-9a-zA-Z-_.]{6,16}$/', $input);
	break;
	case 2:
		$query = prepared('SELECT pid FROM people WHERE people.userId = ? LIMIT 1', array($input));
		if(!$query || $query->num_rows != 0) {
		$ok = false;
		} else {
		$ok = true;
		}
	break;
	case 3:
		$ok = (filter_var($input, FILTER_VALIDATE_EMAIL) !== null);
	break;
	case 4:
		if(!isset($_SERVER['HTTP_X_NINTENDO_DEVICE_ID']) || !is_numeric($_SERVER['HTTP_X_NINTENDO_DEVICE_ID'])) {
		$ok = 'deviceId';
		}
		elseif(!isset($_SERVER['HTTP_X_NINTENDO_SERIAL_NUMBER']) || !preg_match('/^[A-Za-z]{2}[0-9]{2,10}$/', $_SERVER['HTTP_X_NINTENDO_SERIAL_NUMBER'])) {
		$ok = 'serialNumber';
		}
		elseif(!isset($_SERVER['HTTP_X_NINTENDO_PLATFORM_ID']) || !is_numeric($_SERVER['HTTP_X_NINTENDO_PLATFORM_ID']) || $_SERVER['HTTP_X_NINTENDO_PLATFORM_ID'] > 1) {
		$ok = 'platformId';
		}
		elseif(!isset($_SERVER['HTTP_X_NINTENDO_DEVICE_TYPE']) || !is_numeric($_SERVER['HTTP_X_NINTENDO_DEVICE_TYPE']) || $_SERVER['HTTP_X_NINTENDO_DEVICE_TYPE'] > 2) {
		$ok = 'deviceType';
		}
		elseif(!isset($_SERVER['HTTP_X_NINTENDO_COUNTRY']) || strlen($_SERVER['HTTP_X_NINTENDO_COUNTRY']) != 2) {
		$ok = 'country';
		}
		elseif(!isset($_SERVER['HTTP_X_NINTENDO_SYSTEM_VERSION']) || !is_numeric($_SERVER['HTTP_X_NINTENDO_SYSTEM_VERSION']) || strlen($_SERVER['HTTP_X_NINTENDO_SYSTEM_VERSION']) != 4) {
		$ok = 'version';
		}
		elseif(!isset($_SERVER['HTTP_X_NINTENDO_REGION']) || !is_numeric($_SERVER['HTTP_X_NINTENDO_REGION'])) {
		$ok = 'region';
		}
			else {
				$ok = true;
			}
	break;
	// more go here
	default:
		$ok = true;
	break;
	}
	return $ok;
}