<?php

	class UserConfig {

		private $dbConnect;

		private $tableName1 = 'usr_s1';
		private $u_id;
		private $u_img;
		private $u_desc;

		function setUid($u_id) { $this->u_id = $u_id; }
		function getUid() { return $this->u_id; }
		function setUimg($u_img) { $this->u_img = $u_img; }
		function getUimg() { return $this->u_img; }
		function setUdesc($u_desc) { $this->u_desc = $u_desc; }
		function getUdesc() { return $this->u_desc; }

		private $tableName2 = 'usr_s2';

		private $us_id;
    	private $us_sid;
		private $us_exp ;
		private $us_proof;
		private $us_prfid;
    	private $us_typ;
		private $us_lang;

		function setUsid($us_id) { $this->us_id = $us_id; }
		function getUsid() { return $this->us_id; }
		function setUexp($us_exp) { $this->us_exp = $us_exp; }
		function getUexp() { return $this->us_exp; }
		function setUproof($us_proof) { $this->us_proof = $us_proof; }
		function getUproof() { return $this->us_proof; }
		function setUprfid($us_prfid) { $this->us_prfid = $us_prfid; }
		function getUprfid() { return $this->us_prfid; }
		function setUtyp($us_typ) { $this->us_typ = $us_typ; }
		function getUtyp() { return $this->us_typ; }
		function setUlang($us_lang) { $this->us_lang = $us_lang; }
		function getUlang() { return $this->us_lang; }

		private $tableName3 = 'u_intrs';

		private $ui_id;
		private $ui_intr;

		function setUiid($ui_id) { $this->ui_id = $ui_id; }
    	function getUiid() { return $this->ui_id; }
		function setUintr($ui_intr) { $this->ui_intr = $ui_intr; }
		function getUintr() { return $this->ui_intr; }

		public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

		public function createUserConfig() {
			
			try{

				/* Begin a transaction, turning off autocommit */
				$this->dbConnect->beginTransaction();

				/* Change the database schema and data */

				$sql = 'UPDATE '.$this->tableName1.' SET `u_desc` = :u_desc WHERE `u_id` = :u_id';
				$stmt = $this->dbConnect->prepare($sql);
				$stmt->bindParam(':u_id', $this->u_id);
				$stmt->bindParam(':u_desc', $this->u_desc);
				$stmt->execute();

				$sql = 'INSERT INTO '.$this->tableName2.' SET `us_uid` = :us_uid,`us_exp` = :us_exp,`us_proof` = :us_proof, `us_prfid` = :us_prfid, `us_typ` = :us_typ, `us_lang` = :us_lang';
				$stmt = $this->dbConnect->prepare($sql);
				$stmt->bindParam(':us_uid', $this->u_id);
				$stmt->bindParam(':us_exp', $this->us_exp);
				$stmt->bindParam(':us_proof', $this->us_proof);
				$stmt->bindParam(':us_prfid', $this->us_prfid);
				$stmt->bindParam(':us_typ', $this->us_typ);
				$stmt->bindParam(':us_lang', $this->us_lang);
				$stmt->execute();

				$length = count($this->ui_intr);
				for ($i = 0; $i < $length; $i++) {
					$sql = 'INSERT INTO '.$this->tableName3.' SET `ui_uid` = :ui_uid, `ui_intr` = :ui_intr';
					$stmt = $this->dbConnect->prepare($sql);
					$stmt->bindParam(':ui_uid', $this->u_id);
					$stmt->bindParam(':ui_intr', $this->ui_intr[$i]);
					$stmt->execute();
				}

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

		public function saveProfileImage() {

			$sql = 'UPDATE '.$this->tableName1.' SET `u_img` = :u_img WHERE `u_id` = :u_id';

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->bindParam(':u_img', $this->u_img);

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function removeProfileImage() {

			$sql = 'UPDATE '.$this->tableName1.' SET `u_img` = NULL WHERE `u_id` = :u_id';

			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(':u_id', $this->u_id);

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function updateProfileDescription(){

			$sql = 'UPDATE '.$this->tableName1.' SET `u_desc` = :u_desc WHERE `u_id` = :u_id';

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->bindParam(':u_desc', $this->u_desc);

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

	}

?>
