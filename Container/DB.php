<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Взаимодействие с базой данных */
class DBClass{
	/* Соединение с БД */
	function __construct($server, $user, $pass, $dbname){
		$dsn = "mysql:host=".$server.";dbname=".$dbname.";charset=utf8";
		$opt = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
				PDO::ATTR_EMULATE_PREPARES => false);
				
		$this->pdo = new PDO($dsn, $user, $pass, $opt);
	}
	
	/* Опрерация получения данныз из БД */
	public function select($what, $from, $where = null, $order = null){
		$sql = 'SELECT '.$what.' FROM '.$from;
		if($where != null) $sql .= ' WHERE '.$where;
		if($order != null) $sql .= ' ORDER BY '.$order;
		try{
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			$result = false;
		}
		
		return $result;
	}
	
	/* Опрерация внесения новых данных в БД */
	public function insert($table, $rows = null, $values, $type = null){
		$sql = 'INSERT INTO '.$table;
		if($rows != null) $sql .= ' ('.$rows.')';
		$sql .= ' VALUES ('.$values.')';
		
		try{
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute();
			$result = $this->pdo->lastInsertId();
		}catch(PDOException $e){
			$result = false;
		}
		
		return $result;
	}
	
	/* Опрерация обновления данных в БД */
	public function update($what,$set,$where = null, $type = null){
		$sql = 'UPDATE '.$what.' SET '.$set;
		if($where != null) $sql .= ' WHERE '.$where;
		
		try{
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute();
		}catch(PDOException $e){
    		$result = false;  
		}
		
		return $result;
	}
}
?>