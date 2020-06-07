<?php

	class MySQLDatabase
	{
		public static function connect($database,$username,$password)
		{
			$db_driver="mysql"; $host = "127.0.0.1";
			$dsn = "$db_driver:host=$host; dbname=$database";
			$options = array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
			  
			try 
			{
				$dbh = new PDO ($dsn, $username, $password, $options);
				return $dbh;
			}
			catch (PDOException $e) 
			{
				echo "Error!: " . $e->getMessage() . "<br/>"; 
				return null;
			}
		}
	}
?>
