<?php

class Finder {

  private $dbConnect;
  private $tableName = 'pfd_stat';

  private $pfd_fdid;
  private $pfd_fxid;
  private $pfd_pid;
  private $pfd_rate;
  private $pfd_mode;
  private $pfd_ramt;
  private $pfd_comment;

  function setPfdid($pfd_fdid) { $this->pfd_fdid = $pfd_fdid; }
  function getPfdid() { return $this->pfd_fdid; }
  function setPfxid($pfd_fxid) { $this->pfd_fxid = $pfd_fxid; }
  function getPfxid() { return $this->pfd_fxid; }
  function setPrate($pfd_rate) { $this->pfd_rate = $pfd_rate; }
  function getPrate() { return $this->pfd_rate; }
  function setPfmode($pfd_mode) { $this->pfd_mode = $pfd_mode; }
  function getPfmode() { return $this->pfd_mode; }
  function setPramt($pfd_ramt) { $this->pfd_ramt = $pfd_ramt; }
  function getPramt() { return $this->pfd_ramt; }
  function setPid($pfd_pid) { $this->pfd_pid = $pfd_pid; }
  function getPid() { return $this->pfd_pid; }
  function setComment($pfd_comment){ $this->pfd_comment = $pfd_comment;}

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    try{

      /* Begin a transaction, turning off autocommit */
      $this->dbConnect->beginTransaction();

      /* Change the database schema and data */
      $sql = 'INSERT INTO '.$this->tableName.' SET `pfd_fdid` = :pfd_fdid, `pfd_fxid` = :pfd_fxid, `pfd_pid` = :pfd_pid, `pfd_rate` = :pfd_rate, `pfd_mode` = :pfd_mode, `pfd_ramt` = :pfd_ramt, `pfd_comment` = :pfd_comment';
      $stmt = $this->dbConnect->prepare($sql);
      $stmt->bindParam(':pfd_fdid', $this->pfd_fdid);
      $stmt->bindParam(':pfd_fxid', $this->pfd_fxid);
      $stmt->bindParam(':pfd_pid', $this->pfd_pid);
      $stmt->bindParam(':pfd_rate', $this->pfd_rate);
      $stmt->bindParam(':pfd_mode', $this->pfd_mode);
      $stmt->bindParam(':pfd_ramt', $this->pfd_ramt);
      $stmt->bindParam(':pfd_comment', $this->pfd_comment);
      $stmt->execute();

      $p_astat = 1;
      $sql = 'UPDATE post SET `p_astat` = :p_astat WHERE `p_id` = :pfd_pid';
      $stmt = $this->dbConnect->prepare($sql);
      $stmt->bindParam(':p_astat', $p_astat);
      $stmt->bindParam(':pfd_pid', $this->pfd_pid);
      $stmt->execute();

      if($this->dbConnect->commit()){
        return true;
      }else{
        return false;
      }
    
    }catch(Exception $e){
      /* Recognize mistake and roll back changes */
      $this->dbConnect->rollBack();
      echo $e->getMessage();
      exit;
      /* Database connection is now back in autocommit mode */
    }

  }

  public function getFinderPostTitleAndUserNumber()
  {
    //$stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit FROM `post` INNER JOIN `usr_s1` on usr_s1.u_id=post.p_uid WHERE `p_id` = :pfd_pid");
    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post_select.ps_pid,post.p_tit FROM `post_select` INNER JOIN `usr_s1` on usr_s1.u_id=post_select.ps_fxid INNER JOIN `post` on post.p_id = post_select.ps_pid WHERE `ps_pid` = :pfd_pid");
    
      $stmt->bindParam(':pfd_pid', $this->pfd_pid);
      //$stmt->bindParam(':pfd_fxid', $this->pfd_fxid);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      return $data;
  }

}
?>
