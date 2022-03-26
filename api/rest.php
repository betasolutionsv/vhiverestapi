<?php

	require_once('constants.php');

	class Rest {

		protected $request;
		protected $serviceName;
		protected $param;
		protected $dbConnect;
		protected $redis;
		protected $userId;
		protected $services;
		protected $content_types;

		public function __construct() {

			if(($_SERVER['REQUEST_METHOD'] !== 'POST')) {
				$this->throwError(REQUEST_METHOD_NOT_VALID, 'Request Method Is Not Valid.');
			}

			$handler = fopen('php://input', 'r');
			$this->request = stream_get_contents($handler);
			$this->validateRequest();

			$db = new Database;
			$this->dbConnect = $db->connect();

			$redisConnect = new Redis;
			$this->redis = $redisConnect->connect();


			$this->services = array("login","register","uniquemobilenumbervalidation","uniqueemailvalidation","adduserconfig","addprofileimage","deleteprofileimage","categorynamesbykeyword","getmastersdataofaddpostform","otpcheck","directlogin","forgotpassword","resetpassword","resendotp","getlocationid","vlogin");

			if (!in_array(strtolower($this->serviceName), $this->services))
			{
				$this->validateToken();
			}
		}

		public function validateRequest() {

			// $this->content_types = array("application/json","multipart/form-data; boundary=--------------------------703199064470902802121296");

			// echo $_SERVER['CONTENT_TYPE'];
			// // var_dump($this->content_types);
			// exit;

			// if (!in_array($_SERVER['CONTENT_TYPE'], $this->content_types))
			// {
			// 	echo $_SERVER['CONTENT_TYPE'];
			// 	// $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request Content Type Is Not Valid');
			// }

			// if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
			// 	$this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request Content Type Is Not Valid');
			// }

			$data = json_decode($this->request, true);

			if(!isset($data['service_name']) || $data['service_name'] == "") {
				$this->throwError(API_SERVICE_NAME_REQUIRED, "API Service Name Is Required.");
			}

			$this->serviceName = $data['service_name'];

			if(!is_array($data['param'])) {
				$this->throwError(API_PARAM_REQUIRED, "API Param Is Required.");
			}

			$this->param = $data['param'];
		}

		public function validateParameter($fieldName, $value, $dataType, $required = true) {

			if($required == true && empty($value) == true) {
				$this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldName . " Parameter Is Required.");
			}

			switch ($dataType) {
				case BOOLEAN:
					if(!is_bool($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be boolean.');
					}
					break;
				case INTEGER:
					if(!is_numeric($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be numeric.');
					}
					break;

				case STRING:
					if(!is_string($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be string.');
					}
					break;
				case ARRAYTYPE:
					if(!is_array($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be array.');
					}
					break;
				default:
					$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName);
					break;
			}

			return $value;

		}

		public function validateToken() {

			$status = $this->invalidateToken();

			if($status){

				$this->returnResponse(ACCESS_TOKEN_DELETED, "Access Token Deleted");

			}else{

				try {

					//Json Web Token Signature Validation Here

					$token = $this->getBearerToken();
					$payload = JWT::decode($token, SECRETE_KEY, ['HS256']);

					$stmt = $this->dbConnect->prepare("SELECT * FROM `vsitor` WHERE v_id = :userId");
					$stmt->bindParam(":userId", $payload->userId);
					$stmt->execute();
					$user = $stmt->fetch(PDO::FETCH_ASSOC);

					if(!is_array($user)) {
						$this->returnResponse(INVALID_USER_PASS, "This User is not found in our database.");
					}

					// if( $user['active'] == 0 ) {
					// 	$this->returnResponse(USER_NOT_ACTIVE, "The User May be deactivated. Please contact to admin");
					// }

				}catch(Exception $e){
					$this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
				}
			}
		}

		public function processApi() {

			try {
				$api = new API;
				$rMethod = new reflectionMethod('API', $this->serviceName);
				if(!method_exists($api, $this->serviceName)) {
					$this->throwError(API_DOST_NOT_EXIST, "API Does Not Exist.");
				}
				$rMethod->invoke($api);
			} catch (Exception $e) {
				$this->throwError(API_DOST_NOT_EXIST,  $e->getMessage());
			}

		}

		public function throwError($code, $message) {
			header("content-type: application/json");
			$errorMsg = json_encode(['error' => ['status'=>$code, 'message'=>$message]]);
			echo $errorMsg; exit;
		}

		public function returnResponse($code, $data) {
			header("content-type: application/json");
			$response = json_encode(['response' => ['status' => $code, "result" => $data]]);
			echo $response; exit;
		}

		//JWT Authorization Checking Functions

		/**
	    * Get hearder Authorization
	    * */
	    public function getAuthorizationHeader(){
	        $headers = null;
	        if (isset($_SERVER['Authorization'])) {
	            $headers = trim($_SERVER["Authorization"]);
	        }
	        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
	            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	        } elseif (function_exists('apache_request_headers')) {
	            $requestHeaders = apache_request_headers();
	            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
	            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
	            if (isset($requestHeaders['Authorization'])) {
	                $headers = trim($requestHeaders['Authorization']);
	            }
	        }
	        return $headers;
		}

	    /**
	     * get access token from header
	     * */
	    public function getBearerToken() {
	        $headers = $this->getAuthorizationHeader();
	        // HEADER: Get the access token from the header
	        if (!empty($headers)) {
	            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	                return $matches[1];
	            }
	        }
	        $this->throwError( ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not Found');
		}

		/**
		 * invalidating token
		 */
		public function invalidateToken() {

			// Get currently logged client token
			$token = $this->getBearerToken();

			//get token data from redis keys
			$tokensList = $this->redis->keys("*");

			//check token exist or not
			if (in_array($token , $tokensList))
			{
				return false;
			}
			else
			{
				return true;
			}

			// Find Token Data Index Value
			// $index = array_search($token, $tokensList);

			// Get the token data
			// $tokenData = $this->redis->hgetall($tokensList[$index]);

			// if($tokenData['token_type'] == 'black'){
			// 	return true;
			// }else{
			// 	return false;
			// }
		}
	}
 ?>
