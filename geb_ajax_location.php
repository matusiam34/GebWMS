<?php


//	FIX the fact that I can update the warehose to ANY WAREHOUSE! The warehouse uid needs to be not 0!
//	Same applies to adding! You can add a wh with uid = 0 aka ---- 

//	Action 1 needs sanitization of the uid input and few others checks!

/*

	Error code for the script!
	Script code		=	102

 
	//	Action code breakdown
	0	:	Get all locations (HTML format, disabled locations are included via SQL and marked with a greyish tint background)
	1	:	Get one location info (disabled location and warehouses ARE included! So it will grab any location!) + HTML details for category selectboxes!
	2	:	Add location!
	3	:	Update location details!

*/



// load the login class
require_once('lib_login.php');


$message_id		=	102999;		//	999:	default bad
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



		$action_code		=	leave_numbers_only($_POST['action_code_js']);	// this should be a number

		//	Get the company of the currently logged in user!
		$user_company_uid	=	leave_numbers_only($_SESSION['user_company']);

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



		//	Get all locations. The action code for this is 0
		if ($action_code == 0)
		{



			$sql	=	'


				SELECT

				geb_warehouse.wh_code,
				geb_location.loc_pkey,
				geb_location.loc_code,

				geb_location.loc_barcode,
				geb_location.loc_function,
				geb_location.loc_type,
				geb_location.loc_blocked,
				geb_location.loc_note,
				geb_location.loc_magic_product,
				geb_location.loc_disabled


				FROM  geb_location

				INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey


				WHERE

				geb_warehouse.wh_disabled = 0

				AND
				
				geb_location.loc_owner = :sowner


				ORDER BY wh_code, loc_code


			';

				$html_results		=	'';

				$html_results		.=	'<thead>';

				$html_results		.=	'<tr>';
				$html_results		.=		'<th>UID</th>';
				$html_results		.=		'<th>' . $mylang['warehouse'] . '</th>';
				$html_results		.=		'<th>' . $mylang['location'] . '</th>';
				$html_results		.=		'<th>' . $mylang['barcode'] . '</th>';
				$html_results		.=		'<th>' . $mylang['note'] . '</th>';
				$html_results		.=	'</tr>';

				$html_results		.=	'</thead>';

				$html_results		.=	'<tbody style="max-height:400px;">';

				if ($stmt = $db->prepare($sql))
				{

					$stmt->bindValue(':sowner',		$user_company_uid,		PDO::PARAM_INT);
					$stmt->execute();


					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{

						//	<AI>
						$tr_style = (leave_numbers_only($row['loc_disabled']) == 1) ? ' style="background-color: #d9d9d9;"' : '';

						$html_results .= '<tr' . $tr_style . '>';
						$html_results .= '<td>' . trim($row['loc_pkey']) . '</td>';
						$html_results .= '<td>' . trim($row['wh_code']) . '</td>';

						$loc_details_arr = decode_loc
						(
							leave_numbers_only($row['loc_function']),
							leave_numbers_only($row['loc_type']),
							leave_numbers_only($row['loc_blocked']),
							$loc_function_codes_arr,
							$loc_type_codes_arr
						);

						$html_results .= '<td style="' . $loc_details_arr[1] . '">' . trim($row['loc_code']) . ' (' . $loc_details_arr[0] . ')</td>';
						$html_results .= '<td>' . trim($row['loc_barcode']) . '</td>';
						$html_results .= '<td>' . trim($row['loc_note']) . '</td>';
						$html_results .= '</tr>';

						//	</AI>

					}

					$html_results		.=	'</tbody>';
					$message_id		=	0;	//	all went well

				}

		}	//	Action 0 end!


		//	Get details of one location!

		else if ($action_code == 1)
		{

			if
			(
				is_it_enabled($_SESSION['menu_adm_warehouse_loc'])
			)
			{

				$category_arr	=	array();

				//	Get the UID of the location hopefully provided from the frontend.
				$loc_uid		=	leave_numbers_only($_POST['loc_uid_js']);	//	this should be a number

				$sql	=	'


					SELECT

					geb_warehouse.wh_pkey,
					geb_location.loc_pkey,
					geb_location.loc_owner,
					geb_location.loc_code,

					geb_location.loc_barcode,
					geb_location.loc_function,
					geb_location.loc_type,
					geb_location.loc_blocked,
					geb_location.loc_cat_a,
					geb_location.loc_cat_b,
					geb_location.loc_cat_c,
					geb_location.loc_cat_d,
					geb_location.loc_magic_product,
					geb_location.loc_max_qty,
					geb_location.loc_note,
					geb_location.loc_disabled,

					geb_product.prod_code


					FROM  geb_location

					INNER JOIN geb_warehouse ON geb_location.loc_wh_pkey = geb_warehouse.wh_pkey
					LEFT JOIN geb_product ON geb_location.loc_magic_product = geb_product.prod_pkey


					WHERE

					geb_location.loc_pkey = :sloc

					AND

					geb_location.loc_owner = :sowner


					ORDER BY wh_code, loc_code

				';


				if ($stmt = $db->prepare($sql))
				{


					$stmt->bindValue(':sloc',		$loc_uid,				PDO::PARAM_INT);
					$stmt->bindValue(':sowner',		$user_company_uid,		PDO::PARAM_INT);
					$stmt->execute();

					while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{

						$data_results	=	array(
						
							'wh_pkey'			=>	leave_numbers_only($row['wh_pkey']),
							'loc_pkey'			=>	leave_numbers_only($row['loc_pkey']),
							'loc_owner'			=>	leave_numbers_only($row['loc_owner']),
							'loc_code'			=>	trim($row['loc_code']),
							'loc_barcode'		=>	trim($row['loc_barcode']),
							'loc_function'		=>	leave_numbers_only($row['loc_function']),
							'loc_type'			=>	leave_numbers_only($row['loc_type']),
							'loc_blocked'		=>	leave_numbers_only($row['loc_blocked']),
							'loc_max_qty'		=>	leave_numbers_only($row['loc_max_qty']),
							'loc_cat_a'			=>	leave_numbers_only($row['loc_cat_a']),
							'loc_cat_b'			=>	leave_numbers_only($row['loc_cat_b']),
							'loc_cat_c'			=>	leave_numbers_only($row['loc_cat_c']),
							'loc_cat_d'			=>	leave_numbers_only($row['loc_cat_d']),
							'loc_note'			=>	trim($row['loc_note']),
							'loc_disabled'		=>	leave_numbers_only($row['loc_disabled']),
							'prod_code'			=>	trim($row['prod_code'])

						);


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
							
							geb_category_a.cat_a_owner = :sowner


					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sowner',		$user_company_uid,		PDO::PARAM_INT);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							// drop it into the final array...
							$category_arr[]	=	$row;
						}

					}


					// Generate HTML for Category A, B, C and D select box
					$category_A_options = [];
					$category_B_options = [];
					$category_C_options = [];
					$category_D_options = [];

					// Populate category options arrays
					foreach ($category_arr as $category)
					{

						$category_A_options[$category['cat_a_pkey']] = $category['cat_a_name'];

						// Check if the current category B is associated with the selected category A
						if ($category['cat_b_a_level'] == $data_results['loc_cat_a'])
						{
							$category_B_options[$category['cat_b_pkey']] = $category['cat_b_name'];
						}

						// Check if the current category C is associated with the selected category B
						if ($category['cat_c_b_level'] == $data_results['loc_cat_b'])
						{
							$category_C_options[$category['cat_c_pkey']] = $category['cat_c_name'];
						}

						// Check if the current category D is associated with the selected category C
						if ($category['cat_d_c_level'] == $data_results['loc_cat_c'])
						{
							$category_D_options[$category['cat_d_pkey']] = $category['cat_d_name'];
						}





					}


					$data_results['cat_a_html']	=	generate_select_options($category_A_options, leave_numbers_only($data_results['loc_cat_a']), $mylang['none']);
					$data_results['cat_b_html']	=	generate_select_options($category_B_options, leave_numbers_only($data_results['loc_cat_b']), $mylang['none']);
					$data_results['cat_c_html']	=	generate_select_options($category_C_options, leave_numbers_only($data_results['loc_cat_c']), $mylang['none']);
					$data_results['cat_d_html']	=	generate_select_options($category_D_options, leave_numbers_only($data_results['loc_cat_d']), $mylang['none']);

					$message_id		=	0;	//	all went well



				}



			}		//	Permissions check!

		}	//	Action 1 end!


		//	Add one location to the system!

		else if ($action_code == 2)
		{

			//	Only an Admin of this system can add!
			if
			(

				(
					is_it_enabled($_SESSION['menu_adm_warehouse_loc'])
				)

				AND

				(
					can_user_add($_SESSION['menu_adm_warehouse_loc'])
				)

			)
			{


				// Data from the user to process...
				$warehouse		=	leave_numbers_only($_POST['warehouse_js']);
				$loc_owner		=	leave_numbers_only($_POST['owner_uid_js']);		//	geb_company table!
				$location		=	trim($_POST['location_js']);
				$barcode		=	trim($_POST['barcode_js']);
				$type			=	leave_numbers_only($_POST['type_js']);
				$cat_a			=	leave_numbers_only($_POST['loc_cat_a_js']);
				$cat_b			=	leave_numbers_only($_POST['loc_cat_b_js']);
				$cat_c			=	leave_numbers_only($_POST['loc_cat_c_js']);
				$cat_d			=	leave_numbers_only($_POST['loc_cat_d_js']);
				$magic_product	=	trim($_POST['magic_product_js']);				//	Will need to convert string into a product UID!
				$max_qty		=	leave_numbers_only($_POST['max_qty_js']);
				$function		=	leave_numbers_only($_POST['function_js']);
				$blocked		=	leave_numbers_only($_POST['blocked_js']);
				$loc_desc		=	trim($_POST['loc_desc_js']);
				$disabled		=	leave_numbers_only($_POST['disabled_js']);


				//	Add some validation of the input fields here!


				if (strlen($location) >= 1)	//	I am allowing the name of the location to be 1 character long!
				{

					$db->beginTransaction();


					//	Since two warehouses can have the same location names I need to check for barcodes instead!
					//	I can't afford to have two identical barcodes since that will cause chaos!

					$found_match		=	0;	//	0 = all good!
					$magic_product_uid	=	0;	//	default! Means no product has been provided. I am converting the string to a number!

					//	TO DO: Also, can't have duplicate name of location within a warehouse!!!

					//
					//	Seek out for duplicate barcode entry across ALL locations!
					//	Also check if the operator is not trying to add an identically named location name to the same warehouse!
					//	I can't have two identical location names like C113A in a Coventry warehouse.
					//
					$sql	=	'

						SELECT

						loc_pkey,
						loc_wh_pkey,
						loc_code,
						loc_barcode

						FROM geb_location

						WHERE

						loc_barcode = :sloc_barcode

						OR

						(

							(loc_wh_pkey = :sloc_warehouse_pkey)

							AND
							
							(loc_code = :sloc_code)
						
						)

					';


					if ($stmt = $db->prepare($sql))
					{

						$stmt->bindValue(':sloc_barcode',			$barcode,		PDO::PARAM_STR);
						$stmt->bindValue(':sloc_warehouse_pkey',	$warehouse,		PDO::PARAM_INT);
						$stmt->bindValue(':sloc_code',				$location,		PDO::PARAM_STR);
						$stmt->execute();

						while($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{

							//	if the warehouse code matches and location name matches = the operator is trying to add the same
							//	location name to the same warehouse. It is a NO NO!
							if (($row['loc_wh_pkey'] == $warehouse) AND ($row['loc_code'] == $location))
							{
								$found_match	=	1;	//	Trying to add identical location name in the same warehouse! Not cool!
							}
							else if  (($row['loc_barcode'] == $barcode))
							{
								$found_match	=	2;	//	The barcode you entered is already allocated to a location!
							}

						}

					}
					// show an error if the query has an error?
					else
					{
					}


					//	Now... Since the operator is providing me potentially with a Magic Product I need to query the geb_product
					//	table to see if there is a match. If there is a match ===>>> get the UID (prod_pkey) of that product and use it
					//	to insert!


					if (strlen($magic_product) >= 1)	//	Some product has been provided...
					{

						$magic_arr	=	array();	//	all of the magic product matches that I can find.
													//	I will check if there is more than one product code found etc
													//	Better to check for errors just to be on the safe side!

						//	Run the query and see what I get!

						$sql	=	'

							SELECT

							prod_pkey

							FROM geb_product

							WHERE

							prod_code = :sprod_code

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sprod_code',		$magic_product,		PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$magic_arr[]	=	$row;
							}



							if (count($magic_arr) == 1)
							{
								//	!!! WINNER !!!
								//	One product found! Allocated the $magic_product_uid to the prod_pkey
								$magic_product_uid	=	$magic_arr[0]['prod_pkey'];
								//	!!! WINNER !!!
							}

							elseif (count($magic_arr) == 0)	//	No match based on the product name has been found in the geb_product table!
							{
								$found_match	=	3;	//	No match has been found!
							}

							elseif (count($magic_arr) > 1)
							{
								$found_match	=	4;	//	Multiple products with the same name! Needs to be investigated by the sys admin!
							}


						}
						// show an error if the query has an error?
						else
						{
						}

					}




					//	0	means no issues!
					if ($found_match == 0)
					{

						$sql	=	'


								INSERT
								
								INTO

								geb_location
								
								(
									loc_wh_pkey,
									loc_owner,
									loc_code,
									loc_barcode,
									loc_function,
									loc_type,
									loc_magic_product,
									loc_max_qty,
									loc_cat_a,
									loc_cat_b,
									loc_cat_c,
									loc_cat_d,
									loc_blocked,
									loc_note,
									loc_disabled
								) 

								VALUES

								(
									:iloc_wh_pkey,
									:iloc_owner,
									:iloc_code,
									:iloc_barcode,
									:iloc_function,
									:iloc_type,
									:iloc_magic_product,
									:iloc_max_qty,
									:iloc_cat_a,
									:iloc_cat_b,
									:iloc_cat_c,
									:iloc_cat_d,
									:iloc_blocked,
									:iloc_note,
									:iloc_disabled
								)

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':iloc_wh_pkey',			$warehouse,				PDO::PARAM_INT);
							$stmt->bindValue(':iloc_owner',				$loc_owner,				PDO::PARAM_INT);
							$stmt->bindValue(':iloc_code',				$location,				PDO::PARAM_STR);
							$stmt->bindValue(':iloc_barcode',			$barcode,				PDO::PARAM_STR);
							$stmt->bindValue(':iloc_function',			$function,				PDO::PARAM_INT);
							$stmt->bindValue(':iloc_type',				$type,					PDO::PARAM_INT);
							$stmt->bindValue(':iloc_magic_product',		$magic_product_uid,		PDO::PARAM_INT);
							$stmt->bindValue(':iloc_max_qty',			$max_qty,				PDO::PARAM_INT);

							$stmt->bindValue(':iloc_cat_a',				$cat_a,					PDO::PARAM_INT);
							$stmt->bindValue(':iloc_cat_b',				$cat_b,					PDO::PARAM_INT);
							$stmt->bindValue(':iloc_cat_c',				$cat_c,					PDO::PARAM_INT);
							$stmt->bindValue(':iloc_cat_d',				$cat_d,					PDO::PARAM_INT);



							$stmt->bindValue(':iloc_blocked',			$blocked,				PDO::PARAM_INT);
							$stmt->bindValue(':iloc_note',				$loc_desc,				PDO::PARAM_STR);
							$stmt->bindValue(':iloc_disabled',			$disabled,				PDO::PARAM_INT);

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
							$message_id		=	102200;
							$message2op		=	$mylang['location_already_exists'];
						}
						elseif ($found_match == 2)
						{
							$message_id		=	102201;
							$message2op		=	$mylang['barcode_already_exists'];
						}


						elseif ($found_match == 3)
						{
							$message_id		=	102201;
							$message2op		=	'No matching product found!!!';//$mylang['barcode_already_exists'];
						}
						elseif ($found_match == 4)
						{
							$message_id		=	102201;
							$message2op		=	'Multiple products with the same name found';//$mylang['barcode_already_exists'];
						}


					}


				}
				else
				{
					//	Name is null = tell the user that they need to do better!
					$message_id		=	102202;
					$message2op		=	$mylang['name_too_short'];
				}



			}
			
		}	//	Action 2 end!


		//	Update one location!

		else if ($action_code == 3)
		{

			//	Only an Admin of this system can update a location!
			if
			(

				(is_it_enabled($_SESSION['menu_adm_warehouse_loc']))

				AND

				(can_user_update($_SESSION['menu_adm_warehouse_loc']))

			)
			{

				// Data from the user to process...
				$loc_owner		=	leave_numbers_only($_POST['owner_uid_js']);		//	geb_company table!
				$warehouse		=	leave_numbers_only($_POST['warehouse_js']);
				$location		=	trim($_POST['location_js']);
				$barcode		=	trim($_POST['barcode_js']);
				$type			=	leave_numbers_only($_POST['type_js']);
				$cat_a			=	leave_numbers_only($_POST['cat_a_js']);
				$cat_b			=	leave_numbers_only($_POST['cat_b_js']);
				$cat_c			=	leave_numbers_only($_POST['cat_c_js']);
				$cat_d			=	leave_numbers_only($_POST['cat_d_js']);
				$loc_function	=	leave_numbers_only($_POST['function_js']);
				$blocked		=	leave_numbers_only($_POST['blocked_js']);
				$loc_desc		=	trim($_POST['loc_desc_js']);
				$magic_product	=	trim($_POST['magic_product_js']);				//	Will need to convert string into a product UID!
				$max_qty		=	leave_numbers_only($_POST['max_qty_js']);
				$disabled		=	leave_numbers_only($_POST['disabled_js']);
				$loc_uid		=	leave_numbers_only($_POST['loc_uid_js']);	//	this should be a number

				$magic_product_uid	=	0;	//	default! Means no product has been provided. I am converting the string to a number!



				if ($loc_uid >= 0)
				{

					if (strlen($location) >= 1)	//	I am allowing the name of the location to be 1 character long!
					{

						$db->beginTransaction();


						//	Since two warehouses can have the same location names I need to check for barcodes instead!
						//	I can't afford to have two identical barcodes since that will cause chaos!

						$found_match	=	0;	//	0 = all good!

						//	TO DO: Also, can't have duplicate name of location within a warehouse!!!

						//
						// Seek out for duplicate barcode entry across ALL locations!
						//
						$sql	=	'

							SELECT

							loc_pkey,
							loc_wh_pkey,
							loc_code,
							loc_barcode

							FROM geb_location

							WHERE

							loc_barcode = :sloc_barcode

							OR

							(

								(loc_wh_pkey = :sloc_warehouse_pkey)

								AND
								
								(loc_code = :sloc_code)
							
							)


						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sloc_barcode',			$barcode,			PDO::PARAM_STR);
							$stmt->bindValue(':sloc_warehouse_pkey',	$warehouse,			PDO::PARAM_INT);
							$stmt->bindValue(':sloc_code',				$location,			PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{

								if ($row['loc_pkey'] == $loc_uid)
								{
									//	Here is the original entry... I am not worried about this one!

								}
								else
								{

									if
									(
										($row['loc_wh_pkey'] == $warehouse) AND ($row['loc_code'] == $location)
										AND
										($row['loc_pkey'] <> $loc_uid)
									)
									{
										//	Same named entry exists on this warehouse!
										$found_match	=	1;
										
									}	else if
									(
										($row['loc_barcode'] == $barcode) AND ($row['loc_pkey'] <> $loc_uid)
									)
									{
										$found_match	=	2;	//	The barcode you entered is already allocated to a location!
									}



								}


							}

						}
						// show an error if the query has an error?
						else
						{
							//	Need to do something about this some time...
						}





					//	This entire Magic Product section of code needs to be redone. Once done here it will have to be done in the
					//	add section as well! For now it will do as it works, but not looking good me thinks!


					if (strlen($magic_product) >= 1)	//	Some product has been provided...
					{

						$magic_arr	=	array();	//	all of the magic product matches that I can find.
													//	I will check if there is more than one product code found etc
													//	Better to check for errors just to be on the safe side!

						//	Run the query and see what I get!

						$sql	=	'

							SELECT

							prod_pkey

							FROM geb_product

							WHERE

							prod_code = :sprod_code

						';


						if ($stmt = $db->prepare($sql))
						{

							$stmt->bindValue(':sprod_code',		$magic_product,		PDO::PARAM_STR);
							$stmt->execute();

							while($row = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								$magic_arr[]	=	$row;
							}



							if (count($magic_arr) == 1)
							{
								//	!!! WINNER !!!
								//	One product found! Allocated the $magic_product_uid to the prod_pkey
								$magic_product_uid	=	$magic_arr[0]['prod_pkey'];
								//	!!! WINNER !!!
							}

							elseif (count($magic_arr) == 0)	//	No match based on the product name has been found in the geb_product table!
							{
								$found_match	=	3;	//	No match has been found!
							}

							elseif (count($magic_arr) > 1)
							{
								$found_match	=	4;	//	Multiple products with the same name! Needs to be investigated by the sys admin!
							}


						}
						// show an error if the query has an error?
						else
						{
						}

					}






						//	0	means no issues!
						if ($found_match == 0)
						{

							$sql	=	'

								UPDATE

								geb_location

								SET

								loc_wh_pkey			=		:uloc_wh_pkey,
								loc_owner			=		:uloc_owner,
								loc_code			=		:uloc_code,
								loc_barcode			=		:uloc_barcode,
								loc_function		=		:uloc_function,
								loc_type			=		:uloc_type,
								loc_magic_product	=		:uloc_magic_product,
								loc_max_qty			=		:uloc_max_qty,
								loc_cat_a			=		:uloc_cat_a,
								loc_cat_b			=		:uloc_cat_b,
								loc_cat_c			=		:uloc_cat_c,
								loc_cat_d			=		:uloc_cat_d,
								loc_blocked			=		:uloc_blocked,
								loc_note			=		:uloc_note,
								loc_disabled		=		:uloc_disabled

								WHERE

								loc_pkey	 =	:sloc_pkey

							';


							if ($stmt = $db->prepare($sql))
							{

								$stmt->bindValue(':uloc_wh_pkey',	$warehouse,						PDO::PARAM_INT);
								$stmt->bindValue(':uloc_owner',		$loc_owner,						PDO::PARAM_INT);
								$stmt->bindValue(':uloc_code',		$location,						PDO::PARAM_STR);
								$stmt->bindValue(':uloc_barcode',	$barcode,						PDO::PARAM_STR);
								$stmt->bindValue(':uloc_function',	$loc_function,					PDO::PARAM_INT);
								$stmt->bindValue(':uloc_type',		$type,							PDO::PARAM_INT);
								$stmt->bindValue(':uloc_magic_product',		$magic_product_uid,		PDO::PARAM_INT);
								$stmt->bindValue(':uloc_max_qty',			$max_qty,				PDO::PARAM_INT);
								$stmt->bindValue(':uloc_cat_a',		$cat_a,							PDO::PARAM_INT);
								$stmt->bindValue(':uloc_cat_b',		$cat_b,							PDO::PARAM_INT);
								$stmt->bindValue(':uloc_cat_c',		$cat_c,							PDO::PARAM_INT);
								$stmt->bindValue(':uloc_cat_d',		$cat_d,							PDO::PARAM_INT);
								$stmt->bindValue(':uloc_blocked',	$blocked,						PDO::PARAM_INT);
								$stmt->bindValue(':uloc_note',		$loc_desc,						PDO::PARAM_STR);
								$stmt->bindValue(':uloc_disabled',	$disabled,						PDO::PARAM_INT);

								$stmt->bindValue(':sloc_pkey',		$loc_uid,						PDO::PARAM_INT);
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
								$message_id		=	102203;
								$message2op		=	$mylang['location_already_exists'];
							}
							elseif ($found_match == 2)
							{
								$message_id		=	102204;
								$message2op		=	$mylang['barcode_already_exists'];
							}

							elseif ($found_match == 3)
							{
								$message_id		=	102201;
								$message2op		=	'No matching product found!!!';//$mylang['barcode_already_exists'];
							}
							elseif ($found_match == 4)
							{
								$message_id		=	102201;
								$message2op		=	'Multiple products with the same name found';//$mylang['barcode_already_exists'];
							}


						}

					}
					else
					{
						$message_id		=	102205;
						$message2op		=	$mylang['name_too_short'];
					}

				}
				else
				{
					$message_id		=	102206;
					$message2op		=	$mylang['incorrect_uid'];
				}


			}
			
		}	//	Action 3 end!







	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	102666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Grab all locations
		print_message_html_payload($message_id, $message2op, $html_results);
		break;
		case 1:	//	Get one location details
		print_message_data_payload($message_id, $message2op, $data_results);
		break;
		case 2:	//	Add location
		print_message($message_id, $message2op);
		break;
		case 3:	//	Update location
		print_message($message_id, $message2op);
		break;
		default:
		print_message(102945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
