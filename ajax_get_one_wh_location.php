<?php


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


		// allow to execute script only if the requirements are met !
		// min_priv : variable that holds the lowest level user that can access and execute this script
		if 
		(

			(

				(can_user_access($_SESSION['user_inventory']))

			)

			AND

			(leave_numbers_only($_SESSION['user_priv']) >=	min_priv)

		)


		{


			// remove anything that is not a number?!
			$loc_uid	=	leave_numbers_only($_POST['loc_uid_js']);

			if ($loc_uid > 0)
			{


				if ($stmt = $db->prepare('

					SELECT

					geb_warehouse.wh_pkey,
					geb_location.loc_pkey,
					geb_location.loc_code,

					geb_location.loc_barcode,
					geb_location.loc_type,
					geb_location.loc_blocked,
					geb_location.loc_note


					FROM  geb_location

					INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


					WHERE

					geb_location.loc_disabled = 0 AND geb_warehouse.wh_disabled = 0

					AND

					geb_location.loc_pkey = :iloc


					ORDER BY wh_code, loc_code

				'))
				{



					$stmt->bindValue(':iloc',	$loc_uid,		PDO::PARAM_INT);
					$stmt->execute();


					// My result array before encoded via json !
					$result = array();
					$result['control'] = 0;		// 0 means all went well !!!

					/* fetch values */
					$data_results	=	array();


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{

						$data_results	=	$row;

					}


					$result['data'] = $data_results;

					echo json_encode($result);


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
 
