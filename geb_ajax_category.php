<?php



/*

	Error code for the script!
	Script code		=	105

 
	//	Action code breakdown

	//	Special ones as I can specify if they are HTML or just an array by providing an additional variable called action_format
	//	Format	:	0	(HTML) Default option!
	//	Format	:	1	(Array)

	0	:	Get A categories!	+	action_format = 0: HTML; 1:  Array
	1	:	Get B categories!	+	action_format = 0: HTML; 1:  Array
	2	:	Get C categories!	+	action_format = 0: HTML; 1:  Array
	3	:	Get D categories!	+	action_format = 0: HTML; 1:  Array


	5	:	Get one category A entry data!
	6	:	Get one Category B entry data!
	7	:	Get one Category C entry data!
	8	:	Get one Category D entry data!


	10	:	Add Category A!
	11	:	Add Category B!
	12	:	Add Category C!
	13	:	Add Category D!


	15	:	Update Category A!
	16	:	Update Category B!
	17	:	Update Category C!
	18	:	Update Category D!


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
		require_once('lib_system.php');
		require_once('lib_db_conn.php');


		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number
		$action_format		=	0;	//	by default provide HTML for actions that have the ability to provide different formats.
		$action_disabled	=	1;	//	by default provide EVERYTHING!



		//	*******************************************************************************************************
		//
		//	Ohhh... such an ugly solution right here! FIX
		//
		$company_uid_js		=	0;

		if (isset($_POST["company_uid_js"]))
		{
			$company_uid_js		=	leave_numbers_only($_POST['company_uid_js']);	// this should be a number
		}

		if (($user_company_uid == 0) AND ($company_uid_js > 0))
		{
			$user_company_uid	=	$company_uid_js;
		}

		//
		//	*******************************************************************************************************




		//	if it is set grab it!
		if (isset($_POST["action_format_js"]))
		{
			$action_format		=	leave_numbers_only($_POST['action_format_js']);			// this should be a number
		}

		//	if it is set grab it!
		if (isset($_POST["action_disabled_js"]))
		{
			$action_disabled		=	leave_numbers_only($_POST['action_disabled_js']);	// this should be a number
		}




		//	Get categories. The action code for this is 0
		if
		(
			($action_code == 0)		//	Get all category A in HTML table form (action_format by default is 0)
		
			OR
		
			($action_code == 1)		//	Get all category B in HTML table form (action_format by default is 0)

			OR
		
			($action_code == 2)		//	Get all category C in HTML table form (action_format by default is 0)

			OR
		
			($action_code == 3)		//	Get all category D in HTML table form (action_format by default is 0)

		)
		{

				$which_cat	=	'a';
				if		($action_code == 1)	{	$which_cat	=	'b';	}
				elseif	($action_code == 2)	{	$which_cat	=	'c';	}
				elseif	($action_code == 3)	{	$which_cat	=	'd';	}
				
				$cat_uid	=	0;

				$sql	=	'


						SELECT

						*

						FROM

						geb_category_' . $which_cat . '

				';


				if ($action_code == 0)
				{
					//	Do not need any criteria by default to get all category A entries!
					//	WHERE statement only needed if disabled flag is = 0... So...
					$sql		.=	'	WHERE		cat_a_owner	=	:scat_owner_uid	';		//	category A owner
				}
				else if ($action_code == 1)
				{
					$cat_uid	=	leave_numbers_only($_POST['cat_uid_js']);				//	this should be a number
					$sql		.=	'	WHERE	cat_b_a_level	=	:scat_uid	';			//	category B
					$sql		.=	'	AND		cat_b_owner		=	:scat_owner_uid	';		//	category B owner

				}
				else if ($action_code == 2)
				{
					$cat_uid	=	leave_numbers_only($_POST['cat_uid_js']);				//	this should be a number
					$sql		.=	'	WHERE	cat_c_b_level	=	:scat_uid	';			//	category C
					$sql		.=	'	AND		cat_c_owner		=	:scat_owner_uid	';		//	category C owner
				}
				else if ($action_code == 3)
				{
					$cat_uid	=	leave_numbers_only($_POST['cat_uid_js']);				//	this should be a number
					$sql		.=	'	WHERE	cat_d_c_level	=	:scat_uid	';			//	category D
					$sql		.=	'	AND		cat_d_owner		=	:scat_owner_uid	';		//	category D owner
				}


				//	Get all categories that are active. Otherwise get EVERYTHING!
				if ($action_disabled == 0)
				{
					$sql	.=	'	AND	cat_' . $which_cat . '_disabled = 0	';
				}


				$sql	.=	'	ORDER BY cat_' . $which_cat . '_disabled ASC, cat_' . $which_cat . '_name';


				if ($stmt = $db->prepare($sql))
				{

					if ($action_code == 0)
					{
						$stmt->bindValue(':scat_owner_uid',	$user_company_uid,		PDO::PARAM_INT);
					}
					elseif
					(
						($action_code == 1) OR ($action_code == 2) OR ($action_code == 3)
					)
					{
						$stmt->bindValue(':scat_uid',		$cat_uid,				PDO::PARAM_INT);
						$stmt->bindValue(':scat_owner_uid',	$user_company_uid,		PDO::PARAM_INT);
					}


					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// drop it into the final array...
						$data_results[]	=	$row;
					}


					//	Only generate the HTML output when action_format is equal to 0!
					if ($action_format == 0)
					{

						foreach ($data_results as $item)
						{
							$color_code	=	'';
							
							$disabled	=	leave_numbers_only($item['cat_' . $which_cat . '_disabled']);
							
							if ($disabled == 1)	{		$color_code		=	'red_class';	}
							
							$html_results	.=	'<tr>';
								$html_results	.=	'<td class="' . $color_code . '">' . leave_numbers_only($item['cat_' . $which_cat . '_pkey']) . '</td>';
								$html_results	.=	'<td class="' . $color_code . '">' . trim($item['cat_' . $which_cat . '_name']) . '</td>';
							$html_results	.=	'</tr>';
						}

					}


					$message_id		=	0;	//	all went well

				}


		}	//	Action 0, 1 and 2 end!	Get all categories!



		//	Get details of a single category!!
		else if 
		(
			($action_code == 5)	//	Get category A details
		
			OR
		
			($action_code == 6)	//	Get category B details

			OR
		
			($action_code == 7)	//	Get category C details

			OR
		
			($action_code == 8)	//	Get category D details
		)
		{

			$which_cat	=	'a';
			if 		($action_code == 6)	{	$which_cat	=	'b';	}
			elseif	($action_code == 7)	{	$which_cat	=	'c';	}
			elseif	($action_code == 8)	{	$which_cat	=	'd';	}

			//	Get the UID of the category entry.
			$cat_uid	=	leave_numbers_only($_POST['cat_uid_js']);	//	this should be a number

			$sql	=	'


					SELECT

					*
					
					FROM

					geb_category_' . $which_cat . '

					WHERE

					cat_' . $which_cat . '_pkey = :scat_uid

					AND
					
					cat_' . $which_cat . '_owner = :scat_owner_uid    

			';


			if ($stmt = $db->prepare($sql))
			{

				$stmt->bindValue(':scat_uid',			$cat_uid,				PDO::PARAM_INT);
				$stmt->bindValue(':scat_owner_uid',		$user_company_uid,		PDO::PARAM_INT);
				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// drop it into the final array...
					$data_results	=	$row;
				}

				$message_id		=	0;	//	all went well
			}




			
		}	//	Action 2 and 3 end!



		//	Add category A!

		else if ($action_code == 10)
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

						FROM  geb_category_a

						WHERE

						cat_a_name = :scata_name
						
						AND
						
						cat_a_owner = :scata_owner

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':scata_name',		$cata_name,			PDO::PARAM_STR);
						$stmt->bindValue(':scata_owner',	$user_company_uid,	PDO::PARAM_INT);
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

								geb_category_a
								
								(
									cat_a_name,
									cat_a_owner,
									cat_a_disabled
								) 

								VALUES

								(
									:icat_a_name,
									:icat_a_owner,
									:icat_a_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':icat_a_name',		$cata_name,			PDO::PARAM_STR);
							$stmt->bindValue(':icat_a_owner',		$user_company_uid,	PDO::PARAM_INT);
							$stmt->bindValue(':icat_a_disabled',	$cata_status,		PDO::PARAM_INT);

							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}

					}
					else
					{
						$message_id		=	105200;
						$message2op		=	$mylang['category_already_exists'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	105201;
					$message2op		=	$mylang['name_too_short'];
				}



			}
			
		}	//	Action 10 end!



		//	Add category B, C and D!

		else if 
		(
			($action_code == 11)	//	Add category B
		
			OR
		
			($action_code == 12)	//	Add category C

			OR
		
			($action_code == 13)	//	Add category D
		)
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

				$which_cat	=	'b';
				$level		=	'b_a';
				if 		($action_code == 12)	{	$which_cat	=	'c';	$level		=	'c_b';	}
				elseif 	($action_code == 13)	{	$which_cat	=	'd';	$level		=	'd_c';	}
				
				
				$cat_master_uid		=	leave_numbers_only($_POST['cat_master_uid_js']);	// this should be a number and has to be a value!
				$cat_name			=	trim($_POST['cat_name_js']);	//	has to have a value
				$cat_status			=	leave_numbers_only($_POST['cat_status_js']);	// this should be a number and has to be a value!

				if (strlen($cat_name) >= 1)	//	I am allowing the name of the category to be 1 character long!
				{

					
					if
					(
						($cat_master_uid > 0)	//	the operator needs to select a master category first!
					
						AND

						(is_numeric($cat_master_uid) == true)
					
					)
					{

						$db->beginTransaction();

						//	Detect duplicates!
						$found_match	=	false;


						//
						// Seek out for duplicate entry !
						//
						$sql	=	'

							SELECT

							*

							FROM  geb_category_' . $which_cat . '

							WHERE

							cat_' . $level . '_level = :scat_master_uid
							
							AND
							
							cat_' . $which_cat . '_name = :scat_name

							AND
							
							cat_' . $which_cat . '_owner = :scat_owner

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':scat_master_uid',	$cat_master_uid,	PDO::PARAM_INT);
							$stmt->bindValue(':scat_name',			$cat_name,			PDO::PARAM_STR);
							$stmt->bindValue(':scat_owner',			$user_company_uid,	PDO::PARAM_INT);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$found_match	=	true;
							}

						}
						// show an error if the query has an error?
						else
						{
						}


						if (!$found_match)
						{

							$sql	=	'


									INSERT
									
									INTO

									geb_category_' . $which_cat . '
									
									(
										cat_' . $which_cat . '_name,
										cat_' . $level . '_level,
										cat_' . $which_cat . '_owner,
										cat_' . $which_cat . '_disabled
									) 

									VALUES

									(
										:icat_' . $which_cat . '_name,
										:icat_' . $level . '_level,
										:icat_' . $which_cat . '_owner,
										:icat_' . $which_cat . '_disabled
									)

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':icat_' . $which_cat . '_name',		$cat_name,			PDO::PARAM_STR);
								$stmt->bindValue(':icat_' . $level . '_level',			$cat_master_uid,	PDO::PARAM_INT);
								$stmt->bindValue(':icat_' . $which_cat . '_owner',		$user_company_uid,	PDO::PARAM_INT);
								$stmt->bindValue(':icat_' . $which_cat . '_disabled',	$cat_status,		PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}

						}
						else
						{
							$message_id		=	105202;
							$message2op		=	$mylang['category_already_exists'];
						}


					}
					else
					{
						// Category A UID is 0... No go!
						$message_id		=	105203;
						$message2op		=	$mylang['select_category_first'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	105204;
					$message2op		=	$mylang['name_too_short'];
				}



			}
			
		}	//	Action 5 end!



		//	Update category!

		else if
		(
			($action_code == 15)		//	Update category A
		
			OR
		
			($action_code == 16)		//	Update category B

			OR
		
			($action_code == 17)		//	Update category C

			OR
		
			($action_code == 18)		//	Update category C
		)


		{

			//	Only an Admin of this system can update a group!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_category']))

				AND

				(can_user_update($_SESSION['menu_adm_category']))

			)
			{


				$cat_uid			=	leave_numbers_only($_POST['cat_uid_js']);	// this should be a number and has to be a value!
				$cat_name			=	trim($_POST['cat_name_js']);	//	has to have a value
				$cat_status			=	leave_numbers_only($_POST['cat_status_js']);	// this should be a number and has to be a value!


				if
				(

					($cat_uid > 0)

					AND

					(is_numeric($cat_uid) == true)

				)
				{

					if (strlen($cat_name) >= 1)
					{

						$which_cat	=	'a';
						if 		($action_code == 16)	{	$which_cat	=	'b';	}
						elseif	($action_code == 17)	{	$which_cat	=	'c';	}
						elseif	($action_code == 18)	{	$which_cat	=	'd';	}

						//	Here check if the name already maybe exists. If so ==>> notify the user!
						$found_match	=	0;

						$sql	=	'

							SELECT

							*

							FROM  geb_category_' . $which_cat . '

							WHERE

							cat_' . $which_cat . '_name = :scat_name

							AND
							
							cat_' . $which_cat . '_owner = :scat_owner

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':scat_name',		$cat_name,			PDO::PARAM_STR);
							$stmt->bindValue(':scat_owner',		$user_company_uid,	PDO::PARAM_INT);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{

								if
								(
									($row['cat_' . $which_cat . '_pkey'] <> $cat_uid)

									AND

									(strcmp(trim($row['cat_' . $which_cat . '_name']), $cat_name) === 0)
								)
								{
									$found_match++;
								}

							}

						}
						// show an error if the query has an error?
						else
						{
						}



						if (($found_match	== 0))	//	hack	Maybe duplicates can be found in a more elegant way. DNC!
						{

							$db->beginTransaction();

							$sql	=	'

									UPDATE

									geb_category_' . $which_cat . '

									SET

									cat_' . $which_cat . '_name		=	:ucat_name,
									cat_' . $which_cat . '_owner	=	:ucat_owner,
									cat_' . $which_cat . '_disabled	=	:ucat_disabled

									WHERE

									cat_' . $which_cat . '_pkey	 =	:ucat_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':ucat_name',			$cat_name,			PDO::PARAM_STR);
								$stmt->bindValue(':ucat_owner',			$user_company_uid,	PDO::PARAM_INT);
								$stmt->bindValue(':ucat_disabled',		$cat_status,		PDO::PARAM_INT);
								$stmt->bindValue(':ucat_pkey',			$cat_uid,			PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{
							$message_id		=	105205;
							$message2op		=	$mylang['category_already_exists'];
						}


					}
					else
					{
						$message_id		=	105206;
						$message2op		=	$mylang['name_too_short'];
					}

				}
				else
				{
					$message_id		=	105207;
					$message2op		=	$mylang['incorrect_uid'];
				}


			}
			
		}	//	Action 4 end!







	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	105666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all category A
			if ($action_format == 0)		//	HTML
			{
				print_message_html_payload($message_id, $message2op, $html_results);
			}
			else
				if ($action_format == 1)	//	Array
				{
					print_message_data_payload($message_id, $message2op, $data_results);
				}
		break;

		case 1:	//	Grab all category B that corresponds to category A
			if ($action_format == 0)		//	HTML
			{
				print_message_html_payload($message_id, $message2op, $html_results);
			}
			else
				if ($action_format == 1)	//	Array
				{
					print_message_data_payload($message_id, $message2op, $data_results);
				}
		break;

		case 2:	//	Grab all category C that corresponds to category B
			if ($action_format == 0)		//	HTML
			{
				print_message_html_payload($message_id, $message2op, $html_results);
			}
			else
				if ($action_format == 1)	//	Array
				{
					print_message_data_payload($message_id, $message2op, $data_results);
				}
		break;

		case 3:	//	Grab all category D that corresponds to category C
			if ($action_format == 0)		//	HTML
			{
				print_message_html_payload($message_id, $message2op, $html_results);
			}
			else
				if ($action_format == 1)	//	Array
				{
					print_message_data_payload($message_id, $message2op, $data_results);
				}
		break;


		case 5:	//	Get one Category A entry data
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 6:	//	Get one Category B entry data
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 7:	//	Get one Category C entry data
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 8:	//	Get one Category D entry data
		print_message_data_payload($message_id, $message2op, $data_results);
		break;


		case 10:	//	Add Category A!
		print_message($message_id, $message2op);
		break;
		case 11:	//	Add Category B!
		print_message($message_id, $message2op);
		break;
		case 12:	//	Add Category C!
		print_message($message_id, $message2op);
		break;
		case 13:	//	Add Category D!
		print_message($message_id, $message2op);
		break;


		case 15:	//	Update category A!
		print_message($message_id, $message2op);
		break;
		case 16:	//	Update category B!
		print_message($message_id, $message2op);
		break;
		case 17:	//	Update category C!
		print_message($message_id, $message2op);
		break;
		case 18:	//	Update category D!
		print_message($message_id, $message2op);
		break;



		default:
		print_message(105945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>