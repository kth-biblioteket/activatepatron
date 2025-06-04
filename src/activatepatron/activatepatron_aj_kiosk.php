<?php
header('Content-Type: application/json; charset=utf-8');
require('config.php.inc'); //innehåller API-KEY.

//inkluderar hjälpklass för mailfunktioner
require_once($_SERVER['DOCUMENT_ROOT'] .  '/PHPMailer/PHPMailerAutoload.php');

if (!isset($_SESSION)) {
	session_start();
}
$errorcode = 0;
$debug = true;
/* 
	TODO 
*/

/********** 
191003
Funktion som hämtar autentiserar användare mot KTH LDAP 

**********/
function authenticateuser_ldap($user_id, $password) {
	global $ldap_api_key;
	global $ldap_api_url;
	$data = array("username" => $user_id, "password" => $password);                                                                    
	$data_string = json_encode($data);
	$ch = curl_init();
	$url = $ldap_api_url . 'login';
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		'Content-Type: application/json',                                                                                
		'Content-Length: ' . strlen($data_string))                                                                       
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	if(curl_errno($ch)) {
		$json = '"error":{"Error connecting to loginserver."}' . curl_errno($ch);
		$error = $json;
		curl_close($ch);
		return $error;
	}
	curl_close($ch);
	return $response;
}

function authenticateuser($user_id, $password) {
	global $api_key;
	$ch = curl_init();
	$url = 'https://api-eu.hosted.exlibrisgroup.com/almaws/v1/users/' . $user_id;
	$queryParams = '?' . urlencode('apikey') . '=' . urlencode($api_key) . '&' . urlencode('user_id_type') . '=' . urlencode('all_unique') . '&' . urlencode('op') . '=' . urlencode('auth') . '&' . urlencode('password') . '=' . $password;
	curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	$api_response_info = curl_getinfo($ch);
	curl_close($ch);
	$api_response_header = trim(substr($response, 0, $api_response_info['header_size']));
	$api_response_body = substr($response, $api_response_info['header_size']);
 	$error = 0;
	$xml = simplexml_load_string($api_response_body);
	if ($xml) {
		foreach( $xml as $nodes ) {
			if ($nodes->getName() == 'errorsExist') { 
				$error = 1;
				break;
			}
		}
	}
	$errortext = "";
	if ($error==1) {
		$user = json_decode(getuser($user_id),true);
		if (isset($user['errorsExist'])) {
			$errortext = ", användaren finns inte.";
		}
		else {
			if(isset($user['full_name'])) {
				$errortext = " for user " . $user['primary_id'] . ", fel password.";
			}
		}
		$result = "Error";
			$data = array(
			  "result"  => $result,
			  "message" => "Authenticate misslyckades" . $errortext
			);
			$json_data = json_encode($data);
			$error = $json_data;
			return $error;
	}
	else {
		$source = json_decode(getuser($user_id), true);
		$result = "Success";
			$data = array(
			  "result"  => $result,
			  "message" => "Authenticated",
			  "fullname" => $source['full_name'],
			  "primaryid" => $source['primary_id']
			);
			$json_data = json_encode($data);
			$error = $json_data;
			return $error;
	}
}

/********** 

Funktion som hämtar användarinformation från alma utifrån angivet ID 

**********/
function getuser($user_id) {
	global $api_key;
	$ch = curl_init();
	$url = 'https://api-eu.hosted.exlibrisgroup.com/almaws/v1/users/' . $user_id;
	$queryParams = '?' . urlencode('user_id_type') . '=' . urlencode('all_unique') . '&' . urlencode('view') . '=' . urlencode('full') . '&' . urlencode('apikey') . '=' . urlencode($api_key) . '&' . urlencode('format') . '=' . urlencode('json');
	curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}


/********

Funktion som uppdaterar en användare i Alma. Inparameter är ett userobject i XML- eller JSONformat.

*********/

function updateuser($user_object, $user_id) {
	global $api_key;
	$ch = curl_init();
	$url = 'https://api-eu.hosted.exlibrisgroup.com/almaws/v1/users/' . $user_id;
	$queryParams = '?' . urlencode('user_id_type') . '=' . urlencode('all_unique') . '&' . urlencode('apikey') . '=' . urlencode($api_key). '&' . "override=pin_number";
	curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $user_object);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($user_object)));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}


/**********

Funktion som skickar mail till beställaren

**********/
function sendconfirmemail($id,$epostadress,$username,$message,$subject) {
	global $language;

	$mail = new PHPMailer();
	
	$mail->isSMTP();
	$mail->Host = "relayhost.sys.kth.se";
	$mail->SMTPAuth   = FALSE;
	$mail->SMTPSecure = "tls";

	$mail->CharSet = 'UTF-8';
	$mail->From      = 'biblioteket@kth.se';
	$mail->FromName  = 'KTH Biblioteket';
	$mail->Subject   = $subject;
	$mail->Body = $message;
	$mail->msgHTML($message);
	$mail->AddAddress( $epostadress );

	if($mail->Send()){
		return 'Success';
	}else{
		return $mail->ErrorInfo;
	}
}


/**********
Funktion som hanterar felmeddelanden från ALMA

Exempel på Error från Alma:
<web_service_result xmlns="http://com/exlibris/urm/general/xmlbeans"><errorsexist>true</errorsexist><errorlist><error><errorcode>401851</errorcode><errormessage>User with identifier alma0005@almakth.se of type Primary id already exists.
(Tracking ID: E02-2005081805-4QVEB-AWAE2122010420)</errormessage></error></errorlist></web_service_result>

***********/
function checkifAlmaerror($response,$format) {
	if ($format == "xml") {
		$xml = simplexml_load_string($response);
		foreach( $xml as $nodes ) {
			if ($nodes->getName() == 'errorsExist') { 
				$error = 1;
				break;
			}
			else {
				$error = 0;
			}
		}
		if ($error == 1) {
			$result = "Error";
			$message = "Fel: "  . str_replace(array("\r", "\n"), "", $xml->errorList[0]->error->errorMessage);
			//Kända "fel" som kan uppstå
			if (strpos(str_replace(array("\r", "\n"), "", $xml->errorList[0]->error->errorMessage),"User with identifier") !== false) {
				if(!empty($_POST['lang'])) {
					if ($_POST['lang'] == 'sv') {
						$message = "Ditt KTH-id \"" . $_SESSION['kth_id'] . "\" är inte registrerad som låntagare hos oss. Vänligen kontrollera att du skrivit in rätt.<p>Behöver du hjälp så kan du <a href='https://www.kth.se/kthb/besok-och-kontakt/kontakta/fraga-oss-1.546631'>kontakta oss</a> på biblioteket.";
					} else {
						$message = "Your KTH-id \"" . $_SESSION['kth_id'] . "\" is not registered as a patron at the library. Please make sure you have typed the correct username .<p>If you need assistance don't hesitate to <a href='https://www.kth.se/kthb/besok-och-kontakt/kontakta/fraga-oss-1.546631'>contact us</a>.";
					}
				}
				$callback = "";
			} 
			
			if (strpos(str_replace(array("\r", "\n"), "", $xml->errorList[0]->error->errorMessage),"Patron has duplicate") !== false) {
				if(!empty($_POST['lang'])) {
					if ($_POST['lang'] == 'sv') {
						$message = "Du har redan gjort en beställning av detta material.";
					} else {
						$message = "You already have an active request for this material.";
					}
				}
			}
			
			if (strpos(str_replace(array("\r", "\n"), "", $xml->errorList[0]->error->errorMessage),"Patron does not have resource sharing requesting privileges") !== false) {
				if ($_POST['lang'] == 'sv') {
					$message = "Du har inte behörighet att beställa material.";
				} else {
					$message = "You are not authorized to order material.";
				}
			}
			
			$data = array(
			  "result"  => $result,
			  "message" => $message
			);
		}
		else {
			$result = "Success";
			$data = array(
			  "result"  => $result,
			  "message" => "No Errors"
			);
		}
		$json_data = json_encode($data);
		$error = $json_data;
	} else { //JSON
		//{"errorsExist":true,"errorList":{"error":[{"errorCode":"401861","errorMessage":"User with identifier dfsfsfsdfaa was not found.","trackingId":"E01-0804072350-LLRHA-AWAE790016211"}]},"result":null}
		$responsearray = json_decode($response,TRUE);
		
		if(!empty($responsearray['errorList'])) {
			$result = "Error";
			$message = "Fel: "  . str_replace(array("\r", "\n"), "", $responsearray['errorList']['error'][0]['errorMessage']);
			//Kända "fel" som kan uppstå
			if (strpos(str_replace(array("\r", "\n"), "", $responsearray['errorList']['error'][0]['errorMessage']),"User with identifier") !== false) {
				if(!empty($_POST['lang'])) {
					if ($_POST['lang'] == 'sv') {
						$message = "<p>Ditt KTH-id \"" . $_SESSION['kth_id'] . "\" är inte registrerat som låntagare hos oss.</p>". 
						"<p>Om du är ny student eller anställd på KTH och precis har kvitterat ut ditt KTH-konto kan du behöva vänta 1-2 dagar innan dina uppgifter finns i vårt system. Du kan därefter försöka igen.</p>" .
						"<p>Är du KTH-anknuten men är här som gästdoktorand, stipendiat eller annan typ av tjänst som inte finansieras av KTH kan du behöva fylla i en <a href=\"https://www.kth.se/kthb/lana-och-bestall/lana-och-bestall/ansok-om-biblioteksk\">ansökan manuellt</a>.</p>".
						"<p>Om du är osäker, <a href='https://www.kth.se/kthb/besok-och-kontakt/kontakta/fraga-oss-1.546631'>kontakta oss</a> på biblioteket.";
					} else {
						$message = "<p>Your KTH-id \"" . $_SESSION['kth_id'] . "\" is not registered as a patron at the library.</p>
						<p>If you are a new student or employee at KTH and just got your KTH-account you may have to wait 1-2 days before your information is in our library system. You may then try to activate again.</p>
						<p>If you are KTH-affiliated but are a guest doctoral student, on an external scholarship or som other type of employment not financed by KTH you may have to fill in an <a href=\"https://www.kth.se/en/kthb/lana-och-bestall/lana-och-bestall/ansok-om-biblioteksk\">application manually</a>.</p>
						<p>If you need assistance don't hesitate to <a href='https://www.kth.se/en/kthb/besok-och-kontakt/kontakta/fraga-oss-1.546631'>contact us</a>.";
					}
				}
				$callback = "";
			}
			
			if (strpos(str_replace(array("\r", "\n"), "", $responsearray['errorList']['error'][0]['errorMessage']),"Patron has duplicate") !== false) {
				if(!empty($_POST['lang'])) {
					if ($_POST['lang'] == 'sv') {
						$message = "Du har redan gjort en beställning av detta material.";
					} else {
						$message = "You already have an active request for this material.";
					}
				}
			}
			
			if (strpos(str_replace(array("\r", "\n"), "", $responsearray['errorList']['error'][0]['errorMessage']),"Patron does not have resource sharing requesting privileges") !== false) {
				if ($_POST['lang'] == 'sv') {
					$message = "Du har inte behörighet att beställa material.";
				} else {
					$message = "You are not authorized to order material.";
				}
			}
			
			$data = array(
			  "result"  => $result,
			  "message" => $message
			);
		}
		else {
			$result = "Success";
			$data = array(
			  "result"  => $result,
			  "message" => "No Errors"
			);
		}
		$json_data = json_encode($data);
		$error = $json_data;	
	}
	return $error ;
}

/**********

Funktion som ser till att göra "escape" på de fält som kan innehålla specialtecken som: ",/,\ osv...

**********/
function escapeJsonString($value) {
    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}

/**********

Funktion för att lägga till en user note

**********/
function addusernote($userarray,$note_text) {
/*
"user_note":[{"note_type":{"value":"POPUP","desc":"General"},"note_text":"Test /Thomas","user_viewable":false,"created_by":"tholind@kth.se","created_date":"2016-08-04T07:35:36.239Z","segment_type":"Internal"}]
*/
	$numberofcurrentusernotes  = count($userarray['user_note']);
	$userarray['user_note'][$numberofcurrentusernotes]['note_text'] = $note_text;
	$userarray['user_note'][$numberofcurrentusernotes]['note_type']['value'] = "POPUP";
	$userarray['user_note'][$numberofcurrentusernotes]['note_type']['desc'] = "General";
	$userarray['user_note'][$numberofcurrentusernotes]['segment_type'] = "Internal";
	return $userarray;	
}

/**********

Huvudkod (som körs om användarID skickats från formuläret)

**********/
if(!empty($_REQUEST['auth'])) {
	header('Content-Type: text/html; charset=utf-8');
	$language = "en"; 
	if(!empty($_GET['lang'])) {
		if($_GET['lang'] == 'sv') {
			$language = 'sv';
		}
	}
	?>
	<link type="text/css" href="css/activatepatron_kiosk.css" rel="stylesheet" />
	<style>
	
		.form-label {
			margin-bottom: 0.5rem;
		}
		label {
			display: inline-block;
		}
		.form-control {
			display: block;
			width: 300px;
			padding: 0.375rem 0.75rem;
			font-size: 1rem;
			font-weight: 400;
			line-height: 1.5;
			color: #212529;
			background-color: #fff;
			background-clip: padding-box;
			border: 1px solid #ced4da;
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			border-radius: 0.25rem;
			transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
		}
		button, input, optgroup, select, textarea {
			margin: 0;
			font-family: inherit;
			font-size: inherit;
			line-height: inherit;
		}
		[type=button]:not(:disabled), [type=reset]:not(:disabled), [type=submit]:not(:disabled), button:not(:disabled) {
			cursor: pointer;
		}

		.btn {
			display: inline-block;
			font-weight: 400;
			line-height: 1.5;
			color: #212529;
			text-align: center;
			text-decoration: none;
			vertical-align: middle;
			cursor: pointer;
			-webkit-user-select: none;
			-moz-user-select: none;
			user-select: none;
			background-color: transparent;
			border: 1px solid transparent;
			padding: 0.375rem 0.75rem;
			font-size: 1rem;
			border-radius: 0.25rem;
			transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
		}

		.btn-primary {
			color: #fff;
			background-color: #0d6efd;
			border-color: #0d6efd;
		}


		[type=button], [type=reset], [type=submit], button {
			-webkit-appearance: button;
		}
		button, select {
			text-transform: none;
		}
		button, input, optgroup, select, textarea {
			margin: 0;
			font-family: inherit;
			font-size: inherit;
			line-height: inherit;
		}
		button {
			border-radius: 0;
		}

		.login-wrapper {
			display: flex;
			flex-direction: column;
			align-items: center;
		}
	</style>
	<div class="login-wrapper">
		<?php if ($language == 'sv') {?>
			<h1>Logga in</h1>
		<?php } else {?>
			<h1>Login</h1>
		<?php }?>

        <form id="loginuserform" action="activatepatron_aj_kiosk.php?auth=<?php echo $_REQUEST['auth'] . "&lang=" . $language?>" method="post">
			<?php if ($language == 'sv') {?>
				<label class="form-label">KTH-Konto</label>
				<input class="form-control" type="username" id="username" name="username" placeholder="Ditt KTH-Konto(utan @kth.se)">
				<label class="form-label">Lösenord</label>
				<input class="form-control" type="password" id="password" name="password" placeholder="Ange lösenord">
				<button id="submit" type="submit" class="btn btn-primary">Logga in</button>
			<?php } else {?>
				<label class="form-label">KTH-Account</label>
				<input class="form-control" type="username" id="username" name="username" placeholder="Your KTH-Account(without @kth.se)">
				<label class="form-label">Password</label>
				<input class="form-control" type="password" id="password" name="password" placeholder="password">
				<button id="submit" type="submit" class="btn btn-primary">Login</button>
			<?php }?>
        </form>
		<script>
			window.addEventListener("keydown", function(event) {
				console.log(event.key)
			});
		</script>
	<?php
}

if(!empty($_REQUEST['auth'])) {
	if(!empty($_POST['username']) && !empty($_POST['password'])) {
		if($_REQUEST['auth'] == 'alma') {
			$loginresponse = authenticateuser($_POST['username'], $_POST['password']);
		}
		if($_REQUEST['auth'] == 'ldap') {
			$loginresponse = authenticateuser_ldap($_POST['username'] . "@ug.kth.se", $_POST['password']);
		}
		$loginresponse = json_decode($loginresponse);
		if($_REQUEST['auth'] == 'ldap') {
			if ($loginresponse->auth) {
				session_start() ;
				$_SESSION['kth_id']  	= $_POST['username'] . "@kth.se";
				$userid 				= $_SESSION['kth_id'] ;
				header("location: index_kiosk.php" . "?lang=" . $language );
			} else {
				echo "<div id='errormessage'>Wrong credentials, try again</div>";
			}
		}
		if($_REQUEST['auth'] == 'alma') {
			if ($loginresponse->result == "Success") {
				session_start() ;
				$_SESSION['kth_id']  	= $loginresponse->primaryid;
				$userid 				= $_SESSION['kth_id'] ;
				header("location: /activatepatron/index_kiosk.php" );
			} else {
				header("location: /activatepatron/activatepatron_aj_kiosk.php?auth=alma" );
			}
		}
	}
	?>
	</div>
<?php
} else {
	if(isset($_SESSION['kth_id'])) {
		if(!empty($_POST['getuserid'])) {
			if($_POST['getuserid'] == 1) {
				$currentuser 			= getuser($_SESSION['kth_id']);
				$almaresponse = checkifAlmaerror($currentuser,"json");
				$jsonalmaresponse = json_decode($almaresponse);
				if ($jsonalmaresponse->result == "Error") {
					print $almaresponse;
				} else {
					
					$source = json_decode($currentuser,TRUE);
					$index = 0;
					$activepatron = false;
					foreach ($source['user_role'] as $value) {
						if($value['role_type']['value'] == "200") {
							if($value['status']['value'] == "ACTIVE") {
								$activepatron = true;
							}
						}
						$index++;
					}
					$data = array(
									"result"  => "Success",
									"message" => $source['first_name'] . " " .  $source['last_name'],
									"activepatron" => $activepatron
								);
					$json_data = json_encode($data);
					$jsonalmaresponse = json_decode($json_data);
					$almaresponse=$json_data;
					print $almaresponse;
				}
			}
		}
	    else { 
			if(!empty($_POST['activate'])) {
				if($_POST['activate'] == 1) {
					$currentuser 			= getuser($_SESSION['kth_id']);
					$almaresponse = checkifAlmaerror($currentuser,"json");
					$jsonalmaresponse = json_decode($almaresponse);
					if ($jsonalmaresponse->result == "Error") {
						print $almaresponse;
					} else {
						$source = json_decode($currentuser,TRUE);
						$user_primary_id = $source['primary_id'];

						foreach ($source['contact_info']['email'] as $value) {
							if($value['preferred'] == "1") {
								$epostadress = $value['email_address'];
							}
						}
						$fullname = $source['first_name'] . " " . $source['last_name'];

						if ($_POST['isactivepatron'] == "true") {
							$source['user_role'][0]['status']['value'] = "INACTIVE";
							$source['user_role'][0]['status']['desc'] = "Inactive";
							$willing = "J";
						} else {
							$source['user_role'][0]['status']['value'] = "ACTIVE";
							$source['user_role'][0]['status']['desc'] = "Active";
							$willing = $_POST['willingcheck'];
						}
						$source['user_role'][0]['scope']['value'] = "46KTH_INST";
						$source['user_role'][0]['scope']['desc'] = "KTH Library";
						$source['user_role'][0]['role_type']['value'] = "200";
						$source['user_role'][0]['role_type']['desc'] = "Patron";
						
						$pinmessage = "";
						if(!empty($_POST['almapinnumber'])) {
							if($_POST['almapinnumber'] != "") {
								$source['pin_number'] = $_POST['almapinnumber'];
								if ($language == 'sv') {
									$pinmessage = "Din PIN Code är: " . $_POST['almapinnumber'];
								} else {
									$pinmessage = "Your PIN Code is: " . $_POST['almapinnumber'];
								}
							}
						}
						
						$usergroup = $source['user_group']['value'];
						$fullname = $source['full_name'];
						
						date_default_timezone_set("Europe/Stockholm");
						$note_text = "Låntagarrollen aktiverades, via webben, " . date('Y-m-d H:i:s');
						$source = addusernote($source,$note_text);
						
						if (!empty($willing)) { 
							if($willing == "J") {
								$source = json_encode($source);
								$response = updateuser($source, $_SESSION['kth_id']);
								$almaresponse = checkifAlmaerror($response,"xml");
								$jsonalmaresponse = json_decode($almaresponse);
								if ($jsonalmaresponse->result == "Success") {
									$result = "";
									$sendmailmessage = "";
									$mailmessage  = "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
									if ($_POST['isactivepatron'] != "true") {
										if ($language == 'sv') {
											$subject  = "Välkommen till KTH Biblioteket!";
											$mailmessage .= "<div>" ;
											$mailmessage .= "<p>Hej,</p>";
											$mailmessage .= "</div>" ;
											$mailmessage .= "<div>" ;
											if ($usergroup == "10") { 
												$mailmessage .= "<div><p>Ditt konto (" . $user_primary_id . ") är nu aktiverat.</p></div>
																<div><p>Du kan nu reservera och låna böcker samt beställa artikelkopior och fjärrlån.</p></div>
																<div>For more information about our services, please visit our website<a href=\"https://www.kth.se/biblioteket\"> kth.se/biblioteket</a></div>";
												$mailmessage .= "</div>";
											}
											if ($usergroup == "20") { 
												$mailmessage .= "<div><p>Ditt konto (" . $user_primary_id . ") är nu aktiverat.</p></div>"
																. $pinmessage .
																"
																<p>Du kan nu reservera och låna böcker samt beställa artikelkopior och fjärrlån genom KTH Biblioteket. Var vänlig ta med ditt ID eller bibliotekskort om du ska låna böcker på biblioteket. Bibliotekskort kvitterar du ut i informationsdisken mot uppvisande av legitimation.</p>
																<div>Du kan söka bland vårt tryckta och elektroniska material i sökverktyget Primo eller i någon av våra databaser och söktjänster. Genom att logga in på ditt KTH.se-konto kan du nå elektroniska resurser även utanför campus.</div>
																<div><a href=\"https://kth-primo.hosted.exlibrisgroup.com/primo-explore/search?vid=46KTH_VU1&lang=sv_SE\">Sök material i Primo</a></div>
																<div><a href=\"https://www.kth.se/kthb/sokverktyg/databaser-och-soktjanster-1.546373\">Våra databaser och söktjänster</a></div>
																</br>
																<div><a href=\"https://www.kth.se/en/biblioteket/anvanda-biblioteket/oppettider-kontakt\">Se våra öppettider</a></div>
																</br>
																<div>På biblioteken finns gott om studieplatser, såväl tysta läsplatser som datorer och utrymmen där man får sitta och prata. Du kan även boka något av våra grupprum.</div>
																<div><a href=\"https://www.kth.se/en/biblioteket/anvanda-biblioteket/studera-i-biblioteket-1.953572\">Boka grupprum</a></div>
																</br>
																<div>Ska du skriva ett arbete och behöver vägledning i informationssökning, databaser, referenshantering eller publiceringsfrågor?</div>
																<div><a href=\"https://www.kth.se/en/biblioteket/soka-vardera/boka-handledning-1.853064\">Boka handledning</a></div>";
												$mailmessage .= "</div>";
											}
										} else {
											$subject  = "Welcome to KTH Library!";
											$mailmessage .= "<div>" ;
											$mailmessage .= "<p>Hi,</p>";
											$mailmessage .= "</div>" ;
											$mailmessage .= "<div>" ;
											if ($usergroup == "10") { 
												$mailmessage .= "<div><p>Your account (" . $user_primary_id . ") has now been activated.</p></div>
																<div><p>You are now able to borrow books and request articles or interlibrary loans.</p></div>
																<div>For more information about our services, please visit our website<a href=\"https://www.kth.se/biblioteket\"> kth.se/biblioteket</a></div>";
												$mailmessage .= "</div>";
											}
											if ($usergroup == "20") { 
												$mailmessage .= "<p>Your account (" . $user_primary_id . ") has now been activated.</p>"
																. $pinmessage .
																"
																<p>You can now borrow or request books, article copies and interlibrary loans. Please bring your ID or library card if you want to borrow materials from the library. You can collect your library card at the information desk if you show your ID.</p>
																<div>You can search our printed and electronic materials in Primo or in one of our databases or search tools. If you log in to your KTH account you can access our electronic resources outside of campus.</div>
																<div><a href=\"https://kth-primo.hosted.exlibrisgroup.com/primo-explore/search?vid=46KTH_VU1&lang=en_US\">Search in Primo</a></div>
																<div><a href=\"https://www.kth.se/en/biblioteket/sokverktyg/databaser-och-soktjanster-1.546373\">Databases and search tools</a></div>
																</br>
																<div><a href=\"https://www.kth.se/en/biblioteket/anvanda-biblioteket/oppettider-kontakt\">See our opening hours</a></div>
																</br>
																<div>In the library you’ll find plenty of study spaces, both quiet study areas as well as computers and spaces where talking is allowed. You can also book a group study room.</div>
																<div><a href=\"https://www.kth.se/en/biblioteket/anvanda-biblioteket/studera-i-biblioteket-1.953572\">Book a group study room</a></div>
																</br>
																<div>Are you writing a paper and need guidance in information retrieval, databases, reference management or questions related to publishing?</div>
																<div><a href=\"https://www.kth.se/en/biblioteket/soka-vardera/boka-handledning-1.853064\">Book a tutorial</a></div>";
												$mailmessage .= "</div>";
											}
										}
									} else {
										if ($language == 'sv') {
											$subject  = "Avaktivering av bibliotekskonto";
											$mailmessage .= "<div>" ;
											$mailmessage .= "<p>Hej $fullname</p>";
											$mailmessage .= "</div>" ;
											$mailmessage .= "<div>" ;
											$mailmessage .= "<p>Ditt konto (" . $user_primary_id . ") är nu inte längre aktivt.</p>";
											$mailmessage .= "</div>" ;
										} else {
											$subject  = "Deactivation of library account";
											$mailmessage .= "<div>" ;
											$mailmessage .= "<p>Hi $fullname</p>";
											$mailmessage .= "</div>" ;
											$mailmessage .= "<div>" ;
											$mailmessage .= "<p>Your account (" . $user_primary_id . ") is no longer active.</p>";
											$mailmessage .= "</div>" ;
										}
									}
									$mailmessage .= "<div>";
									if ($language == 'sv') {
										$mailmessage .= "<p>Välkommen till KTH Biblioteket!</p>";
									} else {
										$mailmessage .= "<p>Welcome to KTH Library!</p>";
									}
									$mailmessage .= "</div>" ;
									$mailresponse = sendconfirmemail($_SESSION['kth_id'],$epostadress,$fullname,$mailmessage,$subject);
									if ($mailresponse=="Success") {
										$mailresult = "Success";
									} else {
										$mailresult = "Error";
										if ($language == 'sv') {
											$sendmailmessage = "Det gick dock inte att skicka ett bekräftelsemail! <br/><br/>Felmeddelande: " . $mailresponse;
										} else {
											$sendmailmessage = "We could not send you a confirmation email!. <br/><br/>Error message: " . $mailresponse;
										}
									}
									$result = "Success";
									if ($language == 'sv') {
										if ($_POST['isactivepatron'] == "true") {
											$message = "Konto Avaktiverat, " . $sendmailmessage;
											$active = "false";
										} else {
											$message = "Ditt låntagarkonto är aktiverat, ". $sendmailmessage . "<br/><br/> Välkommen till KTH Biblioteket";
											$active = "true";
										}
									} else {
										if ($_POST['isactivepatron'] == "true") {
											$message = "Deactivated, " . $sendmailmessage;
											$active = "false";
										} else {
											$message = "Your library card is activated, ". $sendmailmessage . "<br/><br/> Welcome to KTH Library";
											$active = "true";
										}
									}
									$data = array(
									  "result"  => $result,
									  "mailresult" => $mailresult,
									  "message" => $message,
									  "active"	=> $active
									);
									$json_data = json_encode($data);
									$almaresponse=$json_data;
								} else {
								}
							} 
						} else {
							$result = "Error";
							if ($language == 'sv') {
								$message = "Nödvändiga fält inte ifyllda!";
							} else {
								$message = "Required fields not completed";
							}
							$data = array(
							  "result"  => $result,
							  "message" => $message
							);
							$json_data = json_encode($data);
							$almaresponse=$json_data;
						}
					}
					print $almaresponse;
				}
			} else {
				$result = "Error";
				if ($language == 'sv') {
					$message = "Fel!";
				} else {
					$message = "Error!";
				}
				$data = array(
				  "result"  => $result,
				  "message" => $message
				);
				$json_data = json_encode($data);
				print $json_data;
			}
		}
	}
}
?>