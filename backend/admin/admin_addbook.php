<?php
//-------------------------------------------------------------------------------------
// admin_addbook.php: adds a book to the database
// operation: admin_addbook.php?id=_UID_&t=_TITLE_&p=_PUBLISHER_&i=_ISSUE_DATE_&r=_RETURN_DATE_&a=_AUTHOR_&c=_CID_&bookcover=
// 				where 	_UID_ is an unsigned integer, this is a required field.
//						_TITLE_ is a string with no quotes, this is a required field
//						_PUBLISHER_ is a string with no quotes, this is a required field
//						_ISSUE_DATE_ is in the format yyyy-mm-dd, this is a required field
//						_RETURN_DATE_ is in the format yyyy-mm-dd
//						_AUTHOR_ is a string with no quotes, this is a required field
//						_CID_ is an unsigned integer representing customer id
//-------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/book.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

$db = new phpLibraryDatabase();
$book = new book();

if (isset($_POST['t'])) {
	$title = $db->escapeString($_POST['t']);
	$book->setTitle($title);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

if (isset($_POST['p'])) {
	$publisher = $db->escapeString($_POST['p']);
	$book->setPublisher($publisher);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}
	
if (isset($_POST['i'])) {
	$matches = "";
	$issue_date = $db->escapeString($_POST['i']);
	if(preg_match("/\d{4}-\d{2}-\d{2}/", $issue_date, $matches)) {
		$book->setIssue_Date($issue_date);
	} else {
		header("HTTP/1.0 400 Bad Request");
		return;
	}
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// not necessary for creation
if (isset($_POST['r'])) {
	$matches = "";
	$return_date = $db->escapeString($_POST['r']);
	if(preg_match("/\d{4}-\d{2}-\d{2}/", $return_date, $matches)) {
		$book->setReturn_Date($return_date);
	}
}


if (isset($_POST['a'])) {
	$author = $db->escapeString($_POST['a']);
	$book->setAuthor($author);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

if (isset($_POST['c'])) {
	$cid = $db->escapeString($_POST['c']);
	$book->setCID($cid);
}

if (isset($_FILES['bookcover']['name'])) {
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
		}
	} else {
		header("HTTP/1.0 400 Bad Request");
	}
}

if (isset($_FILES['pdf']['name'])) {
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
			echo $filename;
			// if there was a previous file delete it
			if ($book->getBookCover() != "" && file_exists(dirname(__FILE__) . "/../../uploads/bookpdfs/" . $filename)) { 
				unlink(dirname(__FILE__) . "/../../uploads/bookpdfs/" . $filename);
			}
			
			move_uploaded_file($_FILES['pdf']['tmp_name'],
			dirname(__FILE__) . "/../../uploads/bookpdfs/" . $filename);
			// associate book file name with book in database
			$book->setBookPDF($filename);
		}
	} else {
		header("HTTP/1.0 400 Bad Request");
	}
}
$sql = "INSERT INTO `books`
		(`Title`, `BookCover`, `BookPDF`, `Publisher`, `Issue_Date`, `Return_Date`, `Author`, `CID`)
		VALUES 
		('" . $book->getTitle() . "',
		'". $book->getBookCover() ."',
		'". $book->getBookPDF() ."',
		'". $book->getPublisher() ."',
		'". $book->getIssue_Date() ."',
		'". $book->getReturn_Date() ."',
		'". $book->getAuthor() ."',
		'". $book->getCID() ."')";
$db->update($sql);
?>
