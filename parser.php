<?php

require 'vendor/autoload.php';

use components\Parser;

if (!empty($argv[1])) {
	Parser::parse($argv[1], !empty($argv[2]) ? (int) $argv[2] : 1);
} else {
	echo 'You should pass second parameter with Institution name';
}



