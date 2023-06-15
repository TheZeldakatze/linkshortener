<?php
include "config.php";

/* create a session */
session_start();

/* login if the session is not authenticated */
if(!isset($_SESSION['auth']) || !$_SESSION['auth']) {
	require __DIR__.'/internal/openid.php';
	runOpenID();

	if($OIDC_sub == null)
		die("Authentication failed!");
	else {
		/* write names and stuff into the session */
		$_SESSION['auth']  = true;
		$_SESSION['sub']   = $OIDC_sub;
		$_SESSION['name']  = $OIDC_name;
		$_SESSION['token'] = $OIDC_id_token;
	}
}

/* connect to the database */
include("../connect.php");
//print("Hello, ".$_SESSION['name']);

/* create a link */
$displayMSG = "";
if(isset($_GET['create'])) {
	if(isset($_GET['url'])) {
		/* get a random token */
		$name = "";
		if(isset($_GET['randomName'])) {
			$found = false;
			while(!$found) {
				$name = uniqid("", true);
				$stmt = mysqli_prepare($mysqli, "SELECT id FROM links WHERE short=?");
                                mysqli_stmt_bind_param($stmt, "s", $name);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                if(mysqli_num_rows($result) == 0)
					$found = true;
			}
		} else { /* check if a token like that exists already */
			if(isset($_GET['name']) && $_GET['name'] != "") {
				$stmt = mysqli_prepare($mysqli, "SELECT id FROM links WHERE short=?");
				mysqli_stmt_bind_param($stmt, "s", $_GET['name']);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				if(mysqli_num_rows($result) == 0)
					$name = $_GET['name'];
				else
					$displayMSG = "Name already used!";
				mysqli_stmt_close($stmt);
			} else
				$displayMSG = "No name specified!";
		}

		/* if a name was found, continue */
		if($name != "") {
			$stmt = mysqli_prepare($mysqli, "INSERT INTO `links` (`id`, `short`, `url`, `click_count`, `creatorName`) VALUES (NULL, ?, ?, 0, ?)");
			mysqli_stmt_bind_param($stmt, "sss", $name, $_GET['url'], $_SESSION['name']);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
			$displayMSG = "Link created!<br>URL: <a href='".$REDIRECT_BASE.$name."'>".$REDIRECT_BASE.$name."</a>";
		}
	} else
		$displayMSG = "No URL specified";
}

if(isset($_GET['delete']) && isset($_GET['linkID'])) {
	$stmt = mysqli_prepare($mysqli, "DELETE FROM links WHERE `id`=?");
	mysqli_stmt_bind_param($stmt, "i", $_GET['linkID']);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	$displayMSG = "Deleted link";
}
?>


<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<h1>Link Shortener settings</h1>
		<?php print("Hello, ".$_SESSION['name']); ?><br>
		<?php
		if($displayMSG != "")
			print("<font color='red'>Last Message: ".$displayMSG."<br></font>");
		?>
		<a href="logout.php">Logout</a><br>
		<hr>
		<form>
			<label for="url">URL: </label>
			<input type="text" id="url" name="url" placeholder="URL" maxlength="512" value="<?php if(isset($_GET['url'])) print($_GET['url']); ?>"><br>
			<input type="checkbox" name="randomName" id="randomName">
			<label for="randomName">Random Name?</label><br>
			<label for="name">Custom Name:</label>
			<input type="text" id="name" name="name" value="<?php if(isset($_GET['name'])) print($_GET['name']); ?>"><br>
			<input type="submit" value="create link" name="create"><br>
		</form>
		<hr>
		<table border="1" width="100%" style="table-layout: fixed; word-wrap: break-word;">
			<tr><th>ID</th><th>URL</th><th>Token</th><th>Use count</th><th>creator</th><th>Delete</th></tr>
			<?php
			$query = mysqli_query($mysqli, "SELECT * FROM links");
			while($row = mysqli_fetch_assoc($query)) {
				print("<tr><td>".$row['id']."</td><td>".$row['url']."</td><td>".
					"<a href='".$REDIRECT_BASE.$row['short']."'>".$row['short']."</a></td><td>".$row['click_count']."</td><td>".
					$row['creatorName']."</td><td>");
				print("<form><input type='hidden' name='linkID' value='".$row['id']."'><input type='submit' value='delete' name='delete'></form></td></tr>");
			}
			?>
		</table>
	</body>
</html>
