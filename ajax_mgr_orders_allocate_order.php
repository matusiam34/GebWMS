<?php

/*

	Prefix	:	101

	A feature for the order manager page (gv_mgr_orders.php).
	
	Allows the guns to pick orders by allocating the order to some stock. The page where this all happens shows the
	operator how much of stuff is available which allows them to make the right call.



	Steps to take:

	-	get the order number,
	-	grab all of the items / products that are on that order
	-	update the item / product allocated 

 
	All of this needs more work...

 
 

	Error code explained:

	666:	try could not be established 
	100:	user access control permission error (most likely no rights to whatever they are accessing)
	101:	could not get data
	102:	Product query failed
	103:	Product not found
	104:	INSERT of the stock into location failed BAD!
	105:	stock UPDATE of the location failed BAD!

	108:	Location blocked (or just not found?)

*/


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


	$message2op		=	'';		// a little message that will be provided to the operator with errors, success etc
	$message_id		=	101999;	// error / success code. 999 is the default as in EVERYTHING went sideways. 0 = success 
	$html_results	=	'';		// all HTML output will be populated into this variable before it is sent back.

	try
	{


		// load the supporting functions....
		require_once('lib_functions.php');
		require_once('lib_db_conn.php');


		//	Do your stuff here!
		//	Just before the first query?
		$db->beginTransaction();



		//	Check if user has the right access level
		if (is_it_enabled($_SESSION['menu_mgr_orders']))
		{


			//
			// Data I receive to process the request
			//


			//	Warehouse code set for the operator is in the session. Can be changed by the admin in the USERS tab
			$user_warehouse_uid		=	leave_numbers_only($_SESSION['user_warehouse']);




			//	Since this allocates the entire order... All is required is the Order Number
			$order_number		=	'';
			
			if (isset($_POST['ordnum_js']))
			{
				$order_number		=	trim($_POST['ordnum_js']);
			}

			//	Maybe verify that something has been provided?




			if ($user_warehouse_uid < 1)
			{
				$message2op		=	'Warehouse is beyond scope';
				$message_id		=	101221;
			}
			else
			{

				//	store all product details here!
				$order_products_arr	=	array();
				$order_uid			=	0;	//	Will be populated by the first SQL



				//	First things first... Grab all items from the order and later on adjust the phy_qty , alloc_qty & free_qty of each
				//	product in the geb_product table with the corresponding warehouse code!


		$sql	=	'

					SELECT

					geb_order_header.ordhdr_uid,
					geb_order_details.orddet_prod_pkey,
					geb_order_details.orddet_ord_qty

					FROM 

					geb_order_header

					INNER JOIN geb_order_details ON geb_order_header.ordhdr_order_number = geb_order_details.orddet_ordhdr_ordnum

					WHERE

					geb_order_header.ordhdr_order_number = :sordernumber

					and

					geb_order_header.ordhdr_warehouse_uid = :swarehouse


		';


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':sordernumber',	$order_number,			PDO::PARAM_STR);
			$stmt->bindValue(':swarehouse',		$user_warehouse_uid,	PDO::PARAM_INT);
			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

				$order_uid				=	$row['ordhdr_uid'];
				$order_products_arr[]	=	$row;

			}



			//	If there is something in $order_products_arr
			if (count($order_products_arr) > 0)
			{
				
				//	Update the geb_product rows related to the products!
				//	The thing here is to write a history file that links to the order number (in the perfect world).
				//	However, the most important part is to update the phy_qty, free_qty and alloc_qty!
				//	Also make sure to change the status code from "On Hold" to "Ready" so that the order can be picked!
				//	NOTE:	Maybe the last bit needs to be figured out by the script as I can configure Geb to always
				//			allocate orders on import... Something to keep in mind!


				if ($stmt = $db->prepare('


					UPDATE

					geb_product

					SET

					prod_alloc_qty		=	prod_alloc_qty + :uord_qty,
					prod_free_qty		=	prod_free_qty - :uord_qtyf

					WHERE

					prod_pkey	 =	:uprod_pkey


				'))


				{


					foreach ($order_products_arr as $product_arr)
					{
						$stmt->bindValue(':uord_qty',	$product_arr['orddet_ord_qty'],		PDO::PARAM_INT);
						$stmt->bindValue(':uord_qtyf',	$product_arr['orddet_ord_qty'],		PDO::PARAM_INT);
						$stmt->bindValue(':uprod_pkey',	$product_arr['orddet_prod_pkey'],	PDO::PARAM_INT);
						$stmt->execute();
					}


					//	Now add these entries into the history...??!?!
					//	This time an INSERT!

					if ($stmt = $db->prepare('


					INSERT
					
					INTO
					
					geb_stock_allocation
					
					(
						stk_alloc_order_uid,
						stk_alloc_prod_pkey,
						stk_alloc_qty,
						stk_alloc_operator,
						stk_alloc_date
					) 

					VALUES

					(
						:istk_alloc_order_uid,
						:istk_alloc_prod_pkey,
						:istk_alloc_qty,
						:istk_alloc_operator,
						:istk_alloc_date
					)


					'))
					{


						//	Bit of a change how I deal with the date... Create a variable here and use it for everything!
						//	Been working on the stock allocation page and it is much better to look at the same time figures.
						//	This is just a cosmetic thing but I find it a odd.
						$date_now	=	date('Y-m-d H:i:s');


						foreach ($order_products_arr as $product)
						{
							$stmt->bindValue(':istk_alloc_order_uid',		$order_uid,										PDO::PARAM_INT);
							$stmt->bindValue(':istk_alloc_prod_pkey',		$product['orddet_prod_pkey'],					PDO::PARAM_INT);
							$stmt->bindValue(':istk_alloc_qty',				$product['orddet_ord_qty'],						PDO::PARAM_INT);
							$stmt->bindValue(':istk_alloc_operator',		leave_numbers_only($_SESSION['user_id']),		PDO::PARAM_INT);
							$stmt->bindValue(':istk_alloc_date',			$date_now,										PDO::PARAM_STR);
							$stmt->execute();
						}


						//	lib_function.php has a function to update the order status... Do I go down this path?! Not sure which way to go!
						//	For now the usual!

						if ($stmt = $db->prepare('


							UPDATE

							geb_order_header

							SET

							ordhdr_status	=	:ustatus

							WHERE

							ordhdr_uid	 	=	:uorder_uid


						'))


						{

							//	New Status: Ready!
							//	Means that it will now show on the gun for the pickers to get busy with!
							$stmt->bindValue(':ustatus',		$order_status_reverse_arr['R'],		PDO::PARAM_INT);
							$stmt->bindValue(':uorder_uid',		$order_uid,							PDO::PARAM_INT);
							$stmt->execute();

							// make sure to commit all of the changes to the DATABASE !
							$db->commit();

							$message2op	=	'';	// When everything runs smooth there is no need for an error msg!
							$message_id	=	0;	// Seems like everything went well!

						}
						//	Error: Can't update the order header to NOT be on Hold!
						else
						{
						}


					}
					// show an error if the query has an error
					else
					{
						//echo	'Error: x31232';
					}



				}
				// show an error if the query has an error
				else
				{
				}


			}	//	items in orders > 0




		}
		else
		{
			//	Error: Could not even get the order details!
		}





			}




		}	// END OF user permission checks
		else
		{
			$message2op		=	'Permissions error';
			$message_id		=	101100;
		}






	}		// try
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	101666;
	}


	// Close db connection !
	$db = null;

	// return the findings
	print_message_html_payload($message_id, $message2op, $html_results);



} else {
    // the user is not logged in. Show them the login page.
    include('not_logged_in.php');
}



?>
 
