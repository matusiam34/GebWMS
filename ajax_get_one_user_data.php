<?php

//	Add a simple check to see if the user id is a number and it has to be > 0

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

    // the user is logged in


	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');



		//	Check if user has the right access level
		if 
		(

			(is_it_enabled($_SESSION['menu_adm_users']))

		)
		{



				$user_uid	=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number


				if ($stmt = $db->prepare('

				SELECT

				*

				FROM  users
				
				WHERE
				
				user_id	=	:suser_id


				'))
				{

					$stmt->bindValue(':suser_id',	$user_uid,		PDO::PARAM_INT);
					$stmt->execute();

					// My result array before encoded via json !
					$result = array();
					$result['control'] = 0;		// 0 means all went well !!!

					/* fetch values */
					$data_results	=	array();
					$table_text		=	"";


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{

						// Wrap the values into an array for json encoding !
						$data_results = array
						(

								'username'						=> trim($row['user_name']),
								'firstname'						=> trim($row['user_firstname']),
								'surname'						=> trim($row['user_surname']),
								'email'							=> trim($row['user_email']),
								'description'					=> trim($row['user_description']),
								'active'						=> leave_numbers_only($row['user_active']),
								'warehouse'						=> leave_numbers_only($row['user_warehouse']),

								'menu_my_account'				=> leave_numbers_only($row['menu_my_account']),
								'menu_adm_warehouse'			=> leave_numbers_only($row['menu_adm_warehouse']),
								'menu_adm_warehouse_loc'		=> leave_numbers_only($row['menu_adm_warehouse_loc']),
								'menu_adm_users'				=> leave_numbers_only($row['menu_adm_users']),

								'menu_prod_search'				=> leave_numbers_only($row['menu_prod_search']),
								'menu_location_search'			=> leave_numbers_only($row['menu_location_search']),
								'menu_order_search'				=> leave_numbers_only($row['menu_order_search']),


								'menu_goodsin'					=> leave_numbers_only($row['menu_goodsin']),
								'menu_prod2loc'					=> leave_numbers_only($row['menu_prod2loc']),
								'menu_pick_order'				=> leave_numbers_only($row['menu_pick_order']),
								'menu_recent_activity'			=> leave_numbers_only($row['menu_recent_activity']),

								'menu_mgr_prod_add_update'		=> leave_numbers_only($row['menu_mgr_prod_add_update']),
								'menu_mgr_place_order'			=> leave_numbers_only($row['menu_mgr_place_order']),
								'menu_mgr_orders'				=> leave_numbers_only($row['menu_mgr_orders'])


						);


					}


					$result['data'] = $data_results;

					echo json_encode($result);


				}

				// show an error if the query has an error
				else
				{
					print_message(2, 'could not get data');
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
 
