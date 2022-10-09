<?php



/*
 * 
 *		Sys ID and what it does.
 * 
 * 		Each mySystem has an unique value that it gets from the admin. At the moment max is 255 combinations (8 bit integer)
 * 
 * 		So this is how it looks like
 * 
 * 		Range	Total		Product
 * 
 * 		0-31 	(32)	:	myServer	:	there can be many virtual machines so this makes sense
 * 		32-40	(8)		:	myUSERS		:	the users storage server is not expected to have many deployments
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 		Sys ID is being used every time to login. It is being read from the sysid.php file EVERY TIME a login takes place !
 * 		The integer value is being used to create a match in the set_acl table
 * 
 * 
 * 
 * 
*/







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
	public $login_status = "";




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



		// 20 April 2016
		// Include auth.php to select how the user login behaves.
		// 0 - local database
		// 1 - myUSERS server over SSH
		// 2 - DC auth (on myUSERS server that connects to DC...)

		// check if the auth.php file exists or not and if it does not
//		if (! file_exists('/' . $application_folder . '/wdrive/config/auth.php'))
/*
		if (! file_exists('/' . 'my' . '/wdrive/config/auth.php'))
		{

			// The file does NOT EXIST for some reason. 
			// When this happens make sure to set the auth method to 0 - which means that
			// the system will use the local database connection !
			$this->setAuthType(0);

		}
		else
			{

				// auth.php file exists so please read the mode which to use to validate the user !
				//include('/' . $application_folder . '/wdrive/config/auth.php');
				include('/' . 'my' . '/wdrive/config/auth.php');
				$this->setAuthType($auth_method);

			}
*/

		// old way
        // create/read session - does not matter if the session is already established or not...
        //session_start();


		// new way
		// Check if the session exist - if it does do nothing. If it does not EXIST -> Start one !
		if ($this->is_session_started() === FALSE ) session_start();



        // create internal reference to global array with translation of language strings
        // TODO: @devplanete: This should be $_GLOBALS !!?!?!?!?!?!
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


            // database query, getting all the info of the selected user


			/*
			 * 
			 *	24/11/2016 
			 * 
			 * 	Now each auth Type will need to include the SysID for the query.
			 * 	This means that the new Access Control System can be implemented - it includes the ability
			 * 	to allow certain users to have different access rights on lots of myProducts.
			 * 	For example: Adam can be a full adminstrator on myServer based in Coventry but will be just a simple user
			 * 	on myDAC (or any other myProduct).
			 *
			 * 
			 * 
			 *	HINT: Maybe add an option that when the script can't find the sysid.php file it will query the db to find
			 * 	the sysid ?!
			 * 
			 *  
			 * 
			*/

/*
			$curr_sysid	=	0;	// Set to 0 by default !!! This means localhost login only !

			// Check if the sysid file exist - if not we are going to be using the local login feature only !
			// For that sysid = 0 ! Any other value above that indicates a myProduct instance !
			if (! file_exists('/' . 'my' . '/wdrive/config/sysid.php'))
			{
				// The file does NOT EXIST for some reason. 
			}
			else
				{

					// sysid.php file exists so please read the mode which to use to validate the user !
					include('/' . 'my' . '/wdrive/config/sysid.php');
					$curr_sysid	=	$sys_id;
				}
*/



			// Standard type - local database auth ! It can be sqlite / mariaDB !
//			if ($this->getAuthType() == 0)
//			{

/*
				$query_user = $this->db_connection->prepare('



				SELECT 

				users.user_id, users.user_name, users.user_email, users.user_password_hash, users.user_active,
				user_firstname, user_surname,


				set_acl.db_acl_sysid,
				set_acl.db_acl_user_priv,
				set_acl.db_acl_services,
				set_acl.db_acl_settings,
				set_acl.db_acl_kvm,
				set_acl.db_acl_mylingo,
				set_acl.db_acl_dhcp,
				set_acl.db_acl_mycrf,
				set_acl.db_acl_mytherapy

				FROM users

				LEFT JOIN set_acl on users.user_id	=	set_acl.db_acl_user_id

				WHERE
				
				users.user_name	=	:iuser_name

				and
				
				set_acl.db_acl_sysid	=	:isysid

				LIMIT 1

				');
*/

				$query_user = $this->db_connection->prepare('


					SELECT
					
					*

					FROM users  
					
					WHERE
					
					users.user_name	=	:iuser_name


				');



				$query_user->bindValue(':iuser_name',	$user_name,		PDO::PARAM_STR);
				//$query_user->bindValue(':isysid',		$curr_sysid,	PDO::PARAM_INT);
				$query_user->execute();
				// get result row (as an object)
				return $query_user->fetchObject();
//			}
/*
			else
			{


				// Second type - myUSERS database auth ! It can be sqlite / mariaDB !
				// It needs to SSH to the myUSERS servers and fetch the user data and priviliges
				if ($this->getAuthType() == 1)
				{
				}
				elseif ($this->getAuthType() == 2)	// Active Directory Auth ! This only works when this machine is myUSERS !
				{
				}


			}
*/

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
        //$this->user_email = $_SESSION['user_email'];

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
            $result_row = $this->getUserData(trim($user_name));


            // if this user not exists
			if (! isset($result_row->user_id)) 
			{
				$this->login_status = $this->lang['User not exist'];
            }
            // using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
            else if (! password_verify($user_password, $result_row->user_password_hash))
			{
				$this->login_status = $this->lang['Wrong password'];
            }
            else if ($result_row->user_active == 0)
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
                $_SESSION['user_id']				=	$result_row->user_id;
                $_SESSION['user_name']				=	$result_row->user_name;
                $_SESSION['user_inventory']			=	$result_row->user_inventory;
                $_SESSION['user_priv']				=	$result_row->user_priv;
                $_SESSION['user_logged_in']			=	1;

				// This section gives values to the variables that are on the top of this file.
                // Declare user id, set the login status to true
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
		//$this->login_status = $this->lang['Logged out'];
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
     * @return string username
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

