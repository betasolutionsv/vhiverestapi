<?php

class PostSelect {

  private $dbConnect;
  private $tableName = 'post_select';

  private $ps_pid;
  private $ps_fdid;
  private $ps_fxid;
  private $ps_stat;
  private $ps_id;

  function setPid($ps_pid) { $this->ps_pid = $ps_pid; }
  function getPid() { return $this->ps_pid; }
  function setPfid($ps_fdid) { $this->ps_fdid = $ps_fdid; }
  function getPfid() { return $this->ps_fdid; }
  function setPfxid($ps_fxid) { $this->ps_fxid = $ps_fxid; }
  function getPfxidt() { return $this->ps_fxid; }
  function setPstat($ps_stat) { $this->ps_stat = $ps_stat; }
  function getPstat() { return $this->ps_stat; }
  function setpsid($ps_id) { $this->ps_id = $ps_id; }



  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `ps_pid` = :ps_pid, `ps_fdid` = :ps_fdid,`ps_fxid` = :ps_fxid, `ps_stat` = :ps_stat';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':ps_pid', $this->ps_pid);
    $stmt->bindParam(':ps_fdid', $this->ps_fdid);
    $stmt->bindParam(':ps_fxid', $this->ps_fxid);
    $stmt->bindParam(':ps_stat', $this->ps_stat);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }

  public function updateRecord() {

    $sql = 'UPDATE '.$this->tableName.' SET `ps_stat` = :ps_stat WHERE `ps_pid` = :ps_pid AND `ps_fdid` = :ps_fdid AND `ps_fxid` = :ps_fxid';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':ps_pid', $this->ps_pid);
    $stmt->bindParam(':ps_fdid', $this->ps_fdid);
    $stmt->bindParam(':ps_fxid', $this->ps_fxid);
    $stmt->bindParam(':ps_stat', $this->ps_stat);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }
// SELECT p_tit,cat_name,ps_id FROM `post_select` INNER JOIN `post` ON post_select.ps_pid= post.p_id INNER JOIN `category` ON post.p_catid = category.cat_id WHERE post_select.ps_fxid='2' AND post_select.ps_stat=0
  public function recordExistUpdateOrNotExistInsert(){

    $stmt = $this->dbConnect->prepare("SELECT * FROM ".$this->tableName." WHERE (`ps_pid` = :ps_pid) AND (`ps_fdid` = :ps_fdid) AND (`ps_fxid` = :ps_fxid)");
    $stmt->bindParam(":ps_pid",$this->ps_pid);
    $stmt->bindParam(":ps_fdid",$this->ps_fdid);
    $stmt->bindParam(":ps_fxid",$this->ps_fxid);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($data){
      if($this->updateRecord()) {
        return true;
      } else {
        return false;
      }
    }else{
      if($this->insertRecord()) {
        return true;
      } else {
        return false;
      }
    }

  }


  public function denyPost() {

    if($this->updateRecord()) {
      return true;
    } else {
      return false;
    }
  }

  public function fixer_complete_postselect_stat(){
    $stmt = $this->dbConnect->prepare("UPDATE `post_select` SET `ps_stat`='1' WHERE `ps_id`=:ps_id");
    $stmt->bindParam(":ps_id",$this->ps_id);
    if($stmt->execute())
    {
      return true;
    } else {
      return false;
    }
  }

  public function fixer_show_activejobs(){
    // $stmt = $this->dbConnect->prepare("SELECT p_tit,cat_name,ps_id FROM `post_select` INNER JOIN `post` ON post_select.ps_pid= post.p_id INNER JOIN `category` ON post.p_catid = category.cat_id WHERE post_select.ps_fxid=:ps_fxid AND post_select.ps_stat='0'");
    $stmt = $this->dbConnect->prepare("SELECT p_tit,cat_name,ps_id,u_nm,u_img,u_id AS fdid,p_id FROM `post_select` INNER JOIN `post` ON post_select.ps_pid= post.p_id INNER JOIN `category` ON post.p_catid = category.cat_id INNER JOIN `usr_s1` ON usr_s1.u_id=post.p_uid WHERE post_select.ps_fxid=:ps_fxid AND post_select.ps_stat='0'  AND p_id NOT IN (SELECT pfx_stat.pfx_pid FROM `pfx_stat`) ");

    $stmt->bindParam(":ps_fxid",$this->ps_fxid);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;

  }

}
?>
