<?php

//	NOTE TO SELF:	The insert and update action are nearly identical! Merge them into one as that will save on making changes to the checks!
//					as I will not have to remember to apply them to the second or first one (update / insert)

/*

	Error code for the script!
	Script code		=	106

	//	NOTE: Not complete by any means!

	//	Action code breakdown
	0	:	Add Product
	1	:	Update Product details


	//	Old stuff here!
	2	:	Add product!
	3	:	Update product details!

*/



// load the login class
require_once('lib_login.php');


$message_id		=	106999;		//	999:	default bad
$message2op		=	'';			//	When an error happens provide a message here. Can be something positive as well like "All done", "a-Ok"
$html_results	=	'';			//	HTML code as output. Depending the Action Code this can be empty or a full HTML table.
$data_results	=	array();	//	array with all of the data collected

// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {


    // the user is logged in.

	try
	{


		// load the supporting functions....
		require_once('lib_system.php');
		require_once('lib_db_conn.php');



/*


		//	REMOVE ALL OF THIS WHEN EVERYTHING IS WORKING!

		//	*******************************************************************************************************
		//
		//	Ohhh... such an ugly solution right here! FIX
		//
		$company_uid_js		=	0;

		if (isset($_POST["company_uid_js"]))
		{
			$company_uid_js		=	leave_numbers_only($_POST['company_uid_js']);	// this should be a number
		}

		if (($user_company_uid == 0) AND ($company_uid_js > 0))
		{
			$user_company_uid	=	$company_uid_js;
		}

		//
		//	*******************************************************************************************************

*/



		//	Error codes defined... Good / bad?
		//	FIX and find another solution... at some point in the future! For now good enough!
		define('ERROR_SUCCESS', 0);
		define('ERROR_PRODUCT_CODE_SHORT', 1);
		define('ERROR_BARCODE_SHORT', 2);
		define('ERROR_EACH_BARCODE_NOT_NUMERIC', 3);
		define('ERROR_CASE_BARCODE_NOT_NUMERIC', 4);
		define('ERROR_CASE_BARCODE_SHORT', 5);
		define('ERROR_CASE_QTY_INVALID', 6);
		define('ERROR_BARCODES_IDENTICAL', 7);



		//	Validate product input from the operator! Used by Add and Update!
		//	This needs further work as I need to create more rules!
		function validate_product_data($prod_arr)
		{


			$input_checks	=	0;	//	0 means all good;	666 means BAD!

			if (strlen($prod_arr['sku_code']) < min_sku_len)
			{
				//	Product code too short
				$input_checks	=	ERROR_PRODUCT_CODE_SHORT;
			}
			elseif (strlen($prod_arr['sku_barcode']) < min_sku_barcode_len)	//	barcode lenght does not meet requirements!
			{
				//	Each barcode has to be at least $min_each_barcode_len characters long
				$input_checks	=	ERROR_BARCODE_SHORT;
			}

/*
			elseif (each_barcode_alphanumeric == 0)	//	Numbers only barcodes allowed! Defined in lib_system.php!
			{
				if (!is_numeric($prod_arr['product_barcode']))	//	each barcode is not a string of numbers! ERR!!
				{
					//	Each barcode has to be a number!
					$input_checks	=	ERROR_EACH_BARCODE_NOT_NUMERIC;
				}
			}
*/

			return $input_checks;


		}


		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number


		//	Add product!
		if ($action_code == 0)
		{

			//	Only an Manager or Admin of this system can add! At least that is the idea...
			if
			(

				(
					is_it_enabled($_SESSION['menu_mgr_product_sku'])
				)

				AND

				(
					can_user_add($_SESSION['menu_mgr_product_sku'])
				)

			)
			{

				
				$sku_arr = array
				(
					'sku_code' 				=> trim($_POST['sku_code_js']),
					'sku_group_code' 		=> trim($_POST['sku_group_code_js']),
					'sku_description'		=> trim($_POST['sku_description_js']),
					'sku_barcode'			=> trim($_POST['sku_barcode_js']),
					'sku_package_unit'		=> leave_numbers_only($_POST['sku_package_unit_js']),
					'sku_base_uom'			=> leave_numbers_only($_POST['sku_base_uom_js']),
					'sku_category_a'		=> leave_numbers_only($_POST['sku_category_a_js']),
					'sku_category_b'		=> leave_numbers_only($_POST['sku_category_b_js']),
					'sku_category_c'		=> leave_numbers_only($_POST['sku_category_c_js']),
					'sku_category_d'		=> leave_numbers_only($_POST['sku_category_d_js']),
					'disabled'				=> leave_numbers_only($_POST['disabled_js'])
				);


				$input_checks	=	validate_product_data($sku_arr);


				if ($input_checks == 0)	//	All input checks have passed! Move towards further checks!
				{

					$db->beginTransaction();

					$found_match	=	0;	//	0 = all good!

					//
					//	See if there are duplicates!
					//
					$sql	=	'

						SELECT

						*

						FROM wms_prodsku

						WHERE

						(
						
							prodsku_code = :sSKU_code

							OR

							prodsku_barcode = :sSKU_barcode
						)

						AND

						prodsku_owner = :sowner


					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sSKU_code',			$sku_arr['sku_code'],			PDO::PARAM_STR);
						$stmt->bindValue(':sSKU_barcode',		$sku_arr['sku_barcode'],		PDO::PARAM_STR);
						$stmt->bindValue(':sowner',				$user_company_uid,				PDO::PARAM_INT);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{

							if (strcmp(trim($row['prodsku_code']), $sku_arr['sku_code']) === 0)
							{
								//	Found duplicate product code!
								$found_match	=	1;
							}
							elseif	(strcmp(trim($row['prodsku_barcode']), $sku_arr['sku_barcode']) === 0)
							{
								//	Found duplicate each barcode!
								$found_match	=	2;
							}

						}

					}
					// show an error if the query has an error?
					else
					{
						//	Just in case I end up here...
						$found_match	=	166;
					}



					//	0	means no issues!
					if ($found_match == 0)
					{



						$sql	=	'


								INSERT
								
								INTO

								wms_prodsku
								
								(
									prodsku_owner,
									prodsku_code,
									prodsku_group,
									prodsku_desc,
									prodsku_barcode,
									prodsku_pu_pkey,
									prodsku_base_uom_pkey,
									prodsku_category_a,
									prodsku_category_b,
									prodsku_category_c,
									prodsku_category_d,
									prodsku_disabled
								) 

								VALUES

								(
									:iprodsku_owner,
									:iprodsku_code,
									:iprodsku_group,
									:iprodsku_desc,
									:iprodsku_barcode,
									:iprodsku_pu_pkey,
									:iprodsku_base_uom_pkey,
									:iprodsku_category_a,
									:iprodsku_category_b,
									:iprodsku_category_c,
									:iprodsku_category_d,
									:iprodsku_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{


							$stmt->bindValue(':iprodsku_owner',				$user_company_uid,					PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_code',				$sku_arr['sku_code'],				PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_group',				$sku_arr['sku_group_code'],			PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_desc',				$sku_arr['sku_description'],		PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_barcode',			$sku_arr['sku_barcode'],			PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_pu_pkey',			$sku_arr['sku_package_unit'],		PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_base_uom_pkey',		$sku_arr['sku_base_uom'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_a',		$sku_arr['sku_category_a'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_b',		$sku_arr['sku_category_b'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_c',		$sku_arr['sku_category_c'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_d',		$sku_arr['sku_category_d'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_disabled',			$sku_arr['disabled'],				PDO::PARAM_INT);
							$stmt->execute();
							$db->commit();


							$message_id		=	0;	//	all went well
							$message2op		=	$mylang['success'];

						}


					}
					else
					{

						if ($found_match == 1)
						{
							$message_id		=	106200;
							$message2op		=	$mylang['product_already_exists'];
						}
						elseif ($found_match == 2)
						{
							$message_id		=	106201;
							$message2op		=	$mylang['barcode_already_exists'];
						}

					}




				}
				else
				{
					//	Old method of having cases and stuff... FIX
					//	Input checks have failed... Provide all the required messages so that the operator can fix them!

					if ($input_checks	==	1)
					{
						$message_id		=	106205;
						$message2op		=	$mylang['name_too_short'];
					}
					elseif ($input_checks	==	2)
					{
						$message_id		=	106206;
						$message2op		=	$mylang['barcode_too_short'];
					}
					//	FIX
					//	This is not implemented at all yet!
					elseif ($input_checks	==	3)
					{
						$message_id		=	106207;
						$message2op		=	$mylang['invalid_barcode'];
					}


				}



			}


		}	//	Action 2 end!


		//	Update one product!

		else if ($action_code == 1)
		{

			//	So far only someone who has the right to update
			//	Maybe make it for admin only as well in the future? FIX
			if
			(

				(is_it_enabled($_SESSION['menu_mgr_product_sku']))

				AND

				(can_user_update($_SESSION['menu_mgr_product_sku']))

			)
			{

				// Data from the user to process...
				$product_uid			=	leave_numbers_only($_POST['sku_uid_js']);	//	this should be a number

				//	Get all the relevant data into a nice single array! This will help do checks like
				//	input validation and other in the future.






				if ($product_uid > 0)
				{


					//	Here figure out if the operator provided the correct data and the right mix of required fields.
					//	For example if you provide a case barcode but not the case Qty = we have a problem since the system
					//	will not be able to scan in any cases of that product.


					$input_checks	=	0;			//	0 means all good;	666 means BAD!
					$dup_arr		=	array();	//	drop all results here when looking for duplicates!

					$sku_arr = array
					(
						'sku_code' 				=> trim($_POST['sku_code_js']),
						'sku_group_code' 		=> trim($_POST['sku_group_code_js']),
						'sku_description'		=> trim($_POST['sku_description_js']),
						'sku_barcode'			=> trim($_POST['sku_barcode_js']),
						'sku_package_unit'		=> leave_numbers_only($_POST['sku_package_unit_js']),
						'sku_base_uom'			=> leave_numbers_only($_POST['sku_base_uom_js']),
						'sku_category_a'		=> leave_numbers_only($_POST['sku_category_a_js']),
						'sku_category_b'		=> leave_numbers_only($_POST['sku_category_b_js']),
						'sku_category_c'		=> leave_numbers_only($_POST['sku_category_c_js']),
						'sku_category_d'		=> leave_numbers_only($_POST['sku_category_d_js']),
						'disabled'				=> leave_numbers_only($_POST['disabled_js'])
					);


					$input_checks	=	validate_product_data($sku_arr);



					if ($input_checks == 0)	//	All input checks have passed!
					{

						$db->beginTransaction();

						$found_match	=	0;	//	0 = all good!


						//
						//
						//
						//	xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
						//
						//	Check for the following duplicates
						//
						//	-	barcode
						//	-	SKU code
						//
						//	xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
						//
						//
						//


						$sql	=	'

							SELECT

							*

							FROM wms_prodsku

							WHERE

							(
							
								prodsku_code = :sSKU_code

								OR

								prodsku_barcode = :sSKU_barcode
							)

							AND

							prodsku_owner = :sowner

							AND
							
							prodsku_pkey NOT IN (:sSKU_UID)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sSKU_code',			$sku_arr['sku_code'],			PDO::PARAM_STR);
							$stmt->bindValue(':sSKU_barcode',		$sku_arr['sku_barcode'],		PDO::PARAM_STR);
							$stmt->bindValue(':sowner',				$user_company_uid,				PDO::PARAM_INT);
							$stmt->bindValue(':sSKU_UID',			$product_uid,					PDO::PARAM_INT);
							$stmt->execute();


							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								//	I am very lazy!
								$dup_arr[]	=	$row;
							}

						}
						// show an error if the query has an error? FIX
						else
						{
							//	Just in case I ever end up here...
							$found_match	=	166;
						}


						//	Here find if the array has any duplicate entries...
						if (count($dup_arr) > 0)
						{
							//	We already have a problem! There should be 0 entries in the $dup_arr!
							$found_match	=	5;

							//	Try and figure out what the exact problem is...
							foreach ($dup_arr as $item)
							{


								if (strcmp(trim($item['prodsku_code']), $sku_arr['sku_code']) === 0)
								{
									//	Found duplicate product code!
									$found_match	=	1;
								}
								elseif	(strcmp(trim($item['prodsku_barcode']), $sku_arr['sku_barcode']) === 0)
								{
									//	Found duplicate barcode!
									$found_match	=	2;
								}


							}

						}




						//	0	means no issues!
						if ($found_match == 0)
						{


							$sql	=	'


									UPDATE

									wms_prodsku

									SET

									prodsku_code				=	:uprod_code,
									prodsku_group				=	:uprod_group,
									prodsku_desc				=	:uprod_desc,
									prodsku_barcode				=	:uprod_barcode,
									prodsku_pu_pkey				=	:uprod_pu_pkey,
									prodsku_base_uom_pkey		=	:uprod_base_uom_pkey,
									prodsku_category_a			=	:uprod_category_a,
									prodsku_category_b			=	:uprod_category_b,
									prodsku_category_c			=	:uprod_category_c,
									prodsku_category_d			=	:uprod_category_d,
									prodsku_disabled			=	:uprod_disabled

									WHERE

									prodsku_pkey			=	:uprod_pkey


							';


							if ($stmt = $db->prepare($sql))
							{


								$stmt->bindValue(':uprod_code',				$sku_arr['sku_code'],			PDO::PARAM_STR);
								$stmt->bindValue(':uprod_group',			$sku_arr['sku_group_code'],		PDO::PARAM_STR);
								$stmt->bindValue(':uprod_desc',				$sku_arr['sku_description'],	PDO::PARAM_STR);
								$stmt->bindValue(':uprod_barcode',			$sku_arr['sku_barcode'],		PDO::PARAM_STR);
								$stmt->bindValue(':uprod_pu_pkey',			$sku_arr['sku_package_unit'],	PDO::PARAM_INT);
								$stmt->bindValue(':uprod_base_uom_pkey',	$sku_arr['sku_base_uom'],		PDO::PARAM_INT);

								$stmt->bindValue(':uprod_category_a',		$sku_arr['sku_category_a'],		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_b',		$sku_arr['sku_category_b'],		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_c',		$sku_arr['sku_category_c'],		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_d',		$sku_arr['sku_category_d'],		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_disabled',			$sku_arr['disabled'],			PDO::PARAM_INT);

								$stmt->bindValue(':uprod_pkey',				$product_uid,					PDO::PARAM_INT);

								$stmt->execute();
								$db->commit();

								$message_id		=	0;	//	all went well
								$message2op		=	$mylang['success'];
							}


						}
						else
						{

							if ($found_match == 1)
							{
								$message_id		=	106211;
								$message2op		=	$mylang['product_already_exists'];
							}
							elseif ($found_match == 2)
							{
								$message_id		=	106212;
								$message2op		=	$mylang['barcode_already_exists'];
							}


						}


					}
					else
					{
						//	Input checks have failed... Provide all the required messages so that the operator can fix them!

						if ($input_checks	==	1)
						{
							$message_id		=	106216;
							$message2op		=	$mylang['name_too_short'];
						}
						elseif ($input_checks	==	2)
						{
							$message_id		=	106217;
							$message2op		=	$mylang['barcode_too_short'];
						}


					}


				}
				else
				{
					$message_id		=	106222;
					$message2op		=	$mylang['incorrect_uid'];
				}


			}
			
		}	//	Action 1 end!







	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	106666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Add product
		print_message($message_id, $message2op);
		break;
		case 1:	//	Update product
		print_message($message_id, $message2op);
		break;
		default:
		print_message(106945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
