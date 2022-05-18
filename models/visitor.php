<?php 
class vistor{
    private $dbConnect;
    private $V_table = "vsitor";    


    private $v_phn;
    private $v_pwd;
    private $v_nm;
    private $v_em;
	private $v_id;
	// private $v_rvid;
	// private $v_loc;
	// private $v_dep;
	// private $v_rel;
	// private $v_img;
	private $vl_dep;
	private $vl_hnm;
	private $vl_pov;
	private $vl_st;
	private $vl_id;
	private $vl_rid;
	
	private $v_img;
	private $v_rvid;
	private $v_loc;
	private $v_typ;
	private $v_gen;
	private $v_rid;

	private $p_snm;
	private $p_rel;
	private $p_dep;
	private $p_srn;

	private $g_com;
	private $g_aphn;
	private $g_eid;
	private $g_des;

	private $a_un;
	private $a_nm;
	private $a_pwd;
	private $a_dep;
	private $a_emp;
	private $a_id;









	

    function setVid($v_id) { $this->v_id = $v_id; }
		function getVid() { return $this->v_id; }
		function setVnm($v_nm) { $this->v_nm = $v_nm; }
		function getVnm() { return $this->v_nm; }
		function setVphn($v_phn) { $this->v_phn = $v_phn; }
		function getVphn() { return $this->v_phn; }
		function setVem($v_em) { $this->v_em = $v_em; }
		function getVem() { return $this->v_em; }
		function setVpwd($v_pwd) { $this->v_pwd = $v_pwd; }
		function getVpwd() { return $this->v_pwd; }
		// function setVrvid($v_rvid) { $this->v_rvid = $v_rvid; }
		// function getVrvid() { return $this->v_rvid; }
		// function setVloc($v_loc) { $this->v_loc = $v_loc; }
		// function getVloc() { return $this->v_loc; }
		// function setVdep($v_dep) { $this->v_dep = $v_dep; }
		// function getVdep() { return $this->v_dep; }
		// function setVrel($v_rel) { $this->v_rel = $v_rel; }
		// function getVrel() { return $this->v_rel; }
		// function setVimg($v_img) { $this->v_img = $v_img; }
		// function getVimg() { return $this->v_img; }
		function setVl_dep($vl_dep) { $this->vl_dep = $vl_dep; }
		function getVl_dep() { return $this->vl_dep; }
		function setVl_hnm($vl_hnm) { $this->vl_hnm = $vl_hnm; }
		function getVl_hnm() { return $this->vl_hnm; }
		function setVl_pov($vl_pov) { $this->vl_pov = $vl_pov; }
		function getVl_pov() { return $this->vl_pov; }
		function setVl_st($vl_st) { $this->vl_st = $vl_st; }
		function getVl_st() { return $this->vl_st; }
		function setVl_id($vl_id) { $this->vl_id = $vl_id; }
		function getVl_id() { return $this->vl_id; }
		function setVimg($v_img) { $this->v_img = $v_img; }
		function getVimg() { return $this->v_img; }
		function setVrvid($v_rvid) { $this->v_rvid = $v_rvid; }
		function getVrvid() { return $this->v_rvid; }
		function setVloc($v_loc) { $this->v_loc = $v_loc; }
		function getVloc() { return $this->v_loc; }
		function setPsnm($p_snm) { $this->p_snm = $p_snm; }
		function getPsnm() { return $this->p_snm; }
		function setPrel($p_rel) { $this->p_rel = $p_rel; }
		function getPrel() { return $this->p_rel; }
		function setPdep($p_dep) { $this->p_dep = $p_dep; }
		function getPdep() { return $this->p_dep; }
		function setPsrn($p_srn) { $this->p_srn = $p_srn; }
		function getPsrn() { return $this->p_srn; }
		function setVtyp($v_typ) { $this->v_typ = $v_typ; }
		function getVtyp() { return $this->v_typ; }
		function setVgen($v_gen) { $this->v_gen = $v_gen; }
		function getVgen() { return $this->v_gen; }
		function setGcom($g_com) { $this->g_com = $g_com; }
		function getGcom() { return $this->g_com; }
		function setGaphn($g_aphn) { $this->g_aphn = $g_aphn; }
		function getGaphn() { return $this->g_aphn; }
		function setGeid($g_eid) { $this->g_eid = $g_eid; }
		function getGeid() { return $this->g_eid; }
		function setGdes($g_des) { $this->g_des = $g_des; }
		function getGdes() { return $this->g_des; }
		function setVlrid($vl_rid) { $this->vl_rid = $vl_rid; }
		function getVlrid() { return $this->vl_rid; }
		function setAun($a_un) { $this->a_un = $a_un; }
		function getAun() { return $this->a_un; }
		function setAnm($a_nm) { $this->a_nm = $a_nm; }
		function getAnm() { return $this->a_nm; }
		function setApwd($a_pwd) { $this->a_pwd = $a_pwd; }
		function getApwd() { return $this->a_pwd; }
		function setAdep($a_dep) { $this->a_dep = $a_dep; }
		function getAdep() { return $this->a_dep; }
		function setAemp($a_emp) { $this->a_emp = $a_emp; }
		function getAemp() { return $this->a_emp; }
		function setaid($a_id) { $this->a_id = $a_id; }
		function getaid() { return $this->a_id; }
		


		

        public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

		//Login Model
        public function checkVisitorExist(){

			$stmt = $this->dbConnect->prepare("SELECT v_id,v_nm,v_phn,v_em,v_pwd,v_rvid FROM `vsitor` WHERE v_phn=:v_phn");
			$stmt->bindParam(":v_phn",$this->v_phn);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		// GetVistiorDetails
		public function getvistiordetails(){
			$stmt = $this->dbConnect->prepare("SELECT v_id,v_nm,v_phn,v_em FROM `vsitor` WHERE v_id=:v_id");
			$stmt->bindParam(":v_id",$this->v_id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		//Get Departments from DB
		public function getDepartments(){
			$stmt = $this->dbConnect->prepare("SELECT * FROM `department`");
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

		//Get location from DBS
		public function getLocation(){
			$stmt = $this->dbConnect->prepare("SELECT * FROM `location`");
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

		//Get relation from DB
		public function getRelation(){
			$stmt = $this->dbConnect->prepare("SELECT * FROM `relation`");
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

		//Get Hostnames form DBS
		public function getHostnames(){
			$stmt = $this->dbConnect->prepare("SELECT a_id,a_nm,d_nm FROM `admin` inner join department on department.d_id = a_dep WHERE a_typ = 0");
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

		//Book Visit for Host from Visitor
		public function InsbookVisit(){
			
			$stmt = $this->dbConnect->prepare("INSERT INTO `visitor_log` SET vl_dep=:vl_dep,vl_hnm=:vl_hnm,vl_pov=:vl_pov,vl_st=:vl_st,vl_vid = :v_id,vl_rid=:vl_rid");
			$stmt->bindParam(":vl_dep",$this->vl_dep);
			$stmt->bindParam(":vl_hnm",$this->vl_hnm);
			$stmt->bindParam(":vl_pov",$this->vl_pov);
			$stmt->bindParam(":vl_st",$this->vl_st);
			$stmt->bindParam(":v_id",$this->v_id);
			$stmt->bindParam(":vl_rid",$this->vl_rid);
			// print($stmt);
			$stmt->execute();

			$data = $this->dbConnect->lastInsertId();
			return $data;


		}

		//Get Visitor Logs
		public function getVisitorLogs(){
			$stmt = $this->dbConnect->prepare("SELECT * FROM `visitor_log` INNER JOIN admin ON admin.a_id = vl_hnm WHERE vl_vid = :v_id ORDER BY `visitor_log`.`vl_id` DESC ");
			$stmt->bindParam(":v_id",$this->v_id);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

		//Get Visitor Detailed visit data
		public function getVisitorVisitDetails(){
			$stmt = $this->dbConnect->prepare("SELECT * FROM `visitor_log` INNER JOIN admin ON admin.a_id = vl_hnm INNER JOIN vsitor ON vsitor.v_id = visitor_log.vl_vid WHERE vl_id = :vl_id ORDER BY `visitor_log`.`vl_id` DESC ");
			$stmt->bindParam(":vl_id",$this->vl_id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}

		
		//Change Password for Visitor 
		public function changePassword(){
			$pwd = password_hash($this->v_pwd,PASSWORD_BCRYPT);
			$stmt = $this->dbConnect->prepare("UPDATE `vsitor` SET v_pwd = :pwd WHERE v_id = :v_id");
			$stmt->bindParam(":pwd",$pwd);
			$stmt->bindParam(":v_id",$this->v_id);
			if($stmt->execute()){
				return true;
			} else {
				return false;
			}
			


		}
		// Insert Visitor g
		public function insertvisitor_g(){
			$stmt = $this->dbConnect->prepare("INSERT INTO `vsitor` SET v_nm=:v_nm, v_phn =:v_phn,v_em=:v_em,v_gen=:v_gen,v_typ=0,v_rvid=:v_rvid");
			$stmt->bindParam(":v_nm",$this->v_nm);
			$stmt->bindParam(":v_phn",$this->v_phn);
			$stmt->bindParam(":v_em",$this->v_em);
			// $stmt->bindParam(":v_loc",$this->v_loc);
			$stmt->bindParam(":v_gen",$this->v_gen);
			// $stmt->bindParam(":v_typ",$this->v_typ);
			$stmt->bindParam(":v_rvid",$this->v_rvid);
			$stmt->execute();
			$data = $this->dbConnect->lastInsertId();
			// return $data;
			
			// // // if($this->v_typ == 0 ){
			$stmt1 = $this->dbConnect->prepare("INSERT INTO `guest` SET g_com=:g_com,g_aphn=:g_aphn,g_eid=:g_eid,g_des=:g_des ,g_vid=:v_id");
			$stmt1->bindParam(":g_com",$this->g_com);
			$stmt1->bindParam(":g_aphn",$this->g_aphn);
			$stmt1->bindParam(":g_eid",$this->g_eid);
			$stmt1->bindParam(":g_des",$this->g_des);
			$stmt1->bindParam(":v_id",$data);
			if($stmt1->execute()){
				return $data;
			} else {
				return false;
			}
			// } 
		}

		// Insert Visitor 
		public function insertvisitor(){
			$stmt = $this->dbConnect->prepare("INSERT INTO `vsitor` SET v_nm=:v_nm, v_phn =:v_phn,v_em=:v_em,v_gen=:v_gen,v_typ=:v_typ,v_rvid=:v_rvid");
			$stmt->bindParam(":v_nm",$this->v_nm);
			$stmt->bindParam(":v_phn",$this->v_phn);
			$stmt->bindParam(":v_em",$this->v_em);
			// $stmt->bindParam(":v_loc",$this->v_loc);
			$stmt->bindParam(":v_gen",$this->v_gen);
			$stmt->bindParam(":v_typ",$this->v_typ);
			$stmt->bindParam(":v_rvid",$this->v_rvid);
			$stmt->execute();
			$data = $this->dbConnect->lastInsertId();
			// return $data;
			
			// // if($this->v_typ == 0 ){
				$stmt1 = $this->dbConnect->prepare("INSERT INTO `parent` SET p_snm=:p_snm,p_rel=:p_rel,p_dep=:p_dep,p_srn=:p_srn,p_vid=:v_id");
			$stmt1->bindParam(":p_snm",$this->p_snm);
			$stmt1->bindParam(":p_rel",$this->p_rel);
			$stmt1->bindParam(":p_dep",$this->p_dep);
			$stmt1->bindParam(":p_srn",$this->p_srn);
			$stmt1->bindParam(":v_id",$data);
			if($stmt1->execute()){
				return $data;
			} else {
				return false;
			}
			// } 
		}
		


		// // Insert guest 
		// public function insertvisitorg(){
		// 	$stmt = $this->dbConnect->prepare("INSERT INTO `vsitor` SET v_nm=:v_nm, v_phn =:v_phn,v_em=:v_em,v_loc=:v_loc,v_gen=:v_gen,v_typ=:v_typ,v_rvid=:v_rvid");
		// 	$stmt->bindParam(":v_nm",$this->v_nm);
		// 	$stmt->bindParam(":v_phn",$this->v_phn);
		// 	$stmt->bindParam(":v_em",$this->v_em);
		// 	$stmt->bindParam(":v_loc",$this->v_loc);
		// 	$stmt->bindParam(":v_gen",$this->v_gen);
		// 	$stmt->bindParam(":v_typ",$this->v_typ);
		// 	$stmt->bindParam(":v_rvid",$this->v_rvid);
		// 	$stmt->execute();
		// 	$data = $this->dbConnect->lastInsertId();
		// 	return $data;
		// 	// // if($this->v_typ == 0 ){
		// 	// $stmt = $this->dbConnect->prepare("INSERT INTO `guest` SET g_com/inst=:g_com,g_aphn=:g_aphn,g_eid=:g_eid,g_des=:g_des,g_vid=:v_id");
		// 	// $stmt->bindParam(":g_com",$this->g_com);
		// 	// $stmt->bindParam(":g_aphn",$this->g_aphn);
		// 	// $stmt->bindParam(":g_eid",$this->g_eid);
		// 	// $stmt->bindParam(":g_des",$this->g_des);
		// 	// $stmt->bindParam(":v_id",$data);
		// 	// if($stmt->execute()){
		// 	// 	return true;
		// 	// } else {
		// 	// 	return false;
		// 	// }
		// 	// } 
		// }
		
		// Insert Admin
		public function insertadmin(){
			$stmt = $this->dbConnect->prepare("INSERT INTO `admin` SET a_un=:a_un,a_pwd=:a_pwd,a_typ=1,a_nm=:a_nm,a_dep=:a_dep,a_emp=:a_emp");
			$stmt->bindParam(":a_un",$this->a_un);
			$stmt->bindParam(":a_pwd",$this->a_pwd);
			$stmt->bindParam(":a_nm",$this->a_nm);
			$stmt->bindParam(":a_dep",$this->a_dep);
			$stmt->bindParam(":a_emp",$this->a_emp);
			if($stmt->execute()){
				return true;
			} else {
				return false;
			}
			
		}

		// upadte visitor image
		public function updateimage(){
			$stmt = $this->dbConnect->prepare("UPDATE `vsitor` SET `v_img`=:v_img WHERE `v_id`=:v_id");
			$stmt->bindParam(":v_img",$this->v_img);
			$stmt->bindParam(":v_id",$this->v_id);
			if($stmt->execute()){
				return true;
			} else {
				return false;
			}
		}
		//Change Password for Host 
		public function HchangePassword(){
			
			$stmt = $this->dbConnect->prepare("UPDATE `admin` SET `a_pwd`=:pwd WHERE `a_id` =:a_id");
			$stmt->bindParam(":pwd",$this->a_pwd);
			$stmt->bindParam(":a_id",$this->a_id);
			if($stmt->execute()){
				return true;
			} else {
				return false;
			}
			


		}

		//Change Password for Visitor 
		public function createPassword(){
			$pwd = password_hash($this->v_pwd,PASSWORD_BCRYPT);
			$stmt = $this->dbConnect->prepare("UPDATE `vsitor` SET v_pwd = :pwd WHERE v_phn = :v_phn");
			$stmt->bindParam(":pwd",$pwd);
			$stmt->bindParam(":v_phn",$this->v_phn);
			if($stmt->execute()){
				return true;
			} else {
				return false;
			}
			


		}

		// INSERT INTO `vsitor` SET v_nm=:v_nm, v_phn =:v_phn,v_em=:v_em,v_loc=:v_loc,v_gen=:v_gen,v_typ=:v_typ,v_rvid=:v_rvid
		// INSERT INTO `parent` SET p_snm=:p_snm,p_rel=:p_rel,p_dep:p_dep,p_srn=:p_srn WHERE p_vid=1
		// INSERT INTO `guest` SET g_com/inst=:g_com,g_aphn:g_aphn,g_eid=:g_eid,g_des=:g_des WHERE g_vid=1
		// INSERT INTO `admin` SET a_un=:a_un,a_pwd=:a_pwd,a_typ=1,a_nm=:a_nm,a_dep=:a_dep,a_emp=:a_emp
}
?>
