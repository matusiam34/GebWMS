<?php


//	NOTE: save (action 3) needs to run a SELECT on the company code to see if there is one already with that name before updating!
//	not sure if this applies to the company.Will investigate later on as now I just want the functionality / feature


/*

	Error code for the script!
	Script code		=	101			//	needs changing!

 
	//	Action code breakdown
	0	:	Get all companies (HTML, for a table).
	1	:	Get one company info (array).
	2	:	Add company!
	3	:	Update company details!

 
	20	:	Get all companies that are active. Output: array!




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

	//	Now, make sure that ADD and UPDATE only work for the Admin of the entire system. Nobody else needs this kind of power!


	try
	{


		// load the supporting functions....
		require_once('lib_system.php');
		require_once('lib_db_conn.php');



		$action_code		=	leave_numbers_only($_POST['action_code_js']);		// this should be a number

/*
		//	Simple way to keep !Admins out of the game!
		if ($user_company_uid > 0)
		{
			$action_code	=	777;
			$message_id		=	777;
			$message2op		=	'ERR';		
		}
		else
		{
*/



		//	Get all companies. The action code for this is 0!
		if
		(
			($action_code == 0)		//	Get all companies in HTML table form.

			OR

			($action_code == 20)	//	All active companies in an array format!


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

						geb_company

				';


				if ($action_code == 20)
				{
					$sql	.=	'	WHERE company_disabled = 0';

					if ($user_company_uid > 0)
					{
						$sql	.=	'	AND company_pkey = ' . $user_company_uid;
					}
					else
					{

						//	Add All companies when it is someone who has the power to see all!
						$data_results[] =
						[
							'company_pkey'		=> '0',
							'company_code'		=> $mylang['all'],
							'company_disabled'	=> '0',
						];

					}

				}
				elseif ($action_code == 0)
				{
					if ($user_company_uid > 0)
					{
						$sql	.=	'	WHERE company_pkey = ' . $user_company_uid;
					}
					else
					{
/*
						//	Add All companies when it is someone who has the power to see all!
						$data_results[] =
						[
							'company_pkey'		=> '0',
							'company_code'		=> $mylang['all'],
							'company_disabled'	=> '0',
						];
*/
					}

				}



				$sql	.=	'	ORDER BY company_disabled ASC, company_code';




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
							
							$disabled	=	leave_numbers_only($item['company_disabled']);
							
							if ($disabled == 1)	{		$color_code		=	'red_class';	}


							$html_results	.=	'<tr data-id="' . leave_numbers_only($item['company_pkey']) . '" class="' . $color_code . '" >';
								$html_results	.=	'<td>' . trim($item['company_code']) . '</td>';
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
				is_it_enabled($_SESSION['menu_adm_company'])
			)
			{

				//	Get the UID of the company hopefully provided from the frontend.
				$company_uid	=	leave_numbers_only($_POST['company_uid_js']);	//	this should be a number

				$sql	=	'


						SELECT

						*
						
						FROM

						geb_company

						WHERE

						company_pkey = :scompany_uid

				';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':scompany_uid',			$company_uid,		PDO::PARAM_INT);
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


		//	Add one company to the system!

		else if ($action_code == 2)
		{

			//	Only an Admin of this system can add!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_company']))

				AND

				(can_user_add($_SESSION['menu_adm_company']))

			)
			{

				$company_name			=	trim($_POST['company_name_js']);	//	has to have a value
				$company_description	=	trim($_POST['company_description_js']);	//	optional
				$company_status			=	leave_numbers_only($_POST['company_status_js']);	// this should be a number and has to be a value!

				if (strlen($company_name) >= 2)	//	I am allowing the name of the company to be 2 character long!
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

						FROM  geb_company

						WHERE

						company_code = :scompany_name

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':scompany_name',	$company_name,	PDO::PARAM_STR);
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

								geb_company
								
								(
									company_code,
									company_desc,
									company_disabled
								) 

								VALUES

								(
									:icompany_code,
									:icompany_desc,
									:icompany_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':icompany_code',			$company_name,				PDO::PARAM_STR);
							$stmt->bindValue(':icompany_desc',			$company_description,		PDO::PARAM_STR);
							$stmt->bindValue(':icompany_disabled',		$company_status,			PDO::PARAM_INT);
							$stmt->execute();
							$db->commit();

							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];
						}

					}
					else
					{
						$message_id		=	101200;
						$message2op		=	$mylang['company_already_exists'];
					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	101201;
					$message2op		=	$mylang['name_too_short'];
				}



			}
			
		}	//	Action 2 end!


		//	Update one company!

		else if ($action_code == 3)
		{

			//	Only an Admin of this system can update a company!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_company']))

				AND

				(can_user_update($_SESSION['menu_adm_company']))

			)
			{

				$company_uid			=	leave_numbers_only($_POST['company_uid_js']);	// this should be a number
				$company_name			=	trim($_POST['company_name_js']);	//	has to have a value
				$company_description	=	trim($_POST['company_description_js']);	//	optional
				$company_status			=	leave_numbers_only($_POST['company_status_js']);	// this should be a number and has to be a value!




				if ($company_uid >= 0)
				{

					if (strlen($company_name) >= 2)	//	I am allowing the name of the warehouse to be 2 character long!
					{


						//	Here check if the name already maybe exists. If so ==>> notify the user!
						$match_uid	=	$company_uid;

						$sql	=	'

							SELECT

							*

							FROM  geb_company

							WHERE

							company_code = :scompany_name

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':scompany_name',	$company_name,	PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$match_uid	=	leave_numbers_only($row['company_pkey']);
							}

						}
						// show an error if the query has an error?
						else
						{
						}



						if ($match_uid == $company_uid)	//	hack	Maybe duplicates can be found in a more elegant way. DNC!
						{

							$db->beginTransaction();

							$sql	=	'

									UPDATE

									geb_company

									SET

									company_code		=	:ucompany_code,
									company_desc		=	:ucompany_desc,
									company_disabled	=	:ucompany_disabled

									WHERE

									company_pkey		 =	:ucompany_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':ucompany_code',			$company_name,				PDO::PARAM_STR);
								$stmt->bindValue(':ucompany_desc',			$company_description,		PDO::PARAM_STR);
								$stmt->bindValue(':ucompany_disabled',		$company_status,			PDO::PARAM_INT);
								$stmt->bindValue(':ucompany_pkey',			$company_uid,				PDO::PARAM_INT);
								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{
							$message_id		=	101202;
							$message2op		=	$mylang['company_already_exists'];
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
			
		}	//	Action 3 end!




	//	}	//	$user_company_uid chk


	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	101666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all companies
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Get one company details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 2:	//	Add Company
		print_message($message_id, $message2op);
		break;
		case 3:	//	Update Company
		print_message($message_id, $message2op);
		break;
		case 20:	//	Get all active companies!
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 777:	//	Not good! Someone is trying to be naughty!
		print_message($message_id, $message2op);
		break;
		default:
		print_message(101945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
