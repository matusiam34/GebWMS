<?php




/*
	sqlite_or_mariadb is a simple variable that changes from one database type to another.

	0	-	sqlite
	1	-	mariadb

*/

define("sqlite_or_mariadb", 0);	// set to sqlite for small installations !!


/*	
	Extra folder is needed to adjust the depth of the folder. For exaple :
	Apache can hold the website in /var/www or /var/www/html.
	Set the extra_db_folder to "../" when apache stores files in html folder !
*/

$extra_db_folder = "../";

/*

	Application folder name is typically something like
		ALWAYS /my to make it easy to manage serveral servers !
	They are stored in the root of the filesystem of the server hosting the particular
	application. 
 
*/

$application_folder		=	"my";


/*

	db_name is a variable used in the entire application ! It is critical to get this right
	as it does save the time to mess around with the path settings to the database !
	When you move the database to a different place make sure to alter this path as well !! 

*/

//define("db_name", "/" . $application_folder . "/wdrive/db/auto.sqlite");
//define("db_name", "/var/www/html/db/gebwms.db");

define("db_name", "db/gebwms.db");


/** database host, usually it's "127.0.0.1" or "localhost", some servers also need port info, like "127.0.0.1:8080" */
define("DB_HOST", "127.0.0.1");

/** name of the database. please note: database and database table are not the same thing! */
define("DB_NAME", "login");

/** user for your database. the user needs to have rights for SELECT, UPDATE, DELETE and INSERT.
/** By the way, it's bad style to use "root", but for development it will work */
define("DB_USER", "tom");

/** The password of the above user */
define("DB_PASS", "bombom33");




/**
 * handles the user login/logout/session
 */
class Login
{
	/**
	 * @var object $db_connection The database connection
	 */
	private $db_connection = null;
	/**
	 * @var array with translation of language strings
	 */
	private $lang = array();
	/**
	 * @var int $user_id The user's id
	 */
	private $user_id = null;
	/**
	 * @var string $user_name The user's name
	 */
	private $user_name = "";
	/**
	 * @var string $user_email The user's mail
	 */
	private $user_email = "";
	/**
	 * @var boolean $user_is_logged_in The user's login status
	 */
	private $user_is_logged_in = false;

	// by default set to 0 - in case the auth.php file does not exist!
	// 0 means only local user can login !
	private $auth_variable	=	0;


	/**
	 * 
	 * Provides status info like : Wrong password ! Empty username ! User does not exist ! etc
	 * 
	 */
	private $login_status = "";



	/**
	* @return bool
	*/
	public function is_session_started()
	{

		if (php_sapi_name() !== 'cli' )
		{

			if ( version_compare(phpversion(), '5.4.0', '>=') )
			{
				return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
			}
		}
		return FALSE;

	}




	/**
	 * the function "__construct()" automatically starts whenever an object of this class is created,
	 * you know, when you do "$login = new Login();"
	 */
	public function __construct()
	{


		// new way
		// Check if the session exist - if it does do nothing. If it does not EXIST -> Start one !
		if ($this->is_session_started() === FALSE ) session_start();


		// create internal reference to global array with translation of language strings
		$this->lang = & $GLOBALS['mylang'];

		// check the possible login actions:
		// 1. logout (happen when user clicks logout button)
		// 2. login via session data (happens each time user opens a page on your php project AFTER he has successfully logged in via the login form)
		// 3. login via cookie
		// 4. login via post data, which means simply logging in via the login form. after the user has submit his login/password successfully, his
		//    logged-in-status is written into his session data on the server. this is the typical behaviour of common login scripts.

		// if user tried to log out
		if (isset($_GET["logout"])) {
			$this->doLogout();

		// if user has an active session on the server
		} elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)) {
			$this->loginWithSessionData();
		
		// if user just submitted a login form
		} elseif (isset($_POST["login"])) {
			$this->loginWithPostData($_POST['user_name'], $_POST['user_password'] , 0);  // , $_POST['user_rememberme']); 0 - means do not remember !
		}


		$db_connection	=	null;

	}


    /**
     * Checks if database connection is opened.
     * If not, then tries to open it.
     */
	private function databaseConnection()
	{


		// connection already opened
		if ($this->db_connection != null)
		{
			return true;
		}
		else
		{
			// Create a database connection, using the constants from wdrive/config/config.php
			try
			{

				//
				// Check which type of auth is being used and act accordingly !
				//

				// Standard type - local database auth ! It can be sqlite / mariaDB !
				if ($this->getAuthType() == 0)
				{


					if (sqlite_or_mariadb == 0)
					{
						// Database name is stored in the config file in the db_name variable
						$this->db_connection = new PDO('sqlite:' . db_name);
					}
					else
					{
						$this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
					}


				}
				else
				{
					// Second type - myUSERS database auth ! It can be sqlite / mariaDB !
					// Part of an old old project now...
					if ($this->getAuthType() == 1)
					{

					}
				}

				return true;
			// If an error is catched, database connection failed
			}
			catch (PDOException $e)
			{
				$this->login_status = $this->lang['Database error'];
				return false;
			}

		}


	}

	/**
	 * Search into database for the user data of user_name specified as parameter
	 * @return user data as an object if existing user
	 * @return false if user_name is not found in the database
	 * TODO: @devplanete This returns two different types. Maybe this is valid, but it feels bad. We should rework this.
	 * TODO: @devplanete After some resarch I'm VERY sure that this is not good coding style! Please fix this.
	 */
	private function getUserData($user_name)
	{

		// if database connection opened -> take action and perform an SQL query to fetch all user data !
		if ($this->databaseConnection())
		{

			$query_user = $this->db_connection->prepare('


				SELECT

				*

				FROM users

				WHERE

				users.user_name	=	:iuser_name


			');

			$query_user->bindValue(':iuser_name',	trim($user_name),		PDO::PARAM_STR);
			$query_user->execute();
			return $query_user->fetchObject();

		}
		else
		{
			return false;
		}

	}



    /**
     * Logs in with S_SESSION data.
     * Technically we are already logged in at that point of time, as the $_SESSION values already exist.
     */
	private function loginWithSessionData()
	{
		$this->user_name = trim($_SESSION['user_name']);
		// set logged in status to true, because we just checked for this:
		// !empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)
		// when we called this method (in the constructor)
		$this->user_is_logged_in = true;
	}


	/**
	 * Logs in with the data provided in $_POST, coming from the login form
	 * @param $user_name
	 * @param $user_password
	 * @param $user_rememberme
	 */
	private function loginWithPostData($user_name, $user_password, $user_rememberme)
	{
		if (empty($user_name)) {
			$this->login_status = $this->lang['Empty username'];
        } else if (empty($user_password)) {
			$this->login_status = $this->lang['Empty password'];

		// if POST data (from login form) contains non-empty user_name and non-empty user_password
		}
		else
		{

			// Only username + password allowed to login !
			// getUserData will fetch all user data ! This can involve connecting to a 
			// myUSERS server for example via SSH...
			//
			$result_row = $this->getUserData($user_name);


			// if this user not exists
			if (!isset($result_row->user_id)) 
			{
				$this->login_status = $this->lang['User not exist'];
			}
			// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
			else if (! password_verify($user_password, $result_row->user_password_hash))
			{
//				$this->login_status = $this->lang['Wrong password'];
				$this->login_status = 'Wrong password. Try again';


			}
			else if ($result_row->user_active == 1)
			{
				$this->login_status = $this->lang['Account not activated'];
			}
			else if ($result_row->user_active == 2)
			{
				$this->login_status[] = $this->lang['Account suspended'];
			}
			else
			{

				//
				//	Here we create the session and save into a file on the server !
				//

				// write user data into PHP SESSION [a file on your server]

				$_SESSION['user_id']						=	$result_row->user_id;
				$_SESSION['user_name']						=	$result_row->user_name;

				$_SESSION['user_logged_in']					=	1;


				// The actual ACL lives here! It will be a mess for now but I need something flexible fast!
				$_SESSION['menu_prod_search']				=	$result_row->menu_prod_search;
				$_SESSION['menu_location_search']			=	$result_row->menu_location_search;
				$_SESSION['menu_goodsin']					=	$result_row->menu_goodsin;
				$_SESSION['menu_prod2loc']					=	$result_row->menu_prod2loc;
				$_SESSION['menu_recent_activity']			=	$result_row->menu_recent_activity;
				$_SESSION['menu_mgr_products']				=	$result_row->menu_mgr_products;
				$_SESSION['menu_adm_users']					=	$result_row->menu_adm_users;
				$_SESSION['menu_adm_warehouse']				=	$result_row->menu_adm_warehouse;
				$_SESSION['menu_adm_warehouse_loc']			=	$result_row->menu_adm_warehouse_loc;
				$_SESSION['menu_adm_category']				=	$result_row->menu_adm_category;

				//	Each user can be granted the ability to change their password and other settings... like Language :)
				$_SESSION['menu_my_account']				=	$result_row->menu_my_account;

				//	Language that the user has set! Default is English... however there will be few available to select... later on...
				$_SESSION['user_language']					=	$result_row->user_language;
				//	Warehouse that the user can operate in. Can be all!
				$_SESSION['user_warehouse']					=	$result_row->user_warehouse;





				// Declare user id, set the login status to true
				// Have no idea if I need these as they are the relic of the previous system.
				// Potentially the user_is_logged_in matters... Will have to play with it one day to see!
				$this->user_id				=	$result_row->user_id;
				$this->user_name			=	$result_row->user_name;
				$this->user_is_logged_in	=	true;

			}


		}


	}




	/**
	 * Delete all data needed for remember me cookie connection on client and server side
	 */
	private function deleteRememberMeCookie()
	{
		// set the rememberme-cookie to ten years ago (3600sec * 365 days * 10).
		// that's obivously the best practice to kill a cookie via php
		// @see http://stackoverflow.com/a/686166/1114320
		//setcookie('rememberme', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
	}


	/**
	 * Perform the logout, resetting the session
	 */
	public function doLogout()
	{
		$this->deleteRememberMeCookie();
		$_SESSION = array();
		session_destroy();
		$this->user_is_logged_in = false;
	}


	/**
	 * Simply return the current state of the user's login
	 * @return bool user's login status
	 */
	public function isUserLoggedIn()
	{
		return $this->user_is_logged_in;
	}


	/**
	 * Gets the username
	 * @return string username
	 */
	public function getUsername()
	{
		return $this->user_name;
	}


	/**
	 * Gets the $login_status string
	 */
	public function getLoginStatus()
	{
		return $this->login_status;
	}


	// comment on these please....
	public function getAuthType()
	{
		return $this->auth_variable;
	}

	public function setAuthType($input_data)
	{
		$this->auth_variable = $input_data;
	}

}
