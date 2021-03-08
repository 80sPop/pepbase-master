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

require_once('config.php'); 
require_once('functions.php'); 	

$errCode=0;
$id				=0;
$pantry_id		=0;
$is_active		=0;	
$password		="";
$date = date('Y-m-d');
$time = date('H:i:s');
	
if ( isset($_POST['signInName']) && isset($_POST['password']) ) {
	
	$foundMatch=0;
	$db = getDB($config);
    $sql = "SELECT * FROM users WHERE username = :username";
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':username', $_POST['signInName'], PDO::PARAM_STR);
	$stmt->execute();
	while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {	
	
// MariaDB queries are not case sensitive, so check here
	    if ( $_POST['signInName'] == $users['username'] ) { 
			$foundMatch=1;	
			$id				=$users['id'];
			$pantry_id		=$users['pantry_id'];
			$password		=$users['password'];			
			$is_active		=$users['is_active'];			
		}	
	}	

// check for valid sign-in name	
	if ( !$foundMatch )
		$errCode=3;
	
// use password_verify() for hashed passwords		
	elseif ( !password_verify($_POST['password'], $password) ) 
		$errCode=1;
			
// check activation status
	elseif ( !$is_active ) 
		$errCode=30;		
} else 
	$errCode=31;

if (!$errCode) {
	
// give unassigned users first pantry in pantries table	
	if ($pantry_id == 0) { 
		$sql = "SELECT id FROM pantries ORDER BY id LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch();		
		$pantry_id = $row['id'];
	}	
	
// To avoid $_SESSION garbage collectors and early kick-out, use an everlasting cookie 
// instead. 

	$scrambled=$id*VERIFICATION_KEY;
	$uCookie=base64_encode($scrambled);
	$ver=(float)phpversion();
	if ($ver < 7.3)	
		setcookie('p_SID', $uCookie, 0, '/; samesite=strict');
	else
		setcookie("p_SID", $uCookie);

// Changing cookie values in php > 7.3 is proving difficult, so use a session to 
// store dynamic pantry IDs. Later, during user verification, reload the id in case
// of early garbage collection.

	session_start(['cookie_lifetime' => 86400]);
	$_SESSION['users_pantry_id'] = $pantry_id;	

	$control=array();
	$control['users_id']=$id;
	$control['users_pantry_id']=$pantry_id;
	writeUserLog( $db, $date, $time, 0, "users", $id, "SIGN IN" );	
	writeSigninDate( $db, $id, $date, $time );	
	
	header( "Location: households.php?tab=profile" ); 
	
} else
	header("Location: index.php?errCode=$errCode");	
?>  