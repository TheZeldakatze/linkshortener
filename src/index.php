<?php

function die_link_broken() {
	?>
	<!DOCTYPE html>
	<html>
	<head>
	</head>
	<body>
		<h1>Link Dead!</h1>
	</body>
	</html>
	<?php
	die();
}

/* was a token specified? */
if(!isset($_GET['t']))
	die_link_broken();


$token = $_GET['t'];

/* prepare a statement */
include 'connect.php';
$stmt = mysqli_prepare($mysqli, "SELECT * FROM links WHERE short=?");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
/* get the statement */
$count = 0;
while($row = mysqli_fetch_assoc($result)) {
	$linkID  = $row['id'];
	$linkURL = $row['url'];
	$count++;
}

if($count == 0)
	die_link_broken();
else
if($count > 1)
	die("Link exists more than once");

/* send a redirect */
header("HTTP/1.1 301 Moved Permanently");
header('Location: '.$linkURL);

/* close the statement */
mysqli_stmt_close($stmt);

/* increment the use counter by one */
$stmt = mysqli_prepare($mysqli, "UPDATE links SET click_count = click_count + 1 WHERE short=?");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($mysqli);
