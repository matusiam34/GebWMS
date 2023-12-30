<?php

//	NOTE:	add qty info for mimics in the product details table (frontend HTML). This will be very good to let the operator know that they have 
//			encountered a mimic and could check the case qty if needed!

/*

	Error code for the script!
	Script code		=	107

 
	//	Action code breakdown
	0	:	Get product details in a form of an HTML table.
	1	:	Get location details in a form of an HTML table + Perform checks if the product and location is compatible!



*/



// load the login class
require_once('lib_login.php');


$message_id		=	107999;		//	999:	default bad
$message2op		=	'';			//	When an error happens provide a message here. Can be something positive as well like "All done", "a-Ok"
$html_results	=	'';			//	HTML code as output. Depending the Action Code this can be empty or a full HTML table.
$data_results	=	array();	//	array with all of the data collected

// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{



	try
	{


		// load the supporting functions....
		require_once('lib_system.php');
		require_once('lib_db_conn.php');

		$db->beginTransaction();


		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number


		//	Get product details!
		if
		(
			($action_code == 0)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_prod2loc'])
			)
			{
			
				$product_barcode	=	trim($_POST['prod_barcode_js']);	// this should be a number
				$product_data		=	array();	//	Product details stored here

				$input_checks	=	666;	//	0 means all good; by default it is 666 = BAD!

				if (strlen($product_barcode) < min_product_len)	//	Set io lib_system
				{
					// Product barcode doesn't meet the length requirements! 
					$input_checks = 1;
				}
				else if (is_numeric($product_barcode) == false)
				{
					// Product barcode is not numeric! Abort!
					$input_checks = 2;
				}
				else
				{
					// Success! All checks are good so far!
					$input_checks = 0;
				}



				if ($input_checks == 0)
				{

					$sql	=	'

						SELECT


						prod_code,
						prod_desc,
						prod_each_barcode,
						prod_case_barcode,
						prod_each_barcode_mimic,
						prod_case_barcode_mimic


						FROM 

						geb_product

						WHERE

						prod_each_barcode = :sprod_each_bar OR prod_case_barcode = :sprod_case_bar

						OR

						CASE
							WHEN prod_mimic = 1 THEN ((prod_each_barcode_mimic = :smimic_bar) OR (prod_case_barcode_mimic = :smimic_bar))
						END;


						AND
						
						prod_disabled = 0

					';



					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sprod_each_bar',		$product_barcode,	PDO::PARAM_STR);
						$stmt->bindValue(':sprod_case_bar',		$product_barcode,	PDO::PARAM_STR);
						$stmt->bindValue(':smimic_bar',			$product_barcode,	PDO::PARAM_STR);

						$stmt->execute();


						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$product_data[]	=	$row;
						}

						// Analyise what the products_arr has to offer...
						if (count($product_data) == 1)
						{

							//	Found one product... So... Show the operator some basic details so that it matches what they
							//	think they are scanning is correct. Basic checks on the human side!

							$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

							foreach ($product_data as $product)
							{

								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['product_code'] . '</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($product['prod_code']) . '</td>';
								$html_results	.=	'</tr>';

								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['description'] . '</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($product['prod_desc']) . '</td>';
								$html_results	.=	'</tr>';




								$qty_adjust_input	=	'';


								$input_disabled	=	' readonly ';	// for kicks lets make the product_qty read only! this could change after revision.

								//	Here provide the ability to increase the QTY of products the operator wants to add to the location!
								$qty_adjust_input	.=	'<div class="field has-addons">';

									$qty_adjust_input	.=	'<p class="control is-expanded">';
									$qty_adjust_input	.=	'<input class="input" type="text" id="product_qty" name="product_qty" value="1"' . $input_disabled . '>';
									$qty_adjust_input	.=	'</p>';

									$qty_adjust_input	.=	'<p class="control">';
									$qty_adjust_input	.=		'<button class="button inventory_class iconMinus" onClick="decrease_value();" style="width:50px;"></button>';
									$qty_adjust_input	.=	'</p>';

									$qty_adjust_input	.=	'<p class="control">';
									$qty_adjust_input	.=		'<button class="button inventory_class iconAdd" onClick="increase_value();" style="width:50px;"></button>';
									$qty_adjust_input	.=	'</p>';

								$qty_adjust_input	.=	'</div>';


								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;" class="is-vcentered">' . $mylang['qty'] . '</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';  padding: 0">' . $qty_adjust_input . '</td>';
								$html_results	.=	'</tr>';



								//	Figure out if it is an EACH or CASE unit!
								$product_unit	=	'';
								if
								(
									(strcmp($product_barcode, trim($product['prod_each_barcode'])) === 0 )
									OR
									(strcmp($product_barcode, trim($product['prod_each_barcode_mimic'])) === 0 )
								)
								{
									$product_unit	=	$mylang['each'];
								}
								elseif
								(
									(strcmp($product_barcode, trim($product['prod_case_barcode'])) === 0 )
									OR
									(strcmp($product_barcode, trim($product['prod_case_barcode_mimic'])) === 0 )
								)
								{
									$product_unit	=	$mylang['case'];
								}




								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['unit'] . '</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_unit . '</td>';
								$html_results	.=	'</tr>';


							}

							$html_results	.=	'</table>';



							//	Now it is time to provide the location input field
							$html_results	.=	'

								<div class="field has-addons">

									<p class="control is-expanded">
										<input class="input is-fullwidth" type="text" id="location_barcode" placeholder="' . $mylang['location_barcode'] . '">
									</p>

									<p class="control">
										<button class="button inventory_class iconSearch" style="width:50px;" onClick="get_location_details();" type="submit"></button>
									</p>

									<p class="control">
										<button class="button inventory_class iconFocus" style="width:50px;" onClick="clear_location_barcode();" type="submit"></button>
									</p>


								</div>';


							$html_results	.=	'
							
							<script>

								set_Focus_On_Element_By_ID("location_barcode");


								function clear_location_barcode()
								{
									set_Element_Value_By_ID("location_barcode", "");
									set_Focus_On_Element_By_ID("location_barcode");
								}



							</script>';


							$message_id		=	0;	//	all went well


						}
						elseif (count($product_data) == 0)
						{
							//	No product found!
							$message_id		=	107203;
							$message2op		=	$mylang['product_not_found'];
						}
						elseif (count($product_data) > 1)
						{
							//	Two or more products found with the same barcode... Needs to be fixed ASAP!
							$message_id		=	107203;
							$message2op		=	$mylang['products_found_with_the_same_barcode'];
						}



					}
					// show an error if the query has an error
					else
					{
						//	Needs work!
					}



				}	//	END OF: if ($input_checks == 0)
				else
				{

					if ($input_checks	==	1)
					{
						// Product barcode doesn't meet the length requirements! 
						$message_id		=	107203;
						$message2op		=	$mylang['barcode_too_short'];
					}
					elseif ($input_checks	==	2)
					{
						// Product barcode is not numeric! Abort!
						$message_id		=	107204;
						$message2op		=	$mylang['invalid_barcode'];
					}

				}



				//	When things do NOT go to plan!
				if ($message_id > 0)
				{
					$html_results	=	'<table class="is-fullwidth table is-bordered is-marginless">';	//	all error messages for action 0 here!
						$html_results	.=	'<tr>';
							$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['error'] . ':</td>';
							$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $message_id . '</td>';
						$html_results	.=	'</tr>';

						$html_results	.=	'<tr class="">';
							$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['description'] . ':</td>';
							$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $message2op . '</td>';
						$html_results	.=	'</tr>';
					$html_results	.=	'</table>';
				}



			}	//	Permissions check!


		}	//	Action 0 end!


		//	Check if location and product is ok to update or insert to stock!
		elseif
		(
			($action_code == 1)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_prod2loc'])
			)
			{



				$product_barcode		=	trim($_POST['prod_barcode_js']);
				$product_qty			=	leave_numbers_only($_POST['prod_qty_js']);
				$location_barcode		=	trim($_POST['loc_barcode_js']);

				$outcome				=	do_magic($db, $product_barcode, $location_barcode, $product_qty);

				$message_id				=	$outcome['control'];
				$message2op				=	$outcome['msg'];


			}	//	Permissions check!


		}	//	Action 1 end!









	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	107666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab product details
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Grab location details
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		default:
		print_message(107945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}








/*



										//	Generate a table with some explanation...
										$mismatch_table		=	'<table class="is-fullwidth table is-bordered is-marginless">';


										$mismatch_table		.=	'<tr>';
										$mismatch_table		.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['product'] . '</td>';
										$mismatch_table		.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . '</td>';
										$mismatch_table		.=	'<tr>';


										//	THese two most likely need a rewrite...
										$product_unit_str	=	'';
										if 		($product_unit == 1)	{	$product_unit_str	=	$mylang['each'];	}
										elseif	($product_unit == 3)	{	$product_unit_str	=	$mylang['case'];	}


										$location_unit_str	=	'';
										if 		($location_type == 11)	{	$location_unit_str	=	$mylang['each'];	}
										elseif	($location_type == 12)	{	$location_unit_str	=	$mylang['case'];	}

										$mismatch_table		.=	'<tr>';
										$mismatch_table		.=	'<td style="background-color: ' . $backclrB . ';">' . $product_unit_str . '</td>';
										$mismatch_table		.=	'<td style="background-color: ' . $backclrB . ';">' . $location_unit_str . '</td>';
										$mismatch_table		.=	'<tr>';

										$mismatch_table		.=	'</table>';


										$message_id		=	107203;
										$message2op		=	$mylang['unit_mismatch'] . '<br>' . $mismatch_table;



								//	Decode the location code into something more use friendly!
								$loc_details_arr = decode_loc
								(
									$location_arr['loc_function'],
									$location_arr['loc_type'],
									$location_arr['loc_blocked'],
									$loc_function_codes_arr,
									$loc_type_codes_arr
								);


								//	Generate the HTML Table for the operator!
								$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['error'] . ':</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $message2op . '</td>';
								$html_results	.=	'</tr>';

								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $loc_details_arr[1] . '">' . $loc_arr[0]['loc_code'] . ' (' . $loc_details_arr[0] . ')</td>';
								$html_results	.=	'</tr>';

								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['function'] . ':</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_functions_arr[$loc_arr[0]['loc_function']] . '</td>';
								$html_results	.=	'</tr>';


								$html_results	.=	'</table>';






*/



?>
