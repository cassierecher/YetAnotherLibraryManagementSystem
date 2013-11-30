<?php
//----------------------------------------------------------------------------------
// admin_createadmin.php is a script that creates an admin in the database
// it works by taking post data from a form and translates it into a user on mysql
// the post data is as following:
// u => is a string containing username
// p => is a string containing password
// The script will then try to create a user by that name in database
// if there exists a user in the database with the name input in u,
// document returns value false
// else document returns true
//-----------------------------------------------------------------------------------
include "../include/session.php";
include "../include/user.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

$db = new phpLibraryDatabase();
$admin = new User();
$admin->setAdmin(TRUE);

if (isset($_POST['u'])) {
	$username = $db->escapeString($_POST['u']);
	$admin->setUsername($username);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}
if (isset($_POST['p'])) {
	$admin->hashPassword($_POST['p']);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$sql = "SELECT * FROM `users` WHERE `Username` = '". $admin->getUsername() ."'";

$query = $db->query($sql);

if($query->getRowCount() != 0) {
	echo json_encode(FALSE);
	return;
}

$sql = "INSERT INTO `library`.`users` 
		(`admin`, `Username`, `CID`, `Password`, `Salt`)
		VALUES ('". $admin->getAdmin() ."', 
		'". $admin->getUsername() ."', 
		'". $admin->getCID() ."', 
		'". $admin->getHash() ."', 
		'". $admin->getSalt() ."');";
$db->update($sql);
echo json_encode(TRUE);
header("HTTP/1.0 201 Created");
?>