<?php


/*

	Error code for the script!
	Script code		=	107

 
	//	Action code breakdown
	0	:	Get product details in a form of an HTML table.
	1	:	Get location details in a form of an HTML table.





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
				$product_arr		=	array();	//	Product details stored here


				$input_checks	=	666;	//	0 means all good; by default it is 666 = BAD!

				if (strlen($product_barcode) < 4)
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
							$product_arr[]	=	$row;
						}

						// Analyise what the products_arr has to offer...
						if (count($product_arr) == 1)
						{

							//	Found one product... So... Show the operator some basic details so that it matches what they
							//	think they are scanning is correct. Basic checks on the human side!

							$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

							foreach ($product_arr as $product)
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


								</div>';


							$html_results	.=	'
							
							<script>

								set_Focus_On_Element_By_ID("location_barcode");

							</script>';


							$message_id		=	0;	//	all went well


						}
						elseif (count($product_arr) == 0)
						{
							//	No product found!
							$message_id		=	107203;
							$message2op		=	$mylang['product_not_found'];
						}
						elseif (count($product_arr) > 1)
						{
							//	Two or more products found with the same barcode... Needs to be fixed ASAP!
							$message_id		=	107203;
							$message2op		=	$mylang['products_found_with_the_same_barcode'];
						}



					}
					// show an error if the query has an error
					else
					{
					}



				}	//	if ($input_checks == 0)
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

				//	Note to self:	First
				/*
					SELECT

					loc_pkey,
					loc_wh_pkey,
					loc_barcode,
					
					stk_pkey,
					stk_loc_pkey,
					stk_prod_pkey,
					stk_qty

					FROM 

					geb_location
					
					LEFT JOIN geb_stock ON geb_location.loc_pkey = geb_stock.stk_loc_pkey

					WHERE

					geb_location.loc_barcode = '62631518'
					
					AND
					
					(geb_stock.stk_disabled IS NULL OR geb_stock.stk_disabled = 0)
				*/



					//	This will get me everything about the location (that exists FOR SURE! unless wrong barcode is provided!
					/*
					Array ( [stk_pkey] => 22 [stk_loc_pkey] => 4 [stk_prod_pkey] => 8 [stk_qty] => 6 )
					Array ( [stk_pkey] => 21 [stk_loc_pkey] => 4 [stk_prod_pkey] => 8 [stk_qty] => 10 )
					Array ( [stk_pkey] => 25 [stk_loc_pkey] => 4 [stk_prod_pkey] => 9 [stk_qty] => 43 )
					*/


					//	This array will allow me to iterate and see if the product provided (the ID) matchest the stk_prod_pkey!
					//	Based on that I will be able to see if the product gets a green light or not!
					//	Most likely not to duplicate the action do the UPDATE / INSERT within this one with an extra flag!
					//	This way I will have to write checks only once and not twice if anything changes!!!




				
				//	Figure out if the location is any good. Basic checks first. Things like:
				//
				//	-	is the location blocked?
				//	-	is the location disabled?
				//	-	does the warehouse of the operator match the warehouse he wants to do a move in.
				//

				$location_arr		=	array();	//	Location details stored here
				$product_arr		=	array();	//	Product details stored here

				$product_uid		=	0;	//	A query will obtain this using the provided product barcode!
				$product_barcode	=	trim($_POST['prod_barcode_js']);
				$product_qty		=	leave_numbers_only($_POST['prod_qty_js']);
				$location_barcode	=	trim($_POST['loc_barcode_js']);

				//	Used to determine what to show based on users warehouse settings.
				//	Keep in mind that a value of 0 means ALL warehouses! So only apply a filter when the value is <> 0
				$user_warehouse_uid	=	leave_numbers_only($_SESSION['user_warehouse']);


				$input_checks	=	666;	//	0 means all good; by default it is 666 = BAD!

				if
				(
					(strlen($product_barcode) < 4)
					OR
					(strlen($location_barcode) < 4)
				)
				{
					// Product nor location barcode doesn't meet the length requirements! 
					$input_checks = 1;
				}
				else if
				(
					(is_numeric($product_barcode) == false)
					OR
					(is_numeric($location_barcode) == false)
				)
				{
					// Product nor location barcode is not numeric! Abort!
					$input_checks = 2;
				}
				else if (is_numeric($product_qty) == false)
				{
					// Product Qty is not a number...
					$input_checks = 3;
				}
				else if ($product_qty <= 0)
				{
					// Product Qty is either 0 or negative (-1, -50, etc.). Can't insert stock that is a negative quantity!
					$input_checks = 4;
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

						*

						FROM 

						geb_product

						WHERE

						prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar

						AND
						
						prod_disabled = 0

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':iprod_each_bar',		$product_barcode,	PDO::PARAM_STR);
						$stmt->bindValue(':iprod_case_bar',		$product_barcode,	PDO::PARAM_STR);

						$stmt->execute();


						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$product_arr[]	=	$row;
						}




						if (count($product_arr) == 1)
						{

							//	One product found and everything seems to be just fine!
							//	Next step is to get the location details + stock allocated to it, if any!


							$product_uid	=	$product_arr[0]['prod_pkey'];


							//	Figure out if it is an EACH or CASE unit!
							$product_unit	=	0;	//	Wrong! Has to be at least 1 (each) or 3 (case)

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


							
							//	Run the location query!
							$sql	=	'


								SELECT

								loc_pkey,
								loc_wh_pkey,
								loc_barcode,
								
								stk_pkey,
								stk_loc_pkey,
								stk_prod_pkey,
								stk_unit,
								stk_qty,
								stk_mimic

								FROM 

								geb_location

								LEFT JOIN geb_stock ON geb_location.loc_pkey = geb_stock.stk_loc_pkey

								WHERE

								geb_location.loc_barcode = :sloc_barcode

								AND

								(geb_stock.stk_disabled IS NULL OR geb_stock.stk_disabled = 0)						


							';

							if ($user_warehouse_uid > 0)
							{
									//	Add a warehouse filter to the location!
								$sql	=	' AND	geb_location.loc_wh_pkey = :swarehouse_uid	';
							}


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':sloc_barcode',	$location_barcode,	PDO::PARAM_STR);

								//	Limit the scope of locations based on the warehouse!
								//	Again, if the user has this set to 0 = can view ANY warehouse!
								if ($user_warehouse_uid > 0)
								{
									$stmt->bindValue(':swarehouse_uid',		$user_warehouse_uid,	PDO::PARAM_INT);
								}

								$stmt->execute();


								while($row = $stmt->fetch(PDO::FETCH_ASSOC))
								{

									$locationKey = trim($row['loc_barcode']);


									// Check if the location already exists in the array, if not, create it
									if (!isset($location_arr[$locationKey]))
									{
										$location_arr[$locationKey] =
										[
											'loc_pkey' => $row['loc_pkey'],
											'loc_wh_pkey' => $row['loc_wh_pkey'],
											'loc_barcode' => $row['loc_barcode'],
											'stock_info' => [],
										];

									}

										// Add stock information to the location's stock_info array
										$location_arr[$locationKey]['stock_info'][] =
										[
											'stk_pkey' => $row['stk_pkey'],
											'stk_loc_pkey' => $row['stk_loc_pkey'],
											'stk_prod_pkey' => $row['stk_prod_pkey'],
											'stk_unit' => $row['stk_unit'],
											'stk_qty' => $row['stk_qty'],
											'stk_mimic' => $row['stk_mimic'],

										];


								}


								$message_id		=	0;	//	Everything went GREAT!


							}






						}
						elseif (count($product_arr) == 0)
						{
							//	No product found!
							$message_id		=	107203;
							$message2op		=	$mylang['product_not_found'];
						}
						elseif (count($product_arr) > 1)
						{
							//	Two or more products found with the same barcode... Needs to be fixed ASAP!
							$message_id		=	107203;
							$message2op		=	$mylang['products_found_with_the_same_barcode'];
						}





					}
					else
					{
						//	ERR
					}
		




					//$message_id		=	98;	//	all went well
					$data_results	=	$location_arr;

				}	//	if ($input_checks == 0)
				else
				{

					if ($input_checks	==	1)
					{
						$message_id		=	107203;
						$message2op		=	$mylang['barcode_too_short'];
					}
					elseif ($input_checks	==	2)
					{
						$message_id		=	107204;
						$message2op		=	$mylang['invalid_barcode'];
					}
					elseif ($input_checks	==	3)
					{
						$message_id		=	107205;
						$message2op		=	'Case barcode too short';//$mylang['barcode_to_short'];
					}
					elseif ($input_checks	==	4)
					{
						$message_id		=	107205;
						$message2op		=	'Case qty incorrect';//$mylang['barcode_to_short'];
					}


/*

				else if (is_numeric($product_qty) == false)
				{
					// Product Qty is not a number...
					$input_checks = 3;
				}
				else if ($product_qty <= 0)
				{
					// Product Qty is either 0 or negative (-1, -50, etc.). Can't insert stock that is a negative quantity!
					$input_checks = 4;
				}
*/







				}

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
		print_message_data_payload($message_id, $message2op, $data_results);
//		print_message_html_payload($message_id, $message2op, $html_results);
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
		print_message(107945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
