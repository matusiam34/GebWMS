<?php


/*

	Error code for the script!
	Script code		=	105

 
	//	Action code breakdown
	0	:	Get one product details!
	1	:	Get one location info (disabled location and warehouses ARE included! So it will grab any location!)
	2	:	Add location!
	3	:	Update location details!

*/



// load the login class
require_once('lib_login.php');


$message_id		=	102999;		//	999:	default bad
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


		//	Get all locations. The action code for this is 0
		if ($action_code == 0)
		{



			$sql	=	'


				SELECT

				geb_warehouse.wh_code,
				geb_location.loc_pkey,
				geb_location.loc_code,

				geb_location.loc_barcode,
				geb_location.loc_function,
				geb_location.loc_type,
				geb_location.loc_blocked,
				geb_location.loc_note,
				geb_location.loc_disabled


				FROM  geb_location

				INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


				WHERE

				geb_warehouse.wh_disabled = 0


				ORDER BY wh_code, loc_code


			';

				$html_results		=	'';

				$html_results		.=	'<thead>';

				$html_results		.=	'<tr>';
				$html_results		.=		'<th>UID</th>';
				$html_results		.=		'<th>Warehouse</th>';
				$html_results		.=		'<th>Location</th>';
				$html_results		.=		'<th>Barcode</th>';
				$html_results		.=		'<th>Note</th>';
				$html_results		.=	'</tr>';

				$html_results		.=	'</thead>';

				$html_results		.=	'<tbody style="max-height:400px;">';

				if ($stmt = $db->prepare($sql))
				{
					$stmt->execute();


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{

						$tr_style	=	'';

						if (leave_numbers_only($row['loc_disabled']) == 1)
						{
							//	Location is disabled. Have a greyish hue background color!
							$tr_style	=	' style="background-color: #d9d9d9;" ';
						}

						$html_results		.=	'<tr ' . $tr_style . '>';
						$html_results		.=		'<td>'	.	trim($row['loc_pkey'])	.	'</td>';
						$html_results		.=		'<td>'	.	trim($row['wh_code'])	.	'</td>';


						// Generate the loc status code. This will allow the operator to see if the location is a Single, Blocked, Mixed etc at a glance
						$loc_function			=	leave_numbers_only($row['loc_function']);
						$loc_type				=	leave_numbers_only($row['loc_type']);
						$loc_blocked			=	leave_numbers_only($row['loc_blocked']);

						$loc_details_arr		=	decode_loc($loc_function, $loc_type, $loc_blocked, $loc_function_codes_arr, $loc_type_codes_arr);


						$html_results		.=		'<td style="'	.	$loc_details_arr[1]	.	'">'	.	trim($row['loc_code'])	.	' ('	.	$loc_details_arr[0]	.	')</td>';
						$html_results		.=		'<td>'	.	trim($row['loc_barcode'])	.	'</td>';


						// Convert the type of location type info into meaninful text.
						$html_results		.=		'<td>'	.	trim($row['loc_note'])		.	'</td>';
						$html_results		.=	'</tr>';

					}

					$html_results		.=	'</tbody>';
					$message_id		=	0;	//	all went well

				}

		}	//	Action 0 end!


		//	Get details of one!

		else if ($action_code == 1)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_warehouse_loc'])
			)
			{

				//	Get the UID of the location hopefully provided from the frontend.
				$loc_uid	=	leave_numbers_only($_POST['loc_uid_js']);	//	this should be a number

				$sql	=	'


					SELECT

					geb_warehouse.wh_pkey,
					geb_location.loc_pkey,
					geb_location.loc_code,

					geb_location.loc_barcode,
					geb_location.loc_function,
					geb_location.loc_type,
					geb_location.loc_blocked,
					geb_location.loc_note,
					geb_location.loc_disabled


					FROM  geb_location

					INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


					WHERE

					geb_location.loc_pkey = :sloc


					ORDER BY wh_code, loc_code

				';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':sloc',		$loc_uid,		PDO::PARAM_INT);
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
					is_it_enabled($_SESSION['menu_adm_warehouse_loc'])
				)

				AND

				(
					can_user_add($_SESSION['menu_adm_warehouse_loc'])
				)

			)
			{


				// Data from the user to process...
				$warehouse		=	leave_numbers_only($_POST['warehouse_js']);
				$location		=	trim($_POST['location_js']);
				$barcode		=	trim($_POST['barcode_js']);
				$type			=	leave_numbers_only($_POST['type_js']);
				$function		=	leave_numbers_only($_POST['function_js']);
				$blocked		=	leave_numbers_only($_POST['blocked_js']);
				$loc_desc		=	trim($_POST['loc_desc_js']);
				$disabled		=	leave_numbers_only($_POST['disabled_js']);




				if (strlen($location) >= 1)	//	I am allowing the name of the location to be 1 character long!
				{

					$db->beginTransaction();


					//	Since two warehouses can have the same location names I need to check for barcodes instead!
					//	I can't afford to have two identical barcodes since that will cause chaos!

					$found_match	=	0;	//	0 = all good!

					//	TO DO: Also, can't have duplicate name of location within a warehouse!!!

					//
					// Seek out for duplicate barcode entry across ALL locations!
					//
					$sql	=	'

						SELECT

						loc_pkey,
						loc_wh_pkey,
						loc_code,
						loc_barcode

						FROM geb_location

						WHERE

						loc_barcode = :sloc_barcode

						OR

						(

							(loc_wh_pkey = :sloc_warehouse_pkey)

							AND
							
							(loc_code = :sloc_code)
						
						)

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sloc_barcode',			$barcode,		PDO::PARAM_STR);
						$stmt->bindValue(':sloc_warehouse_pkey',	$warehouse,		PDO::PARAM_INT);
						$stmt->bindValue(':sloc_code',				$location,		PDO::PARAM_STR);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{

							//	if the warehouse code matches and location name matches = the operator is trying to add the same
							//	location name to the same warehouse. It is a NO NO!
							if (($row['loc_wh_pkey'] == $warehouse) AND ($row['loc_code'] == $location))
							{
								$found_match	=	1;	//	Trying to add identical location name in the same warehouse! Not cool!
							}
							else if  (($row['loc_barcode'] == $barcode))
							{
								$found_match	=	2;	//	The barcode you entered is already allocated to a location!
							}


						}

					}
					// show an error if the query has an error?
					else
					{
					}



					//	0	means no issues!
					if ($found_match == 0)
					{

						$sql	=	'


								INSERT
								
								INTO

								geb_location
								
								(
									loc_wh_pkey,
									loc_code,
									loc_barcode,
									loc_function,
									loc_type,
									loc_blocked,
									loc_note,
									loc_disabled
								) 

								VALUES

								(
									:iloc_wh_pkey,
									:iloc_code,
									:iloc_barcode,
									:iloc_function,
									:iloc_type,
									:iloc_blocked,
									:iloc_note,
									:iloc_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':iloc_wh_pkey',		$warehouse,			PDO::PARAM_INT);
							$stmt->bindValue(':iloc_code',			$location,			PDO::PARAM_STR);
							$stmt->bindValue(':iloc_barcode',		$barcode,			PDO::PARAM_STR);
							$stmt->bindValue(':iloc_function',		$function,			PDO::PARAM_INT);
							$stmt->bindValue(':iloc_type',			$type,				PDO::PARAM_INT);
							$stmt->bindValue(':iloc_blocked',		$blocked,			PDO::PARAM_INT);
							$stmt->bindValue(':iloc_note',			$loc_desc,			PDO::PARAM_STR);
							$stmt->bindValue(':iloc_disabled',		$disabled,			PDO::PARAM_INT);

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
							$message_id		=	102200;
							$message2op		=	$mylang['location_already_exists'];
						}
						elseif ($found_match == 2)
						{
							$message_id		=	102201;
							$message2op		=	$mylang['barcode_already_exists'];
						}

					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	102202;
					$message2op		=	$mylang['name_to_short'];
				}



			}
			
		}	//	Action 2 end!


		//	Update one location!

		else if ($action_code == 3)
		{

			//	Only an Admin of this system can update a group!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_warehouse_loc']))

				AND

				(can_user_update($_SESSION['menu_adm_warehouse_loc']))

			)
			{

				// Data from the user to process...
				$warehouse		=	leave_numbers_only($_POST['warehouse_js']);
				$location		=	trim($_POST['location_js']);
				$barcode		=	trim($_POST['barcode_js']);
				$type			=	leave_numbers_only($_POST['type_js']);
				$lfunction		=	leave_numbers_only($_POST['function_js']);
				$blocked		=	leave_numbers_only($_POST['blocked_js']);
				$loc_desc		=	trim($_POST['loc_desc_js']);
				$disabled		=	leave_numbers_only($_POST['disabled_js']);
				$loc_uid		=	leave_numbers_only($_POST['loc_uid_js']);	//	this should be a number


				if ($loc_uid >= 0)
				{

					if (strlen($location) >= 1)	//	I am allowing the name of the location to be 1 character long!
					{

						$db->beginTransaction();


						//	Since two warehouses can have the same location names I need to check for barcodes instead!
						//	I can't afford to have two identical barcodes since that will cause chaos!

						$found_match	=	0;	//	0 = all good!

						//	TO DO: Also, can't have duplicate name of location within a warehouse!!!

						//
						// Seek out for duplicate barcode entry across ALL locations!
						//
						$sql	=	'

							SELECT

							loc_pkey,
							loc_wh_pkey,
							loc_code,
							loc_barcode

							FROM geb_location

							WHERE

							loc_barcode = :sloc_barcode

							OR

							(

								(loc_wh_pkey = :sloc_warehouse_pkey)

								AND
								
								(loc_code = :sloc_code)
							
							)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sloc_barcode',			$barcode,		PDO::PARAM_STR);
							$stmt->bindValue(':sloc_warehouse_pkey',	$warehouse,		PDO::PARAM_INT);
							$stmt->bindValue(':sloc_code',				$location,		PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{

								if ($row['loc_pkey'] == $loc_uid)
								{
									//	Here is the original entry... I am not worried about this one!

								}
								else
								{

									if
									(
										($row['loc_wh_pkey'] == $warehouse) AND ($row['loc_code'] == $location)
										AND
										($row['loc_pkey'] <> $loc_uid)
									)
									{
										//	Same named entry exists on this warehouse!
										$found_match	=	1;
										
									}	else if
									(

										($row['loc_barcode'] == $barcode)AND ($row['loc_pkey'] <> $loc_uid)
									)
									{
										$found_match	=	2;	//	The barcode you entered is already allocated to a location!
									}



/*
									//	if the warehouse code matches and location name matches = the operator is trying to add the same
									//	location name to the same warehouse. It is a NO NO!
									if (($row['loc_wh_pkey'] == $warehouse) AND ($row['loc_code'] == $location))
									{
										$found_match	=	1;	//	Trying to add identical location name in the same warehouse! Not cool!
									}
									else if  (($row['loc_barcode'] == $barcode))
									{
										$found_match	=	2;	//	The barcode you entered is already allocated to a location!
									}
	*/								
									//$found_match	=	2;
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

								geb_location

								SET

								loc_wh_pkey		=		:uloc_wh_pkey,
								loc_code		=		:uloc_code,
								loc_barcode		=		:uloc_barcode,
								loc_function	=		:uloc_function,
								loc_type		=		:uloc_type,
								loc_blocked		=		:uloc_blocked,
								loc_note		=		:uloc_note,
								loc_disabled	=		:uloc_disabled

								WHERE

								loc_pkey	 =	:sloc_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':uloc_wh_pkey',	$warehouse,		PDO::PARAM_INT);
								$stmt->bindValue(':uloc_code',		$location,		PDO::PARAM_STR);
								$stmt->bindValue(':uloc_barcode',	$barcode,		PDO::PARAM_STR);
								$stmt->bindValue(':uloc_function',	$lfunction,		PDO::PARAM_INT);
								$stmt->bindValue(':uloc_type',		$type,			PDO::PARAM_INT);
								$stmt->bindValue(':uloc_blocked',	$blocked,		PDO::PARAM_INT);
								$stmt->bindValue(':uloc_note',		$loc_desc,		PDO::PARAM_STR);
								$stmt->bindValue(':uloc_disabled',	$disabled,		PDO::PARAM_INT);

								$stmt->bindValue(':sloc_pkey',		$loc_uid,		PDO::PARAM_INT);
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
								$message_id		=	102203;
								$message2op		=	$mylang['location_already_exists'];
							}
							elseif ($found_match == 2)
							{
								$message_id		=	102204;
								$message2op		=	$mylang['barcode_already_exists'];
							}

						}

					}
					else
					{
						$message_id		=	102205;
						$message2op		=	$mylang['name_to_short'];
					}

				}
				else
				{
					$message_id		=	102206;
					$message2op		=	$mylang['incorrect_uid'];
				}


			}
			
		}	//	Action 3 end!







	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	102666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all locations
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Get one location details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 2:	//	Add location
		print_message($message_id, $message2op);
		break;
		case 3:	//	Update location
		print_message($message_id, $message2op);
		break;
		default:
		print_message(102945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
