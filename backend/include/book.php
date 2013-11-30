<?php
include_once "database.php";
//-----------------------------------------------------------------------
//---[ Book Object] -----------------------------------------------------
//---| This class helps to create a wrapper for books found in the sql 
//---| database associated with this program. All modification and access
//---| will be done through this class and this class only. After mods
//---| have been completed, the book class will be displayed for the
//---| front end or queried back to the database. |----------------------
//-----------------------------------------------------------------------
class Book implements JsonSerializable {
	// the book class will be a wrapper for books found in the mysql database
	// this is a list of all the fields associated with the book object.
	// feel free to add or remove as necessary, make sure the changes are associated
	// in the database
	private $UID, 
		$Title, 
		$Author, 
		$Publisher, 
		$Issue_Date, 
		$Return_Date, 
		$CID,	// this is the customer id associated with the book during a checkout
		$BookCover,
		$BookPDF;

	//-----------------------------------------------------------------------
	//---[ Constructor ] ----------------------------------------------------
	//-----------------------------------------------------------------------

	// default book information
	function __construct() {
		$this->UID = NULL;
		$this->Title = "";
		$this->Author = "";
		$this->Publisher = "";
		$this->Issue_Date = "0000-00-00";
		$this->Return_Date = "0000-00-00";
		$this->CID = NULL;
		$this->BookCover = "";
		$this->BookPDF = "";
	}

	public static function bookFromRow($pRow) {
		$book = new Book();
		$book->setUID($pRow['UID']);
		$book->setTitle($pRow['Title']);
		$book->setAuthor($pRow['Author']);
		$book->setPublisher($pRow['Publisher']);
		$book->setIssue_Date($pRow['Issue_Date']);
		$book->setReturn_Date($pRow['Return_Date']);
		$book->setCID($pRow['CID']);
		$book->setBookCover($pRow['BookCover']);
		$book->setBookPDF($pRow['BookPDF']);
		return $book;
	}

	//-----------------------------------------------------------------------
	//---[ Mutators ] -------------------------------------------------------
	//-----------------------------------------------------------------------
	public function setUID($pUID) {
		$this->UID = $pUID;
	}

	public function setTitle($pTitle) {
		$this->Title = $pTitle;
	}

	public function setAuthor($pAuthor) {
		$this->Author = $pAuthor;
	}

	public function setPublisher($pPublisher) {
		$this->Publisher = $pPublisher;
	}

	public function setBookCover($pBookCover) {
		$this->BookCover = $pBookCover;
	}
	
	public function setBookPDF($pBookPDF) {
		$this->BookPDF = $pBookPDF;
	}

	//setIssue_Date(MONTH, DAY, YEAR)
	//setIssue_Date(DATAFROMDB)
	public function setIssue_Date() {
		if (func_num_args() == 3) {
			$pMonth = func_get_arg(0);
			$pDay = func_get_arg(1);
			$pYear = func_get_arg(2);
			if (checkdate($pMonth, $pDay, $pYear)) {
				$this->Issue_Date = $pYear . "-" . $pMonth . "-" . $pDay;
			}
		}
		if (func_num_args() == 1) {
			$this->Issue_Date = func_get_arg(0);
		}
	}

	public function setReturn_Date() {
		if (func_num_args() == 3) {
			$pMonth = func_get_arg(0);
			$pDay = func_get_arg(1);
			$pYear = func_get_arg(2);
			if (checkdate($pMonth, $pDay, $pYear)) {
				$this->Return_Date = $pYear . "-" . $pMonth . "-" . $pDay;
			}
		}
		if (func_num_args() == 1) {
			$this->Return_Date = func_get_arg(0);
		}
	}
	
	public function setCID($pCID) {
		$this->CID = $pCID;
	}

	//-----------------------------------------------------------------------
	//---[ Accessors ] ------------------------------------------------------
	//-----------------------------------------------------------------------
	public function getUID() { return $this->UID; }

	public function getTitle() { return $this->Title; }

	public function getAuthor() { return $this->Author; }
	
	public function getBookCover() { return $this->BookCover; }
	
	public function getBookPDF() { return $this->BookPDF; }

	public function getPublisher() { return $this->Publisher; }

	public function getIssue_Date() { return $this->Issue_Date; }

	public function getReturn_Date() { return $this->Return_Date; }

	public function getCID() { return $this->CID; }

	// json serailize thingy
	public function jsonSerialize() {
		$out = ['UID' => $this->getUID(),
			'Title' => $this->getTitle(),
			'Author' => $this->getAuthor(),
			'Publisher' => $this->getPublisher(),
			'Issue_Date' => $this->getIssue_Date(),
			'Return_Date' => $this->getReturn_Date(),
			'CID' => $this->getCID(),
			'BookCover' => $this->getBookCover(),
			'BookPDF' => $this->getBookPDF()];
		return $out;
	}
}
/*
$db = new phpLibraryDatabase();
$q = $db->Query("SELECT * FROM  `books` LIMIT 0 , 30");
$row = $q->getRows();
$yolo = Book::bookFromRow($row[0]);
$yolo2 = Book::bookFromRow($row[1]);
$arr = [$yolo, $yolo2];
echo json_encode($arr, JSON_PRETTY_PRINT);
*/
?>