<?php


/*

	Error code for the script!
	Script code		=	106

	//	NOTE: Not complete by any means!

	//	Action code breakdown
	0	:	Get one product details!
	1	:	Get one location info (disabled location and warehouses ARE included! So it will grab any location!)


	2	:	Add product!
	3	:	Update product details!

*/



// load the login class
require_once('lib_login.php');


$message_id		=	106999;		//	999:	default bad
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


		//	Add product!

		else if ($action_code == 2)
		{

			//	Only an Manager or Admin of this system can add! At least that is the idea...
			if
			(

				(
					is_it_enabled($_SESSION['menu_mgr_products'])
				)

				AND

				(
					can_user_add($_SESSION['menu_mgr_products'])
				)

			)
			{


				// Data from the user to process...
				$product_code			=	trim($_POST['product_code_js']);	//	this should be text
				$product_description	=	trim($_POST['product_description_js']);	//	this should be text
				$product_category_a		=	leave_numbers_only($_POST['product_category_a_js']);	//	this should be a number
				$product_category_b		=	leave_numbers_only($_POST['product_category_b_js']);	//	this should be a number
				$each_barcode			=	trim($_POST['each_barcode_js']);	//	this should be text
				$each_weight			=	trim($_POST['each_weight_js']);	//	this should be text
				$case_barcode			=	trim($_POST['case_barcode_js']);	//	this should be text
				$case_qty				=	leave_numbers_only($_POST['case_qty_js']);	//	this should be a number
				$min_qty				=	leave_numbers_only($_POST['min_qty_js']);	//	this should be a number
				$max_qty				=	leave_numbers_only($_POST['max_qty_js']);	//	this should be a number
				$disabled				=	leave_numbers_only($_POST['disabled_js']);	//	this should be a number


				//	Here figure out if the operator provided the correct data and the right mix of required fields.
				//	For example if you provide a case barcode but not the case Qty = we have a problem since the system
				//	will not be able to scan in any cases of that product.

				$input_checks	=	0;	//	0 means all good


				if (strlen($product_code) < 2)	//	I am allowing the product code to be at least 2 characters long
				{
					//	Product code too short
					$input_checks	=	1;
				}
				else if (strlen($each_barcode) < 4)	//	barcode has to be at least 4 characters long!
				{
					//	Each barcode has to be at least 4 characters long
					$input_checks	=	2;
				}
				elseif ((strlen($case_barcode) > 0) OR ($case_qty > 0))
				{
					//	The user has provided info about cases... lets do further checks...
					//	I can not allow to just provide a barcode and not the case number. Same way in reverse!
					//	Both need to be provided for the case scanning to work!
					if (strlen($case_barcode) < 4)
					{
						//	Barcode is too short!
						$input_checks	=	3;
					}
					elseif ($case_qty < 1)
					{
						//	Case qty must be provided!
						$input_checks	=	4;
					}

				}
				elseif (($min_qty > 0) OR ($max_qty > 0))
				{
					//	Hmmmm... Do I need both? Maybe someone does not care about the min but only cares
					//	about the maximum value? Ok... lets leave it for now and see what to do with that at some point.
				}

				if ($input_checks == 0)	//	All input checks have passed! Move towards further checks!
				{

					$db->beginTransaction();

					$found_match	=	0;	//	0 = all good!

					//
					//	See if there are duplicates!
					//
					$sql	=	'

						SELECT

						*

						FROM geb_product

						WHERE

						prod_code = :sprod_code

						OR

						(

							(prod_each_barcode = :sprod_each_barcode)

							OR

							(prod_case_barcode = :sprod_case_barcode)

							OR

							(prod_each_barcode = :sprod_case_barcode)

							OR

							(prod_case_barcode = :sprod_each_barcode)

						)


					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sprod_code',				$product_code,	PDO::PARAM_STR);
						$stmt->bindValue(':sprod_each_barcode',		$each_barcode,	PDO::PARAM_STR);
						$stmt->bindValue(':sprod_case_barcode',		$case_barcode,	PDO::PARAM_STR);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{


							if (strcmp(trim($row['prod_code']), $product_code) === 0)
							{
								//	Found duplicate product code!
								$found_match	=	1;
							}
							else if
							(
								(strcmp(trim($row['prod_each_barcode']), $each_barcode) === 0)

								OR

								(strcmp(trim($row['prod_each_barcode']), $case_barcode) === 0)
							)
							{
								//	Found duplicate each barcode!
								$found_match	=	2;
							}
							else if
							(
								(strcmp(trim($row['prod_case_barcode']), $each_barcode) === 0)

								OR

								(strcmp(trim($row['prod_case_barcode']), $case_barcode) === 0)
							)
							{
								//	Found duplicate case barcode!
								$found_match	=	3;
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

								geb_product
								
								(
									prod_code,
									prod_desc,
									prod_category_a,
									prod_category_b,
									prod_each_barcode,
									prod_each_weight,
									prod_case_barcode,
									prod_case_qty,
									prod_min_qty,
									prod_max_qty,
									prod_disabled
								) 

								VALUES

								(
									:iprod_code,
									:iprod_desc,
									:iprod_category_a,
									:iprod_category_b,
									:iprod_each_barcode,
									:iprod_each_weight,
									:iprod_case_barcode,
									:iprod_case_qty,
									:iprod_min_qty,
									:iprod_max_qty,
									:iprod_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{


							$stmt->bindValue(':iprod_code',				$product_code,				PDO::PARAM_STR);
							$stmt->bindValue(':iprod_desc',				$product_description,		PDO::PARAM_STR);
							$stmt->bindValue(':iprod_category_a',		$product_category_a,		PDO::PARAM_INT);
							$stmt->bindValue(':iprod_category_b',		$product_category_b,		PDO::PARAM_INT);
							$stmt->bindValue(':iprod_each_barcode',		$each_barcode,				PDO::PARAM_STR);
							$stmt->bindValue(':iprod_each_weight',		$each_weight,				PDO::PARAM_STR);
							$stmt->bindValue(':iprod_case_barcode',		$case_barcode,				PDO::PARAM_STR);
							$stmt->bindValue(':iprod_case_qty',			$case_qty,					PDO::PARAM_INT);
							$stmt->bindValue(':iprod_min_qty',			$min_qty,					PDO::PARAM_INT);
							$stmt->bindValue(':iprod_max_qty',			$max_qty,					PDO::PARAM_INT);
							$stmt->bindValue(':iprod_disabled',			$disabled,					PDO::PARAM_INT);
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
							$message_id		=	106200;
							$message2op		=	$mylang['product_already_exists'];
						}
						elseif ($found_match == 2)
						{
							$message_id		=	106201;
							$message2op		=	$mylang['barcode_already_exists'];
						}
						elseif ($found_match == 3)
						{
							$message_id		=	106202;
							$message2op		=	$mylang['barcode_already_exists'];
						}

					}


				}
				else
				{
					//	Input checks have failed... Provide all the required messages so that the operator can fix them!

					if ($input_checks	==	1)
					{
						$message_id		=	106203;
						$message2op		=	$mylang['name_too_short'];
					}
					elseif ($input_checks	==	2)
					{
						$message_id		=	106204;
						$message2op		=	$mylang['barcode_too_short'];
					}
					elseif ($input_checks	==	3)
					{
						$message_id		=	106205;
						$message2op		=	'Case barcode too short';//$mylang['barcode_to_short'];
					}
					elseif ($input_checks	==	4)
					{
						$message_id		=	106205;
						$message2op		=	'Case qty incorrect';//$mylang['barcode_to_short'];
					}


				}



			}


		}	//	Action 2 end!


		//	Update one product!

		else if ($action_code == 3)
		{

			//	Only an Admin of this system can update a product!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_warehouse_loc']))

				AND

				(can_user_update($_SESSION['menu_adm_warehouse_loc']))

			)
			{

				// Data from the user to process...
				$product_uid			=	leave_numbers_only($_POST['product_uid_js']);	//	this should be a number
				$product_code			=	trim($_POST['product_code_js']);	//	this should be text
				$product_description	=	trim($_POST['product_description_js']);	//	this should be text
				$product_category_a		=	leave_numbers_only($_POST['product_category_a_js']);	//	this should be a number
				$product_category_b		=	leave_numbers_only($_POST['product_category_b_js']);	//	this should be a number
				$each_barcode			=	trim($_POST['each_barcode_js']);	//	this should be text
				$each_weight			=	trim($_POST['each_weight_js']);	//	this should be text
				$case_barcode			=	trim($_POST['case_barcode_js']);	//	this should be text
				$case_qty				=	leave_numbers_only($_POST['case_qty_js']);	//	this should be a number
				$min_qty				=	leave_numbers_only($_POST['min_qty_js']);	//	this should be a number
				$max_qty				=	leave_numbers_only($_POST['max_qty_js']);	//	this should be a number
				$disabled				=	leave_numbers_only($_POST['disabled_js']);	//	this should be a number



				if ($product_uid >= 0)
				{


					//	Here figure out if the operator provided the correct data and the right mix of required fields.
					//	For example if you provide a case barcode but not the case Qty = we have a problem since the system
					//	will not be able to scan in any cases of that product.

					$input_checks	=	0;	//	0 means all good


					if (strlen($product_code) < 2)	//	I am allowing the product code to be at least 2 characters long
					{
						//	Product code too short
						$input_checks	=	1;
					}
					else if (strlen($each_barcode) < 4)	//	barcode has to be at least 4 characters long!
					{
						//	Each barcode has to be at least 4 characters long
						$input_checks	=	2;
					}
					elseif ((strlen($case_barcode) > 0) OR ($case_qty > 0))
					{
						//	The user has provided info about cases... lets do further checks...
						//	I can not allow to just provide a barcode and not the case number. Same way in reverse!
						//	Both need to be provided for the case scanning to work!
						if (strlen($case_barcode) < 4)
						{
							//	Barcode is too short!
							$input_checks	=	3;
						}
						elseif ($case_qty < 1)
						{
							//	Case qty must be provided!
							$input_checks	=	4;
						}

					}
					elseif (($min_qty > 0) OR ($max_qty > 0))
					{
						//	Hmmmm... Do I need both? Maybe someone does not care about the min but only cares
						//	about the maximum value? Ok... lets leave it for now and see what to do with that at some point.
					}








					if ($input_checks == 0)	//	All input checks have passed! Move towards further checks!
					{

						$db->beginTransaction();

						$found_match	=	0;	//	0 = all good!

						//
						//
						//
						//	!!!!!!!!!!!!!!!!!!!
						//
						//	See if there are duplicates! Have a look at this later just in case I missed something...
						//
						//	!!!!!!!!!!!!!!!!!!!
						//
						//
						//
						$sql	=	'

							SELECT

							*

							FROM geb_product

							WHERE

							prod_pkey	<>	:sprod_pkey

							AND

							(

								(prod_each_barcode = :sprod_each_barcode)

								OR

								(prod_case_barcode = :sprod_case_barcode)

								OR

								(prod_each_barcode = :sprod_case_barcode)

								OR

								(prod_case_barcode = :sprod_each_barcode)

							)



						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sprod_pkey',				$product_uid,	PDO::PARAM_INT);
							$stmt->bindValue(':sprod_each_barcode',		$each_barcode,	PDO::PARAM_STR);
							$stmt->bindValue(':sprod_case_barcode',		$case_barcode,	PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{


								if
								(
									(strcmp(trim($row['prod_each_barcode']), $each_barcode) === 0)

									OR

									(strcmp(trim($row['prod_each_barcode']), $case_barcode) === 0)
								)
								{
									//	Found duplicate each barcode!
									$found_match	=	2;
								}
								else if
								(
									(strcmp(trim($row['prod_case_barcode']), $each_barcode) === 0)

									OR

									(strcmp(trim($row['prod_case_barcode']), $case_barcode) === 0)
								)
								{
									//	Found duplicate case barcode!
									$found_match	=	3;
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


									UPDATE

									geb_product

									SET

									prod_code			=	:uprod_code,
									prod_desc			=	:uprod_desc,
									prod_category_a		=	:uprod_category_a,
									prod_category_b		=	:uprod_category_b,
									prod_each_barcode	=	:uprod_each_barcode,
									prod_each_weight	=	:uprod_each_weight,
									prod_case_barcode	=	:uprod_case_barcode,
									prod_case_qty		=	:uprod_case_qty,
									prod_min_qty		=	:uprod_min_qty,
									prod_max_qty		=	:uprod_max_qty,
									prod_disabled		=	:uprod_disabled

									WHERE

									prod_pkey	 		=	:uprod_pkey


							';


							if ($stmt = $db->prepare($sql))
							{


								$stmt->bindValue(':uprod_code',				$product_code,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_desc',				$product_description,		PDO::PARAM_STR);
								$stmt->bindValue(':uprod_category_a',		$product_category_a,		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_b',		$product_category_b,		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_each_barcode',		$each_barcode,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_each_weight',		$each_weight,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_case_barcode',		$case_barcode,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_case_qty',			$case_qty,					PDO::PARAM_INT);
								$stmt->bindValue(':uprod_min_qty',			$min_qty,					PDO::PARAM_INT);
								$stmt->bindValue(':uprod_max_qty',			$max_qty,					PDO::PARAM_INT);
								$stmt->bindValue(':uprod_disabled',			$disabled,					PDO::PARAM_INT);

								$stmt->bindValue(':uprod_pkey',				$product_uid,					PDO::PARAM_INT);

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
								$message_id		=	106200;
								$message2op		=	$mylang['product_already_exists'];
							}
							elseif ($found_match == 2)
							{
								$message_id		=	106201;
								$message2op		=	$mylang['barcode_already_exists'];
							}
							elseif ($found_match == 3)
							{
								$message_id		=	106202;
								$message2op		=	$mylang['barcode_already_exists'];
							}

						}


					}
					else
					{
						//	Input checks have failed... Provide all the required messages so that the operator can fix them!

						if ($input_checks	==	1)
						{
							$message_id		=	106203;
							$message2op		=	$mylang['name_too_short'];
						}
						elseif ($input_checks	==	2)
						{
							$message_id		=	106204;
							$message2op		=	$mylang['barcode_too_short'];
						}
						elseif ($input_checks	==	3)
						{
							$message_id		=	106205;
							$message2op		=	'Case barcode too short';//$mylang['barcode_to_short'];
						}
						elseif ($input_checks	==	4)
						{
							$message_id		=	106205;
							$message2op		=	'Case qty incorrect';//$mylang['barcode_to_short'];
						}


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
		case 2:	//	Add product
		print_message($message_id, $message2op);
		break;
		case 3:	//	Update product
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
