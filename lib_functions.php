<?php

/**

		A bunch of support functions to aid the "framework" !

 */



//	Some GebWMS constants...


//	There are only 3 types of locations in GebWMS
//	10 aka Single: Only one item can be stored in such location
//	20 aka Multi: Many identical items can be stored in this location. Imagine a box that holds pencils when they are sold as EACHES.
//	30 aka Multi Mixed: You can throw anything you like into such location. Cases, pallets and eaches of different products and it will be happy.

$loc_types_arr	=	array(

	'10'	=>	'Single',
	'20'	=>	'Multi',
	'30'	=>	'Mixed'

);


// Holds the short one character code for each location type. Used typically as additional info for the operator in places
// like the product search page.
$loc_types_codes_arr	=	array(

	'10'	=>	'S',	//"Single",
	'20'	=>	'M',	//"Multi",
	'30'	=>	'X'		//"Multi Mixed"

);


$loc_types_codes_reverse_arr	=	array(

	'S'		=>	10,		//"Single",
	'M'		=>	20,		//"Multi",
	'X'		=>	30		//"Multi Mixed"

);


// Since I can accept EACH, CASE and PALLET I need to figure out how to mark it.
$stock_unit_type_arr	=	array(

	'3'	=>	'EACH',
	'5'	=>	'CASE',
	'7'	=>	'PALLET'

);


// I would like to control most of the WMS "settings" like unit types from arrays or other constants 
// that can be set in one location. Maybe an ugly hack but will work for now.
$stock_unit_type_reverse_arr	=	array(

	'E'	=>	3,	//"EACH",
	'C'	=>	5,	//"CASE",
	'P'	=>	7	//"PALLET"

);



// two status code so far for products...
$product_status_arr	=	array(

	'0'	=>	'Active',
	'1'	=>	'Disabled'

);



// Activity types. This is going to be used for recent acftivity page to map what code means what.
// Product2Location
// Location 2 Location
// etc etc
// Table:	geb_stock_history
// Column:	stk_hst_op_type


$activity_type_arr	=	array(

	'10'	=>	'Product2Location'

);

// The reverse of the above for some functions... blah blah!
$activity_type_reverse_arr	=	array(

	'Prod2Loc'	=>	10

);






// To have same size gaps between input and select 
$box_size_str	=	'height:64px;';


// Color code scheme for tables... Left and right side.
// This is for the 1 row and two column setup mainly...
$backclrA	=	'#d6bfa9';
$backclrB	=	'#f7f2ee';


$color_admin	=	'background-color: #ef5350; color: white;';
$color_manager	=	'background-color: #42A5F5; color: white;';
$color_general	=	'background-color: #8D6E63; color: white;';




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



/*
// Convert a dec value to binary. Add 0s to match the lenght of 16 - we can expand this later if needed !!
// Returns an array bit values
function convert_dec_to_bin_with_padding($dec_value)
{
	$binary_string	= sprintf( "%016d", decbin($dec_value));
	$binary_arr		= str_split($binary_string);
	return $binary_arr;
}
*/




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

/*
function can_user_access($cookie)
{
	$cookie_array	=	array();
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed.
	return core_acl_cookie_check($cookie_array[0]);	// Check the first bit -> located at location: 1 !
}
*/



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






