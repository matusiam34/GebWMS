<?php

// For the operator to check what have been their last few transactions in case something goes wrong or is not
// sure if the action performed few seconds ago got registered on the system. I think a very useful thing to 
// have when you are BUSY doing stuff at the warehouse :)


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


	//	Allow the operator to see it is enough.
	if (is_it_enabled($_SESSION['menu_recent_activity']))
	{

		// needs a db connection...
		require_once('lib_db_conn.php');


?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title></title>
	<title><?php	echo $mylang['recent_activity'];	?></title>

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


		$sql	=	'

			SELECT


			geb_product.prod_code,
			geb_product.prod_case_qty,
			geb_location.loc_code,
			geb_location.loc_barcode,
			geb_stock_history.stk_hst_op_type,
			geb_stock_history.stk_hst_unit,
			geb_stock_history.stk_hst_qty,
			geb_stock_history.stk_hst_date


			FROM  geb_stock_history

			INNER JOIN geb_product ON geb_stock_history.stk_hst_prod_pkey = geb_product.prod_pkey
			INNER JOIN geb_location ON geb_stock_history.stk_hst_to_loc = geb_location.loc_pkey


			WHERE

			geb_stock_history.stk_hst_disabled = 0 AND geb_product.prod_disabled = 0 AND geb_location.loc_disabled = 0

			AND

			geb_stock_history.stk_hst_operator = :suser_id

			LIMIT 10

		';


		$columns_html	=	'';
		$details_html	=	'';


		// The short name of the user obtained from the lib_login.php script.
		$username	=	trim($_SESSION['user_name']);


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':suser_id',		leave_numbers_only($_SESSION['user_id']),		PDO::PARAM_INT);
			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{


				$columns_html	.=	'<div class="columns">';


				// General info "Page" of product
				$columns_html	.=	'<div class="column is-6">';


					$details_html	=	'<table class="is-fullwidth table is-bordered">';



						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['type'] . ':</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $activity_type_arr[leave_numbers_only($row['stk_hst_op_type'])] . '</td>';
						$details_html	.=	'</tr>';



						if (leave_numbers_only($row['stk_hst_op_type']) == $activity_type_reverse_arr['Prod2Loc'])
						{
							
							// We are dealing with a product 2 location!
							// Set everything up accordingly.



							//	Important feature right here!
							//	If the user does not have access to the product search than do not
							//	provide the links for it here! Logic! :P
							$product_details_lnk	=	trim($row['prod_code']);

							if (is_it_enabled($_SESSION['menu_prod_search']))
							{
								// Create a clickable link so that the operator can investigate the product in more detail (if required & allowed)
								$product_details_lnk	=	'<a href="gv_search_product.php?product=' . trim($row['prod_code']) . '">' . trim($row['prod_code']) . '</a>';
							}

							$details_html	.=	'<tr>';
								$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['product'] . ':</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_details_lnk . '</td>';
							$details_html	.=	'</tr>';



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
								$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['to_location'] . ':</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $loc_details_lnk . '</td>';
							$details_html	.=	'</tr>';



							$qty			=	leave_numbers_only($row['stk_hst_qty']);
							$entry_qty		=	$qty;
							$unit_id		=	leave_numbers_only($row['stk_hst_unit']);
							$unit_type_str	=	$stock_unit_type_arr[$unit_id];


							if ($unit_id == $stock_unit_type_reverse_arr['C'])
							{
								$entry_qty		=	$qty / leave_numbers_only($row['prod_case_qty']);
								if (is_float($entry_qty))
								{
									// If the number is a float than do please trim down the deciman places to a 2 as will look ugly with an
									// entry like 4.6666666666666666666667 or something to that tune.
									$entry_qty		=	number_format($entry_qty, 2);
								}
							}



							// Based on the unit I will have to do some maths to show what exactly it is (CASES, EACHES... maybe PALLETS...)
							$details_html	.=	'<tr>';
								$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['qty'] . ':</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $entry_qty . ' ' . $unit_type_str . '</td>';
							$details_html	.=	'</tr>';


							//	mateusz
							$activity_date	=		trim($row['stk_hst_date']);

							$act_date		=		date('d/m/Y', strtotime($activity_date));
							$act_time		=		date('H:i:s', strtotime($activity_date));


							$details_html	.=	'<tr>';
								$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['when'] . ':</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $act_date . ' at ' . $act_time . '</td>';
							$details_html	.=	'</tr>';

						}



					$details_html	.=	'</table>';



					$columns_html	.=	$details_html;	// place the table in the column...
					$columns_html	.=	'</div>';



				// End of columns div!
				$columns_html	.=	'</div>';


			}		// First query while row bracket...


			// Show the product technical stuff!
			echo	$columns_html;




		}
		// show an error if the query has an error
		else
		{
			echo 'Activity Query Failed!';
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


