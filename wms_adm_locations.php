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

			// When the Admin selects a new location!
			$('#id_company').change(function() {
				get_all_locations(0);
			});

			$('#curr_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlighted').removeClass('highlighted');
					$(this).addClass('highlighted');



					// Get the UOM ID from the data-id attribute
					var locID = $(this).data('id');

/*
					//	Check if the row has a numer
					if ($.isNumeric(	$(this).find('td:nth-child(1)').text()	))
					{

					}
*/

					// Get all the details from the table for the selected UOM
					get_location(locID);



			});




			$('#updateBtn').on('click', function()
			{
				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row

				if (selectedId) {
					// Perform your update logic with the selectedId
					update_location(selectedId);
				} else {
					$.alertable.error('123232', '<?php echo $mylang['select_package_unit']; ?>');
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
		function get_all_locations(row2highlight)
		{

			$.post('ajax_wms_location.php', { 

				action_code_js		:	0,
				company_uid_js		:	get_Element_Value_By_ID('id_company')

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





		// Get all companies for the selectbox!
		function get_all_companies()
		{

			$.post('ajax_companies.php', { 

				action_code_js		:	20

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					emptySelectBox('id_company');
					//addOption2SelectBox('id_company', 0, '-----');

					emptySelectBox('id_location_owner');
					//addOption2SelectBox('id_location_owner', 0, '-----');


					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_company', obje.data[i].company_pkey, obje.data[i].company_code);	
							addOption2SelectBox('id_location_owner', obje.data[i].company_pkey, obje.data[i].company_code);	
						}

					}

					get_all_locations(0);


				}
				else
				{
					$.alertable.info(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						$.alertable.error('103560', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		// Get active packging units and populate them for a selectbox!
		function get_all_packing_units()
		{

			$.post('ajax_wms_uom.php', { 

				action_code_js		:	21

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					emptySelectBox('id_package_unit');

					//	Add "Any" manually?
					addOption2SelectBox('id_package_unit', 0, '<?php	echo $mylang['all'];	?>');	


					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_package_unit', obje.data[i].pu_pkey, obje.data[i].pu_code);	
						}

					}


				}
				else
				{
					$.alertable.info(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						$.alertable.error('103560', '<?php	echo $mylang['server_error'];	?>');
					});

		}






		function get_location(locUID)
		{

			$.post('ajax_wms_location.php', {

				action_code_js		:	1,
				loc_uid_js			:	locUID,
				company_uid_js		:	get_Element_Value_By_ID('id_company')


			},

			function (output)
			{

				const obje = jQuery.parseJSON(output);

				if (obje.control === 0)
				{

					// Set element values
					set_Element_Value_By_ID('id_location_owner', obje.data.loc_owner);
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


			$.post('ajax_wms_location.php', { 

				action_code_js		:	2,
				owner_uid_js		:	get_Element_Value_By_ID('id_location_owner'),
				warehouse_js		:	get_Element_Value_By_ID('id_warehouse'),
				location_js			:	get_Element_Value_By_ID('id_location_name'),
				barcode_js			:	get_Element_Value_By_ID('id_barcode'),
				function_js			:	get_Element_Value_By_ID('id_function'),
				type_js				:	get_Element_Value_By_ID('id_type'),
				pk_unit_js			:	get_Element_Value_By_ID('id_package_unit'),
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
					get_all_locations(0);	// repopulate the table !
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
		function update_location(locUID)
		{

			if (locUID)
			{

				$.post('ajax_wms_location.php', { 

					action_code_js		:	3,
					owner_uid_js		:	get_Element_Value_By_ID('id_location_owner'),
					loc_uid_js			:	locUID,
					warehouse_js		:	get_Element_Value_By_ID('id_warehouse'),
					location_js			:	get_Element_Value_By_ID('id_location_name'),
					barcode_js			:	get_Element_Value_By_ID('id_barcode'),
					function_js			:	get_Element_Value_By_ID('id_function'),
					type_js				:	get_Element_Value_By_ID('id_type'),
					pk_unit_js			:	get_Element_Value_By_ID('id_package_unit'),
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

						get_all_locations(0);	// repopulate the table !
						set_Element_Value_By_ID('id_location_owner', 0);
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

			} else {
				// Handle the case where no row is selected
				$.alertable.error('101558', '<?php echo $mylang['nothing_selected']; ?>');
			}

		}



	function get_all_warehouses()
	{

		$.post('ajax_wms_warehouses.php', {

			action_code_js	:	20

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
				action_code_js		:	1,
				action_format_js	:	1,
				action_disabled_js	:	0,
				cat_uid_js			:	category_a_val,
				company_uid_js		:	get_Element_Value_By_ID('id_company')
			};

			$.post('ajax_wms_categories.php', postData)
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
				cat_uid_js: 			category_b_val,
				company_uid_js		:	get_Element_Value_By_ID('id_company')
			};

			$.post('ajax_wms_categories.php', postData)
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
				cat_uid_js: 			category_c_val,
				company_uid_js		:	get_Element_Value_By_ID('id_company')
			};

			$.post('ajax_wms_categories.php', postData)
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
<body onLoad='get_all_warehouses(); get_all_companies(); get_all_packing_units();'>



<?php


	// A little gap at the top to make it look better a notch.
	echo '<div class="blank_space_12px"></div>';

	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	//	"Menu" here
	$top_menu	=	'';

	$top_menu	.=	'<div class="columns">';
	$top_menu	.=	'<div class="column is-4">';
	$menu_link	=	"'index.php'";
	$top_menu	.=	'<button class="button admin_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
	$top_menu	.=	'</div>';

	$top_menu	.=	'<div class="column is-4">';


	$top_menu	.=	'<div class="columns is-mobile">
	<div class="column is-fullwidth">
		<div class="field is-narrow">
			<div class="control">
				<div class="select is-fullwidth">
					<select id="id_company">
					</select>
				</div>
			</div>
		</div>
	</div>
</div>';




	$top_menu	.=	'</div>';

	$top_menu	.=	'</div>';

	echo $top_menu;



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
							<p class="help"><?php	echo $mylang['company'];		?></p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_location_owner">
									</select>
								</div>
							  </div>
							</div>
						</div>


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
							<p class="help"><?php	echo $mylang['package_unit'];	?></p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_package_unit" name="id_package_unit">
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



<?php


	//	Here grab the live categories into one array!
	$sql	=	'


			SELECT

			cat_a_pkey,
			cat_a_name

			FROM geb_category_a

			WHERE
			
			cat_a_owner = :scat_a_owner

	';

	$category_arr	=	array();	//	Store all Category A entries here!

	$category_a_html	=	'';

	if ($stmt = $db->prepare($sql))
	{

		$stmt->bindValue(':scat_a_owner',	$user_company_uid,		PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{


			$category_a_html .= '<option value="' . $row['cat_a_pkey'] . '"';
/*
			if ($key == $selectedValue)
			{ 
				$html .= ' selected'; 
			}
*/
			$category_a_html .= '>' . $row['cat_a_name'] . '</option>';

		}

	}


?>

				<div class="column is-3">


					<div class="field" style="<?php echo $box_size_str; ?>">
						<p class="help"><?php	echo $mylang['category'] . ' (A)'; ?></p>
						<div class="field is-narrow">
						  <div class="control">
							<div class="select is-fullwidth">
								<select id="id_category_a">
									<option value="0"><?php	echo $mylang['none'];		?></option>
									<?php	echo $category_a_html	?>
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
				<button id="updateBtn" class="button admin_class is-fullwidth">' . $mylang['update'] . '</button>
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
