<?php

class Wallet {

  private $dbConnect;
  private $tableName = 'wallet';

  private $w_uid;
  private $w_amt;
  private $w_pid;
  private $w_mode;

  function setWuid($w_uid) { $this->w_uid = $w_uid; }
  function getWuid() { return $this->w_uid; }
  function setWamt($w_amt) { $this->w_amt = $w_amt; }
  function getWamt() { return $this->w_amt; }
  function setWpid($w_pid) { $this->w_pid = $w_pid; }
  function getWpid() { return $this->w_pid; }
  function setWmode($w_mode) { $this->w_mode = $w_mode; }
  function getWmode() { return $this->w_mode; }


  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `w_uid` = :w_uid, `w_amt` = :w_amt,`w_pid` = :w_pid, `w_mode` = :w_mode';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':w_uid', $this->w_uid);
    $stmt->bindParam(':w_amt', $this->w_amt);
    $stmt->bindParam(':w_pid', $this->w_pid);
    $stmt->bindParam(':w_mode', $this->w_mode);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

}
?>
