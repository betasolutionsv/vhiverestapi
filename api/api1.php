
<?php

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
			$user = $user->checkUserExist();

			if(!is_array($user)) {
				$this->returnResponse(INVALID_USER_PASS, "Mobile Number is Not exist.");
			}else{
				if(!password_verify($u_pwd,$user['u_pwd'])){
					$this->returnResponse(INVALID_USER_PASS, "Mobile Number or Password is Incorrect.");
				}
			}

			// if( $user['active'] == 0 ) {
			// 	$this->returnResponse(USER_NOT_ACTIVE, "User is not activated. Please contact to admin.");
			// }

			try {

				$paylod = [
					'iat' => time(),
					'iss' => 'localhost',
					'exp' => time() + (24*3600),
					'userId' => $user['u_id']
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
				$data['token'] =  $token;

				$this->returnResponse(SUCCESS_RESPONSE, $data);

			} catch (Exception $e) {
				$this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
			}
		}

		//Save User
		public function register() {

			$u_nm = $this->validateParameter('u_nm', $this->param['u_nm'], STRING, false);
			$u_phn = $this->validateParameter('u_phn', $this->param['u_phn'], STRING, false);
			$u_email = $this->validateParameter('u_email', $this->param['u_email'], STRING, false);
			$u_pwd = $this->validateParameter('u_pwd', $this->param['u_pwd'], STRING, false);
			$u_city = $this->validateParameter('u_city', $this->param['u_city'], STRING, false);
			$u_pfn = $this->validateParameter('u_pfn', $this->param['u_pfn'], STRING, false);

			$user = new User;
			$user->setUnm($u_nm);
			$user->setUphn($u_phn);
			$user->setUemail($u_email);
			$user->setUpwd($u_pwd);
			$user->setUcity($u_city);
			$user->setUpfn($u_pfn);
			$id=$user->createUser();

			if(!$id) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to register the User Details. Please try again");
			} else {
				$response['data'] = $id;
				$this->returnResponse(SUCCESS_RESPONSE,$response);
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

			$id = $post->createPost();

			if($id) {
				$response['data'] = $id;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			} else {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to Post the Job. please try again");
			}
		}

		//Get All Posted jobs
		public function postedJobs() {

			$query = new Query;
			$data = $query->getPostedJobs();

			if(is_array($data)){
				$response['data'] = $data;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}

		//Get My Posted jobs
		public function myPostedJobs() {

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->getMyPostedJobs();

			if(is_array($data)){
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

		//Get My Requested Jobs
		public function myRequestedPosts(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);

			$query = new Query;
			$query->setUid($uid);
			$data = $query->getMyRequestedPosts();

			if(is_array($data)){
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

			$cat_id = $this->validateParameter('cat_id', $this->param['cat_id'], INTEGER, false);
			$query = new Query;
			$query->setCatid($cat_id);
			$data = $query->getfixersByCategoryId();

			if(is_array($data)){
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

		//Save Message
		public function addMessage() {

			$m_body = $this->validateParameter('m_body', $this->param['m_body'], STRING, false);
			$m_pid = $this->validateParameter('m_pid', $this->param['m_pid'], INTEGER, false);
			$m_sid = $this->validateParameter('m_sid', $this->param['m_sid'], INTEGER, false);
			$m_rid = $this->validateParameter('m_rid', $this->param['m_rid'], INTEGER, false);

			$msg = new Message;
			$msg->setMbody($m_body);
			$msg->setMpid($m_pid);
			$msg->setMsid($m_sid);
			$msg->setMrid($m_rid);

			if(!$msg->createMessage()) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to catch Message. please try again");
			} else {
				$this->returnResponse(SUCCESS_RESPONSE, "Message Recorded Successfully.");
			}
		}



		//Save Phone Call Initation
		public function addCall() {

			$pc_pid = $this->validateParameter('pc_pid', $this->param['pc_pid'], INTEGER, false);
			$pc_fxid = $this->validateParameter('pc_fxid', $this->param['pc_fxid'], INTEGER, false);
			$pc_fdid = $this->validateParameter('pc_fdid', $this->param['pc_fdid'], INTEGER, false);
			$pc_body = $this->validateParameter('pc_body', $this->param['pc_body'], STRING, false);

			$call = new PhoneCall;
			$call->setPcid($pc_pid);
			$call->setPfxid($pc_fxid);
			$call->setPfdid($pc_fdid);
			$call->setPbody($pc_body);

			if(!$call->createCall()) {
				$this->returnResponse(FAILURE_RESPONSE, "Unable to catch the Call. please try again");
			} else {
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
		public function deleteProfileImage() {

			$u_id = $this->validateParameter('u_id', $this->param['u_id'], INTEGER, false);

			$user = new UserConfig;
			$user->setUid($u_id);

			if($user->removeProfileImage()){
				$this->returnResponse(SUCCESS_RESPONSE, "Profile photo deleted successfully");
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
			$ps_stat = $this->validateParameter('ps_stat', $this->param['ps_stat'], INTEGER, false);

			$model = new PostSelect;
			$model->setPid($ps_pid);
			$model->setPfid($ps_fdid);
			$model->setPfxid($ps_fxid);
			$model->setPstat($ps_stat);

			if($ps_stat == 0){

				if($model->recordExistUpdateOrNotExistInsert()){
					$this->returnResponse(SUCCESS_RESPONSE, "Finder has been selected the fixer");
				}else{
					$this->returnResponse(FAILURE_RESPONSE, "Unable the select the fixer .. please try again");
				}
			}

			if($ps_stat == 2){

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

			$finder = new Finder;
			$finder->setPfdid($pfd_fdid);
			$finder->setPfxid($pfd_fxid);
			$finder->setPid($pfd_pid);
			$finder->setPrate($pfd_rate);
			$finder->setPfmode($pfd_mode);
			$finder->setPramt($pfd_ramt);

			if($finder->insertRecord()){
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

			$fixer = new Fixer;
			$fixer->setPfxid($pfx_fxid);
			$fixer->setPfdid($pfx_fdid);
			$fixer->setPrate($pfx_rate);
			$fixer->setPid($pfx_pid);

			if($fixer->insertRecord()){
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
		public function getPostedJobsfromuid(){

			$uid = $this->validateParameter('uid', $this->param['uid'], INTEGER, false);
			$query = new Query;
			$query->setUid($uid);
			$data = $query->getPostedJobsfromUID();

			if(is_array($data)){
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
	}

 ?>
