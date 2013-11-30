<?php
include_once "database.php";
//-----------------------------------------------------------------------
//---[ Customer Object] -----------------------------------------------------
//---| This class helps to create a wrapper for books found in the sql 
//---| database associated with this program. All modification and access
//---| will be done through this class and this class only. After mods
//---| have been completed, the customer class will be displayed for the
//---| front end or queried back to the database. |----------------------
//-----------------------------------------------------------------------
// for errors
date_default_timezone_set("America/Phoenix");
class Customer implements JsonSerializable {
	// the customer class will be a wrapper for books found in the mysql database
	// this is a list of all the fields associated with the customer object.
	// feel free to add or remove as necessary, make sure the changes are associated
	// in the database
	private 
		$UID, 
		$First_Name, 
		$Last_name, 
		$Creation_Date, 
		$Book_List;
			

	//-----------------------------------------------------------------------
	//---[ Constructor ] ----------------------------------------------------
	//-----------------------------------------------------------------------

	// default customer information
	function __construct() {
		$this->UID = -1;
		$this->First_Name = "";
		$this->Last_name = "";
		$this->Creation_Date = date("Y-m-d");
		$this->Book_List = array();
		
	}

	public static function customerFromRow($pRow) {
		$customer = new Customer();
		$customer->setUID($pRow['UID']);
		$customer->setFirst_Name($pRow['First_Name']);
		$customer->setLast_name($pRow['Last_Name']);
		$customer->setCreation_Date($pRow['Creation_Date']);
		$customer->setBook_List(json_decode($pRow['Book_List']));
		return $customer;
	}

	//-----------------------------------------------------------------------
	//---[ Mutators ] -------------------------------------------------------
	//-----------------------------------------------------------------------
	public function setUID($pUID) {
		$this->UID = $pUID;
	}

	public function setFirst_Name($pFirst_Name) {
		$this->First_Name = $pFirst_Name;
	}

	public function setLast_Name($pLast_Name) {
		$this->Last_Name = $pLast_Name;
	}

	public function setBook_List($pBook_List) { 
		$this->Book_List = $pBook_List;
	}
	
	public function setCreation_Date($pCreation_Date) {
		$this->Creation_Date = $pCreation_Date;
	}
	
	//-----------------------------------------------------------------------
	//---[ Accessors ] ------------------------------------------------------
	//-----------------------------------------------------------------------
	public function getUID() { return $this->UID; }

	public function getFirst_Name() { return $this->First_Name; }

	public function getLast_Name() { return $this->Last_Name; }

	public function getBook_List() { return $this->Book_List; }

	public function getCreation_Date() { return $this->Creation_Date; }

	
	// json serailize thingy
	public function jsonSerialize() {
		$out = ['UID' => $this->getUID(),
			'First_Name' => $this->getFirst_Name(),
			'Last_Name' => $this->getLast_Name(),
			'Book_List' => $this->getBook_List(),
			'Creation_Date' => $this->getCreation_Date()];
		return $out;
	}
}
/*
$db = new phpLibraryDatabase();
$q = $db->Query("SELECT * FROM  `books` LIMIT 0 , 30");
$row = $q->getRows();
$yolo = Customer::bookFromRow($row[0]);
$yolo2 = Customer::bookFromRow($row[1]);
$arr = [$yolo, $yolo2];
echo json_encode($arr, JSON_PRETTY_PRINT);
*/
?>
