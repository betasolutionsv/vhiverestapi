<?php 

	/*Security*/
	define('SECRETE_KEY', 'Beta!Solutions123456789@');
	
	/*Data Type*/
	define('BOOLEAN', 	'1');
	define('INTEGER', 	'2');
	define('STRING', 	'3');
	define('ARRAYTYPE', '4');

	/*Error Codes*/
	define('REQUEST_METHOD_NOT_VALID',		        100);
	define('REQUEST_CONTENTTYPE_NOT_VALID',	        101);
	define('REQUEST_NOT_VALID', 			        102);
    define('VALIDATE_PARAMETER_REQUIRED', 			103);
	define('VALIDATE_PARAMETER_DATATYPE', 			104);
	define('API_SERVICE_NAME_REQUIRED', 			105);
	define('API_PARAM_REQUIRED', 					106);
	define('API_DOST_NOT_EXIST', 					107);
	define('INVALID_USER_PASS', 					108);
	define('USER_NOT_ACTIVE', 						109);
	define('SUCCESS_RESPONSE', 						200);
	define('ACCESS_TOKEN_DELETED', 					201);
	define('FAILURE_RESPONSE', 						202);
	define('MOBILE_NOT_VERIFIED',					203);
	define('USER_TYPE_NOT_ASSIGNED',				204);

	/*Server Errors*/
	define('JWT_PROCESSING_ERROR',					300);
	define('ATHORIZATION_HEADER_NOT_FOUND',			301);
	define('ACCESS_TOKEN_ERRORS',					302);	

	date_default_timezone_set('Asia/Kolkata');
	define('INSERT_DATE_TIME', 						date('Y-m-d H:i:s'));

	define('OTP_EXPIRY_LIMIT_IN_HRS',				24);
	define('OTP_LENGTH',							6);
	define('OTP_MIN_RAND',							100000);
	define('OTP_MAX_RAND',							999999);
?>