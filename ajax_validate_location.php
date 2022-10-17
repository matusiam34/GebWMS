<?php

/*

	Error code explained:

	666:	try could not be established 
	100:	user access control permission error (most likely no rights to whatever they are accessing)
	101:	could not get data
	102:	Product query failed
	103:	Product not found

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



			// some variables to help me out to validate the entire process
			$product_found	=	false;



			// Grab product details based on the UID and barcode.
			$prod_stock_unit		=	$stock_unit_type_reverse_arr['E'];	// by default assume EACH!
			$loc_data_arr			=	array();	// every entry from the location.


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


						// Set some default variables before processing the array.
						$loc_blocked	=	1;	// by default it is blocked!
						$loc_type		=	$loc_types_codes_reverse_arr['S'];	// single by default!


						// Get the location data from the array. Keep in mind that this is a LEFT JOIN so even if there is
						// no product in the specified location you will still get the location config.
						// This is exactly what I am going to exploit here.
						$loc_code		=	trim($loc_data_arr[0]['loc_code']);						// this can be a mix of characters and numbers
						$loc_type		=	leave_numbers_only($loc_data_arr[0]['loc_type']);		// I expect numbers here only

						$loc_blocked	=	leave_numbers_only($loc_data_arr[0]['loc_blocked']);	// I expect numbers here only



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

/*
							$html_results	.=	'<tr>';
								$html_results	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Stk Unit:</td>';
								$html_results	.=	'<td style="background-color: ' . $backclrB . ';">' . $stock_unit . '</td>';
							$html_results	.=	'</tr>';
*/

						$html_results	.=	'</table>';





						$message_id	=	0;	// Seems like everything went well!

					}

					// show an error if the query has an error
					else
					{
						$message2op		=	'Could not get data';
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



		}	// END OF user permission checks
		else
		{
			$message2op		=	'Permissions error';
			$message_id		=	100100;
		}






	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
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
 
