<?php

	class Post {

		private $dbConnect;
		private $tableName = 'post';

		private $p_uid;
		private $p_id;
		private $p_tit;
		private $p_jd ;
		private $p_catid;
		private $p_loc;
		private $p_ctyp;
		private $p_priority;
		private $p_amt;
		private $p_dt;
		// private $p_astat;

		function setPuid($p_uid) { $this->p_uid = $p_uid; }
		function getPuid() { return $this->p_uid; }
		function setPtid($p_id) { $this->p_id = $p_id; }
		function getPtid() { return $this->p_id; }
		function setPtit($p_tit) { $this->p_tit = $p_tit; }
		function getPtit() { return $this->p_tit; }
		function setPid($p_jd) { $this->p_jd = $p_jd; }
		function getPid() { return $this->p_jd; }
		function setPcatid($p_catid) { $this->p_catid = $p_catid; }
		function getPcatid() { return $this->p_catid; }
		function setPloc($p_loc) { $this->p_loc = $p_loc; }
		function getPloc() { return $this->p_loc; }
		function setPctyp($p_ctyp) { $this->p_ctyp = $p_ctyp; }
		function getPctyp() { return $this->p_ctyp; }
		function setPpri($p_priority) { $this->p_priority = $p_priority; }
		function getPpri() { return $this->p_priority; }
		function setPamt($p_amt) { $this->p_amt = $p_amt; }
		function getPamt() { return $this->p_amt; }
		function setPdt($p_dt) { $this->p_dt = $p_dt; }
		function getPdt() { return $this->p_dt; }
		// function setPastat($p_astat) { $this->p_astat = $p_astat; }
		// function getPastat() { return $this->p_astat; }

		public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

		public function createPost() {

			$sql = 'INSERT INTO '.$this->tableName.' SET `p_uid` = :p_uid,`p_tit` = :p_tit, `p_jd` = :p_jd,`p_catid` = :p_catid, `p_loc` = :p_loc,`p_ctyp` = :p_ctyp, `p_priority` = :p_priority, `p_amt` = :p_amt, `p_dt` = :p_dt ';

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':p_uid', $this->p_uid);
			$stmt->bindParam(':p_tit', $this->p_tit);
			$stmt->bindParam(':p_jd', $this->p_jd);
			$stmt->bindParam(':p_catid', $this->p_catid);
			$stmt->bindParam(':p_loc', $this->p_loc);
			$stmt->bindParam(':p_ctyp', $this->p_ctyp);
			$stmt->bindParam(':p_priority', $this->p_priority);
			$stmt->bindParam(':p_amt', $this->p_amt);
			$stmt->bindParam(':p_dt', $this->p_dt);
			$stmt->execute();
			
			return $this->dbConnect->lastInsertId();
		}

		function removeMyPost(){
			$sql = 'DELETE FROM '.$this->tableName.' WHERE `p_uid` = :p_uid AND `p_id`= :p_id';
			$sql1 = 'DELETE FROM `messages` WHERE `m_pid` = :p_id';
			$sql2 = 'DELETE FROM `pfd_stat` WHERE `pfd_pid` = :p_id';
			$sql3 = 'DELETE FROM `pfx_stat` WHERE `pfx_pid` = :p_id';
			$sql4 = 'DELETE FROM `post_call` WHERE `pc_pid` = :p_id';
			$sql5 = 'DELETE FROM `post_select` WHERE `ps_pid` = :p_id';
			$sql6 = 'DELETE FROM `saved_posts` WHERE `sp_pid` = :p_id';
			$sql7 = 'DELETE FROM `reported_posts` WHERE `rp_pid` = :p_id';
			$stmt = $this->dbConnect->prepare($sql);
			$stmt1 = $this->dbConnect->prepare($sql1);
			$stmt2 = $this->dbConnect->prepare($sql2);
			$stmt3 = $this->dbConnect->prepare($sql3);
			$stmt4 = $this->dbConnect->prepare($sql4);
			$stmt5 = $this->dbConnect->prepare($sql5);
			$stmt6 = $this->dbConnect->prepare($sql6);
			$stmt7 = $this->dbConnect->prepare($sql7);

			$stmt->bindParam(':p_uid', $this->p_uid);
			$stmt->bindParam(':p_id', $this->p_id);

			$stmt1->bindParam(':p_id', $this->p_id);

			$stmt2->bindParam(':p_id', $this->p_id);

			$stmt3->bindParam(':p_id', $this->p_id);

			$stmt4->bindParam(':p_id', $this->p_id);

			$stmt5->bindParam(':p_id', $this->p_id);

			$stmt6->bindParam(':p_id', $this->p_id);

			$stmt7->bindParam(':p_id', $this->p_id);

			$stmt1->execute();
			$stmt2->execute();
			$stmt3->execute();
			$stmt4->execute();
			$stmt5->execute();
			$stmt6->execute();
			$stmt7->execute();
					
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}
	}

?>
