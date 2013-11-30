<?php
include_once "database.php";

// This session function was found at:
// http://www.wikihow.com/Create-a-Secure-Login-Script-in-PHP-and-MySQL
// borrowed this because it is generic enough to work.
function sec_session_start() {
        $session_name = 'yaLMS'; // Set a custom session name
        $secure = false; // Set to true if using https.
        $httponly = true; // This stops javascript being able to access the session id. 
 
        ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies. 
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly); 
        session_name($session_name); // Sets the session name to the one set above.
        session_start(); // Start the php session
        session_regenerate_id(); // regenerated the session, delete the old one.  
}

function login($user, $password) {
	// connect to the database
	$db = new phpLibraryDatabase();
	// escape the user string
	$user = $db->escapeString($user);
	// check to see if that user exists
	$queryData = $db->query("SELECT * FROM  `users` WHERE  `Username` =  '" . $user . "'");

	// was there a row returned?
	// if not then throw BADUSERNAME
	if($queryData->getRowCount() == 0) {
		return false;
	}

	// Now let's format this shiznit into readable variables, please.
	$rows = $queryData->getRows();
	// adminid
	$userID = $rows[0]['UID'];
	// username
	$userName = $rows[0]['Username'];
	// password hash:
	$dbPass = $rows[0]['Password'];
	// password salt:
	$dashOfSalt = $rows[0]['Salt'];
	// is the user an admin?
	$admin = $rows[0]['admin'];

	// generate passphrase from provided password
	$password = hash('sha512', $dashOfSalt . $password . $dashOfSalt);

	// does the password match the one in the database?
	if ($dbPass == $password) {
		$user_browser = $_SERVER['HTTP_USER_AGENT'];

		$_SESSION['userID'] = $userID;
		$_SESSION['username'] = $userName;
		$_SESSION['login_string'] = hash('sha512', $user_browser.$password.$user_browser);
		return true;
	} else return false;
}

function loginCheck(){
	// escape the user string
	if (!isset($_SESSION['userID']) || !isset($_SESSION['username']) || !isset($_SESSION['login_string'])) {
		return false;
	}

	// connect to the database
	$db = new phpLibraryDatabase();
	
	$user = $db->escapeString($_SESSION['userID']);
	$queryData = $db->query("SELECT * FROM  `users` WHERE  `UID` =  '" . $user . "'");
	
	
	// was there a row returned?
	// if not then throw BADUSERNAME
	if($queryData->getRowCount() == 0) {
		return FALSE;
	}
	$rows = $queryData->getRows();
		
	
	// username
	$userName = $rows[0]['Username'];
	// password hash:
	$dbPass = $rows[0]['Password'];
	$user_browser = $_SERVER['HTTP_USER_AGENT'];
	
	if($_SESSION['username'] == $userName  && $_SESSION['login_string'] == hash('sha512', $user_browser.$dbPass.$user_browser)) {
		return true;
	}else{
		return false;
	}
	
}

function isAdmin() {
	if (!loginCheck())
		return false;
	// let's not store admin status in a cookie
	// let's just query the database to see if the user was an admin
	$db = new phpLibraryDatabase();
	$id = $db->escapeString($_SESSION['userID']);
	$query = $db->query("SELECT `admin` FROM `users` WHERE `UID` = " . $id);
	$userRow = $query->getRows();
	$adminCheck = $userRow[0]['admin'];
	if ($adminCheck != 0) return true;
	else return false;
}

function logout() {
	$_SESSION = array();
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	session_destroy();
	header('Location: ./');
}

// This will start the session anywhere this file is included.
sec_session_start();

?>