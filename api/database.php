<?php
	/**
	* Mysql Database Connection
	*/
	class Database {

		private $server = 'localhost';
		private $dbname = 'vhive_v1';
		private $user = 'kare';
		private $pass = 'Kare@123';

		public function connect() {
			try {
				$conn = new PDO('mysql:host=' .$this->server .';dbname=' . $this->dbname .';strict=false', $this->user, $this->pass);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return $conn;
			} catch (\Exception $e) {
				echo "Mysql Database Error: " . $e->getMessage();
			}
		}
	}
 ?>
