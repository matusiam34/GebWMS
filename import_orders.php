<?php



//	This will be processing the order from an XML file(s)

//
//	LOTS of checks will have to implemented here! Critical part of the system if I am importing
//	orders (which are critical to be accurate) to the database to later on be picked!
//


$order_header_arr		=	array();	//	keep all header info about the order
$order_items_arr		=	array();	//	keep all order items here!
$order_items_only_arr	=	array();	//	store just product codes. Used for that one query to get the the product UID codes from DB.
$items_uid_arr			=	array();	//	store all product + UID codes here! 

$order_xml				=	simplexml_load_file('TS491643.xml');



$order_header_arr	=	array(

	'customer_code'		=>	trim($order_xml->header->customer_code),
	'order_number'		=>	trim($order_xml->header->order_number),
	'order_type'		=>	trim($order_xml->header->order_type),

	'billing_address1'		=>	trim($order_xml->header->billing_address1),
	'billing_address2'		=>	trim($order_xml->header->billing_address2),
	'billing_address3'		=>	trim($order_xml->header->billing_address3),
	'billing_address4'		=>	trim($order_xml->header->billing_address4),
	'billing_address5'		=>	trim($order_xml->header->billing_address5),
	'shipping_address1'		=>	trim($order_xml->header->shipping_address1),
	'shipping_address2'		=>	trim($order_xml->header->shipping_address2),
	'shipping_address3'		=>	trim($order_xml->header->shipping_address3),
	'shipping_address4'		=>	trim($order_xml->header->shipping_address4),
	'shipping_address5'		=>	trim($order_xml->header->shipping_address5),

);


//	Jam the items in!
for ($i = 0; $i < count($order_xml->items); $i++)
{

	$order_items_arr[]	=	array(

		'item_code'		=>	trim( $order_xml->items[$i]->item_code),
		'item_qty'		=>	trim( $order_xml->items[$i]->item_qty),

	);

	array_push($order_items_only_arr, trim($order_xml->items[$i]->item_code));

}


echo 'Order Header:';
echo '<br>';

print_r($order_header_arr);

echo '<br>';
echo '<br>';

echo 'Ordered items:';
echo '<br>';



print_r($order_items_arr);




echo '<br>';
echo '<br>';


	try
	{


		// needs a db connection...
		require_once('lib_functions.php');
		require_once('lib_db.php');
		require_once('lib_db_conn.php');
		$db->beginTransaction();


		//	by default nothing found!
		$found_duplicate	=	false;


		//
		//	Seek out for a duplicate entry !
		//	Do not want to import an order that already exists right?
		//
		$sql	=	'


			SELECT

			ordhdr_order_number

			FROM  geb_order_header

			WHERE

			ordhdr_order_number = :sorder_number


		';


		if ($stmt = $db->prepare($sql))
		{

			$stmt->bindValue(':sorder_number',	trim($order_xml->header->order_number),	PDO::PARAM_STR);
			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$found_duplicate	=	true;
			}

		}
		// show an error if the query has an error
		else
		{
		}


		if (!$found_duplicate)
		{
			echo 'Order not imported before = proceed!';


			//$items_str	=	implode(",", $order_items_only_arr);
			$items_str	=	"'" . implode("','", $order_items_only_arr) . "'";


			//	Before going any more forward... Get all products for this order from DB.

			//	I will be introducing more checks before doing any of the inserting... so this query
			//	will happen in one way or another. 

			$sql	=	'


				SELECT

				prod_pkey,
				prod_code 

				FROM geb_product

				WHERE

				prod_code IN (' . $items_str . ')

				AND

				prod_disabled = 0

			';


			if ($stmt = $db->prepare($sql))
			{

				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$items_uid_arr[$row['prod_code']]	=	leave_numbers_only($row['prod_pkey']);
				}

			}
			// show an error if the query has an error
			else
			{
			}



			if ($stmt = $db->prepare('


			INSERT
			
			INTO
			
			geb_order_header
			
			(
				ordhdr_type,
				ordhdr_enter_date,
				ordhdr_order_number,
				ordhdr_customer,

				ordhdr_bill_address1,
				ordhdr_bill_address2,
				ordhdr_bill_address3,
				ordhdr_bill_address4,
				ordhdr_bill_address5,
				
				ordhdr_ship_address1,
				ordhdr_ship_address2,
				ordhdr_ship_address3,
				ordhdr_ship_address4,
				ordhdr_ship_address5
			) 

			VALUES

			(
				:iordhdr_type,
				:iordhdr_enter_date,
				:iordhdr_order_number,
				:iordhdr_customer,

				:iordhdr_bill_address1,
				:iordhdr_bill_address2,
				:iordhdr_bill_address3,
				:iordhdr_bill_address4,
				:iordhdr_bill_address5,

				:iordhdr_ship_address1,
				:iordhdr_ship_address2,
				:iordhdr_ship_address3,
				:iordhdr_ship_address4,
				:iordhdr_ship_address5
			)


			'))


			{


				$stmt->bindValue(':iordhdr_type',				$order_header_arr['order_type'],			PDO::PARAM_INT);
				$stmt->bindValue(':iordhdr_enter_date',			date('Y-m-d H:s:i'),						PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_order_number',		$order_header_arr['order_number'],			PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_customer',			$order_header_arr['customer_code'],			PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_bill_address1',		$order_header_arr['billing_address1'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_bill_address2',		$order_header_arr['billing_address2'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_bill_address3',		$order_header_arr['billing_address3'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_bill_address4',		$order_header_arr['billing_address4'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_bill_address5',		$order_header_arr['billing_address5'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_ship_address1',		$order_header_arr['shipping_address1'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_ship_address2',		$order_header_arr['shipping_address2'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_ship_address3',		$order_header_arr['shipping_address3'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_ship_address4',		$order_header_arr['shipping_address4'],		PDO::PARAM_STR);
				$stmt->bindValue(':iordhdr_ship_address5',		$order_header_arr['shipping_address5'],		PDO::PARAM_STR);


				$stmt->execute();


				// If nothing has gone wrong here... add the items!
				if ($stmt = $db->prepare('


				INSERT
				
				INTO
				
				geb_order_details
				
				(
					orddet_ordhdr_ordnum,
					orddet_prod_pkey,
					orddet_ord_qty
				) 

				VALUES

				(
					:iorddet_ordhdr_ordnum,
					:iorddet_prod_pkey,
					:iorddet_ord_qty
				)


				'))


				{



					foreach ($order_items_arr as $product)
					{
						$stmt->bindValue(':iorddet_ordhdr_ordnum',			$order_header_arr['order_number'],			PDO::PARAM_STR);
						$stmt->bindValue(':iorddet_prod_pkey',				$items_uid_arr[$product['item_code']],		PDO::PARAM_INT);
						$stmt->bindValue(':iorddet_ord_qty',				$product['item_qty'],						PDO::PARAM_INT);
						$stmt->execute();
					}


					// make sure to commit all of the changes to the DATABASE !
					$db->commit();

					echo	'<br><br>Order imported!';

				}
				// show an error if the query has an error
				else
				{
					echo	'Error: x31232';
				}




				// make sure to commit all of the changes to the DATABASE !
				//$db->commit();


			}
			// show an error if the query has an error
			else
			{
				echo	'Error: x10002';
			}














		}
		else
		{
			echo 'Order already imported!';
		}






	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		$db->rollBack();
		echo 'Dead';
	}





?>
