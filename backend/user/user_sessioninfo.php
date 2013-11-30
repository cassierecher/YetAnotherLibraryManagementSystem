<?php
include "../include/session.php";
include "../include/user.php";

if (!loginCheck()) {
	echo json_encode(array());;
	return;
}

$db = new phpLibraryDatabase();
$uname = $_SESSION['username'];

$db->escapeString($uname);

$query = $db->query("SELECT * FROM `users` WHERE `Username` = '".$uname."'");

if ($query->getRowCount() == 0) {
	echo json_encode(array());
	return;
}

$userRow = $query->getRows();

$user = User::userFromRow($userRow[0]);

echo json_encode(array($user));
?>