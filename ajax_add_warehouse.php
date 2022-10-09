<?php

// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once("lib_passwd.php");

// include the configs / constants for the database connection
require_once("lib_db.php");

// load the login class
require_once("lib_login.php");

// load the supporting functions....
require_once("lib_functions.php");



// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();



// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {


    // the user is logged in.

	try
	{



		include("lib_db_conn.php");


		// Need to figure out some admin editing access control thing for this.....
		if 
		(
		
			(

				(can_user_access($_SESSION['user_inventory']))

			)
			
			
			AND
			
			(leave_numbers_only($_SESSION['user_priv']) ==	admin_priv)
			
			
		)
		{


			// Provided name
			$name_str = trim($_POST['new_item_name_js']);


			if (strlen($name_str)	<	2)	{
				print_message(3, "Too short name");
			}
			else
			{

				$db->beginTransaction();


		$found_a_match	=	false;


		//
		// Seek out for duplicate entry !
		//
		$sql	=	"


			SELECT

			*

			FROM  geb_warehouse

			WHERE

			wh_code = :iname
			
			and
			
			wh_disabled = 0

		";


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':iname',			$name_str,			PDO::PARAM_STR);
			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$found_a_match	=	true;
			}

		}
		// show an error if the query has an error
		else
		{
		}


				if (!$found_a_match)
				{


					if ($stmt = $db->prepare("


					INSERT
					
					INTO
					
					geb_warehouse
					
					(
						wh_code
					) 

					VALUES

					(
						:iname
					)


					"))


					{

						$stmt->bindValue(':iname',		$name_str,		PDO::PARAM_STR);
						$stmt->execute();

						// make sure to commit all of the changes to the DATABASE !
						$db->commit();
						// dummy message... Just to keep the script happy ? Do not show anything to the user tho !
						print_message(0, "a-OK");

					}
					// show an error if the query has an error
					else
					{
						print_message(2, 'error' . ": x10002");
					}

				}
				else
				{
					print_message(3, "Entry already exists!");
				}


			}





		}	// END OF user priv check
		else
		{
			print_message(23, 'issue with user permissions');
		}








	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		$db->rollBack();
		print_message(1, $e->getMessage());
	}



} else {
    // the user is not logged in. you can do whatever you want here.
	echo 'ps not logged in message';
}



?>
