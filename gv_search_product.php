<?php


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

	// load the supporting functions....
	require_once('lib_functions.php');


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
	echo '<div style="height:12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_form	=	'

	<form action="gv_search_product.php" method="get">

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

			WHERE

		';


		if ($is_barcode)
		{
			// Search by barcode: Fixed 13 Oct 2022
			$sql	.=	' prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar ';
		}
		else
		{
			// Search for a product by name
			$sql	.=	' prod_code LIKE "%' . $product_or_barcode . '%"';
		}
		
		
		$columns_html	=	'';
		$details_html	=	'';



		if ($stmt = $db->prepare($sql))
		{

			if ($is_barcode)
			{
				$stmt->bindValue(':iprod_each_bar',	$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':iprod_case_bar',	$product_or_barcode,	PDO::PARAM_STR);
			}
			else
			{
				//	Can't have this if I am using the LIKE in the query
				//$stmt->bindValue(':iprod_code',	$product_or_barcode,		PDO::PARAM_STR);
			}



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
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['category'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_category']) . '</td>';
						$details_html	.=	'</tr>';


						// Convert the product status into meaninful text.
						$prod_status_id		=	leave_numbers_only($products_arr[0]['prod_disabled']);

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['status'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_status_arr[$prod_status_id] . '</td>';
						$details_html	.=	'</tr>';


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


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['pallet_qty'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($products_arr[0]['prod_pall_qty']) . '</td>';
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

				$total_product_eaches	=	0;		// shown in the last line of the table!

				$sql	=	'


					SELECT

					wh_code,
					loc_code,
					loc_barcode,
					loc_type,
					loc_pickface,
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


					GROUP BY wh_code, loc_code, loc_barcode, loc_type, loc_pickface, loc_blocked, stk_unit, prod_case_qty, prod_pall_qty

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
						$loc_status_code_str	=	'';		// a small code that explains what the location "does" / "is"

						// Generate the loc status code. This will allow the operator to see if the location is a Single, Blocked, Mixed etc at a glance
						$loc_blocked			=	leave_numbers_only($row['loc_blocked']);
						$loc_pickface			=	leave_numbers_only($row['loc_pickface']);
						$loc_type				=	leave_numbers_only($row['loc_type']);


						if ($loc_blocked	==	1)		{	$loc_status_code_str	.=	'B';	}

						//	Get the pickface flag!
						$loc_pickface_style	=	'';
						if ($loc_pickface	==	1)		{	$loc_status_code_str	.=	'P';	$loc_pickface_style	=	'font-weight: bold;';	}

						$loc_status_code_str	.=	$loc_types_codes_arr[$loc_type];



						// Calculate amount of CASES if stk_unit indicates it to be a CASE (id = 5)
						$stock_unit				=	trim($row['stk_unit']);
						$stock_unit_str			=	'E';	// default lets go with EACHES

						if ($stock_unit == $stock_unit_type_reverse_arr['C'])
						{
							$location_case_qty		=	$location_stock_qty / trim($row['prod_case_qty']);

							if (is_float($location_case_qty))
							{
								// If the number is a float than do please trim down the deciman places to a 2 as will look ugly with an
								// entry like 4.6666666666666666666667 or something to that tune.
								$location_case_qty		=	number_format($location_case_qty, 2);
							}
							$stock_unit_str			=	$location_case_qty . ' C';
						}


						//	Important feature right here!
						//	If the user does not have access to the location search than do not
						//	provide the links to it here! Logic! :)
						//	By default just provide with the location code.
						$loc_details_lnk	=	trim($row['loc_code']);

						if (is_it_enabled($_SESSION['menu_location_search']))
						{
							// Create a clickable link so that the operator can investigate the location in more detail (if required & allowed)
							$loc_details_lnk	=	'<a href="gv_search_location.php?location=' . trim($row['loc_barcode']) . '">' . trim($row['loc_code']) . '</a>';
						}


						$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['wh_code']) . '</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . '; ' . $loc_pickface_style . '">' . $loc_details_lnk . ' (' . $loc_status_code_str . ')</td>';
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
					$details_html	=	"";				// empty for the next run!


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
							$product_details_lnk	=	'<a href="gv_search_product.php?product=' . trim($product['prod_code']) . '">' . trim($product['prod_code']) . '</a>';


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


