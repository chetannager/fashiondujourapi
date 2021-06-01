<?php
class db{
	private $HOST = "localhost";
	private $USER = "root";
	private $PASS = "";
	private $DB = "fashiondujour";

	public function Connect(){
		$conn = new PDO("mysql:host=localhost;dbname=fashiondujour", "root", "");
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	}
}
?>