<?php


/*

	Error code for the script!
	Script code		=	103

 
	//	Action code breakdown
	0	:	Get all users.
	1	:	Get one user info.
	2	:	Add user!
	3	:	Update user info!
	4	:	Update user ACL!
	5	:	Set password for user!!!

*/


// load the login class
require_once('lib_login.php');


$message_id		=	103999;		//	999:	default bad
$message2op		=	'';			//	When an error happens provide a message here. Can be something positive as well like "All done", "a-Ok"
$html_results	=	'';			//	HTML code as output. Depending the Action Code this can be empty or a full HTML table.
$data_results	=	array();	//	array with all of the data collected

// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {


    // the user is logged in.

	try
	{


		// load the supporting functions....
		require_once('lib_system.php');
		require_once('lib_db_conn.php');



		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number


		//	Get all users. The action code for this is 0

		//	Note:	Since I have a breakdown of access rights like: Company, warehouse and admin
		//			I need to make sure to reflect this here. The Admin of mySTD (company) cannot manage 
		//			users that are in a different company. But an Admin of all companies can manage them all.
		//			So the admin of mySTD can only see and manage mySTY users.

		if ($action_code == 0)
		{



			$sql	=	'


				SELECT
				*
				FROM users

			';

			//
			//	Figure out what can happen and what can't!
			//
			if ($user_company_uid > 0)
			{

				//	only show users from the same company...
				$sql	.=	'

					WHERE

					user_company	=	:scompany_uid

				';

				if ($user_warehouse_uid > 0)
				{

					$sql	.=	'

						AND

						user_warehouse	=	:swarehouse_uid

					';

				}

			}






			//	Order the output!
			$sql	.=	'

				ORDER BY user_name ASC

			';





			if ($stmt = $db->prepare($sql))
			{

				if ($user_company_uid > 0)
				{
					$stmt->bindValue(':scompany_uid',	$user_company_uid,	PDO::PARAM_INT);
					if ($user_warehouse_uid > 0)
					{
						$stmt->bindValue(':swarehouse_uid',	$user_warehouse_uid,	PDO::PARAM_INT);
					}

				}




				$stmt->execute();


				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{

					$color_code	=	'';

					$disabled	=	leave_numbers_only($row['user_active']);

					if ($disabled == 1)	{		$color_code		=	'red_class';	}					

					$html_results	.=	'<tr data-id="' . leave_numbers_only($row['user_id']) . '" class="' . $color_code . '" >';
						$html_results	.=	'<td>' . trim($row['user_name']) . '</td>';
					$html_results	.=	'</tr>';					

				}

				$message_id		=	0;	//	all went well

			}

		}	//	Action 0 end!


		//	Get details of one!

		else if ($action_code == 1)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_users'])
			)
			{

				// Grab the user UID!
				$user_uid	=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number

				$sql	=	'


					SELECT


					user_id,
					user_name,
					user_firstname,
					user_surname,
					user_email,
					user_description,
					user_is_admin,
					user_company,
					user_warehouse,
					user_language,
					user_active,
					menu_adm_company,
					menu_adm_warehouse,
					menu_adm_warehouse_loc,
					menu_adm_users,
					menu_adm_category,
					menu_adm_uom,
					menu_adm_container_type,
					menu_prod_search,
					menu_location_search,
					menu_goodsin,
					menu_mpa,
					menu_mpp,
					menu_recent_activity,
					menu_mgr_product_line,
					menu_mgr_product_sku,
					menu_my_account


					FROM users
					
					WHERE
					
					user_id	=	:suser_id

				';


				if ($stmt = $db->prepare($sql))
				{

					$stmt->bindValue(':suser_id',	$user_uid,		PDO::PARAM_INT);
					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results	=	$row;
					}

					$message_id		=	0;	//	all went well
				}




			}
			
		}	//	Action 1 end!


		//	Add one!

		else if ($action_code == 2)
		{

			//	Only an Admin of this system can add!
			if
			(

				(
					is_it_enabled($_SESSION['menu_adm_users'])
				)

				AND

				(
					can_user_add($_SESSION['menu_adm_users'])
				)

			)
			{


				// Data from the user to process...
				$user_username			=	trim($_POST['user_username_js']);
				$user_firstname			=	trim($_POST['user_firstname_js']);
				$user_lastname			=	trim($_POST['user_lastname_js']);
				$user_company			=	leave_numbers_only($_POST['user_company_js']);

				$user_desc				=	trim($_POST['user_desc_js']);
				$user_email				=	trim($_POST['user_email_js']);
				$user_warehouse			=	leave_numbers_only($_POST['user_warehouse_js']);
				$user_is_admin			=	leave_numbers_only($_POST['user_is_admin_js']);
				$user_active			=	leave_numbers_only($_POST['user_active_js']);


				$product_search			=	leave_numbers_only($_POST['product_search_js']);
				$location_search		=	leave_numbers_only($_POST['location_search_js']);

				$goodsin				=	leave_numbers_only($_POST['goodsin_js']);
				$mpa					=	leave_numbers_only($_POST['mpa_js']);
				$mpp					=	leave_numbers_only($_POST['mpp_js']);
				$recent_activity		=	leave_numbers_only($_POST['recent_activity_js']);

				
				//	For now not using the product line! SKUs only! Maybe one day
				//$mgr_product_line		=	leave_numbers_only($_POST['mgr_product_line_js']);
				
				$mgr_product_sku		=	leave_numbers_only($_POST['mgr_product_sku_js']);


				$my_account				=	leave_numbers_only($_POST['my_account_js']);
				$adm_users				=	leave_numbers_only($_POST['adm_users_js']);
				$adm_warehouses			=	leave_numbers_only($_POST['adm_warehouses_js']);
				$adm_wh_locations		=	leave_numbers_only($_POST['adm_wh_locations_js']);
				$adm_categories			=	leave_numbers_only($_POST['adm_categories_js']);
				$adm_company			=	leave_numbers_only($_POST['adm_companies_js']);
				$adm_container_type		=	leave_numbers_only($_POST['adm_container_type_js']);



				if (strlen($user_username) >= 2)	//	I am allowing the username to be min 2 characters
				{

					$found_match	=	0;

					$db->beginTransaction();


					//
					// Seek out for a duplicate username entry!
					//
					$sql	=	'

						SELECT

						user_name

						FROM users
						
						WHERE
						
						user_name	=	:suser_name

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':suser_name',	$user_username,	PDO::PARAM_STR);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{

							if(strcmp(trim($row['user_name']), $user_username) === 0)
							{
								$found_match++;
							}

						}

					}
					// show an error if the query has an error?
					else
					{
						//	Need to do something about this some time...
					}


					//	0	means no issues!
					if ($found_match == 0)
					{

						$sql	=	'

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
								user_company,
								user_warehouse,
								user_is_admin,
								user_active,
								menu_adm_warehouse,
								menu_adm_warehouse_loc,
								menu_adm_users,
								menu_adm_category,
								menu_adm_company,
								menu_adm_container_type,
								menu_prod_search,
								menu_location_search,
								menu_goodsin,
								menu_mpa,
								menu_mpp,
								menu_recent_activity,
								menu_mgr_product_sku,
								menu_my_account
							) 

							VALUES

							(
								:iuser_username,
								:iuser_firstname,
								:iuser_surname,
								:iuser_email,
								:iuser_description,
								:iuser_password_hash,
								:iuser_company,
								:iuser_warehouse,
								:iuser_is_admin,
								:iuser_active,
								:imenu_adm_warehouse,
								:imenu_adm_warehouse_loc,
								:imenu_adm_users,
								:imenu_adm_category,
								:imenu_adm_company,
								:imenu_adm_container_type,
								:imenu_prod_search,
								:imenu_location_search,
								:imenu_goodsin,
								:imenu_mpa,
								:imenu_mpp,
								:imenu_recent_activity,
								:imenu_mgr_product_sku,
								:imenu_my_account
							)

						';



						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':iuser_username',				$user_username,		PDO::PARAM_STR);
							$stmt->bindValue(':iuser_firstname',			$user_firstname,	PDO::PARAM_STR);
							$stmt->bindValue(':iuser_surname',				$user_lastname,		PDO::PARAM_STR);
							$stmt->bindValue(':iuser_email',				$user_email,		PDO::PARAM_STR);
							$stmt->bindValue(':iuser_description',			$user_desc,			PDO::PARAM_STR);
							//	FIX
							//	Probably needs to be fixed at some point... The admin can change the password so... Will see
							$stmt->bindValue(':iuser_password_hash',		'$2y$10$D.mv5xg21s4Yi79a98UjUeCJk3/VEmKMu91yYDIiwOVxKZL.AmRqO',		PDO::PARAM_STR);
							$stmt->bindValue(':iuser_company',				$user_company,		PDO::PARAM_INT);
							$stmt->bindValue(':iuser_warehouse',			$user_warehouse,	PDO::PARAM_INT);
							$stmt->bindValue(':iuser_is_admin',				$user_is_admin,		PDO::PARAM_INT);
							$stmt->bindValue(':iuser_active',				$user_active,		PDO::PARAM_INT);
							$stmt->bindValue(':imenu_adm_warehouse',		$adm_warehouses,	PDO::PARAM_INT);
							$stmt->bindValue(':imenu_adm_warehouse_loc',	$adm_wh_locations,	PDO::PARAM_INT);
							$stmt->bindValue(':imenu_adm_users',			$adm_users,			PDO::PARAM_INT);
							$stmt->bindValue(':imenu_adm_category',			$adm_categories,	PDO::PARAM_INT);

							$stmt->bindValue(':imenu_adm_company',			$adm_company,			PDO::PARAM_INT);
							$stmt->bindValue(':imenu_adm_container_type',	$adm_container_type,	PDO::PARAM_INT);



							$stmt->bindValue(':imenu_prod_search',			$product_search,	PDO::PARAM_INT);
							$stmt->bindValue(':imenu_location_search',		$location_search,	PDO::PARAM_INT);
							$stmt->bindValue(':imenu_goodsin',				$goodsin,			PDO::PARAM_INT);
							$stmt->bindValue(':imenu_mpa',					$mpa,				PDO::PARAM_INT);
							$stmt->bindValue(':imenu_mpp',					$mpp,				PDO::PARAM_INT);
							$stmt->bindValue(':imenu_recent_activity',		$recent_activity,	PDO::PARAM_INT);


							//	SQL bit:
							//	menu_mgr_product_line,
							//	:imenu_mgr_product_line,
							//	PHP:
							//	$stmt->bindValue(':imenu_mgr_product_line',		$mgr_product_line,	PDO::PARAM_INT);


							$stmt->bindValue(':imenu_mgr_product_sku',		$mgr_product_sku,	PDO::PARAM_INT);
							$stmt->bindValue(':imenu_my_account',			$my_account,		PDO::PARAM_INT);

							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}


					}
					else
					{

						if ($found_match == 1)
						{
							$message_id		=	103200;
							$message2op		=	$mylang['user_already_exists'];
						}

					}

				}
				else
				{
					$message_id		=	103201;
					$message2op		=	$mylang['name_too_short'];
				}






			}
			
		}	//	Action 2 end!


		//	Update user info!

		else if ($action_code == 3)
		{

			//	Only an Admin of this system can update a user!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_users']))

				AND

				(can_user_update($_SESSION['menu_adm_users']))

			)
			{

				// Data from the user to process...
				$user_username		=	trim($_POST['user_username_js']);
				$user_firstname		=	trim($_POST['user_firstname_js']);
				$user_lastname		=	trim($_POST['user_lastname_js']);
				$user_desc			=	trim($_POST['user_desc_js']);
				$user_email			=	trim($_POST['user_email_js']);
				$user_company		=	leave_numbers_only($_POST['user_company_js']);
				$user_warehouse		=	leave_numbers_only($_POST['user_warehouse_js']);
				$user_is_admin		=	leave_numbers_only($_POST['user_is_admin_js']);
				$user_active		=	leave_numbers_only($_POST['user_active_js']);
				$user_uid			=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number


				if
				(
					($user_uid > 0)

					AND

					(is_numeric($user_uid) == true)
				)
				{

					if (strlen($user_username) >= 2)	//	I am allowing the username to be min 2 characters
					{

						$found_match	=	0;

						$db->beginTransaction();


						//
						// Seek out for a duplicate username entry!
						//
						$sql	=	'

							SELECT

							user_id,
							user_name

							FROM users
							
							WHERE
							
							user_name	=	:suser_name

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':suser_name',	$user_username,	PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{

								if
								(
									( leave_numbers_only($row['user_id']) <> $user_uid)

									AND

									(strcmp(trim($row['user_name']), $user_username) === 0)
								)
								{
									$found_match++;
								}

							}

						}
						// show an error if the query has an error?
						else
						{
							//	Need to do something about this some time...
						}


						//	0	means no issues!
						if ($found_match == 0)
						{

							$sql	=	'

								UPDATE

								users

								SET

								user_name			=		:uuser_username,
								user_firstname		=		:uuser_firstname,
								user_surname		=		:uuser_surname,
								user_email			=		:uuser_email,
								user_description	=		:uuser_description,
								user_company		=		:uuser_company,
								user_warehouse		=		:uuser_warehouse,
								user_is_admin		=		:uuser_is_admin,
								user_active			=		:uuser_active

								WHERE

								user_id	 =	:suser_id

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':uuser_username',			$user_username,		PDO::PARAM_STR);
								$stmt->bindValue(':uuser_firstname',		$user_firstname,	PDO::PARAM_STR);
								$stmt->bindValue(':uuser_surname',			$user_lastname,		PDO::PARAM_STR);
								$stmt->bindValue(':uuser_email',			$user_email,		PDO::PARAM_STR);
								$stmt->bindValue(':uuser_description',		$user_desc,			PDO::PARAM_STR);
								$stmt->bindValue(':uuser_company',			$user_company,		PDO::PARAM_INT);
								$stmt->bindValue(':uuser_warehouse',		$user_warehouse,	PDO::PARAM_INT);
								$stmt->bindValue(':uuser_is_admin',			$user_is_admin,		PDO::PARAM_INT);
								$stmt->bindValue(':uuser_active',			$user_active,		PDO::PARAM_INT);

								$stmt->bindValue(':suser_id',				$user_uid,			PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{

							if ($found_match == 1)
							{
								$message_id		=	103202;
								$message2op		=	$mylang['user_already_exists'];
							}

						}

					}
					else
					{
						$message_id		=	103203;
						$message2op		=	$mylang['name_too_short'];
					}

				}
				else
				{
					$message_id		=	103204;
					$message2op		=	$mylang['user_uid_incorrect'];
				}


			}
			
		}	//	Action 3 end!


		//	Update user ACL!

		else if ($action_code == 4)
		{

			//	Only an Admin of this system can update ACL!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_users']))

				AND

				(can_user_update($_SESSION['menu_adm_users']))

			)
			{

				// Data from the user to process...
				$product_search			=	leave_numbers_only($_POST['product_search_js']);
				$location_search		=	leave_numbers_only($_POST['location_search_js']);

				$goodsin				=	leave_numbers_only($_POST['goodsin_js']);
				$mpa					=	leave_numbers_only($_POST['mpa_js']);
				$mpp					=	leave_numbers_only($_POST['mpp_js']);
				$recent_activity		=	leave_numbers_only($_POST['recent_activity_js']);

				//$mgr_product_line		=	leave_numbers_only($_POST['mgr_product_line_js']);
				$mgr_product_sku		=	leave_numbers_only($_POST['mgr_product_sku_js']);



				$my_account				=	leave_numbers_only($_POST['my_account_js']);
				$adm_users				=	leave_numbers_only($_POST['adm_users_js']);
				$adm_warehouses			=	leave_numbers_only($_POST['adm_warehouses_js']);
				$adm_wh_locations		=	leave_numbers_only($_POST['adm_wh_locations_js']);
				$adm_categories			=	leave_numbers_only($_POST['adm_categories_js']);

				$adm_companies			=	leave_numbers_only($_POST['adm_companies_js']);
				$adm_container_type		=	leave_numbers_only($_POST['adm_container_type_js']);


				$user_uid				=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number


				if
				(
					($user_uid > 0)

					AND

					(is_numeric($user_uid) == true)
				)
				{


					$db->beginTransaction();


					$sql	=	'

						UPDATE

						users

						SET

						menu_adm_warehouse			=		:umenu_adm_warehouse,
						menu_adm_warehouse_loc		=		:umenu_adm_warehouse_loc,
						menu_adm_users				=		:umenu_adm_users,
						menu_adm_category			=		:umenu_adm_category,
						menu_prod_search			=		:umenu_prod_search,
						menu_location_search		=		:umenu_location_search,
						menu_goodsin				=		:umenu_goodsin,
						menu_mpa					=		:umenu_mpa,
						menu_mpp					=		:umenu_mpp,
						menu_recent_activity		=		:umenu_recent_activity,
						menu_mgr_product_sku		=		:umenu_mgr_product_sku,
						menu_my_account				=		:umenu_my_account,
						menu_adm_company			=		:umenu_adm_company,
						menu_adm_container_type		=		:umenu_adm_container_type

						WHERE

						user_id	 =	:suser_id

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':umenu_adm_warehouse',			$adm_warehouses,		PDO::PARAM_INT);
						$stmt->bindValue(':umenu_adm_warehouse_loc',		$adm_wh_locations,		PDO::PARAM_INT);
						$stmt->bindValue(':umenu_adm_users',				$adm_users,				PDO::PARAM_INT);
						$stmt->bindValue(':umenu_adm_category',				$adm_categories,		PDO::PARAM_INT);
						$stmt->bindValue(':umenu_prod_search',				$product_search,		PDO::PARAM_INT);
						$stmt->bindValue(':umenu_location_search',			$location_search,		PDO::PARAM_INT);
						$stmt->bindValue(':umenu_goodsin',					$goodsin,				PDO::PARAM_INT);
						$stmt->bindValue(':umenu_mpa',						$mpa,					PDO::PARAM_INT);
						$stmt->bindValue(':umenu_mpp',						$mpp,					PDO::PARAM_INT);
						$stmt->bindValue(':umenu_recent_activity',			$recent_activity,		PDO::PARAM_INT);

						//	Maybe at some point this will be required... For now only SKUs
						//	menu_mgr_product_line is by default 32768 so all is good for now!

						//	From the SQL bit:
						//	menu_mgr_product_line		=		:umenu_mgr_product_line,
						//	And PHP:
						//	$stmt->bindValue(':umenu_mgr_product_line',			$mgr_product_line,		PDO::PARAM_INT);


						$stmt->bindValue(':umenu_mgr_product_sku',			$mgr_product_sku,		PDO::PARAM_INT);

						$stmt->bindValue(':umenu_my_account',				$my_account,			PDO::PARAM_INT);

						$stmt->bindValue(':umenu_adm_company',				$adm_companies,			PDO::PARAM_INT);
						$stmt->bindValue(':umenu_adm_container_type',		$adm_container_type,	PDO::PARAM_INT);


						$stmt->bindValue(':suser_id',						$user_uid,				PDO::PARAM_INT);

						$stmt->execute();
						$db->commit();

						$message_id		=	0;	//	all went well
						$message2op		=	$mylang['success'];
					}


				}
				else
				{
					$message_id		=	103205;
					$message2op		=	$mylang['user_uid_incorrect'];
				}


			}
			
		}	//	Action 4 end!


		//	Set password for a user!

		else if ($action_code == 5)
		{

			//	Update ACL grants you the ability to update the password!
			//	Maybe this should be : Admin + Update = Change password?
			if
			(

				(is_it_enabled($_SESSION['menu_adm_users']))

				AND

				(check_for_admin($_SESSION['user_is_admin']))

			)
			{

				// Data from the user to process...
				$new_password		=	trim($_POST['new_password_js']);
				$user_uid			=	leave_numbers_only($_POST['user_uid_js']);		// remove anything that is not a number


				if (strlen($new_password) > 2)
				{

					if
					(
						($user_uid > 0)

						AND

						(is_numeric($user_uid) == true)
					)
					{


						$db->beginTransaction();


						$sql	=	'

							UPDATE

							users

							SET

							user_password_hash				=		:unew_password

							WHERE

							user_id	 =	:suser_id

						';


						$new_password = password_hash($new_password, PASSWORD_DEFAULT, array('cost' => 10));

						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':unew_password',		$new_password,			PDO::PARAM_STR);
							$stmt->bindValue(':suser_id',			$user_uid,				PDO::PARAM_INT);

							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}


					}
					else
					{
						$message_id		=	103205;
						$message2op		=	$mylang['user_uid_incorrect'];
					}

				}
				else
				{
					$message_id		=	103206;
					$message2op		=	$mylang['password_too_short'];
				}



			}
			
		}	//	Action 5 end!





	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	103666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all users!
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Get one user details!
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 2:	//	Add user
		print_message($message_id, $message2op);
		break;
		case 3:	//	Update user info
		print_message($message_id, $message2op);
		break;
		case 4:	//	Update user ACL
		print_message($message_id, $message2op);
		break;
		case 5:	//	Set a new password for user
		print_message($message_id, $message2op);
		break;
		default:
		print_message(103945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
