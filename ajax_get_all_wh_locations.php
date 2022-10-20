<?php



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
if ($login->isUserLoggedIn() == true) {

    // the user is logged in. Perform the sql query !


	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');


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


				if ($stmt = $db->prepare('

					SELECT

					geb_warehouse.wh_code,
					geb_location.loc_pkey,
					geb_location.loc_code,

					geb_location.loc_barcode,
					geb_location.loc_type,
					geb_location.loc_blocked,
					geb_location.loc_note


					FROM  geb_location

					INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


					WHERE

					geb_location.loc_disabled = 0 AND geb_warehouse.wh_disabled = 0


					ORDER BY wh_code, loc_code

				'))
				{



					$stmt->execute();


					// My result array before encoded via json !
					$result = array();
					$result['control'] = 0;		// 0 means all went well !!!

					/* fetch values */
					$data_results	=	array();
					$table_text		=	'';


					$table_text		.=	'<tr>';

					$table_text		.=		'<th>UID</th>';
					$table_text		.=		'<th>Warehouse</th>';
					$table_text		.=		'<th>Location</th>';
					$table_text		.=		'<th>Barcode</th>';
					$table_text		.=		'<th>Type</th>';
					$table_text		.=		'<th>Blocked</th>';
					$table_text		.=		'<th>Note</th>';

					$table_text		.=	'</tr>';


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{


						$table_text		.=		'<tr>';
						$table_text		.=			'<td>'	.	trim($row['loc_pkey'])		.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['wh_code'])		.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['loc_code'])	.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['loc_barcode'])	.	'</td>';



						// Convert the type of location type info into meaninful text.
						$type_row		=	leave_numbers_only($row['loc_type']);
						$table_text		.=			'<td>'	.	$loc_types_arr[$type_row]	.	'</td>';



						// Convert the loc blocked to more meaningful text for the operator.
						$blocked_str	=	'';
						$blocked_row	=	leave_numbers_only($row['loc_blocked']);

						if ($blocked_row == 0)
						{
							$blocked_str	=	'N';
						}
						elseif ($blocked_row == 1)
						{
							$blocked_str	=	'Y';
						}


						$table_text		.=			'<td>'	.	$blocked_str		.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['loc_note'])		.	'</td>';
						$table_text		.=		'</tr>';



/*

						$table_text		.=		'<tr>';
						$table_text		.=			'<td>'	.	trim($row['warehouse_alias_name'])		.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['warehouse_location_name'])	.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['warehouse_location_barcode'])	.	'</td>';


						$type_row		=	trim($row['warehouse_location_type']);
						$type_str		=	'None';

						if ($type_row == 0)
						{
							$type_str	=	'Single';
						}
						elseif ($type_row == 1)
						{
							$type_str	=	'Multi';
						}

						$table_text		.=			'<td>'	.	$type_str	.	'</td>';

						$mixed_str	=	'';
						$mixed_row	=	trim($row['warehouse_location_mixed']);

						if ($mixed_row == 0)
						{
							$mixed_str	=	'No';
						}
						elseif ($mixed_row == 1)
						{
							$mixed_str	=	'Yes';
						}

						$table_text		.=			'<td>'	.	$mixed_str	.	'</td>';

						$blocked_str	=	'';
						$blocked_row	=	trim($row['warehouse_location_blocked']);

						if ($blocked_row == 0)
						{
							$blocked_str	=	'N';
						}
						elseif ($blocked_row == 1)
						{
							$blocked_str	=	'Y';
						}

						$table_text		.=			'<td>'	.	$blocked_str	.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['warehouse_location_desc'])	.	'</td>';
						$table_text		.=		'</tr>';
*/


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
 
