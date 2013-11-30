<?php
include "../include/session.php";
include "../include/book.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

if (isset($_POST['id'])) {
	$db = new phpLibraryDatabase();
	$id = $db->escapeString($_POST['id']);
	$query = $db->query("SELECT * FROM `books` WHERE `UID` = " . $id);
	if ($query->getRowCount() == 0) {
		header("HTTP/1.0 400 Bad Request");
		return;
	} else {
		$row = $query->getRows();
		$book = Book::bookFromRow($row[0]);
	}
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// maxImageSize can be changed somewhere at sometime someday
$config_maxPDFSize = 1000000;
// oh fuck break it into two shits
$temp = explode(".", $_FILES["pdf"]["name"]);
$extension = end($temp);

if ($_FILES['pdf']['type'] == "application/pdf"
&& $_FILES['pdf']['size'] < $config_maxPDFSize
&& $extension == "pdf") {
	if ($_FILES['pdf']['error'] > 0) {
		header("HTTP/1.0 400 Bad Request");
		return;
	} else {
		$filename = $book->getTitle() . time() . "." . $extension;
		// if there was a previous file delete it
		if ($book->getBookCover() != "" && file_exists(dirname(__FILE__) . "/../../uploads/bookpdfs/" . $filename)) { 
			unlink(dirname(__FILE__) . "/../../uploads/bookpdfs/" . $filename);
		}
		move_uploaded_file($_FILES['pdf']['tmp_name'],
		dirname(__FILE__) . "/../../uploads/bookpdfs/" . $filename);
		// associate book file name with book in database
		$book->setBookCover($_FILES['pdf']['name']);
		$db->update("UPDATE `library`.`books` SET `BookPDF` = '" . $db->escapeString($filename) ."' WHERE `UID` = " . $id);
		header("HTTP/1.0 200 OK");
	}
} else {
	header("HTTP/1.0 400 Bad Request");
}
?>
