<?php

/*

		A bunch of support functions to aid the "framework" !
		Also many config items to make it yours :)

 */


//	Set the language here.
//	This has been updated (19 Nov 2022) to allow the operator to set the language in the My Account page :)
//	To add your own language just copy one of the existing php files (lang folder) and start translating.
//	To make it easy I would go with English. Unless you can translate from other languages better.

//$set_language	=	'Polski';
//$set_language	=	'English';

$set_language	=	trim($_SESSION['user_language']);
include('lang/' . $set_language . '.php');



//	If you have added a new transation to the lang folder ==>>> please update this array,
//	This will allow the new entry to be shown to the user.
$supported_languages_arr	=	array('English', 'Polski');


$date_display_style	=	0;		//	Default is GebWMS style = 	25/11/2022 at 18:04:33
								//	No other styles currently but... it will be easy to add here and manipulate!
								//	display_date is the one to adjust to reflect the changes here :)



//	Some GebWMS constants...


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

	'10'	=>	$mylang['single'],
	'20'	=>	$mylang['multi'],
	'30'	=>	$mylang['mixed']

);




// Holds the short 1 character code for each location type. Used typically as additional info for the operator in places
// like the product search page.
$loc_types_codes_arr	=	array(

	'10'	=>	'S',	//	"Single"
	'20'	=>	'M',	//	"Multi"
	'30'	=>	'X'		//	"Multi Mixed"

);


$loc_types_codes_reverse_arr	=	array(

	'S'		=>	10,		//	"Single"
	'M'		=>	20,		//	"Multi"
	'X'		=>	30		//	"Multi Mixed"

);


// Since I can accept EACH, CASE and PALLET I need to figure out how to mark it.
$stock_unit_type_arr	=	array(

	'3'	=>	$mylang['each'],
	'5'	=>	$mylang['case'],
	'7'	=>	$mylang['pallet']

);


// I would like to control most of the WMS "settings" like unit types from arrays or other constants 
// that can be set in one location. Maybe an ugly hack but will work for now.
$stock_unit_type_reverse_arr	=	array(

	'E'	=>	3,	//	"EACH"
	'C'	=>	5,	//	"CASE"
	'P'	=>	7	//	"PALLET"

);



// two status codes so far for products...
$product_status_arr	=	array(

	'0'	=>	$mylang['active'],
	'1'	=>	$mylang['disabled']

);



// Activity types. This is going to be used for recent acftivity page to map what code means what.
// Product2Location
// Location 2 Location
// etc etc
// Table:	geb_stock_history
// Column:	stk_hst_op_type


$activity_type_arr	=	array(

	'10'	=>	$mylang['prod2location']

);


// The reverse of the above for some functions... blah blah!
$activity_type_reverse_arr	=	array(

	'Prod2Loc'	=>	10

);



//	Codes that are assigned to the geb_order_header table: ordhdr_type column
//	These are required if I want to have the ability to report on orders that
//	have been imported from a different system, placed within GebWMS or via
//	o Point Of Sale system (or anything else)
$order_type_arr	=	array(

	'100'		=>	'Imported',
	'110'		=>	'Place Order'

);




//	Codes that are assigned to the geb_order_header table: ordhdr_status column
$order_status_arr	=	array(

	'10'	=>	'On Hold',
	'20'	=>	'Ready',
	'30'	=>	'Started',
	'40'	=>	'Paused',
	'50'	=>	'Complete (short)',
	'60'	=>	'Complete',
	'70'	=>	'Cancelled'

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






// To have same size gaps between input and select 
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
//		$just_date		=		date('d/m/Y', strtotime($date_str));
//		$just_time		=		date('H:i:s', strtotime($date_str));
		$output_date	=	date('d/m/Y, H:i:s', strtotime($date_str));
	}

	return $output_date;
}



//	Get location fields and output a nice human readable description block!
function decode_loc($loc_type, $loc_pkface, $loc_blkd, $loc_types_arr)
{

	$output_str	=	'';

	if ($loc_blkd	== 1)	{	$output_str	.=	'B';	}
	if ($loc_pkface	== 1)	{	$output_str	.=	'P';	}
	$output_str	.=	$loc_types_arr[$loc_type];

	return $output_str;
}



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
//	The structure of the Access Control will be super simple
//
//	Access is going to be determined by setting proper bits in a 65k integer value. However the first bit is always going to be 1
//	which means that I got only about 15 options left to explore. Probably will never happen but it is there.
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
