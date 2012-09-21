<?php

$install = function(){
	UI::out("    YES!  Im being installed!");
};

$remove = function(){
	UI::out("    Uh oh, you're removing me?");
};

$purge = function(){
	UI::out("    OKAY FINE ILL PURGE IT ALL!");
};

//upgrade from 0.0.1 -> 0.0.2
$upgrade_2 = function(){
	UI::out("\n\n   Executing Hook\n    Hi! Im upgrading to version 2!\n\n");
};

$upgrade_3 = function(){
	UI::out("\n\n   Executing Hook\n    Hi! Im upgrading to version 3!\n\n");
};

$upgrade_4 = function(){
	UI::out("\n\n   Executing Hook\n    Hi! Im upgrading to version 4!\n\n");
};
