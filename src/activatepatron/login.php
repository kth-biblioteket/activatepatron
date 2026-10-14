<?php
require_once "config.php.inc";
date_default_timezone_set("Europe/Stockholm");

session_start();

//210519 OpenID Connect framework(myits)
require_once($_SERVER['DOCUMENT_ROOT'] .  '/myits/vendor/autoload.php');

use Its\Sso\OpenIDConnectClient;
use Its\Sso\OpenIDConnectClientException;

//Funktioner för att läsa JWTtokens från KTH-login(id_token)
function base64url_decode($base64url) {
	return base64_decode(b64url2b64($base64url));
  }
  
function b64url2b64($base64url) {
	$padding = strlen($base64url) % 4;
	if ($padding > 0) {
		$base64url .= str_repeat("=", 4 - $padding);
	}
	return strtr($base64url, '-_', '+/');
}

function decodeJWT($jwt, $section = 0) {
	$parts = explode(".", $jwt);
	return json_decode(base64url_decode($parts[$section]));
}

try {
	$oidc = new OpenIDConnectClient(
		$kth_auth_endpoint,
		$kth_client_id,
		$kth_client_secret
	);

	$oidc->addScope('openid email profile');
  
	// remove this if in production mode
	$oidc->setVerifyHost(false);
	$oidc->setVerifyPeer(false);

	// redirect_uri måste vara exakt den som är registrerad hos ADFS, så inga parametrar här.
	// language sparas i sessionen vid första anropet och läses därifrån vid retur från ADFS.
	if (!isset($_GET["code"])) {
		$_SESSION['login_language'] = (isset($_GET["language"]) && $_GET["language"] === "swedish") ? "swedish" : "english";
	}
	$language = isset($_SESSION['login_language']) ? $_SESSION['login_language'] : "english";

	//redirect tillbaks till denna sida!
	$oidc->setRedirectURL("https://" . $_SERVER['HTTP_HOST'] . "/activatepatron/login.php");

	//Skickar vidare till login på KTH om användaren inte redan är inloggad
	$oidc->authenticate();
	//Vid redan inloggad exekveras koden nedan 

	//id_token innehåller den användarinfo tjänsten prenumererar på(här används kthid)
	$_SESSION['id_token'] = $oidc->getIdToken();
	$userinfo = decodeJWT($_SESSION['id_token'], 1);
	$_SESSION['kth_id'] = $userinfo->kthid;

	//finns ett kthid så startas applikationen
	if(isset($_SESSION['kth_id']) && $_SESSION['kth_id'] != "") {
		$userid = $_SESSION['kth_id'];
		header("location: /activatepatron/index.php?language=$language");
	}
  
} catch (OpenIDConnectClientException $e) {
	echo $e->getMessage();
}
?>
