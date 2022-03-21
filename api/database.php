<?php
	/**
	* Mysql Database Connection
	*/
	class Database {

		private $server = 'localhost';
		private $dbname = 'goffix';
		private $user = 'root';
		private $pass = 'G_@off!$ix2@19';

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
