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


	// Certain access rights checks should be executed here...
	if (is_it_enabled($_SESSION['menu_prod2loc']))
	{


?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['prod2location'];	?></title>
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

			$("#prod_barcode").focus();
			
			$("#prod_barcode").change(function() {
				get_product_details();
			});

		});


		function decrease_value()
		{
			var qty_value = parseInt(get_Element_Value_By_ID('product_qty')) - 1;
			// Do not allow a QTY of 0
			if (qty_value >= 1)
			{
				set_Element_Value_By_ID('product_qty', qty_value);
			}
			set_Focus_On_Element_By_ID('location_code');
		}

		function increase_value()
		{
			var qty_value = parseInt(get_Element_Value_By_ID('product_qty')) + 1;
			set_Element_Value_By_ID('product_qty', qty_value);
			set_Focus_On_Element_By_ID('location_code');
		}

		// A litte quality of life thing? 1 click = 10 qty!
		// Looks good on the desktop version but not so good on my xiaomi... The input space is taken a bit much... Layout redesign?
		function increase_value_by_10()
		{
			var qty_value = parseInt(get_Element_Value_By_ID('product_qty')) + 10;
			set_Element_Value_By_ID('product_qty', qty_value);
			set_Focus_On_Element_By_ID('location_code');
		}



		function get_product_details()
		{

			$.post('geb_ajax_prod2loc.php', { 

				action_code_js		:	0,
				prod_barcode_js		:	get_Element_Value_By_ID('prod_barcode')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					empty_Element_By_ID('product_details');
					append_HTML_to_Element_By_ID('product_details', obje.html);
				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('107555', '<?php	echo $mylang['server_error'];	?>');
					});

		}


		function get_location_details()
		{

			$.post('geb_ajax_prod2loc.php', { 

				action_code_js		:	1,
				prod_barcode_js		:	get_Element_Value_By_ID('prod_barcode'),
				prod_qty_js			:	get_Element_Value_By_ID('product_qty'),
				loc_barcode_js		:	get_Element_Value_By_ID('location_barcode')

			},

			function(output)
			{
alert(output);
				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					//empty_Element_By_ID('product_details');
					//append_HTML_to_Element_By_ID('product_details', obje.html);
					$.alertable.info(obje.control, obje.msg);

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('107555', '<?php	echo $mylang['server_error'];	?>');
					});

		}







	</script>



</head>
<body>





<?php


	// A little gap at the top to make it look better a notch.
	echo '<div class="blank_space_12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_controls	=	'<p class="control">';
	$menu_link		=	"'index.php'";
	$page_controls	.=		'<button class="button inventory_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
	$page_controls	.=	'</p>';

	$page_controls	.=	'<p class="control">';
	$page_controls	.=		'<button class="button inventory_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
	$page_controls	.=	'</p>';




/*
	// The "menu"!
	echo '<nav class="level">

	<!-- Left side -->
		<div class="level-left">

		<div class="level-item">
	' . $page_controls . '
		</div>

		</div>

	</nav>';
*/


/*
	echo	'<div class="columns">';
	echo		'<div class="column is-4">';

	echo		'</div>';
	echo	'</div>';
*/


	echo	'<div class="columns">';
	echo		'<div class="column is-6">';

	$menu_link		=	"'index.php'";


	echo			'<div class="field has-addons is-marginless">

						<p class="control is-expanded">
							<input class="input is-fullwidth" type="text" id="prod_barcode" placeholder="' . $mylang['product_code'] . '">
						</p>

						<p class="control">
							<button class="button inventory_class iconSearch" style="width:50px;" onClick="get_product_details();"></button>
						</p>

						<p class="control">
							<button class="button inventory_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>
						</p>

					</div>';


	echo		'<div id="product_details"></div>';
	echo		'<div id="location_details"></div>';



	echo		'</div>';
	echo	'</div>';



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


