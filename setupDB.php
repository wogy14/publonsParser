<?php

require 'vendor/autoload.php';

use components\DB;

$dbConnection = DB::getConnection();

$sql = file_get_contents('sql/setupDB.sql');

try {
	$dbConnection->exec($sql);
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}

echo 'All tables was imported';
