<?php


//	TODO:	Add the ability to search by location name. This will mean that if location exists in two different warehouse than
//			they will need to be split and showed on this page. Extra Quality of Life thing me thinks.

//			Also the table does not need a location name (the oeprator knows what it is). Maybe just place it above the table once
//			so that the operator knows what it is if a barcode has been used to search.


// Tells you what stock in held in the location of choice.

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
	if (is_it_enabled($_SESSION['menu_location_search']))
	{

		// needs a db connection...
		require_once('lib_db_conn.php');

		$location_code		=	"";
		
		if (isset($_GET['location']))
		{
			$location_code		=	trim($_GET['location']);
		}

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Location details</title>
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


		});


	</script>



</head>
<body>





<?php


	// A little gap at the top to make it look better a notch.
	echo '<div style="height:12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_form	=	'';

	$page_form	.=	'<form action="gv_search_location.php" method="get">';

		$page_form	.=	'<div class="field has-addons">';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<input class="input" type="text" id="location" name="location" placeholder="' . $mylang['location_barcode'] . '" value="' . $location_code . '">';
			$page_form	.=	'</p>';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<button class="button inventory_class iconSearch" style="width:50px;" type="submit"></button>';
			$page_form	.=	'</p>';

		$page_form	.=	'</div>';

	$page_form	.=	'</form>';

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

		$product_id	=	0;	// for stock query of the product

		// Figure out is the $product variable is numeric only (barcode) or alphanumeric aka Product!
		$is_barcode	=	false;

		if (is_numeric($location_code))	{	$is_barcode	=	true;	}

		$columns_html	=	"";
		$details_html	=	"";


		// Get the current stock of product in the warehouse

		$total_product_eaches	=	0;		// shown in the last line of the table!



		$sql	=	'


			SELECT

			geb_warehouse.wh_code,
			geb_location.loc_code,
			geb_location.loc_type,
			geb_location.loc_pickface,
			geb_location.loc_blocked,
			geb_location.loc_note,

			geb_stock.stk_unit,

			geb_product.prod_code,
			geb_product.prod_case_qty,
			geb_product.prod_pall_qty,
			SUM(geb_stock.stk_qty) as all_stk_qty


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

			loc_barcode = :ilocation_barcode


			GROUP BY wh_code, loc_code, loc_type, loc_pickface, loc_blocked, loc_note, stk_unit, prod_code, prod_case_qty, prod_pall_qty

			ORDER BY prod_code, wh_code, loc_code

		';




		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':ilocation_barcode',		$location_code,		PDO::PARAM_STR);
			$stmt->execute();


			// Reset the entire columns html thing...
			$columns_html	=	'<div class="columns">';
			$columns_html	.=	'<div class="column is-6">';


			// Table that stores product codes and Qty in them locations
			$details_html	.=	'<table class="is-fullwidth table is-bordered">';
			$details_html	.=	'<tr>';
			$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['product'] . '</th>';
			$details_html	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['qty'] . '</th>';
			$details_html	.=	'</tr>';


			// Use $i once only to get the warehouse code, locaction name and note associated with it. Ugly but works for now. Got other things to focus on.
			$i	=	0;

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

				if ($i == 0)
				{

					// Generate the loc status code. This will allow the operator to see if the location is a Single, Blocked, Mixed etc at a glance
					$loc_status_code_str	=	'';		// a small code that explains what the location "does" / "is"

					$loc_blocked			=	leave_numbers_only($row['loc_blocked']);
					$loc_pickface			=	leave_numbers_only($row['loc_pickface']);
					$loc_type				=	leave_numbers_only($row['loc_type']);

					if ($loc_blocked	==	1)		{	$loc_status_code_str	.=	'B';	}

					//	Get the pickface flag!
					$loc_pickface_style	=	'';
					if ($loc_pickface	==	1)		{	$loc_status_code_str	.=	'P';	$loc_pickface_style	=	'font-weight: bold;';	}

					$loc_status_code_str	.=	$loc_types_codes_arr[$loc_type];


					// A details table with Location name, Warehouse and note (for things like DAMAGES, Returns or whatever it could be)
					$location_details	=	'<table class="is-fullwidth table is-bordered">';

						$location_details	.=	'<tr>';
							$location_details	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['warehouse'] . ':</td>';
							$location_details	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['wh_code']) . '</td>';
						$location_details	.=	'</tr>';

						$location_details	.=	'<tr>';
							$location_details	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['location'] . ':</td>';
							$location_details	.=	'<td style="background-color: ' . $backclrB . '; ' . $loc_pickface_style . '">' . trim($row['loc_code']) . ' (' . $loc_status_code_str . ')</td>';
						$location_details	.=	'</tr>';

						$location_details	.=	'<tr>';
							$location_details	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['note'] . ':</td>';
							$location_details	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['loc_note']) . '</td>';
						$location_details	.=	'</tr>';

					$location_details	.=	'</table>';
					$columns_html	.=	$location_details;

					$i++;
				}




				$location_stock_qty		=	trim($row['all_stk_qty']);

				// Calculate amount of CASES if stk_unit indicates it to be a CASE (id = 5)
				$stock_unit				=	leave_numbers_only($row['stk_unit']);
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


				$product_details_lnk	=	trim($row['prod_code']);

				//	Only show the link to the product search page if the operator has the product search tab enabled!
				//	Otherwise just show the product code.
				if (is_it_enabled($_SESSION['menu_prod_search']))
				{
					$product_details_lnk	=	'<a href="gv_search_product.php?product=' . trim($row['prod_code']) . '">' . trim($row['prod_code']) . '</a>';
				}


				$details_html	.=	'<tr>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_details_lnk . '</td>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $location_stock_qty . ' (' . $stock_unit_str .   ')</td>';
				$details_html	.=	'</tr>';

				$total_product_eaches	=	$total_product_eaches + trim($row['all_stk_qty']);


			}		// First query while row bracket...




			// Provide a total eaches for this product in the last row

			$details_html	.=	'<tr>';
			$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $mylang['total_eaches'] . ':</td>';
			$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $total_product_eaches . '</td>';
			$details_html	.=	'</tr>';




			$details_html	.=	'</table>';
			$columns_html	.=	$details_html;	// place the table in the column...
			$columns_html	.=	'</div>';
			$details_html	=	'';				// empty for the next run!


			// End of columns div!
			$columns_html	.=	'</div>';


			// Show the product technical stuff!
			echo	$columns_html;


		}
		// show an error if the query has an error
		else
		{
			echo 'Location Query failed!';
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


