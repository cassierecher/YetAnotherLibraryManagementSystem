<?php
//---------------------------------------------------------------------------------------
// admin_deletecustomer.php: deletes a customer from the database
// operation: admin_deletebook.php?id=_UID_
//				where _UID_ is an unsigned integer representing a customer in the database
//---------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/customer.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

if (!isset($_POST['id'])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// connect to the database
	$db = new phpLibraryDatabase();
	$id = $db->escapeString($_POST['id']);
	$queryData = $db->query("SELECT * FROM  `customers` WHERE  `UID` =  '" . $id . "'");

	// was there a row returned?	
	if($queryData->getRowCount() == 0) {
		header("HTTP/1.0 401 Bad Request");
		return FALSE;
	}

	$rows = $queryData->getRows();
		
	$customer = Customer::customerFromRow($rows[0]);
	
	$booklist = $customer->getBook_List();

	if(empty($booklist)){
		$query = $db->update("DELETE FROM `customers` WHERE `UID` = " . $id);
		$query = $db->update("DELETE FROM `users` WHERE `CID` = " . $id);
		unset($id);
		unset($db);
		unset($query);
		header("HTTP/1.0 204 No Content");

	} else {
		//return an error message
		header("HTTP/1.0 401 Bad Request");
		return;
	}

?>
