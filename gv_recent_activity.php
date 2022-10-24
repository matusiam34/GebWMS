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


	// Certain access rights checks should be executed here...
	if ( (can_user_access($_SESSION['user_inventory']))  AND  (leave_numbers_only($_SESSION['user_priv']) >=	min_priv))
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
	<title>Recent Activity</title>
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

/*
CREATE TABLE "geb_stock_history" (
	"stk_hst_pkey"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"stk_hst_op_type"	INTEGER,
	"stk_hst_prod_pkey"	INTEGER,
	"stk_hst_from_loc"	INTEGER DEFAULT 0,
	"stk_hst_to_loc"	INTEGER,
	"stk_hst_qty"	INTEGER,
	"stk_hst_operator"	INTEGER,
	"stk_hst_date"	TEXT,
	"stk_hst_disabled"	INTEGER
*/

		$sql	=	'

			SELECT


			geb_product.prod_code,
			geb_product.prod_case_qty,
			geb_location.loc_code,
			geb_stock_history.stk_hst_unit,
			geb_stock_history.stk_hst_qty,
			geb_stock_history.stk_hst_date


			FROM  geb_stock_history

			INNER JOIN geb_product ON geb_stock_history.stk_hst_prod_pkey = geb_product.prod_pkey
			INNER JOIN geb_location ON geb_stock_history.stk_hst_to_loc = geb_location.loc_pkey


			WHERE

			stk_hst_op_type = 10
			
			AND

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
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Product:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_code']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">To location:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['loc_code']) . '</td>';
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
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Qty:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $entry_qty . ' ' . $unit_type_str . '(s)</td>';
						$details_html	.=	'</tr>';





						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">When:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['stk_hst_date']) . '</td>';
						$details_html	.=	'</tr>';


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


