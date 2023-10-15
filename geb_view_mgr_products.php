<?php


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
			});


		});




		// Get category B based on category A
		function get_all_category_b()
		{

			$.post('geb_ajax_category.php', { 

				action_code_js		:	1,
				action_format_js	:	1,
				action_disabled_js	:	0,
				cata_uid_js			:	get_Element_Value_By_ID('id_category_a')

			},

			function(output)
			{
				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{

					var len = obje.data.length;


					// jQuery - remove all entries
					$('#id_category_b').empty();

					// The first entry
					var opt = document.createElement('Option');
					document.getElementById('id_category_b').options.add(opt);
					opt.value = 0;
					opt.text = '<?php	echo $mylang['none'];	?>';

					if(len > 0)
					{

						for (var i = 0; i < len; i++)
						{
							var opt = document.createElement('Option');
							document.getElementById('id_category_b').options.add(opt);
							opt.value = obje.data[i].cat_pkey;
							opt.text = obje.data[i].cat_name;
						}

					}

				}
				else
				{
					alert(obje.msg);
				}

			}).fail(function() {
						// something went wrong
						$.alertable.error('102559', '<?php	echo $mylang['server_error'];	?>');
					});

		}



		// Add product to the system
		function add_product()
		{

			$.post('geb_ajax_product.php', { 

				action_code_js			:	2,

				product_code_js			:	get_Element_Value_By_ID('id_product_code'),
				product_description_js	:	get_Element_Value_By_ID('id_product_description'),
				product_category_a_js	:	get_Element_Value_By_ID('id_category_a'),
				product_category_b_js	:	get_Element_Value_By_ID('id_category_b'),
				each_barcode_js			:	get_Element_Value_By_ID('id_each_barcode'),
				each_weight_js			:	get_Element_Value_By_ID('id_each_weight'),
				case_barcode_js			:	get_Element_Value_By_ID('id_case_barcode'),
				case_qty_js				:	get_Element_Value_By_ID('id_case_qty'),
				min_qty_js				:	get_Element_Value_By_ID('id_min_qty'),
				max_qty_js				:	get_Element_Value_By_ID('id_max_qty'),
				disabled_js				:	get_Element_Value_By_ID('id_disabled')

			},

			function(output)
			{

				// Parse the json  !!
				var obje = jQuery.parseJSON(output);

				// Control = 0 => Green light to GO !!!
				if (obje.control == 0)
				{
					$.alertable.info(obje.control, obje.msg);
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









		//	Update product details
		function update_product()
		{

			$.post('geb_ajax_product.php', { 

				action_code_js			:	3,
				product_uid_js			:	get_Element_Value_By_ID('id_hidden'),
				product_code_js			:	get_Element_Value_By_ID('id_product_code'),
				product_description_js	:	get_Element_Value_By_ID('id_product_description'),
				product_category_a_js	:	get_Element_Value_By_ID('id_category_a'),
				product_category_b_js	:	get_Element_Value_By_ID('id_category_b'),
				each_barcode_js			:	get_Element_Value_By_ID('id_each_barcode'),
				each_weight_js			:	get_Element_Value_By_ID('id_each_weight'),
				case_barcode_js			:	get_Element_Value_By_ID('id_case_barcode'),
				case_qty_js				:	get_Element_Value_By_ID('id_case_qty'),
				min_qty_js				:	get_Element_Value_By_ID('id_min_qty'),
				max_qty_js				:	get_Element_Value_By_ID('id_max_qty'),
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
<body onLoad=''>


<?php

		// A little gap at the top to make it look better a notch.
		echo '<div class="blank_space_12px"></div>';

		echo '<section class="section is-paddingless">';
		echo	'<div class="container box has-background-light">';



		$page_form	=	'';

		$page_form	.=	'<form action="geb_view_mgr_products.php" method="get">';

			$page_form	.=	'<div class="field has-addons">';

				$page_form	.=	'<p class="control">';
				$page_form	.=		'<input class="input" type="text" id="product" name="product" placeholder="' . $mylang['product_code'] . '" value="' . $product_or_barcode . '">';
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



				// The menu!
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


		//	NOTE:	query all of the categories and store them in an array. Populate the corresponding entries from there! JD!


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
			$sql	.=	' prod_each_barcode = :iprod_each_bar OR prod_case_barcode = :iprod_case_bar ';
		}
		else
		{
			// Search for a product by name
			$sql	.=	' prod_code = :iprod_code ';
		}


		$columns_html	=	'';
		$details_html	=	'';


		// A fix for now... Look at it at a later stage for a better solution...
		$prod_pkey			=	0;
		$prod_code			=	"";
		$prod_desc			=	"";
		$prod_category_a	=	0;
		$prod_category_b	=	0;
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
				$stmt->bindValue(':iprod_each_bar',	$product_or_barcode,	PDO::PARAM_STR);
				$stmt->bindValue(':iprod_case_bar',	$product_or_barcode,	PDO::PARAM_STR);
			}
			else
			{
				$stmt->bindValue(':iprod_code',	$product_or_barcode,		PDO::PARAM_STR);
			}


			$stmt->execute();

			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{

				// Grab product info... if there is anything!
				$prod_pkey				=	leave_numbers_only($row['prod_pkey']);
				$prod_code				=	trim($row['prod_code']);
				$prod_desc				=	trim($row['prod_desc']);
				$prod_category_a		=	leave_numbers_only($row['prod_category_a']);
				$prod_category_b		=	leave_numbers_only($row['prod_category_b']);
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

					cat_pkey,
					cat_name,
					cat_a,
					cat_b

					FROM

					geb_category

					WHERE

					cat_disabled = 0

			';


			if ($stmt = $db->prepare($sql))
			{

				$stmt->execute();

				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// drop it into the final array...
					$category_arr[]	=	$row;
				}

			}



			//	Generate Category A HTML for the selectbox.
			$category_a_html	=	'<option value="0"';
			if ($prod_category_a == 0)	{	$category_a_html	.=	' selected';	}
			$category_a_html	.=	'>' . $mylang['none'] . '</option>';


			foreach ($category_arr as $category_item)
			{
				//	For category A only deal with ones that have 1 in the cat_a comlumn since that indicates that they are category A (Level 1 etc)
				if ($category_item['cat_a'] == 1)
				{
					//	Figure out if any of the items here are the category the product has = if so highlight it!
					$category_a_html	.=	'<option value="' . leave_numbers_only($category_item['cat_pkey']) . '"';
					if ($prod_category_a == leave_numbers_only($category_item['cat_pkey']))	{	$category_a_html	.=	' selected';	}
					$category_a_html	.=	'>' . $category_item['cat_name'] . '</option>';
				}
			}




			//	Generate Category B HTML for the selectbox based on the Category A... if that exists for a particular product.
			$category_b_html	=	'<option value="0"';
			if ($prod_category_b == 0)	{	$category_b_html	.=	' selected';	}
			$category_b_html	.=	'>' . $mylang['none'] . '</option>';



			foreach ($category_arr as $category_item)
			{
	
				//	Grab entries that are cat_b so they have the cat_a column equal to 0 + cat_b equals to the cat_pkey of the category A
				if
				(

					($category_item['cat_a'] == 0)

					AND

					($category_item['cat_b'] == $prod_category_a)

				)
				{
					//	Figure out if any of the items here are the category the product has = if so highlight it!
					$category_b_html	.=	'<option value="' . leave_numbers_only($category_item['cat_pkey']) . '"';
					if ($prod_category_b == leave_numbers_only($category_item['cat_pkey']))	{	$category_b_html	.=	' selected';	}
					$category_b_html	.=	'>' . $category_item['cat_name'] . '</option>';
				}
			}





			$columns_html	.=	'<div class="columns">';

			$columns_html	.=	'<div class="column is-3">';

				// do a little check for the Status selectbox...
				// Note: ugly but works for now.
				$status_html	=	'<option value="0"';
				if ($prod_disabled == 0)	{	$status_html	.=	' selected';	}
				$status_html	.=	'>' . $mylang['active'] . '</option>';

				$status_html	.=	'<option value="1"';
				if ($prod_disabled == 1)	{	$status_html	.=	' selected';	}
				$status_html	.=	'>' . $mylang['disabled'] . '</option>';


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
