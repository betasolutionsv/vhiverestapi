<?php

	class Query {

        private $dbConnect;

        private $cat_id;
        function setCatid($cat_id) { $this->cat_id = $cat_id; }
        function getCatid() { return $this->cat_id; }

        private $uid;
        function setUid($uid) { $this->uid = $uid; }

        private $sessionid;
        function setSessionId($sessionid){ $this->sessionid = $sessionid; }

        private $pid;
        private $fxuid;
        private $fxpid;
        private $sid;
        private $rid;
		private $now;
        private $lid;
        private $cnfnewpwd;

        function setpid($pid) { $this->pid = $pid; }
        function setfxuid($fxuid) { $this->fxuid = $fxuid; }
        function setfxpid($fxpid) { $this->fxpid = $fxpid; }
        function setsid($sid) { $this->sid = $sid; }
        function setrid($rid) { $this->rid = $rid; }
		function setNow($now) { $this->now = $now; }
        function setCnf_New_Pwd($cnfnewpwd) { $this->cnfnewpwd = $cnfnewpwd; }



        private $fxid;
        function setFxid($fxid) { $this->fxid = $fxid; }
		private $ps_fxid;
		function setpsfxid($ps_fxid) { $this->ps_fxid = $ps_fxid; }
        private $fdid;
        function setFdid($fdid) { $this->fdid = $fdid; }
        private $utype;
        function setUtype($utype) { $this->utype = $utype; }
		private $us_exp;
		function setUs_exp($us_exp) { $this->us_exp = $us_exp; }
		private $us_proof;
		function setUs_proof($us_proof) { $this->us_proof = $us_proof; }
		private $us_prfid;
		function setUs_prfid($us_prfid) { $this->us_prfid = $us_prfid; }
		private $us_uid;
		function setUs_uid($us_uid) { $this->us_uid = $us_uid; }

        private $key_word;
        function setKeywrd($key_word) { $this->key_word = $key_word; }
        function getKeywrd() { return $this->key_word; }

        private $scid;
        function setScid($scid) { $this->scid = $scid; }

        function setLid($lid) { $this->lid = $lid; }

        private $startPost;
        private $limitPost;
        private $filter_by_cat;
				private $filter_by_loc;
        function setLimitForPosts($filter_by_cat,$filter_by_loc, $startPost, $limitPost) { $this->filter_by_cat = $filter_by_cat;$this->filter_by_loc = $filter_by_loc; $this->startPost = $startPost; $this->limitPost = $limitPost; }

        function setLimitForMyPosts($startPost, $limitPost){$this->startPost = $startPost; $this->limitPost = $limitPost;}

        function setLimitForUserPosts($startPost, $limitPost){$this->startPost = $startPost; $this->limitPost = $limitPost;}

        function setLimitForChatPosts($startPost, $limitPost){$this->startPost = $startPost; $this->limitPost = $limitPost;}

        function setLimitForSavedPosts($startPost, $limitPost){$this->startPost = $startPost; $this->limitPost = $limitPost;}

        function setLimitForSearch($startPost, $limitPost){$this->startPost = $startPost; $this->limitPost = $limitPost;}

        public function __construct() {
            $db = new Database();
            $this->dbConnect = $db->connect();
        }

        // Get Feed
        public function getPostedJobsCount() {
            $stmt = $this->dbConnect->prepare("SELECT post.p_catid as p_catid,category.cat_name, (SELECT count(*) from usr_s1 WHERE u_pfn = p_catid) as cat_userCount FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where post.p_id NOT in (SELECT ps_pid FROM `post_select` WHERE post_select.ps_stat='0' OR post_select.ps_stat='1') ORDER BY category.cat_name ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

				public function getPostedJobslocCount() {
            $stmt = $this->dbConnect->prepare("SELECT post.p_loc as p_loc,location.loc_name, (SELECT count(*) from usr_s1 WHERE u_city = loc_id) as loc_userCount FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where post.p_id NOT in (SELECT ps_pid FROM `post_select` WHERE post_select.ps_stat='0' OR post_select.ps_stat='1') ORDER BY category.cat_name ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        public function getPostedJobs() {
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            if($this->filter_by_cat!=''){
                $cat_whr    =   " AND post.p_catid IN(".$this->filter_by_cat.")";
            }else{
                $cat_whr    =   "";
            }
            $stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id as p_id,p_tit,p_priority,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s1.u_img,usr_s1.u_gender,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd,(SELECT count(*) FROM `reported_posts` WHERE rp_uid=:u_id and rp_pid=p_id) as report_count, (SELECT count(*) FROM `saved_posts` WHERE sp_pid=p_id AND sp_uid=:u_id) as issaved FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where post.p_id NOT in (SELECT ps_pid FROM `post_select` WHERE post_select.ps_stat='0' OR post_select.ps_stat='1')".$cat_whr." ORDER BY post.p_id DESC".$limit);
            $stmt->bindParam(':u_id',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

			public function getPostedJobs15() {
                
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            if($this->filter_by_cat!='' && $this->filter_by_loc!=''){
                $cat_whr    =   " AND post.p_catid IN(".$this->filter_by_cat.") AND post.p_loc IN(".$this->filter_by_loc.")";
            }else if($this->filter_by_cat!='' && $this->filter_by_loc ==''){
                $cat_whr    =   " AND post.p_catid IN(".$this->filter_by_cat.")";
            }else if($this->filter_by_cat =='' && $this->filter_by_loc!=''){
							$cat_whr    =   " AND post.p_loc IN(".$this->filter_by_loc.")";
						}else{
							$cat_whr    =   "";
						}
            $stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id as p_id,p_tit,p_priority,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s1.u_img,usr_s1.u_gender,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd,(SELECT count(*) FROM `reported_posts` WHERE rp_uid=:u_id and rp_pid=p_id) as report_count, (SELECT count(*) FROM `saved_posts` WHERE sp_pid=p_id AND sp_uid=:u_id) as issaved FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where post.p_id NOT in (SELECT ps_pid FROM `post_select` WHERE post_select.ps_stat='0' OR post_select.ps_stat='1')".$cat_whr." ORDER BY post.p_id DESC".$limit);
            $stmt->bindParam(':u_id',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }
        //Get live Posts
        public function getLivePosts() {
                
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            
            $stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id as p_id,p_tit,p_priority,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s1.u_img,usr_s1.u_gender,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd,(SELECT count(*) FROM `reported_posts` WHERE rp_uid=:u_id and rp_pid=p_id) as report_count, (SELECT count(*) FROM `saved_posts` WHERE sp_pid=p_id AND sp_uid=:u_id) as issaved FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where post.p_id NOT in (SELECT ps_pid FROM `post_select` WHERE post_select.ps_stat='0' OR post_select.ps_stat='1') ORDER BY post.p_id DESC".$limit);
            $stmt->bindParam(':u_id',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // Get My Posts
        public function getMyPostedJobs()
        {
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            $stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id,p_tit,p_amt,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s1.u_img,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where usr_s1.u_id=:u_id ORDER BY post.p_id DESC".$limit);
            $stmt->bindParam(':u_id',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
        }

        // Get My new Posts without Image
        public function getMyPosts()
        {
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            $stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id,p_tit,p_amt,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where usr_s1.u_id=:uid ORDER BY post.p_id DESC".$limit);
            $stmt->bindParam(':uid',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
        }

        // Get My Requested Posts
        // public function getMyRequestedPosts()
        // {
        //     $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
        //     $stmt = $this->dbConnect->prepare("SELECT DISTINCT(m_pid) as p_id,p_tit,MAX(p_dt) as p_dt ,usr_s1.u_id AS puid,usr_s1.u_nm AS nm,u_img,u_gender,usr_s2.us_typ,MAX(messages.m_dt) as m_dt from messages join post on messages.m_pid = post.p_id INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id where (m_sid = :uid) OR (m_rid = :uid) GROUP BY p_id ORDER BY m_dt DESC".$limit);
        //     $stmt->bindParam(':uid', $this->uid);
        //     $stmt->execute();
        //     $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //     return $data;
        // }

        public function getMyRequestedPosts()
        {
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            $stmt = $this->dbConnect->prepare("SELECT DISTINCT(m_pid) as p_id,p_tit,MAX(p_dt) as p_dt ,usr_s1.u_id AS puid,usr_s1.u_nm AS nm,u_img,u_gender,usr_s2.us_typ,MAX(messages.m_dt) as m_dt, (SELECT count(*) FROM `messages` WHERE m_read_stat='0' AND m_rid=:uid AND m_pid=p_id) AS new_msgs_count,(SELECT ps_fxid FROM `post_select` where ps_pid=p_id AND ps_stat = '5') AS ps_fxid, (SELECT ps_stat FROM `post_select` where ps_pid=p_id AND ps_stat = '5') AS ps_stat from messages join post on messages.m_pid = post.p_id INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id where ((m_sid = :uid) OR (m_rid = :uid)) AND post.p_uid!=:uid GROUP BY p_id ORDER BY m_dt DESC".$limit);
            $stmt->bindParam(':uid', $this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

               // Get My Requested Posts - Optimized
               public function getChatMyPosts()
               {
                   $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
                   $stmt = $this->dbConnect->prepare("SELECT DISTINCT * FROM (
        SELECT MAX(pc_pid) AS p_id,
           post.p_tit,
           post.p_dt AS p_dt,
           post.p_uid AS puid,
           usr_s1.u_nm AS u_un,
           post_call.pc_dt AS m_dt,
           post_call.pc_fxid AS m_rid,
           (SELECT count(*) FROM `post_call` WHERE pc_fxid=:uid AND pc_pid=p_id) AS new_calls_count
         FROM
           `post_call`
         INNER JOIN
           post ON post_call.pc_pid = post.p_id
         INNER JOIN
           usr_s1 ON usr_s1.u_id = post_call.pc_fxid
         INNER JOIN
           usr_s2 ON usr_s2.us_uid = post_call.pc_fxid
         WHERE
           pc_fdid = :uid AND p_tit != 'no post' GROUP BY
         p_id
         UNION 
       SELECT MAX(messages.m_pid) AS p_id,
         post.p_tit,
         MAX(post.p_dt) AS p_dt,
         post.p_uid AS puid,
         usr_s1.u_nm AS u_un,
         MAX(messages.m_dt) AS m_dt,
         messages.m_rid AS m_rid,
         
         (SELECT count(*) FROM `messages` WHERE m_read_stat='0' AND m_rid=:uid AND m_pid=p_id) AS new_msgs_count
       FROM
         messages
       INNER JOIN
         post ON messages.m_pid = post.p_id
       INNER JOIN
         `usr_s1` ON usr_s1.u_id = post.p_uid 
       INNER JOIN
         `usr_s2` ON usr_s2.us_uid = post.p_uid 
        
       WHERE
         post.p_uid = :uid and p_tit != 'no post'
       
       GROUP BY
         p_id) AS A GROUP BY
         p_id
       ORDER BY
         m_dt DESC");
                   $stmt->bindParam(':uid', $this->uid);
                   $stmt->execute();
                   $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                   return $data;
               }

        // Get My Requested Posts
        public function getChatPostMyPosts()
        {
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            $stmt = $this->dbConnect->prepare("SELECT DISTINCT * FROM (
 SELECT MAX(pc_pid) AS p_id,
    post.p_tit,
    post.p_dt AS p_dt,
    post.p_uid AS puid,
    usr_s1.u_img AS u_img,
    usr_s1.u_nm AS u_un,
    usr_s1.u_gender AS u_gender,
    usr_s2.us_typ AS us_type,
    post_call.pc_dt AS m_dt,
    post_call.pc_fxid AS m_rid,
    (SELECT count(*) FROM `post_call` WHERE pc_fxid=:uid AND pc_pid=p_id) AS new_calls_count
  FROM
    `post_call`
  INNER JOIN
    post ON post_call.pc_pid = post.p_id
  INNER JOIN
    usr_s1 ON usr_s1.u_id = post_call.pc_fxid
  INNER JOIN
    usr_s2 ON usr_s2.us_uid = post_call.pc_fxid
  WHERE
    pc_fdid = :uid AND p_tit != 'no post' GROUP BY
  p_id
  UNION 
SELECT MAX(messages.m_pid) AS p_id,
  post.p_tit,
  MAX(post.p_dt) AS p_dt,
  post.p_uid AS puid,
  usr_s1.u_img AS u_img,
  usr_s1.u_nm AS u_un,
  usr_s1.u_gender AS u_gender,
  usr_s2.us_typ AS us_type,
  MAX(messages.m_dt) AS m_dt,
  messages.m_rid AS m_rid,
  
  (SELECT count(*) FROM `messages` WHERE m_read_stat='0' AND m_rid=:uid AND m_pid=p_id) AS new_msgs_count
FROM
  messages
INNER JOIN
  post ON messages.m_pid = post.p_id
INNER JOIN
  `usr_s1` ON usr_s1.u_id = post.p_uid 
INNER JOIN
  `usr_s2` ON usr_s2.us_uid = post.p_uid 
 
WHERE
  post.p_uid = :uid and p_tit != 'no post'

GROUP BY
  p_id) AS A GROUP BY
  p_id
ORDER BY
  m_dt DESC");
            $stmt->bindParam(':uid', $this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // Get Posted Job Info
        public function getPostedJobInfo() {

            $stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id,(select ps_stat from `post_select` where ps_pid = :p_id AND (ps_stat='0' OR ps_stat='1')) as ps_stat,p_tit,p_amt,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s1.u_img,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id where p_id =:p_id ORDER BY post.p_id DESC");
            $stmt->bindParam(':p_id',$this->pid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						return $data;
        }

				public function getPostsforfixer() {
					//echo $this->now; exit;
            $stmt = $this->dbConnect->prepare("SELECT count(*) FROM `post_select` WHERE `ps_fxid`=:ps_fxid AND `ps_stat`='1' AND DATE(ps_dt) = :now");
            $stmt->bindParam(':ps_fxid',$this->ps_fxid);
						$stmt->bindParam(':now',$this->now);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						return $data;
        }

		// Get Posts base on UID
        public function getPostedJobsfromUID(){
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';

           $stmt = $this->dbConnect->prepare("SELECT post_select.ps_dt AS ps_dt, post_select.ps_pid AS ps_pid, post_select.ps_stat AS ps_stat, post.p_tit AS p_tit,post.p_priority AS p_priority, usr_s1.u_img AS u_img, usr_s1.u_nm AS u_nm, usr_s1.u_id AS u_id, category.cat_name AS cat_name, post_select.ps_fdid as ps_fdid,usr_s1.u_gender AS u_gender, post_select.ps_fxid as ps_fxid FROM post_select INNER JOIN `post` ON post.p_id=post_select.ps_pid INNER JOIN `usr_s1` ON usr_s1.u_id=post.p_uid INNER JOIN `category` ON post.p_catid=category.cat_id WHERE (post_select.ps_stat='5' OR post_select.ps_stat='1') and (ps_fxid=:uid OR ps_fdid=:uid) ORDER BY ps_dt DESC".$limit);

            
            $stmt->bindParam(':uid', $this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // Get Posts base on UID
        public function getUserPostedJobs(){
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';

            $stmt = $this->dbConnect->prepare("SELECT *, (SELECT count(*) FROM `reported_posts` WHERE rp_uid=:sessionid and rp_pid=p_id) as report_count, (SELECT count(*) FROM `saved_posts` WHERE sp_pid=p_id AND sp_uid=:sessionid) as issaved FROM `post` INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id INNER JOIN `usr_s1` ON usr_s1.u_id=post.p_uid WHERE p_uid=:uid ORDER BY p_id DESC".$limit);
            $stmt->bindParam(':uid', $this->uid);
            $stmt->bindParam(':sessionid', $this->sessionid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

		// Get saved Posts base on UID
		public function getsavedPostedpostid(){
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';

			$stmt = $this->dbConnect->prepare("SELECT post.p_dt,post.p_id,p_tit,p_amt,p_ctyp,usr_s1.u_id,usr_s1.u_nm,usr_s1.u_phn,usr_s1.u_img,usr_s2.us_typ,category.cat_name,location.loc_name,post.p_jd, (SELECT count(*) FROM `reported_posts` WHERE rp_uid=:uid and rp_pid=p_id) as report_count FROM `saved_posts` INNER JOIN `post` ON post.p_id=saved_posts.sp_pid INNER JOIN `usr_s1` ON usr_s1.u_id=post.p_uid INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN `category` ON post.p_catid=category.cat_id INNER JOIN `location` ON post.p_loc=location.loc_id WHERE saved_posts.sp_uid=:uid ORDER BY saved_posts.sp_pid DESC".$limit);
			$stmt->bindParam(':uid', $this->uid);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

		// Get saved Posts base on UID
		public function getreqPostedpostid(){
				// SELECT * FROM `post` WHERE `p_uid`=''
				$stmt = $this->dbConnect->prepare("SELECT * FROM `post` INNER JOIN `post_select` ON post.p_id=post_select.ps_pid INNER JOIN `usr_s1` ON usr_s1.u_id=post.p_uid WHERE post_select.ps_fxid=:uid AND post_select.ps_stat = '0' ORDER BY post_select.ps_pid DESC");
				$stmt->bindParam(':uid', $this->uid);
				$stmt->execute();
				$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $data;
		}

        // Get Chat Data
        public function getChat(){

            // $stmt = $this->dbConnect->prepare("SELECT MAX(m_id) AS mid,m_sid,m_rid,MAX(m_dt) AS dt,MAX(m_body)AS body,post.p_uid,r.u_nm FROM `messages` LEFT JOIN `post` ON messages.m_pid=post.p_id RIGHT JOIN `usr_s1` ON usr_s1.u_id=post.p_uid  INNER JOIN `usr_s1` AS r ON messages.m_sid = r.u_id WHERE post.p_uid=:uid AND m_pid=:pid AND m_sid NOT IN (SELECT m_sid FROM messages WHERE m_pid=:pid AND m_sid=:uid) GROUP BY m_sid ORDER BY MAX(m_dt) DESC");
            // $stmt->bindParam(':pid',$this->pid);
            // $stmt->bindParam(':uid',$this->uid);
            // $stmt->execute();
            // $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						// (SELECT MAX(m_id) AS mid,usr_s1.u_img AS img,m_pid,m_sid,m_rid,MAX(m_dt) AS dt,MAX(m_body)AS body,post.p_uid,r.u_nm,0 as type FROM `messages` LEFT JOIN `post` ON messages.m_pid=post.p_id RIGHT JOIN `usr_s1` ON usr_s1.u_id=post.p_uid INNER JOIN `usr_s1` AS r ON messages.m_sid = r.u_id WHERE post.p_uid=41 AND m_pid=3 AND m_sid NOT IN (SELECT m_sid FROM messages WHERE m_pid=3 AND m_sid=41) GROUP BY m_sid)
// UNION ALL
// (SELECT MAX(pc_id) AS pcid,usr_s1.u_img AS img,pc_pid,pc_fxid as pc_cid,pc_fdid as pc_rid,MAX(pc_dt) AS dt,MAX(pc_body)AS body,post.p_uid,r.u_nm,1 as type FROM `post_call` LEFT JOIN `post` ON post_call.pc_pid=post.p_id RIGHT JOIN `usr_s1` ON usr_s1.u_id=post.p_uid INNER JOIN `usr_s1` AS r ON post_call.pc_fxid=r.u_id WHERE post.p_uid=41 AND pc_pid=3 AND pc_fxid NOT IN (SELECT pc_fxid FROM post_call WHERE pc_pid=3 AND pc_fxid=41) GROUP BY pc_fxid) order by dt desc
            $stmt = $this->dbConnect->prepare("(SELECT Max(m_id)  AS mid, 
            r.u_img    AS u_img, 
            r.u_gender AS u_gender, 
            post.p_tit, 
            m_pid, 
            m_sid, 
            Max(m_rid) AS m_rid, 
            Max(m_dt)  AS dt, 
            Max(m_body)AS body, 
            post.p_uid, 
            r.u_nm, 
            0          AS type 
     FROM   `messages` 
            LEFT JOIN `post` 
                   ON messages.m_pid = post.p_id 
            RIGHT JOIN `usr_s1` 
                    ON usr_s1.u_id = post.p_uid 
            INNER JOIN `usr_s1` AS r 
                    ON messages.m_sid = r.u_id 
     WHERE  post.p_uid = :uid 
            AND m_pid = :pid
            AND m_sid NOT IN (SELECT m_sid 
                              FROM   messages 
                              WHERE  m_pid = :pid 
                                     AND m_sid = :uid) 
     GROUP  BY m_sid) 
    UNION ALL 
    (SELECT Max(post_call.pc_id) AS mid, 
            p.u_img                AS u_img, 
            p.u_gender             AS u_gender, 
            post.p_tit, 
            post_call.pc_pid     AS m_pid, 
            post_call.pc_fxid    AS m_sid, 
            post_call.pc_fdid    AS m_rid, 
            post_call.pc_dt      AS dt, 
            post_call.pc_body    AS body, 
            post.p_uid, 
            p.u_nm, 
            1                    AS type 
     FROM   `post_call` 
            LEFT JOIN `usr_s1` 
                    ON post_call.pc_fdid = usr_s1.u_id 
            RIGHT JOIN `post` 
                    ON post_call.pc_pid = post.p_id 
             INNER JOIN `usr_s1` AS p 
                            ON post_call.pc_fxid = p.u_id 
     WHERE  post_call.pc_fdid = :uid 
            AND post_call.pc_pid = :pid 
     GROUP  BY m_sid) 
    ORDER  BY dt DESC");



            
            $stmt->bindParam(':pid',$this->pid);
            $stmt->bindParam(':uid',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // Get Last Message
        public function getChatLastActivity(){

            if($this->pid == 0){

             $stmt = $this->dbConnect->prepare("SELECT *,0 as type FROM `messages` where  (m_sid=:sid and m_rid=:rid ) OR (m_sid=:rid AND m_rid=:sid ) order by m_dt desc limit 1");

            }else{

            // $stmt = $this->dbConnect->prepare("select * from ((SELECT *,0 as type FROM `messages` where m_pid=:pid and m_sid=:sid and m_rid=:rid order by m_dt desc limit 1) UNION ALL (SELECT *,1 as type FROM `post_call` where pc_pid=:pid and pc_fxid=:sid and pc_fdid=:rid order by pc_dt desc limit 1)) as r order by m_dt desc limit 1");
            $stmt = $this->dbConnect->prepare("SELECT *,0 as type FROM `messages` where  (m_sid=:sid and m_rid=:rid AND m_pid=:pid) OR (m_sid=:rid AND m_rid=:sid AND m_pid=:pid) order by m_dt desc limit 1");
        }
            $stmt->bindParam(':pid',$this->pid);
            $stmt->bindParam(':sid',$this->sid);
            $stmt->bindParam(':rid',$this->rid);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data;
        }

        //  Get Categories List
		public function getCategories() {

			$stmt = $this->dbConnect->prepare("SELECT * FROM `category` ORDER BY `category`.`cat_id` ASC");
			$stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        //  Get Categories With Fixers Cout Wise List
        public function getfixerCategoriesWithCount() {

			$stmt = $this->dbConnect->prepare("SELECT cat_id,cat_name,(SELECT count(*) from usr_s1 left join usr_s2 on usr_s2.us_uid = usr_s1.u_id WHERE u_pfn = cat_id and u_mob_verify = 1 and us_typ = 1) as fixers_count FROM category left JOIN `usr_s1` ON category.cat_id = usr_s1.u_pfn WHERE u_id IN (select usr_s2.us_uid from usr_s2 where us_typ='1') and cat_astat = 1 GROUP by cat_id ORDER BY fixers_count DESC");
			$stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        //  Get Fixers List Based on Category ID
        public function getfixersByCategoryId(){
            $limit  =   ($this->startPost!="" && $this->startPost>=0 && $this->limitPost!="" && $this->limitPost>0)?" LIMIT ".$this->startPost.", ".$this->limitPost:'';
            // this one is final	// SELECT category.cat_name,usr_s1.u_img,COUNT(post_select.ps_stat) AS works_done,AVG(pfd_stat.pfd_rate) AS rating,usr_s1.u_nm FROM `category` INNER JOIN `usr_s1` ON category.cat_id=usr_s1.u_pfn INNER JOIN `usr_s2` ON usr_s1.u_id=usr_s2.us_uid INNER JOIN `post_select` ON post_select.ps_fxid=usr_s1.u_id INNER JOIN `pfd_stat` ON pfd_stat.pfd_fxid=usr_s1.u_id WHERE usr_s2.us_typ='1' AND usr_s2.us_fxstat='1' AND post_select.ps_stat='1' AND category.cat_id = ':cat_id'
            $sql= "SELECT usr_s1.*, category.cat_name as cat_name, location.loc_name as loc_name, usr_s2.us_typ as us_typ FROM `usr_s1` INNER JOIN `usr_s2` ON usr_s1.u_id=usr_s2.us_uid INNER JOIN `category` ON usr_s1.u_pfn=category.cat_id INNER JOIN `location` ON usr_s1.u_city=location.loc_id WHERE usr_s1.u_pfn=:cat_id and usr_s2.us_typ=1";
            //"SELECT usr_s1.u_nm,category.cat_name,usr_s1.u_id FROM `usr_s1` INNER JOIN `usr_s2` ON usr_s1.u_id=usr_s2.us_uid INNER JOIN `category` ON usr_s1.u_pfn=category.cat_id WHERE category.cat_id=:cat_id"
            $stmt = $this->dbConnect->prepare($sql);
            // SELECT category.cat_name,usr_s1.u_img,usr_s1.u_nm FROM `category` INNER JOIN `usr_s1` ON category.cat_id=usr_s1.u_pfn INNER JOIN `usr_s2` ON usr_s1.u_id=usr_s2.us_uid  WHERE usr_s2.us_typ='1' AND usr_s2.us_fxstat='1' AND category.cat_id = '3'
            $stmt->bindParam(':cat_id', $this->cat_id);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;


            // Fixers With Rating
            // $sql= "SELECT *, TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from AVG(pfd_rate))) AS rating FROM `usr_s1` AS usr INNER JOIN `usr_s2` ON usr.u_id=usr_s2.us_uid left JOIN `pfd_stat` ON usr.u_id=pfd_stat.pfd_fxid INNER JOIN `category` ON usr.u_pfn=category.cat_id WHERE category.cat_id=:cat_id GROUP by pfd_stat.pfd_fxid";
            // $stmt = $this->dbConnect->prepare($sql);
            // $stmt->bindParam(':cat_id', $this->cat_id);
            // $stmt->execute();
            // $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // return $data;
        }

        //  Get Category Name Based On Keyword
        public function getCategoriesByKeyWord(){

            $stmt = $this->dbConnect->prepare("SELECT `cat_id`,`cat_name` FROM category WHERE `cat_name` LIKE :key_word");
			$stmt->bindParam(':key_word', $this->key_word);
			$stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        //  Get Location Name Based On Keyword
        public function getLocationsByKeyWord(){

            $stmt = $this->dbConnect->prepare("SELECT `loc_id`,`loc_name` FROM location WHERE `loc_name` LIKE :key_word");
			$stmt->bindParam(':key_word', $this->key_word);
			$stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        //  Get Feed By Keyword
        public function getPostsByKeyWord(){

            $stmt = $this->dbConnect->prepare("SELECT * FROM post WHERE `p_tit` LIKE :key_word OR  `p_jd` LIKE :key_word");
			$stmt->bindParam(':key_word', $this->key_word);
			$stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }


        //  Get Feed By Category ID
        public function getPostsByCategoryId(){

            $stmt = $this->dbConnect->prepare("SELECT * FROM post WHERE `p_catid` LIKE :cat_id");
			$stmt->bindParam(':cat_id', $this->cat_id);
			$stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // Check Is This Fixer Valid User Or Not
		public function checkIsValidFixer(){

			$sql = 'SELECT us_typ,us_fxstat FROM `usr_s2` INNER JOIN `usr_s1` ON usr_s1.u_id=usr_s2.us_uid WHERE usr_s1.u_id=:u_id';
			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(':u_id', $this->uid);
			$stmt->execute();

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}

        // Get User Profile Data
		public function getUserProfileInfo(){

            $stmt = $this->dbConnect->prepare("SELECT u_id,u_nm,u_gender,u_img,u_pfn,u_desc,us_typ,cat_name,u_email FROM `usr_s1` INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id INNER JOIN category on category.cat_id = usr_s1.u_pfn where u_id=:u_id");
            $stmt->bindParam(':u_id', $this->uid);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['profile'] = $result;
            $this->setUtype($result['us_typ']);
            $data['user_jobs_works_rating'] = $this->getJobsCountAndWorksCountAndRating();
            return $data;

        }

        // Get User Name and Mobile no
		public function getUserNm(){

            $stmt = $this->dbConnect->prepare("SELECT u_nm,u_phn FROM `usr_s1`  where u_id=:u_id");
            $stmt->bindParam(':u_id', $this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;

        }

        public function getBookingDetails(){
            $stmt = $this->dbConnect->prepare("SELECT bs_uuid as bookingId,bs_dt as date,bs_stat as status,'Service' as type, category.cat_name as Service FROM `Book_service` INNER JOIN `category` ON Book_service.bs_cid = category.cat_id WHERE bs_uid=:u_id union select ba_uuid as bookingId,ba_dt as date,ba_stat as status,'Delivery' as type,adds.a_note as Service  from `Book_Adservice` INNER JOIN adds ON Book_Adservice.ba_aid=adds.a_id  WHERE ba_uid=:u_id ORDER by date DESC");
			$stmt->bindParam(':u_id', $this->uid);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
        }

        //getMyFixersAcceptedPosts
        public function getMyFixersAcceptedPosts(){
			$stmt = $this->dbConnect->prepare("SELECT post.p_id,ps_id,p_tit,usr_s1.u_id as f_id,usr_s1.u_nm as f_nm,usr_s1.u_img,usr_s1.u_gender as u_gender,usr_s2.us_typ FROM `post_select` INNER JOIN `post` ON post_select.ps_pid = post.p_id INNER JOIN `usr_s1` ON usr_s1.u_id = post_select.ps_fxid INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id where post_select.ps_stat='0' and post_select.ps_fdid=:u_id AND post.p_id NOT IN (SELECT pfd_pid FROM `pfd_stat` WHERE pfd_fdid =:u_id) AND post.p_id IN (SELECT pfx_pid FROM `pfx_stat` WHERE pfx_fdid =:u_id) ORDER BY post_select.ps_dt DESC");
			$stmt->bindParam(':u_id', $this->uid);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
        }

        // public function getUserRating(){

        //     if($this->utype == 1){

        //         $stmt = $this->dbConnect->prepare("SELECT pfd_fxid,TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from AVG(pfd_rate))) AS rating FROM `pfd_stat` WHERE pfd_fxid=:uid GROUP BY pfd_fxid");
        //         $stmt->bindParam(':uid', $this->uid);
        //         $stmt->execute();
        //         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //         return $data;
        //     }

        //     if($this->utype == 0){

        //         $stmt = $this->dbConnect->prepare("SELECT pfd_fdid,,TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from AVG(pfx_rate))) AS rating FROM `pfx_stat` WHERE pfx_fdid=:uid GROUP BY pfx_fdid");
        //         $stmt->bindParam(':uid', $this->uid);
        //         $stmt->execute();
        //         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //         return $data;
        //     }
        // }

        // public function getFixerJobsCount(){

        //     $stmt = $this->dbConnect->prepare("SELECT COUNT(ps_stat) AS jobs_done from post_select where post_select.ps_stat='1' and ps_fxid=:fxid");
        //     $stmt->bindParam(':fxid', $this->fxid);
        //     $stmt->execute();
        //     $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //     return $data[0]['jobs_done'];
        // }

        public function getJobsCountAndWorksCountAndRating(){

            $stmt = $this->dbConnect->prepare("SELECT COUNT(p_id) AS jobs_posted from post where post.p_uid=:uid");
            $stmt->bindParam(':uid', $this->uid);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['jobs'] = $result[0]['jobs_posted'];

            $stmt = $this->dbConnect->prepare("SELECT COUNT(ps_stat) AS works_done from post_select where post_select.ps_stat='1' and (ps_fxid=:uid or ps_fdid=:uid)");
            $stmt->bindParam(':uid', $this->uid);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['works'] = $result[0]['works_done'];

            if($this->utype == 1){

                $stmt = $this->dbConnect->prepare("SELECT TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from AVG(pfd_rate))) AS rating FROM `pfd_stat` WHERE pfd_fxid=:uid GROUP BY pfd_fxid");
                $stmt->bindParam(':uid', $this->uid);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($result == null){
                    $data['rating'] = "0";
                }else{
                    $data['rating'] = $result[0]['rating'];
                }

            }

            if($this->utype == 0){

                $stmt = $this->dbConnect->prepare("SELECT TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from AVG(pfx_rate))) AS rating FROM `pfx_stat` WHERE pfx_fdid=:uid GROUP BY pfx_fdid");
                $stmt->bindParam(':uid', $this->uid);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($result == null){
                    $data['rating'] = "0";
                }else{
                    $data['rating'] = $result[0]['rating'];
                }

            }

            return $data;
        }

        public function updatePostStatus() {

			$sql = 'UPDATE post SET `p_astat` = 1 WHERE `p_id` = :pid';

			$stmt = $this->dbConnect->prepare($sql);

			$stmt->bindParam(':pid', $this->pid);

			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
        }


			public function updatefindertofixer() {

			//echo $this->us_exp.'&'.$this->us_proof.'&'.$this->us_prfid.'&'.$this->us_uid; exit;
			$us_typ = 1;
			$sql = 'UPDATE `usr_s2` SET `us_exp` = :us_exp,`us_proof` = :us_proof,`us_prfid` = :us_prfid,`us_typ` = :us_typ WHERE `us_uid` = :us_uid';

			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(':us_typ', $us_typ);
			$stmt->bindParam(':us_exp', $this->us_exp);
			$stmt->bindParam(':us_proof', $this->us_proof);
			$stmt->bindParam(':us_prfid', $this->us_prfid);
			$stmt->bindParam(':us_uid', $this->us_uid);

				if($stmt->execute()) {
					return true;
				} else {
					return false;
				}
      }

        public function updateWorkStatus() {

            $sql = 'UPDATE post_select SET `ps_stat` = "0" WHERE `ps_fdid` = :fdid AND `ps_fxid` = :fxid';
           // echo $sql; exit;

            $stmt = $this->dbConnect->prepare($sql);

            $stmt->bindParam(':fdid', $this->fdid);
            $stmt->bindParam(':fxid', $this->fxid);

            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function updateLangStatus() {

            $sql = 'UPDATE `usr_s2` SET `us_lang` = :lid WHERE `us_uid` = :uid';
           // echo $sql; exit;

            $stmt = $this->dbConnect->prepare($sql);

            $stmt->bindParam(':uid', $this->uid);
            $stmt->bindParam(':lid', $this->lid);

            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function updatePassword() {

            $sql = 'UPDATE `usr_s1` SET `u_pwd` = :cnfnewpwd WHERE `u_id` = :uid';
           
            $pwd = password_hash($this->cnfnewpwd,PASSWORD_BCRYPT);
            $stmt = $this->dbConnect->prepare($sql);

            $stmt->bindParam(':uid', $this->uid);
            $stmt->bindParam(':cnfnewpwd', $pwd);
            // echo $sql;
            // echo $this->cnfnewpwd; 
            // echo $pwd;
            // exit;
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }


        public function updatefixerstat() {

            $sql = 'UPDATE post_select SET `ps_stat` = :pid WHERE `ps_fxid` = :fxuid AND `ps_pid` = :fxpid';

            $stmt = $this->dbConnect->prepare($sql);

            $stmt->bindParam(':pid', $this->pid);
            $stmt->bindParam(':fxuid', $this->fxuid);
            $stmt->bindParam(':fxpid', $this->fxpid);

            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }



        // Get My Posts And Requested Posts
        public function getMyPostsAndRequestedPosts() {

            $stmt = $this->dbConnect->prepare("select *,(SELECT ps_stat FROM `post_select` where ps_pid=tmp.p_id order by ps_id desc limit 1) as post_stat from ((SELECT post.p_id,post.p_dt,p_tit,post.p_jd,p_amt,usr_s1.u_id as puid,usr_s1.u_nm as nm,usr_s1.u_img,usr_s2.us_typ,0 as p_typ FROM `post` INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id where usr_s1.u_id=:uid) UNION ALL (SELECT DISTINCT(m_pid) as p_id,p_dt,p_tit,post.p_jd,p_amt,usr_s1.u_id AS puid,usr_s1.u_nm AS nm,u_img,usr_s2.us_typ,1 as p_typ from messages join post on messages.m_pid = post.p_id INNER JOIN `usr_s1` ON post.p_uid=usr_s1.u_id INNER JOIN `usr_s2` ON usr_s2.us_uid=usr_s1.u_id where (m_sid = :uid) OR (m_rid = :uid))) as tmp");
            $stmt->bindParam(':uid',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
        }

        // My Post Responded Users Count
        public function getMyPostRespondersAndCount(){

            $stmt = $this->dbConnect->prepare("select GROUP_CONCAT(users) as users FROM (SELECT GROUP_CONCAT(DISTINCT(m_sid)) as users,count(DISTINCT(m_sid)) as count FROM `messages` where m_pid = :pid and m_sid != :uid UNION ALL SELECT GROUP_CONCAT(DISTINCT(pc_fxid)) as users,count(DISTINCT(pc_fxid)) as count FROM `post_call` where pc_pid=:pid and pc_fxid != :uid) as tmp");
            $stmt->bindParam(':uid',$this->uid);
            $stmt->bindParam(':pid',$this->pid);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($result == null){
                $data['responders'] = "[]";
                $data['count'] = "0";
            }else{
                $tmp = $result[0]['users'];
                $array = explode(',', $tmp);
                $array = array_unique($array);
                $data['responders'] = $array;
                $data['count'] = count($array);
            }

            return $data;
        }

        //My Image Data
        public function getMyImage(){
            $sql = 'SELECT u_img,u_gender FROM `usr_s1` WHERE usr_s1.u_id=:u_id';
			$stmt = $this->dbConnect->prepare($sql);
			$stmt->bindParam(':u_id', $this->uid);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
        }


        // Get Post Select or Unselect Status
        public function getPostSelectUnselectStatus(){

            $stmt = $this->dbConnect->prepare("SELECT ps_fxid,ps_stat FROM `post_select` where ps_pid=:pid order by ps_id desc limit 1");
            $stmt->bindParam(':pid',$this->pid);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result == null){
                $data['status'] = 0;
                $data['id'] = 0;
            }else{
                $data['status'] = $result['ps_stat'];
                $data['id'] = $result['ps_fxid'];
            }

            return $data;
        }

        //Prepare (or) Generate Scratch Card Amount
        public function generateScratchCard($uid,$pid){

             // Check Post Elegible to get Scratch Card or Not
            $sql = 'SELECT * FROM `scratchcard_allotment` where sa_min_pid=:pid or sa_max_pid=:pid or :pid BETWEEN sa_min_pid and sa_max_pid';
						$stmt = $this->dbConnect->prepare($sql);
						$stmt->bindParam(':pid', $pid);
						$stmt->execute();
            $allotment_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $said = $allotment_data['sa_id'];
            $sa_users = $allotment_data['sa_ev'];

            if($allotment_data == false){

                $data['sc_uid'] = $uid;
                $data['sc_utype'] = $utype;
                $data['sc_pid'] = $pid;
                $data['sc_won'] = 0;
                $data['sa_id'] = $said;

                return $data;

            }else{

                // Get Scatch Card Amounts and Ratio Here
                $sql = 'SELECT amount,ratio FROM `scratchcard_amounts` where sa_id=:said';
                $stmt = $this->dbConnect->prepare($sql);
                $stmt->bindParam(':said', $said);
                $stmt->execute();
                $amounts_and_ratios_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get Random Data Function
                function randomCall($amounts_and_ratios_data,$sa_users,$uid,$pid,$said)
                {
                    $random_key = array_rand($amounts_and_ratios_data,1);
                    $random_data = $amounts_and_ratios_data[$random_key];

                    $random_amount = $random_data['amount'];
                    $max_users = ($random_data['ratio']/100)*(int)$sa_users;

                    // Check this amount can issue or not
                    $sql = 'SELECT count(*) as amount_used_users_count FROM `scratch_card` where sc_pid=:sc_pid and sc_won=:sc_won';
                    $db = new Database;
                    $stmt = $db->connect()->prepare($sql);
                    $stmt->bindParam(':sc_pid', $pid);
                    $stmt->bindParam(':sc_won', $random_amount);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if($result[0]['amount_used_users_count'] <= $max_users){

                        $data['sc_uid'] = $uid;
                        $data['sc_utype'] = $utype;
                        $data['sc_pid'] = $pid;
                        $data['sc_won'] = $random_amount;
                        $data['sa_id'] = $said;

                        return $data;

                    }else{

                        randomCall($amounts_and_ratios_data,$sa_users,$uid,$pid,$said);
                    }
                }

                return $data = randomCall($amounts_and_ratios_data,$sa_users,$uid,$pid,$said);

            }

            return $data;

        }

        public function getUserScratchCards(){

            $stmt = $this->dbConnect->prepare("select * from scratch_card where sc_uid=:uid ");
            $stmt->bindParam(':uid',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						return $data;

        }
				public function getUserScratchCards_noti(){

            $stmt = $this->dbConnect->prepare("select * from scratch_card where sc_uid=:uid AND `show_status`='0'");
            $stmt->bindParam(':uid',$this->uid);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						return $data;

        }



        public function updateScratchCardViewStatus(){

            $stmt = $this->dbConnect->prepare("UPDATE `scratch_card` SET `show_status`='1' WHERE `sc_id`=:scid");
            $stmt->bindParam(':scid',$this->scid);
            // $stmt->execute();
            // $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						// return $data;
						if($stmt->execute()){
							return true;
						} else {
							return false;
						}

        }

        public function getPostAddsCount()
        {
            $cdt =  date('Y-m-d');
            $stmt = $this->dbConnect->prepare("select count(*) as adCount from adds where a_type='1' AND a_ex_dt!=:cdt");
            $stmt->bindParam(':cdt',$cdt);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        public function getAdds()
        {
            $cdt =  date('Y-m-d');
            $stmt = $this->dbConnect->prepare("select * from adds where a_ex_dt!=:cdt");
            $stmt->bindParam(':cdt',$cdt);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
        }

        // My Post Responded Users Count
        public function getTotalMoney(){

            $stmt = $this->dbConnect->prepare("SELECT sum(sc_won) as total from scratch_card where sc_uid=:uid");
            $stmt->bindParam(':uid',$this->uid);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($result == null){
                $data['count'] = "0";
            }else{
                $data['count'] = $result[0]['total'];
            }

            return $data;
        }

        public function genamesofcat(){

            $stmt = $this->dbConnect->prepare("select * from category");
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        public function genamesofloc(){

            $stmt = $this->dbConnect->prepare("select * from location");
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }


        


    }
?>
