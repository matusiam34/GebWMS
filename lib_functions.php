<?php

/**

		A bunch of support functions to aid the framework !

 */



// Some GebWMS constants...


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
        $x = decbin($dec_value);
        return str_pad($x, 16, '0', STR_PAD_LEFT);
}




// core function that checks for cookie bit - Returns if cookie : 0 -> false, if 1 -> true !
function core_acl_cookie_check($cookie_value)
{
	$outcome = false;	//	 by default don't allow !
	$cookie_value	=	leave_numbers_only($cookie_value);	// remove anything apart from numbers !
	if ($cookie_value == 1)	{	$outcome = true;	}	
	return $outcome;
}


function can_user_access($cookie)
{
	$cookie_array	=	array();
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed.
	return core_acl_cookie_check($cookie_array[0]);	// Check the first bit -> located at location: 1 !
}


