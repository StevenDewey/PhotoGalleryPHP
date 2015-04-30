<?php
	
	session_start();

	if ($_SESSION["login"] == true) {
		header("location: gallery.php");
	}
	if (isset($_POST["logout"])) {
		$_SESSION["login"] == false;
	}
	if (isset($_POST["username"]) && isset($_POST["password"])) {
		$hashed_password = hash('whirlpool',$_POST["password"]."salt");

		$database = new mysqli("localhost","tardissh_sdew","3701!","tardissh_dewey");
		$query_onePostItem = "
				SELECT 
					username, password
				FROM
					users
				WHERE
					username = ? AND password = ?
					
			";

			# If the query from above prepares properly, execute it
			# Else, show an error message
			if ( $users = $database->prepare($query_onePostItem) ):
				$users->bind_param(
				 	'ss',
				 	$_POST["username"], $hashed_password
				 					 );
				$users->execute();
				$users->bind_result($username,$password);
				 
				 while( $users->fetch() ):
				 	$checkUsername = $username;
				 	$checkPassword = $password;
				 endwhile;
								 
				 $users->close();
				 
			else:
				die ( "<p class='error'>There was a problem executing your query</p>" );
			endif;


		if ($_POST["username"] == $checkUsername && $hashed_password == $password) {
			$_SESSION["login"] = true;
			header("location: gallery.php");
		}
	}
?><!doctype html>
<html>

	<head>
		<title>Gallery</title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>

	<body id="indexBody">
		<!--<form  action="index.php" method="post">
		<input type="text" name="logout" value="true" hidden>
		<input type="submit" value="Logout">
		</form>-->
		<div >
			<form class="centerIndex" action="index.php" method="post">
			Username:<br>
			<input type="text" name="username" placeholder="Username">
			<br>
			Password:<br>
			<input type="password" name="password" placeholder="Password">
			<br><br>
			<input type="submit" value="Submit">
			</form>
		</div>
	</body>

</html>
