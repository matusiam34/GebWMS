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
	if (is_it_enabled($_SESSION['menu_adm_container_type']))
	{




?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['container_type'];	?></title>
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
			$('#container_table').on('click', 'tr', function()
			{
				// Check if the clicked row is inside the table header or if it doesn't have a data-id attribute
				if ($(this).closest('thead').length || !$(this).data('id')) {
					return; // Exit the function if the header is clicked or there's no data-id attribute
				}

				// When the user clicks on anything, it gets selected
				$('.highlighted').removeClass('highlighted');
				$(this).addClass('highlighted');

				// Get the UOM ID from the data-id attribute
				var ctypeID = $(this).data('id');

				// Get all the details from the table for the selected UOM
				get_one_ctype_data(ctypeID);
			});






			$('#updateBtn').on('click', function()
			{
				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row

				if (selectedId) {
					// Perform your update logic with the selectedId
					update_ctype(selectedId);
				} else {
					$.alertable.error('101558', '<?php echo $mylang['nothing_selected']; ?>');
				}
			});







		});



		function get_all_ctype(row2highlight)
		{

			$.post('ajax_wms_ctype.php', { 

				action_code_js				:	10

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#container_table > tbody').html(obje.html);

					//	only apply when row ID is provided and for this system it will always have to be > 0 (because db_uid)
					if (row2highlight > 0)
					{
						highlightRowByDataId(row2highlight, 'container_table');
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


		function get_one_ctype_data(ctypeID)
		{

			if (ctypeID)
			{

				$.post('ajax_wms_ctype.php', { 

					action_code_js		:	12,
					ctype_uid_js		:	ctypeID

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{

						set_Element_Value_By_ID('id_ctype_name',				obje.data.ctype_code);
						set_Element_Value_By_ID('id_ctype_prefix',				obje.data.ctype_prefix);
						set_Element_Value_By_ID('id_ctype_description',			obje.data.ctype_description);
						set_Element_Value_By_ID('id_ctype_status',				obje.data.ctype_disabled);

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



		//	Add one container type
		function add_ctype()
		{

			$.post('ajax_wms_ctype.php', { 

				action_code_js				:	15,
				ctype_name_js				:	get_Element_Value_By_ID('id_ctype_name'),
				ctype_prefix_js				:	get_Element_Value_By_ID('id_ctype_prefix'),
				ctype_description_js		:	get_Element_Value_By_ID('id_ctype_description'),
				ctype_status_js				:	get_Element_Value_By_ID('id_ctype_status')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					//	Refresh the list
					get_all_ctype(0);	// repopulate the table

					set_Element_Value_By_ID('id_ctype_name',			'');
					set_Element_Value_By_ID('id_ctype_prefix',			'');
					set_Element_Value_By_ID('id_ctype_description',		'');
					set_Element_Value_By_ID('id_ctype_status',			0);

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






		//	Update Container Type details
		function update_ctype(ctypeID)
		{

			if (ctypeID)
			{

				let row_status	=	get_Element_Value_By_ID('id_ctype_status');
				
				$.post('ajax_wms_ctype.php', { 

					action_code_js				:	17,
					ctype_uid_js				:	ctypeID,
					ctype_name_js				:	get_Element_Value_By_ID('id_ctype_name'),
					ctype_prefix_js				:	get_Element_Value_By_ID('id_ctype_prefix'),
					ctype_description_js		:	get_Element_Value_By_ID('id_ctype_description'),
					ctype_status_js				:	get_Element_Value_By_ID('id_ctype_status')

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{
						//	Update only the relevant part of the table without a full AJAX
						updateRow(ctypeID, 'container_table', [get_Element_Value_By_ID('id_ctype_name')]);
						//	If user disables the entry make sure to apply the RED
						if (row_status == 0)
						{
							enableRowByDataId(ctypeID, 'container_table');
						}

						if (row_status == 1)
						{
							disableRowByDataId(ctypeID, 'container_table');
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
<body onLoad='get_all_ctype(0);'>


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
							<table class="table is-fullwidth is-hoverable is-scrollable" id="container_table">
								<thead>
									<tr>
										<th>' . $mylang['container_type'] . '</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>';



	// Details
	$layout_details_html	.=	'


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['container_type'] . ':</p>
							<div class="control">
								<input id="id_ctype_name" class="input is-normal" type="text" placeholder="PALLET">
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['prefix'] . ':</p>
							<div class="control">
								<input id="id_ctype_prefix" class="input is-normal" type="text" placeholder="PAL">
							</div>
						</div>




						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['description'] . ':</p>
							<div class="control">
								<input id="id_ctype_description" class="input is-normal" type="text">
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_ctype_status">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						';



		//	Update button section?!
		$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button id="updateBtn" class="button is-normal is-bold admin_class is-fullwidth" >' . $mylang['save'] . '</button>
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_ctype();">' . $mylang['add'] . '</button>
							</div>
						</div>

						';



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
