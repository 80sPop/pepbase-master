<?php
/**
 * pantrycookie.php
 * written: 10/30/2020
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
	require_once('../config.php');
	require_once('../functions.php');	
	
	session_start(['cookie_lifetime' => 86400]);	

// unscramble user_id
	if (isset($_COOKIE['p_SID'])) 
		$users_id = base64_decode($_COOKIE['p_SID']) / VERIFICATION_KEY;
	else
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

// update $_SESSION variable and table when Administrator changes pantry
	if (isset($_POST['apply'])) {
		$_SESSION['users_pantry_id'] = $_POST['users_pantry_id'];	
		$db=getDB($config);	
		$sql = "UPDATE users SET pantry_id =:pantry_id WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':pantry_id', $_POST['users_pantry_id'], PDO::PARAM_INT);		
		$stmt->bindParam(':id', $users_id, PDO::PARAM_INT);	
		$stmt->execute();		
	}	
	
	header( "Location: ../tools.php?tab=change&hhID=$_POST[hhID]" ); 
?>  