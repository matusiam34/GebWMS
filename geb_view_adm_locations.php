<?php


//	barcode of each location HAS TO BE DIFFERENT!! The name can be the same! Nobody cares about that!


// load the login class
require_once('lib_login.php');


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    

	// load the supporting functions....
	require_once('lib_system.php');


	//	Certain access right checks should be executed here...
	if (is_it_enabled($_SESSION['menu_adm_warehouse_loc']))
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

	<script src="js/alertable.js"></script>

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



			// When the operator selects a new category A = fetch the related category B entries!
			$('#id_category_a').change(function() {
				get_all_category_b();
				emptySelectBox('id_category_c');
				addOption2SelectBox('id_category_c', 0, '<?php	echo $mylang['none'];	?>');
				emptySelectBox('id_category_d');
				addOption2SelectBox('id_category_d', 0, '<?php	echo $mylang['none'];	?>');
			});

			// When the operator selects a new category B = fetch the related category C entries!
			$('#id_category_b').change(function() {
				get_all_category_c();
				emptySelectBox('id_category_d');
				addOption2SelectBox('id_category_d', 0, '<?php	echo $mylang['none'];	?>');
			});


			// When the operator selects a new category C = fetch the related category D entries!
			$('#id_category_c').change(function() {
				get_all_category_d();
			});





		});





		// Grab all locations !
		function get_all_locations()
		{

			$.post('geb_ajax_location.php', { 

				action_code_js				:	0

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
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('102555', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		function get_location()
		{

			$.post('geb_ajax_location.php', {
				action_code_js: 1,
				loc_uid_js: get_Element_Value_By_ID('id_hidden')
			},

			function (output)
			{

				const obje = jQuery.parseJSON(output);

				if (obje.control === 0)
				{

					// Set element values
					set_Element_Value_By_ID('id_warehouse', obje.data.wh_pkey);
					set_Element_Value_By_ID('id_location_name', obje.data.loc_code);
					set_Element_Value_By_ID('id_barcode', obje.data.loc_barcode);
					set_Element_Value_By_ID('id_function', obje.data.loc_function);
					set_Element_Value_By_ID('id_type', obje.data.loc_type);
					set_Element_Value_By_ID('id_blocked', obje.data.loc_blocked);
					set_Element_Value_By_ID('id_desc', obje.data.loc_note);
					set_Element_Value_By_ID('id_location_status', obje.data.loc_disabled);
					set_Element_Value_By_ID('id_magic_product_name', obje.data.prod_code);
					set_Element_Value_By_ID('id_max_qty', obje.data.loc_max_qty);

					// Append HTML to elements of categories
					$('#id_category_a').empty().append(obje.data.cat_a_html);
					$('#id_category_b').empty().append(obje.data.cat_b_html);
					$('#id_category_c').empty().append(obje.data.cat_c_html);
					$('#id_category_d').empty().append(obje.data.cat_d_html);


				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function () {});
				}
			})
			.fail(function () {
				$.alertable.error('102556', '<?php echo $mylang['server_error']; ?>');
			});
		}






		// Add location to the system
		function add_item()
		{


			$.post('geb_ajax_location.php', { 

				action_code_js		:	2,
				warehouse_js		:	get_Element_Value_By_ID('id_warehouse'),
				location_js			:	get_Element_Value_By_ID('id_location_name'),
				barcode_js			:	get_Element_Value_By_ID('id_barcode'),
				function_js			:	get_Element_Value_By_ID('id_function'),
				type_js				:	get_Element_Value_By_ID('id_type'),
				blocked_js			:	get_Element_Value_By_ID('id_blocked'),
				loc_desc_js			:	get_Element_Value_By_ID('id_desc'),
				loc_cat_a_js		:	get_Element_Value_By_ID('id_category_a'),
				loc_cat_b_js		:	get_Element_Value_By_ID('id_category_b'),
				loc_cat_c_js		:	get_Element_Value_By_ID('id_category_c'),
				loc_cat_d_js		:	get_Element_Value_By_ID('id_category_d'),
				magic_product_js	:	get_Element_Value_By_ID('id_magic_product_name'),
				max_qty_js			:	get_Element_Value_By_ID('id_max_qty'),
				disabled_js			:	get_Element_Value_By_ID('id_location_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					get_all_locations();	// repopulate the table !
					$.alertable.info(obje.control, obje.msg).always(function() {	});
				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('102557', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		// UPDATE location
		function update_location()
		{

			$.post('geb_ajax_location.php', { 

				action_code_js		:	3,
				loc_uid_js			:	get_Element_Value_By_ID('id_hidden'),
				warehouse_js		:	get_Element_Value_By_ID('id_warehouse'),
				location_js			:	get_Element_Value_By_ID('id_location_name'),
				barcode_js			:	get_Element_Value_By_ID('id_barcode'),
				function_js			:	get_Element_Value_By_ID('id_function'),
				type_js				:	get_Element_Value_By_ID('id_type'),
				cat_a_js			:	get_Element_Value_By_ID('id_category_a'),
				cat_b_js			:	get_Element_Value_By_ID('id_category_b'),
				cat_c_js			:	get_Element_Value_By_ID('id_category_c'),
				cat_d_js			:	get_Element_Value_By_ID('id_category_d'),
				blocked_js			:	get_Element_Value_By_ID('id_blocked'),
				loc_desc_js			:	get_Element_Value_By_ID('id_desc'),
				magic_product_js	:	get_Element_Value_By_ID('id_magic_product_name'),
				max_qty_js			:	get_Element_Value_By_ID('id_max_qty'),
				disabled_js			:	get_Element_Value_By_ID('id_location_status')
				

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					get_all_locations();	// repopulate the table !
					set_Element_Value_By_ID('id_warehouse', 0);
					set_Element_Value_By_ID('id_location_name', '');
					set_Element_Value_By_ID('id_barcode', '');
					set_Element_Value_By_ID('id_function', 0);
					set_Element_Value_By_ID('id_type', 0);
					set_Element_Value_By_ID('id_hidden', 0);
					set_Element_Value_By_ID('id_max_qty', 0);
					set_Element_Value_By_ID('id_magic_product_name', '');
					set_Element_Value_By_ID('id_category_a', 0);
					set_Element_Value_By_ID('id_category_b', 0);
					set_Element_Value_By_ID('id_category_c', 0);
					set_Element_Value_By_ID('id_category_d', 0);
					set_Element_Value_By_ID('id_blocked', 0);
					set_Element_Value_By_ID('id_desc', '');
					set_Element_Value_By_ID('id_location_status', 0);
					set_Element_Value_By_ID('id_hidden', 0);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('102558', '<?php	echo $mylang['server_error'];	?>');
					});

		}



	function get_all_warehouses()
	{

		$.post('geb_ajax_warehouse.php', {

			action_code_js: 20

		})
		.done(function (output)
		{
			const obje = jQuery.parseJSON(output);

			if (obje.control === 0)
			{

				const warehouseSelect = $('#id_warehouse');
				warehouseSelect.empty();

				if (obje.data && obje.data.length > 0)
				{
					obje.data.forEach(function(item)
					{
						warehouseSelect.append($('<option>',
						{
							value: item.wh_pkey,
							text: item.wh_code
						}));
					});
				}
			}
			else
			{
				$.alertable.error(obje.control, obje.msg);
			}
		})
		.fail(function() {
			// Something went wrong
			$.alertable.error('102559', '<?php echo $mylang["server_error"]; ?>');
		});
	}




		// Get category B based on category A
		function get_all_category_b()
		{

			const category_a_val = get_Element_Value_By_ID('id_category_a');
			const postData =
			{
				action_code_js:			1,
				action_format_js:		1,
				action_disabled_js:		0,
				cat_uid_js:				category_a_val
			};

			$.post('geb_ajax_category.php', postData)
				.done(function (output)
				{
					const obje = jQuery.parseJSON(output);
					
					if (obje.control === 0)
					{
						var len = obje.data.length;

						emptySelectBox('id_category_b');
						addOption2SelectBox('id_category_b', 0, '<?php	echo $mylang['none'];	?>');	

						if(len > 0)
						{
							for (var i = 0; i < len; i++)
							{
								addOption2SelectBox('id_category_b', obje.data[i].cat_b_pkey, obje.data[i].cat_b_name);	
							}
						}
					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}
				})
				.fail(function () {
					$.alertable.error('102559', '<?php echo $mylang["server_error"]; ?>');
				});

		}





		function get_all_category_c()
		{
			const category_b_val = get_Element_Value_By_ID('id_category_b');
			const postData =
			{
				action_code_js:			2,
				action_format_js:		1,
				action_disabled_js:		0,
				cat_uid_js: 			category_b_val
			};

			$.post('geb_ajax_category.php', postData)
				.done(function (output) {
					const obje = jQuery.parseJSON(output);
					
					if (obje.control === 0)
					{
						var len = obje.data.length;

						emptySelectBox('id_category_c');
						addOption2SelectBox('id_category_c', 0, '<?php	echo $mylang['none'];	?>');	

						if(len > 0)
						{
							for (var i = 0; i < len; i++)
							{
								addOption2SelectBox('id_category_c', obje.data[i].cat_c_pkey, obje.data[i].cat_c_name);	
							}
						}
					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}
				})
				.fail(function () {
					$.alertable.error('102559', '<?php echo $mylang["server_error"]; ?>');
				});
		}





		function get_all_category_d()
		{
			const category_c_val = get_Element_Value_By_ID('id_category_c');
			const postData =
			{
				action_code_js:			3,
				action_format_js:		1,
				action_disabled_js:		0,
				cat_uid_js: 			category_c_val
			};

			$.post('geb_ajax_category.php', postData)
				.done(function (output) {
					const obje = jQuery.parseJSON(output);
					
					if (obje.control === 0)
					{
						var len = obje.data.length;

						emptySelectBox('id_category_d');
						addOption2SelectBox('id_category_d', 0, '<?php	echo $mylang['none'];	?>');	

						if(len > 0)
						{
							for (var i = 0; i < len; i++)
							{
								addOption2SelectBox('id_category_d', obje.data[i].cat_d_pkey, obje.data[i].cat_d_name);	
							}
						}
					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}
				})
				.fail(function () {
					$.alertable.error('102559', '<?php echo $mylang["server_error"]; ?>');
				});
		}






	</script>





<style>



	.tableAttr { height: 400px; overflow-y: scroll;}


	/*	The sticky header... not perfect but works for now !! Not sure if I wanna use it here... hmmm...	*/


	table th
	{
		position: -webkit-sticky; /* For Safari */
		position: sticky;
		top: 0;
		background: #eee;
		z-index: 1; /* Ensures the header is above other elements */
	}


	/*      For changing the colour of the clicked row in the table         */
	.highlighted {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}


</style>



</head>
<body onLoad='get_all_warehouses(); get_all_locations();'>



<?php


		// A little gap at the top to make it look better a notch.
		echo '<div class="blank_space_12px"></div>';

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
							<p class="help"><?php	echo $mylang['warehouse'];		?></p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_warehouse">
									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['location'];		?></p>
							<div class="control">
								<input id="id_location_name" class="input is-normal" type="text" placeholder="B003A">
							</div>
						</div>


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['barcode'];		?></p>
							<div class="control">
								<input id="id_barcode" class="input is-normal" type="text" placeholder="7334764234185">
							</div>
						</div>





				</div>



				<div class="column is-3">


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['function'];		?></p>
							<div class="field is-narrow">
								<div class="control">
									<div class="select is-fullwidth">
										<select id="id_function">
											<!--	Populate this so that it uses the array from lib_system			-->
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
							<p class="help"><?php	echo $mylang['type'];		?></p>
							<div class="field is-narrow">
								<div class="control">
									<div class="select is-fullwidth">
										<select id="id_type">
											<!--	Populate this so that it uses the array from lib_system			-->
											<?php

												foreach ($loc_types_arr as $typeid => $typedescription)
												{
													echo	'<option value="' . $typeid . '">' . $typedescription . '</option>';
												}

											?>
										</select>
									</div>
								</div>
							</div>
						</div>



						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['magic_product'];	?></p>
							<div class="control">
								<input id="id_magic_product_name" class="input is-normal" type="text">
							</div>
						</div>


						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['max_qty'];	?></p>
							<div class="control">
								<input id="id_max_qty" class="input is-normal" type="text" value="1">
							</div>
						</div>



				</div>



				<div class="column is-3">


					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help"><?php	echo $mylang['category'] . ' (A)'; ?></p>
						<div class="field is-narrow">
						  <div class="control">
							<div class="select is-fullwidth">
								<select id="id_category_a">
									<option value="0"><?php	echo $mylang['none'];		?></option>
								</select>
							</div>
						  </div>
						</div>
					</div>



					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help"><?php	echo $mylang['category'] . ' (B)'; ?></p>
						<div class="field is-narrow">
						  <div class="control">
							<div class="select is-fullwidth">
								<select id="id_category_b">
									<option value="0"><?php	echo $mylang['none'];		?></option>
								</select>
							</div>
						  </div>
						</div>
					</div>



					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help"><?php	echo $mylang['category'] . ' (C)'; ?></p>
						<div class="field is-narrow">
						  <div class="control">
							<div class="select is-fullwidth">
								<select id="id_category_c">
									<option value="0"><?php	echo $mylang['none'];		?></option>
								</select>
							</div>
						  </div>
						</div>
					</div>



					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help"><?php	echo $mylang['category'] . ' (D)'; ?></p>
						<div class="field is-narrow">
						  <div class="control">
							<div class="select is-fullwidth">
								<select id="id_category_d">
									<option value="0"><?php	echo $mylang['none'];		?></option>
								</select>
							</div>
						  </div>
						</div>
					</div>





				</div>


				<!--	The &nbsp; in the <p class="help"></p> is just a "fix" so that everything aligns otherwise it looks odd...		-->


				<div class="column is-3">




						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['status'];		?></p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_location_status">

										<option value="0"><?php	echo $mylang['active'];		?></option>
										<option value="1"><?php	echo $mylang['disabled'];	?></option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="<?php echo $box_size_str; ?>">
							<p class="help"><?php	echo $mylang['blocked'];		?></p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
								  <select id="id_blocked">
									<option value="0"><?php	echo $mylang['no'];		?></option>
									<option value="1"><?php	echo $mylang['yes'];	?></option>
								  </select>
								</div>
							  </div>
							</div>
						</div>



					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help"><?php	echo $mylang['note'];	?></p>
						<div class="control">
							<input id="id_desc" class="input is-normal" type="text" placeholder="do not use">
						</div>
					</div>


<?php


	// If the operator has the ability to add...
	if (can_user_add($_SESSION['menu_adm_warehouse_loc']))
	{
		echo	'

		<div class="field" style="'. $box_size_str .'">
			<p class="help">&nbsp;</p>
			<div class="control">
				<button class="button admin_class is-fullwidth"  onclick="add_item();">' . $mylang['add'] . '</button>
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
				<button class="button admin_class is-fullwidth"  onclick="update_location();">' . $mylang['update'] . '</button>
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
