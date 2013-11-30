<?php
//---------------------------------------------------------------------------------------
// admin_deletebook.php: deletes a book from the database
// operation: admin_deletebook.php?id=_UID_
//				where _UID_ is an unsigned integer representing a book in the database
//---------------------------------------------------------------------------------------
include "../include/session.php";
if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

if (!isset($_POST['id'])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// set up deletion
$id = $_POST['id'];
$db = new phpLibraryDatabase();
$id = $db->escapeString($id);

// is this book checked out?
$query = $db->query("SELECT `CID`, `BookCover` FROM `books` WHERE `UID` = " . $id);
$rows = $query->getRows();
if($rows[0]['CID'] != 0) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

if (file_exists(dirname(__FILE__) . "/../../uploads/bookcovers/" . $rows[0]['BookCover'])) {
	unlink(dirname(__FILE__) . "/../../uploads/bookcovers/" . $rows[0]['BookCover']);
}
if (file_exists(dirname(__FILE__) . "/../../uploads/bookpdfs/" . $rows[0]['BookPDF'])) {
	unlink(dirname(__FILE__) . "/../../uploads/bookpdfs/" . $rows[0]['BookPDF']);
}
// delete that sucka
$query = $db->update("DELETE FROM `books` WHERE `UID` = " . $id);

//set the success header
header("HTTP/1.0 204 No Content");


unset($id);
unset($db);
unset($query);

?>
