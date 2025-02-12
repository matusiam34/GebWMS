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
		define('ERROR_EACH_BARCODE_SHORT', 2);
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
			elseif (strlen($prod_arr['sku_barcode']) < min_sku_barcode_len)	//	each barcode lenght does not meet requirements!
			{
				//	Each barcode has to be at least $min_each_barcode_len characters long
				$input_checks	=	ERROR_EACH_BARCODE_SHORT;
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


/*

"wms_prodsku" (
	"prodsku_pkey"	INTEGER,
	"prodsku_prodm_pkey"	INTEGER DEFAULT 0,
	"prodsku_owner"	INTEGER DEFAULT 0,
	"prodsku_code"	TEXT,
	"prodsku_group"	TEXT,
	"prodsku_desc"	TEXT,
	"prodsku_barcode"	TEXT,
	"prodsku_pu_pkey"	INTEGER DEFAULT 0,
	"prodsku_category_a"	INTEGER DEFAULT 0,
	"prodsku_category_b"	INTEGER DEFAULT 0,
	"prodsku_category_c"	INTEGER DEFAULT 0,
	"prodsku_category_d"	INTEGER DEFAULT 0,
	"prodsku_disabled"	INTEGER DEFAULT 0,

*/


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
							elseif	(strcmp(trim($row['prodsku_barcode']), $sku_arr['sku_code']) === 0)
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
									:iprodsku_category_a,
									:iprodsku_category_b,
									:iprodsku_category_c,
									:iprodsku_category_d,
									:iprodsku_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{


							$stmt->bindValue(':iprodsku_owner',			$user_company_uid,							PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_code',			$sku_arr['sku_code'],				PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_group',			$sku_arr['sku_group_code'],		PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_desc',			$sku_arr['sku_description'],		PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_barcode',		$sku_arr['sku_barcode'],		PDO::PARAM_STR);
							$stmt->bindValue(':iprodsku_pu_pkey',		$sku_arr['sku_package_unit'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_a',		$sku_arr['sku_category_a'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_b',		$sku_arr['sku_category_b'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_c',		$sku_arr['sku_category_c'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_category_d',		$sku_arr['sku_category_d'],			PDO::PARAM_INT);
							$stmt->bindValue(':iprodsku_disabled',			$sku_arr['disabled'],					PDO::PARAM_INT);
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
						elseif ($found_match == 3)
						{
							$message_id		=	106202;
							$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['barcode_already_exists'];
						}
						elseif ($found_match == 4)
						{
							$message_id		=	106203;
							$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['barcode_already_exists'];
						}
						elseif ($found_match == 5)
						{
							$message_id		=	106204;
							$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['barcode_already_exists'];
						}

					}




				}
				else
				{
					//	Input checks have failed... Provide all the required messages so that the operator can fix them!

					if ($input_checks	==	1)
					{
						$message_id		=	106205;
						$message2op		=	$mylang['name_too_short'];
					}
					elseif ($input_checks	==	2)
					{
						$message_id		=	106206;
						$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['barcode_too_short'];
					}
					elseif ($input_checks	==	3)
					{
						$message_id		=	106207;
						$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['invalid_barcode'];
					}
					elseif ($input_checks	==	4)
					{
						$message_id		=	106208;
						$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['invalid_barcode'];
					}
					elseif ($input_checks	==	5)
					{
						$message_id		=	106209;
						$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['barcode_too_short'];
					}
					elseif ($input_checks	==	6)
					{
						$message_id		=	106210;
						$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['incorrect_qty'];
					}
					elseif ($input_checks	==	7)
					{
						$message_id		=	106210;
						$message2op		=	$mylang['identical_barcodes'];
					}


				}



			}


		}	//	Action 2 end!


		//	Update one product!

		else if ($action_code == 1)
		{

			//	Only an Admin of this system can update a product!
			if
			(

				(is_it_enabled($_SESSION['menu_mgr_products']))

				AND

				(can_user_update($_SESSION['menu_mgr_products']))

			)
			{

				// Data from the user to process...
				$product_uid			=	leave_numbers_only($_POST['product_uid_js']);	//	this should be a number
				$product_code			=	trim($_POST['product_code_js']);	//	this should be text
				$product_description	=	trim($_POST['product_description_js']);	//	this should be text
				$product_category_a		=	leave_numbers_only($_POST['product_category_a_js']);	//	this should be a number
				$product_category_b		=	leave_numbers_only($_POST['product_category_b_js']);	//	this should be a number
				$product_category_c		=	leave_numbers_only($_POST['product_category_c_js']);	//	this should be a number
				$product_category_d		=	leave_numbers_only($_POST['product_category_d_js']);	//	this should be a number
				$each_barcode			=	trim($_POST['each_barcode_js']);	//	this should be text
				$each_weight			=	trim($_POST['each_weight_js']);	//	this should be text
				$case_barcode			=	trim($_POST['case_barcode_js']);	//	this should be text
				$case_qty				=	leave_numbers_only($_POST['case_qty_js']);	//	this should be a number
				$min_qty				=	leave_numbers_only($_POST['min_qty_js']);	//	this should be a number
				$max_qty				=	leave_numbers_only($_POST['max_qty_js']);	//	this should be a number
				$disabled				=	leave_numbers_only($_POST['disabled_js']);	//	this should be a number


				$sku_arr = array
				(
					'sku_code' 				=> trim($_POST['sku_code_js']),
					'sku_group_code' 		=> trim($_POST['sku_group_code_js']),
					'sku_description'		=> trim($_POST['sku_description_js']),
					'sku_barcode'			=> trim($_POST['sku_barcode_js']),
					'sku_package_unit'		=> leave_numbers_only($_POST['sku_package_unit_js']),
					'sku_category_a'		=> leave_numbers_only($_POST['sku_category_a_js']),
					'sku_category_b'		=> leave_numbers_only($_POST['sku_category_b_js']),
					'sku_category_c'		=> leave_numbers_only($_POST['sku_category_c_js']),
					'sku_category_d'		=> leave_numbers_only($_POST['sku_category_d_js']),
					'disabled'				=> leave_numbers_only($_POST['disabled_js'])
				);




				if ($product_uid >= 0)
				{


					//	Here figure out if the operator provided the correct data and the right mix of required fields.
					//	For example if you provide a case barcode but not the case Qty = we have a problem since the system
					//	will not be able to scan in any cases of that product.

					$input_checks	=	0;	//	0 means all good;	666 means BAD!

/*
					if (strlen($product_code) < min_product_len)
					{
						//	Product code too short
						$input_checks	=	1;
					}
					elseif (strlen($each_barcode) < min_each_barcode_len)	//	each barcode lenght does not meet requirements!
					{
						//	Each barcode has to be at least $min_each_barcode_len characters long
						$input_checks	=	2;
					}
					elseif (each_barcode_alphanumeric == 0)	//	Numbers only barcodes allowed! Defined in lib_system.php!
					{
						if (!is_numeric($each_barcode))	//	each barcode is not a string of numbers! ERR!!
						{
							//	Each barcode has to be a number!
							$input_checks	=	3;
						}
					}
*/






					if ($input_checks == 0)	//	All input checks have passed! Move towards further checks!
					{

						$db->beginTransaction();

						$found_match	=	0;	//	0 = all good!

						//
						//
						//
						//	!!!!!!!!!!!!!!!!!!!
						//
						//	See if there are duplicates!
						//
						//	!!!!!!!!!!!!!!!!!!!
						//
						//
						//
						$sql	=	'

							SELECT

							prod_pkey,
							prod_code,
							prod_each_barcode,
							prod_case_barcode

							FROM geb_product

							WHERE

							(

								(	(prod_pkey	<>	:sprod_pkey) AND (prod_code = :sprod_code)	)

									OR

									(

										(

											(prod_each_barcode = :sprod_each_barcode)

											OR

											(prod_case_barcode = :sprod_case_barcode)

											OR

											(prod_each_barcode = :sprod_case_barcode)

											OR

											(prod_case_barcode = :sprod_each_barcode)

										)

										AND

										(prod_pkey	<>	:sprod_pkey)

									)

							)

							AND
							
							prod_owner = :sprod_owner


						';



						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sprod_pkey',				$product_uid,		PDO::PARAM_INT);
							$stmt->bindValue(':sprod_code',				$product_code,		PDO::PARAM_STR);
							$stmt->bindValue(':sprod_each_barcode',		$each_barcode,		PDO::PARAM_STR);
							$stmt->bindValue(':sprod_case_barcode',		$case_barcode,		PDO::PARAM_STR);
							$stmt->bindValue(':sprod_owner',			$user_company_uid,	PDO::PARAM_INT);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{


								if (strcmp(trim($row['prod_code']), $product_code) === 0)
								{
									//	Found duplicate product code!
									$found_match	=	1;
								}
								else
								{

									//	Barcode related checks now!
									if	(strcmp(trim($row['prod_each_barcode']), $each_barcode) === 0)
									{
										//	Found duplicate each barcode!
										$found_match	=	2;
									}
									elseif	(strcmp(trim($row['prod_each_barcode']), $case_barcode) === 0)
									{
										//	Found duplicate case barcode in the each column!
										$found_match	=	3;
									}
									elseif (strlen($case_barcode) >= min_case_barcode_len)
									{
										if		(strcmp(trim($row['prod_case_barcode']), $each_barcode) === 0)
										{
											//	Found duplicate each barcode!
											$found_match	=	4;
										}
										elseif	(strcmp(trim($row['prod_case_barcode']), $case_barcode) === 0)
										{
											//	Found duplicate case barcode!
											$found_match	=	5;
										}

									}

								}


							}

						}
						// show an error if the query has an error?
						else
						{
							//	Just in case I ever end up here...
							$found_match	=	166;
						}



						//	0	means no issues!
						if ($found_match == 0)
						{


							$sql	=	'


									UPDATE

									geb_product

									SET

									prod_code			=	:uprod_code,
									prod_desc			=	:uprod_desc,
									prod_category_a		=	:uprod_category_a,
									prod_category_b		=	:uprod_category_b,
									prod_category_c		=	:uprod_category_c,
									prod_category_d		=	:uprod_category_d,
									prod_each_barcode	=	:uprod_each_barcode,
									prod_each_weight	=	:uprod_each_weight,
									prod_case_barcode	=	:uprod_case_barcode,
									prod_case_qty		=	:uprod_case_qty,
									prod_min_qty		=	:uprod_min_qty,
									prod_max_qty		=	:uprod_max_qty,
									prod_disabled		=	:uprod_disabled

									WHERE

									prod_pkey	 		=	:uprod_pkey


							';


							if ($stmt = $db->prepare($sql))
							{


								$stmt->bindValue(':uprod_code',				$product_code,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_desc',				$product_description,		PDO::PARAM_STR);
								$stmt->bindValue(':uprod_category_a',		$product_category_a,		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_b',		$product_category_b,		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_c',		$product_category_c,		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_category_d',		$product_category_d,		PDO::PARAM_INT);
								$stmt->bindValue(':uprod_each_barcode',		$each_barcode,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_each_weight',		$each_weight,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_case_barcode',		$case_barcode,				PDO::PARAM_STR);
								$stmt->bindValue(':uprod_case_qty',			$case_qty,					PDO::PARAM_INT);
								$stmt->bindValue(':uprod_min_qty',			$min_qty,					PDO::PARAM_INT);
								$stmt->bindValue(':uprod_max_qty',			$max_qty,					PDO::PARAM_INT);
								$stmt->bindValue(':uprod_disabled',			$disabled,					PDO::PARAM_INT);

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
								$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['barcode_already_exists'];
							}
							elseif ($found_match == 3)
							{
								$message_id		=	106213;
								$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['barcode_already_exists'];
							}
							elseif ($found_match == 4)
							{
								$message_id		=	106214;
								$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['barcode_already_exists'];
							}
							elseif ($found_match == 5)
							{
								$message_id		=	106215;
								$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['barcode_already_exists'];
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
							$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['barcode_too_short'];
						}
						elseif ($input_checks	==	3)
						{
							$message_id		=	106218;
							$message2op		=	'(' . $mylang['each'] . ') ' . $mylang['invalid_barcode'];
						}
						elseif ($input_checks	==	4)
						{
							$message_id		=	106219;
							$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['invalid_barcode'];
						}
						elseif ($input_checks	==	5)
						{
							$message_id		=	106220;
							$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['barcode_too_short'];
						}
						elseif ($input_checks	==	6)
						{
							$message_id		=	1062221;
							$message2op		=	'(' . $mylang['case'] . ') ' . $mylang['incorrect_qty'];
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
