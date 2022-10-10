<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Main Dashboard</title>
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

		// fixed the resub error message! Important if you don't wanna be annoyed!
		if ( window.history.replaceState )
		{
			window.history.replaceState( null, null, window.location.href );
		}

	</script>


</head>
<body>





<?php



// A little gap at the top to make it look better a notch.
echo '<div style="height:12px"></div>';

echo '<section class="section is-paddingless">';
echo	'<div class="container box has-background-light">';


	$page_form	=	'<form action="gv_product_search.php" method="get">';

		$page_form	.=	'<div class="field has-addons">';

			$page_form	.=	'<p class="control is-expanded">';
			$page_form	.=		'<input class="input" type="text" id="product" name="product" placeholder="Product code">';
			$page_form	.=	'</p>';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<button class="button inventory_class iconSearch" style="width:50px;" type="submit"></button>';
			$page_form	.=	'</p>';

		$page_form	.=	'</div>';

	$page_form	.=	'</form>';


	echo '<div class="columns">';


		echo '<div class="column is-3">';
			echo $page_form;
		echo '</div>';


		echo '<div class="column is-3">';
		echo '</div>';


		echo '<div class="column is-3">';
		echo '</div>';


		// More power section?
		echo '<div class="column is-3">';


			if (leave_numbers_only($_SESSION['user_priv']) ==	admin_priv)
			{

				$admin_link		=	"location.href='gv_adm.php'";

				echo	'<div class="field">
							<div class="control">
								<a class="button is-normal is-fullwidth admin_class is-bold" onclick="' . $admin_link . '">Admin</a>
							</div>
						</div>';

			}

			if (leave_numbers_only($_SESSION['user_priv']) >=	manager_priv)
			{

				$manager_link		=	"location.href='gv_mgr.php'";

				echo	'<div class="field">
							<div class="control">
								<a class="button is-normal is-fullwidth manager_class is-bold" onclick="' . $manager_link . '">Manager</a>
							</div>
						</div>';

			}


			$logout_link		=	"location.href='index.php?logout'";
			echo '<a class="button is-normal is-fullwidth inventory_class is-bold" onclick="' . $logout_link . '">Logout</a>';

		echo '</div>';


	echo '</div>';




	echo '</div>';
echo '</section>';


?>





</body>
</html>
