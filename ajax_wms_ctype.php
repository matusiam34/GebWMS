<?php




/*

	Error code for the script!
	Script code		=	191

	//	ALL OF THIS NEEDS UPDATEING AT SOME POINT


	//	Action code breakdown

	0	:	Get all container types (HTML, for a table).
	1	:	Get one container type info (array).
	2	:	Add container type!
	3	:	Update container type details!

 
	20	:	Get all container types that are active. Output: array!



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


		//	Get all Container Types!
		//	Action code 10 is for the Container Type Admin page
		//	Also, action code 11 will get ONLY ACTIVE Container Types in an array format (if required)!
		if
		(
			($action_code == 10)		//	Get all Container Types in HTML table form.

			OR

			($action_code == 11)		//	All active Container Types in an array format!
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

						wms_container_type


				';

				if ($action_code == 10)	//	All UOMs!
				{
					$sql	.=	'	WHERE ctype_owner = :sctype_owner';
				}
				elseif ($action_code == 11)	//	Active container types ONLY!
				{
					$sql	.=	'	WHERE ctype_disabled = 0 AND ctype_owner = :sctype_owner';
				}



				$sql	.=	' ORDER BY ctype_disabled ASC, ctype_code';



				if ($stmt = $db->prepare($sql))
				{

					$stmt->bindValue(':sctype_owner',	$user_company_uid,	PDO::PARAM_INT);
					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results[]	=	$row;
					}


					if ($action_code == 10)
					{

						foreach ($data_results as $item)
						{
							$color_code	=	'';
							
							$disabled	=	leave_numbers_only($item['ctype_disabled']);
							
							if ($disabled == 1)	{		$color_code		=	'red_class';	}
							
							$html_results	.=	'<tr data-id="' . leave_numbers_only($item['ctype_pkey']) . '" class="' . $color_code . '" >';
								$html_results	.=	'<td>' . trim($item['ctype_code']) . '</td>';
							$html_results	.=	'</tr>';
						}

					}

					$message_id		=	0;	//	all went well

				}


//			}

		}	//	Action 10 and 11 end!



		//	Get details of one Container Type!
		else if ($action_code == 12)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_container_type'])
			)
			{

				//	Get the UID of the container type hopefully provided from the frontend.
				$ctype_uid	=	leave_numbers_only($_POST['ctype_uid_js']);	//	this should be a number

				$sql	=	'


						SELECT

						*
						
						FROM

						wms_container_type

						WHERE

						ctype_pkey = :sctype_uid

						AND

						ctype_owner = :sctype_owner

				';



				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':sctype_uid',		$ctype_uid,			PDO::PARAM_INT);
					$stmt->bindValue(':sctype_owner',	$user_company_uid,	PDO::PARAM_INT);
					$stmt->execute();


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results	=	$row;
					}

					$message_id		=	0;	//	all went well
				}


			}
			
		}	//	Action 12 end!


		//	Add one container type
		else if ($action_code == 15)
		{

			//	Only an Admin of this system can add!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_container_type']))

				AND

				(can_user_add($_SESSION['menu_adm_container_type']))

			)
			{

				$ctype_name				=	trim($_POST['ctype_name_js']);
				$ctype_prefix			=	trim($_POST['ctype_prefix_js']);
				$ctype_description		=	trim($_POST['ctype_description_js']);
				$ctype_status			=	leave_numbers_only($_POST['ctype_status_js']);			//	number!



				if (strlen($ctype_name) >= 1)	//	I am allowing the name to be 1 character long!
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
						
						FROM

						wms_container_type

						WHERE

						ctype_code = :sctype_code

						AND

						ctype_owner = :sctype_owner

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sctype_code',	$ctype_name,			PDO::PARAM_STR);
						$stmt->bindValue(':sctype_owner',	$user_company_uid,		PDO::PARAM_INT);

						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$found_a_match	=	true;
						}

					}
					// show an error if the query has an error?
					else
					{
						//	in the future...
					}


					if (!$found_a_match)
					{

						$sql	=	'


								INSERT
								
								INTO

								wms_container_type
								
								(
									ctype_owner,
									ctype_code,
									ctype_prefix,
									ctype_description,
									ctype_disabled
								) 

								VALUES

								(
									:ictype_owner,
									:ictype_code,
									:ictype_prefix,
									:ictype_description,
									:ictype_disabled

								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':ictype_owner',			$user_company_uid,		PDO::PARAM_INT);
							$stmt->bindValue(':ictype_code',			$ctype_name,			PDO::PARAM_STR);
							$stmt->bindValue(':ictype_prefix',			$ctype_prefix,			PDO::PARAM_STR);
							$stmt->bindValue(':ictype_description',		$ctype_description,		PDO::PARAM_STR);
							$stmt->bindValue(':ictype_disabled',		$ctype_status,			PDO::PARAM_INT);

							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}


					}
					else
					{
						$message_id		=	101200;
						$message2op		=	$mylang['container_type_already_exists'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	101201;
					$message2op		=	$mylang['name_too_short'];
				}



			}
			
		}	//	Action 15 end!



		//	Update one Container Type entry!
		else if ($action_code == 17)
		{

			//	Only an Admin of this system can update a container type!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_container_type']))

				AND

				(can_user_update($_SESSION['menu_adm_container_type']))

			)
			{

				$ctype_uid				=	leave_numbers_only($_POST['ctype_uid_js']);				//	totally a number!
				$ctype_name				=	trim($_POST['ctype_name_js']);
				$ctype_prefix			=	trim($_POST['ctype_prefix_js']);
				$ctype_description		=	trim($_POST['ctype_description_js']);
				$ctype_status			=	leave_numbers_only($_POST['ctype_status_js']);			//	number!


				if ($ctype_uid >= 0)
				{

					if (strlen($ctype_name) >= 1)	//	I am allowing the name of the UOM to be 1 character long! L? G? K? etc
					{


						//	Check 
						$match_uid	=	$ctype_uid;

						$sql	=	'

							SELECT

							*

							FROM  wms_container_type

							WHERE

							ctype_code = :sctype_name

							AND

							ctype_owner = :sctype_owner

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sctype_name',	$ctype_name,		PDO::PARAM_STR);
							$stmt->bindValue(':sctype_owner',	$user_company_uid,	PDO::PARAM_INT);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$match_uid	=	leave_numbers_only($row['ctype_pkey']);
							}

						}
						// show an error if the query has an error?
						else
						{
						}



						if ($match_uid == $ctype_uid)	//	hack	Maybe duplicates can be found in a more elegant way. DNC!
						{


							$db->beginTransaction();

							$sql	=	'

									UPDATE

									wms_container_type

									SET

									ctype_code			=	:uctype_code,
									ctype_prefix		=	:uctype_prefix,
									ctype_description	=	:uctype_description,
									ctype_disabled		=	:uctype_disabled

									WHERE

									ctype_pkey			=	:uctype_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':uctype_code',			$ctype_name,			PDO::PARAM_STR);
								$stmt->bindValue(':uctype_prefix',			$ctype_prefix,			PDO::PARAM_STR);
								$stmt->bindValue(':uctype_description',		$ctype_description,		PDO::PARAM_STR);
								$stmt->bindValue(':uctype_disabled',		$ctype_status,			PDO::PARAM_INT);
								$stmt->bindValue(':uctype_pkey',			$ctype_uid,				PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}



						}
						else
						{
							$message_id		=	101202;
							$message2op		=	$mylang['container_type_already_exists'];
						}


					}
					else
					{
						$message_id		=	101203;
						$message2op		=	$mylang['name_too_short'];
					}

				}
				else
				{
					$message_id		=	101204;
					$message2op		=	$mylang['incorrect_uid'];
				}


			}
			
		}	//	Action 17 end!






	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	101666;
	}


	$db	=	null;


	switch ($action_code)
	{
		case 10:	//	Grab all Container types
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 11:	//	All active Container types in an array format!
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 12:	//	Get one Container type details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 15:	//	Add container type to the system
		print_message($message_id, $message2op);
		break;
		case 17:	//	Update container type details
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
