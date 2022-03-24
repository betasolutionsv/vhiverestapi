21032022
# Check API Code
# checked some api code
# api.php scratch card 14,22 line

# Design Prototype

# Dump some dummy data into database
# dumped some dummy data database

# Create API and test it
# Checking live goffix API

# services:
# login api
# otp authentication
# phonenumber verification
# verify password
# getphone number
# 








# api



public function getVisitorByHostID($hostID){

			$hostID = $this->validateParameter('hostID', $this->param['hostID'], STRING, true);
			$vid = $this->validateParameter('vid', $this->param['vid'], STRING, true);
			$rid = $this->validateParameter('rid', $this->param['rid'], STRING, true);

			$msg = new Message;
			$msg->sethostID($hostID);
			$msg->setvid($vid);
			$msg->setrid($rid);
			$hostID=$msg->getMessage();

			if(is_array($hostID)){
				$response['hostID'] = $hostID;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			}else{
				$this->returnResponse(FAILURE_RESPONSE, "No data found");
			}
		}
















