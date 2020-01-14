<?php
/**
 * mysqli.class.php
 *
 * php mysql harness for database access
 * @author Jeff Hendrickson <jeff@hendricom.com>
 * @version 1.0
 * @package Crawler
 * sample use:
	$db = new DatabaseClass('yourserver.com', 'user', 'pass', 'database');
	// insert into database
	$sql = "INSERT INTO uri_list (url, status) VALUES ('$match', '1')";
	$db->query($sql);
	// get the id as added...
	$lastRow = $db->insert_id();
	if ($lastRow == 0) {
		printf("error, sql = $sql\n");
	}
	
	// get the byte count from the database 
	$sql = "SELECT COUNT(id) AS numberOfFiles, SUM(filesize) AS totalFileSize, MAX(filedate) AS lastUpdated FROM pdf_list";
	// printf("sql = $sql\n");
	$current = $db->query($sql);
	if ($current && $db->num_rows($current) > 0) {
		$row = $db->fetch_assoc($current);
		$localFound = $row['numberOfFiles'];
		if($localFound > 0) {
			$localSize = $row['totalFileSize'];
			$lastUpdated = $row['lastUpdated'];
		}
	} 
	// 2019-03-19 12:48:09
	printf("found %d files in database %s, total byte count %d, newest file %s\n", $localFound, $db->getDatabaseName(), $localSize, $lastUpdated);	
 */
require_once ('tracetofile.php');
class DatabaseClass {

   protected $dbUser = '';
   protected $dbPass = '';
   protected $dbHost = '';
   protected $dbName = '';
   protected $queryResult = NULL;

   private $lastError = NULL;
   private $lastQuery = NULL;
   private $dbo = NULL;

   function __construct($dbHost, $dbUser, $dbPass, $dbName) {
      $this->dbHost = $dbHost;
      $this->dbUser = $dbUser;
      $this->dbPass = $dbPass;
      $this->dbName = $dbName;

      $this->connect();
   }

   private function connect() {
      // tracetofile(__FILE__,__LINE__,"connecting $this->dbHost, $this->dbUser, $this->dbPass");
      $this->conn = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPass) or die("Unable to connect to host " . $this->dbHost . ".\n\n");

      $this->setCharCollation();

      $select_db = mysqli_select_db($this->conn, $this->dbName) or die("Unable to select database " . $this->dbName . ".\n\n");
   }

   function setCharCollation($names = 'utf8', $char = 'utf8', $collation = 'utf8_general_ci') {
      @mysqli_query("SET NAMES 'utf8'");
      @mysqli_query("SET CHARACTER SET 'utf8'"); //
      @mysqli_query("SET COLLATION_CONNECTION = 'utf8_general_ci'");
   }

   function query($sql) { //
      $tur = strtolower(substr($sql, 0, 3));
      switch ($tur) {
         case "sel":
            return mysqli_query($this->conn, $sql);
         break;

         case "ins":
            return mysqli_real_query($this->conn, $sql);
         break;

         case "upd":
            return mysqli_real_query($this->conn, $sql);
         break;

         case "del":
            return mysqli_real_query($this->conn, $sql);
         break;

         default:
            return mysqli_query($this->conn, $sql);
         break;
      }
      unset($tur);
   }

   function fetchArray($sql) {
      $resultsx = array();
      $sqlQuery = $this->query($sql);

      if (!$sqlQuery) return false;

      while ($rows = $this->fetch_array($sqlQuery)) {
         $resultsx[] = $rows;
      }

      if (!is_array($resultsx)) return false;
      else return $resultsx;

      $this->close();
   }

   function fetch_array($result, $type = mysqli_BOTH) {
      return mysqli_fetch_array($result);
   }

   function fetch_object($result) {
      return mysqli_fetch_object($result);
   }

   function fetch_assoc($result) {
      return mysqli_fetch_assoc($result);
   }

   function num_rows($result) {
      return mysqli_num_rows($result);
   }

   function affected_rows() {
      return mysqli_affected_rows();
   }

   function free_result($result) {
      @mysqli_free_result($result);
      unset($result);
   }

   function insert_id() {
      return mysqli_insert_id($this->conn);
   }

   function result($result, $index = 0) {
      return mysqli_result($result, $index);
   }

   function close() {
      return mysqli_close($this->conn);
   }

   function clean($var) {
      return mysqli_real_escape_string($var);
   }

   function nextRow($tableName) {
      $sql = "SELECT MAX(row) AS maxRow FROM $tableName";
      $value = $this->query($sql);
      if ($fetchResult = $this->fetch_assoc($value)) {
         $newRow = $fetchResult["maxRow"];
         if ($newRow % 10 == 0) return $newRow + 10;
         else {
            $modRow = 10 - ($newRow % 10);
            return $newRow + 10 + $modRow;
         }
      }
      else return 0;
   }

   function getDatabaseName() {
      return $this->dbName;
   }
}

?>
