<?php
//---------------------------------------------------------------------------------------
// user_checkoutbook.php is used to check out a book to a user.
// operation of this module is based on the HTTP POST method
// post bid to this module as follows
// b: bid
// where bid is the book id
// this module will determine if the book exists and is not already checked out
// if it is not then the book will be assigned to the customer's book list.
//---------------------------------------------------------------------------------------
include "../include/session.php";
include "../include/book.php";
include "../include/customer.php";

if (!loginCheck()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

if (isAdmin()) { // lol stick it to the admins
	header("HTTP/1.0 403 Forbidden");
	return;
}

$db = new phpLibraryDatabase();

// did we post a book id?
if (isset($_POST['b'])) {
	$id = $db->escapeString($_POST['b']);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$query = $db->query("SELECT * FROM `books` WHERE `UID` = " . $id);
// is that actually a book in the database?

if ($query->getRowCount() == 0) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$row = $query->getRows();
$book = Book::bookFromRow($row[0]);

$username = $_SESSION['username'];
$query = $db->query("SELECT `CID` FROM `users` WHERE `Username` = '". $username ."';");
// this will never happen?
if ($query->getRowCount() == 0) {
	header("HTTP/1.0 418 I'm a teapot"); // because seriously, what?
	return;
}
$rows = $query->getRows();
$cid = $rows[0]['CID'];

// is the book checked out?
if ($book->getCID() != 0 && $book->getCID() != $cid) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

if ($book->getCID() == $cid) {
	$row = $query->getRows();
	$cid = $row[0]['CID'];

	$query = $db->query("SELECT * FROM `customers` WHERE `UID` = '" . $cid . "';");

	$row = $query->getRows();
	$row = $row[0];
	// kk here's the customer we wanted to look at
	$customer = Customer::customerFromRow($row);
	$booklist = $customer->getBook_List();
	$bid = $book->getUID();
	// i suppose the id could exist in the array .... maybe?
	if (in_array($bid, $booklist)) {
		// swap the key to the last element of the array
		$key = array_search($bid, $booklist);
		$temp = $booklist[$key];
		$booklist[$key] = $booklist[count($booklist) - 1];
		$booklist[count($booklist) - 1] = $temp;
		array_pop($booklist);
	} else return;


	$customer->setBook_List($booklist);
	$sql = "UPDATE `customers` SET `Book_List` = '". json_encode($customer->getBook_List()) ."' WHERE `UID` = ". $cid;
	$db->update($sql);

	$query = $db->query("SELECT * FROM `books` WHERE `UID` = " . $bid);
	// is that actually a book in the database?
	// if it's not don't update the book
	if ($query->getRowCount() == 0) {
		header("HTTP/1.0 200 OK");
		return;
	}

	$row = $query->getRows();
	$book = Book::bookFromRow($row[0]);

	// since the book exists we can update it in the database
	$book->setCID(0);
	$book->setReturn_Date(date("Y-m-d", time()));
	$sql = "UPDATE `books` SET `CID` = '". $book->getCID() ."', `Return_Date` = '".$book->getReturn_Date() ."' WHERE `UID` = " . $bid;
	$db->update($sql);
} else {
	$row = $query->getRows();
	$cid = $row[0]['CID'];

	$query = $db->query("SELECT * FROM `customers` WHERE `UID` = '" . $cid . "';");

	$row = $query->getRows();
	$row = $row[0];
	// kk here's the customer we wanted to look at
	$customer = Customer::customerFromRow($row);
	$booklist = $customer->getBook_List();

	// i suppose the id could exist in the array .... maybe?
	if (!in_array($id, $booklist)) {
		array_push($booklist, $id);
	}



	$customer->setBook_List($booklist);
	$sql = "UPDATE `customers` SET `Book_List` = '". json_encode($customer->getBook_List()) ."'	WHERE `UID` = ". $cid;
	$db->update($sql);

	$book->setCID($cid);
	$book->setReturn_Date(date("Y-m-d", time() + 7*24*60*60));
	$sql = "UPDATE `books` SET `CID` = '". $cid ."', `Return_Date` = '".$book->getReturn_Date() ."' WHERE `UID` = " . $id;
	$db->update($sql);
}
?>