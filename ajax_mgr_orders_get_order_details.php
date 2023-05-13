<?php


//	Used for the mgr_orders page... Will grab the product codes and order qty... maybe add picked qty once the order has started to be picked or something?!


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

			(is_it_enabled($_SESSION['menu_mgr_orders']))

		)
		{


			//	To get this to work I need the order UID or Order Number?
			$order_number		=	'';
			
			if (isset($_POST['ordnum_js']))
			{
				$order_number		=	trim($_POST['ordnum_js']);
			}

			$items_ordered		=	'';




				if ($stmt = $db->prepare('

					SELECT

					geb_order_details.orddet_ord_qty,
					geb_order_details.orddet_pk_qty,
					geb_product.prod_code,
					geb_product.prod_phy_qty,
					geb_product.prod_alloc_qty

					FROM 

					geb_order_details
					
					INNER JOIN geb_product ON geb_order_details.orddet_prod_pkey = geb_product.prod_pkey

					WHERE

					orddet_ordhdr_ordnum = :sorder_number

					ORDER BY prod_code

				'))
				{

					$stmt->bindValue(':sorder_number',	$order_number,	PDO::PARAM_STR);
					$stmt->execute();

					// My result array before encoded via json !
					$result = array();
					$result['control'] = 0;		// 0 means all went well !!!

					/* fetch values */
//					$data_results	=	array();
//					$table_text		=	"";




				$items_ordered	.=	'<div class="columns">

										<div class="column is-12">';


					$items_ordered	.=	'<table class="is-fullwidth table is-bordered">';
					$items_ordered	.=	'<tr>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['product'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['physical_qty'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['allocated_qty'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['free_qty'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['ordered'] . '</th>';
					$items_ordered	.=	'<th style="background-color: ' . $backclrA . ';">' . $mylang['picked'] . '</th>';
					$items_ordered	.=	'</tr>';



					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{


						//	Important feature right here!
						//	If the user does not have access to the product search than do not
						//	provide the links for it here! Logic! :P
						$product_details_lnk	=	trim($row['prod_code']);

						if (is_it_enabled($_SESSION['menu_prod_search']))
						{
							// Create a clickable link so that the operator can investigate the product in more detail (if required & allowed)
							$product_details_lnk	=	'<a href="gv_search_product.php?product=' . trim($row['prod_code']) . '">' . trim($row['prod_code']) . '</a>';
						}



						$items_ordered	.=	'<tr>';
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . $product_details_lnk . '</td>';

						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_phy_qty']) . '</td>';
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['prod_alloc_qty']) . '</td>';

						//	Free stock	=	Free - Allocated Stock
						$free_stock		=	(trim($row['prod_phy_qty']) - trim($row['prod_alloc_qty']));
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . $free_stock . '</td>';



						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['orddet_ord_qty']) . '</td>';
						$items_ordered	.=	'<td style="background-color: ' . $backclrB . ';">' . trim($row['orddet_pk_qty']) . '</td>';
						$items_ordered	.=	'</tr>';


/*
						// Wrap the values into an array for json encoding !
						$row_result = array
						(
								'user_id'		=> trim($row['user_id']),
								'user_name'		=> trim($row['user_name'])
						);

						$data_results[] = $row_result;


						$table_text		.=		'<tr>';
						$table_text		.=			'<td>'	.	trim($row['user_id'])	.	'</td>';
						$table_text		.=			'<td>'	.	trim($row['user_name'])	.	'</td>';
						$table_text		.=		'</tr>';
*/

					}


					$items_ordered	.=	'</table>';



				$items_ordered	.=	'</div>
						</div>';




					//	Add another section with a button or two...
					$items_ordered	.=	'<div class="columns">

										<div class="column is-3">

											<div class="field" style="'. $box_size_str .'">
												<div class="control">
													<button class="button manager_class is-fullwidth" onclick="allocate_order();">Allocate</button>
												</div>
											</div>

										</div>

										</div>
						';



/*
					//	Add another section with a button or two...
					$items_ordered	.=	'<div class="columns">

										<div class="column is-3">
										</div>

										</div>
						';

*/

					//$result['data'] = $data_results;
					$result['html'] = $items_ordered;


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
 
