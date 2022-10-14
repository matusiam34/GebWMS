<?php


// Book a known product that exist on the system into a location via barcode or product name?
// Also the location should also be something you can type in and it should work as long as it 
// has been setup on the system before.




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
	<title>Book Product(s) IN</title>
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

			// Focus on the barcode input field...
			set_Focus_On_Element_By_ID('product');


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

	$page_form	.=	'<form action="gv_bookin_prod2loc.php" method="get">';

		$page_form	.=	'<div class="field has-addons">';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<input class="input" type="text" id="product" name="product" placeholder="Product code / barcode" value="' . $product_or_barcode . '">';
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


		$product_id	=	0;	// for the stock update / insert. Whatever it will be.

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


				// Just short product details based on barcode... Hmmm... What when they just key in the product? Each in that case??
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


						// Figure out the QTY scanned based on the barcode... Will have to do something with just a product code being typed in!
						$scanned_qty	=	1;	// by default lets assume it is an EACH (above issue)

						if ($is_barcode)
						{
							if (strcmp(trim($row['prod_each_barcode']), $product_or_barcode) === 0)
							{
								// No need for this is there? Assume each one more time?
							}

							if (strcmp(trim($row['prod_case_barcode']), $product_or_barcode) === 0)
							{
								// A case has been scanned so assign the proper Qty.
								$scanned_qty	=	trim($row['prod_case_qty']);
							}

						}
						else
						{
							// Assume it is a product typed in and do nothing? Each is assumed after all.
						}


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Qty:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $scanned_qty . '</td>';
						$details_html	.=	'</tr>';

					$details_html	.=	'</table>';

					$columns_html	.=	$details_html;	// place the table in the column...
					$details_html	=	"";				// empty for the next run!
					$columns_html	.=	'</div>';		// close the first column...



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




	}		// Try bracket end
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


