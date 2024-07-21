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
	if (is_it_enabled($_SESSION['menu_adm_category']))
	{

		$user_company_uid	=	leave_numbers_only($_SESSION['user_company']);


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

			// When the Admin selects a new company!
			$('#id_company').change(function() {
				get_all_category_a();

				set_Element_Value_By_ID('id_catb_name',		'');
				set_Element_Value_By_ID('id_catb_status',	0);
				set_Element_Value_By_ID('id_hidden_catb',	0);
				$('#id_category_b_table > tbody').empty();


				set_Element_Value_By_ID('id_catc_name',		'');
				set_Element_Value_By_ID('id_catc_status',	0);
				set_Element_Value_By_ID('id_hidden_catc',	0);
				$('#id_category_c_table > tbody').empty();

				set_Element_Value_By_ID('id_catd_name',		'');
				set_Element_Value_By_ID('id_catd_status',	0);
				set_Element_Value_By_ID('id_hidden_catd',	0);
				$('#id_category_d_table > tbody').empty();

			});
			
			
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
					set_Element_Value_By_ID('id_hidden_catb',	0);

					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);


					//	Empty Category C and D table
					$('#id_category_c_table > tbody').empty();
					$('#id_category_d_table > tbody').empty();
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


					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);

					$('#id_category_d_table > tbody').empty();

					// 1 = ID
					$('#id_hidden_catb').val($(this).find('td:nth-child(1)').text()); 

					// Get all the details about the category!
					get_one_category_b_data();
					get_all_category_c();
			});


			// Triggers a function every time the row in the table id_category_c_table is clicked !
			$('#id_category_c_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlightedC').removeClass('highlightedC');
					$(this).addClass('highlightedC');

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);

					// 1 = ID
					$('#id_hidden_catc').val($(this).find('td:nth-child(1)').text()); 

					// Get all the details about the category!
					get_one_category_c_data();
					get_all_category_d();
			});



			// Triggers a function every time the row in the table id_category_d_table is clicked !
			$('#id_category_d_table').on('click', 'tr', function()
			{
					// When user clicks on anything it gets selected !
					$('.highlightedD').removeClass('highlightedD');
					$(this).addClass('highlightedD');

					// 1 = ID
					$('#id_hidden_catd').val($(this).find('td:nth-child(1)').text()); 

					// Get all the details about the category!
					get_one_category_d_data();
			});





		});




		// Get all companies for the selectbox!
		function get_all_companies()
		{

			$.post('geb_ajax_company.php', { 

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

			<?php
				
				//	Add ----- to the selectbox only for Admin!
				//	Maybe this can be done in a better way but for now it does not matter.
				//	FIX
				if ($user_company_uid == 0)
				{
					echo "addOption2SelectBox('id_company', 0, '-----');";
				}

			?>

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_company', obje.data[i].company_pkey, obje.data[i].company_code);	
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



		function get_all_category_a()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js	:	0,
				company_uid_js	:	get_Element_Value_By_ID('id_company')

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

					set_Element_Value_By_ID('id_cata_name',		'');
					set_Element_Value_By_ID('id_cata_status',	0);
					set_Element_Value_By_ID('id_hidden_cata',	0);

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
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_cata'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

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
					set_Element_Value_By_ID('id_catb_name',		'');
					set_Element_Value_By_ID('id_catb_status',	0);
					set_Element_Value_By_ID('id_hidden_catb',	0);
				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105556', '<?php	echo $mylang['server_error'];	?>');
					});

		}


		function get_all_category_c()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	2,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catb'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},

			function(output)
			{
				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#id_category_c_table > tbody').empty();
					$('#id_category_c_table > tbody').append(obje.html);
					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);
				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105557', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		function get_all_category_d()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	3,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catc'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},

			function(output)
			{
				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$('#id_category_d_table > tbody').empty();
					$('#id_category_d_table > tbody').append(obje.html);
					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105557', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		function get_one_category_a_data()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	5,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_cata'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},
			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_cata_name',		obje.data.cat_a_name);
					set_Element_Value_By_ID('id_cata_status',	obje.data.cat_a_disabled);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105558', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		function get_one_category_b_data()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	6,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catb'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},
			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catb_name',		obje.data.cat_b_name);
					set_Element_Value_By_ID('id_catb_status',	obje.data.cat_b_disabled);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105559', '<?php	echo $mylang['server_error'];	?>');
					});

		}





		function get_one_category_c_data()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	7,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catc'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},
			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catc_name',		obje.data.cat_c_name);
					set_Element_Value_By_ID('id_catc_status',	obje.data.cat_c_disabled);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105560', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		function get_one_category_d_data()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	8,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catd'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},
			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catd_name',		obje.data.cat_d_name);
					set_Element_Value_By_ID('id_catd_status',	obje.data.cat_d_disabled);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105560', '<?php	echo $mylang['server_error'];	?>');
					});

		}





		//	Add category A
		function add_category_a()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	10,
				cata_name_js		:	get_Element_Value_By_ID('id_cata_name'),
				cata_status_js		:	get_Element_Value_By_ID('id_cata_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

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

					set_Element_Value_By_ID('id_catb_name',		'');
					set_Element_Value_By_ID('id_catb_status',	0);
					set_Element_Value_By_ID('id_hidden_catb',	0);

					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);

					//	Empty list B, C and D
					$('#id_category_b_table > tbody').empty();
					$('#id_category_c_table > tbody').empty();
					$('#id_category_d_table > tbody').empty();
					get_all_category_a();

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105561', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		//	Add category B
		function add_category_b()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	11,
				cat_master_uid_js	:	get_Element_Value_By_ID('id_hidden_cata'),
				cat_name_js			:	get_Element_Value_By_ID('id_catb_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_catb_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

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
					set_Element_Value_By_ID('id_hidden_catb',	0);

					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);

					//	Empty list C and D
					$('#id_category_c_table > tbody').empty();
					$('#id_category_d_table > tbody').empty();
					//	Refresh the list
					get_all_category_b();

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105562', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		//	Add category C
		function add_category_c()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	12,
				cat_master_uid_js	:	get_Element_Value_By_ID('id_hidden_catb'),
				cat_name_js			:	get_Element_Value_By_ID('id_catc_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_catc_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);

					//	Refresh the list
					get_all_category_c();

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105563', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		//	Add category D
		function add_category_d()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	13,
				cat_master_uid_js	:	get_Element_Value_By_ID('id_hidden_catc'),
				cat_name_js			:	get_Element_Value_By_ID('id_catd_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_catd_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);

					//	Refresh the list
					get_all_category_d();

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105563', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		//	Update category A details
		function update_category_a()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	15,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_cata'),
				cat_name_js			:	get_Element_Value_By_ID('id_cata_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_cata_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

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
					set_Element_Value_By_ID('id_catb_name',		'');
					set_Element_Value_By_ID('id_catb_status',	0);
					set_Element_Value_By_ID('id_hidden_catb',	0);
					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);
					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);
					$('#id_category_b_table > tbody').empty();
					$('#id_category_c_table > tbody').empty();
					$('#id_category_d_table > tbody').empty();
					//	Refresh the list
					get_all_category_a();

					$.alertable.info(obje.control, obje.msg).always(function() {	});

				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105564', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
					});

		}






		//	Update category B details
		function update_category_b()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	16,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catb'),
				cat_name_js			:	get_Element_Value_By_ID('id_catb_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_catb_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

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
					set_Element_Value_By_ID('id_hidden_catb',	0);
					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);
					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);
					$('#id_category_c_table > tbody').empty();
					$('#id_category_d_table > tbody').empty();

					//	Refresh the list
					get_all_category_b();

					$.alertable.info(obje.control, obje.msg).always(function() {	});

				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105565', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
					});

		}



		//	Update category C details
		function update_category_c()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	17,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catc'),
				cat_name_js			:	get_Element_Value_By_ID('id_catc_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_catc_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catc_name',		'');
					set_Element_Value_By_ID('id_catc_status',	0);
					set_Element_Value_By_ID('id_hidden_catc',	0);
					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);
					$('#id_category_d_table > tbody').empty();

					//	Refresh the list
					get_all_category_c();

					$.alertable.info(obje.control, obje.msg).always(function() {	});

				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105566', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
					});

		}



		//	Update category D details
		function update_category_d()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	18,
				cat_uid_js			:	get_Element_Value_By_ID('id_hidden_catd'),
				cat_name_js			:	get_Element_Value_By_ID('id_catd_name'),
				cat_status_js		:	get_Element_Value_By_ID('id_catd_status'),
				company_uid_js		:	get_Element_Value_By_ID('id_company')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					set_Element_Value_By_ID('id_catd_name',		'');
					set_Element_Value_By_ID('id_catd_status',	0);
					set_Element_Value_By_ID('id_hidden_catd',	0);
					//	Refresh the list
					get_all_category_d();

					$.alertable.info(obje.control, obje.msg).always(function() {	});

				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('105566', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
					});

		}






	</script>





<style>


	.category_a_table { height: 320px; overflow-y: scroll;}

	.category_b_table { height: 320px; overflow-y: scroll;}

	.category_c_table { height: 320px; overflow-y: scroll;}

	.category_d_table { height: 320px; overflow-y: scroll;}


	/*	The sticky header... not perfect but works for now !! */

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

	.highlightedC {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}

	.highlightedD {
			color: #261F1D !important;
			background-color: #E5C37E !important;
	}


</style>



</head>
<body onLoad='get_all_category_a(); get_all_companies();'>


<?php

		// A little gap at the top to make it look better a notch.
		echo '<div class="blank_space_12px"></div>';

		echo '<section class="section is-paddingless">';
		echo	'<div class="container box has-background-light">';



	$top_menu	=	'';


	$top_menu	.=	'<div class="columns">';
	$top_menu	.=	'<div class="column is-3">';

	$top_menu	.=	'<button class="button admin_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';

	$top_menu	.=	'</div>';


	$top_menu	.=	'<div class="column is-3">';


$top_menu	.=	'
		<div class="field">
			<div class="control">
				<div class="select is-fullwidth">
					<select id="id_company">
					</select>
				</div>
			</div>
		</div>';


	$top_menu	.=	'</div>';


	$top_menu	.=	'</div>';


	echo $top_menu;


	$layout_details_html	=	'';

	$layout_details_html	.=	'<div class="columns">';


	//	Details of Category C
	$layout_details_html	.=	'

					<div class="column is-3">

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
						</div>';



if (can_user_add($_SESSION['menu_adm_category']))
{	

	$layout_details_html	.=	'
						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_category_a();">' . $mylang['add'] . ' (A)</button>
							</div>
						</div>';

}



if (can_user_update($_SESSION['menu_adm_category']))
{	

	$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="update_category_a();">' . $mylang['save'] . ' (A)</button>
							</div>
						</div>';
}



$layout_details_html	.=	'

						<input id="id_hidden_cata" class="input is-normal" type="hidden">

					</div>';



	// Details of Category B
	$layout_details_html	.=	'


					<div class="column is-3">


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
						</div>';



if (can_user_add($_SESSION['menu_adm_category']))
{

	$layout_details_html	.=	'
						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_category_b();">' . $mylang['add'] . ' (B)</button>
							</div>
						</div>';
}


if (can_user_update($_SESSION['menu_adm_category']))
{

	$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="update_category_b();">' . $mylang['save'] . ' (B)</button>
							</div>
						</div>';
}


$layout_details_html	.=	'
						<input id="id_hidden_catb" class="input is-normal" type="hidden">

					</div>


						';






	// Details of Category C
	$layout_details_html	.=	'


					<div class="column is-3">


						<div class="category_b_table it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="id_category_c_table">
								<thead>
									<tr>
										<th>UID</th>
										<th>' . $mylang['category'] . ' (C)</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['category'] . ' (C):</p>
							<div class="control">
								<input id="id_catc_name" class="input is-normal" type="text" placeholder="">
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ' (C):</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_catc_status">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>';



if (can_user_add($_SESSION['menu_adm_category']))
{

	$layout_details_html	.=	'
						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_category_c();">' . $mylang['add'] . ' (C)</button>
							</div>
						</div>';
}



if (can_user_update($_SESSION['menu_adm_category']))
{

	$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="update_category_c();">' . $mylang['save'] . ' (C)</button>
							</div>
						</div>';
}


$layout_details_html	.=	'
						<input id="id_hidden_catc" class="input is-normal" type="hidden">

					</div>


						';







	// Details of Category D
	$layout_details_html	.=	'


					<div class="column is-3">


						<div class="category_b_table it-has-border">
							<table class="table is-fullwidth is-hoverable is-scrollable" id="id_category_d_table">
								<thead>
									<tr>
										<th>UID</th>
										<th>' . $mylang['category'] . ' (D)</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>


						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['category'] . ' (C):</p>
							<div class="control">
								<input id="id_catd_name" class="input is-normal" type="text" placeholder="">
							</div>
						</div>

						<div class="field" style="'. $box_size_str .'">
							<p class="help">' . $mylang['status'] . ' (C):</p>
							<div class="field is-narrow">
							  <div class="control">
								<div class="select is-fullwidth">
									<select id="id_catd_status">

										<option value="0">' . $mylang['active'] . '</option>
										<option value="1">' . $mylang['disabled'] . '</option>

									</select>
								</div>
							  </div>
							</div>
						</div>';



if (can_user_add($_SESSION['menu_adm_category']))
{

	$layout_details_html	.=	'
						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="add_category_d();">' . $mylang['add'] . ' (D)</button>
							</div>
						</div>';
}



if (can_user_update($_SESSION['menu_adm_category']))
{

	$layout_details_html	.=	'

						<div class="field" style="'. $box_size_str .'">
							<p class="help">&nbsp;</p>
							<div class="control">
								<button class="button is-normal is-bold admin_class is-fullwidth"  onclick="update_category_d();">' . $mylang['save'] . ' (D)</button>
							</div>
						</div>';
}


$layout_details_html	.=	'
						<input id="id_hidden_catd" class="input is-normal" type="hidden">

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
