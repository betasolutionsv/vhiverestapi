<?php

class ReportedPosts {

  private $dbConnect;
  private $tableName = 'reported_posts';

  private $rp_uid;
  private $rp_pid;
  private $rp_des;
  
  function setRpuid($rp_uid) { $this->rp_uid = $rp_uid; }
  function getRpuid() { return $this->rp_uid; }
  function setRppid($rp_pid) { $this->rp_pid = $rp_pid; }
  function getRppid() { return $this->rp_pid; }
  function setRpdes($rp_des) { $this->rp_des = $rp_des; }
  function getRpdes() { return $this->rp_des; }

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function getReportPost()
  {
    $stmt = $this->dbConnect->prepare("SELECT rp_pid FROM ".$this->tableName." WHERE rp_uid =:rp_uid AND rp_pid =:rp_pid");
    $stmt->bindParam(':rp_uid', $this->rp_uid);
    $stmt->bindParam(':rp_pid', $this->rp_pid);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `rp_uid` = :rp_uid, `rp_pid` = :rp_pid,`rp_des` = :rp_des';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':rp_uid', $this->rp_uid);
    $stmt->bindParam(':rp_pid', $this->rp_pid);
    $stmt->bindParam(':rp_des', $this->rp_des);
 
    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

}

?>