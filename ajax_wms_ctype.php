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


		//	Get all UOM!
		//	Action code 10 is for the UOM Admin page
		//	Also, action code 11 will get ONLY ACTIVE UOMs in an array format (if required)!
		if
		(
			($action_code == 10)		//	Get all UOMs in HTML table form.

			OR

			($action_code == 11)		//	All active UOMs in an array format!
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



		//	Get details of one UOM!
		else if ($action_code == 12)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_uom'])
			)
			{

				//	Get the UID of the uom hopefully provided from the frontend.
				$uom_uid	=	leave_numbers_only($_POST['uom_uid_js']);	//	this should be a number

				$sql	=	'


						SELECT

						*
						
						FROM

						wms_uom

						WHERE

						uom_pkey = :suom_uid

						AND

						uom_owner = :suom_owner

				';



				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':suom_uid',		$uom_uid,			PDO::PARAM_INT);
					$stmt->bindValue(':suom_owner',		$user_company_uid,	PDO::PARAM_INT);
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


		//	Add one UOM (not PackageUNITs)!!
		else if ($action_code == 15)
		{

			//	Only an Admin of this system can add!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_uom']))

				AND

				(can_user_add($_SESSION['menu_adm_uom']))

			)
			{


				$uom_name				=	trim($_POST['uom_name_js']);
				$uom_description		=	trim($_POST['uom_description_js']);
				$uom_measurement_type	=	leave_numbers_only($_POST['uom_measurement_type_js']);	//	totally a number!
				$uom_conv_factor		=	trim($_POST['uom_conv_factor_js']);						//	real number !
				$uom_status				=	leave_numbers_only($_POST['uom_status_js']);			//	number!



				if (strlen($uom_name) >= 1)	//	I am allowing the name to be 1 character long!
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

						wms_uom

						WHERE

						uom_code = :suom_code

						AND

						uom_owner = :suom_owner

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':suom_code',		$uom_name,			PDO::PARAM_STR);
						$stmt->bindValue(':suom_owner',		$user_company_uid,	PDO::PARAM_INT);

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

								wms_uom
								
								(
									uom_owner,
									uom_code,
									uom_description,
									uom_type,
									uom_conv_factor,
									uom_disabled
								) 

								VALUES

								(
									:iuom_owner,
									:iuom_code,
									:iuom_description,
									:iuom_type,
									:iuom_conv_factor,
									:iuom_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':iuom_owner',				$user_company_uid,		PDO::PARAM_INT);
							$stmt->bindValue(':iuom_code',				$uom_name,				PDO::PARAM_STR);
							$stmt->bindValue(':iuom_description',		$uom_description,		PDO::PARAM_STR);
							$stmt->bindValue(':iuom_type',				$uom_measurement_type,	PDO::PARAM_INT);
							$stmt->bindValue(':iuom_conv_factor',		$uom_conv_factor,		PDO::PARAM_STR);
							$stmt->bindValue(':iuom_disabled',			$uom_status,			PDO::PARAM_INT);

							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}


					}
					else
					{
						$message_id		=	101200;
						$message2op		=	$mylang['uom_already_exists'];
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



		//	Update one UOM entry!
		else if ($action_code == 17)
		{

			//	Only an Admin of this system can update a group!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_uom']))

				AND

				(can_user_update($_SESSION['menu_adm_uom']))

			)
			{

				$uom_uid				=	leave_numbers_only($_POST['uom_uid_js']);				//	totally a number!
				$uom_name				=	trim($_POST['uom_name_js']);
				$uom_description		=	trim($_POST['uom_description_js']);
				$uom_measurement_type	=	leave_numbers_only($_POST['uom_measurement_type_js']);	//	totally a number!
				$uom_conv_factor		=	trim($_POST['uom_conv_factor_js']);						//	real number !
				$uom_status				=	leave_numbers_only($_POST['uom_status_js']);			//	number!


				if ($uom_uid >= 0)
				{

					if (strlen($uom_name) >= 1)	//	I am allowing the name of the UOM to be 1 character long! L? G? K? etc
					{


						//	Check 
						$match_uid	=	$uom_uid;

						$sql	=	'

							SELECT

							*

							FROM  wms_uom

							WHERE

							uom_code = :suom_name

							AND

							uom_owner = :suom_owner

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':suom_name',	$uom_name,			PDO::PARAM_STR);
							$stmt->bindValue(':suom_owner',	$user_company_uid,	PDO::PARAM_INT);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$match_uid	=	leave_numbers_only($row['uom_pkey']);
							}

						}
						// show an error if the query has an error?
						else
						{
						}



						if ($match_uid == $uom_uid)	//	hack	Maybe duplicates can be found in a more elegant way. DNC!
						{


							$db->beginTransaction();

							$sql	=	'

									UPDATE

									wms_uom

									SET

									uom_code			=	:uuom_code,
									uom_description		=	:uuom_description,
									uom_type			=	:uuom_type,
									uom_conv_factor		=	:uuom_conv_factor,
									uom_disabled		=	:uuom_disabled

									WHERE

									uom_pkey			=	:uuom_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':uuom_code',				$uom_name,				PDO::PARAM_STR);
								$stmt->bindValue(':uuom_description',		$uom_description,		PDO::PARAM_STR);
								$stmt->bindValue(':uuom_type',				$uom_measurement_type,	PDO::PARAM_INT);
								$stmt->bindValue(':uuom_conv_factor',		$uom_conv_factor,		PDO::PARAM_STR);
								$stmt->bindValue(':uuom_disabled',			$uom_status,			PDO::PARAM_INT);
								$stmt->bindValue(':uuom_pkey',				$uom_uid,				PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{
							$message_id		=	101202;
							$message2op		=	$mylang['uom_already_exists'];
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



		//	Get all Package Units!
		//	Action code 20 is for the Package Unit Admin page
		//	Also, action code 21 will get ONLY ACTIVE Package Units in an array format (if required)!
		elseif
		(
			($action_code == 20)		//	Get all PU in HTML table form.

			OR

			($action_code == 21)		//	All active PU in an array format!
		)
		{


				$sql	=	'


						SELECT

						*

						FROM

						wms_pack_unit


				';

				if ($action_code == 20)	//	All PUs!
				{
					$sql	.=	'	WHERE pu_owner = :spu_owner';
				}
				elseif ($action_code == 21)	//	Active PUs ONLY!
				{
					$sql	.=	'	WHERE pu_disabled = 0 AND pu_owner = :spu_owner';
				}


				$sql	.=	' ORDER BY pu_disabled ASC, pu_code';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':spu_owner',	$user_company_uid,	PDO::PARAM_INT);
					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results[]	=	$row;
					}


					if ($action_code == 20)
					{

						foreach ($data_results as $item)
						{
							$color_code	=	'';
							
							$disabled	=	leave_numbers_only($item['pu_disabled']);
							
							if ($disabled == 1)	{		$color_code		=	'red_class';	}
							
							$html_results	.=	'<tr data-id="' . leave_numbers_only($item['pu_pkey']) . '" class="' . $color_code . '" >';
								$html_results	.=	'<td>' . trim($item['pu_code']) . '</td>';
							$html_results	.=	'</tr>';
						}

					}


					$message_id		=	0;	//	all went well

				}



		}	//	Action 20 and 21 end!



		//	Get details of one Package Unit!
		else if ($action_code == 22)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_uom'])
			)
			{

				//	Get the UID of the Package Unit hopefully provided from the frontend.
				$pu_uid	=	leave_numbers_only($_POST['pu_uid_js']);	//	this should be a number

				$sql	=	'


						SELECT

						*
						
						FROM

						wms_pack_unit

						WHERE

						pu_pkey = :spu_uid

						AND

						pu_owner = :spu_owner

				';



				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':spu_uid',	$pu_uid,			PDO::PARAM_INT);
					$stmt->bindValue(':spu_owner',	$user_company_uid,	PDO::PARAM_INT);

					$stmt->execute();


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results	=	$row;
					}

					$message_id		=	0;	//	all went well
				}

			}

		}	//	Action 22 end!


		//	Add one Package UNIT
		else if ($action_code == 25)
		{

			//	Only an Admin of this system can add!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_uom']))

				AND

				(can_user_add($_SESSION['menu_adm_uom']))

			)
			{


				$pu_name			=	trim($_POST['pu_name_js']);
				$pu_description		=	trim($_POST['pu_description_js']);
				$pu_uom				=	leave_numbers_only($_POST['pu_uom_js']);		//	totally a number!
				$pu_qty				=	trim($_POST['pu_qty_js']);						//	real number !
				$pu_status			=	leave_numbers_only($_POST['pu_status_js']);		//	number!


				if (strlen($pu_name) >= 1)	//	I am allowing the name to be 1 character long!
				{

					$db->beginTransaction();


					//	Check if an entry with the same name already exists or not. If so = notify the operator about it!

					$found_a_match	=	false;


					//
					//	Seek out for duplicate entry !
					//
					$sql	=	'

						SELECT

						*
						
						FROM

						wms_pack_unit

						WHERE

						pu_code = :spu_code

						AND

						pu_owner = :spu_owner

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':spu_code',		$pu_name,			PDO::PARAM_STR);
						$stmt->bindValue(':spu_owner',		$user_company_uid,	PDO::PARAM_INT);

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

								wms_pack_unit
								
								(
									pu_owner,
									pu_code,
									pu_description,
									pu_uom_pkey,
									pu_qty,
									pu_disabled
								) 

								VALUES

								(
									:ipu_owner,
									:ipu_code,
									:ipu_description,
									:ipu_uom_pkey,
									:ipu_qty,
									:ipu_disabled
								)

						';



						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':ipu_owner',				$user_company_uid,		PDO::PARAM_INT);
							$stmt->bindValue(':ipu_code',				$pu_name,				PDO::PARAM_STR);
							$stmt->bindValue(':ipu_description',		$pu_description,		PDO::PARAM_STR);
							$stmt->bindValue(':ipu_uom_pkey',			$pu_uom,				PDO::PARAM_INT);
							$stmt->bindValue(':ipu_qty',				$pu_qty,				PDO::PARAM_STR);
							$stmt->bindValue(':ipu_disabled',			$pu_status,				PDO::PARAM_INT);


							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}


					}
					else
					{
						$message_id		=	101200;
						$message2op		=	$mylang['package_unit_already_exists'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	101201;
					$message2op		=	$mylang['name_too_short'];
				}



			}
			
		}	//	Action 25 end!




		//	Update one Package UNIT entry!
		else if ($action_code == 27)
		{

			//	Only an Admin of this system can update a group!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_uom']))

				AND

				(can_user_update($_SESSION['menu_adm_uom']))

			)
			{

				$pu_uid				=	leave_numbers_only($_POST['pu_uid_js']);		//	totally a number!
				$pu_name			=	trim($_POST['pu_name_js']);
				$pu_description		=	trim($_POST['pu_description_js']);
				$pu_uom				=	leave_numbers_only($_POST['pu_uom_js']);		//	totally a number!
				$pu_qty				=	trim($_POST['pu_qty_js']);						//	real number !
				$pu_status			=	leave_numbers_only($_POST['pu_status_js']);		//	number!


				if ($pu_uid >= 0)
				{

					if (strlen($pu_name) >= 1)	//	I am allowing the name of the UOM to be 1 character long! L? G? K?
					{


						//	Check 
						$match_uid	=	$pu_uid;

						$sql	=	'

							SELECT

							*

							FROM  wms_pack_unit

							WHERE

							pu_code = :spu_name

							AND

							pu_owner = :spu_owner

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':spu_name',	$pu_name,				PDO::PARAM_STR);
							$stmt->bindValue(':spu_owner',	$user_company_uid,		PDO::PARAM_INT);

							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$match_uid	=	leave_numbers_only($row['pu_pkey']);
							}

						}
						// show an error if the query has an error?
						else
						{
						}



						if ($match_uid == $pu_uid)	//	found a matching entry with the UID the user provided in the matching company!
						{


							$db->beginTransaction();

							$sql	=	'

									UPDATE

									wms_pack_unit

									SET

									pu_code			=	:upu_code,
									pu_description	=	:upu_description,
									pu_uom_pkey		=	:upu_uom,
									pu_qty			=	:upu_qty,
									pu_disabled		=	:upu_disabled

									WHERE

									pu_pkey			=	:upu_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':upu_code',				$pu_name,			PDO::PARAM_STR);
								$stmt->bindValue(':upu_description',		$pu_description,	PDO::PARAM_STR);
								$stmt->bindValue(':upu_uom',				$pu_uom,			PDO::PARAM_INT);
								$stmt->bindValue(':upu_qty',				$pu_qty,			PDO::PARAM_STR);
								$stmt->bindValue(':upu_disabled',			$pu_status,			PDO::PARAM_INT);
								$stmt->bindValue(':upu_pkey',				$pu_uid,			PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{
							//	No matching entry found...
							$message_id		=	101202;
							$message2op		=	$mylang['package_unit_already_exists'];
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
		case 10:	//	Grab all UOMs
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 11:	//	All active UOMs in an array format!
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 12:	//	Get one UOM details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 15:	//	Add UOM to the system
		print_message($message_id, $message2op);
		break;
		case 17:	//	Update UOM details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;

		//	All codes for the Package Unit page!

		case 20:	//	Grab all Package Units in HTML
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 21:	//	Get one Package Unit details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 22:	//	Get one Package Unit details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 25:	//	Add Package UNIT to the system
		print_message($message_id, $message2op);
		break;
		case 27:	//	Update Package UNIT details
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
