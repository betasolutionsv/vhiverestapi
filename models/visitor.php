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
			
			$stmt = $this->dbConnect->prepare("INSERT INTO `visitor_log` SET vl_dep=:vl_dep,vl_hnm=:vl_hnm,vl_pov=:vl_pov,vl_st=:vl_st,vl_vid = :v_id");
			$stmt->bindParam(":vl_dep",$this->vl_dep);
			$stmt->bindParam(":vl_hnm",$this->vl_hnm);
			$stmt->bindParam(":vl_pov",$this->vl_pov);
			$stmt->bindParam(":vl_st",$this->vl_st);
			$stmt->bindParam(":v_id",$this->v_id);
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
}
?>
