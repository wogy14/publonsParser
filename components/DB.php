<?php

namespace components;

use PDO;
use PDOException;
use config\Config;

class DB {
	private static PDO|null $instance = null;

	public static function getConnection() {
		if (null === self::$instance) {
			try {
				self::$instance = new PDO("mysql:host=" . Config::$dbConfig['host'] . ";dbname=" . Config::$dbConfig['name'], Config::$dbConfig['user'], Config::$dbConfig['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			} catch (PDOException $e) {
				print "Error!: " . $e->getMessage();
				die();
			}
		}

		return self::$instance;
	}

	public static function insertOrUpdate($tableName, $params, $uniqueFields) {
		$findSqlParams = implode(' AND ', array_map(function ($key) { return "`$key` = :$key"; }, $uniqueFields));
		$stmt = self::getConnection()->prepare("SELECT `id` FROM `$tableName` WHERE $findSqlParams ;");
		$stmt->execute(array_intersect_key($params, array_flip($uniqueFields)));

		$id = $stmt->fetchColumn();

		if (empty($id)) {
			$fields = implode(',', array_map(function ($str) { return "`$str`"; }, array_keys($params)));
			$valuesKeys = implode(',', array_map(function ($str) { return ":$str"; }, array_keys($params)));
			$stmt = self::getConnection()->prepare("INSERT INTO `$tableName` ($fields) VALUES ($valuesKeys) ;");
			$stmt->execute($params);
			$id = self::getConnection()->lastInsertId();
		} else {
			unset($params['created_at']);
			$updateSqlParams = implode(',', array_map(function ($key) { return "`$key` = :$key"; }, array_keys($params)));
			$stmt = self::getConnection()->prepare("UPDATE `$tableName` SET $updateSqlParams WHERE $findSqlParams ;");
			$stmt->execute($params);
		}

		return $id;
	}
}
