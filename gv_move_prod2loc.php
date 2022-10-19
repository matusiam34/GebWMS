<?php


// Book a known product that exist on the system into a location via barcode or product name?
// Also the location should also be something you can type in and it should work as long as it 
// has been setup on the system before.

// NOTE: VERY important to implement a check that tell the operator that there have been two entries found
// with the same barcode. If that happens the system CAN'T forward with the booking and it needs to be 
// reported to a supervisor / manager as it will need to be fixed URGENTLY!


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

		// Supporting barcode here only to keep things simple.
		// If there is an issue on the shop floor I am sure it can be solved in a different way.
		$product_barcode		=	"";

		if (isset($_GET["barcode"]))
		{
			$product_barcode		=	trim($_GET["barcode"]);
		}

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Product 2 Location</title>
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
			//set_Focus_On_Element_By_ID('product');

		});





		//	Grab selected location details... and also update the location (depending on option)
		//	validorwrite: 
		//	0	:	check if the product will fit into the location,
		//	1	:	same as 0 + it will place the product into the location
		function get_location_details(validorwrite)
		{

			$.post('ajax_prod2loc_validorwrite.php', { 

				prod_barcode_js		:	get_Element_Value_By_ID('barcode'),
				prod_qty_js			:	get_Element_Value_By_ID('product_qty'),
				loc_barcode_js		:	get_Element_Value_By_ID('location_code'),
				prod_id_js			:	get_Element_Value_By_ID('hidden_product_id'),
				validorwrite_js		:	validorwrite

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					if (validorwrite == 0)
					{
						$('#loc_data_table').empty();
						$('#loc_data_table').append(obje.html);
					}

					if (validorwrite == 1)
					{
						// If things go well... Why bother?
						//alert(obje.msg);
						// Need to visit the page again to start another prod2loc activity!
					}

				}
				else
				{
					alert(obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						alert("server problem");
					});

		}






	</script>



</head>
<body>





<?php


	// A little gap at the top to make it look better a notch.
	echo '<div style="height:12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_form	=	'';

	$page_form	.=	'<form action="gv_move_prod2loc.php" method="get">';

		$page_form	.=	'<div class="field has-addons">';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<input class="input" type="text" id="barcode" name="barcode" placeholder="Product barcode" value="' . $product_barcode . '">';
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


		$sql	=	"

			SELECT

			*

			FROM 

			geb_product

			WHERE

			prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar

		";


		$columns_html	=	"";
		$details_html	=	"";




		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':iprod_each_bar',	$product_barcode,	PDO::PARAM_STR);
			$stmt->bindValue(':iprod_case_bar',	$product_barcode,	PDO::PARAM_STR);
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

/*
	//	Probably do not need this much information do I? Create a product enquiry for this if you need tons of data!

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Description:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_desc']) . '</td>';
						$details_html	.=	'</tr>';
*/

						// Figure out the QTY scanned based on the barcode... Will have to do something with just a product code being typed in!
						$scanned_qty	=	1;	// by default lets assume it is an EACH (above issue)
						$input_disabled	=	"";	// if EACH leave it open for editing, if CASE == disable it. Should do the job.

						if (strcmp(trim($row['prod_each_barcode']), $product_barcode) === 0)
						{
							// No need for this is there? Assume EACH one more time?
						}

						if (strcmp(trim($row['prod_case_barcode']), $product_barcode) === 0)
						{
							// CASE has been scanned so assign the proper Qty.
							$scanned_qty	=	trim($row['prod_case_qty']);
							$input_disabled	=	' readonly ';
						}



						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold; vertical-align: middle;">Qty:</td>';

							$qty_input_field	=	'<input class="input" type="text" id="product_qty" name="product_qty" placeholder="Product barcode" value="' . $scanned_qty .'" ' . $input_disabled . '>';

							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $qty_input_field . '</td>';


//							$details_html	.=	'<td style="background-color: ' . $backclrB . ';" id="product_qty" name="product_qty" >' . $scanned_qty . '</td>';
						$details_html	.=	'</tr>';

					$details_html	.=	'</table>';


					// Now also provide an input field for the location barcode / name (maybe allow name typing?!?)
					// Note: what if there are identical location names in different warehouses?!


					$details_html	.=	'<div class="field" style="">
											<div class="control">
												<input id="location_code" class="input is-normal" type="text" placeholder="location code">
											</div>
										</div>

										<div class="field" style="">
											<div class="control" id="loc_data_table">
											</div>
										</div>


										<div class="field">
											<div class="control">
												<input id="hidden_product_id" class="input is-normal" type="hidden" value="' . $product_id . '">
											</div>
										</div>




										<script>


											// When enter is pressed in location_code go and ajax the details!
											$("#location_code").keypress(function (e) {
												var key = e.which;
												if(key == 13)  // the enter key code
												{

													get_location_details(0);
													//alert(get_Element_Value_By_ID("product_qty"));
													//alert("test");
												}
											});

											// Focus on the location input field...
											// Need to rethink this totally!
											//set_Focus_On_Element_By_ID("location_code");

										</script>
										';





					$columns_html	.=	$details_html;	// place the table in the column...
					$details_html	=	"";				// empty for the next run!
					$columns_html	.=	'</div>';		// close the first column...


				// End of columns div!
				$columns_html	.=	'</div>';

				// Show what has been found
				echo	$columns_html;


			}		// First query while row bracket...


		}
		// show an error if the query has an error
		else
		{
			echo "Product Query Failed!";
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

