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


    // the user is logged in.

	try
	{


		// load the supporting functions....
		require_once("lib_functions.php");
		require_once("lib_db_conn.php");


		// Need to figure out some admin editing access control thing for this.....
		if 
		(

			(

				(can_user_access($_SESSION['user_inventory']))

			)

			AND

			(leave_numbers_only($_SESSION['user_priv']) >=	manager_priv)

		)
		{



			$product_code			= trim($_POST['product_code_js']);
			$product_description 	= trim($_POST['product_description_js']);
			$product_category		= trim($_POST['product_category_js']);
			$each_barcode			= trim($_POST['each_barcode_js']);
			$each_weight			= trim($_POST['each_weight_js']);
			$case_barcode			= trim($_POST['case_barcode_js']);
			$case_qty				= leave_numbers_only($_POST['case_qty_js']);	// should be a number....?
			$pall_qty				= leave_numbers_only($_POST['pall_qty_js']);	// should be a number....?
			$disabled_or_not		= leave_numbers_only($_POST['disabled_js']);	// should be a number....?


			if (strlen($product_code)	<	2)	{
				print_message(3, "Product name is short");
			}
			else
			{

				$db->beginTransaction();


		$found_a_match	=	false;






		//
		// Seek out for duplicate entry !
		//
		$sql	=	"

			SELECT

			*

			FROM geb_product

			WHERE

			prod_code = :iprod_name

		";


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':iprod_name',		$product_code,		PDO::PARAM_STR);
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



					if ($stmt = $db->prepare("


					INSERT
					
					INTO
					
					geb_product
					
					(
						prod_code,
						prod_desc,
						prod_category,
						prod_each_barcode,
						prod_each_weight,
						prod_case_barcode,
						prod_case_qty,
						prod_pall_qty,
						prod_disabled
					) 

					VALUES

					(
						:iprod_code,
						:iprod_desc,
						:iprod_category,
						:iprod_each_barcode,
						:iprod_each_weight,
						:iprod_case_barcode,
						:iprod_case_qty,
						:iprod_pall_qty,
						:iprod_disabled
					)

					"))


					{

						$stmt->bindValue(':iprod_code',				$product_code,				PDO::PARAM_STR);
						$stmt->bindValue(':iprod_desc',				$product_description,		PDO::PARAM_STR);
						$stmt->bindValue(':iprod_category',			$product_category,			PDO::PARAM_STR);
						$stmt->bindValue(':iprod_each_barcode',		$each_barcode,				PDO::PARAM_STR);
						$stmt->bindValue(':iprod_each_weight',		$each_weight,				PDO::PARAM_STR);
						$stmt->bindValue(':iprod_case_barcode',		$case_barcode,				PDO::PARAM_STR);
						$stmt->bindValue(':iprod_case_qty',			$case_qty,					PDO::PARAM_INT);
						$stmt->bindValue(':iprod_pall_qty',			$pall_qty,					PDO::PARAM_INT);
						$stmt->bindValue(':iprod_disabled',			$disabled_or_not,			PDO::PARAM_INT);


						$stmt->execute();

						// make sure to commit all of the changes to the DATABASE !
						$db->commit();
						// dummy message... Just to keep the script happy ? Do not show anything to the user tho !
						print_message(0, "a-OK");

					}
					// show an error if the query has an error
					else
					{
						print_message(2, "Error" . ": x10002");
					}

				}
				else
				{
					print_message(3, "Entry already exists!");
				}


			}




		}	// END OF user priv check
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
