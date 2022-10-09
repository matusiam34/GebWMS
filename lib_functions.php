<?php

/**

		A bunch of support functions to aid the framework !

 */


// Some GebWMS constants...

$loc_types_arr	=	array(

	"10"	=>	"Single",
	"20"	=>	"Multi",
	"30"	=>	"Multi Mixed"

);


// To have same size gaps between input and select 
$box_size_str	=	"height:64px;";




// define the print message function !
function print_message($control, $msg)
{
	$result = array();
	$result['control'] = $control;
	$result['msg'] = $msg;
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
        return str_pad($x, 16, "0", STR_PAD_LEFT);
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
	$cookie_array	=	convert_dec_to_bin_with_padding(leave_numbers_only($cookie));	// allow numbers only - anything else will be removed from the $cookie input
	return core_acl_cookie_check($cookie_array[0]);	// Check the first bit -> located at location: 1 !
}

