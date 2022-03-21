<?php

class SavedPosts {

  private $dbConnect;
  private $tableName = 'saved_posts';

  private $sp_uid;
  private $sp_pid;

  function setSpuid($sp_uid) { $this->sp_uid = $sp_uid; }
  function getSpuid() { return $this->sp_uid; }
  function setSppid($sp_pid) { $this->sp_pid = $sp_pid; }
  function getSppid() { return $this->sp_pid; }

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  // Get Posted Job Info
  public function getSavedPost()
  {
    $stmt = $this->dbConnect->prepare("SELECT sp_pid FROM ".$this->tableName." WHERE sp_uid =:sp_uid AND sp_pid =:sp_pid");
    $stmt->bindParam(':sp_uid', $this->sp_uid);
    $stmt->bindParam(':sp_pid', $this->sp_pid);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  public function insertRecord() 
  {   

    $sql = 'INSERT INTO '.$this->tableName.' SET `sp_uid` = :sp_uid, `sp_pid` = :sp_pid';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':sp_uid', $this->sp_uid);
    $stmt->bindParam(':sp_pid', $this->sp_pid);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

   public function removeRecord()
  {   

    $sql = 'DELETE FROM '.$this->tableName.' WHERE `sp_uid` = :sp_uid AND `sp_pid` = :sp_pid';
   // echo $sql; exit;
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':sp_uid', $this->sp_uid);
    $stmt->bindParam(':sp_pid', $this->sp_pid);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }



  }

}
?>
