<?php


/*

	Error code for the script!
	Script code		=	107

 
	//	Action code breakdown
	0	:	Get location details in a form of an HTML table.
	1	:	Get product details in a form of an HTML table + check if it is in stock at that location
	2	:	Do all the usual checks and if all is good commit the changes to the DB aka Stock!


*/



// load the login class
require_once('lib_login.php');


$message_id		=	107999;		//	999:	default bad
$message2op		=	'';			//	When an error happens provide a message here. Can be something positive as well like "All done", "a-Ok"
$messageXtra	=	array();	//	Provide with extra details / info for the operator when and if required!
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


		//	Main code!
		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number



		//	Get basic details about the location based on the barcode as input!
		if
		(
			($action_code == 0)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_mpp'])
			)
			{

				$location_barcode	=	trim($_POST['loc_barcode_js']);
				$location_data		=	array();	//	Location details stored here

				$input_checks	=	666;	//	0 means all good; by default it is 666 = BAD!

				if (strlen($location_barcode) < min_location_barcode_len)	//	Set in lib_system
				{
					// location barcode doesn't meet the length requirements! 
					$input_checks = 1;
				}
				else if (is_numeric($location_barcode) == false)
				{
					// Location barcode is not numeric! Abort!
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

						geb_location.loc_pkey,
						geb_location.loc_code,

						geb_location.loc_barcode,
						geb_location.loc_function,
						geb_location.loc_type,
						geb_location.loc_blocked,
						geb_location.loc_cat_a,
						geb_location.loc_cat_b,
						geb_location.loc_cat_c,
						geb_location.loc_cat_d,
						geb_location.loc_magic_product,
						geb_location.loc_note,
						geb_location.loc_disabled

						FROM  geb_location

						INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey

						WHERE

						geb_location.loc_barcode = :sloc_barcode

						ORDER BY loc_code

					';



					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sloc_barcode',		$location_barcode,	PDO::PARAM_STR);
						$stmt->execute();


						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$location_data[]	=	$row;
						}

						// Analyise what the $location_data has to offer...
						if (count($location_data) == 1)
						{

							//	Found one location = That is good and promising!

							$loc_details_arr = decode_loc
							(
								$location_data[0]['loc_function'],
								$location_data[0]['loc_type'],
								$location_data[0]['loc_blocked'],
								$loc_function_codes_arr,
								$loc_type_codes_arr
							);

							if ($location_data[0]['loc_disabled'] == 1)
							{

								//	Location is DISABLED! NO GO!
								$message_id		=	107209;
								$message2op		=	$mylang['location_disabled'];

								$messageXtra = array(
									array($mylang['location'], $location_data[0]['loc_code'] . ' (' . $loc_details_arr[0] . ')', $loc_details_arr[1])
								);

							}
							else if ($location_data[0]['loc_blocked'] == 1)
							{

								//	Location is blocked... Add the Xtra info for the operator to see!
								$message_id		=	107209;
								$message2op		=	$mylang['location_blocked'];

								$messageXtra = array(
									array($mylang['location'], $location_data[0]['loc_code'] . ' (' . $loc_details_arr[0] . ')', $loc_details_arr[1])
								);

							}
							else
							{

								//	No more checks here. Get on with business as usual!

								foreach ($location_data as $location_arr)
								{


									$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

									$html_results	.=	'<tr>';
										$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
										$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($location_arr['loc_code']) . ' (' . $loc_details_arr[0] . ')' . '</td>';
									$html_results	.=	'</tr>';


									if (strlen(trim($location_arr['loc_note'])) > 0)
									{
										//	Add this for the operator to see. In case there is something important they need to know about 
										//	this particular location or how they should deal with the products there.
										$html_results	.=	'<tr>';
											$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['note'] . ':</td>';
											$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($location_arr['loc_note']) . '</td>';
										$html_results	.=	'</tr>';
									}




									$html_results	.=	'</table>';
								}

								//	Now it is time to provide the location input field
								$html_results	.=	'

									<div class="field has-addons is-marginless">

										<p class="control is-expanded">
											<input class="input is-fullwidth" type="text" id="product_barcode" placeholder="' . $mylang['product_barcode'] . '">
										</p>

										<p class="control">
											<button class="button inventory_class iconSearch" style="width:50px;" onClick="get_product_details();"></button>
										</p>

										<p class="control">
											<button class="button inventory_class iconFocus" style="width:50px;" onClick="clear_product_barcode();"></button>
										</p>

									</div>';


								$html_results	.=	'
								
								<script>

									set_Focus_On_Element_By_ID("location_barcode");


									function clear_product_barcode()
									{
										empty_Element_By_ID("product_details");
										empty_Element_By_ID("error_details");
										set_Element_Value_By_ID("product_barcode", "");
										set_Focus_On_Element_By_ID("product_barcode");
									}


									$("#product_barcode").keypress(function(event)
									{
										if (event.which === 13)
										{ // Check if Enter key is pressed
											event.preventDefault(); // Prevent default form submission
											get_product_details();
										}
									});	


								</script>';


								$message_id		=	0;	//	all went well


							}


						}
						elseif (count($location_data) == 0)
						{
							//	No product found!
							$message_id		=	107203;
							$message2op		=	$mylang['location_not_found'];
						}
						elseif (count($location_data) > 1)
						{
							//	Two or more locations found with the same barcode... Needs to be fixed ASAP!
							$message_id		=	107203;
							$message2op		=	$mylang['location_found_with_the_same_barcode'];
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


					//	Add any extra info that has b een provided!
					if (count($messageXtra) > 0)
					{
						//	There is some extra info here so get busy!
						foreach ($messageXtra as $xitem)
						{
							$column_style	=	'';
							if (isset($xitem[2]))
							{
								$column_style	=	$xitem[2];
							}
							
							$html_results	.=	'<tr>';
								$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $xitem[0] . ':</td>';
								$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $column_style . '">' . $xitem[1] . '</td>';
							$html_results	.=	'</tr>';
						}
					}


					$html_results	.=	'</table>';


				}



			}	//	Permissions check!


		}	//	Action 0 end!


		//	Get product details! But keep in mind that this product needs to be physically in the location (at least on the system in stock)
		//	for it to actually work.

		elseif
		(
			($action_code == 1)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_mpp'])
			)
			{





/*
						SELECT

						geb_stock.stk_unit,
						geb_stock.stk_qty,
						geb_location.loc_pkey,
						geb_location.loc_code,
						geb_location.loc_blocked,
						geb_product.prod_each_barcode,
						geb_product.prod_case_barcode

						FROM geb_stock

						INNER JOIN geb_location ON geb_stock.stk_loc_pkey = geb_location.loc_pkey
						INNER JOIN geb_product ON geb_stock.stk_prod_pkey = geb_product.prod_pkey

						WHERE

						geb_location.loc_barcode = '62631518'

						AND

						(

							geb_product.prod_each_barcode = '3849775495720'

							OR

							geb_product.prod_case_barcode = '3849775495720'
						)						
						
						AND
						
						geb_stock.stk_disabled = 0
				
*/

				
				$product_barcode	=	trim($_POST['product_barcode_js']);
				$location_barcode	=	trim($_POST['location_barcode_js']);


				$product_data		=	array();	//	Location details stored here

				$input_checks	=	666;	//	0 means all good; by default it is 666 = BAD!

				if (strlen($product_barcode) < min_each_barcode_len)	//	Set in lib_system
				{
					// location barcode doesn't meet the length requirements! 
					$input_checks = 1;
				}
				else if (is_numeric($product_barcode) == false)
				{
					// Location barcode is not numeric! Abort!
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

						geb_location.loc_pkey,
						geb_location.loc_code,

						geb_location.loc_barcode,
						geb_location.loc_function,
						geb_location.loc_type,
						geb_location.loc_blocked,
						geb_location.loc_cat_a,
						geb_location.loc_cat_b,
						geb_location.loc_cat_c,
						geb_location.loc_cat_d,
						geb_location.loc_magic_product,
						geb_location.loc_note,
						geb_location.loc_disabled

						FROM  geb_location

						INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey

						WHERE

						geb_location.loc_barcode = :sloc_barcode


						ORDER BY loc_code

					';



					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sloc_barcode',		$location_barcode,	PDO::PARAM_STR);
						$stmt->execute();


						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$location_data[]	=	$row;
						}

						// Analyise what the $location_data has to offer...
						if (count($location_data) == 1)
						{

							//	Found one location = That is good and promising!

							$loc_details_arr = decode_loc
							(
								$location_data[0]['loc_function'],
								$location_data[0]['loc_type'],
								$location_data[0]['loc_blocked'],
								$loc_function_codes_arr,
								$loc_type_codes_arr
							);

							
							if ($location_data[0]['loc_blocked'] == 1)
							{

								//	Location is blocked... Add the Xtra info for the operator to see!
								$message_id		=	107209;
								$message2op		=	$mylang['location_blocked'];

								$messageXtra = array(
									array($mylang['location'], $location_data[0]['loc_code'] . ' (' . $loc_details_arr[0] . ')', $loc_details_arr[1])
								);

							}
							else
							{

								//	No more checks here. Get on with business as usual!

								foreach ($location_data as $location_arr)
								{


									$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

									$html_results	.=	'<tr>';
										$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
										$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($location_arr['loc_code']) . ' (' . $loc_details_arr[0] . ')' . '</td>';
									$html_results	.=	'</tr>';


									if (strlen(trim($location_arr['loc_note'])) > 0)
									{
										//	Add this for the operator to see. In case there is something important they need to know about 
										//	this particular location or how they should deal with the products there.
										$html_results	.=	'<tr>';
											$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['note'] . ':</td>';
											$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($location_arr['loc_note']) . '</td>';
										$html_results	.=	'</tr>';
									}




									$html_results	.=	'</table>';
								}

								//	Now it is time to provide the location input field
								$html_results	.=	'

									<div class="field has-addons is-marginless">

										<p class="control is-expanded">
											<input class="input is-fullwidth" type="text" id="product_barcode" placeholder="' . $mylang['product_barcode'] . '">
										</p>

										<p class="control">
											<button class="button inventory_class iconSearch" style="width:50px;" onClick="get_product_details();"></button>
										</p>

										<p class="control">
											<button class="button inventory_class iconFocus" style="width:50px;" onClick="clear_product_barcode();"></button>
										</p>

									</div>';


								$html_results	.=	'
								
								<script>

									set_Focus_On_Element_By_ID("location_barcode");


									function clear_product_barcode()
									{
										empty_Element_By_ID("product_details");
										empty_Element_By_ID("error_details");
										set_Element_Value_By_ID("product_barcode", "");
										set_Focus_On_Element_By_ID("product_barcode");
									}


									$("#product_barcode").keypress(function(event)
									{
										if (event.which === 13)
										{ // Check if Enter key is pressed
											event.preventDefault(); // Prevent default form submission
											get_product_details();
										}
									});	


								</script>';


								$message_id		=	0;	//	all went well


							}


						}
						elseif (count($location_data) == 0)
						{
							//	No product found!
							$message_id		=	107203;
							$message2op		=	$mylang['location_not_found'];
						}
						elseif (count($location_data) > 1)
						{
							//	Two or more locations found with the same barcode... Needs to be fixed ASAP!
							$message_id		=	107203;
							$message2op		=	$mylang['location_found_with_the_same_barcode'];
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


					//	Add any extra info that has b een provided!
					if (count($messageXtra) > 0)
					{
						//	There is some extra info here so get busy!
						foreach ($messageXtra as $xitem)
						{
							$column_style	=	'';
							if (isset($xitem[2]))
							{
								$column_style	=	$xitem[2];
							}
							
							$html_results	.=	'<tr>';
								$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $xitem[0] . ':</td>';
								$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $column_style . '">' . $xitem[1] . '</td>';
							$html_results	.=	'</tr>';
						}
					}


					$html_results	.=	'</table>';


				}









			}	//	Permissions check!


		}	//	Action 1 end!




		//	The database / Stock changing action!
		elseif
		(
			($action_code == 2)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_mpp'])
			)
			{

				$product_barcode		=	trim($_POST['prod_barcode_js']);
				$product_qty			=	leave_numbers_only($_POST['prod_qty_js']);
				$location_barcode		=	trim($_POST['loc_barcode_js']);


				//	First param is dryrun! And it is exactly what you thinking it is :)
				//	0	:	just check, no insert, no update or anything. Just tell me if it is ok to do so!
				//	1	:	check if everything is ok and JUST insert / update etc It will give user an error
				//			if there are issues with the action he is trying to execute!

				$outcome				=	do_magic_IN(1, $db, $product_barcode, $location_barcode, $product_qty);

				$message_id				=	$outcome['control'];
				$message2op				=	$outcome['msg'];


			}	//	Permissions check!


		}	//	Action 2 end!




		//	Testing only! Can be deleted at any time!
		elseif
		(
			($action_code == 3)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_mpp'])
			)
			{

				$location_barcode	=	trim($_POST['loc_barcode_js']);
				$location_data		=	array();	//	Location details stored here

				$location_data		=	get_location_data_via_barcode($db, $location_barcode);


				if ($location_data['control'] == 0)
				{

					//	All good! 

					$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

					$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
						$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $location_data['location_arr']['loc_code_style'] . '">' . $location_data['location_arr']['loc_code_str'] . '</td>';
					$html_results	.=	'</tr>';


					if (strlen($location_data['location_arr']['loc_note']) > 0)
					{
						//	Add this for the operator to see. In case there is something important they need to know about 
						//	this particular location or how they should deal with the products there.
						$html_results	.=	'<tr>';
							$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['note'] . ':</td>';
							$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $location_data['location_arr']['loc_note'] . '</td>';
						$html_results	.=	'</tr>';
					}


					$html_results	.=	'</table>';



					//	Now it is time to provide the location input field
					$html_results	.=	'

						<div class="field has-addons is-marginless">

							<p class="control is-expanded">
								<input class="input is-fullwidth" type="text" id="product_barcode" placeholder="' . $mylang['product_barcode'] . '">
							</p>

							<p class="control">
								<button class="button inventory_class iconSearch" style="width:50px;" onClick="get_product_details();"></button>
							</p>

							<p class="control">
								<button class="button inventory_class iconFocus" style="width:50px;" onClick="clear_product_barcode();"></button>
							</p>

						</div>';


					$html_results	.=	'
					
					<script>

						set_Focus_On_Element_By_ID("location_barcode");


						function clear_product_barcode()
						{
							empty_Element_By_ID("product_details");
							empty_Element_By_ID("error_details");
							set_Element_Value_By_ID("product_barcode", "");
							set_Focus_On_Element_By_ID("product_barcode");
						}


						$("#product_barcode").keypress(function(event)
						{
							if (event.which === 13)
							{ // Check if Enter key is pressed
								event.preventDefault(); // Prevent default form submission
								get_product_details();
							}
						});	


					</script>';


					$message_id		=	0;	//	all went well



				}	//	END OF: if ($input_checks == 0)
				else
				{

					//	Something went sideways! Do the thing!
					$message_id		=	$location_data['control'];
					$message2op		=	$location_data['msg'];
					$messageXtra	=	$location_data['xtra'];
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


					//	Add any extra info that has b een provided!
					if (count($messageXtra) > 0)
					{
						//	There is some extra info here so get busy!
						foreach ($messageXtra as $xitem)
						{
							$column_style	=	'';
							if (isset($xitem[2]))
							{
								$column_style	=	$xitem[2];
							}
							
							$html_results	.=	'<tr>';
								$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $xitem[0] . ':</td>';
								$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $column_style . '">' . $xitem[1] . '</td>';
							$html_results	.=	'</tr>';
						}
					}


					$html_results	.=	'</table>';


				}



			}	//	Permissions check!



		}	//	Action 2 end!









	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	107666;
	}


	$db	=	null;




	switch ($action_code) {
		case 0:	//	Grab product details!
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Grab location details!
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 2:	//	Make changes to the database aka Stock!
		print_message($message_id, $message2op);
		break;
		case 3:	//	Experimental. Delete me after you are done!
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		default:
		print_message(107945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}


?>