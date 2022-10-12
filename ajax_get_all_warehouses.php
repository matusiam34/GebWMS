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
if ($login->isUserLoggedIn() == true) {

    // the user is logged in. Perform the sql query !


	try
	{


		// load the supporting functions....
		require_once("lib_functions.php");
		require_once("lib_db_conn.php");



		// allow to execute script only if the requirements are met !
		// min_priv : variable that holds the lowest level user that can access and execute this script
		if 
		(

			(

				(can_user_access($_SESSION['user_inventory']))

			)

			AND

			(leave_numbers_only($_SESSION['user_priv']) >=	min_priv)

		)

		{


				if ($stmt = $db->prepare("

				SELECT

				*

				FROM  geb_warehouse

				WHERE

				wh_disabled = 0


				ORDER BY wh_code ASC

				"))
				{


					$stmt->execute();

					// My result array before encoded via json !
					$result = array();
					$result['control'] = 0;		// 0 means all went well !!!

					/* fetch values */
					$data_results	=	array();
					$table_text		=	"";


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{

						// Wrap the values into an array for json encoding !
						$row_result = array
						(
								'warehouse_id'			=> trim($row['wh_pkey']),
								'warehouse_name'		=> trim($row['wh_code'])
						);

						$data_results[] = $row_result;

						//if (trim($row['prod_item_cat_core']) == 1)	{	$core_item	=	"Y";	}

						$table_text		.=		'<tr>';
						$table_text		.=			'<td>'	.	trim($row['wh_pkey'])	.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['wh_code'])	.	'</td>';
						$table_text		.=		'</tr>';

					}


					$result['data'] = $data_results;
					$result['html'] = $table_text;


					echo json_encode($result);


				}

				// show an error if the query has an error
				else
				{
					print_message(2, 'could not get data');
				}




			}	// END OF user permission checks
			else
			{
				print_message(23, 'permissions error');
			}



			// Close db connection !
			$db = null;



	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		print_message(1, $e->getMessage());
	}




} else {
    // the user is not logged in. you can do whatever you want here.
	echo $mylang['ps not logged in message'];
}



?>
 
