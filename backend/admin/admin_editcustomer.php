<?php
//-------------------------------------------------------------------------------------
// admin_editcustomer.php: this script takes the id of a customer in the database, and 
// 						modifies that entry in the database
// operation: admin_editcustomer.php?id=_UID_&f=_FIRST_NAME_&l=_LAST_NAME_
// 				where 	_UID_ is an unsigned integer, this is a required field
//						_FIRST_NAME_ is a string with no quotes
//						_LAST_NAME_ is a string with no quotes
//						One can update any single field or all fields at once.
//-------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/customer.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

// need an id for the database query.
if (!isset($_POST['id'])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// nullify all these values, so we can construct an update string out of the $_POST data
// excluding id
$id = $_POST['id'];
// do we even need to query the database for an update?
$update = 0;

$db = new phpLibraryDatabase();
$id = $db->escapeString($id);
// potentially check to make sure id is in fact an unsigned integer.

//grab user from database
$query = $db->query("SELECT * FROM `customers` WHERE `UID` = " . $id);


// does this customer even exist?
if ($query->getRowCount() == 0) {
	header("HTTP/1.0 400 Bad Request");
	return; // the customer doesn't exist. No need to query the DB.
}
$rows = $query->getRows();

// sick it's a reperesntation of the customer in the database :O
$customer = Customer::customerFromRow($rows[0]);

if (isset($_POST['f'])) {
	$first_name = $db->escapeString($_POST['f']);
	$customer->setFirst_Name($first_name);
	$update = 1; // yes we do update
}

if (isset($_POST['l'])) {
	$last_name = $db->escapeString($_POST['l']);
	$customer->setLast_Name($last_name);
	$update = 1; // yes we do update
}

// here we'll construct the update string from our customer model
if($update) {
	$sql = "UPDATE `library`.`customers`
				SET `First_Name` = '" . $customer->getFirst_Name() ."',
				`Last_Name` = '" . $customer->getLast_Name() ."'				
				WHERE `customers`.`UID`='" . $id . "'";
	$query = $db->update($sql);
	header("HTTP/1.0 204 No Content");
}
?>
