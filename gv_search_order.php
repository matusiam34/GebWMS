<?php

//	By default when the order hits the system it becomes "Ready2Pick" and will show on the picker gun as an option.
//	This can be most likely controlled by a flag of sorts to change this. For example maybe how the business
//	is designed someone needs to authorise the order to be available for pickers to select on their gun.
//	In that scenario the order could be named : On Hold or Requires Approval. Something to that tune


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
	if (is_it_enabled($_SESSION['menu_order_search']))
	{

		// needs a db connection...
		require_once('lib_db_conn.php');

		$order_number		=	'';
		
		if (isset($_GET['ordnum']))
		{
			$order_number		=	trim($_GET['ordnum']);
		}

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Order Search</title>
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


	$page_form	=	'

	<form action="gv_search_order.php" method="get">

		<div class="field has-addons">

			<p class="control">
				<input class="input" type="text" id="ordnum" name="ordnum" placeholder="Order number" value="' . $order_number . '">
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


		$order_header_arr	=	array();		//	store all header info here
		$order_uid			=	0;				//	obtained from the first query and used in the details query (second one)


		//	Grab the order header data. Things like Order Number, Customer etc etc
		$sql	=	'

			SELECT

			geb_order_header.ordhdr_uid,
			geb_order_header.ordhdr_status,
			geb_order_header.ordhdr_pick_operator,
			geb_order_header.ordhdr_pick_start_date,
			geb_order_header.ordhdr_pick_complete_date,
			geb_order_header.ordhdr_enter_date,
			geb_order_header.ordhdr_order_number,
			geb_order_header.ordhdr_customer,

			users.user_name


			FROM 

			geb_order_header

			LEFT JOIN users ON geb_order_header.ordhdr_pick_operator = users.user_id


			WHERE

			ordhdr_order_number = :sorder_number

		';


		$columns_html	=	'';
		$details_html	=	'';



		if ($stmt = $db->prepare($sql))
		{


			$stmt->bindValue(':sorder_number',	$order_number,	PDO::PARAM_STR);
			$stmt->execute();


			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$order_header_arr[]	=	$row;
			}


			// Analyise what the order_header_arr has to offer...

			if (count($order_header_arr) == 1)
			{

				//	Ok, order has been found in the system. Give it to the operator!
				$order_uid		=	leave_numbers_only($order_header_arr[0]['ordhdr_uid']);

				$columns_html	.=	'<div class="columns">';
				$columns_html	.=	'<div class="column is-6">';

					$details_html	.=	'<table class="is-fullwidth table is-bordered">';

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">Order:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_order_number']) . '</td>';
						$details_html	.=	'</tr>';


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Customer:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_customer']) . '</td>';
						$details_html	.=	'</tr>';



						//	mateusz
						$enter_date		=		trim($order_header_arr[0]['ordhdr_enter_date']);

						$act_date		=		date('d/m/Y', strtotime($enter_date));
						$act_time		=		date('H:i:s', strtotime($enter_date));



						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Enter Date:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $act_date . ' at ' . $act_time . '</td>';
						$details_html	.=	'</tr>';



/*

	"ordhdr_uid"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"ordhdr_status"	INTEGER DEFAULT 0,
	"ordhdr_pick_status"	INTEGER DEFAULT 0,
	"ordhdr_pick_start_date"
	"ordhdr_pick_operator"	INTEGER DEFAULT 0,
	"ordhdr_pick_complete_date"	TEXT,

*/

						//	ordhdr_pick_status entry...
						//	'0'		=>	'Imported',
						//	'10'	=>	'Ready2Pick',
						//	'20'	=>	'Started',
						//	'30'	=>	'On Hold',
						//	'40'	=>	'Complete',
						//	'50'	=>	'Cancelled',


						$order_status_cde	=	leave_numbers_only($order_header_arr[0]['ordhdr_status']);
						$order_status_str	=	$order_status_reverse_arr[$order_status_cde];

						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Order Status:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $order_status_str . '</td>';
						$details_html	.=	'</tr>';


						$pick_operator_cde	=	leave_numbers_only($order_header_arr[0]['ordhdr_pick_operator']);
						$pick_operator_str	=	'None';

						if ($pick_operator_cde > 0)
						{
							//	There is an operator allocated to the job... Get the name!
							$pick_operator_str	=	trim($order_header_arr[0]['user_name']);
						}


						$details_html	.=	'<tr>';
							$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Picker:</td>';
							$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $pick_operator_str . '</td>';
						$details_html	.=	'</tr>';






						// Show more details about the pick status when the status is actually of some merit aka > 0
						if ($order_status_str > 0)
						{


							//	mateusz
							//	Going to hardcode few things here that probably should be stored in lib_functions.php...


							$details_html	.=	'<tr>';
								$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Pick Start:</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $order_status_str . '</td>';
							$details_html	.=	'</tr>';


							$details_html	.=	'<tr>';
								$details_html	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">Pick End:</td>';
								$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $order_status_str . '</td>';
							$details_html	.=	'</tr>';

						}





					$details_html	.=	'</table>';



				$columns_html	.=	$details_html;	// place the table in the column...
				$details_html	=	"";				// empty for the next run!
				$columns_html	.=	'</div>';


				// End of columns div!
				$columns_html	.=	'</div>';


				// Show the order header details!
				echo	$columns_html;



				//	Next step is to get information about order details... What product, qty and what has been picked or not!


				$sql	=	'

					SELECT

					geb_order_details.orddet_ord_qty,
					geb_order_details.orddet_pk_qty,
					geb_product.prod_code

					FROM 

					geb_order_details
					
					INNER JOIN geb_product ON geb_order_details.orddet_prod_pkey = geb_product.prod_pkey

					WHERE

					orddet_ordhdr_uid = :sorder_number

					ORDER BY orddet_uid

				';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':sorder_number',	$order_uid,		PDO::PARAM_INT);
					$stmt->execute();

					$columns_html	=	'';
					$details_html	=	'';


					$columns_html	.=	'<div class="columns">';
					$columns_html	.=	'<div class="column is-6">';
					$details_html	.=	'<table class="is-fullwidth table is-bordered">';

					$details_html	.=	'<tr>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">Product</th>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">Ordered</th>';
					$details_html	.=	'<th style="background-color: ' . $backclrA . ';">Picked</th>';
					$details_html	.=	'</tr>';



					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{




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
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_details_lnk . '</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['orddet_ord_qty']) . '</td>';
						$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['orddet_pk_qty']) . '</td>';
						$details_html	.=	'</tr>';


					}


					$details_html	.=	'</table>';

					$columns_html	.=	$details_html;	// place the table in the column...
					$columns_html	.=	'</div>';

					// End of columns div!
					$columns_html	.=	'</div>';

					// Show the details of the order: products, order vs picked qty etc
					echo	$columns_html;


				}
				// show an error if the query has an error
				else
				{
					echo '<br>Order Details Query Failed!';
				}









			}
			else
			{
				//	No order found. Provide a message ?
			}


		}
		// show an error if the query has an error
		else
		{
			echo 'Order Query Failed!';
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


