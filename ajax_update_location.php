<?php

// TO DO: check if certain mandatory inputs are provided!


// Ugly fix but I am not going to get hung up on things like these... In the future a better solution can be found if needed!



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

			(can_user_access($_SESSION['menu_adm_warehouse_loc']))

			AND

			(can_user_update($_SESSION['menu_adm_warehouse_loc']))

		)


		{


			// remove anything that is not a number?!
			$loc_uid	=	leave_numbers_only($_POST['loc_uid_js']);

			if ($loc_uid > 0)
			{


				// Data from the user to process...
				$warehouse		=	trim($_POST['warehouse_js']);
				$location		=	trim($_POST['location_js']);
				$barcode		=	trim($_POST['barcode_js']);
				$type			=	trim($_POST['type_js']);
				$blocked		=	trim($_POST['blocked_js']);
				$loc_desc		=	trim($_POST['loc_desc_js']);



				if ($stmt = $db->prepare('


					UPDATE

					geb_location

					SET

					loc_wh_pkey		=		:uloc_wh_pkey,
					loc_code		=		:uloc_code,
					loc_barcode		=		:uloc_barcode,
					loc_type		=		:uloc_type,
					loc_blocked		=		:uloc_blocked,
					loc_note		=		:uloc_note

					WHERE

					loc_pkey	 =	:sloc_pkey

				'))
				{

					$stmt->bindValue(':uloc_wh_pkey',	$warehouse,		PDO::PARAM_INT);
					$stmt->bindValue(':uloc_code',		$location,		PDO::PARAM_STR);
					$stmt->bindValue(':uloc_barcode',	$barcode,		PDO::PARAM_STR);
					$stmt->bindValue(':uloc_type',		$type,			PDO::PARAM_INT);
					$stmt->bindValue(':uloc_blocked',	$blocked,		PDO::PARAM_INT);
					$stmt->bindValue(':uloc_note',		$loc_desc,		PDO::PARAM_STR);
					$stmt->bindValue(':sloc_pkey',		$loc_uid,		PDO::PARAM_INT);
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
					print_message(2, 'location ID incorrect');
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
 
