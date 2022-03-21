<?php

class ScratchCard {

  private $dbConnect;
  private $tableName = 'scratch_card';

  private $sc_uid;
  private $sc_utype;
  private $sc_pid;
  private $sc_won;
  private $sc_sdt;
  private $sa_id;
  private $token;

  function setScid($sc_uid) { $this->sc_uid = $sc_uid; }
  function getScid() { return $this->sc_uid; }
  function setStype($sc_utype) { $this->sc_utype = $sc_utype; }
  function getStype() { return $this->sc_utype; }
  function setSpid($sc_pid) { $this->sc_pid = $sc_pid; }
  function getSpid() { return $this->sc_pid; }
  function setSwon($sc_won) { $this->sc_won = $sc_won; }
  function getSwon() { return $this->sc_won; }
  function setSscdt($sc_sdt) { $this->sc_sdt = $sc_sdt; }
  function getSscdt() { return $this->sc_sdt; }
  function setSaid($sa_id) { $this->sa_id = $sa_id; }
  function getSaid() { return $this->sa_id; }

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $date = date('Y-m-d H:i:s');
    $token = '62e21et2e6217682hjsgd';

    $sql = 'INSERT INTO '.$this->tableName.' SET `sc_uid` = :sc_uid,`sc_pid` = :sc_pid, `sc_won` = :sc_won,`sc_sdt` = :sc_sdt,`sa_id` = :sa_id,`sc_transaction_id` = :token';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':sc_uid', $this->sc_uid);
    $stmt->bindParam(':sc_pid', $this->sc_pid);
    $stmt->bindParam(':sc_won', $this->sc_won);
    $stmt->bindParam(':sc_sdt', $date);
    $stmt->bindParam(':sa_id', $this->sa_id);
    $stmt->bindParam(':token', $token);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

}
?>
