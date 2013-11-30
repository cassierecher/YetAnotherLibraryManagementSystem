<?php
// Config file
include_once "config.php";


// This is a container class for query data.
// Simplifies code throughout the project.
class phpLibraryQueryData {

	private $data, $rows, $rowCount;

	function __construct($r)
	{
		$this->data = $r;
		$this->rows = $r->fetch_all(MYSQLI_BOTH);
		$this->rowCount = $r->num_rows;
	}

	public function getRows()
	{
		return $this->rows;
	}
	
	public function getRowCount()
	{
		return $this->rowCount;
	}
}


// This is a wrapper class for mysqli. We will declare this object once, so all queries to
// the database will be done through this class. 
class phpLibraryDatabase
{
	protected $mysqli;

	function __construct()
	{
		// some pseudo magic prevents me from directly inputting these
		// into the mysqli constructor..? So here's to memory management!
		$host = $GLOBALS["config_mysql_host"];
		$user = $GLOBALS["config_mysql_user"];
		$password = $GLOBALS["config_mysql_password"];
		$database = $GLOBALS["config_mysql_database"];

		$this->mysqli = new mysqli( $host, $user, $password, $database );

		if ($this->mysqli->connect_errno)
		{
			echo "Failed to connect to MySQL: " . $this->mysqli->connect_error;
		}
	}

	public function query($q)
	{
		$result = $this->mysqli->query($q);
		
		if (!$result)
		{
			echo "Query failure: (" . $this->mysqli->errno .") " . $this->mysqli->error;
			return;
		}
		
		return new phpLibraryQueryData($result);
	}
	
	public function update($q)
	{
		$result = $this->mysqli->query($q);
		
		if (!$result)
		{
			echo "Query failure: (" . $this->mysqli->errno .") " . $this->mysqli->error;
			return;
		}
	}

	public function escapeString($s) {
		return $this->mysqli->real_escape_string($s);
	}

	// the power of memory management compels you :o......
	function __destruct() { $this->mysqli->close(); }
}

/* this was explicitly for testing the database class.
$db = new phpLibraryDatabase();
$q = $db->query("SELECT * FROM `readthisshit` ORDER BY `readthisshit` . `id` DESC LIMIT 0, 30");
$rows = $q->getRows();
$rowCount = $q->getRowCount();

for ($i = 0; $i < $rowCount; $i++)
{
	echo $rows[$i]['title'] . "<br />" . $rows[$i]['content'] . "<br />";
}
*/

?>