<?php

class Fixer {

  private $dbConnect;
  private $tableName = 'pfx_stat';

  private $pfx_fxid;
  private $pfx_fdid;
  private $pfx_rate;
  private $pfx_pid;
  private $pfx_comment;

  function setPfxid($pfx_fxid) { $this->pfx_fxid = $pfx_fxid; }
  function getPfxid() { return $this->pfx_fxid; }
  function setPfdid($pfx_fdid) { $this->pfx_fdid = $pfx_fdid; }
  function getPfdid() { return $this->pfx_fdid; }
  function setPrate($pfx_rate) { $this->pfx_rate = $pfx_rate; }
  function getPrate() { return $this->pfx_rate; }
  function setPid($pfx_pid) { $this->pfx_pid = $pfx_pid; }
  function getPid() { return $this->pfx_pid; }
  function setComment($pfx_comment){ $this->pfx_comment = $pfx_comment;}

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `pfx_fxid` = :pfx_fxid, `pfx_fdid` = :pfx_fdid,`pfx_rate` = :pfx_rate, `pfx_pid` = :pfx_pid, `pfx_comment` = :pfx_comment';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':pfx_fxid', $this->pfx_fxid);
    $stmt->bindParam(':pfx_fdid', $this->pfx_fdid);
    $stmt->bindParam(':pfx_rate', $this->pfx_rate);
    $stmt->bindParam(':pfx_pid', $this->pfx_pid);
    $stmt->bindParam(':pfx_comment', $this->pfx_comment);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

  public function getFixerPostTitleAndUserNumber()
  {
    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit FROM `post` INNER JOIN `usr_s1` on usr_s1.u_id=post.p_uid WHERE `p_id` = :pfx_pid");
      $stmt->bindParam(':pfx_pid', $this->pfx_pid);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      return $data;
  }

}
?>
