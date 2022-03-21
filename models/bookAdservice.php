<?php

	class BookAdService {

		private $dbConnect;
		private $tableName = 'Book_Adservice';

		private $ba_uid;
        private $ba_cid;
        private $ba_lid;
        private $ba_loc;
        private $ba_bdt;
        private $ba_time;
        private $ba_phn;
        private $ba_desc;
        private $ba_uuid; 
        private $ba_dt;


		function setBauid($ba_uid) { $this->ba_uid = $ba_uid; }
		function getBauid() { return $this->ba_uid; }		
        function setBaaid($ba_aid) { $this->ba_aid = $ba_aid; }
		function getBaaid() { return $this->ba_aid; }
        function setBalid($ba_lid) { $this->ba_lid = $ba_lid; }
        function getBalid() { return $this->ba_lid; }
        function setBaloc($ba_loc) { $this->ba_loc = $ba_loc; }
        function getBaloc() { return $this->ba_loc; }
        function setBabdt($ba_bdt) { $this->ba_bdt = $ba_bdt; }
        function getBabdt() { return $this->ba_bdt; }
        function setBatime($ba_time) { $this->ba_time = $ba_time; }
        function getBatime() { return $this->ba_time; }
        function setBaphn($ba_phn) { $this->ba_phn = $ba_phn; }
        function getBaphn() { return $this->ba_phn; }
        function setBadesc($ba_desc) { $this->ba_desc = $ba_desc; }
        function getBadesc() { return $this->ba_desc; }
        function setBauuid($ba_uuid) { $this->ba_uuid = $ba_uuid; }
        function getBauuid() { return $this->ba_uuid; }
        function setBadt($ba_dt) { $this->ba_dt = $ba_dt; }
        function getBadt() { return $this->ba_dt; }

		public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

		public function createBookService() {
            $query = "INSERT INTO " . $this->tableName . " (ba_uid, ba_aid, ba_lid, ba_loc, ba_bdt, ba_time, ba_phn, ba_desc, ba_uuid, ba_dt) VALUES (:ba_uid, :ba_aid, :ba_lid, :ba_loc, :ba_bdt, :ba_time, :ba_phn, :ba_desc, :ba_uuid, :ba_dt)";
            $stmt = $this->dbConnect->prepare($query);
            $stmt->bindParam(':ba_uid', $this->ba_uid);
            $stmt->bindParam(':ba_aid', $this->ba_aid);
            $stmt->bindParam(':ba_lid', $this->ba_lid);
            $stmt->bindParam(':ba_loc', $this->ba_loc);
            $stmt->bindParam(':ba_bdt', $this->ba_bdt);
            $stmt->bindParam(':ba_time', $this->ba_time);
            $stmt->bindParam(':ba_phn', $this->ba_phn);
            $stmt->bindParam(':ba_desc', $this->ba_desc);
            $stmt->bindParam(':ba_uuid', $this->ba_uuid);
            $stmt->bindParam(':ba_dt', $this->ba_dt);
            $stmt->execute();
            $data = $this->dbConnect->lastInsertId();
			return $data;
        

			
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
