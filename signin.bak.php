<?php
/**
 * signin.php
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

/* INCLUDE FILES */

require_once('config.php'); 
require_once('functions.php'); 	

$errCode=0;
$sa_id				=0;
$sa_access_level_id	=0;
$sa_pantry_id		=0;
$sa_is_active		=0;	
$sa_password		="";
$date = date('Y-m-d');
$time = date('H:i:s');
	
if ( isset($_POST['signInName']) && isset($_POST['password']) ) {
	
// escape special characters in sign in name and password.	
	$signInName = mysqli_real_escape_string($conn, $_POST['signInName']);
	$password   = mysqli_real_escape_string($conn, $_POST['password']);
	$foundMatch=0;
    $sql = "SELECT * FROM signin_accounts WHERE sa_signin_name = '$signInName'";			
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ( $row = mysqli_fetch_assoc($result) ) {
		
// MariaDB queries are not case sensitive, so check here			
	    if ( $signInName == $row['sa_signin_name'] ) { 
			$foundMatch=1;	
			$sa_id				=$row['sa_id'];
			$sa_access_level_id	=$row['sa_access_level_id'];
			$sa_pantry_id		=$row['sa_pantry_id'];
			$sa_password		=$row['sa_password'];			
			$sa_is_active		=$row['sa_is_active'];			
		}	
	}

// check for valid sign-in name	
	if ( ! $foundMatch )
		$errCode=3;
	
// use password_verify() for hashed passwords		
	elseif ( ! password_verify($password, $sa_password) ) 
		$errCode=1;
			
// check activation status
	elseif ( ! $sa_is_active ) 
		$errCode=30;		
} else 
	$errCode=31;

if (!$errCode) {
	
// Add SameSite cookie attribute using the 'Strict' directive.
	setcookie('signinId', $sa_id, 0, '/; samesite=strict');	
	setcookie('accessLevel', $sa_access_level_id, 0, '/; samesite=strict');	
	
//	setcookie ( "signinId", $sa_id );
//	setcookie ( "accessLevel", $sa_access_level_id );
	writeUserLog( $conn, $date, $time, $sa_id, $sa_pantry_id, "SIGN IN" );
	writeSigninDate( $conn, $sa_id, $date, $time );		
	header( "Location: households.php?login=1" ); 
	
} else
	header("Location: index.php?errCode=$errCode&signInName=".$signInName."&password=".$password);		
?>  