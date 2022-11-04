<?php


//	TO DO:	check if certain mandatory inputs are provided
//			and make sure that they meet minimal criteria like the username can't be like 0 characters long



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

    // the user is logged in. Perform the sql query !


	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');



		//	Check if user has the right access level
		if 
		(

			(is_it_enabled($_SESSION['menu_mgr_prod_add_update']))

			AND

			(can_user_update($_SESSION['menu_mgr_prod_add_update']))

		)


		{


			$user_uid	=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number

			// make sure that the user id is provided. 
			if ($user_uid > 0)
			{


				// Data from the user to process...
				$user_username		=	trim($_POST['user_username_js']);
				$user_firstname		=	trim($_POST['user_firstname_js']);
				$user_lastname		=	trim($_POST['user_lastname_js']);
				$user_desc			=	trim($_POST['user_desc_js']);
				$user_email			=	trim($_POST['user_email_js']);
				$user_active		=	leave_numbers_only($_POST['user_active_js']);


				if ($stmt = $db->prepare('


					UPDATE

					users

					SET

					user_name			=		:iuser_username,
					user_firstname		=		:iuser_firstname,
					user_surname		=		:iuser_surname,
					user_email			=		:iuser_email,
					user_description	=		:iuser_description,
					user_active			=		:iuser_active

					WHERE

					user_id	 =	:suser_id

				'))
				{




					$stmt->bindValue(':iuser_username',			$user_username,		PDO::PARAM_STR);
					$stmt->bindValue(':iuser_firstname',		$user_firstname,	PDO::PARAM_STR);
					$stmt->bindValue(':iuser_surname',			$user_lastname,		PDO::PARAM_STR);
					$stmt->bindValue(':iuser_email',			$user_email,		PDO::PARAM_STR);
					$stmt->bindValue(':iuser_description',		$user_desc,			PDO::PARAM_STR);
					$stmt->bindValue(':iuser_active',			$user_active,		PDO::PARAM_INT);

					$stmt->bindValue(':suser_id',				$user_uid,			PDO::PARAM_INT);
					$stmt->execute();

					// dummy message... Just to keep the script happy ? Do not show anything to the use tho !
					print_message(0, 'a-OK');

				}

				// show an error if the query has an error
				else
				{
					print_message(2, 'could not get data');
				}


				}
				// check if the location is > 0... If not ===>>>> throw an error. Can be done better I am sure!
				else
				{
					print_message(2, 'User ID incorrect');
				}




			}	// END OF user permission checks
			else
			{
				print_message(23, 'permissions error');
			}



			// Close db connection !
			$db = null;



	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		print_message(1, $e->getMessage());
	}




} else {
    // the user is not logged in. you can do whatever you want here.
	echo $mylang['ps not logged in message'];
}



?>
 
