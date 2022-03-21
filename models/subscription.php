<?php

class Subscription {

  private $dbConnect;
  private $tableName = 'subscription';

  private $s_uid;
  private $s_amt;
  private $s_disc;
  private $s_vdt;
  private $s_stat;

  function setSuid($s_uid) { $this->s_uid = $s_uid; }
  function getSuid() { return $this->s_uid; }
  function setSamt($s_amt) { $this->s_amt = $s_amt; }
  function getSamt() { return $this->s_amt; }
  function setSdisc($s_disc) { $this->s_disc = $s_disc; }
  function getSdisc() { return $this->s_disc; }
  function setSvdt($s_vdt) { $this->s_vdt = $s_vdt; }
  function getSvdt() { return $this->s_vdt; }
  function setStat($s_stat) { $this->s_stat = $s_stat; }
  function getStat() { return $this->s_stat; }

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `s_uid` = :s_uid, `s_amt` = :s_amt,`s_disc` = :s_disc, `s_vdt` = :s_vdt,`s_stat` = :s_stat';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':s_uid', $this->s_uid);
    $stmt->bindParam(':s_amt', $this->s_amt);
    $stmt->bindParam(':s_disc', $this->s_disc);
    $stmt->bindParam(':s_vdt', $this->s_vdt);
    $stmt->bindParam(':s_stat', $this->s_stat);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

}
?>
