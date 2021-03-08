<?php
/**
 * setup/addadmin.php
 * written: 12/23/2020
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

//	if (!$control=validUser())
//		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	$control=fillControlArray($control, $config, "households");	
//	$control=array();	
	$control['err'] = 0;	
	
/* MAINLINE */

	if (isset($_POST['complete'])) {
		$users = editAdministrator();
		if (!$control['err']) 	
			$header=insertAdmin($users, $header); 
		else
			$header=redirect($control['err']);
	}	
	
	header($header);	

function editAdministrator() {
	global $control;
	
	$control['err']=0;	
	
	$arr = [	
		'firstname'			=> "",
		'lastname'			=> "",
		'username'			=> "",	
		'pantry_id'			=> 1,
		'access_level'		=> 1,	
		'email'				=> "",	
		'phone'				=> "",
		'is_active'			=> 1,
		'password'			=> ""
	]; 	
	
	if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password']))
		$control['err']= 80;	
	else
		$control['err'] = editAdminEmail();		

	if (!$control['err']) {
		$arr['firstname'] = $_POST['firstname'];
		$arr['lastname'] = $_POST['lastname'];
		$arr['username'] = $_POST['username'];
		$arr['email'] = $_POST['email'];
		$arr['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);			
	}	
	
	return $arr;
}

function editAdminEmail() {
	global $control;	
	
	$err=0;
	
	if (! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
		$err = 24;
			
	return $err;
}

function insertAdmin($users, $header) { 
	global $control;
	
	$sql = "INSERT INTO users 	

		(firstname,
		lastname,
		username,	
		pantry_id,
		access_level,	
		email,	
		phone,
		is_active,
		password)
	
		VALUES 
		(:firstname,
		:lastname,
		:username,	
		:pantry_id,
		:access_level,	
		:email,	
		:phone,
		:is_active,
		:password)";	
		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($users);
	
	$sql = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetch();	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "users", $users['id'], "ADD");		
	
	$header = "Location: index.php?addok=1";	
	return $header;	
	
}		

function redirect($err) {

	$header = "Location: index.php?addadmin=1&errCode=$err";
	$header.= "&firstname=$_POST[firstname]"; 
	$header.= "&lastname=$_POST[lastname]"; 
	$header.= "&username=$_POST[username]";
	$header.= "&email=$_POST[email]";	

	return $header;	
} 
?> 