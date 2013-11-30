<?php
//---------------------------------------------------------------------------------------
// admin_viewcustomer.php: returns authenticated users a json object containing 1
//							customer.
// operation: 	admin_viewcustomer.php?id=_UID_
//				where _UID_ is an unsigned integer, this is a required field.
//---------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/customer.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

if(!isset($_GET['id'])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$db = new phpLibraryDatabase();
$id = $db->escapeString($_GET['id']);
$query = $db->query("SELECT * FROM `customers` WHERE `UID` = " . $id);
$out = array();

if ($query->getRowCount() != 0) {
	$rows = $query->getRows();
	$customer = Customer::customerFromRow($rows[0]);
	$out = [$customer];
}


echo json_encode($out);

?>
