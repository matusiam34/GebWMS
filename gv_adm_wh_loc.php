<?php


//	TODO:	The location type should be populated from the variable from the lib_functions.php


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


	//	Certain access right checks should be executed here...
	if (is_it_enabled($_SESSION['menu_adm_warehouse_loc']))
	{



?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['warehouse_locations'];	?></title>
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


			$('#curr_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlighted').removeClass('highlighted');
					$(this).addClass('highlighted');


					//	Check if the row has a numer
					if ($.isNumeric(	$(this).find('td:nth-child(1)').text()	))
					{
						$('#id_hidden').val($(this).find('td:nth-child(1)').text()); 
						get_location();
					}



			});


		});





		// Grab all current locations !
		function get_all_locations()
		{

			$.post('ajax_get_all_wh_locations.php', { 

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					$('#curr_table').empty();
					$('#curr_table').append(obje.html);

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




		// Get one location... potentially should be fixed in the future but for now this will do.
		function get_location()
		{

			$.post('ajax_get_one_wh_location.php', { 

				loc_uid_js	:	get_Element_Value_By_ID('id_hidden')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					set_Element_Value_By_ID('id_warehouse', obje.data.wh_pkey);
					set_Element_Value_By_ID('id_location_name', obje.data.loc_code);
					set_Element_Value_By_ID('id_barcode', obje.data.loc_barcode);
					set_Element_Value_By_ID('id_item_type', obje.data.loc_type);
					set_Element_Value_By_ID('id_function', obje.data.loc_function);
					set_Element_Value_By_ID('id_blocked', obje.data.loc_blocked);
					set_Element_Value_By_ID('id_desc', obje.data.loc_note);
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




		// Add location to the system
		function add_item()
		{


			$.post('ajax_add_location.php', { 

				warehouse_js	:	get_Element_Value_By_ID('id_warehouse'),
				location_js		:	get_Element_Value_By_ID('id_location_name'),
				barcode_js		:	get_Element_Value_By_ID('id_barcode'),
				function_js		:	get_Element_Value_By_ID('id_function'),
				type_js			:	get_Element_Value_By_ID('id_item_type'),
				blocked_js		:	get_Element_Value_By_ID('id_blocked'),
				loc_desc_js		:	get_Element_Value_By_ID('id_desc')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					//set_Element_Value_By_ID('id_item_name', '');
					get_all_locations();	// repopulate the table !
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



		// UPDATE location
		function update_item()
		{

			$.post('ajax_update_location.php', { 

				loc_uid_js		:	get_Element_Value_By_ID('id_hidden'),
				warehouse_js	:	get_Element_Value_By_ID('id_warehouse'),
				location_js		:	get_Element_Value_By_ID('id_location_name'),
				barcode_js		:	get_Element_Value_By_ID('id_barcode'),
				function_js		:	get_Element_Value_By_ID('id_function'),
				type_js			:	get_Element_Value_By_ID('id_item_type'),
				blocked_js		:	get_Element_Value_By_ID('id_blocked'),
				loc_desc_js		:	get_Element_Value_By_ID('id_desc')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					get_all_locations();	// repopulate the table !
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




		// Get all warehouses
		function get_all_warehouses()
		{

			$.post('ajax_get_all_warehouses.php', { 

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					// jQuery - remove all entries
					$('#id_warehouse').empty();


					// The first entry
					var opt = document.createElement('Option');
					document.getElementById('id_warehouse').options.add(opt);
					opt.value = 0;
					opt.text = '-----';


					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{

							var opt = document.createElement('Option');
							document.getElementById('id_warehouse').options.add(opt);
							opt.value = obje.data[i].warehouse_id;
							opt.text = obje.data[i].warehouse_name;

						}

					}

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



	.tableAttr { height: 400px; overflow-y: scroll;}


	/*	The sticky header... not perfect but works for now !! Not sure if I wanna use it here... hmmm...	*/

	table th
	{
		position: sticky;
		top: 0;
		background: #eee;
	}


	/*      For changing the colour of the clicked row in the table         */
	.highlighted {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}


</style>



</head>
<body onLoad='get_all_locations(); get_all_warehouses();'>



<?php



		// A little gap at the top to make it look better a notch.
		echo '<div style="height:12px"></div>';

		echo '<section class="section is-paddingless">';
		echo	'<div class="container box has-background-light">';



				$page_form	=	'<p class="control">';
				$page_form	.=		'<button class="button admin_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
				$page_form	.=	'</p>';


				// The "menu"!
				echo '<nav class="level">

				<!-- Left side -->
					<div class="level-left">

					<div class="level-item">
				' . $page_form . '
					</div>

					</div>

				</nav>';




?>


				<div class="columns">

					<div class="column is-12">
						<div class="tableAttr it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable " id="curr_table">
								<tbody>
								</tbody>
							</table>
						</div>
					</div>

				</div>



				<div class="columns">


					<div class="column is-3">

						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help">Warehouse</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_warehouse" name="id_warehouse" >
									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help">Location:</p>
							<div class="control">
								<input id="id_location_name" class="input is-normal" type="text" placeholder="B003A">
							</div>
						</div>


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help">Barcode:</p>
							<div class="control">
								<input id="id_barcode" class="input is-normal" type="text" placeholder="7334764234185">
							</div>
						</div>

				</div>



				<div class="column is-3">


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help">Function:</p>
							<div class="field is-narrow">
								<div class="control">
									<div class="select is-fullwidth">
										<select id="id_function" name="id_function" >
											<!--	Populate this so that it uses the array from lib_functions			-->
											<?php

												foreach ($loc_functions_arr as $locid => $locdescription)
												{
													echo	'<option value="' . $locid . '">' . $locdescription . '</option>';
												}

											?>
										</select>
									</div>
								</div>
							</div>
						</div>



						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help">Type:</p>
							<div class="field is-narrow">
								<div class="control">
									<div class="select is-fullwidth">
										<select id="id_item_type" name="id_item_type" >

											<!--	Populate this so that it uses the array from lib_functions			-->
											<option value="10">Single</option>
											<option value="20">Multi</option>
											<option value="30">Mixed</option>
										</select>
									</div>
								</div>
							</div>
						</div>



						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help">Blocked:</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
								  <select id="id_blocked" name="id_blocked" >
									<option value="0">No</option>
									<option value="1">Yes</option>
								  </select>
								</div>
							  </div>
							</div>
						</div>

				</div>



				<div class="column is-3">

					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help">Note:</p>
						<div class="control">
							<input id="id_desc" class="input is-normal" type="text" placeholder="do not use">
						</div>
					</div>

				</div>


				<!--	The &nbsp; in the <p class="help"></p> is just a "fix" so that everything aligns otherwise it looks odd...		-->


				<div class="column is-3">



<?php


	// If the operator has the ability to add...
	if (can_user_add($_SESSION['menu_adm_warehouse_loc']))
	{
		echo	'

		<div class="field" style="'. $box_size_str .'">
			<p class="help">&nbsp;</p>
			<div class="control">
				<button class="button admin_class is-fullwidth"  onclick="add_item();">Add</button>
			</div>
		</div>';
	}


	// If the operator has the ability to update...
	if (can_user_update($_SESSION['menu_adm_warehouse_loc']))
	{
		echo	'

		<div class="field" style="'. $box_size_str .'">
			<p class="help">&nbsp;</p>
			<div class="control">
				<button class="button admin_class is-fullwidth"  onclick="update_item();">Update</button>
			</div>
		</div>';
	}



?>

				</div>






				<input id="id_hidden" class="input is-normal" type="hidden" value="0">

			</div>







<?php



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
