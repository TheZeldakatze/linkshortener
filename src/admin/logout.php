<?php
include "config.php";
session_start();
$_SESSION["auth"] = false;

// log out from openID
include __DIR__ . '/internal/openid.php';
if(isset($_SESSION['token'])) {
	$OIDC_id_token = $_SESSION['token'];
	oidcLogout();
}
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
	<h1>logout Successful!</h1>
</body>
</html>
