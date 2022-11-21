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


//	Trying a new way of providing feedback and output.
$script_message_str		=	'';
$script_message_cde		=	666;		//	by default it goes wrong; 0: green light!
$script_uid				=	100;		//	unique to every AJAX script


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

			(is_it_enabled($_SESSION['menu_my_account']))

			AND

			(can_user_update($_SESSION['menu_my_account']))

		)

		{



			//	Grab the user ID for the update SQL
			$user_uid		=	leave_numbers_only($_SESSION['user_id']);		// remove anything that is not a number


			$language_str	=	trim($_POST['language_js']);

			//	Check if the language provided is actually supported. This means comparing it to the supported_languages_arr from lib-functions


			if ($user_uid > 0)
			{

				$db->beginTransaction();

				if ($stmt = $db->prepare('


					UPDATE

					users

					SET

					user_language		=	:uuser_language

					WHERE

					user_id	 =	:suser_id

				'))
				{




					$stmt->bindValue(':uuser_language',			$language_str,		PDO::PARAM_STR);
					$stmt->bindValue(':suser_id',				$user_uid,			PDO::PARAM_INT);

					$stmt->execute();

					// make sure to commit all of the changes to the DATABASE !
					$db->commit();

					$script_message_str	=	$mylang['a_OK'];
					$script_message_cde	=	0;	//	all good here!

				}

				// show an error if the query has an error
				else
				{
					$script_message_str	=	$mylang['could_not_update'];
					$script_message_cde	=	100;

				}


			}
			else
			{
				$script_message_str	=	$mylang['user_uid_incorrect'];
				$script_message_cde	=	101;
			}


		}	// END OF user permission checks
		else
		{
			$script_message_str	=	$mylang['permissions_error'];
			$script_message_cde	=	102;
		}



		// Close db connection !
		$db = null;



	}		//	End of try bracket !
	catch(PDOException $e)
	{
		$db->rollBack();
		//	When things go sideways...
		$script_message_str	=	$e->getMessage();
	}




} else {
    // the user is not logged in. you can do whatever you want here.
	//echo $mylang['ps not logged in message'];
}




$output_msg_class	=	'';

if ($script_message_cde == 0)
{
	$output_msg_class	=	'ajax_good_msg_class';
}
else
{

	$output_msg_class	=	'ajax_err_msg_class';
	$script_message_str	=	'Err' . $script_uid . $script_message_cde . ': ' . $script_message_str;
}


$output_msg		=	'<p class="has-text-centered ' . $output_msg_class . '">' . $script_message_str . '</p>';

//	Provide with the output here?
print_message($script_message_cde, $output_msg);


?>
 
