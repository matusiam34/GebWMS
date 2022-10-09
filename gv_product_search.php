<?php

// checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.7', '<') ) {    
  exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");  
}

// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once("lib_passwd.php");

// include the configs / constants for the database connection
require_once("lib_db.php");

// load the login class
require_once("lib_login.php");




// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
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

  <!--	Include all custom scripts - tables gen and other related ! -->
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

	// Generate the container for everything!

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
	// This has to show before the tables!

	// The menu!
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


		// Works with one only! As I am not expecting duplicate entries of products !!!!!
		// BAD!!

		// 22:43pm 25 Apr 2022: Ability to search via INNER and OUTER barcode.

		// I will split the table in two so it is nice to look at ish!


		$product_id		=	0;		// I want to search quicker via primary key instead of querying the prod_code string column


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
			// Search for a product by barcode :)
			$sql	.=	" product_master.product_master_barcode_inner = :innerbar OR product_master.product_master_barcode_outer = :outerbar ";
			
		}
		else
		{
			// Search for a product by name
			$sql	.=	" prod_code = :iprod_code ";
			
		}
		
		
		$columns_html	=	"";
		$details_html	=	"";
		$stock_html		=	"";

		$backclrA	=	'#d6bfa9';
		$backclrB	=	'#f7f2ee';



		if ($stmt = $db->prepare($sql))
		{


			if ($is_barcode)
			{
				$stmt->bindValue(':innerbar',	$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':outerbar',	$product_or_barcode,	PDO::PARAM_STR);

			}
			else
			{
				$stmt->bindValue(':iprod_code',	$product_or_barcode,	PDO::PARAM_STR);
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



		// Get the stock of product!

		$sql	=	"


			SELECT

			wh_code,
			loc_code,
			stk_qty

			FROM 

			geb_stock

			INNER JOIN geb_location ON geb_stock.stk_loc_pkey = geb_location.loc_pkey
			INNER JOIN geb_product ON geb_stock.stk_prod_pkey = geb_product.prod_pkey
			INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


			WHERE
			
			stk_disabled = 0
			
			AND
			
			prod_disabled = 0

			AND
			
			loc_disabled = 0

			AND
			
			prod_pkey = :iprod_pkey


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

				$details_html	.=	'<tr>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['wh_code']) . '</td>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['loc_code']) . '</td>';
				$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['stk_qty']) . '</td>';
				$details_html	.=	'</tr>';

			}		// First query while row bracket...



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



?>





			</div>
		</section>






<?php


	}
	else
	{
		// User has logged in but does not have the rights to access this page !
		include("not_logged_in.php");
	}


}
else
{

    // the user is not logged in. you can do whatever you want here.
    // for demonstration purposes, we simply show the "you are not logged in" view.
    include("not_logged_in.php");

}

?>



<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>


