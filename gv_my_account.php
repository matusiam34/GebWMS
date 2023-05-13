<?php

//	Each user can manage their account within limits. Change password, language and other stuff etc 


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


	<!-- Favicon
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="icon" type="image/png" href="images/favicon.png">



	<script language="javascript" type="text/javascript">


		$(document).ready(function() 
		{


		});




		// UPDATE item 
		function update_item()
		{


			$.post('ajax_update_language.php', { 

				language_js			:	get_Element_Value_By_ID('id_language')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);
				set_HTML_to_Element_By_ID('id_message', obje.msg);

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						alert('server problem');
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

/*
	$page_form	.=	'<p class="control">';
	$menu_link	=	"'index.php'";
	$page_form	.=		'<button class="button inventory_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
	$page_form	.=	'</p>';
*/

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

				//	Warehouse code set for the operator is in the session. Can be changed by the admin in the USERS tab
				$user_warehouse_uid		=	leave_numbers_only($_SESSION['user_warehouse']);



				//	Get the warehouse code here (it will be a string usually) and jam it in to some tiny table.
				//	Maybe other things can be placed in it later on.
				$info_table_html	=	'';


		$sql	=	'


			SELECT

			wh_code

			FROM 

			geb_warehouse

			WHERE

			wh_pkey = :swh_uid

			AND

			wh_disabled = 0



		';



		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':swh_uid',	$user_warehouse_uid,	PDO::PARAM_INT);
			$stmt->execute();


			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

				$info_table_html	=	'<table class="is-fullwidth table is-bordered">';

					$info_table_html	.=	'<tr>';
						$info_table_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['warehouse'] . ':</td>';
						$info_table_html	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['wh_code']) . '</td>';
					$info_table_html	.=	'</tr>';

				$info_table_html	.=	'</table>';

			}

		}
		// show an error if the query has an error
		else
		{
			//	echo 'Location Query failed!';	//	need a better way for this page. no urgent issue
		}












				echo	'<div class="columns">';

				echo	'<div class="column is-3">';



				echo	$info_table_html;


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
							<button class="button inventory_class is-fullwidth" onclick="update_item();">' . $mylang['save'] . '</button>
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


