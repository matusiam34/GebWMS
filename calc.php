<?php


$base	=	30;
$inc	=	0.1;
$dep	=	2.1;

$calc	=	0;

$max	=	1;

echo "Start";

while ($max < 100)
{
	$base	=	$base	+	$inc;
	$calc	=	($base - $dep) / 3;
	
	
	
	echo "H: " . $base . "; Div: " . $calc . "<br>";
	
	
	
	
	
	
	
	
	
	
	
	$max++;
}







?>