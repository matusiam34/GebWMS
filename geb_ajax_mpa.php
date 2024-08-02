<?php

//	NOTE:	add qty info for mimics in the product details table (frontend HTML). This will be very good to let the operator know that they have 
//			encountered a mimic and could check the case qty if needed!

/*

	Error code for the script!
	Script code		=	107

 
	//	Action code breakdown
	0	:	Get product details in a form of an HTML table.
	1	:	Get location details in a form of an HTML table + Perform checks if the product and location is compatible!
	2	:	Do all the usual checks and if all is good commit the changes to the DB aka Stock!


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
				is_it_enabled($_SESSION['menu_mpa'])
			)
			{
			
				$product_barcode	=	trim($_POST['prod_barcode_js']);	//	This should be a string of numbers... should be!
				$product_data		=	array();							//	Product details stored here


				//	Do all the checks and grab all of the product data (whenever possible that is)
				$outcome		=	get_product_data_via_barcode($db, $product_barcode, $user_company_uid);

				$message_id		=	$outcome['control'];
				$message2op		=	$outcome['msg'];
				//	Maybe leverage this later... I would like it to be consistent but for now it is what it is!
				//$messageXtra	=	$outcome['xtra'];


				//	Show the error / info line only when something did not go according to plan!
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



				//	Sounds like all went well!
				//	Query run well and got the relevant product details! Great!
				if ($message_id == 0)
				{

					//	All data about the product here...
					$product_data	=	$outcome['product_arr'];
					$product_unit	=	$outcome['unit'];

					$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';


					$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['product_code'] . '</td>';
						$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($product_data[0]['prod_code']) . '</td>';
					$html_results	.=	'</tr>';

					$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['description'] . '</td>';
						$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($product_data[0]['prod_desc']) . '</td>';
					$html_results	.=	'</tr>';




					$qty_adjust_input	=	'';


					$input_disabled	=	' readonly ';	// for kicks lets make the product_qty read only! this could change after revision!

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


					$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['unit'] . '</td>';
						$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $stock_unit_type_arr[$product_unit] . '</td>';
					$html_results	.=	'</tr>';


					$html_results	.=	'</table>';



					//	Now it is time to provide the location input field
					$html_results	.=	'

						<div class="field has-addons is-marginless">

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
							empty_Element_By_ID("location_details");
							empty_Element_By_ID("error_details");
							set_Element_Value_By_ID("location_barcode", "");
							set_Focus_On_Element_By_ID("location_barcode");
						}


						$("#location_barcode").keypress(function(event)
						{
							if (event.which === 13)
							{ // Check if Enter key is pressed
								event.preventDefault(); // Prevent default form submission
								get_location_details();
							}
						});	


					</script>';



				}	//	$message_id == 0 END





			}	//	Permissions check!


		}	//	Action 0 end!


		//	Check if location and product is ok to update or insert to stock!
		//	Will provide a HTML for the operator with the potential error or a prompt
		//	DIV to Cancel or COMMIT the action.
		elseif
		(
			($action_code == 1)
		)
		{


			if
			(
				is_it_enabled($_SESSION['menu_mpa'])
			)
			{

				$product_barcode		=	trim($_POST['prod_barcode_js']);
				$product_qty			=	leave_numbers_only($_POST['prod_qty_js']);
				$location_barcode		=	trim($_POST['loc_barcode_js']);


				//	First param is dryrun! And it is exactly what you thinking it is :)
				//	0	:	just check, no insert, no update or anything. Just tell me if it is ok to do so!
				//	1	:	check if everything is ok and JUST insert / update etc It will give user an error
				//			if there are issues with the action he is trying to execute!

				$outcome				=	do_magic_IN(0, $db, $product_barcode, $location_barcode, $product_qty, $user_company_uid, $user_warehouse_uid);

				$message_id				=	$outcome['control'];
				$message2op				=	$outcome['msg'];
				$messageXtra			=	$outcome['xtra'];


				//	Show the error / info line only when something did not go according to plan!
				if ($message_id > 0)
				{

					//	Generate the HTML Table for the operator!
					$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

					$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['error'] . ':</td>';
						$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $message_id . '</td>';
					$html_results	.=	'</tr>';

					$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['description'] . ':</td>';
						$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $message2op . '</td>';
					$html_results	.=	'</tr>';


					//	Add any extra info that has been provided!
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


				if ($message_id == 0)
				{


					//	Add any extra info that has been provided! Here this will be in a form of a table!
					if (count($messageXtra) > 0)
					{

						$html_results	=	'<table class="is-fullwidth table is-bordered is-marginless">';

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

						$html_results	.=	'</table>';

					}


					// Declare the two buttons that I will need for the final step.
					$confirm_lnk	=	'<a class="button is-fullwidth green_class" onClick="confirm_action();">' . strtoupper($mylang['confirm']) . '</a>';
					$cancel_lnk		=	'<a class="button is-fullwidth red_class" onClick="clear_product_barcode();">' . strtoupper($mylang['cancel']) . '</a>';

					//	The checks are ok, so generate a tiny table with CANCEL / CONFIRM buttons for the operator to commit their action!
					$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';
						$html_results	.=	'<tr>';
						$html_results	.=	'<td style="width:49%; text-align:center; font-weight:bold;">' . $confirm_lnk . '</td>';
						$html_results	.=	'<td style="width:49%; text-align:center; font-weight:bold;">' . $cancel_lnk . '</td>';
						$html_results	.=	'</tr>';
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
				is_it_enabled($_SESSION['menu_mpa'])
			)
			{

				$product_barcode		=	trim($_POST['prod_barcode_js']);
				$product_qty			=	leave_numbers_only($_POST['prod_qty_js']);
				$location_barcode		=	trim($_POST['loc_barcode_js']);


				//	First param is dryrun! And it is exactly what you thinking it is :)
				//	0	:	just check, no insert, no update or anything. Just tell me if it is ok to do so!
				//	1	:	check if everything is ok and JUST insert / update etc It will give user an error
				//			if there are issues with the action he is trying to execute!

				$outcome				=	do_magic_IN(1, $db, $product_barcode, $location_barcode, $product_qty, $user_company_uid, $user_warehouse_uid);

				$message_id				=	$outcome['control'];
				$message2op				=	$outcome['msg'];


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
		default:
		print_message(107945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}


?>