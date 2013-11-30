<?php
//-------------------------------------------------------------------------------------
// searchbooks.php: returns multiple json objects representing books from our database
// operation: searchbooks.php is a sorting and searching application.
// 				Important to mention are the sorting operations before the searching
//				operations
//---[ sort ]
// searchbooks.php?sortMode=ASC&col=title&start=0&items=30
// these are the default settings for the sortmode - you don't have to input these
// for the application to sort by them
// nondefault sortsettings:
// sortMode = ASC | DESC - ASC is ascending, DESC is descending
// col = title | publisher | issue | return | author | cid
//start = the starting index from the database
// if you have sorted material, and you input 5 for start, then it will skip to the 5th row
// and grab
// items = number of items to grab from database
// it will grab however many items you request of it
//---[ search ]
// the same options can be applied from the sort on the search, defaults still apply
// searchbooks.php?q=querytosearch&searchField=title
// q = inputstringforsearch, no quotes
// searchField = title | author | publisher
// this will search the database by q in the field searchField
//-------------------------------------------------------------------------------------

include "include/database.php";
include "include/book.php";




$db = new phpLibraryDatabase();

if (isset($_GET['q'])) {
	$q = $db->escapeString($_GET['q']);
} else $q = NULL;

if (isset($_GET['searchField'])) {
	if ($_GET['searchField'] == "title") {
		$searchField = "Title";
	} else if ($_GET['searchField'] == "author") {
		$searchField = "Author";
	} else if ($_GET['searchField'] == "publisher") {
		$searchField = "Publisher";
	} else $searchField = "Title";
} else $searchField = "Title";


if (isset($_GET['sortMode'])) {
	if ($_GET['sortMode'] == "DESC") {
		$sortMode = "DESC";
	} else $sortMode = "ASC";
} else $sortMode = "ASC";

if (isset($_GET['col'])) {
	if ($_GET['col'] == "title") {
		$col = "Title";
	} else if ($_GET['col'] == "publisher") {
		$col = "Publisher";
	} else if ($_GET['col'] == "issue") {
		$col = "Issue_Date";
	} else if ($_GET['col'] == "return") {
		$col = "Return_Date";
	} else if ($_GET['col'] == "author") {
		$col = "Author";
	} else if ($_GET['col'] == "cid") {
		$col = "CID";
	} else {
		$col = "Title";
	}
} else $col = "Title";

if (isset($_GET['start'])) {
	if (is_int($_GET['start'])) {
		$start = $db->escapeString($_GET['start']);
	} else $start = 0;
} else $start = 0;

if (isset($_GET['items'])) {
	if (is_int($_GET['items'])) {
		$items = $db->escapeString($_GET['items']);
	} else $items = 120;
} else $items = 120;


if (is_null($q)) {
	$query = $db->query("SELECT * FROM `books` ORDER BY `books`.`" . $col . "` " . $sortMode . " LIMIT " . $start . " , " . $items);
} else {
	$query = $db->query("SELECT * FROM `books` WHERE `" . $searchField . "` LIKE '%" . $q . "%' ORDER BY `books`.`" . $col . "` " . $sortMode . " LIMIT " . $start . " , " . $items);
}

$rows = $query->getRows();
$out = array();

for ($i = 0; $i < $query->getRowCount(); $i++) {
	array_push($out, Book::bookFromRow($rows[$i]));
}

// are we displaying content?
header("HTTP/1.0 200 OK");
echo json_encode($out);

?>
