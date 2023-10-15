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

?>


	<!DOCTYPE html>
	<html lang="en">
	<head>

		<!-- Basic Page Needs
		–––––––––––––––––––––––––––––––––––––––––––––––––– -->
		<meta charset="utf-8">
		<title><?php	echo $mylang['main_dashboard'];	?></title>
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


		<script>



			$(document).ready(function() 
			{

				//	Submit the product search form when the operator on the desktop selects a recent item... No need to press enter :)
				$('#product').change(function() {
					this.form.submit();
				});


				//	Submit the location search form when the operator on the desktop selects a recent item... No need to press enter :)
				$('#location').change(function() {
					this.form.submit();
				});

			});


			// fixed the resub error message! Important if you don't wanna be annoyed!
			if ( window.history.replaceState )
			{
				window.history.replaceState( null, null, window.location.href );
			}

		</script>


	</head>
	<body>





<?php


	//
	//
	//	Red		:	Admin type of functionality
	//	Blue	:	Manager type of functionality
	//	Green	:	Sales / Reporting functionality
	//	Brown	:	Warehouse operator stuff?!
	//
	//




	// A little gap at the top to make it look better a notch.
	echo '<div class="blank_space_12px"></div>';

	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';



		echo '<div class="columns">';


			echo '<div class="column is-3">';

				//	Show the product search if the user has that feature enabled
				if (is_it_enabled($_SESSION['menu_prod_search']))
				{

					echo	'<div class="field">
							<form action="gv_search_product.php" method="get">

								<div class="field has-addons">

									<p class="control is-expanded">
										<input class="input" type="text" id="product" name="product" placeholder="' . $mylang['product_code'] . '">
									</p>

									<p class="control">
										<button class="button inventory_class iconSearch" style="width:50px;" type="submit"></button>
									</p>

								</div>

							</form>
							</div>';

				}


				//	Show the location search if the user has that feature enabled
				if (is_it_enabled($_SESSION['menu_location_search']))
				{

					echo	'

						<div class="field">
						<form action="gv_search_location.php" method="get">
							<div class="field has-addons" >

								<p class="control is-expanded">
									<input class="input" type="text" id="location" name="location" placeholder="' . $mylang['location_barcode'] . '">
								</p>

								<p class="control">
									<button class="button inventory_class iconSearch" style="width:50px;" type="submit"></button>
								</p>

							</div>
						</form>
						</div>';

				}


			echo '</div>';


			echo '<div class="column is-3">';


				// Display menu items that are granted


				if (is_it_enabled($_SESSION['menu_goodsin']))
				{
					$goodsin_link		=	"location.href='gv_goods_in.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth inventory_class is-bold" onclick="' . $goodsin_link . '">' . $mylang['goodsin'] . '</a>
								</div>
							</div>';
				}



				if (is_it_enabled($_SESSION['menu_prod2loc']))
				{
					$bookin_prod2loc_link		=	"location.href='gv_move_prod2loc.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth inventory_class is-bold" onclick="' . $bookin_prod2loc_link . '">' . $mylang['prod2location'] . '</a>
								</div>
							</div>';
				}



			echo '</div>';




			// Manager type of stuff here?!?! Still under construction!
			echo '<div class="column is-3">';



				if (is_it_enabled($_SESSION['menu_mgr_products']))
				{
					$products_link		=	"location.href='geb_view_mgr_products.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth manager_class is-bold" onclick="' . $products_link . '">' . $mylang['products'] . '</a>
								</div>
							</div>';

				}


			echo '</div>';



			// More power section?
			echo '<div class="column is-3">';


				// My Account! Change Password, set Language etc etc 
				if (is_it_enabled($_SESSION['menu_my_account']))
				{
					$my_account_link		=	"location.href='view_my_account.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth inventory_class is-bold" onclick="' . $my_account_link . '">' . $mylang['my_account'] . '</a>
								</div>
							</div>';
				}



				// Basic user management + Access Control tool
				if (is_it_enabled($_SESSION['menu_adm_users']))
				{
					$users_link		=	"location.href='geb_view_adm_users.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $users_link . '">' . $mylang['users'] . '</a>
								</div>
							</div>';
				}



				if (is_it_enabled($_SESSION['menu_adm_warehouse']))
				{
					$warehouses_link		=	"location.href='geb_view_adm_warehouses.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $warehouses_link . '">' . $mylang['warehouses'] . '</a>
								</div>
							</div>';
				}



				if (is_it_enabled($_SESSION['menu_adm_warehouse_loc']))
				{
					$warehouse_locations_link		=	"location.href='geb_view_adm_warehouse_locations.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $warehouse_locations_link . '">' . $mylang['warehouse_locations'] . '</a>
								</div>
							</div>';
				}


				if (is_it_enabled($_SESSION['menu_adm_category']))
				{
					$category_link		=	"location.href='geb_view_adm_categories.php'";

					echo	'<div class="field">
								<div class="control">
									<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $category_link . '">' . $mylang['categories'] . '</a>
								</div>
							</div>';
				}





				//	Everyone deserves to leave!
				$logout_link		=	"location.href='index.php?logout'";
				echo '<a class="button is-normal is-fullwidth yellow_class is-bold" onclick="' . $logout_link . '">' . $mylang['logout'] . '</a>';

			echo '</div>';





		echo '</div>';


		echo '</div>';




		echo	'<div class="container box has-background-light">';


		//	Footer with GebWMS info? Display the username?
		echo	'

		<div class="has-text-centered">
			<p>
				' . $mylang['logged_in_as'] . ' <strong>' . trim($_SESSION['user_name']) . '</strong>
			</p>
		</div>';



		echo '</div>';




	echo '</section>';


?>


    </body>
	</html>


<?php

}
else
{
 
    // the user is not logged in. Show them the login page.
    include('not_logged_in.php');

}
