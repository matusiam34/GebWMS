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



	<script language="javascript" type="text/javascript">



		$(document).ready(function() 
		{

			// Triggers a function every time the row in the table departmentList is clicked !
			$('#curr_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlighted').removeClass('highlighted');
					$(this).addClass('highlighted');

					// 1 = ID
					$('#id_hidden').val($(this).find('td:nth-child(1)').text()); 

					// Get all the details from the table...
					get_one_user_data();

			});


		});



		// Grab all users
		function get_all_users()
		{

			$.post('geb_ajax_users.php', { 

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
		function get_one_user_data()
		{

			$.post('geb_ajax_users.php', { 

				action_code_js	:	1,
				user_uid_js		:	get_Element_Value_By_ID('id_hidden')

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
					set_Element_Value_By_ID('id_user_warehouse',		obje.data.user_warehouse);
					set_Element_Value_By_ID('id_user_active',			obje.data.user_active);

					//	ACL
					set_Element_Value_By_ID('id_product_search',		obje.data.menu_prod_search);
					set_Element_Value_By_ID('id_location_search',		obje.data.menu_location_search);


					set_Element_Value_By_ID('id_goodsin',				obje.data.menu_goodsin);
					set_Element_Value_By_ID('id_mpa',					obje.data.menu_mpa);
					set_Element_Value_By_ID('id_mpp',					obje.data.menu_mpp);
					set_Element_Value_By_ID('id_recent_activity',		obje.data.menu_recent_activity);


					set_Element_Value_By_ID('id_mgr_products',			obje.data.menu_mgr_products);


					set_Element_Value_By_ID('id_my_account',			obje.data.menu_my_account);
					set_Element_Value_By_ID('id_adm_users',				obje.data.menu_adm_users);
					set_Element_Value_By_ID('id_adm_warehouses',		obje.data.menu_adm_warehouse);
					set_Element_Value_By_ID('id_adm_wh_locations',		obje.data.menu_adm_warehouse_loc);
					set_Element_Value_By_ID('id_adm_categories',		obje.data.menu_adm_category);



				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						$.alertable.error('103556', '<?php	echo $mylang['server_error'];	?>');
					});

		}





		// UPDATE user details only!
		function update_user_details()
		{

			$.post('geb_ajax_users.php', { 

				action_code_js			:	3,
				user_uid_js				:	get_Element_Value_By_ID('id_hidden'),
				user_username_js		:	get_Element_Value_By_ID('id_user_name'),
				user_firstname_js		:	get_Element_Value_By_ID('id_user_firstname'),
				user_lastname_js		:	get_Element_Value_By_ID('id_user_lastname'),
				user_desc_js			:	get_Element_Value_By_ID('id_user_desc'),
				user_email_js			:	get_Element_Value_By_ID('id_user_email'),
				user_warehouse_js		:	get_Element_Value_By_ID('id_user_warehouse'),
				user_active_js			:	get_Element_Value_By_ID('id_user_active')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					get_all_users();	// repopulate the table
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

		}




		// UPDATE access control only!
		function update_user_acl()
		{

			$.post('geb_ajax_users.php', { 

				action_code_js			:	4,

				product_search_js		:	get_Element_Value_By_ID('id_product_search'),
				location_search_js		:	get_Element_Value_By_ID('id_location_search'),
				goodsin_js				:	get_Element_Value_By_ID('id_goodsin'),
				mpa_js					:	get_Element_Value_By_ID('id_mpa'),
				mpp_js					:	get_Element_Value_By_ID('id_mpp'),
				recent_activity_js		:	get_Element_Value_By_ID('id_recent_activity'),
				mgr_products_js			:	get_Element_Value_By_ID('id_mgr_products'),
				my_account_js			:	get_Element_Value_By_ID('id_my_account'),
				adm_users_js			:	get_Element_Value_By_ID('id_adm_users'),
				adm_warehouses_js		:	get_Element_Value_By_ID('id_adm_warehouses'),
				adm_wh_locations_js		:	get_Element_Value_By_ID('id_adm_wh_locations'),
				adm_categories_js		:	get_Element_Value_By_ID('id_adm_categories'),
				user_uid_js				:	get_Element_Value_By_ID('id_hidden')


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

		}




		//	ADD a new user to the system!
		function add_new_user()
		{

			$.post('geb_ajax_users.php', { 

				action_code_js			:	2,

				user_username_js		:	get_Element_Value_By_ID('id_user_name'),
				user_firstname_js		:	get_Element_Value_By_ID('id_user_firstname'),
				user_lastname_js		:	get_Element_Value_By_ID('id_user_lastname'),
				user_desc_js			:	get_Element_Value_By_ID('id_user_desc'),
				user_email_js			:	get_Element_Value_By_ID('id_user_email'),
				user_warehouse_js		:	get_Element_Value_By_ID('id_user_warehouse'),
				user_active_js			:	get_Element_Value_By_ID('id_user_active'),
				product_search_js		:	get_Element_Value_By_ID('id_product_search'),
				location_search_js		:	get_Element_Value_By_ID('id_location_search'),
				goodsin_js				:	get_Element_Value_By_ID('id_goodsin'),
				mpa_js					:	get_Element_Value_By_ID('id_mpa'),
				mpp_js					:	get_Element_Value_By_ID('id_mpp'),
				recent_activity_js		:	get_Element_Value_By_ID('id_recent_activity'),
				mgr_products_js			:	get_Element_Value_By_ID('id_mgr_products'),
				my_account_js			:	get_Element_Value_By_ID('id_my_account'),
				adm_users_js			:	get_Element_Value_By_ID('id_adm_users'),
				adm_warehouses_js		:	get_Element_Value_By_ID('id_adm_warehouses'),
				adm_wh_locations_js		:	get_Element_Value_By_ID('id_adm_wh_locations'),
				adm_categories_js		:	get_Element_Value_By_ID('id_adm_categories')


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

			$.post('geb_ajax_warehouse.php', { 

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

					emptySelectBox('id_user_warehouse');
					addOption2SelectBox('id_user_warehouse', 0, '<?php	echo $mylang['all'];	?>');	

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
<body onLoad='get_all_users(); get_all_warehouses();'>


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




	//	The user table + details + update button?
	$user_details_html	=	'';

	$user_details_html	.=	'<div class="columns">';



	// User table
	$user_details_html	.=	'

					<div class="column is-4">

						<div class="tableAttr it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="curr_table">
								<thead>
									<tr>
										<th>UID</th>
										<th>' . $mylang['user'] . '</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>

					</div>';



	// User details
	$user_details_html	.=	'


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




						';




	// If the operator has the ability to update...
	if (can_user_update($_SESSION['menu_adm_users']))
	{

		//	Update button section?!
		$user_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button admin_class is-fullwidth"  onclick="update_user_details();">' . $mylang['save_details'] . '</button>
							</div>
						</div>';

	}





	$user_details_html	.=	'

					</div>';




	$user_details_html	.=	'

				</div>';


echo	$user_details_html;






//	<!--		The ACL section here		-->


//	Configure the AC for each menu as a drop down. This should make it a bit easier.
//	The trick will be to figure out which page has what options and provide them to the Administrator
//	as an option. Do this once and all will be good! That is as long as the page that is configured does not
//	expand in functionality... Keep that in mind!


	$user_acl_html	=	'<div class="columns">';

	$user_acl_html	.=	'<div class="column is-2">';

	$user_acl_html	.=	'


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


';


	$user_acl_html	.=	'</div>';





	$user_acl_html	.=	'<div class="column is-2">';

	$user_acl_html	.=	'






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
</div>';





	$user_acl_html	.=	'</div>';





	$user_acl_html	.=	'<div class="column is-2">';

	$user_acl_html	.=	'


<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['my_account'] . ':</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-yellow is-fullwidth">
			<select style="' . $color_manager . '" id="id_mgr_products">

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



';



	$user_acl_html	.=	'</div>';



	$user_acl_html	.=	'<div class="column is-2">';

	$user_acl_html	.=	'



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
';





	$user_acl_html	.=	'</div>';


	// If the operator has the ability to update...
	if (can_user_update($_SESSION['menu_adm_users']))
	{

		//	Update button section?!
		$user_acl_html	.=	'<div class="column is-2">';

		$user_acl_html	.=	'

			<div class="field" style="'. $box_size_str .'">
				<p class="help">&nbsp;</p>
				<div class="control">
					<button class="button admin_class is-fullwidth" onclick="update_user_acl();">' . $mylang['update_acl'] . '</button>
				</div>
			</div>';

		$user_acl_html	.=	'</div>';

	}






	// If the operator has the ability to add...
	if (can_user_add($_SESSION['menu_adm_users']))
	{

		//	Add user input + button
		$user_acl_html	.=	'<div class="column is-2">';

		$user_acl_html	.=	'

			<div class="field" style="'. $box_size_str .'">
				<p class="help">&nbsp;</p>
				<div class="control">
					<button class="button admin_class is-fullwidth" onclick="add_new_user();">' . $mylang['add_user'] . '</button>
				</div>
			</div>';

		$user_acl_html	.=	'</div>';

	}


	$user_acl_html	.=	'</div>';	//	close the ACL "row"





echo	$user_acl_html;




//	Place it in a better space maybe? Not urgent.
echo	'<input id="id_hidden" class="input is-normal" type="hidden">';




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
