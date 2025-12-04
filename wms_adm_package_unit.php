<?php


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
	if (is_it_enabled($_SESSION['menu_adm_package_unit']))
	{




?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['package_unit'];	?></title>
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


			// Triggers a function every time a row in the table is clicked
			$('#package_table').on('click', 'tr', function()
			{
				// Check if the clicked row is inside the table header or if it doesn't have a data-id attribute
				if ($(this).closest('thead').length || !$(this).data('id')) {
					return; // Exit the function if the header is clicked or there's no data-id attribute
				}

				// When the user clicks on anything, it gets selected
				$('.highlighted').removeClass('highlighted');
				$(this).addClass('highlighted');

				// Get the UOM ID from the data-id attribute
				var puID = $(this).data('id');

				// Get all the details from the table for the selected UOM
				get_one_package_unit_data(puID);
			});






			$('#updateBtn').on('click', function()
			{
				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row

				if (selectedId) {
					// Perform your update logic with the selectedId
					update_pu(selectedId);
				} else {
					$.alertable.error('123232', '<?php echo $mylang['select_package_unit']; ?>');
				}
			});







		});



		function get_all_package_units(row2highlight)
		{

			$.post('ajax_wms_uom.php', { 

				action_code_js				:	20

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#package_table > tbody').html(obje.html);

					//	only apply when row ID is provided and for this system it will always have to be > 0 (because db_uid)
					if (row2highlight > 0)
					{
						highlightRowByDataId(row2highlight, 'package_table');
					}
				
				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('101555', '<?php	echo $mylang['server_error'];	?>');
					});

		}


		function get_one_package_unit_data(puID)
		{

			if (puID)
			{

				$.post('ajax_wms_uom.php', { 

					action_code_js		:	22,
					pu_uid_js			:	puID

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{

						set_Element_Value_By_ID('id_pu_name',				obje.data.pu_code);
						set_Element_Value_By_ID('id_pu_description',		obje.data.pu_description);
						set_Element_Value_By_ID('id_pu_uom',				obje.data.pu_uom_pkey);
						set_Element_Value_By_ID('id_pu_qty',				obje.data.pu_qty);
						set_Element_Value_By_ID('id_pu_status',				obje.data.pu_disabled);

					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}

				}).fail(function() {
							// something went wrong
							$.alertable.error('101556', '<?php	echo $mylang['server_error'];	?>');
						});

			} else {
				// Handle the case where no row is selected
				$.alertable.error('123232', '<?php echo $mylang['nothing_selected']; ?>');
			}


		}



		// Add one Package UNIT
		function add_pu()
		{
			const data =
			{
				action_code_js: 25,
				pu_name_js: get_Element_Value_By_ID('id_pu_name'),
				pu_description_js: get_Element_Value_By_ID('id_pu_description'),
				pu_uom_js: get_Element_Value_By_ID('id_pu_uom'),
				pu_qty_js: get_Element_Value_By_ID('id_pu_qty'),
				pu_status_js: get_Element_Value_By_ID('id_pu_status')
			};

			$.post('ajax_wms_uom.php', data, function (output) {
				let response;

				try {
					response = JSON.parse(output);
				} catch (e) {
					console.error("Invalid JSON response:", output);
					$.alertable.error('JSON_PARSE_ERROR', 'Server response was not valid JSON.');
					return;
				}

				if (response.control === 0)
				{
					get_all_package_units(0); // Refresh the table

					set_Element_Value_By_ID('id_pu_name', '');
					set_Element_Value_By_ID('id_pu_description', '');
					set_Element_Value_By_ID('id_pu_uom', 10);
					set_Element_Value_By_ID('id_pu_qty', 1);
					set_Element_Value_By_ID('id_pu_status', 0);

					$.alertable.info(response.control, response.msg);
				} else {
					$.alertable.error(response.control, response.msg);
				}
			})
			.fail(function () {
				$.alertable.error('101557', 'Server error. Please try again.');
			});
		}

/*
		//	Add one Package UNIT
		function add_pu()
		{

			$.post('ajax_wms_uom.php', { 

				action_code_js				:	25,
				pu_name_js					:	get_Element_Value_By_ID('id_pu_name'),
				pu_description_js			:	get_Element_Value_By_ID('id_pu_description'),
				pu_uom_js					:	get_Element_Value_By_ID('id_pu_uom'),
				pu_qty_js					:	get_Element_Value_By_ID('id_pu_qty'),
				pu_status_js				:	get_Element_Value_By_ID('id_pu_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					//	Refresh the list
					get_all_package_units(0);	// repopulate the table

					set_Element_Value_By_ID('id_pu_name',			'');
					set_Element_Value_By_ID('id_pu_description',	'');
					set_Element_Value_By_ID('id_pu_uom',			10);
					set_Element_Value_By_ID('id_pu_qty',			1);
					set_Element_Value_By_ID('id_pu_status',			0);

					$.alertable.info(obje.control, obje.msg);


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
*/





		//	Update Package UNIT details
		function update_pu(puID)
		{

			if (puID)
			{

				let row_status	=	get_Element_Value_By_ID('id_pu_status');
				
				$.post('ajax_wms_uom.php', { 

					action_code_js				:	27,
					pu_uid_js					:	puID,
					pu_name_js					:	get_Element_Value_By_ID('id_pu_name'),
					pu_description_js			:	get_Element_Value_By_ID('id_pu_description'),
					pu_uom_js					:	get_Element_Value_By_ID('id_pu_uom'),
					pu_qty_js					:	get_Element_Value_By_ID('id_pu_qty'),
					pu_status_js				:	row_status

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{
						//	Update only the relevant part of the table without a full AJAX
						updateRow(puID, 'package_table', [get_Element_Value_By_ID('id_pu_name')]);
						//	If user disables the entry make sure to apply the RED
						if (row_status == 0)
						{
							enableRowByDataId(puID, 'package_table');
						}

						if (row_status == 1)
						{
							disableRowByDataId(puID, 'package_table');
						}

						$.alertable.info(obje.control, obje.msg);

					}
					else
					{
						$.alertable.error(obje.control, obje.msg).always(function() {	});
					}

				}).fail(function() {
							// something went wrong
							$.alertable.error('101558', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
						});

			} else {
				// Handle the case where no row is selected
				$.alertable.error('101558', '<?php echo $mylang['nothing_selected']; ?>');
			}


		}





		// Get UOMs and populate them for a selectbox!
		function get_all_uoms()
		{

			$.post('ajax_wms_uom.php', { 

				action_code_js		:	11

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					emptySelectBox('id_pu_uom');


					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_pu_uom', obje.data[i].uom_pkey, obje.data[i].uom_code);	
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




	</script>





<style>


	.tableAttr { height: 224px; overflow-y: scroll;}

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
<body onLoad='get_all_package_units(0); get_all_uoms();'>


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
	$top_menu	.=	'</div>';

	echo $top_menu;




	$layout_details_html	=	'';

	$layout_details_html	.=	'<div class="columns">';



	// Table Header
	$layout_details_html	.=	'

					<div class="column is-4">

						<div class="tableAttr it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="package_table">
								<thead>
									<tr>
										<th>' . $mylang['package_unit'] . '</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>';



	// Details
	$layout_details_html	.=	'


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['package_unit'] . ':</p>
							<div class="control">
								<input id="id_pu_name" class="input is-normal" type="text" placeholder="BOX20">
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['description'] . ':</p>
							<div class="control">
								<input id="id_pu_description" class="input is-normal" type="text" placeholder="">
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['uom'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_pu_uom">
									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['qty'] . ':</p>
							<div class="control">
								<input id="id_pu_qty" class="input is-normal" type="text" placeholder="1.0">
							</div>
						</div>





						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_pu_status">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>






						';



	//	Show Add or Update related buttons only if the user permissions are set!


	// If the operator has the ability to add...
	if (can_user_add($_SESSION['menu_adm_package_unit']))
	{

		$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_pu();">' . $mylang['add'] . '</button>
							</div>
						</div>


						';


	}


	// If the operator has the ability to update...
	if (can_user_update($_SESSION['menu_adm_package_unit']))
	{

		$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button id="updateBtn" class="button is-normal is-bold admin_class is-fullwidth" >' . $mylang['save'] . '</button>
							</div>
						</div>

						';

	}





	$layout_details_html	.=	'

				</div>';


echo	$layout_details_html;



//	Place it in a better space maybe? Not urgent.
//echo	'<input id="id_hidden" class="input is-normal" type="hidden">';


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
