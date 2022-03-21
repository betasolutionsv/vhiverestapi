<?php

class PostSelect {

  private $dbConnect;
  private $tableName = 'post_select';
  private $tableName1 = 'usr_s1';

  private $ps_pid;
  private $ps_fdid;
  private $ps_fxid;
  private $ps_stat;
  private $ps_id;
  private $ps_dt;
  private $fdate;
  private $ftime;
  private $flocation;
  private $fprice;
  private $fphone;

  function setPid($ps_pid) { $this->ps_pid = $ps_pid; }
  function getPid() { return $this->ps_pid; }
  function setPfid($ps_fdid) { $this->ps_fdid = $ps_fdid; }
  function getPfid() { return $this->ps_fdid; }
  function setPfxid($ps_fxid) { $this->ps_fxid = $ps_fxid; }
  function getPfxidt() { return $this->ps_fxid; }
  function setPstat($ps_stat) { $this->ps_stat = $ps_stat; }
  function getPstat() { return $this->ps_stat; }
  function setpsid($ps_id) { $this->ps_id = $ps_id; }
  function getpsid() { return $this->ps_id; }
  function setUpdt($ps_dt){ $this->ps_dt = $ps_dt; }
  function setFdate($fdate){ $this->fdate = $fdate; }
  function setFtime($ftime){ $this->ftime = $ftime; }
  function setFlocation($flocation){ $this->flocation = $flocation; }
  function setFprice($fprice){ $this->fprice = $fprice; }
  function setFphone($fphone){ $this->fphone = $fphone; }




  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function insertRecord() {

    $sql = 'INSERT INTO '.$this->tableName.' SET `ps_pid` = :ps_pid, `ps_fdid` = :ps_fdid,`ps_fxid` = :ps_fxid, `ps_stat` = :ps_stat, `ps_dt` = :ps_dt';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':ps_pid', $this->ps_pid);
    $stmt->bindParam(':ps_fdid', $this->ps_fdid);
    $stmt->bindParam(':ps_fxid', $this->ps_fxid);
    $stmt->bindParam(':ps_stat', $this->ps_stat);
    $stmt->bindParam(':ps_dt', $this->ps_dt);

    if($stmt->execute()) {

      $insid   = $this->dbConnect->lastInsertId();

      $sql = "INSERT INTO `post_select_status` SET `ps_id` = :ps_id, `finder` = '0',`fixer` = '0'";
      $stmt = $this->dbConnect->prepare($sql);
      $stmt->bindParam(':ps_id', $insid);
      $stmt->execute();

      $this->getfixerselstatus();
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
      $this->getfixerselstatus();
     return true;
    } else {
      return false;
    }
  }


  public function getfixerselstatus(){

    //echo "SELECT ps_stat FROM ".$this->tableName." WHERE `ps_fxid` = :ps_fxid";

    $stmt = $this->dbConnect->prepare("SELECT ps_stat FROM ".$this->tableName." WHERE `ps_fxid` = :ps_fxid");
    $stmt->bindParam(":ps_fxid",$this->ps_fxid);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

   // echo $this->ps_fxid; exit;

    //print_r($data); exit;

    if($data['ps_stat'] == '0'){

       $this->sendmsgtofixerselected();

        return true;

    }else if($data['ps_stat'] == '2'){

      $this->sendmsgtofixerunselected();

        return true;
    }
    else if($data['ps_stat'] == '1'){

      $this->sendmsgtofixercompleted();

        return true;
    }
    else{

      return false;
    }


  }


  public function sendmsgtofixerselected(){

   //echo "SELECT usr_s1.u_phn,post_select.p_tit FROM `post_select` INNER JOIN `post` on post.p_id=post_select.ps_pid INNER JOIN `usr_s1` on usr_s1.u_id=post_select.ps_fxid WHERE `u_id` = :ps_fxid";

    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit FROM `post_select` INNER JOIN `post` on post.p_id=post_select.ps_pid INNER JOIN `usr_s1` on usr_s1.u_id=post_select.ps_fxid WHERE `u_id` = :ps_fxid AND `p_id` = :ps_pid");
    $stmt->bindParam(':ps_fxid',$this->ps_fxid);
    $stmt->bindParam(':ps_pid', $this->ps_pid);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

   //print_r($data); exit;

    if($data){

     //  $title = $data['p_tit'];

     // $msg = urlencode("GOFFIX: %0A You have selected for this post-".$title);
     //  $url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$data['u_phn']."&from=GOFFIX&message=".$msg;
     //  $this->sendMsg($url);

        return true;

    }else{

        return false;
    }

  }

   public function sendmsgtofixerunselected(){

     $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit FROM `post_select` INNER JOIN `post` on post.p_id=post_select.ps_pid INNER JOIN `usr_s1` on usr_s1.u_id=post_select.ps_fxid WHERE `u_id` = :ps_fxid AND `p_id` = :ps_pid");
    $stmt->bindParam(":ps_fxid",$this->ps_fxid);
    $stmt->bindParam(':ps_pid', $this->ps_pid);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($data){

      $title = $data['p_tit'];

      $msg = urlencode("GOFFIX: %0A You have Unselected for this post-".$title);
     $url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$data['u_phn']."&from=GOFFIX&message=".$msg;
      $this->sendMsg($url);

        return true;

    }else{

        return false;
    }

  }

  public function sendMsg($url)
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
      // This is what solved the issue (Accepting gzip encoding)
      curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
      $msgresponse = curl_exec($ch);
      curl_close($ch);
      return $msgresponse;
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

  public function getPostTitleAndUserNumber()
  {
    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit,category.cat_name FROM `post_select` INNER JOIN `usr_s1` on usr_s1.u_id=post_select.ps_fdid INNER JOIN category on category.cat_id = usr_s1.u_pfn INNER JOIN `post` on post.p_id = post_select.ps_pid WHERE `ps_pid` = :ps_pid");
      $stmt->bindParam(':ps_pid', $this->ps_pid);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      return $data;
  }

  public function getPostTitleAndUserNumberfixer()
  {
    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit,category.cat_name FROM `post_select` INNER JOIN `usr_s1` on usr_s1.u_id=post_select.ps_fxid INNER JOIN category on category.cat_id = usr_s1.u_pfn INNER JOIN `post` on post.p_id = post_select.ps_pid WHERE `ps_pid` = :ps_pid");
      $stmt->bindParam(':ps_pid', $this->ps_pid);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      return $data;
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
       $this->getfixerselstatus();
      return true;
    } else {
      return false;
    }
  }

  public function fixer_show_activejobs(){
    $stmt = $this->dbConnect->prepare("SELECT p_tit,cat_name,ps_id,u_nm,u_img,u_gender,u_id AS fdid,p_id FROM `post_select` INNER JOIN `post` ON post_select.ps_pid = post.p_id INNER JOIN `category` ON post.p_catid = category.cat_id INNER JOIN `usr_s1` ON usr_s1.u_id = post.p_uid INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id WHERE post_select.ps_stat='0' AND post_select.ps_fxid=:ps_fxid AND post.p_id NOT IN (SELECT pfx_pid FROM `pfx_stat` WHERE pfx_fxid =:ps_fxid) ORDER BY post_select.ps_dt DESC");

    $stmt->bindParam(":ps_fxid",$this->ps_fxid);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;

  }

  public function fixer_show_activejobsnew(){
    $stmt = $this->dbConnect->prepare("SELECT p_tit,cat_name,ps_id,u_nm,u_img,u_gender,u_id AS fdid,ps_id,ps_pid FROM `post_select` INNER JOIN `post` ON post_select.ps_pid= post.p_id INNER JOIN `category` ON post.p_catid = category.cat_id INNER JOIN `usr_s1` ON usr_s1.u_id=post.p_uid INNER JOIN `pfd_stat` ON pfd_stat.pfd_pid=post.p_id  WHERE post_select.ps_fxid=:ps_fxid AND pfd_stat.pfd_pid=:ps_id AND post_select.ps_stat='0'  AND p_id NOT IN (SELECT pfx_stat.pfx_pid FROM `pfx_stat`) ");

    $stmt->bindParam(":ps_fxid",$this->ps_fxid);
    $stmt->bindParam(":ps_id",$this->ps_id);

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;

  }

  public function createFindersubmitform() {

    // echo $this->ps_pid.'&'.$this->ps_fdid.'&'.$this->ps_fxid.'&'.$this->ps_stat.'&'.$this->fdate.'&'.$this->ftime.'&'.$this->flocation.'&'.$this->fprice;
    // exit;
    $sql = 'INSERT INTO `finder_submit` SET `ps_pid` = :ps_pid, `ps_fdid` = :ps_fdid,`ps_fxid` = :ps_fxid, `fdate` = :fdate, `ftime` = :ftime, `flocation` = :flocation, `fprice` = :fprice, `fphone` = :fphone';
    $stmt = $this->dbConnect->prepare($sql);
    $stmt->bindParam(':ps_pid', $this->ps_pid);
    $stmt->bindParam(':ps_fdid', $this->ps_fdid);
    $stmt->bindParam(':ps_fxid', $this->ps_fxid);
    $stmt->bindParam(':fdate', $this->fdate);
    $stmt->bindParam(':ftime', $this->ftime);
    $stmt->bindParam(':flocation', $this->flocation);
    $stmt->bindParam(':fprice', $this->fprice);
    $stmt->bindParam(':fphone', $this->fphone);

    if($stmt->execute()) {

      $fsid   = $this->dbConnect->lastInsertId();
      return $fsid;
    } else {
      return false;
    }
  }

  public function getfinderdetails()
  {
    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,finder_submit.fdate,finder_submit.ftime,finder_submit.flocation,finder_submit.fprice,finder_submit.fphone FROM `post` INNER JOIN `usr_s1` on usr_s1.u_id=post.p_uid INNER JOIN finder_submit on finder_submit.ps_fxid = usr_s1.u_id WHERE  `p_uid` = :ps_fxid and `ps_pid` = :ps_pid");
      $stmt->bindParam(':ps_pid', $this->ps_pid);
      $stmt->bindParam(':ps_fxid', $this->ps_fxid);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      //print_r($data); exit;
      return $data;
  }

}?>
