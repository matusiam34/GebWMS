<?php

// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once('lib_passwd.php');

// include the configs / constants for the database connection
require_once('lib_db.php');

// load the login class
require_once('lib_login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();



// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {


    // the user is logged in.

	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');


		//	Check if user has the right access level
		if 
		(

			(is_it_enabled($_SESSION['menu_adm_warehouse']))

			AND

			(can_user_update($_SESSION['menu_adm_warehouse']))

		)
		{




			// Name provided
			$name_str		=	trim($_POST['item_name_js']);
			// ID
			$id_str			=	leave_numbers_only($_POST['item_id_js']);



			if ($id_str	<	0)	{
				print_message(3, 'No ID provided');
			}
			elseif (strlen($name_str)	<	2)	{
				print_message(3, 'Too short');
			}

			else
			{


				$db->beginTransaction();


				if ($stmt = $db->prepare('



					UPDATE

					geb_warehouse

					SET

					wh_code		=		:iname

					WHERE

					wh_pkey	 =	:uid


				'))


				{

					$stmt->bindValue(':iname',		$name_str,			PDO::PARAM_STR);
					$stmt->bindValue(':uid',		$id_str,			PDO::PARAM_INT);


					$stmt->execute();
					// make sure to commit all of the changes to the DATABASE !
					$db->commit();
					// dummy message... Just to keep the script happy ? Do not show anything to the user tho !
					print_message(0, 'a-OK');

				}
				// show an error if the query has an error
				else
				{
					print_message(2, 'error : x10002');
				}


			}




		}	// END OF ACL check
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
