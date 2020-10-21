<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/VGS_i5_Conn.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/Group_User_Xref.php';
require_once '../model/Auth_Profile_Xref.php';
require_once 'Zend/Loader/Autoloader.php';

class Security 
{
	public $adServer = "192.168.11.12";
	public $ibmServer = "192.168.11.11";
	/**
	 * $redirectOnDeny - if true, permission checking will redirect to an error page
	 * when the user is denied permission to a resource - If false, permission checking
	 * will return false when the user is denied permission to a resource, in which case
	 * the application should decide how to handle denied permission.
	 * @var boolean 
	 */
	private $redirectOnDeny = true;
	/**
	 * $usersGroups This will be populated with the list of group profiles this user belongs to -
	 * It is stored statically so that we only have to load it once per request.    
	 * @var static array
	 */
	private static $usersGroups = null;
	/**
	 * requiredAuthorities is a multidimensional array to store the permissions required 
	 * for functional areas of the system, which might be shared among several applications.
	 * This array is keyed by functional area and operation:
	 * eg, $requiredAuthorities['WO']['UPDATE'] holds the authorities to check for work order 
	 * update permission. The entries should be most-specific to least-specific.   
	 * @var multi-dim array
	 */
	private $requiredAuthorities = array(
		'WO' => array(
			'CREATE' => array('WO_CRT', 'WO_ALL', '*ALL_CRT'),
			'UPDATE' => array('WO_UPD', 'WO_ALL', '*ALL_UPD'),
			'INQUIRY' => array('WO_INQ', 'WO_ALL', '*ALL_INQ'),
			'CANCEL' => array('WO_CANCEL', 'WO_CANCEL_PND', 'WO_ALL'),
			'CANCEL_PND' => array('WO_CANCEL_PND')
		),
		'WEF' => array(
			'CREATE' => array('WEF_CRT', 'WEF_ALL', 'WEF_UPLOAD', '*ALL_CRT'),
			'UPDATE' => array('WEF_UPD', 'WEF_ALL', '*ALL_UPD'),
			'INQUIRY' => array('WEF_INQ', 'WEF_ALL', '*ALL_INQ'),
			'DELETE' => array('WEF_DELETE', 'WEF_ALL')
		),
		'SEC' => array(
			'CREATE' => array('SEC_ALL'),
			'UPDATE' => array('SEC_ALL'),
			'INQUIRY' => array('SEC_INQ', 'SEC_ALL'),
			'DELETE' => array('SEC_ALL')
		),
		'DD' => array(
			'CREATE' => array('DDLISTS_CRT', 'DDLISTS_ALL', '*ALL_CRT'),
			'UPDATE' => array('DDLISTS_UPD', 'DDLISTS_ALL', '*ALL_UPD'),
			'INQUIRY' => array('DDLISTS_INQ', 'DDLISTS_ALL', '*ALL_INQ'),
			'DELETE' => array('DDLISTS_DEL', 'DDLISTS_ALL')
		),
		'PIPE' => array(
			'CREATE' => array('PIPE_CRT', 'PIPE_ALL', '*ALL_CRT'),
			'UPDATE' => array('PIPE_UPD', 'PIPE_ALL', '*ALL_UPD'),
			'INQUIRY' => array('PIPE_INQ', 'PIPE_ALL', '*ALL_INQ'),
			'DELETE' => array('PIPE_DELETE', 'PIPE_ALL')
		),
		'PROJ' => array(
			'CREATE' => array('PROJ_CRT', 'PROJ_ALL', '*ALL_CRT'),
			'UPDATE' => array('PROJ_UPD', 'PROJ_ALL', '*ALL_UPD'),
			'INQUIRY' => array('PROJ_INQ', 'PROJ_ALL', '*ALL_INQ'),
			'DELETE' => array('PROJ_DELETE', 'PROJ_ALL')
		),
		'SVC' => array(
			'CREATE' => array('SVC_CRT', 'SVC_ALL', '*ALL_CRT'),
			'UPDATE' => array('SVC_UPD', 'SVC_ALL', '*ALL_UPD'),
			'DELETE' => array('SVC_DEL', 'SVC_ALL', '*ALL_UPD'),
			'INQUIRY' => array('SVC_INQ', 'SVC_ALL', '*ALL_INQ'),
		)
	);
	
	function __construct() {
		$this->redirectOnDeny = true;
		if (self::$usersGroups == NULL) {
//			echo "loading user's groups...<br>";
			$this->loadUsersGroups();
		}
	}

	public function setRedirectOnDeny ( $setValue ) {
		$this->redirectOnDeny = $setValue;
	}
	
	private function loadUsersGroups() {
    	if (isset($_SESSION['current_user'])) {
    		$conn = VGS_DB_Conn_Singleton::getInstance();
    		$gux = new Group_User_Xref($conn);
    		self::$usersGroups = $gux->getGroupsForUser($_SESSION['current_user']);
//	    	self::$usersGroups = $this->getGroupsForUser($_SESSION['current_user']);
    	}
	}

//    public function getGroupsForUser( $user ) {
//    	/** NOTE: Database access to GROUP_USER_XREF is performed here instead 
//    	 * of a class that extends VGS_DB_Table. This is done to avoid the 
//    	 * possibility of recursively instantiating Security(), which was causing
//    	 * an infinite loop in initial testing.  
//    	 */
//    	$conn = VGS_DB_Conn_Singleton::getInstance();
//    	$select = new VGS_DB_Select();
//    	
//		$select->from = 'GROUP_USER_XREF';
//		$select->andWhere("UG_USER_ID = ?", trim($user) );
//
//		$table = new VGS_DB_Table($conn);
//		$table->execListQuery($select->toString(), $select->parms);
//		
//		$rows = array();
//		while ($row = db2_fetch_assoc( $table->stmt )) {
//			$rows[] = $row['UG_GROUP_ID'];
//		};
//		return $rows;
//    }
	
	public function checkAuthoritiesPermission(array $authorities, $blnShowErrorPage = true ) {
    	$conn = VGS_DB_Conn_Singleton::getInstance();
		$apx = new Auth_Profile_Xref($conn);
		
		// If user is sys admin, all permissions are granted.
		$sysadminPermission = trim($apx->getPermission('*SYSADMIN', $_SESSION['current_user']));
		if ('ALLOW' == $sysadminPermission) {
			return true; // if sysadmin user, look no further
		}
		
		// Check user-specific authority first. If this is found, it overrides
		// any group-level authorities.
		$permissionAtUserLevel = '';
		$isUserPermitted = false;
		
		foreach ($authorities as $authority) {
			$userPermission = $apx->getPermission($authority, $_SESSION['current_user']);
// 			pre_dump("permission = '$userPermission' for authority = '$authority' and user = '{$_SESSION['current_user']}'");
			if ('ALLOW'  == trim($userPermission)) {
				return true; 
			}
		}

		// If no permissions specified at user-level, search for group-level permission
		foreach ($authorities as $authority) {
			$isAuthorizedAtGroupLevel = false;
			foreach (self::$usersGroups as $groupProfile) {
				$groupPermission =  $apx->getPermission($authority, $groupProfile);
				// If one of user's groups is Allowed this authority, set authorized to true.
				if ('ALLOW' == $groupPermission) {
					$isAuthorizedAtGroupLevel = true;
					break; // look no further
				}
			}
			if ($isAuthorizedAtGroupLevel) {
				return true; 
			}
		}

		// If we get here, user is not authorized.
		if ($blnShowErrorPage) $this->denyAccess(); // Die with error
		return false;
		
	}
	
	public function denyAccess() {
		/**
		 * TODO: Create a "Not Authorized" message page and redirect to it, 
		 * with option to return to previous page. For now, just die with message.
		 */
		if ($this->redirectOnDeny) {
			die('You are not authorized to this option. <br /><a href="javascript:history.back();">Go Back</a>');
		}
	}
	
	public function checkPermissionByCategory( $category, $mode, $blnShowErrorPage = true) {
		//pre_dump("checkPermissionByCategory('$category', '$mode')");
		
		$mode = strtoupper($mode);
		// Retrieve list of authorities to check
		$authorities = $this->requiredAuthorities[$category][$mode];
		if (!isset($authorities)) {
			if ($blnShowErrorPage) $this->denyAccess(); // Die with error 
			return false;
		} else {
			return $this->checkAuthoritiesPermission($authorities, $blnShowErrorPage);
		}
	}
	
	/**
	 * swap_ibm_UsrPrf: This is used to swap the user profile on a db2 connection from a 
	 * generic user ID to the user ID of the user logged into the current session;
	 * This is done to ensure that iSeries object-level security and auditing
	 * apply to the actions performed by this user.
	 * 
	 * The reason this function is needed is so that we can adopt the authority
	 * of the user on each http request, without having to store the user's password
	 * on the server between requests.
	 *  
	 * This will call a stored procedure which is an RPG program (SWAPUSRPRF) to 
	 * call the required system APIs to accomplish this.
	 * 
	 * @param string $toProfile Optional - Porfile to swap to. 
	 * 					If not passed, will use $_SESSION['current_user']
	 * 
	 * @return true if successful call to stored procedure, else false.
	 */
	public static function swap_ibm_UsrPrf( $toProfile = NULL) {
    	if (!isset($toProfile)) {
    		if (!isset($_SESSION['current_user'])) {
    			return false;
    		} else {
	    		$toProfile = $_SESSION['current_user'];
    		}
    	}
    	$toPswd = '*NOPWDCHK';
		
    	$dbconn = VGS_DB_Conn_Singleton::getInstance();
    	$sql = 'call spSwapUserProfile(?, ?)';
    	$stmt = db2_prepare($dbconn, $sql);
    	db2_bind_param($stmt, 1, "toProfile", DB2_PARAM_INOUT);
		db2_bind_param($stmt, 2, "toPswd", DB2_PARAM_INOUT);
		
		$res = db2_execute($stmt);
		return $res;
	}
	
	/**
	 * Authenticates a user/password against the Windows Active Directory
	 * 
	 * @param string $user User ID to authenticate
	 * @param string $pswd User's password to authenticate
	 * 
	 * @return true if successful authentication, else error message string.
	 */
	public function authenticateWinAD( $user, $pswd ) {
		// Connect to Windows AD server
		$ldapConn = ldap_connect ( $this->adServer ); 
		
		if (! $ldapConn ) {
			return "Could not connect to LDAP/Windows AD server."; 
		}
		
		// Add domain to user
		$domainUser = $user . '@VERMONTGAS';
		
		// Authenticate against Active Directory
		$ldapBind = ldap_bind ( $ldapConn, $domainUser, $pswd );
		
		if ($ldapBind) {
			return true;
		} else {
			return ldap_error( $ldapConn );
		}
	}

	/**
	 * Authenticates a user/password against the IBM System i 
	 * 
	 * @param string $user User ID to authenticate
	 * @param string $pswd User's password to authenticate
	 * 
	 * @return true if successful authentication, else error message string.
	 */
	public function authenticateIBMi( $user, $pswd ) {
		// Connect to IBM i with suplied credentials
		$i5conn = new VGS_i5_Conn();
		$i5result = $i5conn->connect($user, $pswd);
		
		if (! $i5result ) {
			return $i5conn->getErrorMessage(); 
		} else {
			return $i5conn->get_i5connResource();
		}
	}
	
	/**
	 * Authenticates a WOMS user/password, as a valid IBM i user and an active WOMS user.
	 *
	 * @param string $user
	 *        	User ID to authenticate
	 * @param string $pswd
	 *        	User's password to authenticate
	 *        	
	 * @return true if successful authentication, else error message string.
	 */
	public function authenticate_WOMS_User($user, $pswd) {
		$error_message = '';
		
		if ($user == '' || $pswd == '') {
			$error_message = 'Both user and password are required.';
		} else {
			$authResult = $this->authenticateIBMi ( $user, $pswd );
			
			if (is_string ( $authResult )) {
				$error_message = $authResult;
			} else {
				$secProf = new Sec_Profiles ( $conn );
				$secProfRec = $secProf->retrieveByID ( $user );
				
				if ($secProfRec ['PRF_PROFILE_TYPE'] != 'USER') {
					$error_message = 'User ID entered is not a valid user profile for the Work Order System.';
				} elseif ($secProfRec ['PRF_PROFILE_STATUS'] != 'ACT') {
					$error_message = 'User ID entered has been disabled for the Work Order System.';
				}
			}
		}
		
		return ($error_message == '') ? true : $error_message;
	}
	
	/**
	 * Determines if a valid WOMS user is currently logged in.
	 * 
	 * @return boolean true if a valid WOMS user ID is set in $_SESSION['current_user'], else false.
	 */
	public function isValidUserLoggedIn() {
		session_start();
		if (!isset($_SESSION ['current_user'])) {
			return false;
		} else {
			$currUser = $_SESSION ['current_user'];
			$secProf = new Sec_Profiles( );
			$secProfRec = $secProf->retrieveByID ( $currUser );
			
			if ($secProfRec['PRF_PROFILE_TYPE'] == 'USER'
			&&  $secProfRec['PRF_PROFILE_STATUS'] == 'ACT') {
				return true;
			}
				
		}
		return false;
	}
	
	/**
	 * Logs out the current user, and destroys the session. 
	 * 
	 */
	public function logout() {
		session_start();
		
		// Unset all of the session variables.
		$_SESSION = array();
		
		// To kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(	session_name(), 
						'', 
						time() - 42000,
						$params["path"], $params["domain"],
						$params["secure"], $params["httponly"]
			);
		}
		
		// Finally, destroy the session.
		session_destroy();
	}
}

?>