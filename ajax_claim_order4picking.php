<?php

//	Menu:		Pick Order
//	Action:		Operator commits to complete the selected order.
//
//	To accomplish these the order status and the operator code need to be updated in the geb_order_header table


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



		//	Check if user has the right access level
		if 
		(

			(is_it_enabled($_SESSION['menu_pick_order']))

		)


		{


			$order_uid	=	leave_numbers_only($_POST['order_uid_js']);		// remove anything that is not a number


			if ($order_uid > 0)
			{


				$db->beginTransaction();


				if ($stmt = $db->prepare('


					UPDATE

					geb_order_header

					SET

					ordhdr_status				=		:uorder_status,
					ordhdr_pick_operator		=		:uoperator_uid,
					ordhdr_pick_start_date		=		:upick_start_date

					WHERE

					ordhdr_uid	 =	:uorder_uid

				'))
				{


					$stmt->bindValue(':uorder_status',		$order_status_reverse_arr['S'],					PDO::PARAM_INT);
					$stmt->bindValue(':uoperator_uid',		leave_numbers_only($_SESSION['user_id']),		PDO::PARAM_INT);
					$stmt->bindValue(':upick_start_date',	date('Y-m-d H:i:s'),							PDO::PARAM_STR);

					$stmt->bindValue(':uorder_uid',			$order_uid,		PDO::PARAM_INT);


					$stmt->execute();

					// make sure to commit all of the changes to the DATABASE !
					$db->commit();

					// dummy message... Just to keep the script happy ? Do not show anything to the use tho !
					print_message(0, 'a-OK');

				}

				// show an error if the query has an error
				else
				{
					print_message(2, 'could not update order');
				}


				}
				// check if the location is > 0... If not ===>>>> throw an error. Can be done better I am sure!
				else
				{
					print_message(2, 'Order ID incorrect');
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
		$db->rollBack();
		print_message(1, $e->getMessage());
	}




} else {
    // the user is not logged in. you can do whatever you want here.
	echo $mylang['ps not logged in message'];
}



?>
 
