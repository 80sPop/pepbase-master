<?php
/**
 * signout.php
 * written: 5/7/2020
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do so, 
 * subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
*/ 

	require_once('config.php'); 
	require_once('functions.php'); 
	
	$date = date('Y-m-d');
	$time = date('H:i:s');	
	
	$db=getDB($config);
	
	session_start(['cookie_lifetime' => 86400]);	
	
// write to user log	
	if (isset($_COOKIE['p_SID'])) {
		$control=array();
		$control['users_id']=base64_decode($_COOKIE['p_SID']) / VERIFICATION_KEY;
		if (isset($_SESSION['users_pantry_id'])) 
			$control['users_pantry_id']=$_SESSION['users_pantry_id'];
		else {
			if ($users=getUsersRow( $db, $control['users_id'] )) 
				$control['users_pantry_id'] = $users['pantry_id'];			
			else
				die ("SESSION ERROR in signout.php");	
		}				
		writeUserLog( $db, $date, $time, 0, "users", $control['users_id'], "SIGN OUT" );
	}

// destroy cookie
	$ver=(float)phpversion();
	if ($ver < 7.3)	
		setcookie("p_SID", '', time()-1000, '/');	
	else
		setcookie("p_SID");
	
// destroy session
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	session_destroy();			
	
    header("Location: index.php");
?>