<?php

/*

		A bunch of support stuff to aid the WMS "framework" !
		Also many config items to make it yours :)

 */



//	20/05/2023:	


//	Set the language here.
//	This has been updated (19 Nov 2022) to allow the operator to set the language in the My Account page :)
//	To add your own language just copy one of the existing php files (lang folder) and start translating.
//	To make it easy I would go with English as your template/
//	The only issue this introduces is the fact that the login page can't be translated since I am not sure who is using 
//	it. So either some generic images or use English there as default.


$set_language	=	trim($_SESSION['user_language']);
require_once('lang/' . $set_language . '.php');


//	NOTE: Orders
//
//	geb_order_header contains an ordhdr_warehouse_uid column which is set to 0 by default in the sqlite!
//	0 can't be a warehouse hence there can ba a page where it will tell the administrator
//	that orders have arrived with 



//	If you have added a new transation to the lang folder ==>>> please update this array,
//	This will allow the new entry to be shown to the user.
$supported_languages_arr	=	array('English', 'Polski');


$date_display_style	=	0;		//	Default is GebWMS style = 	25/11/2022 at 18:04:33
								//	No other styles currently but... it will be easy to add here and manipulate!
								//	display_date is the one to adjust to reflect the changes here :)



//	Some GebWMS settings...


//	Global settings for the min lenght of fields!
define('min_product_len', 4);
define('min_each_barcode_len', 4);
define('min_case_barcode_len', 4);

//	What kind of barcodes are alloed to be allocated to products!
define('each_barcode_alphanumeric', 0);		//	0:	Each barcode is numbers only!	1:	Alphanumeric:	BAR57072 allowed!
define('case_barcode_alphanumeric', 0);		//	0:	Case barcode is numbers only!	1:	Alphanumeric:	BAR57072 allowed!




//	Cancelled for now
//	Order Header: Order Type!
//	Since I want to support different types of orders:
//	- a typical order that will come from a website,
//	- an order placed within the GebWMS system,
//	- Point of Sale (potentially... why not?)




//	There are only 3 types of locations in GebWMS
//	10 aka Single		:	Only one item can be stored in such location
//	20 aka Multi		:	Many identical items can be stored in this location. Imagine a box that holds pencils when they are sold as EACHES.
//	30 aka Multi Mixed	:	You can throw anything you like into such location. Cases, pallets and eaches of different products and it will be happy.

$loc_types_arr	=	array(

	'10'	=>	$mylang['single'] . ' (A)',
	'11'	=>	$mylang['single'] . ' (E)',
	'12'	=>	$mylang['single'] . ' (C)',
	'20'	=>	$mylang['multi'] . ' (A)',
	'21'	=>	$mylang['multi'] . ' (E)',
	'22'	=>	$mylang['multi'] . ' (C)',
	'30'	=>	$mylang['mixed'] . ' (A)',
	'31'	=>	$mylang['mixed'] . ' (E)',
	'32'	=>	$mylang['mixed'] . ' (C)'

);




// Holds the short 1 character code for each location type. Used typically as additional info for the operator in places
// like the product search page.
$loc_type_codes_arr	=	array(

	'10'	=>	'SIA',	//	"Single" A
	'11'	=>	'SIE',	//	"Single" E
	'12'	=>	'SIC',	//	"Single" C
	'20'	=>	'MUA',	//	"Multi" A
	'21'	=>	'MUE',	//	"Multi" E
	'22'	=>	'MUC',	//	"Multi" C
	'30'	=>	'MXA',	//	"Multi Mixed" A
	'31'	=>	'MXE',	//	"Multi Mixed" E
	'32'	=>	'MXC'	//	"Multi Mixed" C

);


$loc_types_codes_reverse_arr	=	array(

	'S'		=>	10,		//	"Single"
	'M'		=>	20,		//	"Multi"
	'X'		=>	30		//	"Multi Mixed"

);



//	GebWMS supports different location functions. As of 24/04/2023 there will be:
//	-	Pickface (usually bottom of the rack),
//	-	Bulk (usually above pickface to keep product that will be used to replenish the pickface stock)
//	-	Goods IN (one location per warehouse). This by default needs to be a mixed location as products will be stored here
//		before they get labels and get moved to somewhere (potentially bulk location or emply pallet spaces)
//
//	18/05/2023:	Another location to consider would be some kind of general storage, damages and returns????!!?!?
//


$loc_functions_arr	=	array(

	'300'	=>	$mylang['goodsin'],
	'310'	=>	$mylang['pickface'],
	'320'	=>	$mylang['bulk'],
	'330'	=>	$mylang['storage'],
	'340'	=>	$mylang['returns'],
	'399'	=>	$mylang['despatch']

);


$loc_function_codes_arr	=	array(

	'300'	=>	'GI',	//	Goods IN
	'310'	=>	'PF',	//	Pick face
	'320'	=>	'BU',	//	Bulk
	'330'	=>	'ST',	//	Storage
	'340'	=>	'RT',	//	Returns
	'399'	=>	'DE'	//	Despatch

);




// Since I can accept EACH, CASE and PALLET I need to figure out how to mark it.
$stock_unit_type_arr	=	array(

	'1'	=>	$mylang['each'],
	'3'	=>	$mylang['case'],
//	'5'	=>	$mylang['pallet']

);


// I would like to control most of the WMS "settings" like unit types from arrays or other constants 
// that can be set in one location. Maybe an ugly hack but will work for now.
$stock_unit_type_reverse_arr	=	array(

	'E'	=>	1,	//	"EACH"
	'E'	=>	1,	//	"EACH"
	'C'	=>	3,	//	"CASE"
//	'P'	=>	5	//	"PALLET"

);





// two status codes so far for products...
$product_status_arr	=	array(

	'0'	=>	$mylang['active'],
	'1'	=>	$mylang['disabled']

);



// Activity types. This is going to be used for recent activity page to map what code means what.
// Product2Location
// Location 2 Location
// etc etc
// Table:	geb_stock_history
// Column:	stk_hst_op_type


$activity_type_arr	=	array(

	'10'	=>	$mylang['prod2location'],
	'20'	=>	$mylang['goodsin']

);


// The reverse of the above for some functions... blah blah!
$activity_type_reverse_arr	=	array(

	'Prod2Loc'	=>	10,
	'GoodIN'	=>	20

);


/*
//	Codes that are assigned to the geb_order_header table: ordhdr_type column
//	These are required if I want to have the ability to report on orders that
//	have been imported from a different system, placed within GebWMS or via
//	a Point Of Sale system (or anything else)
//	Can be customised to add your own codes if required
$order_type_arr	=	array(

	'100'		=>	$mylang['imported'],
	'110'		=>	$mylang['geb_order']

);
*/

/*
//	Codes that are assigned to the geb_order_header table: ordhdr_status column
$order_status_arr	=	array(

	'10'	=>	$mylang['on_hold'],
	'20'	=>	$mylang['ready'],
	'30'	=>	$mylang['started'],
	'40'	=>	$mylang['paused'],
	'50'	=>	$mylang['completed_short'],
	'60'	=>	$mylang['completed'],
	'70'	=>	$mylang['cancelled']

);


$order_status_reverse_arr	=	array(

	'H'		=>	10,	// On Hold
	'R'		=>	20,	// Ready
	'S'		=>	30,	// Started
	'P'		=>	40,	// Pause
	'X'		=>	50,	// Complete (short)
	'C'		=>	60,	// Complete
	'9'		=>	70	// Cancelled

);
*/





// To have same size gaps between input and select and other elements etc etc
$box_size_str	=	'height:64px;';


// Color code scheme for tables... Left and right side.
// This is for the 1 row and two column setup mainly...
$backclrA		=	'#d6bfa9';
$backclrB		=	'#f7f2ee';


$color_admin	=	'background-color: #ef5350; color: white;';
$color_manager	=	'background-color: #42A5F5; color: white;';
$color_general	=	'background-color: #8D6E63; color: white;';



//	This will set the global way of showing the date. This will impact the pick_start date,
//	order enter date, pick complete date etc etc
function display_date($date_str, $date_format_selected)
{
	$output_date	=	'';

	if ($date_format_selected == 0)
	{

		//	make sure that there is at least something in the provided date string.
		if (strlen($date_str) > 5)
		{
			$output_date	=	date('d/m/Y, H:i:s', strtotime($date_str));
		}
	}

	return $output_date;
}




//
//	Warehouse Specific functions
//
//	In the future these could be moved to a different php file if required.
//


//	Get location fields and output a nice human readable description!
function decode_loc($loc_func, $loc_type, $loc_blkd, $function_codes_arr, $type_codes_arr)
{

	//	The goal here is to have a little system that allows me to make it very flexible to add
	//	other stuff later on if required. 

	$loc_str	=	'';	//	The final string. Like: PX, BMX or PM etc
	$loc_style	=	'';	//	Color, text weight etc etc Things that adjust the apperance of the text displayed on the page!
						//	For example: Pickface is going to be in bold! Blocked is going to be Red! 

	$output_arr	=	array();	//	I will be storing two things here. The String and the Style!


	//	Location fuction "decoder"
	if		($loc_func == 300)	{	$loc_str	.=	$function_codes_arr[$loc_func];	$loc_style	.=	'';		}	//					//	Goods IN	:	GI
	elseif	($loc_func == 310)	{	$loc_str	.=	$function_codes_arr[$loc_func];	$loc_style	.=	'font-weight: bold;';		}	//	Pickface	:	PF
	elseif	($loc_func == 320)	{	$loc_str	.=	$function_codes_arr[$loc_func];	$loc_style	.=	'';		}	//					//	Bulk		:	BU
	elseif	($loc_func == 330)	{	$loc_str	.=	$function_codes_arr[$loc_func];	$loc_style	.=	'';		}	//					//	Storage		:	ST
	elseif	($loc_func == 340)	{	$loc_str	.=	$function_codes_arr[$loc_func];	$loc_style	.=	'';		}	//					//	Return		:	RT

	//	What about Damages??????

	elseif	($loc_func == 399)	{	$loc_str	.=	$function_codes_arr[$loc_func];	$loc_style	.=	'';		}	//					//	Despatch	:	DE


	//	Location type "decoder"
	if		($loc_type == 10)	{	$loc_str	.=	'+' . $type_codes_arr[$loc_type];	$loc_style	.=	'';		}	//	Single		:	SI
	elseif	($loc_type == 20)	{	$loc_str	.=	'+' . $type_codes_arr[$loc_type];	$loc_style	.=	'';		}	//	Multi		:	MU
	elseif	($loc_type == 30)	{	$loc_str	.=	'+' . $type_codes_arr[$loc_type];	$loc_style	.=	'';		}	//	Multi Mixed	:	MX


	//	This is meant to be the last entry. Blocked location!
	if ($loc_blkd	==	1)		{		$loc_str	.=	'+BL';	$loc_style	.=	'color: #ef5350;';		}


	$output_arr[0]	=	$loc_str;
	$output_arr[1]	=	$loc_style;

	return $output_arr;
}



//	Checks if the product categories match the location categories. Used in things like prod2loc (and I am sure it will be in others)
function location_category_check($location_arr, $product_arr)
{

	if
	(
		($location_arr['loc_cat_a'] == 0 || $location_arr['loc_cat_a'] == $product_arr[0]['prod_category_a']) 

		AND	

		($location_arr['loc_cat_b'] == 0 || $location_arr['loc_cat_b'] == $product_arr[0]['prod_category_b'])

		AND

		($location_arr['loc_cat_c'] == 0 || $location_arr['loc_cat_c'] == $product_arr[0]['prod_category_c'])

		AND

		($location_arr['loc_cat_d'] == 0 || $location_arr['loc_cat_d'] == $product_arr[0]['prod_category_d'])

	)
	{
		return true;
	}
	else
	{
		return false;
	}

}




//
//
//	Provide a barcode and get an error code if the product does not meet certain criteria!
//	Will be used in many places in GebWMS
//
//

function get_product_data_via_barcode($db, $product_barcode, $product_qty)
{

    global $mylang;

	$msg				=	'';			//	An error message typically!
	$control			=	666;		//	0 = all good, anything else means an error occured!
	$result				=	array();	//	the final array!
	$product_arr		=	array();	//	all product details will be stored here from the SQL!

	$mimic				=	0;			//	Not a MIMIC by default! 1 = MIMIC!
	$product_final_qty	=	0;			//	0 by default! The final quantity that will be INSERTED / UPDATED!
	$product_unit		=	0;			//	Wrong! Has to be at least 1 (each) or 3 (case), 0 by default to show it it BAD!


	$sql	=	'

		SELECT

		prod_pkey,
		prod_category_a,
		prod_category_b,
		prod_category_c,
		prod_category_d,
		prod_mimic,
		prod_each_barcode,
		prod_each_barcode_mimic,
		prod_case_barcode,
		prod_case_barcode_mimic,
		prod_case_qty,
		prod_case_qty_mimic

		FROM 

		geb_product

		WHERE

		prod_each_barcode = :sprod_each_bar OR prod_case_barcode = :sprod_case_bar

		OR

		CASE
			WHEN prod_mimic = 1 THEN ((prod_each_barcode_mimic = :smimic_bar) OR (prod_case_barcode_mimic = :smimic_bar))
		END;

		AND
		
		prod_disabled = 0


	';



	if ($stmt = $db->prepare($sql))
	{

		$stmt->bindValue(':sprod_each_bar',		$product_barcode,	PDO::PARAM_STR);
		$stmt->bindValue(':sprod_case_bar',		$product_barcode,	PDO::PARAM_STR);
		$stmt->bindValue(':smimic_bar',			$product_barcode,	PDO::PARAM_STR);

		$stmt->execute();


		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$product_arr[]	=	$row;
		}


		//	Only process the product if ONLY 1 is returned! If > 1 means that barcodes are a mess!!
		//	That would require the Admin to fix the duplicates!!!
		if (count($product_arr) == 1)
		{

			if		(strcmp($product_barcode, trim($product_arr[0]['prod_each_barcode'])) === 0 )
			{
				$product_unit		=	1;
				$product_final_qty	=	$product_qty;
			}
			elseif	(strcmp($product_barcode, trim($product_arr[0]['prod_each_barcode_mimic'])) === 0 )
			{
				$product_unit		=	1;
				//	Always in EACH and describes the total amount that will be INSERTED / UPDATED in the location!
				$product_final_qty	=	$product_qty;
				$mimic				=	1;	//	Totally a mimic product using the mimic each barcode!
			}
			elseif	(strcmp($product_barcode, trim($product_arr[0]['prod_case_barcode'])) === 0 )
			{
				$product_unit		=	3;
				//	Always in EACH and describes the total amount that will be INSERTED / UPDATED in the location!
				$product_final_qty	=	leave_numbers_only($product_arr[0]['prod_case_qty']) * $product_qty;		//	Mimic case qty!
			}
			elseif	(strcmp($product_barcode, trim($product_arr[0]['prod_case_barcode_mimic'])) === 0 )
			{
				$product_unit		=	3;
				//	Always in EACH and describes the total amount that will be INSERTED / UPDATED in the location!
				$product_final_qty	=	leave_numbers_only($product_arr[0]['prod_case_qty_mimic']) * $product_qty;	//	Mimic case qty!
				$mimic				=	1;	//	Totally a mimic product using the mimic case barcode and new case qty!
			}


		}


		//	Note:	Maybe check if the product_qty provided is not 0?

		//
		//	Do some checks here!
		//

		if
		(
			(count($product_arr) == 0)
		)
		{
			//	No product found!
			$control	=	107203;
			$msg		=	$mylang['product_not_found'];
		}
		elseif (count($product_arr) > 1)
		{
			//	Two or more products found with the same barcode... Needs to be fixed ASAP by the Admin!
			$control	=	107203;
			$msg		=	$mylang['products_found_with_the_same_barcode'];
		}
		elseif ($product_final_qty == 0)
		{
			//	The final qty can't be 0 = ERR somewhere!
			$control	=	107203;
			$msg		=	$mylang['products_found_with_the_same_barcode'];
		}
		elseif ($product_unit == 0)
		{
			//	The product unit can't be 0 = ERR somewhere!
			$control	=	107203;
			$msg		=	$mylang['products_found_with_the_same_barcode'];
		}
		else
		{
			//	Seems like all checks are a-Ok if you got here!
			$control	=	0;
		}



	}
	else
	{
		//	Something did not go well!
		$control	=	667;
	}



	$result['control']			=	$control;
	$result['msg']				=	$msg;

	$result['product_arr']		=	$product_arr;
	$result['unit']				=	$product_unit;		//	1:	EACH;	3:	CASE
	$result['final_qty']		=	$product_final_qty;	//	always in EACH
	$result['is_mimic']			=	$mimic;

	return $result;


}








function get_location_data_via_barcode($db, $location_barcode)
{

    global $mylang;


	$msg			=	'';			//	An error message typically!
	$control		=	666;		//	0 = all good, anything else means an error occured!
	$result			=	array();	//	the final array!

	$location_arr	=	array();	//	all location details will be stored here from the SQL!
	$stock_arr		=	array();	//	all stock that is in this location!
	//	fix
	$user_warehouse_uid		=	leave_numbers_only($_SESSION['user_warehouse']);
	//$user_warehouse_uid	=	0;

	//	Run the location query!
	$sql	=	'


		SELECT

		loc_pkey,
		loc_wh_pkey,
		loc_code,
		loc_function,
		loc_type,
		loc_blocked,
		loc_cat_a,
		loc_cat_b,
		loc_cat_c,
		loc_cat_d,
		loc_magic_product,

		stk_pkey,
		stk_loc_pkey,
		stk_prod_pkey,
		stk_unit,
		stk_qty,
		stk_mimic

		FROM 

		geb_location

		LEFT JOIN geb_stock ON geb_location.loc_pkey = geb_stock.stk_loc_pkey

		WHERE

		geb_location.loc_barcode = :sloc_barcode

		AND

		geb_location.loc_disabled = 0

		AND

		(geb_stock.stk_disabled IS NULL OR geb_stock.stk_disabled = 0)


	';

	if ($user_warehouse_uid > 0)
	{
		//	Add a warehouse filter to the location!
		//	THis is to comply with the user settings!
		$sql	.=	' AND geb_location.loc_wh_pkey = :swarehouse_uid ';
	}



	if ($stmt = $db->prepare($sql))
	{

		$stmt->bindValue(':sloc_barcode',	$location_barcode,	PDO::PARAM_STR);

		//	Limit the scope of locations based on the warehouse!
		//	Again, if the user has this set to 0 = can view ANY warehouse!
		if ($user_warehouse_uid > 0)
		{
			$stmt->bindValue(':swarehouse_uid',		$user_warehouse_uid,	PDO::PARAM_INT);
		}


		$stmt->execute();


		$product_ids_arr		=	array();	//	All IDs of the products in the location stored in the location!
												//	This is more of a helper array!


		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{


			// Check if the location already exists in the array, if not, create it
			if (count($location_arr) == 0)
			{
				$location_arr = array(
				
					'loc_pkey'				=>	$row['loc_pkey'],
					'loc_wh_pkey'			=>	$row['loc_wh_pkey'],
					'loc_code'				=>	$row['loc_code'],
					'loc_function'			=>	$row['loc_function'],
					'loc_type'				=>	$row['loc_type'],
					'loc_cat_a'				=>	$row['loc_cat_a'],
					'loc_cat_b'				=>	$row['loc_cat_b'],
					'loc_cat_c'				=>	$row['loc_cat_c'],
					'loc_cat_d'				=>	$row['loc_cat_d'],
					'loc_magic_product'		=>	$row['loc_magic_product'],
					'loc_blocked'			=>	$row['loc_blocked']
				);

			}


			//	Only add if the location has a product allocated to it!
			if (leave_numbers_only($row['stk_pkey']) > 0)
			{

				$stock_arr[] =
				// Add stock information to the location's stock_info array
				[
					'stk_pkey'		=>	$row['stk_pkey'],
					'stk_loc_pkey'	=>	$row['stk_loc_pkey'],
					'stk_prod_pkey' =>	$row['stk_prod_pkey'],
					'stk_unit'		=>	$row['stk_unit'],
					'stk_qty'		=>	$row['stk_qty'],
					'stk_mimic'		=>	$row['stk_mimic']
				];
				array_push($product_ids_arr, leave_numbers_only($row['stk_prod_pkey']));
			}


		}



		//
		//	Do some checks here!
		//

		if (count($location_arr) == 0)
		{
			//	Location not found!
			$control	=	21;
			$msg		=	'Location not found';
		}
		elseif ($location_arr['loc_blocked'] > 0)
		{
			$control	=	20;
			$msg		=	'Location blocked';
		}
		else
		{
			//	Seems like all checks are a-Ok if you got here!
			$control	=	0;
		}



	}
	else
	{
		//	Something did not go well!
		$control	=	668;
		$msg		=	'ups';
	}



	$result['control']			=	$control;
	$result['msg']				=	$msg;
	$result['location_arr']		=	$location_arr;
	$result['stock_arr']		=	$stock_arr;
	$result['product_ids_arr']	=	$product_ids_arr;


	return $result;

}




//	Exactly what it says on the tin!
//	Provide a product and location + other info and it will insert it to the location!
//	This works for NOT occupied locations!
//	A different function will be used to UPDATE the location stock about a product!
function insert_product_2_location($db, $location_uid, $product_uid, $product_unit, $product_final_qty, $mimic)
{

    global $mylang;


	$msg			=	'';			//	An error message typically!
	$control		=	666;		//	0 = all good, anything else means an error occured!
	$result			=	array();	//	the final array!


	$sql	=	'


		INSERT
		
		INTO

		geb_stock
		
		(
			stk_loc_pkey,
			stk_prod_pkey,
			stk_unit,
			stk_qty,
			stk_mimic
		) 

		VALUES

		(
			:istk_loc_pkey,
			:istk_prod_pkey,
			:istk_unit,
			:istk_qty,
			:istk_mimic
		)

	';



	if ($stmt = $db->prepare($sql))
	{

		$stmt->bindValue(':istk_loc_pkey',		$location_uid,				PDO::PARAM_INT);
		$stmt->bindValue(':istk_prod_pkey',		$product_uid,				PDO::PARAM_INT);
		$stmt->bindValue(':istk_unit',			$product_unit,				PDO::PARAM_INT);
		$stmt->bindValue(':istk_qty',			$product_final_qty,			PDO::PARAM_INT);
		$stmt->bindValue(':istk_mimic',			$mimic,						PDO::PARAM_INT);

		$stmt->execute();
		$db->commit();

		$control	=	0;	//	all went well
	}
	else
	{
		//	Something did not go well!
		$control	=	670;
		$msg		=	$mylang['sql_error'];		
	}


	$result['control']			=	$control;
	$result['msg']				=	$msg;

	return $result;

}








//
//
//	Big Boy! Does all the heavy lifting for me!
//	Provide location barcode, product barcode and QTY. The function will figure out
//	the product can be be INSERTED / UPDATED into the specified location!
//
//
function do_magic($db, $product_barcode, $location_barcode, $product_qty)
{

    global $mylang;

	$result				=	array();
	$message_id			=	666;		//	Not so good by default :)
	$message2op			=	'';

	$location_data		=	array();	//	Location details stored here
	$stock_data			=	array();	//	Stock details within a location
	$product_data		=	array();	//	Product details stored here


	//	Used to determine what to show based on users warehouse settings.
	//	Keep in mind that a value of 0 means ALL warehouses! So only apply a filter when the value is > 0
	//	Maybe also add a check to see it if even has been set????
	$user_warehouse_uid		=	leave_numbers_only($_SESSION['user_warehouse']);

	$input_checks			=	666;		//	0 means all good; by default it is 666 = BAD!


	//
	//
	//	INPUT Checks!
	//
	//

	if
	(
		(strlen($product_barcode) < 4)
		OR
		(strlen($location_barcode) < 4)
	)
	{
		// Product nor location barcode doesn't meet the length requirements! 
		$input_checks = 1;
	}
	else if
	(
		(is_numeric($product_barcode) == false)
		OR
		(is_numeric($location_barcode) == false)
	)
	{
		// Product nor location barcode is not numeric! Abort!
		$input_checks = 2;
	}
	else if (is_numeric($product_qty) == false)
	{
		// Product Qty is not a number...
		$input_checks = 3;
	}
	else if ($product_qty <= 0)
	{
		// Product Qty is either 0 or negative (-1, -50, etc.). Can't insert stock that is a negative quantity!
		$input_checks = 4;
	}
	else
	{
		// Success! All checks are good so far!
		$input_checks = 0;
	}




	if ($input_checks == 0)
	{

		//	Get all the required info about the product like if it is a mimic, product_uid etc
		//	All of that information based on the product barcode! This will also run checks if 
		//	duplicate barcodes exists etc
		$product_data	=	get_product_data_via_barcode($db, $product_barcode, $product_qty);

		//	If control is == 0 that means that I can move to the next stage!
		//	Otherwise assign the $message_id and $message2op variables!
		if ($product_data['control'] == 0)
		{

			//	Maybe will replace them soon.. for now let them be!
			$mimic				=	$product_data['is_mimic'];						//	0:	NO;	1:	YES!
			$product_uid		=	$product_data['product_arr'][0]['prod_pkey'];	//	The unique key of the product!
			$product_unit		=	$product_data['unit'];							//	1:	EACH;	3:	CASE
			$product_final_qty	=	$product_data['final_qty'];


			//	Gets all location specs + entire stock off the location!
			$location_data		=	get_location_data_via_barcode($db, $location_barcode);


			//	If control is == 0 that means that I can move to the next stage!
			//	Otherwise assign the $message_id and $message2op variables!
			if ($location_data['control'] == 0)
			{

				$location_arr		=	$location_data['location_arr'];
				$location_type		=	$location_arr['loc_type'];
				$location_uid		=	$location_arr['loc_pkey'];

				$product_ids_arr	=	array_unique($location_data['product_ids_arr']);		//	Total number of products in location!
				$product_count		=	count($product_ids_arr);


				// SINGLE LOCATION Checks!
				if
				(
				
					($location_type == 10)								//	ANY can go in here!

					OR

					($location_type == 11 AND $product_unit == 1)		//	SINGLE (E) AND EACH only

					OR

					($location_type == 12 AND $product_unit == 3)		//	SINGLE (C) AND CASE only

				)
				{

					//	Check if location is empty or has a product allocated!
					if ($product_count == 0)
					{

						//	No product in this SINGLE location!
						//	Check if the SINGLE location allows for this particular product unit to be inserted!
						//	Example: If the product is a case and the location is SINGLE EACH (11) = Can't go further!


						//	Magic Product takes priority over category settings!
						if
						(
							($location_arr['loc_magic_product'] > 0)	//	just in case the one below in not sufficient!
							
							AND
							
							($location_arr['loc_magic_product'] == $product_uid)
						)
						{
							//	INSERT product into location!
							$message_id		=	0;	//	+++		ALL WENT WELL	+++
							$message2op		=	$mylang['success'];
						}
						else
						{
							//	NOT a magic product!
							//	Now check if the location has categories specified!


							//	Can be merged into one if statement with the category check????
							if ($location_arr['loc_cat_a'] > 0)	//	if true		=	category settings enabled for this location!
							{
								//	This has to be actioned only when the category_a is > 0!
								if (location_category_check($location_arr, $product_data['product_arr']))
								{
									//	The product matches to the categories of the location!
									$message_id		=	0;	//	+++		ALL WENT WELL	+++
									$message2op		=	$mylang['success'];
									
								}
								else
								{
									//	Category requirements NOT MET!
									$message_id		=	3;	//	Can't insert product! Category mismatch!
															//	Basically, the location has some category filter set but hte product
															//	that the operator is trying to insert to it does not meet these categories.
									$message2op		=	$mylang['success'];
								}
							}
							else
							{
								//	Location has no categories set for it.
								//	No Magic Product either!
								// Just INSERT the goods!
								$message_id		=	0;	//	+++		ALL WENT WELL	+++
								$message2op		=	$mylang['success'];
							}


						}

						//	No further checks needed! Product can be allocated to this location if the message_id = 0!
						//	Also, there will be a time where you want to just insert the product and a time when you want To
						//	provide a HTML output for the operator to select what action to take!
						if ($message_id == 0)
						{
							$insert_now	=	insert_product_2_location($db, $location_uid, $product_uid, $product_unit, $product_final_qty, $mimic);
							$message_id	=	$insert_now['control'];
						}


					}	//	if ($product_count == 0)
					else
					{
						$message_id		=	107203;
						$message2op		=	$mylang['location_full'];
					}





				}
				elseif	//	MULTI LOCATION Checks!
				(
				
					($location_type == 20)

					OR

					($location_type == 21)

					OR

					($location_type == 22)

				)
				{

							$message_id		=	234;

				}
				elseif	//	MULTI MIXED LOCATION Checks!
				(
				
					($location_type == 30)

					OR

					($location_type == 31)

					OR

					($location_type == 32)

				)
				{
					
					
				}
				else
				{

					$message_id	=	3456789;
					$message2op	=	$mylang['product_loc_nt_compatible'];

				}







			}	//	location checks end here!
			else
			{
				$message_id	=	$location_data['control'];
				$message2op	=	$location_data['msg'];
			}



		}	//	product checks end here!
		else
		{
			$message_id	=	$product_data['control'];
			$message2op	=	$product_data['msg'];
		}



	}	//	if ($input_checks == 0)
	else
	{

		if ($input_checks	==	1)
		{
			$message_id		=	107203;
			$message2op		=	$mylang['barcode_too_short'];
		}
		elseif ($input_checks	==	2)
		{
			$message_id		=	107204;
			$message2op		=	$mylang['invalid_barcode'];
		}
		elseif ($input_checks	==	3)
		{
			$message_id		=	107205;
			$message2op		=	'Case barcode too short';	//$mylang['barcode_to_short'];
		}
		elseif ($input_checks	==	4)
		{
			$message_id		=	107205;
			$message2op		=	'Case qty incorrect';	//$mylang['barcode_to_short'];
		}

	}


	$result['control']		=	$message_id;
	$result['msg']			=	$message2op;


	return $result;

}












//
//	END of: Warehouse Specific functions
//



//
//	Warehouse SQL functions (Hmmm... Good or bad idea?)
//

function update_order_status($db, $status_code, $order_uid)
{
	
	
}


//
//	END of: Warehouse SQL functions
//


// Define a function for generating select box options!
function generate_select_options($options, $selectedValue, $defaultLabel)
{

	$html = '<option value="0"';

	if ($selectedValue == 0)
	{ 
		$html .= ' selected'; 
	}

	$html .= '>' . $defaultLabel . '</option>';

	foreach ($options as $key => $value)
	{
		if (strlen($value) > 0)
		{
			$html .= '<option value="' . $key . '"';
			if ($key == $selectedValue)
			{ 
				$html .= ' selected'; 
			}
		
			$html .= '>' . $value . '</option>';
		}
	}

	return $html;
}





//	Some support stuff here...

// define the print message function !
function print_message($control, $msg)
{
	$result = array();
	$result['control'] = $control;
	$result['msg'] = $msg;
	echo json_encode($result);
}


// define the print message function with a payload in form of HTML or Array with DATA

function print_message_data_payload($control, $msg, $data_arr)
{
	$result = array();
	$result['control'] = $control;
	$result['msg'] = $msg;
	$result['data'] = $data_arr;
	echo json_encode($result);
}


function print_message_html_payload($control, $msg, $html_str)
{
	$result = array();
	$result['control'] = $control;
	$result['msg'] = $msg;
	$result['html'] = $html_str;
	echo json_encode($result);
}




// Removes everything apart from numbers = Security reasons !
function leave_numbers_only($input_string)
{
	return preg_replace('/[^0-9]/', '', trim($input_string));
}



// New Version of that thing as it does not work on PHP 7.x
// Convert a dec value to binary. Add 0s to match the lenght of 16 - we can expand this later if needed !!
// Returns an array bit values
function convert_dec_to_bin_with_padding($dec_value)
{
	$x	=	decbin($dec_value);
	return str_pad($x, 16, '0', STR_PAD_LEFT);
}




// core function that checks for cookie bit - Returns if cookie : 0 -> false, if 1 -> true !
function core_acl_cookie_check($cookie_value)
{
	$outcome		=	false;								//	by default don't allow !
	$cookie_value	=	leave_numbers_only($cookie_value);	//	remove anything apart from numbers !
	if ($cookie_value == 1)	{	$outcome = true;	}
	return $outcome;
}




//
//
//	GebWMS Access Control
//
//	The structure of the Access Control will be super simple!
//
//	Access is going to be determined by setting proper bits in a 65k integer value. This means 16 possible bits to set.
//	However the first bit is always going to be 1 which means that I got only about 15 options left to explore.
//	Probably will never happen but it is there if anyone ever needs it.
//
//	Baseline decimal value 32768 represented below in binary = NO RIGHTS!
//	1000000000000000
//
//	2nd location	:	Feature Enabled		:	0: Disabled;	1: Enabled;		This determines if the option / menu is even avaiable to the operator,
//	3rd location	:	Add Allowed			:	0: Nope;		1: Allow;		Operator can Add items/warehouses/locations to the system,
//	4th location	:	Update Allowed		:	0: Nope;		1: Allow;		Operator can update entries in the system,
//	5th location	:	Delete Allowed		:	0: Nope;		1: Allow;		Operator can delete entries from the system.
//
//
//	ACL value = 55296:
//	1101100000000000
//
//	In short translates to:
//
//	Feature is enabled, The operator can't Add anything to the system but he can Update and Delete entries.
//
//	Details:
//
//
//	1	1	0	1	1	00000000000
//	|	|	|	|	|
//	|	|	|	|	|
//	|	|	|	|	Deleting items has been allowed
//	|	|	|	|
//	|	|	|	Updating items like warehouse location or products allowed
//	|	|	|
//	|	|	Adding items is disabled
//	|	|
//	|	Feature is Enabled = operator can see / access it (think of a menu option for example)
//	|
//	This means nothing and can be ignored
//
//
//


function is_it_enabled($cookie)
{
	$cookie_array	=	array();
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed.
	return core_acl_cookie_check($cookie_array[1]);		//	Check the second bit
}


function can_user_add($cookie)
{
	$cookie_array	=	array();
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed.
	return core_acl_cookie_check($cookie_array[2]);		//	Check the third bit
}


function can_user_update($cookie)
{
	$cookie_array	=	array();
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed.
	return core_acl_cookie_check($cookie_array[3]);		//	Check the forth bit
}


function can_user_delete($cookie)
{
	$cookie_array	=	array();
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed.
	return core_acl_cookie_check($cookie_array[4]);		//	Check the fifth bit
}
