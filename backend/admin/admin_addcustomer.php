<?php
//-------------------------------------------------------------------------------------
// admin_addbook.php: adds a customer to the database
// operation: admin_addbook.php?f=_FIRSTNAME_&l=_LASTNAME_
// 				where 	_FIRSTNAME is a string with no quotes, this is a required field
//						_LASTNAME_ is a string with no quotes, this is a required field
//-------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/customer.php";
include "../include/user.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

$db = new phpLibraryDatabase();
$customer = new Customer();

if (isset($_POST['f'])) {
	$First_Name = $db->escapeString($_POST['f']);
	$customer->setFirst_Name($First_Name);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

if (isset($_POST['l'])) {
	$Last_Name = $db->escapeString($_POST['l']);
	$customer->setLast_Name($Last_Name);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

if (isset($_POST['p'])) {
	$userPassword = $_POST['p'];
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$sql = "SELECT * FROM `customers` WHERE `First_Name` LIKE '" . $customer->getFirst_Name() . "' AND `Last_Name` LIKE '" . $customer->getLast_Name() . "'";

$query = $db->query($sql);

if ($query->getRowCount() == 0) {
	// here we'll construct the update string from our book model
	$sql = "INSERT INTO `customers` 
				(`First_Name`, `Last_Name`, `Creation_Date`, `Book_List`)
				VALUES ('" . $customer->getFirst_Name() ."',
				'" . $customer->getLast_Name() . "',
				'" . $customer->getCreation_Date() . "',
				'" . json_encode($customer->getBook_List()) . "')";
	$query = $db->update($sql);
	
	$query = $db->query("SELECT `UID` FROM `customers` WHERE `First_Name` = '" . $customer->getFirst_Name() . "' AND `Last_Name` = '" . $customer->getLast_Name() . "';");
	
	$rows = $query->getRows();
	
	// now we need to make a user :O
	$user = new User();
	
	$user->setUsername(strtolower($customer->getFirst_Name() . $customer->getLast_Name()));
	
	$user->hashPassword($userPassword);
	
	$user->setCID($rows[0]['UID']);
	
	$sql = "INSERT INTO `users` (`admin`, `Username`, `CID`, `Password`, `Salt`)
			VALUES ('" . $user->getAdmin() . "',
					'". $user->getUsername() . "',
					'". $user->getCID() ."',
					'". $user->getHash() ."',
					'". $user->getSalt() ."')";
	$query = $db->update($sql);
	
	header("HTTP/1.0 201 Created");
} else {
	header("HTTP/1.0 400 Bad Request");
}
?>
