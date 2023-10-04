<?php


//	Each user can manage their account within limits.



// load the login class
require_once('lib_login.php');


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    

	// load the supporting functions....
	require_once('lib_functions.php');


	//	Allow the operator to see it is enough.
	if (is_it_enabled($_SESSION['menu_my_account']))
	{

		// needs a db connection...
		require_once('lib_db_conn.php');


?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['my_account'];	?></title>
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


		});




		// UPDATE the profile to a different language
		function update_language()
		{


			$.post('ajax_my_account.php', { 

				action_code_js		:	0,
				language_js			:	get_Element_Value_By_ID('id_language')

			},

			function(output)
			{

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
						// something went wrong
						$.alertable.error('104555', '<?php	echo $mylang['server_error'];	?>');
					});

		}




	</script>



</head>
<body>





<?php


	// A little gap at the top to make it look better a notch.
	echo '<div style="height:12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';

	$page_form	=	'';


	$page_form	.=	'<p class="control">';
	$page_form	.=		'<button class="button inventory_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
	$page_form	.=	'</p>';



	// Show the page header

	echo '<nav class="level">

	<!-- Left side -->
		<div class="level-left">

		<div class="level-item">
	' . $page_form . '
		</div>

		</div>

	</nav>';


				$user_lang		=	trim($_SESSION['user_language']);


				echo	'<div class="columns">';

				echo	'<div class="column is-3">';

				echo	'<div class="field" style="'. $box_size_str . '">
							<p class="help">' . $mylang['language'] . ':</p>
							<div class="field is-narrow">
								<div class="control">
									<div class="select is-fullwidth">
										<select id="id_language" name="id_language" >';

								//	Give the user all of the languages in a dropdown + show the selected on as default!
								foreach ($supported_languages_arr as $language)
								{
									$selected	=	'';
									if (strcmp($language, $user_lang) === 0)	{	$selected	=	' selected ';	}
									echo	'<option value="' . $language . '"' . $selected . '>' . $language . '</option>';
								}


				echo						'</select>
									</div>
								</div>
							</div>
						</div>';



				// If the operator has the ability to change the language...
				if (can_user_update($_SESSION['menu_my_account']))
				{
					echo	'

					<div class="field" style="'. $box_size_str .'">
						<p class="help">&nbsp;</p>
						<div class="control">
							<button class="button inventory_class is-fullwidth" onclick="update_language();">' . $mylang['save'] . '</button>
						</div>
					</div>

					<p class="help" id="id_message">&nbsp;</p>
					';
				}




				echo	'</div>';
				echo	'</div>';	//	end of columns




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


