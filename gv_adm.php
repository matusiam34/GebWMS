<?php


// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once("lib_passwd.php");

// include the configs / constants for the database connection
require_once("lib_db.php");

// load the login class
require_once("lib_login.php");




// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    


	// load the supporting functions....
	require_once("lib_functions.php");


	//	Certain access right checks should be executed here...
	if (leave_numbers_only($_SESSION['user_priv']) ==	admin_priv)
	{

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Admin Dashboard</title>
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

		});


	</script>


<style>


</style>



</head>
<body>




<?php



		// A little gap at the top to make it look better a notch.
		echo '<div style="height:12px"></div>';

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



			echo '<div class="columns">';

				// This needs to be re-evaluted based on the rights I will give to users... But that later!
				echo '<div class="column is-3">';


/*
					$category_link		=	"location.href='geb_admin_product_category.php'";
					echo '<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $category_link . '">Product Category</a>';

					echo '<div style="height:16px;"></div>';
*/


					$warehouses_link		=	"location.href='gv_adm_wh.php'";
					echo '<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $warehouses_link . '">Warehouses</a>';

					echo '<div style="height:16px;"></div>';

					$warehouse_locations_link		=	"location.href='gv_adm_wh_loc.php'";
					echo '<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $warehouse_locations_link . '">Warehouse Locations</a>';

				echo '</div>';

			echo '</div>';



		echo '</div>';

	echo '</section>';






	}
	else
	{
		// User has logged in but does not have the rights to access this page !
		include("not_logged_in.php");
	}


}
else
{

    // the user is not logged in. you can do whatever you want here.
    include("not_logged_in.php");

}

?>


</body>
</html>
