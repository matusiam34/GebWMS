<?php


// load the login class
require_once('lib_login.php');


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    

	// load the supporting functions....
	require_once('lib_functions.php');

	//	Certain access right checks should be executed here...
	if (is_it_enabled($_SESSION['menu_adm_category']))
	{




?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['categories'];	?></title>
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

	<script src="js/alertable.js"></script>


	<!-- Favicon
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="icon" type="image/png" href="images/favicon.png">



	<script language="javascript" type="text/javascript">



		$(document).ready(function() 
		{

			// Triggers a function every time the row in the table id_category_a_table is clicked !
			$('#id_category_a_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlightedA').removeClass('highlightedA');
					$(this).addClass('highlightedA');

					// 1 = ID
					$('#id_hidden_cata').val($(this).find('td:nth-child(1)').text()); 

					set_Element_Value_By_ID('id_catb_name',		'');
					set_Element_Value_By_ID('id_catb_status',	0);

					// Get all the details about the category and populate it to the second table!
					get_one_category_a_data();
					get_all_category_b();

			});


			// Triggers a function every time the row in the table id_category_b_table is clicked !
			$('#id_category_b_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlightedB').removeClass('highlightedB');
					$(this).addClass('highlightedB');

					// 1 = ID
					$('#id_hidden_catb').val($(this).find('td:nth-child(1)').text()); 

					// Get all the details about the category!
					get_one_category_b_data();

			});




		});



		function get_all_category_a()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js				:	0

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#id_category_a_table > tbody').empty();
					$('#id_category_a_table > tbody').append(obje.html);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105555', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		function get_all_category_b()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	1,
				cata_uid_js			:	get_Element_Value_By_ID('id_hidden_cata')

			},

			function(output)
			{
				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#id_category_b_table > tbody').empty();
					$('#id_category_b_table > tbody').append(obje.html);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105555', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		//	Add category A
		function add_category_a()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	4,
				cata_name_js		:	get_Element_Value_By_ID('id_cata_name'),
				cata_status_js		:	get_Element_Value_By_ID('id_cata_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_cata_name',		'');
					set_Element_Value_By_ID('id_cata_status',	0);
					set_Element_Value_By_ID('id_hidden_cata',	0);

					//	Refresh the list
					$('#id_category_b_table > tbody').empty();
					get_all_category_a();

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101557', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		//	Add category B
		function add_category_b()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	5,
				cata_uid_js			:	get_Element_Value_By_ID('id_hidden_cata'),
				catb_name_js		:	get_Element_Value_By_ID('id_catb_name'),
				catb_status_js		:	get_Element_Value_By_ID('id_catb_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catb_name',		'');
					set_Element_Value_By_ID('id_catb_status',	0);
					//	Refresh the list
					get_all_category_b();

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101557', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		function get_one_category_a_data()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	2,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_cata')

			},
			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_cata_name',		obje.data.cat_name);
					set_Element_Value_By_ID('id_cata_status',	obje.data.cat_disabled);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101556', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		function get_one_category_b_data()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	3,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catb')

			},
			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catb_name',		obje.data.cat_name);
					set_Element_Value_By_ID('id_catb_status',	obje.data.cat_disabled);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101556', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		//	Add warehouse
		function add_warehouse()
		{

			$.post('geb_ajax_warehouse.php', { 

				action_code_js				:	2,
				warehouse_name_js			:	get_Element_Value_By_ID('id_warehouse_name'),
				warehouse_status_js			:	get_Element_Value_By_ID('id_warehouse_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					//	Refresh the list
					get_all_warehouses();	// repopulate the table

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101557', '<?php	echo $mylang['server_error'];	?>');
					});

		}





		//	Update warehouse details
		function update_warehouse()
		{

			$.post('geb_ajax_warehouse.php', { 

				action_code_js				:	3,
				warehouse_uid_js			:	get_Element_Value_By_ID('id_hidden'),
				warehouse_name_js			:	get_Element_Value_By_ID('id_warehouse_name'),
				warehouse_status_js			:	get_Element_Value_By_ID('id_warehouse_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					//	Refresh the list of warehouses!
					get_all_warehouses();
					$.alertable.info(obje.control, obje.msg).always(function() {	});

				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101558', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
					});

		}




	</script>





<style>


	.category_a_table { height: 320px; overflow-y: scroll;}

	.category_b_table { height: 320px; overflow-y: scroll;}


	/*	The sticky header... not perfect but works for now !! Not sure if I wanna use it here... hmmm...	*/

	table th
	{
		position: sticky;
		top: 0;
		background: #eee;
	}

	/*      For changing the colour of the clicked row in the table         */
	.highlightedA {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}

	.highlightedB {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}


</style>



</head>
<body onLoad='get_all_category_a();'>


<?php

		// A little gap at the top to make it look better a notch.
		echo '<div class="blank_space_12px"></div>';

		echo '<section class="section is-paddingless">';
		echo	'<div class="container box has-background-light">';


				$page_form	=	'<p class="control">';
				$page_form	.=		'<button class="button admin_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
				$page_form	.=	'</p>';


				// The menu!
				echo '<nav class="level">

					<!-- Left side -->
					<div class="level-left">

					<div class="level-item">
				' . $page_form . '
					</div>

					</div>

				</nav>';




	$layout_details_html	=	'';

	$layout_details_html	.=	'<div class="columns">';



	$layout_details_html	.=	'

					<div class="column is-4">

						<div class="category_a_table it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="id_category_a_table">
								<thead>
									<tr>
										<th>UID</th>
										<th>' . $mylang['category'] . ' (A)</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['category'] . ' (A):</p>
							<div class="control">
								<input id="id_cata_name" class="input is-normal" type="text" placeholder="Houseware">
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ' (A):</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_cata_status">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="update_cata();">' . $mylang['save'] . ' (A)</button>
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_category_a();">' . $mylang['add'] . ' (A)</button>
							</div>
						</div>


						<input id="id_hidden_cata" class="input is-normal" type="hidden">

					</div>';



	// Details
	$layout_details_html	.=	'


					<div class="column is-4">


						<div class="category_b_table it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="id_category_b_table">
								<thead>
									<tr>
										<th>UID</th>
										<th>' . $mylang['category'] . ' (B)</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['category'] . ' (B):</p>
							<div class="control">
								<input id="id_catb_name" class="input is-normal" type="text" placeholder="Buckets">
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ' (B):</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_catb_status">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="update_catb();">' . $mylang['save'] . ' (B)</button>
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_category_b();">' . $mylang['add'] . ' (B)</button>
							</div>
						</div>

						<input id="id_hidden_catb" class="input is-normal" type="hidden">

					</div>


						';








	$layout_details_html	.=	'

				</div>';


echo	$layout_details_html;






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

    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');

}

?>



</body>
</html>
