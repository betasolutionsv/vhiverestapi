<?php
// demo
	require("rest.php");
	require("redis.php");
	require("../models/visitor.php");

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


	}

 ?>
