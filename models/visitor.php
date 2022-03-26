<?php 
class vistor{
    private $dbConnect;
    private $V_table = "vsitor";    


    private $v_phn;
    private $v_pwd;
    private $v_nm;
    private $v_em;

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

        public function __construct() {
			$db = new Database();
			$this->dbConnect = $db->connect();
		}

        public function checkVisitorExist(){

			$stmt = $this->dbConnect->prepare("SELECT v_id,v_nm,v_phn,v_em,v_pwd FROM `vsitor` WHERE v_phn=:v_phn");
			$stmt->bindParam(":v_phn",$this->v_phn);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
}
?>
