<?php


//	If the operator is not allocated to any order than please allow him to select one to do.
//	If an order has the picker set to > 0 than show the picking page... To be done that is!

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
	if (is_it_enabled($_SESSION['menu_pick_order']))
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
	<title><?php	echo $mylang['pick_order'];	?></title>
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

			// Do things when operator clicks on the row.
			$('#curr_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlighted').removeClass('highlighted');
					$(this).addClass('highlighted');

					// 1 = ID
					// 2 = Order Number
					// 3 = Number of Lines in the order
					$('#id_hidden').val($(this).find('td:nth-child(1)').text()); 

			});


		});


		// Bind the order with the operator! 
		function claim_order()
		{

			$.post('ajax_claim_order4picking.php', { 

				order_uid_js	:	get_Element_Value_By_ID('id_hidden')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					//	Need to refresh the page to show the picking screen.
					window.location.href = 'gv_pick_order.php';

				}
				else
				{
					alert(obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						alert('server problem');
					});

		}



	</script>



<style>



	/*      For changing the colour of the clicked row in the table         */
	.highlighted {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}




	.tableAttr { height: 360px; overflow-y: scroll;}



	/*	The sticky header... not perfect but works for now !! Not sure if I wanna use it here... hmmm...	*/

	table th
	{
		position: sticky;
		top: 0;
		background: #eee;
	}



</style>



</head>
<body>





<?php


	// A little gap at the top to make it look better a notch.
	echo '<div style="height:12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_form	=	'';

/*
	$page_form	.=	'<p class="control">';
	$menu_link	=	"'index.php'";
	$page_form	.=		'<button class="button inventory_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
	$page_form	.=	'</p>';
*/

	$page_form	.=	'<p class="control">';
	$page_form	.=		'<button class="button inventory_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
	$page_form	.=	'</p>';


	// Not sure if the escape \ is the perfect match... Works tho.
	$page_form	.=	'<p class="control">';
	$page_form	.=		'<button class="button inventory_class iconRefresh" style="width:50px;" onClick="window.location.href = \'gv_pick_order.php\';"></button>';
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

		//	Before going any further first make sure to check if the operator is not already
		//	in the middle of a picking job. Because if that is the case that means that
		//	I need to show a picking screen and not the order selection screen.
		//	Simples :)

		//	Only check if the order is active. What I want to allow is for the operator to be picking
		//	and the manager going : nahh mate, we need to pause this picking because Dan needs to do a replen.
		//	So can he needs to be able to put the order in a pause mode but this should not stop the picker
		//	from going to the screen and selecting a different order to pick while the other order is being sorted out
		//	for stock or other issues.


		$current_order_uid		=	0;		//	UID of the order number 
		$current_order_number	=	'';		//	order number that is currently being processed by the operator (the string)
		$current_order_pick_start_date	=	'';		//	when it all started...


		$sql	=	'

			SELECT

			ordhdr_uid,
			ordhdr_order_number,
			ordhdr_pick_start_date

			FROM 

			geb_order_header


			WHERE

			ordhdr_pick_operator = :soperator_uid

			AND

			ordhdr_status = :sStarted

		';


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':soperator_uid',	leave_numbers_only($_SESSION['user_id']),		PDO::PARAM_INT);
			$stmt->bindValue(':sStarted',		$order_status_reverse_arr['S'],					PDO::PARAM_INT);
			//$stmt->bindValue(':sPaused',		$order_status_reverse_arr['P'],					PDO::PARAM_INT);

			$stmt->execute();


			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				//	Now... The operator can only be assigned to one order at a time.
				//	Needs to be a tool that will notify the manager or admin if that is not
				//	the case so that it can be fixed otherwise strange things will happen!
				//	Maybe given give the operator a warning as well to go and see a manager / admin??!?!
				$current_order_uid					=	leave_numbers_only($row['ordhdr_uid']);
				$current_order_number				=	trim($row['ordhdr_order_number']);

				//	Display_date can be adjusted in lib_functions to display things differently. Changes are global :)
				$current_order_pick_start_date	=	display_date( trim($row['ordhdr_pick_start_date']) , $date_display_style);
			}


		}






		//	Operator is free to take on any order!
		if ($current_order_uid == 0)
		{

			$orders_arr			=	array();		//	all pickable orders here


			//	Only show orders with the order_status = Ready
			$sql	=	'

				SELECT

				geb_order_header.ordhdr_uid,
				geb_order_header.ordhdr_order_number,
				COUNT(*) as linesPerOrder

				FROM 

				geb_order_header

				INNER JOIN geb_order_details ON geb_order_header.ordhdr_order_number = geb_order_details.orddet_ordhdr_ordnum

				WHERE

				geb_order_header.ordhdr_status = :sorder_ready_status

				GROUP BY geb_order_header.ordhdr_uid, geb_order_header.ordhdr_order_number

			';


			$columns_html	=	'';
			$details_html	=	'';



			if ($stmt = $db->prepare($sql))
			{

				//	Get only orders that are status = Ready (lib_functions.php)
				$stmt->bindValue(':sorder_ready_status',	$order_status_reverse_arr['R'],		PDO::PARAM_STR);
				$stmt->execute();


				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$orders_arr[]	=	$row;
				}



				//	If there is anything of interest please build a simple page with a table that holds all of the
				//	orders that are Ready for picking. Provide the line amount and order number,
				if (count($orders_arr) > 0)
				{


					echo	'<div class="columns">

								<div class="column is-6">
									<div class="tableAttr it-has-border">
										<table class="table is-fullwidth is-hoverable is-scrollable"  style="table-layout:fixed;" id="curr_table">
										<thead>
											<tr>
												<th>UID</th>
												<th>' . $mylang['order'] . '</th>
												<th>' . $mylang['entries'] . '</th>
											</tr>
										</thead>


										<tbody>';


										foreach ($orders_arr as $order_line)
										{
											echo	'<tr>';
											echo	'<td>' . leave_numbers_only($order_line['ordhdr_uid']) . '</td>';
											echo	'<td>' . trim($order_line['ordhdr_order_number']) . '</td>';
											echo	'<td>' . $order_line['linesPerOrder'] . '</td>';
											echo	'</tr>';
										}



					echo				'</tbody>
										</table>

									</div>';



					echo	'<div class="field" style="'. $box_size_str .'">
								<p class="help">&nbsp;</p>
								<div class="control">
									<button class="button inventory_class is-fullwidth" onclick="claim_order();">' . $mylang['pick_order'] . '</button>
								</div>
							</div>


							<div class="control">
								<input id="id_hidden" class="input is-normal" type="hidden" value="0">
							</div>';




					echo		'</div>';	//	column close

					echo	'</div>';	//	close the entire columns div






				}	//	END OF count($orders_arr) > 0
				else
				{
					//	say something about no orders to pick?
				}





			}
			// show an error if the query has an error
			else
			{
				echo 'Order Query Failed!';
			}

		}
		else
		if ($current_order_uid > 0)
		{


			//
			//
			//
			//	The operator needs a menu to allow picking!
			//
			//
			//


			$columns_html	=	'';
			$details_html	=	'';


			$columns_html	.=	'<div class="columns">';
			$columns_html	.=	'<div class="column is-6">';

			$details_html	.=	'<table class="is-fullwidth table is-bordered">';

				$details_html	.=	'<tr>';
					$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['order'] . ':</td>';
					$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $current_order_number . '</td>';
				$details_html	.=	'</tr>';


				$details_html	.=	'<tr>';
					$details_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['start_date'] . ':</td>';
					$details_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $current_order_pick_start_date . '</td>';
				$details_html	.=	'</tr>';







			$details_html	.=	'</table>';



			$columns_html	.=	$details_html;	// place the table in the column...
			$details_html	=	"";				// empty for the next run!
			$columns_html	.=	'</div>';


			// End of columns div!
			$columns_html	.=	'</div>';


			// Show the order header details!
			echo	$columns_html;





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


