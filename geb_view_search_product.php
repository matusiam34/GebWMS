<?php



// load the login class
require_once('lib_login.php');


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    

	// load the supporting functions....
	require_once('lib_system.php');


	// Certain access rights checks should be executed here...
	if (is_it_enabled($_SESSION['menu_prod_search']))
	{

		// needs a db connection...
		require_once('lib_db_conn.php');

		$product_or_barcode		=	'';
		
		if (isset($_GET['product']))
		{
			$product_or_barcode		=	trim($_GET['product']);
		}

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['product_search'];	?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta name="viewport" content="width=device-width, initial-scale=1">


	<!-- CSS
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->

	<link rel="stylesheet" href="css/bulma.css">
	<link rel="stylesheet" href="css/custom.css">


	<!-- Scripts
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<script src="js/jquery.js"></script>

	<!--	Include all custom scripts	-->
	<script src="js/myFunctions.js"></script>

	<script src="js/alertable.js"></script>


	<!-- Favicon
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="icon" type="image/png" href="images/favicon.png">



	<script language="javascript" type="text/javascript">


		$(document).ready(function() 
		{

			//	Submit the form when the operator on the desktop selects a recent item... No need to press enter :)
			$('#product').change(function() {
				this.form.submit();
			});

		});


	</script>



</head>
<body>





<?php


	// A little gap at the top to make it look better a notch.
	echo '<div class="blank_space_12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_form	=	'

	<form action="geb_view_search_product.php" method="get">

		<div class="field has-addons">

			<p class="control">
				<input class="input" type="text" id="product" name="product" placeholder="' . $mylang['product_code'] . '" value="' . $product_or_barcode . '">
			</p>

			<p class="control">
				<button class="button inventory_class iconSearch" style="width:50px;" type="submit"></button>
			</p>

		</div>

	</form>';



	$page_form	.=	'<p class="control">';
	$menu_link	=	"'index.php'";
	$page_form	.=		'<button class="button inventory_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
	$page_form	.=	'</p>';

	$page_form	.=	'<p class="control">';
	$page_form	.=		'<button class="button inventory_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
	$page_form	.=	'</p>';



	// Show the page header aka Product Search input field!


	// The "menu"!
	echo '<nav class="level">

	<!-- Left side -->
		<div class="level-left">

		<div class="level-item">
	' . $page_form . '
		</div>

		</div>

	</nav>';



	try
	{


		//	Warehouse code set for the operator is in the session. Can be changed by the admin in the USERS tab
		//$user_warehouse_uid	=	leave_numbers_only($_SESSION['user_warehouse']);

		$products_arr	=	array();	//	store all found products right here! this is combined with the LIKE in the SELECT
										//	statement because I want to provide the operator with a better search functionality


		// Figure out is the $product variable is numeric only (barcode) or alphanumeric aka Product!
		$is_barcode	=	false;

		if (is_numeric($product_or_barcode))	{	$is_barcode	=	true;	}

		$sql	=	'

			SELECT

			*

			FROM 

			geb_product

			LEFT JOIN geb_category_a ON geb_product.prod_category_a = geb_category_a.cat_a_pkey
			LEFT JOIN geb_category_b ON geb_product.prod_category_b = geb_category_b.cat_b_pkey
			LEFT JOIN geb_category_c ON geb_product.prod_category_c = geb_category_c.cat_c_pkey


			WHERE


		';


		if ($is_barcode)
		{
			// Search by barcode
			$sql	.=	' prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar ';
		}
		else
		{
			// Search for a product by name
			$sql	.=	' prod_code LIKE "%' . $product_or_barcode . '%"';
		}
		
		
		//	The warehouse filter here... Keep in mind that the product needs to exist in the warehouse that the operator is in.
		//	This is because I have things like physical_qty and allocated_qty etc etc
		//$sql	.=	' AND prod_warehouse = :iuser_warehouse ';


		$columns_html	=	'';
		$details_html	=	'';



		if ($stmt = $db->prepare($sql))
		{

			if ($is_barcode)
			{
				$stmt->bindValue(':iprod_each_bar',		$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':iprod_case_bar',		$product_or_barcode,	PDO::PARAM_STR);
			}
			else
			{
				//	Can't have this if I am using the LIKE in the query. I need to revist this at some point...
				//	Maybe totally remove it.. no idea for now. Works so I am going to leave it be.
				//$stmt->bindValue(':iprod_code',	$product_or_barcode,		PDO::PARAM_STR);
			}


			//$stmt->bindValue(':iuser_warehouse',	$user_warehouse_uid,	PDO::PARAM_INT);
			$stmt->execute();


			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$products_arr[]	=	$row;
			}

			// Analyise what the products_arr has to offer...
			if (count($products_arr) == 1)
			{
				//	Found one matching product. Just show it and be done with it!

				$columns_html	.=	'<div class="columns">';

				// General info "Page" of product
				$columns_html	.=	'<div class="column is-6">';

					$details_html	.=	'<table class="is-fullwidth table is-bordered">';

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['product'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_code']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['description'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_desc']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['category'] . ' (A):</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['cat_a_name']) . '</td>';
						$details_html	.=	'</tr>';

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['category'] . ' (B):</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['cat_b_name']) . '</td>';
						$details_html	.=	'</tr>';

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['category'] . ' (C):</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['cat_c_name']) . '</td>';
						$details_html	.=	'</tr>';








						// Convert the product status into meaninful text.
						$prod_status_id		=	leave_numbers_only($products_arr[0]['prod_disabled']);

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['status'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_status_arr[$prod_status_id] . '</td>';
						$details_html	.=	'</tr>';

/*
						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['physical_qty'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_phy_qty']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['allocated_qty'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_alloc_qty']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['free_qty'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_free_qty']) . '</td>';
						$details_html	.=	'</tr>';
*/


					$details_html	.=	'</table>';



				$columns_html	.=	$details_html;	// place the table in the column...
				$details_html	=	"";				// empty for the next run!
				$columns_html	.=	'</div>';


					// Prepare the technical "Page" of product details
					$columns_html	.=	'<div class="column is-6">';


					$details_html	.=	'<table class="is-fullwidth table is-bordered">';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['each_barcode'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_each_barcode']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['each_weight'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_each_weight']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['case_barcode'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_case_barcode']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['case_qty'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_case_qty']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'</table>';

						$columns_html	.=	$details_html;	// place the table in the column...
						$details_html	=	"";				// empty for the next run!



					$columns_html	.=	'</div>';


				// End of columns div!
				$columns_html	.=	'</div>';


				// Show the product technical stuff!
				echo	$columns_html;



				//	Also since there is only one product get the warehouse stock details...
				$total_product_eaches	=	0;			//	shown in the last line of the table!
				$location_totals_arr	=	array();	//	all locations found added up here. This is to provide a glance look
														//	of the entire stock across the entire warehouse regardless where the stuff is.
														//	Displayed as another table next to the Warehouse/Location/Qty table.


				$sql	=	'


					SELECT

					wh_code,
					loc_code,
					loc_barcode,
					loc_type,
					loc_function,
					loc_blocked,
					stk_unit,
					prod_case_qty,
					prod_pall_qty,
					SUM(stk_qty) as all_stk_qty

					FROM 

					geb_stock

					INNER JOIN geb_location ON geb_stock.stk_loc_pkey = geb_location.loc_pkey
					INNER JOIN geb_product ON geb_stock.stk_prod_pkey = geb_product.prod_pkey
					INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


					WHERE

					stk_disabled = 0

					AND
					
					loc_disabled = 0

					AND

					prod_disabled = 0

					AND

					prod_pkey = :iprod_pkey


					GROUP BY wh_code, loc_code, loc_barcode, loc_type, loc_function, loc_blocked, stk_unit, prod_case_qty, prod_pall_qty

					ORDER BY wh_code, loc_code

				';


				if ($stmt = $db->prepare($sql))
				{

					$stmt->bindValue(':iprod_pkey',		leave_numbers_only($products_arr[0]['prod_pkey']),		PDO::PARAM_INT);
					$stmt->execute();


					// Reset the entire columns html thing...
					$columns_html	=	'<div class="columns">';
					$columns_html	.=	'<div class="column is-6">';
					$details_html	.=	'<table class="is-fullwidth table is-bordered">';



					$details_html	.=	'<tr>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['warehouse'] . '</th>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['location'] . '</th>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['qty'] . '</th>';
					$details_html	.=	'</tr>';



					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{


						$location_stock_qty		=	trim($row['all_stk_qty']);

						// Generate the loc status code. This will allow the operator to see if the location is a Single, Blocked, Mixed etc at a glance
						$loc_function			=	leave_numbers_only($row['loc_function']);
						$loc_type				=	leave_numbers_only($row['loc_type']);
						$loc_blocked			=	leave_numbers_only($row['loc_blocked']);


						//	Before I do anything else add the location function and qty to the $location_totals_arr
						if (isset($location_totals_arr[$loc_function]))
						{
							//	Update the location function type with the EACH QTY!
							$location_totals_arr[$loc_function]	+=	$location_stock_qty;
						}
						else
						{
							//	Entry does not exist = add it
							$location_totals_arr[$loc_function]	=	$location_stock_qty;
						}




						//	Figure out if the location is a pickface, bulk, goods in etc etc 
						//	A function in lib_functions will do the decoding for me. Also, it will provide the text and boldness so
						//	that I can have a consistent experiece across the entire app. 1:54am and I am on fire!!!
						//	NOTE:
						//	0:	Stores the string
						//	1:	Stores the styling 
						$loc_details_arr	=	decode_loc($loc_function, $loc_type, $loc_blocked, $loc_function_codes_arr, $loc_type_codes_arr);


						// Calculate amount of CASES if stk_unit indicates it to be a CASE (id = 5)
						$stock_unit				=	trim($row['stk_unit']);
						$stock_unit_str			=	'E';	// default lets go with EACHES

						if ($stock_unit == $stock_unit_type_reverse_arr['C'])
						{
							$location_case_qty		=	$location_stock_qty / trim($row['prod_case_qty']);

							if (is_float($location_case_qty))
							{
								// If the number is a float than do please trim down the deciman places to a 2 as it will look ugly with an
								// entry like 4.6666666666666666666667 or something to that tune.
								$location_case_qty		=	number_format($location_case_qty, 2);
							}
							$stock_unit_str			=	$location_case_qty . ' C';
						}


						//	Important feature right here!
						//	If the user does not have access to the location search than do not
						//	provide the link to it here! Logic! :)
						//	By default just provide with the location code.

						$loc_details_code_str	=	' (' . $loc_details_arr[0] . ')';
						$loc_details_lnk		=	trim($row['loc_code']) . $loc_details_code_str;

						if (is_it_enabled($_SESSION['menu_location_search']))
						{
							// Create a clickable link so that the operator can investigate the location in more detail (if required & allowed)
							$loc_details_lnk	=	'<a style="' . $loc_details_arr[1] . '" href="geb_view_search_location.php?location=' . trim($row['loc_barcode']) . '">' . trim($row['loc_code']) . $loc_details_code_str . '</a>';
						}


						$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['wh_code']) . '</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_details_lnk . '</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $location_stock_qty . ' (' . $stock_unit_str .   ')</td>';
						$details_html	.=	'</tr>';

						$total_product_eaches	=	$total_product_eaches + trim($row['all_stk_qty']);


					}		// First query while row bracket...


					// Provide a total eaches for this product in the last row

					$details_html	.=	'<tr>';
					$details_html	.=	'<td style="background-color: ' . $backclrB . ';"></td>';
					$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $mylang['total_eaches'] . '</td>';
					$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $total_product_eaches . '</td>';
					$details_html	.=	'</tr>';

					$details_html	.=	'</table>';


					$columns_html	.=	$details_html;	// place the table in the column...
					$columns_html	.=	'</div>';

					//	Finished with the left side table that stores locations and QTYs + short code for type of location (Single, Mixed etc)
					//	Now generate the table for the left that stores all of the EACH(es) per location function (pickface, bulk etc)!


					$columns_html	.=	'<div class="column is-6">';
					$details_html	=	'<table class="is-fullwidth table is-bordered">';
					$details_html	.=	'<tr>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['location'] . '</th>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['qty'] . '</th>';
					$details_html	.=	'</tr>';
					//	Loop to provide the details!
					foreach ($location_totals_arr as $key => $value)
					{

						$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_functions_arr[$key] . '</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $value . '</td>';
						$details_html	.=	'</tr>';

					}


					$details_html	.=	'</table>';
					$columns_html	.=	$details_html;	// place the table in the column...
					$columns_html	.=	'</div>';


					// End of columns div!
					$columns_html	.=	'</div>';


					// Show the product technical stuff!
					echo	$columns_html;


				}
				// show an error if the query has an error
				else
				{
					echo 'Stock Query failed!';
				}



			}
			elseif (count($products_arr) > 1)
			{
				//	Found few matches. Show them in a tiny table with short info about them?!

				$columns_html	.=	'<div class="columns">';

				// General info "Page" of product
				$columns_html	.=	'<div class="column is-6">';

						$details_html	.=	'<table class="is-fullwidth table is-bordered">';

					foreach ($products_arr as $product)
					{

							// Create a clickable link so that the operator can investigate the product in more detail.
							$product_details_lnk	=	'<a href="geb_view_search_product.php?product=' . trim($product['prod_code']) . '">' . trim($product['prod_code']) . '</a>';


							$details_html	.=	'<tr>';
								$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $product_details_lnk . '</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($product['prod_desc']) . '</td>';
							$details_html	.=	'</tr>';

					}

						$details_html	.=	'</table>';

				$columns_html	.=	$details_html;	// place the table in the column...
				$details_html	=	"";				// empty for the next run!
				$columns_html	.=	'</div>';


				// End of columns div!
				$columns_html	.=	'</div>';


				// Show the product technical stuff!
				echo	$columns_html;


			}



		}
		// show an error if the query has an error
		else
		{
			echo 'Search Query Failed!';
		}





	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		print_message(1, $e->getMessage());
	}



		echo '</div>';
	echo '</section>';



	}
	else
	{
		// User has logged in but does not have the rights to access this page !
		include('not_logged_in.php');
	}


}
else
{

    // the user is not logged in.
    include('not_logged_in.php');

}

?>


</body>
</html>


