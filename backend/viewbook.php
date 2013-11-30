<?php
//---------------------------------------------------------------------------------------
// viewbook.php : returns json object containing a single book object from the database.
// operation: viewbook.php?id=_IDHERE_
// 				where _IDHERE_ is an integer representing the UID of any book in the db.
//---------------------------------------------------------------------------------------
include_once "./include/database.php";
include "./include/book.php";

if (!isset($_GET['id'])) {
	echo json_encode(array());
	header("HTTP/1.0 400 Bad Request");
	return;
}


$db = new phpLibraryDatabase();

$id = $_GET['id'];
$id = $db->escapeString($id);


$query = $db->query("SELECT * FROM `books` WHERE `UID` = " . $id);

if ($query->getRowCount() == 0) {
	echo json_encode(array());
	return;
}

$rows = $query->getRows();

$book = Book::bookFromRow($rows[0]);

$book = [ $book ];

header("HTTP/1.0 200 OK");
echo json_encode($book);
?>