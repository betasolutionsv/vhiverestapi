<?php
// demo
	require("rest.php");
	require("redis.php");
	require("../models/user.php");
	require("../models/userconfig.php");
	require("../models/post.php");
	require("../models/image.php");
	require("../models/phonecall.php");
	require("../models/message.php");
	require("../models/postselect.php");
	require("../models/finder.php");
	require("../models/fixer.php");
	require("../models/scratchcard.php");
	require("../models/wallet.php");
	require("../models/subscription.php");
	require("../models/savedposts.php");
	require("../models/reportedposts.php");
	require("../models/add.php");
	require("../models/query.php");
	require("../models/bookservice.php");
	require("../models/bookAdservice.php");

	class Api extends Rest {

		protected $redis;

		public function __construct() {
			parent::__construct();
			$redisConnect = new Redis;
			$this->redis = $redisConnect->connect();
		}

		//login
		public function login() { 

			$u_phn = $this->validateParameter('u_phn', $this->param['u_phn'], STRING);
			$u_pwd = $this->validateParameter('u_pwd', $this->param['u_pwd'], STRING);

			$user = new User;
			$user->setUphn($u_phn);
			$userexist = $user->checkUserExist();

			if(!is_array($userexist)) {
				$this->returnResponse(INVALID_USER_PASS, "Mobile Number is Not Exist.");
			}else{
				if(!password_verify($u_pwd,$userexist['u_pwd'])){
					$this->returnResponse(INVALID_USER_PASS, "Mobile Number or Password is Incorrect.");
				}
			}

			// if( $userexist['active'] == 0 ) {
			// 	$this->returnResponse(USER_NOT_ACTIVE, "User is not activated. Please contact to admin.");
			// }

			$user->setUid($userexist['u_id']);

			if( $userexist['u_mob_verify'] == 0 ) {
				$otp 			=	mt_rand(OTP_MIN_RAND,OTP_MAX_RAND);
				$user->setOTP($otp);
				$user->setUpdt(INSERT_DATE_TIME);
				$user->setOtptype(1);
				$otpid			=	$user->generateOTP();
				$msg = $otp." is your OTP to verify your mobile number with Goffix to enjoy 100+ home services and offers. http://www.goffix.com";
				// $url =	"http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$u_phn."&from=GOFFIX&message=".$msg;
				$url = "http://sms.sonicsoftsolutions.in/v3/api.php?username=goffix&apikey=62fe9b4b029c4375506c&senderid=goffix&templateid=1707160873389910548&mobile=".$u_phn."&message=".urlencode($msg);
				$otpresponse =	$this->sendMsg($url);
				$user->saveresponsefrommsg($otpresponse,$otpid);
				$response['otp']=$otpresponse;
				$response['data'] = $userexist['u_id'];
				$response['u_gender'] = $userexist['u_gender'];
				$this->returnResponse(MOBILE_NOT_VERIFIED, $response);
			}

			$otherdata 	=	$user->checknextstepsignup();
			if($otherdata['us_uid']!=$userexist['u_id']){
				$response['data'] = $userexist['u_id'];
				$response['u_gender'] = $userexist['u_gender'];
				$this->returnResponse(USER_TYPE_NOT_ASSIGNED, $response);
			}

			$this->tokenGenerate($userexist);
		}

		
		public function tokenGenerate($userexist)
		{
			try {

				$paylod = [
					4
				];

				// Json Web Token Creation
				$token = JWT::encode($paylod, SECRETE_KEY);

				// Add this token to white list
				// $this->redis->hmset($token , array(
                //     "token_type" => "white",
				// 	"user_id" => $paylod['userId'],
				// 	"issued_at" => $paylod['iat'],
                //     "exp_time" => $paylod['exp'])
				// );

					// Add this token to redis
				$this->redis->hmset($token , array('time_stamp'=>time()));

				// Delete this token form redis based on expiry time
				$this->redis->expire($token,$paylod['exp']);

				$data['u_id'] = $paylod['userId'];
				$data['us_typ'] = $userexist['us_typ'];
				$data['u_gender']=$userexist['u_gender'];
				$data['u_phn']=$userexist['u_phn'];
				$data['token'] =  $token;

				$this->returnResponse(SUCCESS_RESPONSE, $data);

			} catch (Exception $e) {
					$this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
			}
		}

		public function directlogin()
		{
			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER);

			$user = new User;
			$user->setUid($u_id);
			$userexist = $user->checkandgetloginuser();

			if(!is_array($userexist)) {
				$this->returnResponse(FAILURE_RESPONSE, "Mobile Number is Not Exist.");
			}

			$this->tokenGenerate($userexist);
		}

		//Save User
		public function register() {

			$u_nm = $this->validateParameter('u_nm', $this->param['u_nm'], STRING, false);
			$u_phn = $this->validateParameter('u_phn', $this->param['u_phn'], STRING, false);
			$u_email = $this->validateParameter('u_email', $this->param['u_email'], STRING, false);
			$u_pwd = $this->validateParameter('u_pwd', $this->param['u_pwd'], STRING, false);
			$u_city = $this->validateParameter('u_city', $this->param['u_city'], STRING, false);
			$u_pfn = $this->validateParameter('u_pfn', $this->param['u_pfn'], STRING, false);
			$u_gender = $this->validateParameter('u_gender', $this->param['u_gender'], STRING, false);

			$user = new User;
			$user->setUnm($u_nm);
			$user->setUphn($u_phn);
			$user->setUemail($u_email);
			$user->setUpwd($u_pwd);
			$user->setUcity($u_city);
			$user->setUpfn($u_pfn);
			$user->setUgen($u_gender);
			$user->setUpdt(INSERT_DATE_TIME);
			$id=$user->createUser();

			if(!$id) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to register the User Details. Please try again");
			} else {
				$otp 	=	mt_rand(OTP_MIN_RAND,OTP_MAX_RAND);
				$user->setOTP($otp);
				$user->setUid($id);
				$user->setOtptype(1);
				$otpid=$user->generateOTP();

				// $msg = urlencode($otp." is your one time verification code for Goffix account. It is valid for 24hrs.");
				// $url =	"http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$u_phn."&from=GOFFIX&message=".$msg;
				$msg = $otp." is your OTP to verify your mobile number with Goffix to enjoy 100+ home services and offers. http://www.goffix.com";
					// $url =	"http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$u_phn."&from=GOFFIX&message=".$msg;
				$url = "http://sms.sonicsoftsolutions.in/v3/api.php?username=goffix&apikey=62fe9b4b029c4375506c&senderid=goffix&templateid=1707160873389910548&mobile=".$u_phn."&message=".urlencode($msg);
	
				$otpresponse =	$this->sendMsg($url);
				$user->saveresponsefrommsg($otpresponse,$otpid);
				$response['otp']=$otpresponse;
				$response['data'] = $id;
				$this->returnResponse(SUCCESS_RESPONSE,$response);
			}

		}

		public function otpcheck()
		{
			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER);
			$otp_res = $this->validateParameter('otp_res', $this->param['otp_res'], STRING);
			$otp = $this->validateParameter('otp', $this->param['otp'], INTEGER);
			$otp_type = $this->validateParameter('otp_type', $this->param['otp_type'], INTEGER);

			$user = new User;
			$user->setUid($u_id);
			$user->setOtpres($otp_res);
			$user->setOtptype($otp_type);
			$checkotp = $user->checkotpuser();
			if(!is_array($checkotp)) {
				$this->returnResponse(FAILURE_RESPONSE, "Invalid OTP. Try again!");
			}else{
				if($checkotp['otp_verify_status']==1){
					$this->returnResponse(FAILURE_RESPONSE, "OTP Expired. Please resend OTP");
				}else if(!password_verify($otp,$checkotp['otp'])){
					$this->returnResponse(FAILURE_RESPONSE, "Invalid OTP. Try again!");
				}else if((strtotime(INSERT_DATE_TIME)-strtotime($checkotp['otp_date'])) <= OTP_EXPIRY_LIMIT_IN_HRS*60*60){
					if($user->changeuserverifystatus()){
						$this->returnResponse(SUCCESS_RESPONSE, "Mobile verified successfully");
					}else{
						$this->returnResponse(FAILURE_RESPONSE, "Mobile verification failed. Try again");
					}
				}else{
					$this->returnResponse(FAILURE_RESPONSE, "OTP Expired. Please resend OTP");
				}
			}
		}

		public function sendMsg($url)
		{
			// $camp = file($url);
			// $msgresponse =json_decode($camp);
			// return $msgresponse['campid'];
			// $dataMsg =file($url);
			// $dtMsg = str_replace('"','',$dataMsg);
			// $expMsg = explode("'",$dtMsg);
			// // $msgresponse = explode("'",$dataMsg);
			// $msgresponse = $expMsg[3];
			// // $msgresponse = json_encode($msgresponse);
			// return $msgresponse;
			$ch = curl_init();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
			// This is what solved the issue (Accepting gzip encoding)
			curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
			$dataMsg = curl_exec($ch);
			curl_close($ch);
			$expMsg = str_replace('"'," ",$dataMsg);
			$msgresponse = explode("'",$expMsg);
			// $obj = json_decode($msgresponse);
			return $msgresponse[3];
		}

		public function resendotp()
		{
			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER);
			$otp_type = $this->validateParameter('otp_type', $this->param['otp_type'], INTEGER);

			$user = new User;
			$otp 	=	mt_rand(OTP_MIN_RAND,OTP_MAX_RAND);
			$user->setOTP($otp);
			$user->setUid($u_id);
			$user->setOtptype($otp_type);
			$user->setUpdt(INSERT_DATE_TIME);
			$otpid=$user->generateOTP();
			$getuser = $user->checkandgetloginuser();
			$u_phn	=	$getuser['u_phn'];

			// $msg = urlencode($otp." is your one time verification code for Goffix account. It is valid for 24hrs.");
			// $url =	"http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$u_phn."&from=GOFFIX&message=".$msg;
			$msg = $otp." is your OTP to verify your mobile number with Goffix to enjoy 100+ home services and offers. http://www.goffix.com";
			$url = "http://sms.sonicsoftsolutions.in/v3/api.php?username=goffix&apikey=62fe9b4b029c4375506c&senderid=goffix&templateid=1707160873389910548&mobile=".$u_phn."&message=".urlencode($msg);

			$otpresponse =	$this->sendMsg($url);
			$user->saveresponsefrommsg($otpresponse,$otpid);
			$response['otp']=$otpresponse;
			$response['data'] = $u_id;
			$this->returnResponse(SUCCESS_RESPONSE,$response);
		}


		// update register user
		public function registerupdate() {

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$u_nm = $this->validateParameter('u_nm', $this->param['u_nm'], STRING, false);

			$u_email = $this->validateParameter('u_email', $this->param['u_email'], STRING, false);

			$u_desc = $this->validateParameter('u_desc', $this->param['u_desc'], STRING, false);

			$u_img = $this->validateParameter('u_img', $this->param['u_img'], STRING, false);

			$u_pfn = $this->validateParameter('u_pfn', $this->param['u_pfn'], STRING, false);
			//$u_phn = $this->validateParameter('u_phn', $this->param['u_phn'], STRING, false);
			//$u_email = $this->validateParameter('u_email', $this->param['u_email'], STRING, false);
			//$u_pwd = $this->validateParameter('u_pwd', $this->param['u_pwd'], STRING, false);
			//$u_city = $this->validateParameter('u_city', $this->param['u_city'], STRING, false);
			//$u_pfn = $this->validateParameter('u_pfn', $this->param['u_pfn'], STRING, false);

			$user = new User;
			$user->setUnm($u_nm);
			$user->setUemail($u_email);
			$user->setUdesc($u_desc);
			$user->setUimg($u_img);
			$user->setUid($uid);
			$user->setUpfn($u_pfn);
			//$user->setUphn($u_phn);
			//$user->setUemail($u_email);
			//$user->setUpwd($u_pwd);
			//$user->setUcity($u_city);
			//$user->setUpfn($u_pfn);
			$data = $user->UpdateUser();
			//$id=$user->UpdateUser();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}


		public function forgotpassword()
		{
			$u_phn = $this->validateParameter('u_phn', $this->param['u_phn'], STRING);

			$user = new User;
			$user->setUphn($u_phn);
			$userexist = $user->checkUserExist();

			if(!is_array($userexist)) {
				$this->returnResponse(INVALID_USER_PASS, "Mobile Number does Not Exist.");
			}else{
				$user->setUid($userexist['u_id']);
				$otp 			=	mt_rand(OTP_MIN_RAND,OTP_MAX_RAND);
				$user->setOTP($otp);
				$user->setUpdt(INSERT_DATE_TIME);
				$user->setOtptype(2);
				$otpid			=	$user->generateOTP();
				// $msg = urlencode($otp.' is your one time verification code for Goffix account. It is valid for 24hrs.');
				// $url =	"http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$u_phn."&from=GOFFIX&message=".$msg;
				$msg = $otp." is your OTP to verify your mobile number with Goffix to enjoy 100+ home services and offers. http://www.goffix.com";
				$url = "http://sms.sonicsoftsolutions.in/v3/api.php?username=goffix&apikey=62fe9b4b029c4375506c&senderid=goffix&templateid=1707160873389910548&mobile=".$u_phn."&message=".urlencode($msg);
	
				$otpresponse =	$this->sendMsg($url);
				$user->saveresponsefrommsg($otpresponse,$otpid);
				$response['otp']  	=	$otpresponse;
				$response['data'] 	= 	$userexist['u_id'];
				$response['u_gender']	=	$userexist['u_gender'];
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}
		}

		public function resetpassword()
		{
			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$u_pwd = $this->validateParameter('u_pwd', $this->param['u_pwd'], STRING, false);
			$u_cpwd = $this->validateParameter('u_cpwd', $this->param['u_cpwd'], STRING, false);
			$otp_res = $this->validateParameter('otp_res', $this->param['otp_res'], STRING);
			$otp_type = $this->validateParameter('otp_type', $this->param['otp_type'], INTEGER);

			if($u_pwd==$u_cpwd){
				$user = new User;
				$user->setUid($uid);
				$user->setUpwd($u_pwd);
				$user->setOtpres($otp_res);
				$user->setOtptype($otp_type);
				$checkotp = $user->checkotpuser();
				if(!is_array($checkotp)) {
					$this->returnResponse(FAILURE_RESPONSE, "Unable to change password. Please try again");
				}else{
					if($checkotp['otp_verify_status']==1){
						$changepwd=$user->changePassword();
						if(!$changepwd) {
							$this->returnResponse(FAILURE_RESPONSE, "Unable to change password. Please try again");
						} else {
							$getUserData 	=	$user->checkUserId();
							$msg = urlencode('GOFFIX: %0A Password have been reset successfully for your goffix profile. If it isn\'t you undo it by contacting the goffix help desk');
			      			$url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getUserData['u_phn']."&from=GOFFIX&message=".$msg;
			       			$this->sendMsg($url);

							$this->returnResponse(SUCCESS_RESPONSE, "Successfully changed the password");
						}
					}else{
						$this->returnResponse(FAILURE_RESPONSE, "Unable to change password. Please try again");
					}
				}
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Password and Confirm password should be equal");
			}

		}


		//Check Fixer Status
		public function isValidFixer(){

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->checkIsValidFixer();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Save Post
		public function addPost() {

			$p_uid = $this->validateParameter('p_uid', $this->param['p_uid'], INTEGER, false);
			$p_tit = $this->validateParameter('p_tit', $this->param['p_tit'], STRING, false);
			$p_jd = $this->validateParameter('p_jd', $this->param['p_jd'], STRING, false);
			$p_catid = $this->validateParameter('p_catid', $this->param['p_catid'], INTEGER, false);
			$p_loc = $this->validateParameter('p_loc', $this->param['p_loc'], INTEGER, false);
			$p_ctyp = $this->validateParameter('p_ctyp', $this->param['p_ctyp'], INTEGER, false);
			$p_priority = $this->validateParameter('p_priority', $this->param['p_priority'], INTEGER, false);
			$p_amt = $this->validateParameter('p_amt', $this->param['p_amt'], STRING, false);

			$post = new Post;

			$post->setPuid($p_uid);
			$post->setPtit($p_tit);
			$post->setPid($p_jd);
			$post->setPcatid($p_catid);
			$post->setPloc($p_loc);
			$post->setPctyp($p_ctyp);
			$post->setPpri($p_priority);
			$post->setPamt($p_amt);
			$post->setPdt(INSERT_DATE_TIME);

			$id = $post->createPost();

			if($id) {
				$response['data'] = $id;
				$userObj = new User;
				$userObj->setUProf(1);
				$userObj->setUCat($p_catid);

				// $getAllFixers =	$userObj->getAllFixersByCatId();
				// $redirect_array	=	array('pagename' => 'home',
				// 	   					'param'	=>	'');
				// foreach($getAllFixers as $getFixer){ 
				// 	if($getFixer['push_notify_id']!=''){
				// 		$this->sendMessage('Goffix: There is a new post awating for you', '', $getFixer['push_notify_id'], array(), false, $redirect_array);
				// 	}
				// }

				$this->returnResponse(SUCCESS_RESPONSE, $response);
			} else {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to Post the Job. please try again");
			}
		}

		//Book Service
		public function bookservice() {

			$bs_uid = $this->validateParameter('bs_uid', $this->param['bs_uid'], INTEGER, false);
			$bs_cid = $this->validateParameter('bs_cid', $this->param['bs_cid'], INTEGER, false);
			$bs_lid = $this->validateParameter('bs_lid', $this->param['bs_lid'], INTEGER, false);
			$bs_loc = $this->validateParameter('bs_Loc', $this->param['bs_Loc'], STRING, false);			
			$bs_dt = $this->validateParameter('bs_dt', $this->param['bs_dt'], STRING, false);
			$bs_time = $this->validateParameter('bs_time', $this->param['bs_time'], INTEGER, false);
			$bs_isPrem = $this->validateParameter('bs_isPrem', $this->param['bs_isPrem'], INTEGER, false);
			$bs_phn = $this->validateParameter('bs_phn', $this->param['bs_phn'], INTEGER, false);
			$bs_desc = $this->validateParameter('bs_desc', $this->param['bs_desc'], STRING, false);
			$bs_uuid = random_int(100000, 999999);
		 
			$bookService = new BookService;

			$bookService->setBsuid($bs_uid);
			$bookService->setBscid($bs_cid);
			$bookService->setBslid($bs_lid);
			$bookService->setBsloc($bs_loc);
			$bookService->setBsbdt($bs_dt);
			$bookService->setBstime($bs_time);
			$bookService->setBsIsPrem($bs_isPrem);
			$bookService->setBsphn($bs_phn);
			$bookService->setBsdesc($bs_desc);
			$bookService->setBsdt(INSERT_DATE_TIME);
			$bookService->setBsuuid($bs_uuid);

			$id = $bookService->createBookService();

			if($id) {
				$response['data'] = $id;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			} else {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to Book the Service. please try again");
			}
			
		}

		//Book Ad Service
		public function bookadservice() {

			$ba_uid = $this->validateParameter('ba_uid', $this->param['ba_uid'], INTEGER, false);
			$ba_aid = $this->validateParameter('ba_aid', $this->param['ba_aid'], INTEGER, false);
			$ba_lid = $this->validateParameter('ba_lid', $this->param['ba_lid'], INTEGER, false);
			$ba_loc = $this->validateParameter('ba_Loc', $this->param['ba_Loc'], STRING, false);			
			$ba_dt = $this->validateParameter('ba_dt', $this->param['ba_dt'], STRING, false);
			$ba_time = $this->validateParameter('ba_time', $this->param['ba_time'], INTEGER, false);
			$ba_phn = $this->validateParameter('ba_phn', $this->param['ba_phn'], INTEGER, false);
			$ba_desc = $this->validateParameter('ba_desc', $this->param['ba_desc'], STRING, false);
			$ba_uuid = random_int(100000, 999999);
		 
			$bookAdService = new BookAdService;

			$bookAdService->setBauid($ba_uid);
			$bookAdService->setBaaid($ba_aid);
			$bookAdService->setBalid($ba_lid);
			$bookAdService->setBaloc($ba_loc);
			$bookAdService->setBabdt($ba_dt);
			$bookAdService->setBatime($ba_time);
			$bookAdService->setBaphn($ba_phn);
			$bookAdService->setBadesc($ba_desc);
			$bookAdService->setBadt(INSERT_DATE_TIME);
			$bookAdService->setBauuid($ba_uuid);

			$id = $bookAdService->createBookService();

			if($id) {
				$response['data'] = $id;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			} else {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to Book the Service. please try again");
			}
			
		}

		//Get All Posted jobs
		public function postedJobs() {
			$uid 			= $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$pageLoading 	= $this->validateParameter('pageLoading', $this->param['pageLoading'], STRING, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);
			$filter_by_cat 	= $this->validateParameter('filter_by_cat', $this->param['filter_by_cat'], STRING, false);

			$query 			= new Query;

			$query->setUid($uid);
			$query->setLimitForPosts($filter_by_cat,$startPost ,$limitPost);
			if($pageLoading=='true'){
				$totPostCount=$query->getPostedJobsCount();
				$totAddsCount=$query->getPostAddsCount();
				$totPostLocNames = $query->getPostedJobslocCount();
				$response['locations'] = $totPostLocNames;
				$response['catgories'] = $totPostCount;
				$response['postadscount']=	$totAddsCount;
			}

			$data 			= $query->getPostedJobs();
			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function postedJobs15() {
			$uid 			= $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$pageLoading 	= $this->validateParameter('pageLoading', $this->param['pageLoading'], STRING, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);
			$filter_by_cat 	= $this->validateParameter('filter_by_cat', $this->param['filter_by_cat'], STRING, false);
			$filter_by_loc 	= $this->validateParameter('filter_by_loc', $this->param['filter_by_loc'], STRING, false);
			//print_r($this->param['filter_by_loc']);//exit;
			$query 			= new Query;

			$query->setUid($uid);
			$query->setLimitForPosts($filter_by_cat,$filter_by_loc,$startPost ,$limitPost);
			if($pageLoading=='true'){
				$totPostCount=$query->getPostedJobsCount();
				$totAddsCount=$query->getPostAddsCount();
				$totPostLocNames = $query->getPostedJobslocCount();
				$response['locations'] = $totPostLocNames;
				$response['catgories'] = $totPostCount;
				$response['postadscount']=	$totAddsCount;
			}

			$data 			= $query->getPostedJobs15();
			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}
		
		// Live Posts
		public function LivePosts() {
			$uid 			= $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);
			
			//print_r($this->param['filter_by_loc']);//exit;
			$query 			= new Query;

			$query->setUid($uid);
			// $query->setLimitForPosts($filter_by_cat,$filter_by_loc,$startPost ,$limitPost);
			$query->setLimitForMyPosts($startPost, $limitPost);
			

			$data 			= $query->getLivePosts();
			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get My Posted jobs
		public function myPostedJobs() {

			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setLimitForMyPosts($startPost, $limitPost);

			$data = $query->getMyPostedJobs();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get New My Posted jobs without images
		public function myPosts() {

			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setLimitForMyPosts($startPost, $limitPost);

			$data = $query->getMyPosts();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Posted Job Info
		public function postedJobInfo() {

			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);

			$query = new Query;
			$query->setpid($pid);
			$data = $query->getPostedJobInfo();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function checkpostsforfixer() {

			$ps_fxid = $this->validateParameter('ps_fxid', $this->param['ps_fxid'], INTEGER, true);
			$now 				= date('Y-m-d');

			$query = new Query;
			$query->setpsfxid($ps_fxid);
			$query->setNow($now);
			$data = $query->getPostsforfixer();

			if($data){
				//$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}


		//Get My Requested Jobs
		public function myRequestedPosts(){

			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query 			= new Query;
			$query->setUid($uid);
			$query->setLimitForChatPosts($startPost, $limitPost);
			$data 			= $query->getMyRequestedPosts();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function chatpost_mypost()
		{
			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query 			= new Query;
			$query->setUid($uid);
			$query->setLimitForChatPosts($startPost, $limitPost);
			$data 			= $query->getChatPostMyPosts();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// Get Chat my Posts - Optimzed
		public function myPostsChat()
		{
			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query 			= new Query;
			$query->setUid($uid);
			$query->setLimitForChatPosts($startPost, $limitPost);
			$data 			= $query->getChatMyPosts();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Chat Users Of Post
		public function userChatAboutPost(){

			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);
			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$query = new Query;
			$query->setpid($pid);
			$query->setUid($uid);

			$data = $query->getChat();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Chat Data of Post
		//Get messages
		public function getmsg(){

			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);
			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$rid = $this->validateParameter('rid', $this->param['rid'], INTEGER, false);

			$msg = new Message;
			$msg->setpid($pid);
			$msg->setuid($uid);
			$msg->setrid($rid);
			$data=$msg->getMessage();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}


		public function getmsgchatbody(){
			//echo $this->param['uid']; exit;
			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);
			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			//$rid = $this->validateParameter('rid', $this->param['rid'], INTEGER, false);

			$msg = new Message;
			$msg->setpid($pid);
			$msg->setuid($uid);
			//$msg->setrid($rid);
			$data=$msg->getMessagechatbody();

			if(is_array($data)){
				$response = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}


		//Get Last Chat Message
		public function chatLastActivity(){

			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);
			$sid = $this->validateParameter('sid', $this->param['sid'], INTEGER, false);
			$rid = $this->validateParameter('rid', $this->param['rid'], INTEGER, false);

			$msg = new Query;
			$msg->setpid($pid);
			$msg->setsid($sid);
			$msg->setrid($rid);

			$data = $msg->getChatLastActivity();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Categories
		public function fixerCategories() {

			$query = new Query;
			$data = $query->getCategories();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Categories With Fixers Count
		public function fixerCategoriesWithCountWise() {

			$query = new Query;
			$data = $query->getfixerCategoriesWithCount();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Fixers Based On Category
		public function fixersOnCategorySelect(){

			$cat_id 		= $this->validateParameter('cat_id', $this->param['cat_id'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query = new Query;
			$query->setCatid($cat_id);
			$query->setLimitForSearch($startPost, $limitPost);
			$data = $query->getfixersByCategoryId();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Fixer Completed Jobs Count
		// public function fixerJobsCount(){

		// 	$fxid = $this->validateParameter('fxid', $this->param['fxid'], INTEGER, false);
		// 	$query = new Query;
		// 	$query->setFxid($fxid);
		// 	$data = $query->getFixerJobsCount();

		// 	if($data){
		// 		$response['data'] = $data;
		// 		$this->returnResponse(SUCCESS_RESPONSE, $response);
		// 	}else{
		// 		$this->returnResponse(FAILURE_RESPONSE, "No data found");
		// 	}
		// }

		//Get Category Name Based On Keyword
		public function categoryNamesByKeyWord(){

			$key_word = $this->validateParameter('key_word', $this->param['key_word'], STRING, false);
			$key_word = $key_word.'%';

			$query = new Query;
			$query->setKeywrd($key_word);
			$data = $query->getCategoriesByKeyWord();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get Location Name Based On Keyword
		public function locationNamesByKeyWord(){

			$key_word = $this->validateParameter('key_word', $this->param['key_word'], STRING, false);
			$key_word = $key_word.'%';

			$query = new Query;
			$query->setKeywrd($key_word);
			$data = $query->getLocationsByKeyWord();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Mobile number Validation
		public function uniqueMobileNumberValidation(){

			$u_phn = $this->validateParameter('u_phn', $this->param['u_phn'], INTEGER, false);

			$user = new User;
			$user->setUphn($u_phn);

			if($user->checkMobileNumberExist()) {
				$this->returnResponse(SUCCESS_RESPONSE, "Mobile Number is Already exist.");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Mobile Number is Not exist.");
			}
		}

		// public function uniqueEmailValidation(){

		// 	$u_email = $this->validateParameter('u_email', $this->param['u_email'], STRING, false);

		// 	$user = new User;
		// 	$user->setUemail($u_email);

		// 	if($user->checkEmailExist()) {
		// 		$this->returnResponse(SUCCESS_RESPONSE, "Email is Already exist.");
		// 	}else{
		// 		$this->returnResponse(FAILURE_RESPONSE, "Email is Not exist.");
		// 	}
		// }

		//Email Validation
		public function uniqueEmailValidation(){

			$u_email = $this->validateParameter('u_email', $this->param['u_email'], STRING, false);

			$user = new User;
			$user->setUemail($u_email);

			if($user->checkEmailExist()) {
				$this->returnResponse(SUCCESS_RESPONSE, "Email is Already exist.");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Email is Not exist.");
			}
		}

		//Ger User Profile Info
		public function userProfileInfo(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$utype = $this->validateParameter('utype', $this->param['utype'], INTEGER, false);

			$user = new Query;
			$user->setUid($uid);
			$user->setUtype($utype);
			$data = $user->getUserProfileInfo();

			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}

		//Retreive Booking Details
		public function getBookingDetails(){
			
			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$booking = new Query;
			$booking->setUid($uid);
			$data = $booking->getBookingDetails();

			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}

		//Ger User Name and MObile No
		public function userNm(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			
			$user = new Query;
			$user->setUid($uid);
		    $data = $user->getUserNm();

			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}

		//Save Message
		public function addMessage() { //echo 123; exit;

			$m_body = $this->validateParameter('m_body', $this->param['m_body'], STRING, false);
			$m_pid = $this->validateParameter('m_pid', $this->param['m_pid'], INTEGER, false);
			$m_sid = $this->validateParameter('m_sid', $this->param['m_sid'], INTEGER, false);
			$m_rid = $this->validateParameter('m_rid', $this->param['m_rid'], INTEGER, false);

			$msg = new Message;
			$msg->setMbody($m_body);
			$msg->setMpid($m_pid);
			$msg->setMsid($m_sid);
			$msg->setMrid($m_rid);
			$msg->setMdt(INSERT_DATE_TIME);

			$getMessageCount	=	$msg->getMessageCount();

			if(!$msg->createMessage()) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to catch Message. please try again");
			} else {
				$user 			= 	new User;

				$user->setUid($m_sid);
				$getSenderData 	=	$user->checkUserId();

				$user->setUid($m_rid);
				$getUserData 	=	$user->checkUserId();

				$redirect_array	=	array('pagename' => 'message',
					   					'param'	=>	'pid='.$m_pid.'&uid='.$m_sid.'&fdid='.$m_rid.'&u_nm='.$getSenderData['u_nm'].'#');
				if($getMessageCount[0]['msgcount']==0){
					$getData		=	$msg->getPostTitleAndUserNumber();
					if($getUserData['push_notify_id']!=''){
						$title 		=	(count($getData['p_tit'])>6)?substr($getData['p_tit'],0,6).'...':$getData['p_tit'];
						$this->sendMessage('Goffix: New fixer awaiting for your post "'.$title.'"', '', $getUserData['push_notify_id'], array(), false, $redirect_array);
					}
					
				}else{
					$getData		=	$msg->getPostTitleAndUserNumber();
					$msg = urlencode("GOFFIX: %0A There are responses awaiting for your ".$getData['p_tit'].'post. Reply immediately before the deal ends.');
	      			$url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getData['u_phn']."&from=GOFFIX&message=".$msg;
	       			//$this->sendMsg($url);
					if($getUserData['push_notify_id']!=''){
						$this->sendMessage('Goffix: Got a message', '', $getUserData['push_notify_id'], array(), false, $redirect_array);
					}
				}
				$this->returnResponse(SUCCESS_RESPONSE, "Message Recorded Successfully.");
			}
		}



		//Save Phone Call Initation
		public function addCall() {

			$pc_pid = $this->validateParameter('pc_pid', $this->param['pc_pid'], INTEGER, false);
			$pc_fxid = $this->validateParameter('pc_fxid', $this->param['pc_fxid'], INTEGER, false);
			$pc_fdid = $this->validateParameter('pc_fdid', $this->param['pc_fdid'], INTEGER, false);
			$pc_body = $this->validateParameter('pc_body', $this->param['pc_body'], STRING, false);
			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$call = new PhoneCall;
			$call->setPcid($pc_pid);
			$call->setPfxid($pc_fxid);
			$call->setPfdid($pc_fdid);
			$call->setPbody($pc_body);
			$call->setMdt(INSERT_DATE_TIME);

			if(!$call->createCall()) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to catch the Call. please try again");
			} else {

				$user 			= 	new User;
				$user->setUid($pc_fdid);
				$getUserData 	=	$user->checkUserId();

				$user->setUid($uid);
				$getcurrentuserData =	$user->checkUserId();

				$getData		=	$call->getPostTitleAndUserNumber();
				$redirect_array	=	array('pagename' => '',
					   					'param'	=>	'');
				if($getUserData['push_notify_id']!=''){
					$title 		=	(count($getData['p_tit'])>6)?substr($getData['p_tit'],0,6).'...':$getData['p_tit']." post";
					$this->sendMessage('Goffix: Call initiated for the post-"'.$title.'" by '.$getcurrentuserData['u_nm'], '', $getUserData['push_notify_id'], array(), false, $redirect_array);
				}
				$this->returnResponse(SUCCESS_RESPONSE, "Call Initated Recorded Successfully.");
			}
		}

		//Finder Post image upload
		public function addFinderPostImage() {

			$upi_pid = $this->validateParameter('upi_pid', $this->param['upi_pid'], INTEGER, false);
			$upi_img = $this->validateParameter('upi_img', $this->param['upi_img'], STRING, false);

			$img = new Image;
			$img->setUpid($upi_pid);
			$img->setUimg($upi_img);
			$data = $img->saveFinderPostedImage();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//Finder Get post images
		public function finderPostedImages() {

			$upi_pid = $this->validateParameter('upi_pid', $this->param['upi_pid'], INTEGER, false);

			$img = new Image;
			$img->setUpid($upi_pid);
			$data = $img->readFinderPostedImages();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//Finder Post delete
		public function deleteFinderPostedImage() {

			$upi_id = $this->validateParameter('upi_id', $this->param['upi_id'], INTEGER, false);
			$upi_pid = $this->validateParameter('upi_pid', $this->param['upi_pid'], INTEGER, false);

			$img = new Image;
			$img->setUid($upi_id);
			$img->setUpid($upi_pid);
			$data = $img->removeFinderPostedImage();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//My Post delete
		public function deleteMyPost() {

			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$p_id = $this->validateParameter('p_id', $this->param['p_id'], INTEGER, false);

			$post = new Post;
			$post->setPuid($u_id);
			$post->setPtid($p_id);
			$data = $post->removeMyPost();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//category and location Get
		public function getMastersDataOfAddPostForm() {

			// Get Locations Masters Data
			$stmt = $this->dbConnect->prepare("SELECT * FROM location");
			$stmt->execute();
			$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Get Categories Masters Data
			$stmt = $this->dbConnect->prepare("SELECT * FROM category");
			$stmt->execute();
			$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$response['locations'] = $locations;
			$response['categories'] = $categories;

			$this->returnResponse(SUCCESS_RESPONSE, $response);

		}

		//Profile image upload
		public function addProfileImage() {

			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$u_img = $this->validateParameter('u_img', $this->param['u_img'], STRING, false);

			$user = new UserConfig;
			$user->setUid($u_id);
			$user->setUimg($u_img);

			if($user->saveProfileImage()){
				$response['data'] = $u_img;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Image not uploaded Properly .. Please Try again");
			}

		}

		//Remove User Profile image
		public function deleteprofileimage() {

			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$user = new UserConfig;
			$user->setUid($u_id);

			if($user->removeProfileImage()){
				$this->returnResponse(SUCCESS_RESPONSE, "Profile photo deleted");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Image not uploaded Properly .. Please Try again");
			}
		}

		//Update User Description
		public function editProfileDescription() {

			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$u_desc = $this->validateParameter('u_desc', $this->param['u_desc'], STRING, false);

			$user = new UserConfig;
			$user->setUid($u_id);
			$user->setUdesc($u_desc);

			if($user->updateProfileDescription()){
				$this->returnResponse(SUCCESS_RESPONSE, "Description Updated Successfully");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Description not Properly saved.. Please Try again");
			}

		}

		//Add User Config
		public function addUserConfig() {

			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$u_desc = $this->validateParameter('u_desc', $this->param['u_desc'], STRING, false);

			$us_exp = $this->validateParameter('us_exp', $this->param['us_exp'], STRING, false);
		 	$us_proof = $this->validateParameter('us_proof', $this->param['us_proof'], INTEGER, false);
		 	$us_prfid = $this->validateParameter('us_prfid', $this->param['us_prfid'], STRING, false);
     		$us_typ = $this->validateParameter('us_typ', $this->param['us_typ'], INTEGER, false);
		 	$us_lang = $this->validateParameter('us_lang', $this->param['us_lang'], INTEGER, false);

			$ui_intr = $this->validateParameter('ui_intr', $this->param['ui_intr'], ARRAYTYPE, false);

			$user = new UserConfig;

			$user->setUid($u_id);
			$user->setUdesc($u_desc);

			$user->setUexp($us_exp);
			$user->setUproof($us_proof);
			$user->setUprfid($us_prfid);
			$user->setUtyp($us_typ);
			$user->setUlang($us_lang);

			$user->setUintr($ui_intr);

			if(!$user->createUserConfig()) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to configure the User. Please try again");

			} else {
				$this->returnResponse(SUCCESS_RESPONSE,"User Configration has been updated");
			}
		}

		//Get User Posts
		public function postsByKeyWord(){

			$key_word = $this->validateParameter('key_word', $this->param['key_word'], STRING, false);
			$key_word = $key_word.'%';

			$query = new Query;
			$query->setKeywrd($key_word);
			$data = $query->getPostsByKeyWord();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get User Posts
		public function postsOnCetegorySelect(){

			$cat_id = $this->validateParameter('cat_id', $this->param['cat_id'], INTEGER, false);

			$query = new Query;
			$query->setCatid($cat_id);
			$data = $query->getPostsByCategoryId();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Finder Post image upload
		public function addFixerPostImage() {

			$upi_pid = $this->validateParameter('upi_pid', $this->param['upi_pid'], INTEGER, false);
			$upi_img = $this->validateParameter('upi_img', $this->param['upi_img'], STRING, false);

			$img = new Image;
			$img->setUpid($upi_pid);
			$img->setUimg($upi_img);
			$data = $img->saveFixerPostedImage();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//Finder Get post images
		public function fixerPostedImages() {

			$upi_pid = $this->validateParameter('upi_pid', $this->param['upi_pid'], INTEGER, false);

			$img = new Image;
			$img->setUpid($upi_pid);
			$data = $img->readFixerPostedImages();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//Finder Post Image delete
		public function deleteFixerPostedImage() {

			$upi_id = $this->validateParameter('upi_id', $this->param['upi_id'], INTEGER, false);
			$upi_pid = $this->validateParameter('upi_pid', $this->param['upi_pid'], INTEGER, false);

			$img = new Image;
			$img->setUid($upi_id);
			$img->setUpid($upi_pid);
			$data = $img->removeFixerPostedImage();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		//Finder Select/Unselect the Fixer
		public function finderSelectOrUnselectTheFixer(){

			$ps_pid = $this->validateParameter('ps_pid', $this->param['ps_pid'], INTEGER, false);
			$ps_fdid = $this->validateParameter('ps_fdid', $this->param['ps_fdid'], INTEGER, false);
			$ps_fxid = $this->validateParameter('ps_fxid', $this->param['ps_fxid'], INTEGER, false);
			$ps_stat = $this->validateParameter('ps_stat', $this->param['ps_stat'], STRING, false);

			$model = new PostSelect;
			$model->setPid($ps_pid);
			$model->setPfid($ps_fdid);
			$model->setPfxid($ps_fxid);
			$model->setPstat($ps_stat);
			$model->setUpdt(INSERT_DATE_TIME);

			if($ps_stat==5){
				$user 			= 	new User;
				$user->setUid($ps_fxid);
				$getUserData 	=	$user->checkUserId();

				$getData		=	$model->getPostTitleAndUserNumber();
				$redirect_array	=	array('pagename' => '',
					   					'param'	=>	'');
				if($getUserData['push_notify_id']!=''){
					$title 		=	(count($getData['p_tit'])>6)?substr($getData['p_tit'],0,6).'...':$getData['p_tit']." post";
					$this->sendMessage('Goffix: Your selected as "'.$getData['cat_name'].'" for "'.$title.'"', '', $getUserData['push_notify_id'], array(), false, $redirect_array);
				}
				if($model->recordExistUpdateOrNotExistInsert()){
					$getData		=	$model->getPostTitleAndUserNumber();
					$getfixerdata 	= 	$model->getPostTitleAndUserNumberfixer();
					$msg = urlencode('GOFFIX: %0A You have selected "'.$getUserData['u_nm'].'" for '.$getData['p_tit'].' post. Sit back '.$getUserData['u_nm'].' will be in touch with you');
	      			$url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getData['u_phn']."&from=GOFFIX&message=".$msg;
	       			$this->sendMsg($url);


	       			$msg1 = urlencode('GOFFIX: %0A You are selected for your '.$getfixerdata['p_tit'].' post');
	      			$url1 =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getfixerdata['u_phn']."&from=GOFFIX&message=".$msg1;
	       			$this->sendMsg($url1);

					$this->returnResponse(SUCCESS_RESPONSE, "Finder has been selected the fixer");
				}else{
					$this->returnResponse(FAILURE_RESPONSE, "Unable the select the fixer .. please try again");
				}
			}

			if($ps_stat == 2){

				$user 			= 	new User;
				$user->setUid($ps_fxid);
				//$getUserData 	=	$user->checkUserId();
					$getUserData 	=	$user->checkUserId();
					$redirect_array	=	array('pagename' => '',
					   					'param'	=>	'');
				if($getUserData['push_notify_id']!=''){
					$this->sendMessage($getUserData['u_nm']. ' Finder UnSelected', "Got message for yout post", $getUserData['push_notify_id'], array(), false, $redirect_array);
				}

				if($model->updateRecord()){
					$this->returnResponse(SUCCESS_RESPONSE, "Finder has been unselected the fixer");
				}else{
					$this->returnResponse(FAILURE_RESPONSE, "Unable the unselect the fixer .. please try again");
				}
			}
		}

		//Fixer Accept the Post

		//Fixer Reject/Unselect the Post
		public function fixerDenyPost(){

			$ps_pid = $this->validateParameter('ps_pid', $this->param['ps_pid'], INTEGER, false);
			$ps_fdid = $this->validateParameter('ps_fdid', $this->param['ps_fdid'], INTEGER, false);
			$ps_fxid = $this->validateParameter('ps_fxid', $this->param['ps_fxid'], INTEGER, false);
			$ps_stat = $this->validateParameter('ps_stat', $this->param['ps_stat'], INTEGER, false);

			$model = new PostSelect;
			$model->setPid($ps_pid);
			$model->setPfid($ps_fdid);
			$model->setPfxid($ps_fxid);
			$model->setPstat($ps_stat);

			if($model->denyPost()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Post has been denyed');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		//Save Finder Post Completion With Rating
		public function finderPostComplete(){

			$pfd_fdid = $this->validateParameter('pfd_fdid', $this->param['pfd_fdid'], INTEGER, false);
			$pfd_fxid = $this->validateParameter('pfd_fxid', $this->param['pfd_fxid'], INTEGER, false);
			$pfd_pid = $this->validateParameter('pfd_pid', $this->param['pfd_pid'], INTEGER, false);
			$pfd_rate = $this->validateParameter('pfd_rate', $this->param['pfd_rate'], STRING, false);
			$pfd_mode = $this->validateParameter('pfd_mode', $this->param['pfd_mode'], STRING, false);
			$pfd_ramt = $this->validateParameter('pfd_ramt', $this->param['pfd_ramt'], INTEGER, false);
			$comment = $this->validateParameter('comment', $this->param['comment'], STRING, false);

			$finder = new Finder;
			$finder->setPfdid($pfd_fdid);
			$finder->setPfxid($pfd_fxid);
			$finder->setPid($pfd_pid);
			$finder->setPrate($pfd_rate);
			$finder->setPfmode($pfd_mode);
			$finder->setPramt($pfd_ramt);
			$finder->setComment($comment);

			if($finder->insertRecord()){
				$user 			= 	new User;
				//$user->setUid($pfd_fxid);
				$user->setUid($pfd_fxid);
				$getUserData 	=	$user->checkUserId();

				$redirect_array	=	array('pagename' => '',
					   					'param'	=>	'');
				if($getUserData['push_notify_id']!=''){
					$this->sendMessage('Finder Gave Rating', "Got message for yout post", $getUserData['push_notify_id'], array(), false, $redirect_array);
				}

				$getData 	=	$finder->getFinderPostTitleAndUserNumber();
				$msg = urlencode("GOFFIX: %0A Finder has been closed the post and given rating for this post-".$getData['p_tit']);
	      			$url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getData['u_phn']."&from=GOFFIX&message=".$msg;
	       			$this->sendMsg($url);
				$this->returnResponse(SUCCESS_RESPONSE, 'Finder has been closed the post');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		// Save Fixer Post Completion With Rating
		public function fixerPostComplete(){

			$pfx_fxid = $this->validateParameter('pfx_fxid', $this->param['pfx_fxid'], INTEGER, false);
			$pfx_fdid = $this->validateParameter('pfx_fdid', $this->param['pfx_fdid'], INTEGER, false);
			$pfx_rate = $this->validateParameter('pfx_rate', $this->param['pfx_rate'], STRING, false);
			$pfx_pid = $this->validateParameter('pfx_pid', $this->param['pfx_pid'], INTEGER, false);
			$comment = $this->validateParameter('comment', $this->param['comment'], STRING, false);

			$fixer = new Fixer;
			$fixer->setPfxid($pfx_fxid);
			$fixer->setPfdid($pfx_fdid);
			$fixer->setPrate($pfx_rate);
			$fixer->setPid($pfx_pid);
			$fixer->setComment($comment);

			if($fixer->insertRecord()){
				$user 			= 	new User;
				$user->setUid($pfx_fdid);
				$getUserData 	=	$user->checkUserId();
				//print_r($getUserData);
				//echo $getUserData."test no";exit;
				if($getUserData['push_notify_id']!=''){
					//$this->sendMessage('Fixer Gave Rating', "", $getUserData['push_notify_id'], array(), false, 'messages');
				}

				$getData 	=	$fixer->getFixerPostTitleAndUserNumber();
				//$msg = urlencode("GOFFIX: %0A Fixer has been rated for this post-".$getData['p_tit']);
	      			//$url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getData['u_phn']."&from=GOFFIX&message=".$msg;
	       			//$this->sendMsg($url);
				$this->returnResponse(SUCCESS_RESPONSE, 'Fixer has been closed the post');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		//Save Scratch Card Data
		public function addScratchCard(){

			$sc_uid = $this->validateParameter('sc_uid', $this->param['sc_uid'], INTEGER, false);
			$sc_utype = $this->validateParameter('sc_utype', $this->param['sc_utype'], INTEGER, false);
			$sc_pid = $this->validateParameter('sc_pid', $this->param['sc_pid'], INTEGER, false);
			$sc_won = $this->validateParameter('sc_won', $this->param['sc_won'], INTEGER, false);
			$sc_sdt = $this->validateParameter('sc_sdt', $this->param['sc_sdt'], STRING, false);

			$card = new ScratchCard;
			$card->setScid($sc_uid);
			$card->setStype($sc_utype);
			$card->setSpid($sc_pid);
			$card->setSwon($sc_won);
			$card->setSscdt($sc_sdt);

			if($card->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Scratch Card has been Added');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		//Add Amount to Wallet
 		public function addAmountToWallet(){

			$w_uid = $this->validateParameter('w_uid', $this->param['w_uid'], INTEGER, false);
			$w_amt = $this->validateParameter('w_amt', $this->param['w_amt'], INTEGER, false);
			$w_pid = $this->validateParameter('w_pid', $this->param['w_pid'], INTEGER, false);
			$w_mode = $this->validateParameter('w_mode', $this->param['w_mode'], INTEGER, false);

			$card = new Wallet;
			$card->setWuid($w_uid);
			$card->setWamt($w_amt);
			$card->setWpid($w_pid);
			$card->setWmode($w_mode);

			if($card->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Amount has been Added To Wallet');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		//Add Subscription Data
		public function addSubscription(){

			$s_uid = $this->validateParameter('s_uid', $this->param['s_uid'], INTEGER, false);
			$s_amt = $this->validateParameter('s_amt', $this->param['s_amt'], INTEGER, false);
			$s_disc = $this->validateParameter('s_disc', $this->param['s_disc'], INTEGER, false);
			$s_vdt = $this->validateParameter('s_vdt', $this->param['s_vdt'], STRING, false);
			$s_stat = $this->validateParameter('s_stat', $this->param['s_stat'], INTEGER, false);

			$card = new Subscription;
			$card->setSuid($s_uid);
			$card->setSamt($s_amt);
			$card->setSdisc($s_disc);
			$card->setSvdt($s_vdt);
			$card->setStat($s_stat);

			if($card->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Subscription has been Added');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		// Profile post based on UID
		public function getPostedJobsActivitiesfromuid(){

			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$utyp 			= $this->validateParameter('utyp', $this->param['utyp'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setUtype($utyp);
			$query->setLimitForUserPosts($startPost, $limitPost);

			$data 			= $query->getPostedJobsfromUID();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// Profile post based on UID
		public function getUserPostedJobsByID(){

			$uid 			= $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$sessionid 		= $this->validateParameter('sessionid', $this->param['sessionid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setSessionId($sessionid);
			$query->setLimitForUserPosts($startPost, $limitPost);

			$data 			= $query->getUserPostedJobs();

			if(is_array($data) && count($data)>0){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// Profile saved post in post id
		public function getsavedPostedJobspostid(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$startPost 		= $this->validateParameter('startPost', $this->param['startPost'], INTEGER, false);
			$limitPost 		= $this->validateParameter('limitPost', $this->param['limitPost'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setLimitForSavedPosts($startPost, $limitPost);
			$data = $query->getsavedPostedpostid();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}


				// Profile saved post in post id
				public function getrequestedPosted(){

					$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
					$query = new Query;
					$query->setUid($uid);
					$data = $query->getreqPostedpostid();

					if(is_array($data) && count($data)>0){
						$response['data'] = $data;
						$this->returnResponse(SUCCESS_RESPONSE, $response);
					}else{
						$this->returnResponse(FAILURE_RESPONSE, "No data found");
					}
				}




		public function fixer_show_activejobs(){

			$ps_fxid = $this->validateParameter('pfx_fxid', $this->param['pfx_fxid'], INTEGER, false);
			$post_select = new PostSelect;
			$post_select->setPfxid($ps_fxid);
			$data = $post_select->fixer_show_activejobs();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		public function fixer_show_activejobsnew(){

			$ps_fxid = $this->validateParameter('pfx_fxid', $this->param['pfx_fxid'], INTEGER, false);
			$ps_id = $this->validateParameter('ps_id', $this->param['ps_id'], INTEGER, false);
			$post_select = new PostSelect;
			$post_select->setPfxid($ps_fxid);
			$post_select->setpsid($ps_id);
			$data = $post_select->fixer_show_activejobsnew();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		public function fixer_complete_postselect_stat(){

			$ps_id = $this->validateParameter('ps_id', $this->param['ps_id'], INTEGER, false);
			$post_select = new PostSelect;
			$post_select->setpsid($ps_id);

			if($post_select->fixer_complete_postselect_stat()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Fixer has been Completed the post');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}
		}

		public function myFixersAcceptedPosts(){

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$query = new Query;
			$query->setUid($uid);
			$data = $query->getMyFixersAcceptedPosts();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function userJobsCountAndWorksCountAndRating(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$utype = $this->validateParameter('utype', $this->param['utype'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setUtype($utype);
			$data = $query->getJobsCountAndWorksCountAndRating();
			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);

		}

		public function savePost(){

			$sp_uid = $this->validateParameter('sp_uid', $this->param['sp_uid'], INTEGER, false);
			$sp_pid = $this->validateParameter('sp_pid', $this->param['sp_pid'], INTEGER, false);

			$sp = new SavedPosts;
			$sp->setSpuid($sp_uid);
			$sp->setSppid($sp_pid);

			$savedData 		=	$sp->getSavedPost();
			if(count($savedData)>0){
				//	$this->returnResponse(123, "Post already saved");
			if($sp->removeRecord()){
				$this->returnResponse(123, 'Post has been Unsaved');
			}

			}

			if($sp->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Post has been Saved');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}

		}

		public function reportPost(){

			$rp_uid = $this->validateParameter('rp_uid', $this->param['rp_uid'], INTEGER, false);
			$rp_pid = $this->validateParameter('rp_pid', $this->param['rp_pid'], INTEGER, false);
			$rp_des = $this->validateParameter('rp_des', $this->param['rp_des'], STRING, false);

			$rp = new ReportedPosts;
			$rp->setRpuid($rp_uid);
			$rp->setRppid($rp_pid);
			$rp->setRpdes($rp_des);

			$reportData 		=	$rp->getReportPost();
			if(count($reportData)>0){
				$this->returnResponse(123, "Thanks for your Feedback!");
			}

			if($rp->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Post has been Saved');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}

		}

		public function createAdd(){

			$a_type = $this->validateParameter('a_type', $this->param['a_type'], INTEGER, false);
			$a_img = $this->validateParameter('a_img', $this->param['a_img'], STRING, false);
			$a_link = $this->validateParameter('a_link', $this->param['a_link'], STRING, false);
			$a_note = $this->validateParameter('a_note', $this->param['a_note'], STRING, false);

			$add = new Adds;
			$add->setAtype($a_type);
			$add->setAimg($a_img);
			$add->setAlink($a_link);
			$add->setAnote($a_note);

			if($rp->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Add has been Created');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}

		}

		public function ChangePostStatus(){

			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);

			$query = new Query;
			$query->setPid($pid);

			$data = $query->updatePostStatus();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function findertofixer(){

			$us_exp = $this->validateParameter('us_exp', $this->param['us_exp'], STRING, false);
			$us_proof = $this->validateParameter('us_proof', $this->param['us_proof'], INTEGER, false);
			$us_prfid = $this->validateParameter('us_prfid', $this->param['us_prfid'], STRING, false);
			$us_uid = $this->validateParameter('us_uid', $this->param['us_uid'], INTEGER, false);

			$query = new Query;
			$query->setUs_exp($us_exp);
			$query->setUs_proof($us_proof);
			$query->setUs_prfid($us_prfid);
			$query->setUs_uid($us_uid);

			$data = $query->updatefindertofixer();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}



		public function updatework(){

			$fdid = $this->validateParameter('fdid', $this->param['fdid'], INTEGER, false);
			$fxid = $this->validateParameter('fxid', $this->param['fxid'], INTEGER, false);

			$query = new Query;
			$query->setFdid($fdid);
			$query->setFxid($fxid);

			$data = $query->updateWorkStatus();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function updatelanguage(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$lid = $this->validateParameter('lid', $this->param['lid'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setLid($lid);

			$data = $query->updateLangStatus();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function passwordupdate(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$new_pwd = $this->validateParameter('new_pwd', $this->param['new_pwd'], STRING, false);
			$cnf_new_pwd = $this->validateParameter('cnf_new_pwd', $this->param['cnf_new_pwd'], STRING, false);

			$query = new Query;
			$query->setUid($uid);
			//$query->setNew_Pwd($new_pwd);
			$query->setCnf_New_Pwd($cnf_new_pwd);

			$data = $query->updatePassword();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}


		public function changefixerstat(){

			$fxuid = $this->validateParameter('fxuid', $this->param['fxuid'], INTEGER, false);
			$pid = $this->validateParameter('stat', $this->param['fxstat'], INTEGER, false);
			$fxpid = $this->validateParameter('fxpid', $this->param['fxpid'], INTEGER, false);



			$query = new Query;
			$query->setPid($pid);
			$query->setfxuid($fxuid);
			$query->setfxpid($fxpid);

			$data = $query->updatefixerstat();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		// public function userRating(){

		// 	$uid = $this->validateParameter('uid', $this->param['uid'], STRING, false);
		// 	$utype = $this->validateParameter('utype', $this->param['utype'], STRING, false);

		// 	$query = new Query;
		// 	$query->setUid($uid);
		// 	$query->setUtype($utype );
		// 	$rating = $query->getUserRating();

		// 	if($rating){
		// 		$response['data'] = $rating;
		// 		$this->returnResponse(SUCCESS_RESPONSE, $response);
		// 	}else{
		// 		$this->returnResponse(FAILURE_RESPONSE, "No data found");
		// 	}

		// }

		public function myPostsAndRequestedPosts(){

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$query = new Query;
			$query->setUid($uid);
			$data = $query->getMyPostsAndRequestedPosts();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function myPostRespondersAndCount(){

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$pid = $this->validateParameter('p_id', $this->param['p_id'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$query->setPid($pid);
			$data = $query->getMyPostRespondersAndCount();
			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}

		public function myImage(){

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->getMyImage();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}

		}

		public function postSelectUnselectStatus(){

			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);

			$query = new Query;
			$query->setPid($pid);
			$data = $query->getPostSelectUnselectStatus();
			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);

		}

		public function issueScratchCard(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$pid = $this->validateParameter('pid', $this->param['pid'], INTEGER, false);

			$query = new Query;
			$data = $query->generateScratchCard($uid,$pid);

			$sc = new ScratchCard;
			$sc->setScid($data['sc_uid']);
			$sc->setSpid($data['sc_pid']);
			$sc->setSwon($data['sc_won']);
			$sc->setSaid($data['sa_id']);

			if($sc->insertRecord()){
				$this->returnResponse(SUCCESS_RESPONSE, 'Scratch Card has been issued');
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "Unable to process.. please try again");
			}

		}

		//Get Scratch Cards
		public function userScratchCards() {

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->getUserScratchCards();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}
		//Get Scratch Cards
		public function userScratchCards_noti() {

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->getUserScratchCards_noti();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}



		// Cahnge Scratch Card View Status
		public function ChangeScratchCardViewStatus(){

			$scid = $this->validateParameter('scid', $this->param['scid'], INTEGER, false);

			$query = new Query;
			$query->setScid($scid);

			// $data = $query->updateScratchCardViewStatus();

			if($query->updateScratchCardViewStatus()){
				// $response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, "ScratchCard Status Updated");
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get All Adds
		public function Adds() {

			$query = new Query;
			$data = $query->getAdds();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//User Total Money Eearnings Till Now
		public function userTotalMoneyEarnings(){

			$uid = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->getTotalMoney();
			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}


		//Logout
		public function logout(){

			// Get currently logged client token
			$token = $this->getBearerToken();

			// delete this token from redis
			if($this->redis->del($token)){
				$message = 'Logged Out Successfully';
				$this->returnResponse(SUCCESS_RESPONSE, $message);
			}

			// Logout From All Devises

			//get data from redis keys
			// $tokensList = $this->redis->keys("*");

			// Find Token Data Index Value
			// $index = array_search($token, $tokensList);

			// Get the token data
			// $tokenData = $this->redis->hgetall($tokensList[$index]);

			// delete this token from white list
			// $this->redis->del($token);

			// Add this token to black list
			// $this->redis->hmset($token , array(
			// 	"token_type" => "black",
			// 	"user_id" => $tokenData['user_id'],
			// 	"issued_at" => $tokenData['issued_at'],
			// 	"exp_time" => $tokenData['exp_time'])
			// );

			// $this->redis->expire($token,($tokenData['exp_time'] - $tokenData['issued_at']));

			// $this->redis->expire($token,($tokenData['exp_time'] - $tokenData['issued_at']));

		}

		//push notification
		public function add_pushToken()
		{
			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);
			$pushTokenId = $this->validateParameter('pushnotify_user_id', $this->param['pushnotify_user_id'], STRING, false);

			$user = new User;
			$user->setUid($u_id);
			$user->setPushTokenid($pushTokenId);
			$data = $user->add_pushtoken();

			$response['data'] = $data;
			$this->returnResponse(SUCCESS_RESPONSE, $response);
		}

		public function sendMessage($title, $message, $tokenId, array $extraParams, bool $isTest = false, $pageredirect)
		{
			$content = array(
		        "en" => $title,
		        );

			$fields = array(
				'app_id' => "7a6bdf05-4f70-4f62-94ef-99662766ec04",
				'include_player_ids' => array($tokenId),
				'data' => $pageredirect,
				'large_icon' =>"ic_launcher_round.png",
				'contents' => $content
			);

			$fields = json_encode($fields);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
			                           'Authorization: Basic YzFiMzIwZDEtNjZhNi00ZjEzLTg5OTQtYjg3ZjEwNTI1YzZm'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$response = curl_exec($ch);
			curl_close($ch);

			$return["allresponses"] = $response;
			return $return = json_encode( $return);
		}

		public function checkNewMsgs()
		{
			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$msg = new Message;
			$msg->setuid($u_id);

			$data = $msg->checkNewMsgsCount();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function checkNewMsgschange()
		{
			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$msg = new Message;
			$msg->setuid($u_id);

			$data = $msg->checkNewMsgsCountchange();

			if($data){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function finderselectfixerform()
		{
			$fdate = $this->validateParameter('fdate', $this->param['fdate'], STRING, false);
			$ftime = $this->validateParameter('ftime', $this->param['ftime'], STRING, false);
			$flocation = $this->validateParameter('flocation', $this->param['flocation'], STRING, false);
			$fprice = $this->validateParameter('fprice', $this->param['fprice'], STRING, false);
			$fphone = $this->validateParameter('fphone', $this->param['fphone'], INTEGER, false);
			$ps_pid = $this->validateParameter('ps_pid', $this->param['ps_pid'], INTEGER, false);
			$ps_fdid = $this->validateParameter('ps_fdid', $this->param['ps_fdid'], INTEGER, false);
			$ps_fxid = $this->validateParameter('ps_fxid', $this->param['ps_fxid'], INTEGER, false);


			$model = new PostSelect;
			$model->setFdate($fdate);
			$model->setFtime($ftime);
			$model->setFlocation($flocation);
			$model->setFprice($fprice);
			$model->setFphone($fphone);
			$model->setPid($ps_pid);
			$model->setPfid($ps_fdid); 
			$model->setPfxid($ps_fxid);


			$finderid=$model->createFindersubmitform();
			if($finderid){
				$getData		=	$model->getfinderdetails();
				//print_r($getData); exit;
				$msg = urlencode("GOFFIX: Finder Selected you as a Fixer and here is the details:- Location: ".$getData['flocation']." & Date and Time : ".$getData['fdate'].":".$getData['ftime']." & Price : ".$getData['fprice']."& Phone : ".$getData['fphone']."");
	      			$url =  "http://sms.sonicsoftsolutions.in/spanelv2/api.php?username=goffix&password=123456&to=".$getData['u_phn']."&from=GOFFIX&message=".$msg;
	       			$this->sendMsg($url);
				//$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $finderid);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function getlocationid()
		{
			$loc_name = $this->validateParameter('loc_name', $this->param['loc_name'], STRING, false);

			$user = new User;
			$user->setloc_name($loc_name);

			$data = $user->getLocation_id();

			if($data){
				//$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function getsearchnamecat()
		{
			//$loc_name = $this->validateParameter('loc_name', $this->param['loc_name'], STRING, false);

			$query = new Query;
			//$user->setloc_name($loc_name);

			$data = $query->genamesofcat();

			if($data){
				//$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		public function getsearchnameloc()
		{
			//$loc_name = $this->validateParameter('loc_name', $this->param['loc_name'], STRING, false);

			$query = new Query;
			//$user->setloc_name($loc_name);

			$data = $query->genamesofloc();

			if($data){
				//$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}
		


	}

 ?>
