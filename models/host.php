<?php
class host{
    private $dbConnect;
    private $h_un;
    private $h_pwd;
    private $h_nm;
    private $h_empid;
	private $h_id;
    private $h_typ;
    private $vl_st;
    private $vl_id;

    
    function setVl_st($vl_st) { $this->vl_st = $vl_st; }
		function getVl_st() { return $this->vl_st; }
    

    public function setH_un($h_un) { $this->h_un = $h_un; }
    public function getH_un() { return $this->h_un; }
    public function setH_pwd($h_pwd) { $this->h_pwd = $h_pwd; }
    public function getH_pwd() { return $this->h_pwd; }
    public function setH_nm($h_nm) { $this->h_nm = $h_nm; }
    public function getH_nm() { return $this->h_nm; }
    public function setH_empid($h_empid) { $this->h_empid = $h_empid; }
    public function getH_empid() { return $this->h_empid; }
    public function setH_id($h_id) { $this->h_id = $h_id; }
    public function getH_id() { return $this->h_id; }
    public function setH_typ($h_typ) { $this->h_typ = $h_typ; }
    public function getH_typ() { return $this->h_typ; }
    function setVl_id($vl_id) { $this->vl_id = $vl_id; }
		function getVl_id() { return $this->vl_id; }
    
    public function __construct() {
        $db = new Database();
        $this->dbConnect = $db->connect();
    }

    //Host Login
    public function hostlogin() {
    
        $stmt = $this->dbConnect->prepare("SELECT * FROM `admin` WHERE a_un = :h_un AND a_pwd = :h_pwd");
        $stmt->bindParam(':h_un', $this->h_un);
        $stmt->bindParam(':h_pwd', $this->h_pwd);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data;
    }

    //Get Visitor Logs for Hosts
		public function getHVisitorLogs(){
			$stmt = $this->dbConnect->prepare("SELECT * FROM `visitor_log` INNER JOIN admin ON admin.a_id = vl_hnm inner join vsitor on vsitor.v_id = visitor_log.vl_vid  WHERE admin.a_id = :h_id ORDER BY `visitor_log`.`vl_id` DESC ");
			$stmt->bindParam(":h_id",$this->h_id);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

        //Change Log Status for
        public function changestat(){
            $stmt = $this->dbConnect->prepare("UPDATE `visitor_log` SET `vl_stat` = :vl_st WHERE `vl_id` = :vlid");
            $stmt->bindParam(":vl_st",$this->vl_st);
            $stmt->bindParam(":vlid",$this->vl_id);
            if($stmt->execute()){
				return true;
			} else {
				return false;
			}
        }
}

?>