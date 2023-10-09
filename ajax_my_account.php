<?php




/*

	Error code for the script!
	Script code		=	104

 
	//	Action code breakdown
	0	:	Update language selection!




*/



// load the login class
require_once('lib_login.php');


$message_id		=	101999;		//	999:	default bad
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


		//	Update language for GebWMS
		if
		(

			($action_code == 0)

		)
		{

			$user_uid		=	leave_numbers_only($_SESSION['user_id']);		// remove anything that is not a number
			$language_str	=	trim($_POST['language_js']);


			$db->beginTransaction();


			$sql	=	'


				UPDATE

				users

				SET

				user_language		=	:uuser_language

				WHERE

				user_id	 =	:suser_id


			';


			if ($stmt = $db->prepare($sql))
			{

				$stmt->bindValue(':uuser_language',			$language_str,		PDO::PARAM_STR);
				$stmt->bindValue(':suser_id',				$user_uid,			PDO::PARAM_INT);

				$stmt->execute();
				$db->commit();

				$message_id		=	0;	//	all went well
				$message2op		=	$mylang['success'];

			}


		}	//	Action 0 end!




	}
	catch(PDOException $e)
	{
		$db->rollBack();
		$message2op		=	$e->getMessage();
		$message_id		=	104666;
	}


	$db	=	null;


	switch ($action_code) {
		case 0:	//	Update language!
		print_message($message_id, $message2op);
		break;
		default:
		print_message(104945, 'X2X');
	}



} else {
    // the user is not logged in. you can do whatever you want here.
    include('not_logged_in.php');
}



?>
