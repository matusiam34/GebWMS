<?php


//
//
//	How can I make it easy for the Admin to manage access?
//
//	I got so far:
//
//	E:	Enabled
//	A:	Add
//	U:	Update
//	D:	Delete
//
//	E
//	EU
//	ED
//	EA
//	EUD
//	EAU
//	EAD
//	EAUD
//
//
//
//
//
//

/*
	"menu_adm_warehouse"	INTEGER DEFAULT 0,
	"menu_adm_warehouse_loc"	INTEGER DEFAULT 0,
	"menu_adm_users"	INTEGER DEFAULT 0,
	"menu_prod_search"	INTEGER DEFAULT 0,
	"menu_location_search"	INTEGER DEFAULT 0,
	"menu_prod2loc"	INTEGER DEFAULT 0,
	"menu_recent_activity"	INTEGER DEFAULT 0,
	"menu_mgr_prod_add_update"	INTEGER DEFAULT 0,
*/


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
	if (is_it_enabled($_SESSION['menu_adm_users']))
	{




?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Users</title>
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

			// Triggers a function every time the row in the table departmentList is clicked !
			$('#curr_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlighted').removeClass('highlighted');
					$(this).addClass('highlighted');

					// 1 = ID
					$('#id_hidden').val($(this).find('td:nth-child(1)').text()); 


			});


		});



		// Grab all users
		function get_all_items()
		{

			$.post('ajax_get_all_users.php', { 

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					$('#curr_table').find('tr:gt(0)').remove();
					$('#curr_table > tbody').append(obje.html);

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



		// Add an item to the database
		function add_item()
		{

			var item_name_str	=	get_Element_Value_By_ID('id_item_name');


			$.post('ajax_add_warehouse.php', { 

				new_item_name_js	:	item_name_str

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_item_name', '');
					get_all_items();	// repopulate the table !
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



		// UPDATE item 
		function update_item()
		{

			var item_name_str	=	get_Element_Value_By_ID('id_item_name');
			var item_id_str		=	get_Element_Value_By_ID('id_hidden');


			$.post('ajax_update_warehouse.php', { 

				item_name_js	:	item_name_str,
				item_id_js		:	item_id_str

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_item_name', '');
					get_all_items();	// repopulate the table !
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



	.tableAttr { height: 360px; overflow-y: scroll;}



	/*	The sticky header... not perfect but works for now !! Not sure if I wanna use it here... hmmm...	*/

	table th
	{
		/*position: sticky;*/
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
<body onLoad='get_all_items();'>


<?php

		// A little gap at the top to make it look better a notch.
		echo '<div style="height:12px"></div>';

		echo '<section class="section is-paddingless">';
		echo	'<div class="container box has-background-light">';



				$page_form	=	'<p class="control">';
				$page_form	.=		'<button class="button admin_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
				$page_form	.=	'</p>';


				$page_form	.=	'<p class="control">';
				$menu_link	=	"'index.php'";
				$page_form	.=		'<button class="button admin_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
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


?>


				<div class="columns">

					<div class="column is-3">
						<div class="tableAttr">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="curr_table">
							<thead>
								<tr>
									<th>ID</th>
									<th>Username</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
							</table>
						</div>

					</div>




					<div class="column is-3">

						<!--	The &nbsp; in the <p class="help"></p> is just a "fix" so that everything aligns otherwise it looks odd...		-->

						<input id="id_hidden" class="input is-normal" type="text">

					</div>


<?php


//	Configure the AC for each menu as a drop down. This should make it a bit easier.
//	The trick will be to figure out which page has what options and provide them to the Administrator
//	as an option. Do this once and all will be good! That is as long as the page that is configured does not
//	expand in functionality... Keep that in mind!

/*

<div class="field" style="'. $box_size_str .'">
	<p class="help">Status:</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_disabled" name="id_disabled">' . $status_html . '
			</select>
		</div>
	  </div>
	</div>
</div>

*/


	$columns_html	=	'';

	$columns_html	.=	'<div class="column is-3">';


	$columns_html	.=	'


<div class="field" style="'. $box_size_str .'">
	<p class="help">Product Search:</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_product_search" name="id_product_search">
				<option value="32768">Disabled</option>
				<option value="49152">E</option>
			</select>
		</div>
	  </div>
	</div>
</div>



<div class="field" style="'. $box_size_str .'">
	<p class="help">Location Search:</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_product_search" name="id_product_search">
				<option value="32768">Disabled</option>
				<option value="49152">E</option>
			</select>
		</div>
	  </div>
	</div>
</div>






';





	$columns_html	.=	'</div>';





echo	$columns_html;

/*
	// If the operator has the ability to add...
	if (can_user_add($_SESSION['menu_adm_warehouse']))
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
	if (can_user_update($_SESSION['menu_adm_warehouse']))
	{
		echo	'

		<div class="field" style="'. $box_size_str .'">
			<p class="help">&nbsp;</p>
			<div class="control">
				<button class="button admin_class is-fullwidth"  onclick="update_item();">Update</button>
			</div>
		</div>';
	}

*/





?>



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

    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');

}

?>



</body>
</html>
