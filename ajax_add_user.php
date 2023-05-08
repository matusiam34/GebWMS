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

			(is_it_enabled($_SESSION['menu_adm_users']))

			AND

			(can_user_add($_SESSION['menu_adm_users']))

		)


		{


			$user_name		=	trim($_POST['user_name_js']);

			// user name has to be at least 2 characters long! Could change later on.
			if ( strlen($user_name) < 2 )
			{
				print_message(23, 'User name short');
			}
			else
			{

				//	Start Transation here?
				$db->beginTransaction();


				//	Data from the user to process...

				//	General details
				$user_firstname			=	trim($_POST['user_firstname_js']);
				$user_lastname			=	trim($_POST['user_lastname_js']);
				$user_desc				=	trim($_POST['user_desc_js']);
				$user_email				=	trim($_POST['user_email_js']);
				$user_active			=	leave_numbers_only($_POST['user_active_js']);
				$user_warehouse			=	leave_numbers_only($_POST['user_warehouse_js']);


				//	ACL
				$product_search			=	leave_numbers_only($_POST['product_search_js']);
				$location_search		=	leave_numbers_only($_POST['location_search_js']);
				$order_search			=	leave_numbers_only($_POST['order_search_js']);
				$goodsin				=	leave_numbers_only($_POST['goodsin_js']);
				$prod2location			=	leave_numbers_only($_POST['prod2location_js']);
				$recent_activity		=	leave_numbers_only($_POST['recent_activity_js']);
				$mgr_products			=	leave_numbers_only($_POST['mgr_products_js']);
				$adm_users				=	leave_numbers_only($_POST['adm_users_js']);
				$adm_warehouses			=	leave_numbers_only($_POST['adm_warehouses_js']);
				$adm_wh_locations		=	leave_numbers_only($_POST['adm_wh_locations_js']);
				$my_account				=	leave_numbers_only($_POST['my_account_js']);

				$mgr_place_order		=	leave_numbers_only($_POST['mgr_place_order_js']);
				$mgr_orders				=	leave_numbers_only($_POST['mgr_orders_js']);
				$pick_order				=	leave_numbers_only($_POST['pick_order_js']);


				if ($stmt = $db->prepare('


					INSERT






					INTO

					users

					(
						user_name,
						user_firstname,
						user_surname,
						user_email,
						user_description,
						user_password_hash,
						user_active,
						user_warehouse,
						menu_adm_warehouse,
						menu_adm_warehouse_loc,
						menu_adm_users,
						menu_prod_search,
						menu_location_search,
						menu_order_search,
						menu_goodsin,
						menu_prod2loc,

						menu_pick_order,

						menu_recent_activity,
						menu_mgr_prod_add_update,

						menu_mgr_place_order,
						menu_mgr_orders,

						menu_my_account
					) 

					VALUES

					(
						:iuser_name,
						:iuser_firstname,
						:iuser_surname,
						:iuser_email,
						:iuser_description,
						:iuser_password_hash,
						:iuser_active,
						:iuser_warehouse,
						:imenu_adm_warehouse,
						:imenu_adm_warehouse_loc,
						:imenu_adm_users,
						:imenu_prod_search,
						:imenu_location_search,
						:imenu_order_search,
						:imenu_goodsin,
						:imenu_prod2loc,

						:imenu_pick_order,

						:imenu_recent_activity,
						:imenu_mgr_prod_add_update,

						:imenu_mgr_place_order,
						:imenu_mgr_orders,

						:imenu_my_account
					)


				'))
				{




					$stmt->bindValue(':iuser_name',				$user_name,				PDO::PARAM_STR);
					$stmt->bindValue(':iuser_firstname',		$user_firstname,		PDO::PARAM_STR);
					$stmt->bindValue(':iuser_surname',			$user_lastname,			PDO::PARAM_STR);
					$stmt->bindValue(':iuser_email',			$user_email,			PDO::PARAM_STR);
					$stmt->bindValue(':iuser_description',		$user_desc,				PDO::PARAM_STR);

					// Needs to changed for a different default password!!!
					$stmt->bindValue(':iuser_password_hash',	'$2y$10$D.mv5xg21s4Yi79a98UjUeCJk3/VEmKMu91yYDIiwOVxKZL.AmRqO',		PDO::PARAM_STR);

					$stmt->bindValue(':iuser_active',			$user_active,			PDO::PARAM_INT);
					$stmt->bindValue(':iuser_warehouse',		$user_warehouse,		PDO::PARAM_INT);


					$stmt->bindValue(':imenu_adm_warehouse',			$adm_warehouses,		PDO::PARAM_INT);
					$stmt->bindValue(':imenu_adm_warehouse_loc',		$adm_wh_locations,		PDO::PARAM_INT);
					$stmt->bindValue(':imenu_adm_users',				$adm_users,				PDO::PARAM_INT);
					$stmt->bindValue(':imenu_prod_search',				$recent_activity,		PDO::PARAM_INT);
					$stmt->bindValue(':imenu_location_search',			$location_search,		PDO::PARAM_INT);
					$stmt->bindValue(':imenu_order_search',				$order_search,			PDO::PARAM_INT);
					$stmt->bindValue(':imenu_goodsin',					$goodsin,				PDO::PARAM_INT);
					$stmt->bindValue(':imenu_prod2loc',					$prod2location,			PDO::PARAM_INT);
					$stmt->bindValue(':imenu_pick_order',				$pick_order,			PDO::PARAM_INT);
					$stmt->bindValue(':imenu_recent_activity',			$recent_activity,		PDO::PARAM_INT);
					$stmt->bindValue(':imenu_mgr_prod_add_update',		$mgr_products,			PDO::PARAM_INT);
					$stmt->bindValue(':imenu_mgr_place_order',			$mgr_place_order,		PDO::PARAM_INT);
					$stmt->bindValue(':imenu_mgr_orders',				$mgr_orders,			PDO::PARAM_INT);
					$stmt->bindValue(':imenu_my_account',				$my_account,			PDO::PARAM_INT);


					$stmt->execute();

					// make sure to commit all of the changes to the DATABASE !
					$db->commit();

					// dummy message... Just to keep the script happy ? Do not show anything to the use tho !
					print_message(0, 'a-OK');

				}

				// show an error if the query has an error
				else
				{
					print_message(2, 'INSERT failed');
				}


			}	//	end of all checks before going 2 INSERT


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
 
