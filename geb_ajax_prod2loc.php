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
		//	With a different number it will allow to INSERT / UPDATE stock!
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




				$loc_arr			=	array();	//	Location details stored here
				$stock_arr			=	array();	//	Stock details within a location
				$product_arr		=	array();	//	Product details stored here

				$mimic				=	0;	//	scanned barcode is not a Mimic by default!


				$product_uid		=	0;	//	A query will obtain this using the provided product barcode!
				$product_barcode	=	trim($_POST['prod_barcode_js']);
				$product_qty		=	leave_numbers_only($_POST['prod_qty_js']);
				$location_barcode	=	trim($_POST['loc_barcode_js']);

				//	Used to determine what to show based on users warehouse settings.
				//	Keep in mind that a value of 0 means ALL warehouses! So only apply a filter when the value is <> 0
				//	Maybe also add a check to see it if even has been set????
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

						prod_pkey,
						prod_category_a,
						prod_category_b,
						prod_category_c,
						prod_mimic,
						prod_each_barcode,
						prod_each_barcode_mimic,
						prod_case_barcode,
						prod_case_barcode_mimic,
						prod_case_qty,
						prod_case_qty_mimic


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


						$stmt->closeCursor();


						if (count($product_arr) == 1)
						{

							//	One product found and everything seems to be just fine!

							$product_uid	=	leave_numbers_only($product_arr[0]['prod_pkey']);
							$final_prod_qty	=	0;	//	by default! The final quantity that will be INSERTED / UPDATED!

							//	Figure out if it is an EACH or CASE unit via the provided barcode!
							$product_unit	=	0;	//	Wrong! Has to be at least 1 (each) or 3 (case)

							if		(strcmp($product_barcode, trim($product_arr[0]['prod_each_barcode'])) === 0 )
							{
								$product_unit	=	1;
								$final_prod_qty	=	$product_qty;
							}
							elseif	(strcmp($product_barcode, trim($product_arr[0]['prod_each_barcode_mimic'])) === 0 )
							{
								$product_unit	=	1;
								//	Always in EACH and describes the total amount that will be INSERTED / UPDATED in the location!
								$final_prod_qty	=	$product_qty;
								$mimic			=	1;	//	Totally a mimic product using the mimic each barcode!
							}
							elseif	(strcmp($product_barcode, trim($product_arr[0]['prod_case_barcode'])) === 0 )
							{
								$product_unit	=	3;
								//	Always in EACH and describes the total amount that will be INSERTED / UPDATED in the location!
								$final_prod_qty	=	leave_numbers_only($product_arr[0]['prod_case_qty']) * $product_qty;	//	Mimic case qty!
							}
							elseif	(strcmp($product_barcode, trim($product_arr[0]['prod_case_barcode_mimic'])) === 0 )
							{
								$product_unit	=	3;
								//	Always in EACH and describes the total amount that will be INSERTED / UPDATED in the location!
								$final_prod_qty	=	leave_numbers_only($product_arr[0]['prod_case_qty_mimic']) * $product_qty;	//	Mimic case qty!
								$mimic			=	1;	//	Totally a mimic product using the mimic case barcode and new case qty!
							}


							//	Run the location query!
							$sql	=	'


								SELECT

								loc_pkey,
								loc_wh_pkey,
								loc_code,
								loc_function,
								loc_type,
								loc_blocked,
								loc_cat_a,
								loc_cat_b,
								loc_cat_c,
								loc_magic_product,

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

								geb_location.loc_disabled = 0

								AND

								(geb_stock.stk_disabled IS NULL OR geb_stock.stk_disabled = 0)



							';

							if ($user_warehouse_uid > 0)
							{
									//	Add a warehouse filter to the location!
								$sql	.=	' AND geb_location.loc_wh_pkey = :swarehouse_uid ';
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


								$product_ids_arr		=	array();	//	All IDs of the products in the location stored here!


								while($row = $stmt->fetch(PDO::FETCH_ASSOC))
								{


									// Check if the location already exists in the array, if not, create it
									if (!isset($loc_arr[0]))
									{
										$loc_arr[0] =
										[
											'loc_pkey'				=>	$row['loc_pkey'],
											'loc_wh_pkey'			=>	$row['loc_wh_pkey'],
											'loc_code'				=>	$row['loc_code'],
											'loc_function'			=>	$row['loc_function'],
											'loc_type'				=>	$row['loc_type'],
											'loc_cat_a'				=>	$row['loc_cat_a'],
											'loc_cat_b'				=>	$row['loc_cat_b'],
											'loc_cat_c'				=>	$row['loc_cat_c'],
											'loc_magic_product'		=>	$row['loc_magic_product'],
											'loc_blocked'			=>	$row['loc_blocked']
										];

									}


									//	Only add if the location has a product allocated to it!
									if (leave_numbers_only($row['stk_pkey']) > 0)
									{

										$stock_arr[] =
										// Add stock information to the location's stock_info array
										[
											'stk_pkey'		=>	$row['stk_pkey'],
											'stk_loc_pkey'	=>	$row['stk_loc_pkey'],
											'stk_prod_pkey' =>	$row['stk_prod_pkey'],
											'stk_unit'		=>	$row['stk_unit'],
											'stk_qty'		=>	$row['stk_qty'],
											'stk_mimic'		=>	$row['stk_mimic']
										];
										array_push($product_ids_arr, leave_numbers_only($row['stk_prod_pkey']));
									}



								}


								if (!isset($loc_arr[0]))
								{
									//	Provided location barcode does not match to anything!
									$message_id		=	21;
									$message2op		=	'Location not found';
									$html_results	=	$message2op;
								}
								elseif ($loc_arr[0]['loc_blocked'] > 0)
								{
									//	Location has been blocked.
									$message_id		=	20;
									$message2op		=	'Location blocked!';
								}
								else
								{

									$product_ids_arr	=	array_unique($product_ids_arr);		//	Total number of products in location!
									$product_count		=	count($product_ids_arr);
									$location_type		=	$loc_arr[0]['loc_type'];


									// SINGLE LOCATION Checks!
									if
									(
									
										($location_type == 10)

										OR

										($location_type == 11)

										OR

										($location_type == 12)

									)
									{


										//	Check if location if empty or has a product inside of it.
										//	Since this is a SINGLE location I do not have to worry about any UPDATEs to
										//	the geb_stock table.
										if ($product_count == 0)
										{

											//	No product in this SINGLE location!
											//	Check if the SINGLE location allows for this particular product unit to be inserted!
											//	Example: If the product is a case and the SINGLE is 11 (Each) = Can't go further!

											if
											(
												($location_type == 10)

												OR

												($location_type == 11 AND $product_unit == 1)

												OR

												($location_type == 12 AND $product_unit == 3)
											)
											{


												//	Check if the product is MAGICAL... 
												if
												(

													($loc_arr[0]['loc_magic_product'] == $product_uid)

													OR
													
													(location_category_check($loc_arr, $product_arr))

												)
												{

													//	Generate the HTML table with all of the details so far!
													//	Operator will be given options here. Accept or Abort!
													
													
													
													
													/*
													
													
													//	No further checks needed! Product can be allocated to this location!

													$sql	=	'


														INSERT
														
														INTO

														geb_stock
														
														(
															stk_loc_pkey,
															stk_prod_pkey,
															stk_unit,
															stk_qty,
															stk_mimic
														) 

														VALUES

														(
															:istk_loc_pkey,
															:istk_prod_pkey,
															:istk_unit,
															:istk_qty,
															:istk_mimic
														)

													';





													if ($stmt = $db->prepare($sql))
													{

														$stmt->bindValue(':istk_loc_pkey',		$loc_arr[0]['loc_pkey'],	PDO::PARAM_INT);
														$stmt->bindValue(':istk_prod_pkey',		$product_uid,				PDO::PARAM_INT);
														$stmt->bindValue(':istk_unit',			$product_unit,				PDO::PARAM_INT);
														$stmt->bindValue(':istk_qty',			$final_prod_qty,			PDO::PARAM_INT);
														$stmt->bindValue(':istk_mimic',			$mimic,						PDO::PARAM_INT);

														$stmt->execute();
														$db->commit();

														$message_id		=	0;	//	all went well
														$message2op		=	$mylang['success'];
													}
*/
														$message_id		=	0;	//	all went well
														$message2op		=	$mylang['success'];

												}
												else
												{
													$message_id		=	107203;
													$message2op		=	$mylang['category_mismatch'];
												}


											}
											else
											{
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
											}


										}	//	if ($product_count == 0)
										else
										{
											$message_id		=	107203;
											$message2op		=	$mylang['location_full'];
										}



										//	Decode the location code into something more use friendly!
										$loc_details_arr = decode_loc
										(
											$loc_arr[0]['loc_function'],
											$loc_arr[0]['loc_type'],
											$loc_arr[0]['loc_blocked'],
											$loc_function_codes_arr,
											$loc_type_codes_arr
										);


										//	Generate the HTML Table for the operator!
										$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';
/*
											$html_results	.=	'<tr>';
												$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['error'] . ':</td>';
												$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $message2op . '</td>';
											$html_results	.=	'</tr>';
*/


/*
											$html_results	.=	'<tr>';
												$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
												$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $loc_details_arr[1] . '">' . $loc_arr[0]['loc_code'] . ' (' . $loc_details_arr[0] . ')</td>';
											$html_results	.=	'</tr>';
*/








/*
											$html_results	.=	'<tr>';
												$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['function'] . ':</td>';
												$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_functions_arr[$loc_arr[0]['loc_function']] . '</td>';
											$html_results	.=	'</tr>';
*/




										$html_results	.=	'</table>';



									}
									elseif	//	MULTI LOCATION Checks!
									(
									
										($location_type == 20)

										OR

										($location_type == 21)

										OR

										($location_type == 22)

									)
									{
										
										
									}
									elseif	//	MULTI MIXED LOCATION Checks!
									(
									
										($location_type == 30)

										OR

										($location_type == 31)

										OR

										($location_type == 32)

									)
									{
										
										
									}



















								}	//	location checks end here!



							}
							else
							{
								//	SQL error of some kind here!
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
						//	SQL ERR
					}
		




					//$message_id		=	98;	//	all went well
					//$data_results	=	$loc_arr;

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

				}




			}	//	Permissions check!


		}	//	Action 1 end!









	}
	catch(PDOException $e)
	{
		//$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	107666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab product details
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Grab location details
//		print_message_data_payload($message_id, $message2op, $data_results);
		print_message_html_payload($message_id, $message2op, $html_results);
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
