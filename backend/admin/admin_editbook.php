<?php
//-------------------------------------------------------------------------------------
// admin_editbook.php: this script takes the id of a book in the database, and 
// 						modifies that entry in the database
// operation: admin_editbook.php?id=_UID_&t=_TITLE_&p=_PUBLISHER_&i=_ISSUE_DATE_&r=_RETURN_DATE_&a=_AUTHOR_&c=_CID_
// 				where 	_UID_ is an unsigned integer, this is the only required field.
//						_TITLE_ is a string with no quotes
//						_PUBLISHER_ is a string with no quotes
//						_ISSUE_DATE_ is in the format yyyy-mm-dd
//						_RETURN_DATE_ is in the format yyyy-mm-dd
//						_AUTHOR_ is a string with no quotes
//						_CID_ is an unsigned integer representing customer id
//						only the id is a required field.
//						One can update any single field or all fields at once.
//-------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/book.php";

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
$update = 0;

$db = new phpLibraryDatabase();
$id = $db->escapeString($id);
// potentially check to make sure id is in fact an unsigned integer.

$query = $db->query("SELECT * FROM `books` WHERE `UID` = " . $id);
// does this book even exist?
if ($query->getRowCount() == 0) {
	header("HTTP/1.0 400 Bad Request");
	return; // the book doesn't exist. No need to query the DB.
}
$rows = $query->getRows();

$book = Book::bookFromRow($rows[0]);

if (isset($_POST['t']) && $_POST['t'] != "") {
	$title = $db->escapeString($_POST['t']);
	$book->setTitle($title);
	$update = 1;
}

if (isset($_POST['p']) && $_POST['p'] != "") {
	$publisher = $db->escapeString($_POST['p']);
	$book->setPublisher($publisher);
	$update = 1;
}

if (isset($_POST['i']) && $_POST['i'] != "") {
	$matches = "";
	$issue_date = $db->escapeString($_POST['i']);
	if(preg_match("/\d{4}-\d{2}-\d{2}/", $issue_date, $matches)) {
		$book->setIssue_Date($issue_date);
		$update = 1;
	}
}

if (isset($_POST['r']) && $_POST['r'] != "") {
	$matches = "";
	$return_date = $db->escapeString($_POST['r']);
	if(preg_match("/\d{4}-\d{2}-\d{2}/", $return_date, $matches)) {
		$book->setReturn_Date($return_date);
		$update = 1;
	}
}

if (isset($_POST['a']) && $_POST['a'] != "") {
	$author = $db->escapeString($_POST['a']);
	$book->setAuthor($author);
	$update = 1;
}

if (isset($_POST['c']) && $_POST['c'] != "") {
	$cid = $db->escapeString($_POST['c']);
	$book->setCID($cid);
	$update = 1;
}

if (isset($_FILES['bookcover']['name']) && $_FILES['bookcover']['name'] != "") {
	echo "inside bookcover";
	// probably not extensible
	$config_allowedExts = array("gif", "jpeg", "jpg", "png");
	$config_allowedMimetypes = array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png");
	// maxImageSize can be changed somewhere at sometime someday
	$config_maxImageSize = 1000000;
	// oh fuck break it into two shits
	$temp = explode(".", $_FILES['bookcover']['name']);
	$extension = end($temp);

	if (in_array($_FILES['bookcover']['type'], $config_allowedMimetypes)
	&& ($_FILES['bookcover']['size'] < $config_maxImageSize)
	&& in_array($extension, $config_allowedExts)) {
		if ($_FILES['bookcover']['error'] > 0) {
			header("HTTP/1.0 400 Bad Request");
			return;
		} else {
			$filename = $book->getTitle() . time() . "." . $extension;
			// if there was a previous file delete it
			if ($book->getBookCover() != "" && file_exists(dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename)) { 
				unlink(dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename);
			}	
				move_uploaded_file($_FILES['bookcover']['tmp_name'],
				dirname(__FILE__) . "/../../uploads/bookcovers/" . $filename);
				// associate book file name with book in database
				$book->setBookCover($filename);
				$update = 1;
		}
	} else {
		header("HTTP/1.0 400 Bad Request");
	}
}

if (isset($_FILES['pdf']['name']) && $_FILES['pdf']['name'] != "") {
	echo "inside pdf";
	// maxImageSize can be changed somewhere at sometime someday
	$config_maxPDFSize = 1000000;
	// oh fuck break it into two shits
	$temp = explode(".", $_FILES['pdf']['name']);
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
			$book->setBookPDF($filename);
			$update = 1;
		}
	} else {
		header("HTTP/1.0 400 Bad Request");
	}
}
echo "hi";
// here we'll construct the update string from our book model
if($update) {
	$sql = "UPDATE `library`.`books`
				SET `Title` = '" . $book->getTitle() ."',
				`Publisher` = '" . $book->getPublisher() . "',
				`Author` = '" . $book->getAuthor() . "',
				`Issue_Date` = '" . $book->getIssue_Date() . "',
				`Return_Date` = '" . $book->getReturn_Date() . "',
				`CID` = '" . $book->getCID() . "',
				`BookCover` = '" . $book->getBookCover() . "',
				`BookPDF` = '" . $book->getBookPDF() . "'
				WHERE `books`.`UID`='" . $id . "'";
	$query = $db->update($sql);
	//header("HTTP/1.0 204 No Content");
}
?>