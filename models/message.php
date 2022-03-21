<?php

class Message {

	private $dbConnect;
	private $tableName = 'messages';

	private $m_body;
	private $m_pid;
	private $m_sid;
	private $m_rid;
	private $m_dt;

	private $p_id;
	private $p_tit;
	private $p_jd;
	private $p_catid;

	private $pid;
	private $rid;
	private $sid;
	private $uid;

	function setMbody($m_body) { $this->m_body = $m_body; }
	function getMbody() { return $this->m_body; }
	function setMpid($m_pid) { $this->m_pid = $m_pid; }
	function getMpid() { return $this->m_pid; }
	function setMsid($m_sid) { $this->m_sid = $m_sid; }
	function getMsid() { return $this->m_sid; }
	function setMrid($m_rid) { $this->m_rid = $m_rid; }
	function getMrid() { return $this->m_rid; }
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

	public function createMessage() {

		if($this->m_pid == 0){

			$this->p_id = '0';
			$this->p_tit = "No Post";
			$this->p_jd = "No Post";
			$this->p_catid = '0';
			$this->p_ctyp = '0';
			$this->p_loc = '0';
			$this->p_priority = '0';


		//	echo $this->p_id.'&'.$this->p_tit.'&'.$this->p_jd.'&'.$this->p_catid;
			 $sql1 = 'INSERT INTO `post` SET `p_id` = :p_id,`p_tit` = :p_tit, `p_jd` = :p_jd, `p_uid` = :m_rid,`p_catid` = :p_catid, `p_ctyp` = :p_ctyp, `p_loc` = :p_loc, `p_priority` = :p_priority';
			$stmt1 = $this->dbConnect->prepare($sql1);

			$stmt1->bindParam(':p_id', $this->p_id);
			$stmt1->bindParam(':p_tit', $this->p_tit);
			$stmt1->bindParam(':p_jd', $this->p_jd);
			$stmt1->bindParam(':m_rid', $this->m_rid);
			$stmt1->bindParam(':p_catid', $this->p_catid);
			$stmt1->bindParam(':p_ctyp', $this->p_ctyp);
			$stmt1->bindParam(':p_loc', $this->p_loc);
			$stmt1->bindParam(':p_priority', $this->p_priority);

			$stmt1->execute();
			$lastid = $this->dbConnect->lastInsertId();

			$sql = 'INSERT INTO '.$this->tableName.' SET `m_body` = :m_body,`m_pid` = :m_pid, `m_sid` = :m_sid, `m_rid` = :m_rid,`m_dt` = :m_dt';

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':m_body', $this->m_body);
			$stmt->bindParam(':m_pid', $lastid);
			$stmt->bindParam(':m_sid', $this->m_sid);
			$stmt->bindParam(':m_rid', $this->m_rid);
			$stmt->bindParam(':m_dt', $this->m_dt);

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}

		}else{

		$sql = 'INSERT INTO '.$this->tableName.' SET `m_body` = :m_body,`m_pid` = :m_pid, `m_sid` = :m_sid, `m_rid` = :m_rid,`m_dt` = :m_dt';

		$stmt = $this->dbConnect->prepare($sql);

		$stmt->bindParam(':m_body', $this->m_body);
		$stmt->bindParam(':m_pid', $this->m_pid);
		$stmt->bindParam(':m_sid', $this->m_sid);
		$stmt->bindParam(':m_rid', $this->m_rid);
		$stmt->bindParam(':m_dt', $this->m_dt);

		if($stmt->execute()) {
			return true;
		} else {
			return false;
		}
	}
}

	public function getMessageCount()
	{
		$sql = 'SELECT count(*) as msgcount FROM `messages` WHERE m_pid=:m_pid AND m_rid=:m_rid AND m_sid=:m_sid ORDER BY m_id ASC';
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':m_pid', $this->m_pid);
		$stmt->bindParam(':m_sid', $this->m_sid);
		$stmt->bindParam(':m_rid', $this->m_rid);

		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

	public function getPostTitleAndUserNumber()
	{
		$stmt = $this->dbConnect->prepare("SELECT usr_s1.u_phn,post.p_tit,cat_name FROM `post` INNER JOIN `usr_s1` on usr_s1.u_id=post.p_uid INNER JOIN category on category.cat_id = usr_s1.u_pfn WHERE  `p_id` = :m_pid");
	    $stmt->bindParam(':m_pid', $this->m_pid);
	    $stmt->execute();
	    $data = $stmt->fetch(PDO::FETCH_ASSOC);
	    //print_r($data); exit;
	    return $data;
	}

	//get messages
	public function getMessage(){
		if($this->pid == 0){
		$sql = 'SELECT * FROM `messages` WHERE ((m_rid=:uid AND m_sid=:rid) OR (m_rid=:rid AND m_sid=:uid)) ORDER BY m_id ASC';
		}else{
		$sql = 'SELECT * FROM `messages` WHERE m_pid=:pid AND ((m_rid=:uid AND m_sid=:rid) OR (m_rid=:rid AND m_sid=:uid)) ORDER BY m_id ASC';
		}
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':pid',$this->pid);
		$stmt->bindParam(':uid',$this->uid);
		$stmt->bindParam(':rid',$this->rid);

		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

	public function getMessagechatbody(){
		// echo $this->uid;
		// echo $this->pid; exit;
		$sql = "(SELECT * FROM `messages` WHERE m_pid=:pid AND (m_rid=:uid OR m_sid=:uid) ORDER BY m_dt DESC) UNION (SELECT * FROM `post_call` where pc_pid=:pid and (pc_fdid=:uid OR pc_fxid=:uid)) ";
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':pid',$this->pid);
		$stmt->bindParam(':uid',$this->uid);
		//$stmt->bindParam(':rid',$this->rid);

		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return $data;
	}


	//get Chat
	public function getChat(){

		$sql = "SELECT MAX(m_id) AS mid,m_sid,MAX(m_rid) AS m_rid,MAX(m_dt) AS dt,MAX(m_body)AS body,post.p_uid,r.u_nm FROM `messages` LEFT JOIN `post` ON messages.m_pid=post.p_id RIGHT JOIN `usr_s1` ON usr_s1.u_id=post.p_uid  INNER JOIN `usr_s1` AS r ON messages.m_sid=r.u_id WHERE post.p_uid=:uid AND m_pid=:pid AND m_sid NOT IN (SELECT m_sid FROM messages WHERE m_pid=:pid AND m_sid=:uid) GROUP BY m_sid ORDER BY MAX(m_dt) DESC";
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':pid',$this->pid);
		$stmt->bindParam(':uid',$this->uid);
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return $data;
	}

	public function checkNewMsgsCount()
	{
		$sql = "SELECT count(*) AS new_msgs_count FROM `messages` WHERE m_read_stat='0' AND m_rid=:uid";
		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':uid',$this->uid);

		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

	public function checkNewMsgsCountchange()
	{


		$sql = "UPDATE `messages` SET `m_read_stat` = '1' WHERE `m_rid` = :uid";
		//echo $sql; exit;
   		$stmt = $this->dbConnect->prepare($sql);
		$stmt->bindParam(':uid',$this->uid);

		//$stmt->execute();
		//$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//return $data;

    if($stmt->execute()) {
     // $this->getfixerselstatus();
     return true;
    } else {
      return false;
    }
	}



}

?>
