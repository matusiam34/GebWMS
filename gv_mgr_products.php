<?php

// if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
// (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
require_once("lib_passwd.php");

// include the configs / constants for the database connection
require_once("lib_db.php");

// load the login class
require_once("lib_login.php");


// create a login object. when this object is created, it will do all login/logout stuff automatically
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true)
{    


	// load the supporting functions....
	require_once("lib_functions.php");


	// Certain access rights checks should be executed here...
	if ( (can_user_access($_SESSION['user_inventory']))  AND  (leave_numbers_only($_SESSION['user_priv']) >=	min_priv))
	{

		// needs a db connection...
		require_once("lib_db_conn.php");

		$product_or_barcode		=	"";
		
		if (isset($_GET["product"]))
		{
			$product_or_barcode		=	trim($_GET["product"]);
		}

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title>Products</title>
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


	<!-- Favicon
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="icon" type="image/png" href="images/favicon.png">



	<script language="javascript" type="text/javascript">


		$(document).ready(function() 
		{


		});



		// Add an item to the database
		function add_item()
		{

			$.post('ajax_add_product.php', { 

				product_code_js			:	get_Element_Value_By_ID('id_product_code'),
				product_description_js	:	get_Element_Value_By_ID('id_product_description'),
				product_category_js		:	get_Element_Value_By_ID('id_product_category'),
				each_barcode_js			:	get_Element_Value_By_ID('id_each_barcode'),
				each_weight_js			:	get_Element_Value_By_ID('id_each_weight'),
				case_barcode_js			:	get_Element_Value_By_ID('id_case_barcode'),
				case_qty_js				:	get_Element_Value_By_ID('id_case_qty'),
				pall_qty_js				:	get_Element_Value_By_ID('id_pall_qty'),
				disabled_js				:	get_Element_Value_By_ID('id_disabled')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					//set_Element_Value_By_ID('id_item_name', '');
					//get_all_items();	// repopulate the table !
				}
				else
				{
					alert(obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						alert("server problem");
					});

		}



		// UPDATE item 
		function update_item()
		{


			$.post('ajax_update_product.php', { 

				product_uid_js			:	get_Element_Value_By_ID('id_hidden'),
				product_code_js			:	get_Element_Value_By_ID('id_product_code'),
				product_description_js	:	get_Element_Value_By_ID('id_product_description'),
				product_category_js		:	get_Element_Value_By_ID('id_product_category'),
				each_barcode_js			:	get_Element_Value_By_ID('id_each_barcode'),
				each_weight_js			:	get_Element_Value_By_ID('id_each_weight'),
				case_barcode_js			:	get_Element_Value_By_ID('id_case_barcode'),
				case_qty_js				:	get_Element_Value_By_ID('id_case_qty'),
				pall_qty_js				:	get_Element_Value_By_ID('id_pall_qty'),
				disabled_js				:	get_Element_Value_By_ID('id_disabled')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

//					set_Element_Value_By_ID('id_item_name', '');
//					get_all_items();	// repopulate the table !
				}
				else
				{
					alert(obje.msg);
				}

			}).fail(function() {
						// something went wrong -> could not execute php script most likely !
						alert("server problem");
					});

		}





	</script>



</head>
<body>





<?php

	// A little gap at the top to make it look better a notch.
	echo '<div style="height:12px"></div>';


	echo '<section class="section is-paddingless">';
	echo	'<div class="container box has-background-light">';


	$page_form	=	'';

	$page_form	.=	'<form action="gv_mgr_products.php" method="get">';

		$page_form	.=	'<div class="field has-addons">';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<input class="input" type="text" id="product" name="product" placeholder="Product code" value="' . $product_or_barcode . '">';
			$page_form	.=	'</p>';

			$page_form	.=	'<p class="control">';
			$page_form	.=		'<button class="button manager_class iconSearch" style="width:50px;" type="submit"></button>';
			$page_form	.=	'</p>';

		$page_form	.=	'</div>';

	$page_form	.=	'</form>';

	$page_form	.=	'<p class="control">';
	$menu_link	=	"'index.php'";
	$page_form	.=		'<button class="button manager_class iconHome" style="width:50px;" onClick="open_link(' . $menu_link . ');"></button>';
	$page_form	.=	'</p>';

	$page_form	.=	'<p class="control">';
	$page_form	.=		'<button class="button manager_class iconBackArrow" style="width:50px;" onClick="goBack();"></button>';
	$page_form	.=	'</p>';



	// Show the page header aka Product Search input field!


	// The "menu"!
	echo '<nav class="level">

	<!-- Left side -->
		<div class="level-left">

		<div class="level-item">
	' . $page_form . '
		</div>

		</div>

	</nav>';




	try
	{

		$product_id		=	0;


		// Figure out is the $product variable is numeric only (barcode) or alphanumeric aka Product!
		$is_barcode	=	false;
		
		
		if (is_numeric($product_or_barcode))	{	$is_barcode	=	true;	}

		$sql	=	"

			SELECT

			*

			FROM 

			geb_product

			WHERE

		";


		if ($is_barcode)
		{
			// Search by barcode: Fixed 13 Oct 2022
			$sql	.=	" prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar";
		}
		else
		{
			// Search for a product by name
			$sql	.=	" prod_code = :iprod_code ";
			
		}
		
		
		$columns_html	=	"";
		$details_html	=	"";


		// A fix for now... Look at it at a later stage for a better solution...
		$prod_pkey			=	0;
		$prod_code			=	"";
		$prod_desc			=	"";
		$prod_category		=	"";
		$prod_each_barcode	=	"";
		$prod_each_weight	=	"";
		$prod_case_barcode	=	"";
		$prod_case_qty		=	0;
		$prod_pall_qty		=	0;
		$prod_disabled		=	0;	// active!



		if ($stmt = $db->prepare($sql))
		{


			if ($is_barcode)
			{
				$stmt->bindValue(':iprod_each_bar',	$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':iprod_case_bar',	$product_or_barcode,	PDO::PARAM_STR);
			}
			else
			{
				$stmt->bindValue(':iprod_code',	$product_or_barcode,	PDO::PARAM_STR);
			}



			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

				// Grab product info... if there is anything!

				$prod_pkey				=	trim($row['prod_pkey']);
				$prod_code				=	trim($row['prod_code']);
				$prod_desc				=	trim($row['prod_desc']);
				$prod_category			=	trim($row['prod_category']);
				$prod_each_barcode		=	trim($row['prod_each_barcode']);
				$prod_each_weight		=	trim($row['prod_each_weight']);
				$prod_case_barcode		=	trim($row['prod_case_barcode']);
				$prod_case_qty			=	trim($row['prod_case_qty']);
				$prod_pall_qty			=	trim($row['prod_pall_qty']);
				$prod_disabled			=	trim($row['prod_disabled']);


			}



			$columns_html	.=	'<div class="columns">';

			$columns_html	.=	'<div class="column is-3">';

			// do a little check for the Status selectbox...
			// Note: ugly but works for now.
			$status_html	=	'<option value="0"';
			if ($prod_disabled == 0)	{	$status_html	.=	' selected';	}
			$status_html	.=	'>Active</option>';

			$status_html	.=	'<option value="1"';
			if ($prod_disabled == 1)	{	$status_html	.=	' selected';	}
			$status_html	.=	'>Disabled</option>';


			// General product info
			$details_html	=	'


<div class="field" style="'. $box_size_str .'">
	<p class="help">Product Code:</p>
	<div class="control">
		<input id="id_product_code" class="input is-normal" type="text" placeholder="e.g. CLSPMRD" value="'. $prod_code .'" name="id_product_code" required>
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">Description:</p>
	<div class="control">
		<input id="id_product_description" class="input is-normal" type="text" placeholder="Collection Sports Medium Red" value="'. $prod_desc . '" name="id_product_description">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">Category:</p>
	<div class="control">
		<input id="id_product_category" class="input is-normal" type="text" placeholder="GARDENWARE" value="'. $prod_category. '" name="id_product_category">
	</div>
</div>





<div class="field" style="'. $box_size_str .'">
	<p class="help">Status:</p>
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







			$columns_html	.=	$details_html;	// all entries for this column go here...
			$details_html	=	"";				// empty for the next run!
			$columns_html	.=	'</div>';


			// Second column
			$columns_html	.=	'<div class="column is-3">';

			// More technical column
			$details_html	=	'


<div class="field" style="'. $box_size_str .'">
	<p class="help">EACH Barcode:</p>
	<div class="control">
		<input id="id_each_barcode" class="input is-normal" type="text" placeholder="87341285732154" value="'. $prod_each_barcode .'" name="id_each_barcode">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">EACH Weight:	</p>
	<div class="control">
		<input id="id_each_weight" class="input is-normal" type="text" placeholder="4.25" value="'. $prod_each_weight . '" name="id_each_weight">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">CASE Barcode:</p>
	<div class="control">
		<input id="id_case_barcode" class="input is-normal" type="text" placeholder="51682361869185" value="'. $prod_case_barcode. '" name="id_case_barcode">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">CASE Qty:</p>
	<div class="control">
		<input id="id_case_qty" class="input is-normal" type="text" placeholder="12" value="'. $prod_case_qty. '" name="id_case_qty">
	</div>
</div>


<div class="field" style="'. $box_size_str .'">
	<p class="help">PALLET Qty:</p>
	<div class="control">
		<input id="id_pall_qty" class="input is-normal" type="text" placeholder="144" value="'. $prod_pall_qty. '" name="id_pall_qty">
	</div>
</div>';





			$columns_html	.=	$details_html;	// place the table in the column...
			$details_html	=	"";				// empty for the next run!

			$columns_html	.=	'</div>';




			// Third column.. buttons?
			$columns_html	.=	'<div class="column is-3">';


			// The &nbsp; in the <p class="help"></p> is just a "fix" so that everything aligns otherwise it looks odd...

			// More technical column
			$details_html	=	'


<div class="field" style="'. $box_size_str .'">
	<p class="help">&nbsp;</p>
	<div class="control">
		<button class="button manager_class is-fullwidth"  onclick="add_item();">Add</button>
	</div>
</div>



<div class="field" style="'. $box_size_str .'">
	<p class="help">&nbsp;</p>
	<div class="control">
		<button class="button manager_class is-fullwidth"  onclick="update_item();">Update</button>
	</div>
</div>



<div class="field" style="'. $box_size_str .'">
	<p class="help">&nbsp;</p>
	<div class="control">
		<input id="id_hidden" class="input is-normal" type="hidden" value="' . $prod_pkey . '">
	</div>
</div>




';







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
			echo "Details Query Failed!";
		}








	}		// Establishing the database connection - end bracket !
	catch(PDOException $e)
	{
		print_message(1, $e->getMessage());
	}


	echo '</div>';
echo '</section>';



	}
	else
	{
		// User has logged in but does not have the rights to access this page !
		include("not_logged_in.php");
	}


}
else
{

    // the user is not logged in.
    include("not_logged_in.php");

}

?>



<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>


