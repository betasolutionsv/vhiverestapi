<?php

	class User {

    	private $dbConnect;
		private $tableName 	= 'usr_s1';
		private $tableName2 = 'usr_s2';
		private $otpTable 	= 'otp_auth';

		private $u_id;
    	private $u_nm;
		private $u_phn;
		private $u_email;
		private $u_pwd;
		private $u_city;
		private $u_pfn;
		private $u_desc;
		private $u_dt;
		private $u_gender;
		private $otp;
		private $otp_res;
		private $otp_type;
		private $pushTokenId;
		private $u_prof;
		private $u_cat;
		private $loc_name;


		function setUid($u_id) { $this->u_id = $u_id; }
		function getUid() { return $this->u_id; }
		function setUnm($u_nm) { $this->u_nm = $u_nm; }
		function getUnm() { return $this->u_nm; }
		function setUphn($u_phn) { $this->u_phn = $u_phn; }
		function getUphn() { return $this->u_phn; }
		function setUemail($u_email) { $this->u_email = $u_email; }
		function getUemail() { return $this->u_email; }
		function setUpwd($u_pwd) { $this->u_pwd = $u_pwd; }
		function getUpwd() { return $this->u_pwd; }
		function setUcity($u_city) { $this->u_city = $u_city; }
		function getUcity() { return $this->u_city; }
		function setUpfn($u_pfn) { $this->u_pfn = $u_pfn; }
		function getUpfn() { return $this->u_pfn; }
		function setUdesc($u_desc) { $this->u_desc = $u_desc; }
		function getUdesc() { return $this->u_desc; }
		function setUimg($u_img) { $this->u_img = $u_img; }
		function getUimg() { return $this->u_img; }
		function setUpdt($u_dt) { $this->u_dt = $u_dt; }
		function getUpdt() { return $this->u_dt; }
		function setUgen($u_gender) { $this->u_gender = $u_gender; }
		function getUgen() { return $this->u_gender; }
		function setOTP($otp) { $this->otp = $otp; }
		function setOtpres($otp_res) { $this->otp_res = $otp_res; }
		function setOtptype($otp_type) { $this->otp_type = $otp_type; }
		function setPushTokenid($pushTokenId){$this->pushTokenId = $pushTokenId;}

		function setUProf($u_prof){$this->u_prof = $u_prof;}
		function getUProf() { return $this->u_prof; }
		function setUCat($u_cat){$this->u_cat = $u_cat;}
		function getUCat() { return $this->u_cat; }
		function setloc_name($loc_name) { $this->loc_name = $loc_name; }

		public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

		public function createUser() {

			$sql = 'INSERT INTO '.$this->tableName.' SET `u_nm` = :u_nm, `u_phn` = :u_phn,`u_email` = :u_email, `u_pwd` = :u_pwd,`u_city` = :u_city, `u_pfn` = :u_pfn, `u_gender` = :u_gender, `u_dt` = :u_dt';

			$pwd = password_hash($this->u_pwd,PASSWORD_BCRYPT);

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':u_nm', $this->u_nm);
			$stmt->bindParam(':u_phn', $this->u_phn);
			$stmt->bindParam(':u_email', $this->u_email);
			$stmt->bindParam(':u_pwd', $pwd);
			$stmt->bindParam(':u_city', $this->u_city);
			$stmt->bindParam(':u_pfn', $this->u_pfn);
			$stmt->bindParam(':u_gender', $this->u_gender);
			$stmt->bindParam(':u_dt', $this->u_dt);
			$stmt->execute();

			$data = $this->dbConnect->lastInsertId();
			return $data;

		}

		//Update User

		public function UpdateUser() {

			$sql = 'UPDATE '.$this->tableName.' SET `u_nm` = :u_nm,`u_email` = :u_email,`u_desc` = :u_desc,`u_pfn` = :u_pfn,`u_img` = :u_img  WHERE `u_id` = :u_id';

			//$pwd = password_hash($this->u_pwd,PASSWORD_BCRYPT);

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':u_nm', $this->u_nm);
			$stmt->bindParam(':u_email', $this->u_email);
			$stmt->bindParam(':u_desc', $this->u_desc);
			$stmt->bindParam(':u_pfn', $this->u_pfn);
			$stmt->bindParam(':u_img', $this->u_img);
			$stmt->bindParam(':u_id', $this->u_id);

			//$stmt->bindParam(':u_email', $this->u_email);
			//$stmt->bindParam(':u_pwd', $pwd);
			//$stmt->bindParam(':u_city', $this->u_city);
			//$stmt->bindParam(':u_pfn', $this->u_pfn);
			//$stmt->execute();

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function checkUserExist(){

			$stmt = $this->dbConnect->prepare("SELECT `u_id`,`u_pwd`,`u_phn`,`u_gender`,`u_mob_verify` FROM `usr_s1`  WHERE u_phn = :u_phn");
			$stmt->bindParam(":u_phn",$this->u_phn);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		public function checkandgetloginuser()
		{
			$stmt = $this->dbConnect->prepare("SELECT `u_id`,`u_phn` FROM ".$this->tableName." WHERE u_id = :u_id");
			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		public function checknextstepsignup()
		{
			$stmt = $this->dbConnect->prepare("SELECT `us_uid`,`us_lang` FROM ".$this->tableName2." WHERE us_uid = :u_id");
			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		public function checkMobileNumberExist(){

			$stmt = $this->dbConnect->prepare("SELECT * FROM ".$this->tableName." WHERE u_phn = :u_phn");
			$stmt->bindParam(":u_phn", $this->u_phn);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);

			if(!is_array($data)) {
				return false;
			} else {
				return true;
			}
		}

		// public function checkEmailExist(){

		// 	$stmt = $this->dbConnect->prepare("SELECT * FROM ".$this->tableName." WHERE u_email = :u_email");
		// 	$stmt->bindParam(":u_email", $this->u_email);
		// 	$stmt->execute();
		// 	$data = $stmt->fetch(PDO::FETCH_ASSOC);

		// 	if(!is_array($data)) {
		// 		return false;
		// 	} else {
		// 		return true;
		// 	}
		// }

		public function generateOTP()
		{
			$this->deleteoldotpbyuserid();
			$otp 		=		password_hash($this->otp,PASSWORD_BCRYPT);

			$sql = 'INSERT INTO '.$this->otpTable.' SET `u_id` = :u_id, `otp` = :otp,`otp_type` = :otp_type, `otp_date` = :otp_date';

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->bindParam(':otp', $otp);
			$stmt->bindParam(':otp_date', $this->u_dt);
			$stmt->bindParam(':otp_type', $this->otp_type);
			$stmt->execute();

			$data = $this->dbConnect->lastInsertId();
			return $data;
		}

		public function saveresponsefrommsg($otpresponse,$otpid)
		{
			$sql = "UPDATE ".$this->otpTable." SET `otp_response` = '".$otpresponse."' WHERE `otp_id` = ".$otpid;
			$stmt = $this->dbConnect->prepare($sql);
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function deleteoldotpbyuserid()
		{
			$sql = 'DELETE FROM '.$this->otpTable.' WHERE `u_id` = :u_id AND otp_type=:otp_type';
			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->bindParam(':otp_type', $this->otp_type);
			$stmt->execute();
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function checkotpuser()
		{
			$stmt = $this->dbConnect->prepare("SELECT `otp`,`otp_date`,`otp_verify_status` FROM ".$this->otpTable." WHERE otp_type =:otp_type AND u_id=:u_id AND otp_response=:otp_res");
			$stmt->bindParam(":u_id", $this->u_id);
			$stmt->bindParam(":otp_res", $this->otp_res);
			$stmt->bindParam(":otp_type", $this->otp_type);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		public function changeuserverifystatus()
		{
			$sql = "UPDATE ".$this->otpTable. " SET `otp_verify_status` = 1 WHERE otp_type =:otp_type AND u_id=:u_id AND otp_response=:otp_res";
			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(":u_id", $this->u_id);
			$stmt->bindParam(":otp_res", $this->otp_res);
			$stmt->bindParam(":otp_type", $this->otp_type);
			$stmt->execute();

			if($this->otp_type==1){
				$sql 	= "UPDATE ".$this->tableName. " SET `u_mob_verify` = 1 WHERE `u_id` = :u_id";
				$stmt 	= $this->dbConnect->prepare($sql);
				$stmt->bindParam(':u_id', $this->u_id);
			}
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function changePassword()
		{
			$pwd = password_hash($this->u_pwd,PASSWORD_BCRYPT);
			$sql = "UPDATE ".$this->tableName. " SET `u_pwd` = :u_pwd WHERE `u_id` = :u_id";
			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->bindParam(':u_pwd', $pwd);
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function getAllFixersByCatId()
		{
			$stmt = $this->dbConnect->prepare("SELECT `push_notify_id` FROM `".$this->tableName."` INNER JOIN `".$this->tableName2."` ON usr_s1.u_id=usr_s2.us_uid WHERE usr_s1.u_pfn=:u_cat AND usr_s2.us_typ=:u_prof AND usr_s1.push_notify_id!=''");
			$stmt->bindParam(':u_prof', $this->u_prof);
			$stmt->bindParam(':u_cat', $this->u_cat);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
		}

		public function checkUserId()
		{
			$stmt = $this->dbConnect->prepare("SELECT `push_notify_id`,`u_nm`,`u_phn` FROM ".$this->tableName." WHERE u_id=:u_id");
			$stmt->bindParam(':u_id', $this->u_id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		public function add_pushtoken()
		{
			$checkUserid 	=	$this->checkUserId();
			if($this->pushTokenId!="" && $this->pushTokenId!=$checkUserid->push_notify_id){
				$sql = "UPDATE ".$this->tableName. " SET `push_notify_id` = :pushTokenId WHERE `u_id` = :u_id";
				$stmt = $this->dbConnect->prepare($sql);
				$stmt->bindParam(':u_id', $this->u_id);
				$stmt->bindParam(':pushTokenId', $this->pushTokenId);
				if($stmt->execute()) {
					return true;
				} else {
					return false;
				}
			}else{
				return true;
			}
		}

		public function getLocation_id()
		{
			$stmt = $this->dbConnect->prepare("SELECT `loc_id` FROM `location` WHERE loc_name=:loc_name");
			$stmt->bindParam(':loc_name', $this->loc_name);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		
	}
 ?>
