<?php
class User implements JsonSerializable {
	private	$mAdmin,
			$mUsername,
			$mSalt,
			$mPassword,
			$mCID;
	
	public function __construct() {
		$this->mUID = NULL;
		$this->mAdmin = FALSE;
		$this->mUsername = "";
		$this->mSalt = time();
		$this->mHash = "";
		$this->mCID = NULL;
	}
	
	public static function userFromRow($pRow) {
		$user = new User();
		$user->setUID($pRow['UID']);
		$user->setAdmin($pRow['admin']);
		$user->setUsername($pRow['Username']);
		$user->setCID($pRow['CID']);
		$user->setHash($pRow['Password']);
		$user->setSalt($pRow['Salt']);
		return $user;
	}
	
	public function getUID() {
		return $this->mUID;
	}
	
	public function getAdmin() {
		return $this->mAdmin;
	}
	
	public function getUsername() {
		return $this->mUsername;
	}
	
	public function getSalt() {
		return $this->mSalt;
	}
	
	public function getHash() {
		return $this->mHash;
	}
	
	public function getCID() {
		return $this->mCID;
	}
	
	public function setUID($pUID) {
		$this->mUID = $pUID;
	}
	
	public function setAdmin($pAdmin) {
		$this->mAdmin = $pAdmin;
	}
	
	public function setUsername($pUsername) {
		$this->mUsername = $pUsername;
	}
	
	public function setSalt($pSalt) {
		$this->mSalt = $pSalt;
	}
	
	public function hashPassword($pPassword) {
		$this->mHash = hash('sha512', $this->getSalt() . $pPassword . $this->getSalt());
	}
	
	public function setHash($pHash) {
		$this->mHash = $pHash;
	}
	
	public function setCID($pCID) {
		$this->mCID = $pCID;
	}
	
	public function jsonSerialize() {
		$out = ['UID' => $this->getUID(),
			'Username' => $this->getUsername(),
			'CID' => $this->getCID(),
			'isAdmin' => $this->getAdmin()];
		return $out;
	}
}
?>