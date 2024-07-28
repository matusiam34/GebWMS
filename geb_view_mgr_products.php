<?php


//	NOTE:	If you are using only numbers for a product code... This will mess this script up a bit at the moment...
//			Need to find a nice solution for this! Maybe not allow to search via barcode?


// load the login class
require_once('lib_login.php');


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    

	// load the supporting functions....
	require_once('lib_system.php');

	//	Certain access right checks should be executed here...
	if (is_it_enabled($_SESSION['menu_mgr_products']))
	{

		// needs a db connection...
		require_once("lib_db_conn.php");

		$product_or_barcode		=	"";
		$company_uid			=	0;
		
		if (isset($_GET["product"]))
		{
			$product_or_barcode		=	trim($_GET["product"]);
		}

		if (isset($_GET["comp"]))
		{
			$company_uid		=	trim($_GET["comp"]);
		}



?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title><?php	echo $mylang['products'];	?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSS
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->

	<link rel="stylesheet" href="css/bulma.css">
	<link rel="stylesheet" href="css/custom.css">


	<!-- Scripts
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<script src="js/jquery.js"></script>

	<!--	Include all custom scripts	-->
	<script src="js/myFunctions.js"></script>

	<script src="js/alertable.js"></script>


	<!-- Favicon
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="icon" type="image/png" href="images/favicon.png">



	<script language="javascript" type="text/javascript">



		$(document).ready(function() 
		{

			// When the operator selects a new category A = fetch the related category B entries!
			$('#id_category_a').change(function() {
				get_all_category_b();
				emptySelectBox('id_category_c');
				addOption2SelectBox('id_category_c', 0, '<?php	echo $mylang['none'];	?>');	
				emptySelectBox('id_category_d');
				addOption2SelectBox('id_category_d', 0, '<?php	echo $mylang['none'];	?>');	
			});


			// When the operator selects a new category B = fetch the related category C entries!
			$('#id_category_b').change(function() {
				get_all_category_c();
				emptySelectBox('id_category_d');
				addOption2SelectBox('id_category_d', 0, '<?php	echo $mylang['none'];	?>');	
			});

			// When the operator selects a new category C = fetch the related category D entries!
			$('#id_category_c').change(function() {
				get_all_category_d();
			});



		});




		// Get category B based on category A
		function get_all_category_b()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	1,
				action_format_js	:	1,
				action_disabled_js	:	0,
				cat_uid_js			:	get_Element_Value_By_ID('id_category_a'),
				company_uid_js		:	get_Element_Value_By_ID('comp')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					emptySelectBox('id_category_b');
					addOption2SelectBox('id_category_b', 0, '<?php	echo $mylang['none'];	?>');	

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_category_b', obje.data[i].cat_b_pkey, obje.data[i].cat_b_name);	
						}

					}

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('106555', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		// Get category C based on category B
		function get_all_category_c()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	2,
				action_format_js	:	1,
				action_disabled_js	:	0,
				cat_uid_js			:	get_Element_Value_By_ID('id_category_b'),
				company_uid_js		:	get_Element_Value_By_ID('comp')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					emptySelectBox('id_category_c');
					addOption2SelectBox('id_category_c', 0, '<?php	echo $mylang['none'];	?>');	

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_category_c', obje.data[i].cat_c_pkey, obje.data[i].cat_c_name);	
						}

					}

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('106556', '<?php	echo $mylang['server_error'];	?>');
					});

		}




		// Get category D based on category C
		function get_all_category_d()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	3,
				action_format_js	:	1,
				action_disabled_js	:	0,
				cat_uid_js			:	get_Element_Value_By_ID('id_category_c'),
				company_uid_js		:	get_Element_Value_By_ID('comp')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;

					emptySelectBox('id_category_d');
					addOption2SelectBox('id_category_d', 0, '<?php	echo $mylang['none'];	?>');	

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							addOption2SelectBox('id_category_d', obje.data[i].cat_d_pkey, obje.data[i].cat_d_name);	
						}

					}

				}
				else
				{
					$.alertable.error(obje.control, obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('106556', '<?php	echo $mylang['server_error'];	?>');
					});

		}






		function add_product()
		{

			const requestData =
			{
				action_code_js:				0,
				product_code_js:			get_Element_Value_By_ID('id_product_code'),
				product_description_js:		get_Element_Value_By_ID('id_product_description'),
				product_category_a_js:	 	get_Element_Value_By_ID('id_category_a'),
				product_category_b_js:		get_Element_Value_By_ID('id_category_b'),
				product_category_c_js:		get_Element_Value_By_ID('id_category_c'),
				product_category_d_js:		get_Element_Value_By_ID('id_category_d'),
				each_barcode_js: 			get_Element_Value_By_ID('id_each_barcode'),
				each_weight_js:				get_Element_Value_By_ID('id_each_weight'),
				case_barcode_js:			get_Element_Value_By_ID('id_case_barcode'),
				case_qty_js:				get_Element_Value_By_ID('id_case_qty'),
				min_qty_js:					get_Element_Value_By_ID('id_min_qty'),
				max_qty_js:					get_Element_Value_By_ID('id_max_qty'),

				company_uid_js:				get_Element_Value_By_ID('comp'),
				disabled_js: 				get_Element_Value_By_ID('id_disabled')
			};


			$.post('geb_ajax_product.php', requestData)
				.done(function (output)
				{
					const obje = jQuery.parseJSON(output);

					if (obje.control === 0)
					{
						$.alertable.info(obje.control, obje.msg).always(function() {	});
					}
					else
					{
						$.alertable.error(obje.control, obje.msg);
					}
				})
				.fail(function (){
					$.alertable.error('106557', '<?php echo $mylang['server_error']; ?>');
				});
		}






		//	Update product details
		function update_product()
		{

			$.post('geb_ajax_product.php', { 

				action_code_js			:	1,
				product_uid_js			:	get_Element_Value_By_ID('id_hidden'),
				product_code_js			:	get_Element_Value_By_ID('id_product_code'),
				product_description_js	:	get_Element_Value_By_ID('id_product_description'),
				product_category_a_js	:	get_Element_Value_By_ID('id_category_a'),
				product_category_b_js	:	get_Element_Value_By_ID('id_category_b'),
				product_category_c_js	:	get_Element_Value_By_ID('id_category_c'),
				product_category_d_js	:	get_Element_Value_By_ID('id_category_d'),
				each_barcode_js			:	get_Element_Value_By_ID('id_each_barcode'),
				each_weight_js			:	get_Element_Value_By_ID('id_each_weight'),
				case_barcode_js			:	get_Element_Value_By_ID('id_case_barcode'),
				case_qty_js				:	get_Element_Value_By_ID('id_case_qty'),
				min_qty_js				:	get_Element_Value_By_ID('id_min_qty'),
				max_qty_js				:	get_Element_Value_By_ID('id_max_qty'),
				company_uid_js			:	get_Element_Value_By_ID('comp'),
				disabled_js				:	get_Element_Value_By_ID('id_disabled')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$.alertable.info(obje.control, obje.msg).always(function() {	});
				}
				else
				{
					$.alertable.error(obje.control, obje.msg).always(function() {	});
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('106558', '<?php	echo $mylang['server_error'];	?>').always(function() {	});
					});

		}




	</script>





<style>
</style>



</head>
<body>


<?php

		// A little gap at the top to make it look better a notch.
		echo '<div class="blank_space_12px"></div>';

		echo '<section class="section is-paddingless">';
		echo	'<div class="container box has-background-light">';



		$columns_html	=	'';
		$details_html	=	'';





			//	SQL the companies!
			$company_arr	=	array();

			$sql	=	'

				SELECT

				company_code,
				company_pkey

				FROM geb_company

			';


			//	Ugly.. but works!
			if ($user_company_uid > 0)
			{
				$sql	.=	'	WHERE	company_pkey = :scompany_uid	';
			}


			if ($stmt = $db->prepare($sql))
			{

				if ($user_company_uid > 0)
				{
					$stmt->bindValue(':scompany_uid',	$user_company_uid,		PDO::PARAM_INT);
				}

				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$company_arr[$row['company_pkey']] = $row['company_code'];
				}

			}
			else
			{
				// Crash message here! FIX
			}


		$which_option_2_select	=	1;	//	keep the default to be safe!

		if ($user_company_uid == 0)		//	means sys admin is here...
		{
			if (isset($_GET["comp"]))
			{
				$which_option_2_select		=	trim($_GET["comp"]);
			}
		}


		$company_select_html	=	'';
		$company_select_html	=	generate_select_options($company_arr, $which_option_2_select, '');	//	''	means row 0 will not exist!




$columns_html	=	'<div class="columns">';

$columns_html	.=	'<div class="column is-3">';

$columns_html	.=	'<form action="geb_view_mgr_products.php" name="product_form" id="product_form" method="get">';

$columns_html	.=	'<div class="field has-addons">';
$columns_html	.=		'<div class="control">';
$columns_html	.=			'<input class="input" type="text" id="product" name="product" placeholder="' . $mylang['product_code'] . '" value="' . $product_or_barcode . '" >';
$columns_html	.=		'</div>';

$columns_html	.=		'<div class="control">';
$columns_html	.=			'<button class="button manager_class iconSearch" style="width:50px;" type="submit"></button>';
$columns_html	.=		'</div>';

$menu_link		=	"'index.php'";

$columns_html	.=		'<div class="control">';
$columns_html	.=			'<button class="button manager_class iconHome" style="width:50px;" type="button" onClick="open_link(' . $menu_link . ');"></button>';
$columns_html	.=		'</div>';


$columns_html	.=	'</div>';





$columns_html	.=	'</div>';


$columns_html	.=	'<div class="column is-3">';

$columns_html	.=	'<div class="field">';


$columns_html	.=	'
			<div class="control">
				<div class="select is-fullwidth">
					<select id="comp" name="comp">' . $company_select_html . '
					</select>
				</div>
			</div>';


$columns_html .= '</div>';
$columns_html .= '</div>';

$columns_html .= '</form>';





$columns_html .= '</div>';







		//	Make sure some protections are in place. Ugly,.. FIX
		if (($user_company_uid == 0) AND ($company_uid > 0))
		{
			$user_company_uid	=	$company_uid;
		}




	try
	{


		//	Start with no product ID.
		$product_id		=	0;

		// Figure out is the $product data provided is numeric only (barcode) or alphanumeric aka Product (hopefully)!
		$is_barcode	=	false;
		
		
		if (is_numeric($product_or_barcode))	{	$is_barcode	=	true;	}

		$sql	=	'

			SELECT

			*

			FROM 

			geb_product

			WHERE

		';


		if ($is_barcode)
		{
			// Search by barcode: Fixed 13 Oct 2022
			$sql	.=	' prod_each_barcode = :sprod_each_bar OR prod_case_barcode = :sprod_case_bar ';
		}
		else
		{
			// Search for a product by name
			$sql	.=	' prod_code = :sprod_code ';
		}


		$sql	.=	' AND prod_owner = :sprod_owner ';






		// A fix for now... Look at it at a later stage for a better solution...
		$prod_pkey			=	0;
		$prod_code			=	"";
		$prod_desc			=	"";
		$prod_category_a	=	0;
		$prod_category_b	=	0;
		$prod_category_c	=	0;
		$prod_category_d	=	0;
		$prod_each_barcode	=	"";
		$prod_each_weight	=	"";
		$prod_case_barcode	=	"";
		$prod_case_qty		=	0;
		$prod_min_qty		=	0;
		$prod_max_qty		=	0;
		$prod_disabled		=	0;	// active!

		$category_arr		=	array();	//	store all categories here!


		if ($stmt = $db->prepare($sql))
		{


			if ($is_barcode)
			{
				$stmt->bindValue(':sprod_each_bar',	$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':sprod_case_bar',	$product_or_barcode,	PDO::PARAM_STR);
			}
			else
			{
				$stmt->bindValue(':sprod_code',	$product_or_barcode,		PDO::PARAM_STR);
			}

			$stmt->bindValue(':sprod_owner',	$user_company_uid,			PDO::PARAM_INT);




			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

				// Grab product info... if there is anything!
				$prod_pkey				=	leave_numbers_only($row['prod_pkey']);
				$prod_code				=	trim($row['prod_code']);
				$prod_desc				=	trim($row['prod_desc']);
				$prod_category_a		=	leave_numbers_only($row['prod_category_a']);
				$prod_category_b		=	leave_numbers_only($row['prod_category_b']);
				$prod_category_c		=	leave_numbers_only($row['prod_category_c']);
				$prod_category_d		=	leave_numbers_only($row['prod_category_d']);
				$prod_each_barcode		=	trim($row['prod_each_barcode']);	// barcodes could contain letters?!?!?!
				$prod_each_weight		=	trim($row['prod_each_weight']);
				$prod_case_barcode		=	trim($row['prod_case_barcode']);	// barcodes could contain letters?!?!?!
				$prod_case_qty			=	leave_numbers_only($row['prod_case_qty']);
				$prod_min_qty			=	leave_numbers_only($row['prod_min_qty']);
				$prod_max_qty			=	leave_numbers_only($row['prod_max_qty']);
				$prod_disabled			=	leave_numbers_only($row['prod_disabled']);

			}



			//	Here grab the live categories into one array!
			$sql	=	'


					SELECT

					cat_a_pkey,
					cat_a_name,
					cat_b_pkey,
					cat_b_name,
					cat_b_a_level,
					cat_c_pkey,
					cat_c_name,
					cat_c_b_level,
					cat_d_pkey,
					cat_d_name,
					cat_d_c_level


					FROM geb_category_a

					LEFT JOIN geb_category_b ON geb_category_a.cat_a_pkey = geb_category_b.cat_b_a_level
					LEFT JOIN geb_category_c ON geb_category_b.cat_b_pkey = geb_category_c.cat_c_b_level
					LEFT JOIN geb_category_d ON geb_category_c.cat_c_pkey = geb_category_d.cat_d_c_level

					WHERE
					
					cat_a_owner = :scat_a_owner

			';



			if ($stmt = $db->prepare($sql))
			{

				$stmt->bindValue(':scat_a_owner',	$user_company_uid,		PDO::PARAM_INT);
				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// drop it into the final array...
					$category_arr[]	=	$row;
				}

			}


			//
			//	Some AI Generated code here... Because I have asked for some help!
			//
			//	<AI>
			//

			// Generate HTML for Category A, B, C and D
			$category_A_options = [];
			$category_B_options = [];
			$category_C_options = [];
			$category_D_options = [];

			// Populate category options arrays
			foreach ($category_arr as $category)
			{

				$category_A_options[$category['cat_a_pkey']] = $category['cat_a_name'];

				// Check if the current category B is associated with the selected category A
				if ($category['cat_b_a_level'] == $prod_category_a)
				{
					$category_B_options[$category['cat_b_pkey']] = $category['cat_b_name'];
				}

				// Check if the current category C is associated with the selected category B
				if ($category['cat_c_b_level'] == $prod_category_b)
				{
					$category_C_options[$category['cat_c_pkey']] = $category['cat_c_name'];
				}

				// Check if the current category C is associated with the selected category B
				if ($category['cat_d_c_level'] == $prod_category_c)
				{
					$category_D_options[$category['cat_d_pkey']] = $category['cat_d_name'];
				}



			}

			$category_a_html	=	generate_select_options($category_A_options, leave_numbers_only($prod_category_a), $mylang['none']);
			$category_b_html	=	generate_select_options($category_B_options, leave_numbers_only($prod_category_b), $mylang['none']);
			$category_c_html	=	generate_select_options($category_C_options, leave_numbers_only($prod_category_c), $mylang['none']);
			$category_d_html	=	generate_select_options($category_D_options, leave_numbers_only($prod_category_d), $mylang['none']);


			//
			//	</AI>
			//





			$columns_html	.=	'<div class="columns">';

			$columns_html	.=	'<div class="column is-3">';



			//	Totally needs a FIX
			$status_options =
			[
				0 => $mylang['active'],
				1 => $mylang['disabled'],
			];

			$status_html = '';
			foreach ($status_options as $value => $label)
			{
				$selected = ($prod_disabled == $value) ? ' selected' : '';
				$status_html .= '"<option value="' . $value . '" ' . $selected . '>' . $label . ' </option>"';
			}




			// General product info
			$details_html	=	'



<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['product_code'] . ':</p>
	<div class="control">
		<input id="id_product_code" class="input is-normal" type="text" placeholder="e.g. CLSPMRD" value="'. $prod_code .'" name="id_product_code" required>
	</div>
</div>



<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['description'] . ':</p>
	<div class="control">
		<input id="id_product_description" class="input is-normal" type="text" placeholder="Collection Sports Medium Red" value="'. $prod_desc . '" name="id_product_description">
	</div>
</div>



<div class="field" style="' . $box_size_str . '">
	<p class="help">' .	$mylang['category'] . ' (A)</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_category_a">' . $category_a_html . '
			</select>
		</div>
	  </div>
	</div>
</div>



<div class="field" style="' . $box_size_str . '">
	<p class="help">' .	$mylang['category'] . ' (B)</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_category_b">' . $category_b_html . '
			</select>
		</div>
	  </div>
	</div>
</div>



<div class="field" style="' . $box_size_str . '">
	<p class="help">' .	$mylang['category'] . ' (C)</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_category_c">' . $category_c_html . '
			</select>
		</div>
	  </div>
	</div>
</div>


<div class="field" style="' . $box_size_str . '">
	<p class="help">' .	$mylang['category'] . ' (D)</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_category_d">' . $category_d_html . '
			</select>
		</div>
	  </div>
	</div>
</div>



';



			$columns_html	.=	$details_html;	// all entries for this column go here...
			$details_html	=	"";				// empty for the next run!
			$columns_html	.=	'</div>';


			// Second column
			$columns_html	.=	'<div class="column is-3">';

			// More technical column
			$details_html	=	'


<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['each_barcode'] . ':</p>
	<div class="control">
		<input id="id_each_barcode" class="input is-normal" type="text" placeholder="87341285732154" value="'. $prod_each_barcode .'" name="id_each_barcode">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['each_weight'] . ':	</p>
	<div class="control">
		<input id="id_each_weight" class="input is-normal" type="text" placeholder="4.25" value="'. $prod_each_weight . '" name="id_each_weight">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['case_barcode'] . ':</p>
	<div class="control">
		<input id="id_case_barcode" class="input is-normal" type="text" placeholder="51682361869185" value="'. $prod_case_barcode. '" name="id_case_barcode">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['case_qty'] . ':</p>
	<div class="control">
		<input id="id_case_qty" class="input is-normal" type="text" placeholder="12" value="'. $prod_case_qty. '" name="id_case_qty">
	</div>
</div>';



			$columns_html	.=	$details_html;	// place the table in the column...
			$details_html	=	"";				// empty for the next run!

			$columns_html	.=	'</div>';


			$columns_html	.=	'<div class="column is-3">';


			$columns_html	.=	'





<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['min_qty'] . ':</p>
	<div class="control">
		<input id="id_min_qty" class="input is-normal" type="text" placeholder="20" value="'. $prod_min_qty . '" name="id_min_qty">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['max_qty'] . ':</p>
	<div class="control">
		<input id="id_max_qty" class="input is-normal" type="text" placeholder="100" value="'. $prod_max_qty . '" name="id_max_qty">
	</div>
</div>





<div class="field" style="'. $box_size_str .'">
	<p class="help">' . $mylang['status'] . ':</p>
	<div class="field is-narrow">
	  <div class="control">
		<div class="select is-fullwidth">
			<select id="id_disabled" name="id_disabled">' . $status_html . '
			</select>
		</div>
	  </div>
	</div>
</div>

';







			$columns_html	.=	'</div>';




			// Fouth column.. buttons?
			$columns_html	.=	'<div class="column is-3">';



// Allow an Add button only if the operator can Add.
// The same for Update :)


if (can_user_add($_SESSION['menu_mgr_products']))
{
	$details_html	.=	'

	<div class="field" style="'. $box_size_str .'">
		<p class="help">&nbsp;</p>
		<div class="control">
			<button class="button manager_class is-fullwidth"  onclick="add_product();">' . $mylang['add'] . '</button>
		</div>
	</div>';
}


if (can_user_update($_SESSION['menu_mgr_products']))
{
	$details_html	.=	'

	<div class="field" style="'. $box_size_str .'">
		<p class="help">&nbsp;</p>
		<div class="control">
			<button class="button manager_class is-fullwidth"  onclick="update_product();">' . $mylang['update'] . '</button>
		</div>
	</div>';
}



// Hidden field is on by default!
$details_html	.=	'

<div class="field" style="'. $box_size_str .'">
	<p class="help">&nbsp;</p>
	<div class="control">
		<input id="id_hidden" class="input is-normal" type="hidden" value="' . $prod_pkey . '">
	</div>
</div>';







			$columns_html	.=	$details_html;	// place the table in the column...
			$details_html	=	"";				// empty for the next run!

			$columns_html	.=	'</div>';




			// End of columns div!
			$columns_html	.=	'</div>';

			// Show the product technical stuff!
			echo	$columns_html;



		}
		// show an error if the query has an error
		else
		{
			echo 'Details Query Failed!';
		}





	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		print_message(1, $e->getMessage());
	}







//	Place it in a better space maybe? Not urgent.
//echo	'<input id="id_hidden" class="input is-normal" type="hidden">';


		echo '</div>';
	echo '</section>';




	}
	else
	{
		// User has logged in but does not have the rights to access this page !
		include('not_logged_in.php');
	}


}
else
{

    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');

}

?>



</body>
</html>
