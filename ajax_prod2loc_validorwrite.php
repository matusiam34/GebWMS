<?php

/*

	All of this needs more work...

	Error code explained:

	666:	try could not be established 
	100:	user access control permission error (most likely no rights to whatever they are accessing)
	101:	could not get data
	102:	Product query failed
	103:	Product not found
	104:	INSERT of the stock into location failed BAD!
	105:	stock UPDATE of the location failed BAD!

	108:	Location blocked (or just not found?)

*/


// Ugly fix... checks if the location is compatible with the product that the operator is trying to put away!
// Need to check if the location is single only while someone is trying to jam in a second CASE...
// You get the picture.


// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once('lib_passwd.php');

// include the configs / constants for the database connection
require_once('lib_db.php');

// load the login class
require_once('lib_login.php');



// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{


	$message2op		=	'';		// a little message that will be provided to the operator with errors, success etc
	$message_id		=	100999;	// error / success code. 999 is the default as in EVERYTHING went sideways. 0 = success 
	$html_results	=	'';		// all HTML output will be populated into this variable before it is sent back.

	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');



		// allow to execute script only if the requirements are met !
		// min_priv : variable that holds the lowest level user that can access and execute this script
		if 
		(

			(

				(can_user_access($_SESSION['user_inventory']))

			)

			AND

			(leave_numbers_only($_SESSION['user_priv']) >=	min_priv)

		)
		{




			//
			// Data I receive to process the request
			//

			// remove anything that is not a number!
			$prod_barcode	=	leave_numbers_only($_POST['prod_barcode_js']);
			$prod_qty		=	leave_numbers_only($_POST['prod_qty_js']);
			$prod_id		=	leave_numbers_only($_POST['prod_id_js']);
			$loc_barcode	=	leave_numbers_only($_POST['loc_barcode_js']);


			$valid_or_write	=	leave_numbers_only($_POST['validorwrite_js']);

			// Before anything check if there is any option selected (0: validate; 1: update location)




			// some variables to help me out to validate the entire process
			$product_found	=	false;



			// Grab product details based on the UID and barcode.
			$prod_stock_unit		=	$stock_unit_type_reverse_arr['E'];	// by default assume EACH!
			$loc_data_arr			=	array();	// every entry from the location will be stored here


			// THese two are like more serious error and they will show up as an alert (the ugly one)
			// In the future I might change it to some kind of <div> or <p> element style notification. Not priority since
			// these errors will most likely occur because of some tempering.
			if ($prod_qty < 1)
			{
				$message2op		=	'Product Qty <= 0';
				$message_id		=	100110;
			}
			else if ($prod_id < 1)
			{
				$message2op		=	'Product ID <= 0';
				$message_id		=	100111;
			}
			else
			{



			// Just before the first query?
			$db->beginTransaction();



			if ($stmt = $db->prepare('


				SELECT

				*

				FROM 

				geb_product

				WHERE

				(prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar)

				AND

				prod_pkey = :iprod_uid


			'))
			{

				$stmt->bindValue(':iprod_each_bar',	$prod_barcode,		PDO::PARAM_STR);
				$stmt->bindValue(':iprod_case_bar',	$prod_barcode,		PDO::PARAM_STR);
				$stmt->bindValue(':iprod_uid',		$prod_id,			PDO::PARAM_INT);
				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{

					//
					// There only should be one product found
					//

					if (strcmp(trim($row['prod_each_barcode']), $prod_barcode) === 0)
					{
						// No need for this is there? Assume EACH one more time?
					}

					if (strcmp(trim($row['prod_case_barcode']), $prod_barcode) === 0)
					{
						// A case has been scanned
						$prod_stock_unit		=	$stock_unit_type_reverse_arr['C'];
						// Assign the proper Qty
						//$scanned_qty	=	trim($row['prod_case_qty']);
					}

					$product_found	=	true;

				}


				if ($product_found)
				{

					// Everything is going smooth. Now go to the next phase...

					if ($stmt = $db->prepare('


						SELECT

						geb_location.loc_pkey,
						geb_location.loc_code,
						geb_location.loc_type,
						geb_location.loc_blocked,
						geb_location.loc_note,

						geb_stock.stk_loc_pkey,
						geb_stock.stk_pkey,
						geb_stock.stk_prod_pkey,
						geb_stock.stk_unit,
						geb_stock.stk_qty


						FROM geb_location


						LEFT JOIN geb_stock ON geb_location.loc_pkey = geb_stock.stk_loc_pkey AND geb_location.loc_disabled = geb_stock.stk_disabled


						WHERE

						geb_location.loc_disabled = 0

						AND

						geb_location.loc_barcode = :iloc


					'))
					{



						$stmt->bindValue(':iloc',	$loc_barcode,		PDO::PARAM_STR);
						$stmt->execute();


						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{

							$loc_data_arr[]		=	$row;

						}

						//
						//
						$allow_item	=	false;	// if true means all checks are correct and the item can be inserted into the location!
						//
						//


						$error_msg	=	'';		// feedback to the operator based on location and product checks


						// Set some default variables before processing the array.
						$loc_blocked	=	1;	// by default it is blocked!
						$loc_type		=	$loc_types_codes_reverse_arr['S'];	// single by default!


// Ugly hack for now.
// count($loc_data_arr) == 0 means nothing was returned from the query = no location at ALL!
if (count($loc_data_arr) > 0)
{

						// Get the location data from the array. Keep in mind that this is a LEFT JOIN so even if there is
						// no product in the specified location you will still get the location config.
						// This is exactly what I am going to exploit here.
						$loc_code			=	trim($loc_data_arr[0]['loc_code']);						// this can be a mix of characters and numbers
						$loc_type			=	leave_numbers_only($loc_data_arr[0]['loc_type']);		// I expect numbers here only
						$loc_blocked		=	leave_numbers_only($loc_data_arr[0]['loc_blocked']);	// I expect numbers here only
						$loc_item_count		=	count($loc_data_arr);	// 1 = at least location data, 2 = location data and two items, 3 = location data and three items
						$loc_row_one_item	=	leave_numbers_only($loc_data_arr[0]['stk_prod_pkey']);	// expecting a product pkey here

						$loc_pkey			=	leave_numbers_only($loc_data_arr[0]['loc_pkey']);	// location pkey in the geb_location table


						// Start checking
						if ($loc_blocked == 0)
						{

							// Location open for business. Proceed with further checks

							if ($loc_item_count == 1)
							{
								// There can be NO stock here or can be one item in this location

								if ($loc_row_one_item == NULL)
								{
									// Location empty = an item can be inserted here! Can do an E, C, P check in the future... 
									// Or even if a specific product or range is allocated to this location... That would be cool!
									$allow_item		=	true;
									$error_msg		=	'a-Ok!';
								}
								else if ($loc_row_one_item > 0)
								{
									// Location has one item here
									// Need to find out if it is a Single, Multi or Mixed location now...

									if ($loc_type == $loc_types_codes_reverse_arr['S'])
									{
										// Single... I can't insert anything into this location so...
										$error_msg	=	'Only one item allowed';
									}
									else if ($loc_type == $loc_types_codes_reverse_arr['M'])
									{
										// Ok, location is a Multi. Now I need to check if the product the operator is trying to insert
										// is the same as the product that is already in this location.
										if ($loc_row_one_item == $prod_id)
										{
											// Location product and product the operator wants to put here are the same.
											// Allow the operation to happen.
											$allow_item		=	true;
											$error_msg		=	'a-Ok!';
										}
										else
										{
											$error_msg	=	'Mixing items not allowed';	
										}

									}
									else if ($loc_type == $loc_types_codes_reverse_arr['X'])
									{
										$allow_item		=	true;
									}

								}

							}
							else if ($loc_item_count > 1)
							{
								// Multiple items in this location. At least 2 (because loc details exist in each line + item details)

								if ($loc_type == $loc_types_codes_reverse_arr['M'])
								{
									// Ok, location is a Multi. Now I need to check if the product the operator is trying to insert
									// is the same as the product that is already in this location.
									if ($loc_row_one_item == $prod_id)
									{
										// Location product and product the operator wants to put here are the same.
										// Allow the operation to happen.
										$allow_item		=	true;
										$error_msg		=	'a-Ok!';
									}
									else
									{
										$error_msg	=	'Mixing items not allowed';	
									}

								}
								else if ($loc_type == $loc_types_codes_reverse_arr['X'])
								{
									$allow_item		=	true;
									$error_msg		=	'a-Ok!';
								}

							}

						}
						else
						{
							$error_msg	=	'Location blocked';		// can probably do this a different way. Good enough for now!
							$message_id		=	100108;
						}


						// Now... if you are here I need to check if I should update the location with the scanned item.

						if (($valid_or_write == 1) AND ($allow_item))
						{

							// Need to update the table.
							// Iterate the location array and find if the stock type is already there.
							// If not then I need to perform an INSERT. Otherwise perform an UPDATE (one entry already exists).
							// Only pallets will deserve to have one entry per item due to their big nature and the fact that
							// most likely the warehouse will be printing an individual label for each single one of them 
							// which makes tracking their lifecycle in the warehouse much easier.


							// Find an entry with the same product ID and stock unit (EACH, CASE, PALLET)
							$found_row	=	false;
							$row_uid	=	0;		// by default 0; Only really useful when UPDATING a row.

							foreach ($loc_data_arr as $item)
							{
								if
								(
									($item['stk_prod_pkey'] == $prod_id)

									AND

									($item['stk_unit'] == $prod_stock_unit)
								)
								{
									// Seems like an entry exists. This means an UPDATE will be served
									$found_row	=	true;
									$row_uid	=	leave_numbers_only($item['stk_pkey']);
								}
							}

							if ($found_row)
							{


								// Perform the UPDATE
								if ($stmt = $db->prepare('

								UPDATE

								geb_stock

								SET

								stk_qty		=	stk_qty + :istk_qty

								WHERE

								stk_pkey	 =	:istk_pkey


								'))


								{

									$stmt->bindValue(':istk_qty',	$prod_qty,		PDO::PARAM_INT);
									$stmt->bindValue(':istk_pkey',	$row_uid,		PDO::PARAM_INT);
									$stmt->execute();


									//
									//	All of this below could be potentially fixed with a function that can be just
									//	called on request. Something to think about in the future.
									//
									//	If you are here means that everything has been doing well and now it is time to
									//	adjust the prod_phy_qty field in geb_product table
									//

									// Perform the Physical Qty field UPDATE in geb_product table
									if ($stmt = $db->prepare('

									UPDATE

									geb_product

									SET

									prod_phy_qty		=	prod_phy_qty + :iphy_qty

									WHERE

									prod_pkey	 =	:iprod_id


									'))

									{

										$stmt->bindValue(':iphy_qty',	$prod_qty,		PDO::PARAM_INT);
										$stmt->bindValue(':iprod_id',	$prod_id,		PDO::PARAM_INT);
										$stmt->execute();

										// make sure to commit all of the changes to the DATABASE !
										// Run this after everything has been applied!
										$db->commit();

										$message2op	=	'';	// When everything runs smooth there is no need for an error msg!
										$message_id	=	0;	// Seems like everything went well!

									}
									else
									{
										// it went south...
										$message2op		=	'Product Physical Qty UPDATE Failed';
										$message_id		=	100106;
									}


								}
								else
								{
									// it went south...
									$message2op		=	'Stock UPDATE Failed';
									$message_id		=	100105;
								}


							}
							else
							{
								// INSERT

								if ($stmt = $db->prepare('


								INSERT
								
								INTO

								geb_stock
								
								(
									stk_loc_pkey,
									stk_prod_pkey,
									stk_unit,
									stk_qty
								) 

								VALUES

								(
									:istk_loc_pkey,
									:istk_prod_pkey,
									:istk_unit,
									:istk_qty
								)


								'))


								{


									$stmt->bindValue(':istk_loc_pkey',		$loc_pkey,				PDO::PARAM_INT);
									$stmt->bindValue(':istk_prod_pkey',		$prod_id,				PDO::PARAM_INT);
									$stmt->bindValue(':istk_unit',			$prod_stock_unit,		PDO::PARAM_INT);
									$stmt->bindValue(':istk_qty',			$prod_qty,				PDO::PARAM_INT);
									$stmt->execute();


									//
									//	All of this below could be potentially fixed with a function that can be just
									//	called on request. Something to think about in the future.
									//
									//	If you are here means that everything has been doing well and now it is time to
									//	adjust the prod_phy_qty field in geb_product table
									//

									// Perform the Physical Qty field UPDATE in geb_product table
									if ($stmt = $db->prepare('

									UPDATE

									geb_product

									SET

									prod_phy_qty		=	prod_phy_qty + :iphy_qty

									WHERE

									prod_pkey	 =	:iprod_id


									'))

									{

										$stmt->bindValue(':iphy_qty',	$prod_qty,		PDO::PARAM_INT);
										$stmt->bindValue(':iprod_id',	$prod_id,		PDO::PARAM_INT);
										$stmt->execute();

										// make sure to commit all of the changes to the DATABASE !
										// Run this after everything has been applied!
										$db->commit();

										$message2op	=	'';	// When everything runs smooth there is no need for an error msg!
										$message_id	=	0;	// Seems like everything went well!

									}
									else
									{
										// it went south...
										$message2op		=	'Product Physical Qty UPDATE Failed';
										$message_id		=	100107;
									}


								}
								else
								{
									// it went south...
									$message2op		=	'Stock INSERT Failed';
									$message_id		=	100104;
								}



							}



						}
						else
						{

							// This is the part where the product is verified against the location policy to 
							// provide feedback if it is suitable to put it away here. No INSERT or UPDATE.
							// Just a table with some details back to the operator.


							// Build a table for the operator.
							$html_results	.=	'<table class="is-fullwidth table is-bordered">';

								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Location:</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_code . '</td>';
								$html_results	.=	'</tr>';


								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Type:</td>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_types_arr[$loc_type] . '</td>';
								$html_results	.=	'</tr>';


								$html_results	.=	'<tr>';
									$html_results	.=	'<td style="background-color: ' . $backclrB . ';" colspan="2">' . $error_msg . '</td>';
								$html_results	.=	'</tr>';


							$html_results	.=	'</table>';


							// Declare the two buttons that I will need for the final step.
							$confirm_lnk	=	'<a class="button is-fullwidth green_class" onClick="get_location_details(1);">CONFIRM</a>';
							$cancel_lnk		=	'<a class="button is-fullwidth red_class" href="gv_move_prod2loc.php">CANCEL</a>';

							if ($allow_item)
							{
								// Maybe there is a better way to keep both buttons the same width?
								$html_results	.=	'<table class="is-fullwidth table is-bordered">';
									$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:49%; text-align:center; font-weight:bold;">' . $confirm_lnk . '</td>';
									$html_results	.=	'<td style="width:49%; text-align:center; font-weight:bold;">' . $cancel_lnk . '</td>';
									$html_results	.=	'</tr>';
								$html_results	.=	'</table>';
							}
							else
							{
								$html_results	.=	'<table class="is-fullwidth table is-bordered">';
									$html_results	.=	'<tr>';
									$html_results	.=	'<td style="width:98%; text-align:center; font-weight:bold;">' . $cancel_lnk. '</td>';
									$html_results	.=	'</tr>';
								$html_results	.=	'</table>';
							}

							$message_id	=	0;	// Seems like everything went well!
						}



					}	// no location found...
					else
					{
						$message2op		=	'Location does not exist';
						$message_id		=	100109;
					}


					}
					// show an error if the query has an error
					else
					{
						$message2op		=	'Could not get location data';
						$message_id		=	100101;
					}



				}
				else
				{
					$message2op		=	'Product not found';
					$message_id		=	100103;
				}


			}

			// show an error if the query has an error
			else
			{
				$message2op		=	'Product query failed';
				$message_id		=	100102;
			}




		}	// check if Qty is not <= 0, if the prod_id is also not 0 etc etc Basic checks about the variables passed down to be processed. 



		}	// END OF user permission checks
		else
		{
			$message2op		=	'Permissions error';
			$message_id		=	100100;
		}






	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		//$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	100666;
	}


	// Close db connection !
	$db = null;

	// return the findings
	print_message_html_payload($message_id, $message2op, $html_results);



} else {
    // the user is not logged in. Show them the login page.
    include('not_logged_in.php');
}



?>
 
