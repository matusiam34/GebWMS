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
	<title><?php	echo $mylang['order_details'];	?></title>
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
//		$order_uid			=	0;				//	obtained from the first query and used in the details query (second one)


		//	Grab the order header data. Things like Order Number, Customer etc etc
		$sql	=	'

			SELECT

			geb_order_header.ordhdr_uid,
			geb_order_header.ordhdr_type,
			geb_order_header.ordhdr_status,
			geb_order_header.ordhdr_pick_operator,
			geb_order_header.ordhdr_pick_start_date,
			geb_order_header.ordhdr_pick_complete_date,
			geb_order_header.ordhdr_enter_date,
			geb_order_header.ordhdr_order_number,
			geb_order_header.ordhdr_customer,
			geb_order_header.ordhdr_bill_address1,
			geb_order_header.ordhdr_bill_address2,
			geb_order_header.ordhdr_bill_address3,
			geb_order_header.ordhdr_bill_address4,
			geb_order_header.ordhdr_bill_address5,
			geb_order_header.ordhdr_ship_address1,
			geb_order_header.ordhdr_ship_address2,
			geb_order_header.ordhdr_ship_address3,
			geb_order_header.ordhdr_ship_address4,
			geb_order_header.ordhdr_ship_address5,
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


				//	Need a bit of a redesign. Keep each table in a different variable.
				$billing_shipping_address	=	'';
				$billing_shipping_address	.=	'<table class="is-fullwidth table is-bordered">';

				$billing_shipping_address	.=	'<tr>';
				$billing_shipping_address	.=	'<th style="width:50%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['shipping_address'] . '</th>';
				$billing_shipping_address	.=	'<th style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['billing_address'] . '</th>';
				$billing_shipping_address	.=	'</tr>';


				$billing_shipping_address	.=	'<tr>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_ship_address1']) . '&nbsp</td>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_bill_address1']) . '</td>';
				$billing_shipping_address	.=	'</tr>';

				$billing_shipping_address	.=	'<tr>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_ship_address2']) . '&nbsp</td>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_bill_address2']) . '</td>';
				$billing_shipping_address	.=	'</tr>';

				$billing_shipping_address	.=	'<tr>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_ship_address3']) . '&nbsp</td>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_bill_address3']) . '</td>';
				$billing_shipping_address	.=	'</tr>';

				$billing_shipping_address	.=	'<tr>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_ship_address4']) . '&nbsp</td>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_bill_address4']) . '</td>';
				$billing_shipping_address	.=	'</tr>';

				$billing_shipping_address	.=	'<tr>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_ship_address5']) . '&nbsp</td>';
				$billing_shipping_address	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_bill_address5']) . '</td>';
				$billing_shipping_address	.=	'</tr>';

				$billing_shipping_address	.=	'</table>';




				$order_details_general_info	=	'';
				$order_details_general_info	.=	'<table class="is-fullwidth table is-bordered">';

				$order_details_general_info	.=	'<tr>';
				$order_details_general_info	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['order'] . ':</td>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_order_number']) . '</td>';
				$order_details_general_info	.=	'</tr>';


				$order_details_general_info	.=	'<tr>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['customer'] . ':</td>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($order_header_arr[0]['ordhdr_customer']) . '</td>';
				$order_details_general_info	.=	'</tr>';


				//	Display_date can be adjusted in lib_functions to display things differently. Changes are global :)
				$act_date	=	display_date( trim($order_header_arr[0]['ordhdr_enter_date']) , $date_display_style);

				$order_details_general_info	.=	'<tr>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['entered_date'] . ':</td>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrB . ';">' . $act_date . '</td>';
				$order_details_general_info	.=	'</tr>';


				//	ordhdr_type...
				//
				//	'100'		=>	'Imported',
				//	'110'		=>	'Place Order'

				$order_type_cde	=	leave_numbers_only($order_header_arr[0]['ordhdr_type']);
				$order_type_str	=	$order_type_arr[$order_type_cde] . ' (' . $order_type_cde . ')';

				$order_details_general_info	.=	'<tr>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['order_type'] . ':</td>';
				$order_details_general_info	.=	'<td style="background-color: ' . $backclrB . ';">' . $order_type_str . '</td>';
				$order_details_general_info	.=	'</tr>';


				$order_details_general_info	.=	'</table>';





				$order_details_picking_info	=	'';
				$order_details_picking_info	.=	'<table class="is-fullwidth table is-bordered">';

				//	ordhdr_status entry...
				//'10'	=>	'On Hold',
				//'20'	=>	'Ready',
				//'30'	=>	'Started',
				//'40'	=>	'Paused',
				//'50'	=>	'Complete (short)',
				//'60'	=>	'Complete',
				//'70'	=>	'Cancelled',

				$order_status_cde	=	leave_numbers_only($order_header_arr[0]['ordhdr_status']);
				$order_status_str	=	$order_status_arr[$order_status_cde] . ' (' . $order_status_cde . ')';

				$order_details_picking_info	.=	'<tr>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold; width:40%;">' . $mylang['order_status'] . ':</td>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrB . ';">' . $order_status_str . '</td>';
				$order_details_picking_info	.=	'</tr>';


				$pick_operator_cde	=	leave_numbers_only($order_header_arr[0]['ordhdr_pick_operator']);
				$pick_operator_str	=	'';	//	no picker name or even "None" if the order is not allocated to one.

				if ($pick_operator_cde > 0)
				{
					//	There is an operator allocated to the job... Get the name!
					$pick_operator_str	=	trim($order_header_arr[0]['user_name']);
				}


				$order_details_picking_info	.=	'<tr>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['picker'] . ':</td>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrB . ';">' . $pick_operator_str . '</td>';
				$order_details_picking_info	.=	'</tr>';

				//	Going to hardcode few things here that probably should be stored in lib_functions.php... ?!?
				$order_details_picking_info	.=	'<tr>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['start_date'] . ':</td>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrB . ';">' . display_date(trim($order_header_arr[0]['ordhdr_pick_start_date']), $date_display_style) . '</td>';
				$order_details_picking_info	.=	'</tr>';


				$order_details_picking_info	.=	'<tr>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['end_date'] . ':</td>';
				$order_details_picking_info	.=	'<td style="background-color: ' . $backclrB . ';">' . display_date(trim($order_header_arr[0]['ordhdr_pick_complete_date']), $date_display_style) . '</td>';
				$order_details_picking_info	.=	'</tr>';


				$order_details_picking_info	.=	'</table>';



				$items_ordered	=	'';



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

					orddet_ordhdr_ordnum = :sorder_number

					ORDER BY orddet_uid

				';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':sorder_number',	$order_number,		PDO::PARAM_STR);
					$stmt->execute();


					$items_ordered	.=	'<table class="is-fullwidth table is-bordered">';

					$items_ordered	.=	'<tr>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['product'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['ordered'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['picked'] . '</th>';
					$items_ordered	.=	'</tr>';



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



						$items_ordered	.=	'<tr>';
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_details_lnk . '</td>';
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['orddet_ord_qty']) . '</td>';
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['orddet_pk_qty']) . '</td>';
						$items_ordered	.=	'</tr>';


					}


					$items_ordered	.=	'</table>';


				}
				// show an error if the query has an error
				else
				{
					$items_ordered	=	'<br>Order Details Query Failed!';
				}



				//	Show everything here... 

				$columns_html	=	'';


				//	Get the first row in...
				$columns_html	.=	'<div class="columns">';

				$columns_html	.=	'<div class="column is-6">';
				$columns_html	.=	$order_details_general_info;
				$columns_html	.=	'</div>';

				$columns_html	.=	'<div class="column is-6">';
				$columns_html	.=	$order_details_picking_info;
				$columns_html	.=	'</div>';

				$columns_html	.=	'</div>';



				//	Second row...
				$columns_html	.=	'<div class="columns">';

				$columns_html	.=	'<div class="column is-6">';
				$columns_html	.=	$billing_shipping_address;
				$columns_html	.=	'</div>';

				$columns_html	.=	'<div class="column is-6">';
				$columns_html	.=	$items_ordered;
				$columns_html	.=	'</div>';

				$columns_html	.=	'</div>';




				//	Show everything!
				echo $columns_html;





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


