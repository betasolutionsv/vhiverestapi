<?php

class Image {

    private $dbConnect;
    
	private $tableName1 = 'up_img';

	private $upi_id;
	private $upi_pid;
	private $upi_img;

	function setUid($upi_id) { $this->upi_id = $upi_id; }
	function getUid() { return $this->upi_id; }
	function setUpid($upi_pid) { $this->upi_pid = $upi_pid; }
	function getUpid() { return $this->upi_pid; }
	function setUimg($upi_img) { $this->upi_img = $upi_img; }
    function getUimg() { return $this->upi_img; }
    
    private $tableName2 = 'pstat_img';

	private $psi_id;
	private $psi_pfxid;
	private $psi_img;

	function setPsid($psi_id) { $this->psi_id = $psi_id; }
	function getPsid() { return $this->psi_id; }
	function setPfid($psi_pfxid) { $this->psi_pfxid = $psi_pfxid; }
	function getPfid() { return $this->psi_pfxid; }
	function setPimg($psi_img) { $this->psi_img = $psi_img; }
	function getPimg() { return $this->psi_img; }


	public function __construct() {
		$db = new Database();
		$this->dbConnect = $db->connect();
	}

	public function saveFinderPostedImage() {

		$sql = 'INSERT INTO '.$this->tableName1.' SET `upi_pid` = :upi_pid, `upi_img` = :upi_img';
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':upi_pid', $this->upi_pid);
		$stmt->bindParam(':upi_img', $this->upi_img);

		if($stmt->execute()) {
			$data = $this->readFinderPostedImages();
			return $data;
		} else {
			return $data;
		}
	}

	public function removeFinderPostedImage() {

		$sql = 'DELETE FROM '.$this->tableName1.' WHERE `upi_id` = :upi_id';
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':upi_id', $this->upi_id);
	
		if($stmt->execute()) {
		
			$data = $this->readFinderPostedImages();
			return $data;
		} else {
			return $data;
		}
	}

	public function readFinderPostedImages() {

		$stmt = $this->dbConnect->prepare("SELECT `upi_id`,`upi_pid`,`upi_img` FROM ".$this->tableName1." WHERE `upi_pid` = :upi_pid");
		$stmt->bindParam(':upi_pid', $this->upi_pid);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
    }
    
    public function saveFixerPostedImage() {

		$sql = 'INSERT INTO '.$this->tableName2.' SET `psi_pfxid` = :psi_pfxid, `psi_img` = :psi_img';
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':psi_pfxid', $this->psi_pfxid);
		$stmt->bindParam(':psi_img', $this->psi_img);

		if($stmt->execute()) {
			$data = $this->readPostedImages();
			return $data;
		} else {
			return $data;
		}
	}

	public function removeFixerPostedImage() {

		$sql = 'DELETE FROM '.$this->tableName2.' WHERE `psi_id` = :psi_id';
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':psi_id', $this->psi_id);
	
		if($stmt->execute()) {
		
			$data = $this->readPostedImages();
			return $data;
		} else {
			return $data;
		}
	}

	public function readFixerPostedImages() {

		$stmt = $this->dbConnect->prepare("SELECT `psi_id`,`psi_pfxid`,`psi_img` FROM ".$this->tableName2." WHERE `psi_pfxid` = :psi_pfxid");
		$stmt->bindParam(':psi_pfxid', $this->psi_pfxid);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

}

?>
