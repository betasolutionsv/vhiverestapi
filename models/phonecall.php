<?php

class PhoneCall {

  private $dbConnect;
  private $tableName = 'post_call';

  private $pc_pid;
  private $pc_fxid;
  private $pc_body;
  private $pc_fdid;
  private $m_dt;

  function setPcid($pc_pid) { $this->pc_pid = $pc_pid; }
  function getPcid() { return $this->pc_pid; }
  function setPfxid($pc_fxid) { $this->pc_fxid = $pc_fxid; }
  function getPfxid() { return $this->pc_fxid; }
  function setPfdid($pc_fdid) { $this->pc_fdid = $pc_fdid; }
	function getPfdid() { return $this->pc_fdid; }
	function setPbody($pc_body) { $this->pc_body = $pc_body; }
  function getPbody($pc_body) { $this->pc_body = $pc_body; }
  function setMdt($m_dt) { $this->m_dt = $m_dt; }
  function getMdt() { return $this->m_dt; }

  function setrid($rid) { $this->rid = $rid; }
	function setpid($pid) { $this->pid = $pid; }
	function setsid($sid) { $this->sid = $sid; }
	function setuid($uid) { $this->uid = $uid; }

  public function __construct() {
    $db = new Database();
    $this->dbConnect = $db->connect();
  }

  public function createCall() {

    if($this->pc_pid == 0){

			$this->p_id = '0';
			$this->p_tit = "No Post";
			$this->p_jd = "No Post";
			$this->p_catid = '0';
			$this->p_ctyp = '0';
			$this->p_loc = '0';
			$this->p_priority = '0';


		//	echo $this->p_id.'&'.$this->p_tit.'&'.$this->p_jd.'&'.$this->p_catid;
			 $sql1 = 'INSERT INTO `post` SET `p_id` = :p_id,`p_tit` = :p_tit, `p_jd` = :p_jd, `p_uid` = :pc_fxid,`p_catid` = :p_catid, `p_ctyp` = :p_ctyp, `p_loc` = :p_loc, `p_priority` = :p_priority';
			$stmt1 = $this->dbConnect->prepare($sql1);

			$stmt1->bindParam(':p_id', $this->p_id);
			$stmt1->bindParam(':p_tit', $this->p_tit);
			$stmt1->bindParam(':p_jd', $this->p_jd);
			$stmt1->bindParam(':pc_fxid', $this->pc_fxid);
			$stmt1->bindParam(':p_catid', $this->p_catid);
			$stmt1->bindParam(':p_ctyp', $this->p_ctyp);
			$stmt1->bindParam(':p_loc', $this->p_loc);
			$stmt1->bindParam(':p_priority', $this->p_priority);

			$stmt1->execute();
			$lastid = $this->dbConnect->lastInsertId();

    $sql = 'INSERT INTO '.$this->tableName.' SET `pc_pid` = :pc_pid, `pc_body` = :pc_body, `pc_fxid` = :pc_fxid, `pc_fdid` = :pc_fdid, `pc_dt` = :m_dt';

    $stmt = $this->dbConnect->prepare($sql);

    $stmt->bindParam(':pc_pid', $this->pc_pid);
    $stmt->bindParam(':pc_body', $this->pc_body);
    $stmt->bindParam(':pc_fxid', $this->pc_fxid);
    $stmt->bindParam(':pc_fdid', $this->pc_fdid);
    $stmt->bindParam(':m_dt', $this->m_dt);


    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }
  }else{
    $sql = 'INSERT INTO '.$this->tableName.' SET `pc_pid` = :pc_pid, `pc_body` = :pc_body, `pc_fxid` = :pc_fxid, `pc_fdid` = :pc_fdid, `pc_dt` = :m_dt';

    $stmt = $this->dbConnect->prepare($sql);

    $stmt->bindParam(':pc_pid', $this->pc_pid);
    $stmt->bindParam(':pc_body', $this->pc_body);
    $stmt->bindParam(':pc_fxid', $this->pc_fxid);
    $stmt->bindParam(':pc_fdid', $this->pc_fdid);
    $stmt->bindParam(':m_dt', $this->m_dt);

    if($stmt->execute()) {
      return true;
    } else {
      return false;
    }

  }
  }

  //get calls
	public function getCalls(){
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE pc_pid=:pid AND ((pc_fxid=:pc_fxid AND pc_fdid=:pc_fdid) OR (pc_fxid=:pc_fdid AND pc_fdid=:pc_fxid)) ORDER BY pc_id ASC';
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':pc_pid',$this->pc_pid);
		$stmt->bindParam(':pc_fxid',$this->pc_fxid);
		$stmt->bindParam(':pc_fdid',$this->pc_fdid);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

  public function getPostTitleAndUserNumber()
  {
    $stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit,cat_name FROM `post` INNER JOIN `usr_s1` on usr_s1.u_id=post.p_uid INNER JOIN category on category.cat_id = usr_s1.u_pfn WHERE `p_id` = :m_pid");
      $stmt->bindParam(':m_pid', $this->pc_pid);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      return $data;
  }
}

?>
