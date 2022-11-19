<?php

// TO DO: check if certain mandatory inputs are provided!


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

			(is_it_enabled($_SESSION['menu_adm_users']))

			AND

			(can_user_update($_SESSION['menu_adm_users']))

		)


		{


			$user_uid	=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number

			if ($user_uid > 0)
			{


				// Data from the user to process...
				$product_search			=	leave_numbers_only($_POST['product_search_js']);
				$location_search		=	leave_numbers_only($_POST['location_search_js']);
				$order_search			=	leave_numbers_only($_POST['order_search_js']);
				$prod2location			=	leave_numbers_only($_POST['prod2location_js']);
				$recent_activity		=	leave_numbers_only($_POST['recent_activity_js']);
				$mgr_products			=	leave_numbers_only($_POST['mgr_products_js']);
				$adm_users				=	leave_numbers_only($_POST['adm_users_js']);
				$adm_warehouses			=	leave_numbers_only($_POST['adm_warehouses_js']);
				$adm_wh_locations		=	leave_numbers_only($_POST['adm_wh_locations_js']);
				$my_account				=	leave_numbers_only($_POST['my_account_js']);



				$db->beginTransaction();


				if ($stmt = $db->prepare('


					UPDATE

					users

					SET

					menu_adm_warehouse			=		:umenu_adm_warehouse,
					menu_adm_warehouse_loc		=		:umenu_adm_warehouse_loc,
					menu_adm_users				=		:umenu_adm_users,
					menu_prod_search			=		:umenu_prod_search,
					menu_location_search		=		:umenu_location_search,
					menu_order_search			=		:umenu_order_search,
					menu_prod2loc				=		:umenu_prod2loc,
					menu_recent_activity		=		:umenu_recent_activity,
					menu_mgr_prod_add_update	=		:umenu_mgr_prod_add_update,
					menu_my_account				=		:umenu_my_account

					WHERE

					user_id	 =	:suser_id

				'))
				{



					$stmt->bindValue(':umenu_adm_warehouse',			$adm_warehouses,		PDO::PARAM_INT);
					$stmt->bindValue(':umenu_adm_warehouse_loc',		$adm_wh_locations,		PDO::PARAM_INT);
					$stmt->bindValue(':umenu_adm_users',				$adm_users,				PDO::PARAM_INT);
					$stmt->bindValue(':umenu_prod_search',				$product_search,		PDO::PARAM_INT);
					$stmt->bindValue(':umenu_location_search',			$location_search,		PDO::PARAM_INT);
					$stmt->bindValue(':umenu_order_search',				$order_search,			PDO::PARAM_INT);
					$stmt->bindValue(':umenu_prod2loc',					$prod2location,			PDO::PARAM_INT);
					$stmt->bindValue(':umenu_recent_activity',			$recent_activity,		PDO::PARAM_INT);
					$stmt->bindValue(':umenu_mgr_prod_add_update',		$mgr_products,			PDO::PARAM_INT);
					$stmt->bindValue(':umenu_my_account',				$my_account,			PDO::PARAM_INT);



					$stmt->bindValue(':suser_id',		$user_uid,		PDO::PARAM_INT);


					$stmt->execute();

					// make sure to commit all of the changes to the DATABASE !
					$db->commit();

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
		$db->rollBack();
		print_message(1, $e->getMessage());
	}




} else {
    // the user is not logged in. you can do whatever you want here.
	echo $mylang['ps not logged in message'];
}



?>
 
