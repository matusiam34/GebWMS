<?php

//	MPP:	Manual Product Pick

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
	if (is_it_enabled($_SESSION['menu_mpp']))
	{


?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['mpp'];	?></title>
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

			//$("#product_barcode").focus();
			set_Focus_On_Element_By_ID('location_barcode');

			
			$('#location_barcode').keypress(function(event)
			{
				if (event.which === 13)
				{ // Check if Enter key is pressed
					event.preventDefault(); // Prevent default form submission
					get_location_details();
				}
			});	



		});




		function clear_location_barcode()
		{
			empty_Element_By_ID('product_details');
			empty_Element_By_ID('location_details')
			empty_Element_By_ID('error_details')
			set_Element_Value_By_ID("location_barcode", "");
			set_Focus_On_Element_By_ID("location_barcode");
		}



		function decrease_value(id)
		{
			var input = document.getElementById(id);
			var value = parseInt(input.value);
			if (value > 0)
			{
				input.value = value - 1;
			}
		}


		function increase_value(id)
		{
			var input = document.getElementById(id);
			var value = parseInt(input.value);
			var stock = parseInt(input.getAttribute('data-stock'));
			if (value < stock)
			{
				input.value = value + 1;
			} else {
				//
				//	Provide any feedback or is this just plain obvious?!
				//	alert('Cannot exceed available stock of ' + stock);
				//
			}
		}




		//	Get basic info + do some checks!
		function get_location_details()
		{

			$.post('geb_ajax_mpp.php', { 

				action_code_js		:	0,
				loc_barcode_js		:	get_Element_Value_By_ID('location_barcode')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					//empty_Element_By_ID('product_details');
					//append_HTML_to_Element_By_ID('product_details', obje.html);
					empty_Element_By_ID('location_details');
					empty_Element_By_ID('product_details');
					empty_Element_By_ID('error_details')
					append_HTML_to_Element_By_ID('location_details', obje.html);

				}
				else
				{
					//$.alertable.error(obje.control, obje.msg);
					empty_Element_By_ID('location_details');
					empty_Element_By_ID('product_details');
					empty_Element_By_ID('error_details');
					append_HTML_to_Element_By_ID('error_details', obje.html);

				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('107555', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		function collectProductData()
		{

			var productData = [];
			$('.product_qty').each(function()
			{

				var prod_uid	=	$(this).data('prod');
				var quantity	=	$(this).val();
				var unit		=	$(this).data('unit');

				if (quantity > 0)
				{
					productData.push
					({
						id:		prod_uid,
						qty:	quantity,
						unit:	unit
					});
				}

			});

			return productData;

		}



		//	Send everything!
		function sendProductData(productData)
		{

			console.log(productData);

			$.post('geb_ajax_mpp.php', {

				action_code_js			:	1,
				loc_barcode_js			:	get_Element_Value_By_ID('location_barcode'),
				productData_js			:	productData

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					alert(obje.msg);
/*
					empty_Element_By_ID('product_details');
					empty_Element_By_ID('location_details');
					empty_Element_By_ID('error_details');
					set_Element_Value_By_ID("product_barcode", "");
					set_Focus_On_Element_By_ID("product_barcode");
*/
				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
					append_HTML_to_Element_By_ID('error_details', obje.html);
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



	echo	'<div class="columns">';
	echo		'<div class="column is-6">';

	$menu_link		=	"'index.php'";


	echo			'<div class="field has-addons is-marginless">

						<p class="control is-expanded">
							<input class="input is-fullwidth" type="text" id="location_barcode" placeholder="' . $mylang['location_barcode'] . '">
						</p>

						<p class="control">
							<button class="button inventory_class iconSearch" style="width:50px;" onClick="get_location_details();"></button>
						</p>

						<p class="control">
							<button class="button inventory_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>
						</p>

						<p class="control">
							<button class="button inventory_class iconFocus" style="width:50px;" onClick="clear_location_barcode();"></button>
						</p>



					</div>';


	echo		'<div class="is-marginless" id="location_details"></div>';
	echo		'<div class="is-marginless" id="product_details"></div>';
	echo		'<div class="is-marginless" id="error_details"></div>';



//is-fullwidth table is-bordered 

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


