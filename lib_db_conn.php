<?php


	// This selects which database backend to use for all of the queries that will be run by everything.

	$db	=	null;

	// connect to the database !
	if (sqlite_or_mariadb == 0)
	{
		$db = new PDO('sqlite:' . db_name);
	}
	else
	{
		$db = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
	}

/*

	// enable debug for DB if you need it?!
	if (db_debug == 1)
	{
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

*/


?>
