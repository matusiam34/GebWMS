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


		//	Get location details in HTML format for the operator!
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
				$location_data		=	array();	//	Location details with stock and others fields!
				$location_arr		=	array();	//	Location info only!
				$stock_arr			=	array();	//	All products with relevant details!

				$location_data		=	get_location_data_via_barcode($db, $location_barcode, $user_company_uid, $user_warehouse_uid);


				if ($location_data['control'] == 0)
				{

					//	Make sure that the location is not empty because you can't pick from that!


					$location_arr		=	$location_data['location_arr'];


					if (count($location_data['stock_arr']) > 0)
					{

						$stock_arr	=	$location_data['stock_arr'];

						//	Provide a table with the general location details.

						$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

						$html_results	.=	'<tr>';
							$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
							$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $location_arr['loc_code_style'] . '">' . $location_arr['loc_code_str'] . '</td>';
						$html_results	.=	'</tr>';


						if (strlen($location_data['location_arr']['loc_note']) > 0)
						{
							//	Add this for the operator to see. In case there is something important they need to know about 
							//	this particular location or how they should deal with the products there.
							$html_results	.=	'<tr>';
								$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['note'] . ':</td>';
								$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $location_arr['loc_note'] . '</td>';
							$html_results	.=	'</tr>';
						}


						$html_results	.=	'</table>';



						//	Product barcode input
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

							set_Focus_On_Element_By_ID("product_barcode");


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
								{
									// Check if Enter key is pressed
									event.preventDefault();		// Prevent default form submission
									get_product_details();
								}
							});	


						</script>';





						$message_id		=	0;	//	all went well


					}
					else
					{
						//	Location Empty... Deliver a message!
						$message_id		=	127612;
						$message2op		=	$mylang['location_empty'];

						//	Provide extra details to the operator!
						$messageXtra = array(
							array($mylang['location'], $location_arr['loc_code_str'], $location_arr['loc_code_style'])
						);

					}



				}	//	END OF: if ($location_data['control'] == 0)
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





		}	//	Action 0 end!
		elseif
		(
			($action_code == 1)
		)
		{

			//	Get product details based on barcode. Generate a line for the product with the input, DEC and INC buttons


			if
			(
				is_it_enabled($_SESSION['menu_mpp'])
			)
			{


				$location_barcode	=	trim($_POST['loc_barcode_js']);
				$product_barcode	=	trim($_POST['prod_barcode_js']);

				$location_data		=	array();	//	Location details with stock and others fields!
				$location_arr		=	array();	//	Location info only!
				$stock_arr			=	array();	//	All products with relevant details!

				$location_data		=	get_location_data_via_barcode($db, $location_barcode, $user_company_uid, $user_warehouse_uid);


				if ($location_data['control'] == 0)
				{

					//	Make sure that the location is not empty because you can't pick from that!


					$location_arr		=	$location_data['location_arr'];


					if (count($location_data['stock_arr']) > 0)
					{

						$stock_arr	=	$location_data['stock_arr'];

						//	Provide a table with the general location details.

						$html_results	.=	'<table class="is-fullwidth table is-bordered is-marginless">';

						$html_results	.=	'<tr>';
							$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
							$html_results	.=	'<td style="background-color: ' . $backclrB . '; ' . $location_arr['loc_code_style'] . '">' . $location_arr['loc_code_str'] . '</td>';
						$html_results	.=	'</tr>';


						if (strlen($location_data['location_arr']['loc_note']) > 0)
						{
							//	Add this for the operator to see. In case there is something important they need to know about 
							//	this particular location or how they should deal with the products there.
							$html_results	.=	'<tr>';
								$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['note'] . ':</td>';
								$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $location_arr['loc_note'] . '</td>';
							$html_results	.=	'</tr>';
						}


						$html_results	.=	'</table>';



						//	Product barcode input
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

							set_Focus_On_Element_By_ID("product_barcode");


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
								{
									// Check if Enter key is pressed
									event.preventDefault();		// Prevent default form submission
									get_product_details();
								}
							});	


						</script>';





						$message_id		=	0;	//	all went well


					}
					else
					{
						//	Location Empty... Deliver a message!
						$message_id		=	127612;
						$message2op		=	$mylang['location_empty'];

						//	Provide extra details to the operator!
						$messageXtra = array(
							array($mylang['location'], $location_arr['loc_code_str'], $location_arr['loc_code_style'])
						);

					}



				}	//	END OF: if ($location_data['control'] == 0)
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


		}	//	Action 1 end!

		elseif
		(
			($action_code == 2)
		)
		{


			//	Get the array of products and qty that the operator needs to pick and try and update the stock!

			if
			(
				is_it_enabled($_SESSION['menu_mpp'])
			)
			{


				$loc_barcode			=	trim($_POST['loc_barcode_js']);
				$products_arr			=	$_POST['productData_js'];			//	stuff to be taken out from the location!


				$message_id				=	0;

				$message2op				=	$loc_barcode;




/*

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

*/

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
		case 0:	//	Grab location details!
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Grab product details (individual)
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