#!/usr/bin/php
<?php

require_once('src/boot.php');

$lo = array(
	'live',		//start the live system
);
$opts = getopt('',$lo);

var_dump($opts);
exit;
