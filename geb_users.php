<?php


//
//
//	How can I make it easy for the Admin to manage access?
//
//	I got this so far:
//
//	X:	Always a 1 at the beginning to make it work.
//	E:	Enabled
//	A:	Add
//	U:	Update
//	D:	Delete
//
//
//	1	1	1	1	1	00000000000
//
//	X	E	A	U	D
//
//
//	Disabled	:	32768	1000000000000000
//	E			:	49152	1100000000000000
//	EU			:	53248	1101000000000000
//	ED			:	51200	1100100000000000
//	EA			:	57344	1110000000000000
//	EUD			:	55296	1101100000000000
//	EAU			:	61440	1111000000000000
//	EAD			:	59392	1110100000000000
//	EAUD		:	63488	1111100000000000
//
//
//
//
//
//
/*

	All select options 

	<option value="32768">X</option>
	<option value="49152">E</option>
	<option value="57344">EA</option>
	<option value="61440">EAU</option>
	<option value="59392">EAD</option>
	<option value="63488">EAUD</option>
	<option value="53248">EU</option>
	<option value="51200">ED</option>
	<option value="55296">EUD</option>


*/



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
	if (is_it_enabled($_SESSION['menu_adm_users']))
	{




?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['users'];	?></title>
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


	<style>


		.password-container
		{
			display: flex;
			align-items: center;
			margin-bottom: 1rem;
		}

		.password-container input
		{
			flex: 1;
			padding: 0.5rem;
			font-size: 1rem;
		}


		.eye-icon
		{
			width: 32px; /* Adjust based on your image size */
			height: 32px;
		}



		/*		For user details and ACL tabs... more in the future maybe as well... Keep the UI Clean Again!	*/
        .tab-content
		{
            display: none;
        }

        .tab-content.is-active
		{
            display: block;
        }


	</style>


	<script language="javascript" type="text/javascript">



		$(document).ready(function() 
		{



			$(".tabs ul li").click(function()
			{
				// Remove active class from all tabs and content
				$(".tabs ul li").removeClass("is-active");
				$(".tab-content").removeClass("is-active");

				// Add active class to the clicked tab
				$(this).addClass("is-active");

				// Show the corresponding tab content
				let tabId = $(this).attr("data-tab");
				$("#" + tabId).addClass("is-active");
			});



			// Triggers a function every time the row in the table departmentList is clicked !
			$('#curr_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlighted').removeClass('highlighted');
					$(this).addClass('highlighted');

					// Get the UOM ID from the data-id attribute
					var userID = $(this).data('id');

					// Get all the details from the table for the selected UOM
					get_one_user_data(userID);

			});



			$('#updateDetailsBtn').on('click', function()
			{
				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row

				if (selectedId) {
					// Perform your update logic with the selectedId
					update_user_details(selectedId);
				} else {
					$.alertable.error('123232', '<?php echo $mylang['select_user']; ?>');
				}
			});




			$('#updateAclBtn').on('click', function()
			{
				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row

				if (selectedId) {
					// Perform your update logic with the selectedId
					update_user_acl(selectedId);
				} else {
					$.alertable.error('123232', '<?php echo $mylang['select_user']; ?>');
				}
			});



			//	To do with the setting password via the admin!
			$(document).on('click', '#submitPassword', function ()
			{

				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row


				if (selectedId)
				{

					$.post('ajax_users.php', { 

						action_code_js			:	5,
						new_password_js			:	$('#newPassword').val(),
						user_uid_js				:	selectedId

					},

					function(output)
					{

						// Parse the json  !!
						var obje = jQuery.parseJSON(output);

						// Control = 0 => Green light to GO !!!
						if (obje.control == 0)
						{
							$.alertable.info(obje.control, obje.msg);
						}
						else
						{
							$.alertable.error(obje.control, obje.msg);
						}

					}).fail(function() {
								// something went wrong -> could not execute php script most likely !
								$.alertable.error('103557', '<?php	echo $mylang['server_error'];	?>');
							});

				} else {
					// Handle the case where no row is selected
					$.alertable.error('123232', '<?php echo $mylang['nothing_selected']; ?>');
				}



			});

			$('#setPasswordBtn').on('click', function()
			{

				let highlightedRow = $('tr.highlighted');		// Get the highlighted row
				let selectedId = highlightedRow.data('id');		// Get data-id from the highlighted row

				//	<button id="updateAclBtn" class="button admin_class is-fullwidth">' . $mylang['update_acl'] . '</button>


				if (selectedId)
				{

					const setPassword_html	=	'<br><div class="field" style="<?php echo $box_size_str; ?>"><div class="control password-container"><input id="newPassword" type="password" class="input" placeholder="<?php echo $mylang['enter_new_password']; ?>"><button type="button" class="toggle-visibility" onclick="togglePasswordVisibility(this)"><img src="images/eye.png" class="eye-icon"></button></div></div><div class="field" style="<?php echo $box_size_str; ?>"><div class="control"><button id="submitPassword" class="button admin_class is-fullwidth"><?php echo $mylang['set_password']; ?></button></div></div><div class="field" style="<?php echo $box_size_str; ?>"><div class="control"><button id="cancelSetPassword" class="button admin_class is-fullwidth"><?php echo $mylang['cancel']; ?></button></div></div>';

					$.alertable.noButton('', setPassword_html);

				} else {
					$.alertable.error('123232', '<?php echo $mylang['select_user']; ?>');
				}


			});





		});




		function togglePasswordVisibility(button)
		{
			const passwordField = document.getElementById('newPassword');
			const isPassword = passwordField.type === "password";
			passwordField.type = isPassword ? "text" : "password";

			// Swap the image
			const eyeIcon = button.querySelector('.eye-icon');
			eyeIcon.src = isPassword ? 'images/eye_not.png' : 'images/eye.png';
		}



		// Grab all users
		function get_all_users()
		{

			$.post('ajax_users.php', { 

				action_code_js	:	0
			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#curr_table > tbody').empty();
					$('#curr_table > tbody').append(obje.html);
				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						$.alertable.error('103555', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		// Grab one user data
		function get_one_user_data(userID)
		{


			if (userID)
			{

				$.post('ajax_users.php', { 

					action_code_js	:	1,
					user_uid_js		:	userID

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{

						//	User details
						set_Element_Value_By_ID('id_user_name',				obje.data.user_name);
						set_Element_Value_By_ID('id_user_firstname',		obje.data.user_firstname);
						set_Element_Value_By_ID('id_user_lastname',			obje.data.user_surname);

						set_Element_Value_By_ID('id_user_desc',				obje.data.user_description);
						set_Element_Value_By_ID('id_user_email',			obje.data.user_email);
						set_Element_Value_By_ID('id_user_company',			obje.data.user_company);
						set_Element_Value_By_ID('id_user_warehouse',		obje.data.user_warehouse);
						set_Element_Value_By_ID('id_user_is_admin',			obje.data.user_is_admin);
						set_Element_Value_By_ID('id_user_active',			obje.data.user_active);


						//	ACL
						set_Element_Value_By_ID('id_product_search',		obje.data.menu_prod_search);
						set_Element_Value_By_ID('id_location_search',		obje.data.menu_location_search);


						set_Element_Value_By_ID('id_goodsin',				obje.data.menu_goodsin);
						set_Element_Value_By_ID('id_mpa',					obje.data.menu_mpa);
						set_Element_Value_By_ID('id_mpp',					obje.data.menu_mpp);
						set_Element_Value_By_ID('id_recent_activity',		obje.data.menu_recent_activity);


						//set_Element_Value_By_ID('id_mgr_product_line',		obje.data.menu_mgr_product_line);
						set_Element_Value_By_ID('id_mgr_product_sku',		obje.data.menu_mgr_product_sku);


						set_Element_Value_By_ID('id_my_account',			obje.data.menu_my_account);
						set_Element_Value_By_ID('id_adm_users',				obje.data.menu_adm_users);
						set_Element_Value_By_ID('id_adm_warehouses',		obje.data.menu_adm_warehouse);
						set_Element_Value_By_ID('id_adm_wh_locations',		obje.data.menu_adm_warehouse_loc);
						set_Element_Value_By_ID('id_adm_categories',		obje.data.menu_adm_category);

						set_Element_Value_By_ID('id_adm_container_type',	obje.data.menu_adm_container_type);
						set_Element_Value_By_ID('id_adm_package_unit',		obje.data.menu_adm_package_unit);
						set_Element_Value_By_ID('id_adm_unit_of_measure',	obje.data.menu_adm_uom);
						set_Element_Value_By_ID('id_adm_companies',			obje.data.menu_adm_company);



					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}

				}).fail(function() {
							// something went wrong -> could not execute php script most likely !
							$.alertable.error('103556', '<?php	echo $mylang['server_error'];	?>');
						});


			} else {
				// Handle the case where no row is selected
				$.alertable.error('123232', '<?php echo $mylang['nothing_selected']; ?>');
			}

		}





		// UPDATE user details only!
		function update_user_details(userID)
		{

			if (userID)
			{

				let user_status	=	get_Element_Value_By_ID('id_user_active');

				$.post('ajax_users.php', { 

					action_code_js			:	3,
					user_uid_js				:	userID,
					user_username_js		:	get_Element_Value_By_ID('id_user_name'),
					user_firstname_js		:	get_Element_Value_By_ID('id_user_firstname'),
					user_lastname_js		:	get_Element_Value_By_ID('id_user_lastname'),
					user_company_js			:	get_Element_Value_By_ID('id_user_company'),
					user_desc_js			:	get_Element_Value_By_ID('id_user_desc'),
					user_email_js			:	get_Element_Value_By_ID('id_user_email'),
					user_warehouse_js		:	get_Element_Value_By_ID('id_user_warehouse'),
					user_is_admin_js		:	get_Element_Value_By_ID('id_user_is_admin'),
					user_active_js			:	user_status

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{

						//	Update only the relevant part of the table without a full AJAX
						updateRow(userID, 'curr_table', [get_Element_Value_By_ID('id_user_name')]);
						//	If user disables the entry make sure to apply the RED
						if (user_status == 0)
						{
							enableRowByDataId(userID, 'curr_table');
						}

						if (user_status == 1)
						{
							disableRowByDataId(userID, 'curr_table');
						}

						$.alertable.info(obje.control, obje.msg);
					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}

				}).fail(function() {
							// something went wrong -> could not execute php script most likely !
							$.alertable.error('103557', '<?php	echo $mylang['server_error'];	?>');
						});



			} else {
				// Handle the case where no row is selected
				$.alertable.error('123232', '<?php echo $mylang['nothing_selected']; ?>');
			}

		}




		// UPDATE access control only!
		function update_user_acl(userID)
		{

			if (userID)
			{

				$.post('ajax_users.php', { 

					action_code_js			:	4,

					product_search_js		:	get_Element_Value_By_ID('id_product_search'),
					location_search_js		:	get_Element_Value_By_ID('id_location_search'),
					goodsin_js				:	get_Element_Value_By_ID('id_goodsin'),
					mpa_js					:	get_Element_Value_By_ID('id_mpa'),
					mpp_js					:	get_Element_Value_By_ID('id_mpp'),
					recent_activity_js		:	get_Element_Value_By_ID('id_recent_activity'),
					//mgr_product_line_js		:	get_Element_Value_By_ID('id_mgr_product_line'),
					mgr_product_sku_js		:	get_Element_Value_By_ID('id_mgr_product_sku'),
					my_account_js			:	get_Element_Value_By_ID('id_my_account'),
					adm_users_js			:	get_Element_Value_By_ID('id_adm_users'),
					adm_warehouses_js		:	get_Element_Value_By_ID('id_adm_warehouses'),
					adm_wh_locations_js		:	get_Element_Value_By_ID('id_adm_wh_locations'),
					adm_categories_js		:	get_Element_Value_By_ID('id_adm_categories'),
					adm_companies_js		:	get_Element_Value_By_ID('id_adm_companies'),
					adm_container_type_js	:	get_Element_Value_By_ID('id_adm_container_type'),
					adm_package_unit_js		:	get_Element_Value_By_ID('id_adm_package_unit'),
					adm_uom_js				:	get_Element_Value_By_ID('id_adm_unit_of_measure'),

					user_uid_js				:	userID

				},

				function(output)
				{

					// Parse the json  !!
					var obje = jQuery.parseJSON(output);

					// Control = 0 => Green light to GO !!!
					if (obje.control == 0)
					{
						//	Some info to let the user know that changes have been applied?
						$.alertable.info(obje.control, obje.msg);
					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}

				}).fail(function() {
							// something went wrong -> could not execute php script most likely !
							$.alertable.error('103558', '<?php	echo $mylang['server_error'];	?>');
						});


			} else {
				// Handle the case where no row is selected
				$.alertable.error('123232', '<?php echo $mylang['nothing_selected']; ?>');
			}

		}




		//	ADD a new user to the system!
		function add_new_user()
		{

			$.post('ajax_users.php', { 

				action_code_js			:	2,

				user_username_js		:	get_Element_Value_By_ID('id_user_name'),
				user_firstname_js		:	get_Element_Value_By_ID('id_user_firstname'),
				user_lastname_js		:	get_Element_Value_By_ID('id_user_lastname'),
				user_company_js			:	get_Element_Value_By_ID('id_user_company'),
				user_desc_js			:	get_Element_Value_By_ID('id_user_desc'),
				user_email_js			:	get_Element_Value_By_ID('id_user_email'),
				user_warehouse_js		:	get_Element_Value_By_ID('id_user_warehouse'),
				user_is_admin_js		:	get_Element_Value_By_ID('id_user_is_admin'),
				user_active_js			:	get_Element_Value_By_ID('id_user_active'),
				product_search_js		:	get_Element_Value_By_ID('id_product_search'),
				location_search_js		:	get_Element_Value_By_ID('id_location_search'),
				goodsin_js				:	get_Element_Value_By_ID('id_goodsin'),
				mpa_js					:	get_Element_Value_By_ID('id_mpa'),
				mpp_js					:	get_Element_Value_By_ID('id_mpp'),
				recent_activity_js		:	get_Element_Value_By_ID('id_recent_activity'),
				//mgr_product_line_js		:	get_Element_Value_By_ID('id_mgr_product_line'),
				mgr_product_sku_js		:	get_Element_Value_By_ID('id_mgr_product_sku'),
				my_account_js			:	get_Element_Value_By_ID('id_my_account'),
				adm_users_js			:	get_Element_Value_By_ID('id_adm_users'),
				adm_warehouses_js		:	get_Element_Value_By_ID('id_adm_warehouses'),
				adm_wh_locations_js		:	get_Element_Value_By_ID('id_adm_wh_locations'),
				adm_categories_js		:	get_Element_Value_By_ID('id_adm_categories'),
				adm_companies_js		:	get_Element_Value_By_ID('id_adm_companies'),
				adm_container_type_js	:	get_Element_Value_By_ID('id_adm_container_type'),
				adm_package_unit_js		:	get_Element_Value_By_ID('id_adm_package_unit'),
				adm_uom_js				:	get_Element_Value_By_ID('id_adm_unit_of_measure')
 


			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					//	Some info to let the user know that changes have been applied?
					get_all_users();	// repopulate the table
					$.alertable.info(obje.control, obje.msg);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						$.alertable.error('103559', '<?php	echo $mylang['server_error'];	?>');
					});

		}






		// Get all warehouses for the selectbox
		function get_all_warehouses()
		{

			$.post('ajax_warehouses.php', { 

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

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_user_warehouse', obje.data[i].wh_pkey, obje.data[i].wh_code);	
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



		// Get all companies for the selectbox
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

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_user_company', obje.data[i].company_pkey, obje.data[i].company_code);	
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
<body onLoad='get_all_users(); get_all_warehouses(); get_all_companies();'>


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




	//	Everything about the user table.
	$user_details_html		=	'';

	//	All stuff related to the WMS
	$gebwms_html			=	'';

	//	All stuff related to the overall system
	$system_html			=	'';


	//	Buttons at the end of everything!
	$low_buttons_html		=	'';




	$user_details_html	.=	'


		<div class="columns">

				<div class="column is-6">

					<div class="tableAttr it-has-border">
						<table class="table is-fullwidth is-hoverable is-scrollable" id="curr_table">
							<thead>
								<tr>
									<th>' . $mylang['user'] . '</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>

				</div>

		</div>



        <!-- Tabs Navigation -->
        <div class="tabs is-boxed">
            <ul>
                <li class="is-active" data-tab="user-info"><a>' . $mylang['user_info'] . '</a></li>
                <li data-tab="gebwms"><a>GebWMS</a></li>
                <li data-tab="system"><a>' . $mylang['system'] . '</a></li>
            </ul>
        </div>

        <!-- Tabs Content -->
        <div class="tab-content is-active" id="user-info">

			<div class="columns">


					<div class="column is-2">

						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['username'] . ':</p>
							<div class="control">
								<input id="id_user_name" class="input is-normal" type="text" placeholder="toms">
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['first_name'] . ':</p>
							<div class="control">
								<input id="id_user_firstname" class="input is-normal" type="text" placeholder="Tom">
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['last_name'] . ':</p>
							<div class="control">
								<input id="id_user_lastname" class="input is-normal" type="text" placeholder="Smith">
							</div>
						</div>


					</div>


					<div class="column is-4">



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['company'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_user_company">
									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['description'] . ':</p>
							<div class="control">
								<input id="id_user_desc" class="input is-normal" type="text" placeholder="Operations Manager">
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['email'] . ':</p>
							<div class="control">
								<input id="id_user_email" class="input is-normal" type="text" placeholder="tom.smith@jacknhide.co.uk">
							</div>
						</div>

					</div>



					<div class="column is-2">


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['warehouse'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_user_warehouse">
									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['admin'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_user_is_admin">

										<option value="0">' . $mylang['no'] . '</option>
										<option value="1">' . $mylang['yes'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_user_active">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>
										<option value="2">' . $mylang['suspended'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>

					</div>


					<div class="column is-2">
					</div>


					<div class="column is-2">';




		if (can_user_update($_SESSION['menu_adm_users']))
		{

			//	Update button section!
			$user_details_html	.=	'


				<div class="field" style="'. $box_size_str .'">
					<p class="help">&nbsp;</p>
					<div class="control">
						<button id="updateDetailsBtn" class="button admin_class is-fullwidth">' . $mylang['save_details'] . '</button>
					</div>
				</div>';

		}




$user_details_html	.=	'
					</div>



			</div>


			<div class="columns">
			</div>


        </div>';





$gebwms_html	=	'


        <div class="tab-content" id="gebwms">



			<div class="columns">

				<div class="column is-2">


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['product_search'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_general . '" id="id_product_search">

										<option value="32768">X</option>
										<option value="49152">E</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['location_search'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select  style="' . $color_general . '" id="id_location_search">

										<option value="32768">X</option>
										<option value="49152">E</option>

									</select>
								</div>
							  </div>
							</div>
						</div>


				</div>









				<div class="column is-2">



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['goodsin'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_general . '" id="id_goodsin">

										<option value="32768">X</option>
										<option value="49152">E</option>

									</select>
								</div>
							  </div>
							</div>
						</div>




						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['mpa'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_general . '" id="id_mpa">

										<option value="32768">X</option>
										<option value="49152">E</option>

									</select>
								</div>
							  </div>
							</div>
						</div>





						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['mpp'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_general . '" id="id_mpp">

										<option value="32768">X</option>
										<option value="49152">E</option>

									</select>
								</div>
							  </div>
							</div>
						</div>




						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['recent_activity'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_general . '" id="id_recent_activity">

										<option value="32768">X</option>
										<option value="49152">E</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



				</div>





				<div class="column is-2">


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['products'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_manager . '" id="id_mgr_product_sku">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>


				</div>









				<div class="column is-2">




						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['warehouse_locations'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_wh_locations">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>




						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['categories'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_categories">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>




				</div>



					<div class="column is-2">




						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['container_type'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_container_type">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['package_unit'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_package_unit">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['uom'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_unit_of_measure">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>




					</div>


					<div class="column is-2">';



	// If the operator has the ability to update...
	if (can_user_update($_SESSION['menu_adm_users']))
	{

		//	Update button section?!
		//$user_details_html	.=	'<div class="column is-2">';

		$gebwms_html	.=	'

			<div class="field" style="'. $box_size_str .'">
				<p class="help">&nbsp;</p>
				<div class="control">
					<button id="updateAclBtn" class="button admin_class is-fullwidth">' . $mylang['save'] . '</button>
				</div>
			</div>';

		//$user_details_html	.=	'</div>';

	}


$gebwms_html	.=	'


					</div>




			</div>



        </div>';











$system_html	=	'


        <div class="tab-content" id="system">



			<div class="columns">




				<div class="column is-2">



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['my_account'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_general . '" id="id_my_account">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['companies'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_companies">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['warehouses'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_warehouses">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['users'] . ':</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-yellow is-fullwidth">
									<select style="' . $color_admin . '" id="id_adm_users">

										<option value="32768">X</option>
										<option value="49152">E</option>
										<option value="57344">EA</option>
										<option value="61440">EAU</option>
										<option value="53248">EU</option>

									</select>
								</div>
							  </div>
							</div>
						</div>



				</div>



					<div class="column is-2">
					</div>

					<div class="column is-2">
					</div>

					<div class="column is-2">
					</div>

					<div class="column is-2">
					</div>


					<div class="column is-2">';



	// If the operator has the ability to update...
	if (can_user_update($_SESSION['menu_adm_users']))
	{

		//	Update button section?!
		//$user_details_html	.=	'<div class="column is-2">';

		$system_html	.=	'

			<div class="field" style="'. $box_size_str .'">
				<p class="help">&nbsp;</p>
				<div class="control">
					<button id="updateAclBtn" class="button admin_class is-fullwidth">' . $mylang['save'] . '</button>
				</div>
			</div>';

		//$user_details_html	.=	'</div>';

	}


$system_html	.=	'


					</div>




			</div>



        </div>';








$low_buttons_html	=	'<div class="columns">';


		if (can_user_add($_SESSION['menu_adm_users']))
		{

			$low_buttons_html	.=	'


			<div class="column is-2">

				<div class="field" style="'. $box_size_str .'">
					<p class="help">&nbsp;</p>
					<div class="control">
						<button class="button admin_class is-fullwidth" onclick="add_new_user();">' . $mylang['add_user'] . '</button>
					</div>
				</div>

			</div>


			';

		}



		//	Allow password change when Admin only? Or do I need the Admin to also have Update powers for this one?
		//	FIX
		if (check_for_admin($_SESSION['user_is_admin']))
		{

			$low_buttons_html	.=	'

			<div class="column is-2">

				<div class="field" style="'. $box_size_str .'">
					<p class="help">&nbsp;</p>
					<div class="control">
						<button id="setPasswordBtn" class="button admin_class is-fullwidth">' . $mylang['set_password'] . '</button>
					</div>
				</div>

			</div>


			';


		}


$low_buttons_html	.=	'</div>';

















echo	$user_details_html;
echo	$gebwms_html;
echo	$system_html;
echo	$low_buttons_html;



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
