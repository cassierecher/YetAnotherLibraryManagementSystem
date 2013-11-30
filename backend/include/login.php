<?php
include "../include/session.php";
if (loginCheck()) {
	logout();
}

$loggedIn = login(strtolower($_POST['username']), $_POST['password']);
if(!$loggedIn) {
	header("HTTP/1.0 401 Unauthorized");
	return;
} else {
	if (isAdmin()) {
		echo "admin";
		return;
	} else {
		echo "user";
		return;
	}
}

?>