<?php

class Add {

  private $dbConnect;
  private $tableName = 'adds';

  private $a_type;
  private $a_img;
  private $a_link;
  private $a_note;

  function setAtype($a_type) { $this->a_type = $a_type; }
  function getAtype() { return $this->a_type; }
  function setAimg($a_img) { $this->a_img = $a_img; }
  function getAimg() { return $this->a_img; }
  function setAlink($a_link) { $this->a_link = $a_link; }
  function getAlink() { return $this->a_link; }
  function setAnote($a_note) { $this->a_note = $a_note; }
  function getAnote() { return $this->a_note; }
 
  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `a_type` = :a_type, `a_img` = :a_img, `a_link` = :a_link, `a_note` = :a_note';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':a_type', $this->a_type);
    $stmt->bindParam(':a_img', $this->a_img);
    $stmt->bindParam(':a_link', $this->a_link);
    $stmt->bindParam(':a_note', $this->a_note);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

}
?>