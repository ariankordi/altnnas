<?php
// Include some functions.
require_once('NNMeta.php');
class NNAccount {
var $connection;
// Constructor, just checks for a MySQLi connection and uses it.
	public function __construct($mysql = null) {
		if($mysql) {
		$mysql->set_charset('utf8mb4');
		$this->connection = $mysql;
		} else {
		throw new Exception('MySQL connection failed to initiate');
		}
	}
	
	// Easy XML encode function, don't know where I got it. Very modified.
	private function xml_encode($mixed, $domElement = null, $DOMDocument = null) {
    if(is_null($DOMDocument)) {
        $DOMDocument = new DOMDocument('1.0', 'UTF-8');
		$DOMDocument->xmlStandalone = true;
		$DOMDocument->preserveWhiteSpace = false;
		$DOMDocument->formatOutput = false;
        $this->xml_encode($mixed, $DOMDocument, $DOMDocument);
        return $DOMDocument->saveXML();
    } else {
        if(is_object($mixed)) {
          $mixed = get_object_vars($mixed);
        }
        if(is_array($mixed)) {
            foreach($mixed as $index => $mixedElement) {
				if(is_int($index)) {
                    if($index === 0) {
                        $node = $domElement;
                    } elseif($domElement->nodeName == '#cdata-section') {
						
					} else {
                        $node = $DOMDocument->createElement($domElement->tagName);
                        $domElement->parentNode->appendChild($node);
                    }
                } elseif(0 === strpos($index, 'cdata')) {
						$index = substr($index, 5);
						$plural = $DOMDocument->createElement($index);
						$domElement->appendChild($plural);
						$node = $plural;
                        $singular = $DOMDocument->createCDATASection($mixedElement);
						$plural->appendChild($singular);
                        $node = $singular;
					} elseif($index == 'texts') {
				    $plural = $DOMDocument->createElement($index);
                    $domAttribute1 = $DOMDocument->createAttribute('xmlns:xsi');
					$domAttribute1->value = 'http://www.w3.org/2001/XMLSchema-instance';
					$domAttribute2 = $DOMDocument->createAttribute('xsi:type');
					$domAttribute2->value = 'chunkedStoredAgreementText';
					$domElement->appendChild($plural);
					$plural->appendChild($domAttribute1);
					$plural->appendChild($domAttribute2);
                    $node = $plural;
					} elseif($domElement->localName == 'texts') {
							if($index == 'main_text') {
									foreach($mixedElement as $textElement) {
									$plural = $DOMDocument->createElement($index);
									$domElement->appendChild($plural);
									$domAttribute1 = $DOMDocument->createAttribute('index');
									$domAttribute1->value = $textElement['index'];
									$plural->appendChild($domAttribute1);
									$singular = $DOMDocument->createCDATASection($textElement['value']);
									$plural->appendChild($singular);
									$node = $singular;
									}
							} else {
						$plural = $DOMDocument->createElement($index);
						$domElement->appendChild($plural);
						$node = $plural;
                        $singular = $DOMDocument->createCDATASection($mixedElement);
						$plural->appendChild($singular);
						$node = $singular;
							}
					} else {
                    $plural = $DOMDocument->createElement($index);
                    $domElement->appendChild($plural);
                    $node = $plural;
                    if(!(rtrim($index, 's') === $index) && $index != 'texts') {
                        $singular = $DOMDocument->createElement(rtrim($index, 's'));
                        $plural->appendChild($singular);
                        $node = $singular;
                    }
					}

                $this->xml_encode($mixedElement, $node, $DOMDocument);
            }
        } else {
            $mixed = is_bool($mixed) ? ($mixed ? 'true' : 'false') : $mixed;
            $domElement->appendChild($DOMDocument->createTextNode($mixed));
        }
    }
}
	// Same thing, but to decode XML into an stdClass.
	private function xml_decode($xml = null) {
    $xml = @simplexml_load_string($xml);
		if($xml) {
			return json_decode(json_encode($xml));
		} else {
			return false;
		}
	}
	
	// Takes an array and puts it in either XML or JSON.
	private function out($a = null) {
	if($a === null || !is_array($a)) {
	return false;
	}
	if(!empty($_SERVER['HTTP_ACCEPT']) && preg_match('/application\/json/', $_SERVER['HTTP_ACCEPT'])) {
	header('Content-Type: application/json;charset=UTF-8');
	return json_encode($a);
	} else {
		header('Content-Type: application/xml;charset=UTF-8');
		return $this->xml_encode($a);
		}
	}
	// Throws an error and dies. (exits)
	public function Error($errno, $var1 = null, $var2 = null) {
	// errno corresponds to this error list.
	// var1 corresponds to the variant of an error.
	// var2 is usually a cause.
	$error_list = array(
	1 => array('Bad Request', 400),
	2 => array('%s format is invalid', 400),
	3 => array(null, 400),
	4 => array(array('API application invalid or incorrect application credentials', 401), array('Invalid Grant Type', 400)),
	7 => array('Forbidden request', 403),
	8 => array('Not Found', 404),
	100 => array('Account ID already exists', 400),
	103 => array('Email format is invalid', 400),
	1100 => array(array('No stored agreement found for this country: %s and type: %s', 400), array('No stored agreement found for this country: %s and type: %s and version: %d', 400)),
	1104 => array('User ID format is not valid', 400),
	1600 => array('Unable to process request', 400),
	2001 => array(array('Internal server error', 500), array('Not implemented', 501)),
	);
	if(!is_array($errno)) {
	$errno = array(array($errno, $var1, $var2));
	}
	$final_errors = array('errors' => array());
		foreach($errno as $reqerr) {
			if(isset($error_list[$reqerr[0]])) {
				$equiv_err = $error_list[$reqerr[0]];
				if(is_array($equiv_err[0])) {
				$equiv_err = $equiv_err[$reqerr[1]];
				$reqerr[1] = $reqerr[2];
				}
					if(!is_int($equiv_err[0])) {
					// There's an error message
						if(strpos($equiv_err[0], '%s') !== null && isset($reqerr[1])) {
							if(is_array($reqerr[1])) {
							$errmsg = vsprintf($equiv_err[0], $reqerr[1]);
							} else {
							$errmsg = sprintf($equiv_err[0], htmlspecialchars($reqerr[1]));
							}
						} else {
						$errmsg = $equiv_err[0];
						}
					$errhttp = $equiv_err[1];
					}
					else {
					$errhttp = $equiv_err[0];
					}
			} else {
			$errhttp = 400;
			}
		$this_err = array();
			// var2 is usually a cause
			if(isset($var2) && is_string($var2)) {
			$this_err['cause'] = htmlspecialchars($var2);
			}
			$this_err['code'] = sprintf('%04d', $reqerr[0]);
			if(isset($errhttp)) {
			http_response_code($errhttp);
			}
			if(isset($errmsg)) {
			$this_err['message'] = htmlspecialchars($errmsg);
			}
		$final_errors['errors'][] = $this_err;
		}
	http_response_code();
	header('X-Cnection: close');
	echo $this->out($final_errors);
	exit();
	}
	// Gets client POST input and puts it into an stdClass. The first arg will make this throw a Bad Request error and exit.
	private function getClientInput($die_on_fail = 0) {
	$post = file_get_contents('php://input');
		if(empty($post)) {
		$thing = false;
		}
		elseif(!empty($_SERVER['HTTP_CONTENT_TYPE']) && preg_match('/application\/json/', $_SERVER['HTTP_CONTENT_TYPE'])) {
		$thing = json_decode($post);
		} else {
		$thing = $this->xml_decode($post);
		}
		if(!$thing && $die_on_fail) {
		$this->Error(1600, 0, 'Bad Request');
		}
		return $thing;
	}
	// Validate if device headers are valid, throws the appropriate error if they aren't.
	private function deviceValidate($die_on_fail = 1) {
		$val = nn_validator(4);
		if(is_string($val) && $die_on_fail) {
		$this->Error(2, $val);
		return true;
		}
		return false;
	}
	
	public function NotImplement() {
	//$this->Error(4, 0, 'client_id');
	$this->Error(2001, 1);
	}
	
	public function Index() {
	header('Content-Type: text/plain; charset=UTF-8');
	echo 'New request from ' . $_SERVER['REMOTE_ADDR'] . ' to ' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_ADDR']) . ' at ' . date('Y/m/d g:i:s', $_SERVER['REQUEST_TIME']);
	}

	public function GetTime() {
	// Blank response, the client will use the X-Nintendo-Date header
	}
	
	public function viewTimezones(array $args) {
	$tz_query = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY,
	$args['country']);
			if(!isset($tz_query[0])) {
			http_response_code(404);
			exit();
			}
	$timezones = array('timezones' => array());
		$order = 1;
		foreach($tz_query as $all_tz) {
		$dt = new DateTime();
		$this_tz = new DateTimeZone($all_tz);
		$dt->setTimeZone($this_tz);
		$timezones['timezones'][] = array(
		'area' => $all_tz,
		'language' => null,
		'name' => $dt->format('T'),
		'utc_offset' => $this_tz->getOffset(new DateTime("now", $this_tz)),
		'order' => $order,
		);
		$order++;
		}
	echo $this->out($timezones);
	}
	
	public function viewAgreement(array $args) {
		$err = array();
		if(empty($args['agreement'])) { $err[] = array(2, 'agreement'); }
		if(empty($args['country'])) { $err[] = array(2, 'country'); }
		if(!isset($args['version'])) { $err[] = array(2, 'version'); }
		if(isset($err[0])) {
		$this->Error($err);
		}
		// End empty check keks
			// If the user wants the latest, 
		$get_latest = (!is_int($args['version']) && $args['version'] == '@latest');
		$query_arg = array(
		filter_var($args['agreement'], FILTER_SANITIZE_STRING),
		filter_var($args['country'], FILTER_SANITIZE_STRING),
		);
			if(!$get_latest) {
			$query_arg[] = filter_var($args['version'], FILTER_VALIDATE_INT);
			}
		$query = prepared('SELECT country, language, languageName, publishDate, updated, agreeText, mainTitle, nonAgreeText, mainText, type, version FROM agreements WHERE agreements.type = ? AND agreements.country = ? ' . (!$get_latest ? 'AND agreements.version = ? ' : '') . 'ORDER BY updated DESC', $query_arg);

		if($query->num_rows == 0) {
			if($get_latest) {
			$this->Error(1100, 0, array(htmlspecialchars($args['country']), htmlspecialchars($args['agreement'])));
			} else {
			$this->Error(1100, 1, array(htmlspecialchars($args['country']), htmlspecialchars($args['agreement']), $args['version']));
			}
		}
		$row = $query->fetch_all(MYSQLI_ASSOC);
		$agreements = array('agreements' => array());
			foreach($row as $agree) {
				if(!empty($_GET['length'])) {
				$split_text = str_split($agree['mainText'], $_GET['length']);
				#var_dump($split_text);
				$current_index = 1;
				$main_text = array();
					foreach($split_text as $a) {
					$text = $a;
					$main_text[] = array('index' => $current_index, 'value' => $text);
					$current_index++;
					}
				} else {
				$main_text = array(array('index' => 1, 'value' => $agree['mainText'])); 
				}
			
			$agreements['agreements'][] = array(
			'country' => htmlspecialchars($agree['country']),
			'language' => htmlspecialchars($agree['language']),
			'language_name' => htmlspecialchars($agree['languageName']),
			'publish_date' => nn_date($agree['publishDate']),
			'updated' => nn_date($agree['updated']),
			'texts' => array(
				'agree_text' => $agree['agreeText'],
				'main_title' => $agree['mainTitle'],
				'non_agree_text' => $agree['nonAgreeText'],
				'main_text' => $main_text,
				),
			'type' => htmlspecialchars($agree['type']),
			'version' => sprintf('%04d', +$agree['version']),
			);
			}
		echo $this->out($agreements);

	}
	
	public function checkUserID(array $args) {
		if(!isset($args['user_id']) || !nn_validator(1, $args['user_id'])) {
		$this->Error(1104);
		}
		if(!nn_validator(2, $args['user_id'])) {
		$this->Error(100);
		}
	}

	public function checkDeviceStatus() {
	echo $this->out(array('device' => array()));
	
	}

	public function checkEmail() {
		if(empty($_POST['email']) || !nn_validator(3, $_POST['email'])) {
		$this->Error(103, 0, 'email');
		}
	}

	public function createPerson() {
		$this->deviceValidate();
	echo $this->out(json_decode(json_encode($this->getClientInput(1)), true));
	}

}