<?php


//	NOTE: save (action 3) needs to run a SELECT on the warehouse code to see if there is one already with that name before updating!


/*

	Error code for the script!
	Script code		=	101

 
	//	Action code breakdown
	0	:	Get all warehouses (HTML, for a table).
	1	:	Get one warehouse info (array).
	2	:	Add warehouse!
	3	:	Update warehouse details!

 
	20	:	Get all warehouses that are active. Output: array!




*/



// load the login class
require_once('lib_login.php');


$message_id		=	101999;		//	999:	default bad
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


		//	Get all warehouses. The action code for this is 0
		if
		(
			($action_code == 0)		//	Get all warehouses in HTML table form.

			OR

			($action_code == 20)	//	All active warehouses in an array format!
		)
		{


/*
			if
			(
				is_it_enabled($_SESSION['menu_adm_warehouse'])
			)
			{
*/

				$sql	=	'


						SELECT

						*

						FROM

						geb_warehouse


				';

				if ($action_code == 20)
				{
					$sql	.=	'	WHERE wh_disabled = 0';
				}

				$sql	.=	'	ORDER BY wh_disabled ASC, wh_code';




				if ($stmt = $db->prepare($sql))
				{

					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results[]	=	$row;
					}


					if ($action_code == 0)
					{

						foreach ($data_results as $item)
						{
							$color_code	=	'';
							
							$disabled	=	leave_numbers_only($item['wh_disabled']);
							
							if ($disabled == 1)	{		$color_code		=	'red_class';	}
							
							$html_results	.=	'<tr>';
								$html_results	.=	'<td class="' . $color_code . '">' . leave_numbers_only($item['wh_pkey']) . '</td>';
								$html_results	.=	'<td class="' . $color_code . '">' . trim($item['wh_code']) . '</td>';
							$html_results	.=	'</tr>';
						}

					}



					$message_id		=	0;	//	all went well

				}


//			}

		}	//	Action 0 end!


		//	Get details of one!

		else if ($action_code == 1)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_warehouse'])
			)
			{

				//	Get the UID of the warehouse hopefully provided from the frontend.
				$wareouse_uid	=	leave_numbers_only($_POST['warehouse_uid_js']);	//	this should be a number

				$sql	=	'


						SELECT

						*
						
						FROM

						geb_warehouse

						WHERE

						wh_pkey = :swarehouse_uid

				';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':swarehouse_uid',			$wareouse_uid,		PDO::PARAM_INT);
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

				(is_it_enabled($_SESSION['menu_adm_warehouse']))

				AND

				(can_user_add($_SESSION['menu_adm_warehouse']))

			)
			{

				$warehouse_name			=	trim($_POST['warehouse_name_js']);	//	has to have a value
				$warehouse_description	=	trim($_POST['warehouse_description_js']);	//	optional
				$warehouse_status		=	leave_numbers_only($_POST['warehouse_status_js']);	// this should be a number and has to be a value!

				if (strlen($warehouse_name) >= 1)	//	I am allowing the name of the warehouse to be 1 character long! Maybe they have WH A,B and C
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

								geb_warehouse
								
								(
									wh_code,
									wh_desc,
									wh_disabled
								) 

								VALUES

								(
									:iwh_code,
									:iwh_desc,
									:iwh_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':iwh_code',				$warehouse_name,			PDO::PARAM_STR);
							$stmt->bindValue(':iwh_desc',				$warehouse_description,		PDO::PARAM_STR);
							$stmt->bindValue(':iwh_disabled',			$warehouse_status,			PDO::PARAM_INT);
							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}

					}
					else
					{
						$message_id		=	101200;
						$message2op		=	$mylang['warehouse_already_exists'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	101201;
					$message2op		=	$mylang['name_to_short'];
				}



			}
			
		}	//	Action 2 end!


		//	Update one warehouse!

		else if ($action_code == 3)
		{

			//	Only an Admin of this system can update a group!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_warehouse']))

				AND

				(can_user_update($_SESSION['menu_adm_warehouse']))

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
			
		}	//	Action 3 end!







	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	101666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all warehouses
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Get one warehouse details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 2:	//	Add Warehouse
		print_message($message_id, $message2op);
		break;
		case 3:	//	Update Warehouse
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
