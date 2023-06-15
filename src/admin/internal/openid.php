<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/phpseclib/phpseclib");
require __DIR__.'/phpseclib/phpseclib/bootstrap.php';
require __DIR__.'/phpseclib/phpseclib/Crypt/Random.php';
require __DIR__.'/phpseclib/phpseclib/Crypt/Hash.php';
require __DIR__.'/phpseclib/phpseclib/Math/BigInteger.php';
require __DIR__.'/phpseclib/phpseclib/Crypt/RSA.php';
require 'OpenIDConnectClient.php';
use Jumbojett\OpenIDConnectClient;

$OIDC_info = null;
$OIDC_sub = null;
$OIDC_name = null;
$OIDC_id_token = null;
$oidc = null;

function initOpenID() {
	global $oidc;
	global $CONFIG_OIDC_ADDRESS;
	global $CONFIG_OIDC_APPLICATION;
	global $CONFIG_OIDC_SECRET;
	global $CONFIG_OIDC_USE_CERT;
	$oidc = new OpenIDConnectClient($CONFIG_OIDC_ADDRESS, $CONFIG_OIDC_APPLICATION, $CONFIG_OIDC_SECRET);
	
	if($CONFIG_OIDC_USE_CERT) {
		global $CONFIG_OIDC_CERT_PATH;
		//$oidc->setCertPath($CONFIG_OIDC_CERT_PATH);
	} else {
		$oidc->setVerifyHost(false);
		$oidc->setVerifyPeer(false);
	}
}

function runOpenID() {
	global $oidc;
	global $WEBSITE_ADDRESS;
	initOpenID();
	
	$oidc->addScope("email");
	$oidc->setRedirectURL($WEBSITE_ADDRESS."/admin/index.php");
	$oidc->authenticate();
	
	global $OIDC_info;
	global $OIDC_sub;
	global $OIDC_name;
	global $OIDC_id_token;
	$OIDC_info = $oidc->requestUserInfo();
	$p = 'sub'; $OIDC_sub = $OIDC_info->$p;
	$p = 'preferred_username'; $OIDC_name = $OIDC_info->$p;
	$OIDC_id_token = $oidc->getIdToken();
}

function oidcLogout() {
	global $oidc;
	global $OIDC_id_token;
	global $WEBSITE_ADDRESS;
	initOpenID();
	
	$oidc->signOut($OIDC_id_token, $WEBSITE_ADDRESS . "/admin/logout.php");
}
