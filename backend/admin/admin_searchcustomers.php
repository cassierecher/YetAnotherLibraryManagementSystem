<?php
//-------------------------------------------------------------------------------------
// admin_searchcustomers.php: returns multiple json objects representing customers from our database
// operation: admin_searchcustomers.php is a sorting and searching application.
// 				Important to mention are the sorting operations before the searching
//				operations
//---[ sort ]
// searchbooks.php?sortMode=ASC&col=last&start=0&items=30
// these are the default settings for the sortmode - you don't have to input these
// for the application to sort by them
// nondefault sortsettings:
// sortMode = ASC | DESC - ASC is ascending, DESC is descending
// col = first | last | id
//start = the starting index from the database
// if you have sorted material, and you input 5 for start, then it will skip to the 5th row
// and grab
// items = number of items to grab from database
// it will grab however many items you request of it
//---[ search ]
// the same options can be applied from the sort on the search, defaults still apply
// searchbooks.php?q=querytosearch&searchField=last
// q = inputstringforsearch, no quotes
// searchField = first | last | id
// this will search the database by q in the field searchField
//-------------------------------------------------------------------------------------

include "../include/session.php";
include "../include/customer.php";

if (!isAdmin()) {
	header("HTTP/1.0 401 Unauthorized");
	return;
}

$db = new phpLibraryDatabase();

if (isset($_GET['q'])) {
	$q = $db->escapeString($_GET['q']);
} else $q = NULL;

if (isset($_GET['searchField'])) {
	if ($_GET['searchField'] == "first") {
		$searchField = "First_Name";
	} else if ($_GET['searchField'] == "last") {
		$searchField = "Last_Name";
	} else if ($_GET['searchField'] == "id") {
		$searchField = "UID";
	} else $searchField = "Last_Name";
} else $searchField = "UID";


if (isset($_GET['sortMode'])) {
	if ($_GET['sortMode'] == "DESC") {
		$sortMode = "DESC";
	} else $sortMode = "ASC";
} else $sortMode = "ASC";

if (isset($_GET['col'])) {
	if ($_GET['col'] == "first") {
		$col = "First_Name";
	} else if ($_GET['col'] == "last") {
		$col = "Last_Name";
	} else if ($_GET['col'] == "id") {
		$col = "UID";
	} else $col = "Last_Name";
} else $col = "Last_Name";

if (isset($_GET['start'])) {
	if (is_int($_GET['start'])) {
		$start = $db->escapeString($_GET['start']);
	} else $start = 0;
} else $start = 0;

if (isset($_GET['items'])) {
	if (is_int($_GET['items'])) {
		$items = $db->escapeString($_GET['items']);
	} else $items = 30;
} else $items = 30;


if (is_null($q)) {
	$query = $db->query("SELECT * FROM `customers` ORDER BY `customers`.`" . $col . "` " . $sortMode . " LIMIT " . $start . " , " . $items);
} else {
	$query = $db->query("SELECT * FROM `customers` WHERE `" . $searchField . "` LIKE '" . $q . "' ORDER BY `customers`.`" . $col . "` " . $sortMode . " LIMIT " . $start . " , " . $items);
}

$rows = $query->getRows();
$out = array();

for ($i = 0; $i < $query->getRowCount(); $i++) {
	array_push($out, Customer::customerFromRow($rows[$i]));
}

// are we displaying content?
header("HTTP/1.0 200 OK");
echo json_encode($out);

?>