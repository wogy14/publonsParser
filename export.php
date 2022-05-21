<?php

require 'vendor/autoload.php';

use components\Export;

if (!empty($argv[1])) {
	switch ($argv[1]) {
		case 'json':
			Export::json();
			break;
		case 'csv':
			Export::csv();
			break;
		default:
			echo 'Please select file format as second parameter(json, csv).';
	}
}
