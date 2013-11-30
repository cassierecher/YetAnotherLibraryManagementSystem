<?php
//---------------------------------------------------------------------------------------
// user_returnbook.php is used to check in a book from a user.
// operation of this module is based on the HTTP POST method
// post bid to this module as follows
// b: bid
// where bid is the book id
// this module will determine if the book exists and is already checked out
// if it is checked out to this user it will be removed from the users list
// and the book will be set to null CID
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
	$bid = $db->escapeString($_POST['b']);
} else {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$username = $_SESSION['username'];

$query = $db->query("SELECT `CID` FROM `users` WHERE `Username` = '". $username ."';");

// this will never happen?
if ($query->getRowCount() == 0) {
	header("HTTP/1.0 418 I'm a teapot"); // because seriously, what?
	return;
}

$row = $query->getRows();
$cid = $row[0]['CID'];

$query = $db->query("SELECT * FROM `customers` WHERE `UID` = '" . $cid . "';");

$row = $query->getRows();
$row = $row[0];
// kk here's the customer we wanted to look at
$customer = Customer::customerFromRow($row);
$booklist = $customer->getBook_List();

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
$sql = "UPDATE `books` SET `CID` = '". $book->getCID() ."' WHERE `UID` = " . $bid;
$db->update($sql);
?>