<?php
include "../include/session.php";
include "../include/user.php";
include "../include/book.php";

if (!loginCheck()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

$admin = false;
if (isAdmin()) $admin = true;

$db = new phpLibraryDatabase();

if(isset($_GET['c']) && $admin) {
	$cid = $db->escapeString($_GET['c']);
} else if (!$admin) {
	$cid = $_SESSION['userID'];
} else {
	header("HTTP/1.0 Bad Request");
	return;
}

$query = $db->query("SELECT * FROM `users` WHERE `UID` = ". $cid);
if ($query->getRowCount() == 0) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$rows = $query->getRows();

$user = User::userFromRow($rows[0]);

$booklist = $user->getBook_List();

if (empty($booklist)) {
	echo json_encode(array());
	return;
}

$sql = "SELECT * FROM `books` WHERE";
?>