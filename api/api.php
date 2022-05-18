<?php
// demo
	require("rest.php");
	require("redis.php");
	require("../models/visitor.php");
	require("../models/host.php");


	class Api extends Rest {

		protected $redis;

		public function __construct() {
			parent::__construct();
			$redisConnect = new Redis;
			$this->redis = $redisConnect->connect();
		}

		//login
		
		public function vlogin(){
			$v_phn = $this->validateParameter('v_phn', $this->param['v_phn'], STRING);
			$v_pwd = $this->validateParameter('v_pwd', $this->param['v_pwd'], STRING);

			$visitor = new vistor;
			$visitor->setVphn($v_phn);
			$visitorExists = $visitor->checkVisitorExist();

			if(!is_array($visitorExists)){
				$this->returnResponse(INVALID_USER_PASS, "Mobile Number is Not Exist.");
			} else{
				if(!password_verify($v_pwd,$visitorExists['v_pwd'])){
					$this->returnResponse(INVALID_USER_PASS, "Mobile Number or Password is Incorrect.");
				}
			}

			$visitor->setVid($visitorExists['v_id']);
			$this->vistorTokenGenerate($visitorExists);

		}	
		
		public function vistorTokenGenerate($visitorExists){
			try {

				$paylod = [
					'iat' => time(),
					'iss' => 'localhost',
					'exp' => time() + (24*3600),
					'userId' => $visitorExists['v_id']
				];

				// Json Web Token Creation
				$token = JWT::encode($paylod, SECRETE_KEY);

				
					// Add this token to redis
				$this->redis->hmset($token , array('time_stamp'=>time()));

				// Delete this token form redis based on expiry time
				$this->redis->expire($token,$paylod['exp']);

				$data['v_id'] = $paylod['userId'];
				$data['v_phn']=$visitorExists['v_phn'];
				$data['v_em']=$visitorExists['v_em'];
				$data['v_nm'] = $visitorExists['v_nm'];
				$data['v_rvid'] = $visitorExists['v_rvid'];
				$data['token'] =  $token;

				$this->returnResponse(SUCCESS_RESPONSE, $data);

			} catch (Exception $e) {
					$this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
			}
		}
		
		// GetVistiorDetails
		public function getVistiorDetails(){
			$v_id = $this->validateParameter('v_id', $this->param['v_id'], STRING,false);
			$visitor = new vistor;
			$visitor->setVid($v_id);

			$data = $visitor->getvistiordetails();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//Get Departments
		public function getDepartments(){
			$department = new vistor;
			$data = $department->getDepartments();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Location 
		public function getLocation(){
			$location = new vistor;
			$data = $location->getLocation();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Relation
		public function getRelation(){
			$relation = new vistor;
			$data = $relation->getRelation();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Host names from DB
		public function getHostNames(){
			$hostName = new vistor;
			$data = $hostName->getHostNames();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// book visit 
		public function bookVisit(){
			$vl_dep  = $this->validateParameter('vl_dep', $this->param['vl_dep'], INTEGER);
			$vl_hnm = $this->validateParameter('vl_hnm', $this->param['vl_hnm'], INTEGER);
			$vl_pov = $this->validateParameter('vl_pov', $this->param['vl_pov'], STRING);
			$vl_st = $this->validateParameter('vl_st', $this->param['vl_st'], STRING);
			$vl_vid = $this->validateParameter('vl_vid', $this->param['vl_vid'], INTEGER);
			$vl_rid = random_int(100000, 999999);
			$visitor = new vistor;
			$visitor->setVl_dep($vl_dep);
			$visitor->setVl_hnm($vl_hnm);
			$visitor->setVl_pov($vl_pov);
			$visitor->setVl_st($vl_st);
			$visitor->setVid($vl_vid);
			$visitor->setVlrid($vl_rid);

			$data = $visitor->InsbookVisit();

			// if(is_array($data) && count($data)>0){
			// 	$response['data'] = $data;
			// 	$this->returnResponse(SUCCESS_RESPONSE, $response);	
			// }else{
			// 	$this->returnResponse(FAILURE_RESPONSE, "No data Inserted");
			// }
			if(!$data) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to register the User Details. Please try again");
			} else {
					$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);	
			}



		}

		// Get Booked Visits
		public function getBookedVisits(){
			$vid = $this->validateParameter('vid', $this->param['vid'], INTEGER);
			$visitor = new vistor;
			$visitor->setVid($vid);

			$data = $visitor->getVisitorLogs();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// Get Booked Visits
		public function getVisitorId(){
			$vlid = $this->validateParameter('vlid', $this->param['vlid'], INTEGER);
			$visitor = new vistor;
			$visitor->setVl_id($vlid);

			$data = $visitor->getVisitorVisitDetails();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Change Password for visitor
		public function vChangePassword(){
			$vid = $this->validateParameter('vid', $this->param['vid'], INTEGER);
			$v_pwd = $this->validateParameter('v_pwd', $this->param['v_pwd'], STRING);

			$visitor = new vistor;
			$visitor->setVid($vid);
			$visitor->setVpwd($v_pwd);

			$data = $visitor->changePassword();

			if($data){
				// $response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, "Successfully changed the password");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Password not Changed");
			}
		}


		//Host Login

		public function hlogin(){
			$h_un = $this->validateParameter('h_un', $this->param['h_un'], STRING);
			$h_pwd = $this->validateParameter('h_pwd', $this->param['h_pwd'], STRING);

			$host = new host;
			$host->setH_un($h_un);
			$host->setH_pwd($h_pwd);

			$data = $host->hostLogin();
			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);}
			else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

			

		}	

		// Get Booked Visits for Host
		public function gethostvisitlog(){
			$hid = $this->validateParameter('hid', $this->param['hid'], INTEGER);
			
			$host = new host;
			$host->seth_id($hid);
			$data = $host->getHVisitorLogs();	



			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function getvisitoridh(){
			$vlid = $this->validateParameter('vlid', $this->param['vlid'], INTEGER);
			$visitor = new vistor;
			$visitor->setVl_id($vlid);

			$data = $visitor->getVisitorVisitDetails();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Change Log Status for
	
		public function changestat(){ 
			$vlid = $this->validateParameter('vlid', $this->param['vlid'], INTEGER);
			$vl_st = $this->validateParameter('vl_st', $this->param['vl_st'], INTEGER);

			$host = new host;
			$host->setVl_id($vlid);
			$host->setVl_st($vl_st);
			$data = $host->changeStat();


			if($data){
				// $response['data'] = $data;
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE,$data);
				// $this->returnResponse(SUCCESS_RESPONSE, "Successfully changed the status");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Status not Changed");
			}
		}


		//Add Vistor
		public function addvistorp(){
			// $vid = $this->validateParameter('vid', $this->param['vid'], INTEGER);
			$v_name = $this->validateParameter('v_nm', $this->param['v_nm'], STRING);
			$v_email = $this->validateParameter('v_em', $this->param['v_em'], STRING);
			$v_phone = $this->validateParameter('v_phn', $this->param['v_phn'], STRING);
			$v_typ = $this->validateParameter('v_typ', $this->param['v_typ'], INTEGER);
			$v_gen = $this->validateParameter('v_gen', $this->param['v_gen'], STRING);
			// $v_loc = $this->validateParameter('v_loc', $this->param['v_loc'], INTEGER);
			$p_snm = $this->validateParameter('p_snm', $this->param['p_snm'], STRING);
			$p_rel = $this->validateParameter('p_rel', $this->param['p_rel'], STRING);
			$p_dep = $this->validateParameter('p_dep', $this->param['p_dep'], INTEGER);
			$p_srn = $this->validateParameter('p_srn', $this->param['p_srn'], STRING);
			$v_rid = random_int(100000, 999999);


			$visitor = new vistor;
			// $visitor->setVid($vid);
			$visitor->setVnm($v_name);
			$visitor->setVem($v_email);
			$visitor->setVphn($v_phone);
			$visitor->setVtyp($v_typ);
			$visitor->setVgen($v_gen);
			// $visitor->setVloc($v_loc);
			$visitor->setPsnm($p_snm);
			$visitor->setPrel($p_rel);
			$visitor->setPdep($p_dep);
			$visitor->setPsrn($p_srn);
			$visitor->setVrvid($v_rid);


			$data = $visitor->insertvisitor();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Vistor not Added");
			}
		} 

		//Add Vistor guest
		public function addvistorg(){
			$v_name = $this->validateParameter('v_nm', $this->param['v_nm'], STRING);
			$v_email = $this->validateParameter('v_em', $this->param['v_em'], STRING);
			$v_phone = $this->validateParameter('v_phn', $this->param['v_phn'], STRING);
			// $v_typ = $this->validateParameter('v_typ', $this->param['v_typ'], STRING);
			$v_gen = $this->validateParameter('v_gen', $this->param['v_gen'], STRING);
			// $v_loc = $this->validateParameter('v_loc', $this->param['v_loc'], INTEGER);
			$g_com = $this->validateParameter('g_com', $this->param['g_com'], STRING);
			$g_aphn = $this->validateParameter('g_aphn', $this->param['g_aphn'], STRING);
			$g_eid = $this->validateParameter('g_eid', $this->param['g_eid'], STRING);
			$g_des = $this->validateParameter('g_des', $this->param['g_des'], STRING);
			$v_rid = random_int(100000, 999999);
			


			$visitor = new vistor;
			$visitor->setVnm($v_name);
			$visitor->setVem($v_email);
			$visitor->setVphn($v_phone);
			// $visitor->setVtyp($v_typ);
			$visitor->setVgen($v_gen);
			// $visitor->setVloc($v_loc);
			$visitor->setGcom($g_com);
			$visitor->setGaphn($g_aphn);
			$visitor->setGeid($g_eid);
			$visitor->setGdes($g_des);
			$visitor->setVrvid($v_rid);


			$data = $visitor->insertvisitor_g();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE,$data);
				// $this->returnResponse(SUCCESS_RESPONSE, "Successfully Added the Guest Vistor");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Vistor not Added");
			}
		} 

		//Get Departments
		public function dep(){
			$department = new vistor;
			$data = $department->getDepartments();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// Insert Admin
		public function insertadmin(){
			$a_name = $this->validateParameter('a_nm', $this->param['a_nm'], STRING);
			$a_email = $this->validateParameter('a_um', $this->param['a_um'], STRING);
			$a_loc = $this->validateParameter('a_dep', $this->param['a_dep'], INTEGER);
			$a_pass = $this->validateParameter('a_pwd', $this->param['a_pwd'], STRING);
			$a_emp = $this->validateParameter('a_emp', $this->param['a_emp'], STRING);
			

			$admin = new vistor;
			$admin->setAun($a_name);
			$admin->setAnm($a_email);
			$admin->setAdep($a_loc);
			$admin->setApwd($a_pass);
			$admin->setAemp($a_emp);

			$data = $admin->insertAdmin();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Admin not Added");
			}
		}

		// update vimage
		public function uploadimage(){
			$vid = $this->validateParameter('vid', $this->param['vid'], INTEGER);
			$vimg = $this->validateParameter('vimg', $this->param['vimg'], STRING);
			$visitor = new vistor;
			$visitor->setVid($vid);
			$visitor->setVimg($vimg);
			$data = $visitor->updateimage();

			if($data){
				// $response['data'] = $data;
				// $response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE,"Image Uploaded Successfully");
				// $this->returnResponse(SUCCESS_RESPONSE, "Successfully changed the status");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Image not Uploaded");
			}


		}
 

		//Create Password for visitor
		public function vcreatepassword(){
			$v_phn = $this->validateParameter('v_phn', $this->param['v_phn'], INTEGER);
			$v_pwd = $this->validateParameter('v_pwd', $this->param['v_pwd'], STRING);

			$visitor = new vistor;
			$visitor->setVphn($v_phn);
			$visitor->setVpwd($v_pwd);

			$data = $visitor->createPassword();

			if($data){
				// $response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, "Successfully Created the password");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Password not Changed");
			}
		}

		//Change Password Host 
		public function hchangepassword(){
			$h_id = $this->validateParameter('hid', $this->param['hid'], INTEGER);
			$h_pwd = $this->validateParameter('hpwd', $this->param['hpwd'], STRING);

			$host = new vistor;
			$host->setaid($h_id);
			$host->setApwd($h_pwd);

			$data = $host->HchangePassword();

			if($data){
				// $response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, "Password Changed Successfully");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Password not Changed");
			}
		}


	}

 ?>
