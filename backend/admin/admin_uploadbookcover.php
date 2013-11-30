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

// probably not extensible
$config_allowedExts = array("gif", "jpeg", "jpg", "png");
$config_allowedMimetypes = array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png");
// maxImageSize can be changed somewhere at sometime someday
$config_maxImageSize = 1000000;
// oh fuck break it into two shits
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if (in_array($_FILES['file']['type'], $config_allowedMimetypes)
&& ($_FILES['file']['size'] < $config_maxImageSize)
&& in_array($extension, $config_allowedExts)) {
	if ($_FILES['file']['error'] > 0) {
		header("HTTP/1.0 400 Bad Request");
		return;
	} else {
		$userid = $_SESSION['userID'];
		$filename = $book->getTitle() . $userid . "." . $extension;
		// if there was a previous file delete it
		if ($book->getBookCover() != "" && file_exists(dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename)) { 
			unlink(dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename);
		}
		if (file_exists(dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename)) {
			move_uploaded_file($_FILES['file']['tmp_name'],
			dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename);
			// associate book file name with book in database
			$book->setBookCover($filename);
			$db->update("UPDATE `library`.`books` SET `BookCover` = '" . $db->escapeString($filename) ."' WHERE `UID` = " . $id);
			header("HTTP/1.0 200 OK");
			return;
		} else {
			move_uploaded_file($_FILES['file']['tmp_name'],
			dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename);
			// associate book file name with book in database
			$book->setBookCover($_FILES['file']['name']);
			$db->update("UPDATE `library`.`books` SET `BookCover` = '" . $db->escapeString($filename) ."' WHERE `UID` = " . $id);
			header("HTTP/1.0 200 OK");
		}
	}
} else {
	header("HTTP/1.0 400 Bad Request");
}
?>
