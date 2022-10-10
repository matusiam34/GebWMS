<?php

/**
 * Configuration file for: Database Connection 
 * This is the place where your database login constants are saved
 * 
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see http://stackoverflow.com/q/2447791/1114320
 */





define("min_priv", 0);		// for compatibility so far...

// Brand new stuff for ACL, 3 Oct 2022

define("super_priv", 1);	// Supervisor
define("manager_priv", 2);	// Manager
define("admin_priv", 3);	// Administrator




/*
	sqlite_or_mariadb is a simple variable that changes from one database type to another.

	0	-	sqlite
	1	-	mariadb

*/

define("sqlite_or_mariadb", 0);	// set to sqlite for small installations !!

/*
	db_debug is a simple setting.
	Either each DB connection will execute (1) :

	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	or NOT (0). Simple as that.

*/

define("db_debug", 1);	// enable debug ! Useful as it throws the error messages at the user which screenshot and send it to me

/*	
	Extra folder is needed to adjust the depth of the folder. For exaple :
	Apache can hold the website in /var/www or /var/www/html.
	Set the extra_db_folder to "../" when apache stores files in html folder !
*/

$extra_db_folder = "../";

/*

	Application folder name is typically something like
		ALWAYS /my to make it easy to manage serveral servers !
	They are stored in the root of the filesystem of the server hosting the particular
	application. 
 
*/

$application_folder		=	"my";


/*
	db_name is a variable used in the entire application ! It is critical to get this right
	as it does save the time to mess around with the path settings to the database !

	When you move the database to a different place make sure to alter this path as well !! 
*/


//define("db_name", "/" . $application_folder . "/wdrive/db/auto.sqlite");
//define("db_name", "/var/www/html/db/gebwms.db");

define("db_name", "db/gebwms.db");


/** database host, usually it's "127.0.0.1" or "localhost", some servers also need port info, like "127.0.0.1:8080" */
define("DB_HOST", "127.0.0.1");

/** name of the database. please note: database and database table are not the same thing! */
define("DB_NAME", "login");

/** user for your database. the user needs to have rights for SELECT, UPDATE, DELETE and INSERT.
/** By the way, it's bad style to use "root", but for development it will work */
define("DB_USER", "tom");

/** The password of the above user */
define("DB_PASS", "bombom33");
