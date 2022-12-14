<?php


//	Add more checks to the input data! Leave_numbers and trim things.
//	Now, when looking for duplicates makes sure that they are not in the same WH as the user might wants to have same location in two different WHs!!


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


    // the user is logged in.

	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');


		//	Check if user has the right access level
		if 
		(

			(is_it_enabled($_SESSION['menu_adm_warehouse_loc']))

			AND

			(can_user_add($_SESSION['menu_adm_warehouse_loc']))

		)
		{


			// Data from the user to process...
			$warehouse		=	leave_numbers_only($_POST['warehouse_js']);
			$location		=	trim($_POST['location_js']);
			$barcode		=	trim($_POST['barcode_js']);
			$type			=	leave_numbers_only($_POST['type_js']);
			$pickface		=	leave_numbers_only($_POST['pickface_js']);
			$blocked		=	leave_numbers_only($_POST['blocked_js']);
			$loc_desc		=	trim($_POST['loc_desc_js']);



			// These checks need more work but for now will do... prototype after all! 
			if ($warehouse	==	0)	{
				print_message(3, 'Select warehouse');
			}
			elseif (strlen($location)	<	2)	{
				print_message(3, 'Location code too short');
			}
			elseif (strlen($barcode)	<	2)	{
				print_message(3, 'Barcode too short');
			}
			else
			{

				$db->beginTransaction();


		$found_a_match	=	false;

		//
		// Seek out for duplicate entry! Barcode check later or now?
		//
		$sql	=	'


			SELECT

			*

			FROM  geb_location

			WHERE

			(

				(
					loc_code = :lname

					OR

					loc_barcode = :lbarcode
				)

				AND

				loc_wh_pkey = :lwarehouse

			)

			OR

			(

				loc_barcode = :lbarcode

			)

			AND

			loc_disabled = 0

		';


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':lname',			$location,		PDO::PARAM_STR);
			$stmt->bindValue(':lbarcode',		$barcode,		PDO::PARAM_STR);
			$stmt->bindValue(':lwarehouse',		$warehouse,		PDO::PARAM_INT);

			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$found_a_match	=	true;
			}

		}
		// show an error if the query has an error
		else
		{
		}



				if (!$found_a_match)
				{

					// When nothing found === please insert the brand new location into the system!

					if ($stmt = $db->prepare('


					INSERT

					INTO

					geb_location

					(
						loc_wh_pkey,
						loc_code,
						loc_barcode,
						loc_type,
						loc_pickface,
						loc_blocked,
						loc_note
					) 

					VALUES

					(
						:iloc_wh_pkey,
						:iloc_code,
						:iloc_barcode,
						:iloc_type,
						:iloc_pickface,
						:iloc_blocked,
						:iloc_note
					)


					'))


					{


						$stmt->bindValue(':iloc_wh_pkey',		$warehouse,		PDO::PARAM_INT);
						$stmt->bindValue(':iloc_code',			$location,		PDO::PARAM_STR);
						$stmt->bindValue(':iloc_barcode',		$barcode,		PDO::PARAM_STR);
						$stmt->bindValue(':iloc_type',			$type,			PDO::PARAM_INT);
						$stmt->bindValue(':iloc_pickface',		$pickface,		PDO::PARAM_INT);
						$stmt->bindValue(':iloc_blocked',		$blocked,		PDO::PARAM_INT);
						$stmt->bindValue(':iloc_note',			$loc_desc,		PDO::PARAM_STR);
						$stmt->execute();

						// make sure to commit all of the changes to the DATABASE !
						$db->commit();
						// dummy message... Just to keep the script happy ? Do not show anything to the use tho !
						print_message(0, 'a-OK');


					}
					// show an error if the query has an error
					else
					{
						print_message(2, 'Error: x10002');
					}

				}
				else
				{
					print_message(3, 'Entry already exists!');
				}


			}





		}	// END OF if ( ($user_settings >= 3)  AND  ($user_priv == max_priv) )
		else
		{
			print_message(23, 'issue with user permissions');
		}





	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		$db->rollBack();
		print_message(1, $e->getMessage());
	}



} else {
    // the user is not logged in. you can do whatever you want here.
	echo 'ps not logged in message';
}



?>
