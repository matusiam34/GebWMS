<?php



/*

	Error code for the script!
	Script code		=	105

 
	//	Action code breakdown
	0	:	Get A categories!
	1	:	Get B categories!
	2	:	Get one category A entry data!
	3	:	Get one Category B entry data!

	4	:	Add Category A!

	5	:	Add Category B!


*/



// load the login class
require_once('lib_login.php');


$message_id		=	105999;		//	999:	default bad
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
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');



		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number


		//	Get category A. The action code for this is 0
		if
		(
			($action_code == 0)		//	Get all category A in HTML table form.
		
			OR
		
			($action_code == 1)		//	Get all category B in HTML table form.
		)
		{

				$cata_uid	=	0;

				$sql	=	'


						SELECT

						*

						FROM

						geb_category
						
						WHERE
				';


				if ($action_code == 0)
				{
					$sql	.=	'	cat_a = 1	';					//	category A
				}
				else if ($action_code == 1)
				{
					$cata_uid	=	leave_numbers_only($_POST['cata_uid_js']);				// this should be a number
					$sql		.=	'	cat_a = 0	AND		cat_b	=	:scata_uid	';		//	category B
				}


				$sql	.=	'	ORDER BY cat_disabled ASC, cat_name';


				if ($stmt = $db->prepare($sql))
				{

					
					if ($action_code == 1)
					{
						$stmt->bindValue(':scata_uid',	$cata_uid,		PDO::PARAM_INT);
					}


					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results[]	=	$row;
					}

					if
					(
						($action_code == 0)		//	Get all category A in HTML table form.
					
						OR
					
						($action_code == 1)		//	Get all category B in HTML table form.
					)
					{

						foreach ($data_results as $item)
						{
							$color_code	=	'';
							
							$disabled	=	leave_numbers_only($item['cat_disabled']);
							
							if ($disabled == 1)	{		$color_code		=	'red_class';	}
							
							$html_results	.=	'<tr>';
								$html_results	.=	'<td class="' . $color_code . '">' . leave_numbers_only($item['cat_pkey']) . '</td>';
								$html_results	.=	'<td class="' . $color_code . '">' . trim($item['cat_name']) . '</td>';
							$html_results	.=	'</tr>';
						}

					}

					$message_id		=	0;	//	all went well

				}


		}	//	Action 0 and 1 end!



		//	Get details of category!!
		else if 
		(
			($action_code == 2)	//	Get category A details
		
			OR
		
			($action_code == 3)	//	Get category B details
		)
		{


			//	Get the UID of the category entry.
			$cat_uid	=	leave_numbers_only($_POST['cat_uid_js']);	//	this should be a number

			$sql	=	'


					SELECT

					*
					
					FROM

					geb_category

					WHERE

					cat_pkey = :scat_uid

			';


			if ($stmt = $db->prepare($sql))
			{


				$stmt->bindValue(':scat_uid',		$cat_uid,		PDO::PARAM_INT);
				$stmt->execute();


				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// drop it into the final array...
					$data_results	=	$row;
				}
					$message2op		=	$cat_uid;

				$message_id		=	0;	//	all went well
			}




			
		}	//	Action 2 and 3 end!



		//	Add category B!

		else if ($action_code == 4)
		{

			//	Only an Admin of this system can add!
			if
			(

				(
					is_it_enabled($_SESSION['menu_adm_category'])
				)

				AND

				(
					can_user_add($_SESSION['menu_adm_category'])
				)

			)
			{

				$cata_name			=	trim($_POST['cata_name_js']);	//	has to have a value
				$cata_status		=	leave_numbers_only($_POST['cata_status_js']);	// this should be a number and has to be a value!

				if (strlen($cata_name) >= 1)	//	I am allowing the name of the category to be 1 character long!
				{


					$db->beginTransaction();


					//	Check if an entry with the same name already exists or not. If so = notify the operator about it!

					$found_a_match	=	false;


					//
					// Seek out for duplicate entry !
					//
					$sql	=	'

						SELECT

						*

						FROM  geb_category

						WHERE

						cat_name = :scata_name

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':scata_name',	$cata_name,	PDO::PARAM_STR);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$found_a_match	=	true;
						}

					}
					// show an error if the query has an error?
					else
					{
					}


					if (!$found_a_match)
					{

						$sql	=	'


								INSERT
								
								INTO

								geb_category
								
								(
									cat_name,
									cat_a,
									cat_disabled
								) 

								VALUES

								(
									:icat_name,
									:icat_a,
									:icat_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':icat_name',			$cata_name,			PDO::PARAM_STR);
							$stmt->bindValue(':icat_a',				1,					PDO::PARAM_INT);
							$stmt->bindValue(':icat_disabled',		$cata_status,		PDO::PARAM_INT);
							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}

					}
					else
					{
						$message_id		=	101200;
						$message2op		=	$mylang['category_already_exists'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	101201;
					$message2op		=	$mylang['name_to_short'];
				}



			}
			
		}	//	Action 4 end!



		//	Add category B!

		else if ($action_code == 5)
		{

			//	Only an Admin of this system can add!
			if
			(

				(
					is_it_enabled($_SESSION['menu_adm_category'])
				)

				AND

				(
					can_user_add($_SESSION['menu_adm_category'])
				)

			)
			{

				$cata_uid			=	leave_numbers_only($_POST['cata_uid_js']);	// this should be a number and has to be a value!
				$catb_name			=	trim($_POST['catb_name_js']);	//	has to have a value
				$catb_status		=	leave_numbers_only($_POST['catb_status_js']);	// this should be a number and has to be a value!

				if (strlen($catb_name) >= 1)	//	I am allowing the name of the category to be 1 character long!
				{

					
					if
					(
						($cata_uid > 0)	//	the operator needs to select a category A first!
					
						AND

						(is_numeric($cata_uid) == true)
					
					)
					{

						$db->beginTransaction();


						//	Check if an entry with the same name already exists or not. If so = notify the operator about it!

						$found_a_match	=	false;


						//
						// Seek out for duplicate entry !
						//
						$sql	=	'

							SELECT

							*

							FROM  geb_category

							WHERE

							cat_b = :scata_uid
							
							AND
							
							cat_name = :scatb_name

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':scata_uid',	$cata_uid,	PDO::PARAM_INT);
							$stmt->bindValue(':scatb_name',	$catb_name,	PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$found_a_match	=	true;
							}

						}
						// show an error if the query has an error?
						else
						{
						}


						if (!$found_a_match)
						{

							$sql	=	'


									INSERT
									
									INTO

									geb_category
									
									(
										cat_name,
										cat_b,
										cat_disabled
									) 

									VALUES

									(
										:icat_name,
										:icat_b,
										:icat_disabled
									)

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':icat_name',			$catb_name,			PDO::PARAM_STR);
								$stmt->bindValue(':icat_b',				$cata_uid,			PDO::PARAM_INT);
								$stmt->bindValue(':icat_disabled',		$catb_status,		PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}

						}
						else
						{
							$message_id		=	101200;
							$message2op		=	$mylang['category_already_exists'];
						}


					}
					else
					{
						// Category A UID is 0... No go!
						$message_id		=	101201;
						$message2op		=	$mylang['select_category_first'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	101201;
					$message2op		=	$mylang['name_to_short'];
				}



			}
			
		}	//	Action 5 end!


		//	Update one warehouse!

		else if ($action_code == 7)
		{

			//	Only an Admin of this system can update a group!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_category']))

				AND

				(can_user_update($_SESSION['menu_adm_category']))

			)
			{

				$warehouse_uid			=	leave_numbers_only($_POST['warehouse_uid_js']);	// this should be a number
				$warehouse_name			=	trim($_POST['warehouse_name_js']);	//	has to have a value
				$warehouse_description	=	trim($_POST['warehouse_description_js']);	//	optional
				$warehouse_status		=	leave_numbers_only($_POST['warehouse_status_js']);	// this should be a number and has to be a value!


				if ($warehouse_uid >= 0)
				{

					if (strlen($warehouse_name) >= 1)	//	I am allowing the name of the warehouse to be 1 character long! Maybe they have WH A,B and C
					{


						//	Here check if the name already maybe exists. If so ==>> notify the user!
						$match_uid	=	$warehouse_uid;

						$sql	=	'

							SELECT

							*

							FROM  geb_warehouse

							WHERE

							wh_code = :iwarehouse_name

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':iwarehouse_name',	$warehouse_name,	PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$match_uid	=	leave_numbers_only($row['wh_pkey']);
							}

						}
						// show an error if the query has an error?
						else
						{
						}



						if ($match_uid == $warehouse_uid)	//	hack	Maybe duplicates can be found in a more elegant way. DNC!
						{

							$db->beginTransaction();

							$sql	=	'

									UPDATE

									geb_warehouse

									SET

									wh_code		=	:uwh_code,
									wh_desc		=	:uwh_desc,
									wh_disabled	=	:uwh_disabled

									WHERE

									wh_pkey	 =	:uwh_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':uwh_code',			$warehouse_name,			PDO::PARAM_STR);
								$stmt->bindValue(':uwh_desc',			$warehouse_description,		PDO::PARAM_STR);
								$stmt->bindValue(':uwh_disabled',		$warehouse_status,			PDO::PARAM_INT);
								$stmt->bindValue(':uwh_pkey',			$warehouse_uid,				PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{
							$message_id		=	101202;
							$message2op		=	$mylang['warehouse_already_exists'];
						}


					}
					else
					{
						$message_id		=	101203;
						$message2op		=	$mylang['name_to_short'];
					}

				}
				else
				{
					$message_id		=	101204;
					$message2op		=	$mylang['incorrect_uid'];
				}


			}
			
		}	//	Action 4 end!







	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	101666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all category A
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Grab all category B that corresponds to category A
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 2:	//	Get one Category A entry data
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 3:	//	Get one Category B entry data
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 4:	//	Add Category A!
		print_message($message_id, $message2op);
		break;
		case 5:	//	Add Category B!
		print_message($message_id, $message2op);
		break;


		case 20:	//	Get all active warehouses!
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		default:
		print_message(101945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
