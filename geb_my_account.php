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
	require_once('lib_system.php');


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
	echo '<div class="blank_space_12px"></div>';


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


		//	Warehouse code set for the operator is in the session. Can be changed by the admin in the USERS tab
		$user_warehouse_uid		=	leave_numbers_only($_SESSION['user_warehouse']);
		$warehouse_name			=	$mylang['all'];

		//	The company that the user is a part of.
		$user_company_uid		=	leave_numbers_only($_SESSION['user_company']);
		$company_name			=	$mylang['all'];	//	This most likely will be ONLY the super admin! No normal operator should be in ALL since
													//	it will be a major issue with barcodes of products etc etc




		//	Run the query only if it is NOT all warehouses!
		if ($user_warehouse_uid > 0)
		{


			$sql	=	'


				SELECT

				wh_code

				FROM 

				geb_warehouse

				WHERE

				wh_pkey = :swh_uid


			';



			if ($stmt = $db->prepare($sql))
			{

				$stmt->bindValue(':swh_uid',	$user_warehouse_uid,	PDO::PARAM_INT);
				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$warehouse_name		=	trim($row['wh_code']);
				}

			}
			// show an error if the query has an error
			else
			{
				//	FIX
			}


		}




		//	Run the query only if it is NOT all companies!
		if ($user_company_uid > 0)
		{


			$sql	=	'


				SELECT

				company_code

				FROM 

				geb_company

				WHERE

				company_pkey = :scompany_uid


			';



			if ($stmt = $db->prepare($sql))
			{

				$stmt->bindValue(':scompany_uid',	$user_company_uid,	PDO::PARAM_INT);
				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$company_name		=	trim($row['company_code']);
				}

			}
			// show an error if the query has an error
			else
			{
				//	FIX
			}


		}












		//	Get the warehouse code here (it will be a string usually) and jam it in to some tiny table.
		//	Maybe other things can be placed in it later on.
		$info_table_html	=	'';

		$info_table_html	=	'<table class="is-fullwidth table is-bordered">';


			$info_table_html	.=	'<tr>';
				$info_table_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['company'] . ':</td>';
				$info_table_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $company_name . '</td>';
			$info_table_html	.=	'</tr>';

			$info_table_html	.=	'<tr>';
				$info_table_html	.=	'<td style="width:40%; background-color: ' . $backclrA . '; font-weight: bold;">' . $mylang['warehouse'] . ':</td>';
				$info_table_html	.=	'<td style="background-color: ' . $backclrB . ';">' . $warehouse_name . '</td>';
			$info_table_html	.=	'</tr>';

		$info_table_html	.=	'</table>';







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


				//	If the user is set to be an Admin ==>> Allow to change the company ID! This is super useful when 
				//	the admin needs to "login" as a different company to see the products, troubleshoot a picking and other related stuff

				if (is_it_enabled($_SESSION['user_is_admin']))
				{

				}






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


				echo	'<div class="column is-3">';
				echo	'<p>TD: Display ACL details here</p>';
				echo	'</div>';	//	end of columns


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


