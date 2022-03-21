<?php

	class BookService {

		private $dbConnect;
		private $tableName = 'Book_service';

		private $bs_uid;
        private $bs_cid;
        private $bs_lid;
        private $bs_loc;
        private $bs_bdt;
        private $bs_time;
        private $bs_phn;
        private $bs_desc;
        private $bs_uuid; 
        private $bs_dt;


		function setBsuid($bs_uid) { $this->bs_uid = $bs_uid; }
		function getBsuid() { return $this->bs_uid; }

		function setBscid($bs_cid) { $this->bs_cid = $bs_cid; }
		function getBscid() { return $this->bs_cid; }
        function setBslid($bs_lid) { $this->bs_lid = $bs_lid; }
        function getBslid() { return $this->bs_lid; }
        function setBsloc($bs_loc) { $this->bs_loc = $bs_loc; }
        function getBsloc() { return $this->bs_loc; }
        function setBsbdt($bs_bdt) { $this->bs_bdt = $bs_bdt; }
        function getBsbdt() { return $this->bs_bdt; }
        function setBstime($bs_time) { $this->bs_time = $bs_time; }
        function getBstime() { return $this->bs_time; }
		function setBsIsPrem($bs_isPrem) { $this->bs_isPrem = $bs_isPrem; }
        function getBsIsPrem() { return $this->bs_isPrem; }
        function setBsphn($bs_phn) { $this->bs_phn = $bs_phn; }
        function getBsphn() { return $this->bs_phn; }
        function setBsdesc($bs_desc) { $this->bs_desc = $bs_desc; }
        function getBsdesc() { return $this->bs_desc; }
        function setBsuuid($bs_uuid) { $this->bs_uuid = $bs_uuid; }
        function getBsuuid() { return $this->bs_uuid; }
        function setBsdt($bs_dt) { $this->bs_dt = $bs_dt; }
        function getBsdt() { return $this->bs_dt; }

		public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

		public function createBookService() {
            $query = "INSERT INTO " . $this->tableName . " (bs_uid, bs_cid, bs_lid, bs_loc, bs_bdt, bs_time, bs_phn, bs_desc, bs_uuid, bs_dt,bs_isPrem) VALUES (:bs_uid, :bs_cid, :bs_lid, :bs_loc, :bs_bdt, :bs_time, :bs_phn, :bs_desc, :bs_uuid, :bs_dt,:bs_isPrem)";
            $stmt = $this->dbConnect->prepare($query);
            $stmt->bindParam(':bs_uid', $this->bs_uid);
            $stmt->bindParam(':bs_cid', $this->bs_cid);
            $stmt->bindParam(':bs_lid', $this->bs_lid);
            $stmt->bindParam(':bs_loc', $this->bs_loc);
            $stmt->bindParam(':bs_bdt', $this->bs_bdt);
            $stmt->bindParam(':bs_time', $this->bs_time);
			$stmt->bindParam(':bs_isPrem', $this->bs_isPrem);
            $stmt->bindParam(':bs_phn', $this->bs_phn);
            $stmt->bindParam(':bs_desc', $this->bs_desc);
            $stmt->bindParam(':bs_uuid', $this->bs_uuid);
            $stmt->bindParam(':bs_dt', $this->bs_dt);
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
