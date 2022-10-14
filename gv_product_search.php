<?php


// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once("lib_passwd.php");

// include the configs / constants for the database connection
require_once("lib_db.php");

// load the login class
require_once("lib_login.php");


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    

	// load the supporting functions....
	require_once("lib_functions.php");


	// Certain access rights checks should be executed here...
	if ( (can_user_access($_SESSION['user_inventory']))  AND  (leave_numbers_only($_SESSION['user_priv']) >=	min_priv))
	{

		// needs a db connection...
		require_once("lib_db_conn.php");

		$product_or_barcode		=	"";
		
		if (isset($_GET["product"]))
		{
			$product_or_barcode		=	trim($_GET["product"]);
		}

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Product Search</title>
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

	$page_form	.=	'<form action="gv_product_search.php" method="get">';

		$page_form	.=	'<div class="field has-addons">';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<input class="input" type="text" id="product" name="product" placeholder="Product code" value="' . $product_or_barcode . '">';
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

		if (is_numeric($product_or_barcode))	{	$is_barcode	=	true;	}

		$sql	=	"

			SELECT

			*

			FROM 

			geb_product

			WHERE

		";


		if ($is_barcode)
		{
			// Search by barcode: Fixed 13 Oct 2022
			$sql	.=	" prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar";
		}
		else
		{
			// Search for a product by name
			$sql	.=	" prod_code = :iprod_code ";
			
		}
		
		
		$columns_html	=	"";
		$details_html	=	"";

		$backclrA	=	'#d6bfa9';
		$backclrB	=	'#f7f2ee';



		if ($stmt = $db->prepare($sql))
		{

			if ($is_barcode)
			{
				$stmt->bindValue(':iprod_each_bar',	$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':iprod_case_bar',	$product_or_barcode,	PDO::PARAM_STR);
			}
			else
			{
				$stmt->bindValue(':iprod_code',	$product_or_barcode,		PDO::PARAM_STR);
			}



			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{


				$product_id		=	trim($row['prod_pkey']);	// critical for further queries!

				$columns_html	.=	'<div class="columns">';


				// General info "Page" of product
				$columns_html	.=	'<div class="column is-6">';


					$details_html	.=	'<table class="is-fullwidth table is-bordered">';

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Product:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_code']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Description:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_desc']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Category:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_category']) . '</td>';
						$details_html	.=	'</tr>';


						// Convert the product status into meaninful text.
						$prod_status_id		=	leave_numbers_only($row['prod_disabled']);

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Status:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_status_arr[$prod_status_id] . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Physical Qty:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_phy_qty']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Allocated Qty:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_alloc_qty']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Free Qty:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_free_qty']) . '</td>';
						$details_html	.=	'</tr>';



					$details_html	.=	'</table>';



					$columns_html	.=	$details_html;	// place the table in the column...
					$details_html	=	"";				// empty for the next run!
					$columns_html	.=	'</div>';




					// Prepare the technical "Page" of product details
					$columns_html	.=	'<div class="column is-6">';


					$details_html	.=	'<table class="is-fullwidth table is-bordered">';



					$details_html	.=	'<tr>';
						$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">EACH Barcode:</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_each_barcode']) . '</td>';
					$details_html	.=	'</tr>';


					$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">EACH Weight:</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_each_weight']) . '</td>';
					$details_html	.=	'</tr>';


					$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">CASE Barcode:</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_case_barcode']) . '</td>';
					$details_html	.=	'</tr>';


					$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">CASE Qty:</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_case_qty']) . '</td>';
					$details_html	.=	'</tr>';


					$details_html	.=	'<tr>';
						$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">PALLET Qty:</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_pall_qty']) . '</td>';
					$details_html	.=	'</tr>';


					$details_html	.=	'</table>';

					$columns_html	.=	$details_html;	// place the table in the column...
					$details_html	=	"";				// empty for the next run!



				$columns_html	.=	'</div>';



			// End of columns div!
			$columns_html	.=	'</div>';



			// Show the product technical stuff!
			echo	$columns_html;



			}		// First query while row bracket...


		}
		// show an error if the query has an error
		else
		{
			echo "Details Query Failed!";
		}




		// Get the current stock of product in the warehouse

		$total_product_eaches	=	0;		// shown in the last line of the table!

		$sql	=	"


			SELECT

			wh_code,
			loc_code,
			loc_type,
			stk_unit,
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

			prod_pkey = :iprod_pkey


			GROUP BY wh_code, loc_code, loc_type, stk_unit

			ORDER BY wh_code, loc_code

		";


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':iprod_pkey',	$product_id,	PDO::PARAM_INT);
			$stmt->execute();


			// Reset the entire columns html thing...
			$columns_html	=	'<div class="columns">';
			$columns_html	.=	'<div class="column is-6">';
			$details_html	.=	'<table class="is-fullwidth table is-bordered">';



			$details_html	.=	'<tr>';
			$details_html	.=	'<th style="background-color: ' . $backclrA . ';">Warehouse</th>';
			$details_html	.=	'<th style="background-color: ' . $backclrA . ';">Location</th>';
			$details_html	.=	'<th style="background-color: ' . $backclrA . ';">Qty</th>';
			$details_html	.=	'</tr>';



			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

/*
	"loc_pkey"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"loc_wh_pkey"	INTEGER DEFAULT 0,
	"loc_code"	TEXT,
	"loc_barcode"	TEXT,
	"loc_type"	INTEGER DEFAULT 0,
	"loc_blocked"	INTEGER DEFAULT 0,
	"loc_note"	TEXT,
	"loc_disabled"	INTEGER DEFAULT 0
*/



				$loc_status_code	=	"";		// a small code that explains what the location "does" / "is"

				$loc_status_code	=	$loc_types_codes_arr[trim($row['loc_type'])];

				$details_html	.=	'<tr>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['wh_code']) . '</td>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['loc_code']) . ' (' . $loc_status_code . ')</td>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['all_stk_qty']) . ' (' . trim($row['stk_unit']) .   ')</td>';
				$details_html	.=	'</tr>';

				$total_product_eaches	=	$total_product_eaches + trim($row['all_stk_qty']);


			}		// First query while row bracket...


			// Provide a total eaches for this product in the last row

			$details_html	.=	'<tr>';
			$details_html	.=	'<td style="background-color: ' . $backclrB . ';"></td>';
			$details_html	.=	'<td style="background-color: ' . $backclrB . ';">Total EACHES</td>';
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
			echo "Stock Query failed!";
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
		include("not_logged_in.php");
	}


}
else
{

    // the user is not logged in.
    include("not_logged_in.php");

}

?>


</body>
</html>


