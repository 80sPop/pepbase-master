<?php
/**
 * addupdateuser.php
 * written: 8/26/2020
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

	use PHPMailer\PHPMailer\PHPMailer;
	
	require_once('../vendor/autoload.php');
	require_once('../config.php'); 
	require_once('../functions.php');	

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	
	$control=fillControlArray($control, $config, "security");
	$control=loadAccessLevels();	
	
/* MAINLINE */

	$header = "Location: ../security.php?tab=users&hhID=" . $control['hhID'];

	if (!isset($_POST['cancel'])) {
		$users = editUser();
		if (!$control['err']) {
			if (isset($_POST['id']))
				$header=updateUser($_POST['id'], $users, $header); 
			else {
				$header=insertUser($users, $header); 
				$header=sendNewAccountMsg($users['firstname'], $users['email'], $users['username'], $header);
			}	
		} else	
			$header=redirect($header,$control['err']);				
	} 

	header($header);	

function editUser() {
	global $control;

	$arr = [	
		'firstname'			=> "",
		'lastname'			=> "",
		'username'			=> "",	
		'pantry_id'			=> "",
		'access_level'		=> "",	
		'email'				=> "",	
		'phone'				=> "",
		'is_active'			=> 0
	]; 
	
	if (empty($_POST['firstname']))
		$control['err'] = 66;
	elseif (empty($_POST['lastname']))
		$control['err'] = 67;
	else
		$control['err'] = editUsername();
	if (!$control['err']) 
		$control['err'] = editUserEmail();
	if (!$control['err'])
		if (!isValidPhone($_POST['phone']))		
			$control['err'] = 54;	
	if (!$control['err'] && ( !empty($_POST['password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) ) 
		$control['err'] = editPassword( $_POST['password'], $_POST['new_password'],  $_POST['confirm_password'], 0 );
	
	if (!$control['err']) {
		$arr['firstname'] = $_POST['firstname'];
		$arr['lastname'] = $_POST['lastname'];
		$arr['username'] = $_POST['username'];
		$arr['pantry_id'] = $_POST['pantry_id'];
		if (isset($_POST['access_level']))
			$arr['access_level'] = $_POST['access_level'];
		$arr['email'] = $_POST['email'];
		$arr['phone'] = $_POST['phone'];
		if (isset($_POST['is_active']))
			$arr['is_active'] = 1;
	}	
	
	return $arr;
}

function editUsername() {
	global $control;
	
	$err=0;
	
	if (empty($_POST['username']))
		$err = 69;	
	else {
		$sql = "SELECT * FROM users WHERE username = :username";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':username', $_POST['username'], PDO::PARAM_STR);		
		$stmt->execute();	
		$result = $stmt->fetchAll();	
		foreach ($result as $users) 
			// Usernames are case sensitive, but MariaDB queries are NOT.
			if ($users['username'] == $_POST['username']) 
				if (isset($_POST['id'])) {
					if ($users['id'] != $_POST['id'])
						$err=2;
				} else
					$err=2;	
	}
	return $err;
}	

function editUserEmail() {
	global $control;	
	
	$err=0;
	
	if (! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
		$err = 24;
	else {
		$sql = "SELECT * FROM users WHERE email =:email";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);		
		$stmt->execute();
		$users = $stmt->fetch();		
		$total = $stmt->rowCount();		
		if ($total > 0)			
			if (isset($_POST['id'])) {
				if ($users['id'] != $_POST['id']) {
					$err=25;
				echo "users=$users[id] post=$_POST[id]";					
				}	
			} else {
				echo "here2";
				$err=25;
			}	
	}		
			
	return $err;
}

function insertUser($users, $header) { 
	global $control;
	
	$sql = "INSERT INTO users 	

		(firstname,
		lastname,
		username,	
		pantry_id,
		access_level,	
		email,	
		phone,
		is_active)
	
		VALUES 
		(:firstname,
		:lastname,
		:username,	
		:pantry_id,
		:access_level,	
		:email,	
		:phone,
		:is_active)";	
		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($users);
	
	$sql = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetch();	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "users", $users['id'], "ADD");		
	
	$header.= "&addok=1";
	return $header;	
	
}		

function updateUser($id, $users, $header) { 
	global $control;

	$users['id']=$id;
	
	$sql = "UPDATE users
			SET firstname =:firstname,	
				lastname=:lastname,
				username=:username,
				pantry_id=:pantry_id,";
				
	if ($control['access_level_update'])			
		$sql.="access_level=:access_level,";
	
	$sql.= "
				email=:email,
				phone=:phone,
				is_active=:is_active	
			WHERE id =:id";	
			
	$stmt= $control['db']->prepare($sql);
	$stmt->bindParam(':firstname', $users['firstname'], PDO::PARAM_STR);	
	$stmt->bindParam(':lastname', $users['lastname'], PDO::PARAM_STR);	
	$stmt->bindParam(':username', $users['username'], PDO::PARAM_STR);		
	$stmt->bindParam(':pantry_id', $users['pantry_id'], PDO::PARAM_INT);	
	if ($control['access_level_update'])	
		$stmt->bindParam(':access_level', $users['access_level'], PDO::PARAM_INT);	
	$stmt->bindParam(':email', $users['email'], PDO::PARAM_STR);	
	$stmt->bindParam(':phone', $users['phone'], PDO::PARAM_STR);	
	$stmt->bindParam(':is_active', $users['is_active'], PDO::PARAM_INT);
	$stmt->bindParam(':id', $users['id'], PDO::PARAM_INT);	
	$stmt->execute();	
	
	if (! empty($_POST['new_password'])) {
		$hPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);		
		$sql = "UPDATE users SET password=:password WHERE id =:id";	
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':password', $hPassword, PDO::PARAM_STR);			
		$stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);		
		$stmt->execute();	
		$header.= "&passwordok=1";
	}	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "users", $users['id'], "UPDATE");		
	
	return $header;
}	

function redirect($header, $err) {
	global $control;
	
	if (isset($_POST['id']))	
		$header .= "&edit=1&id=$_POST[id]";
	else
		$header .= "&add=1";		
	$header .= "&errCode=" . $err;
	$header .= "&firstname=" . urlencode($_POST['firstname']);
	$header .= "&lastname=" . urlencode($_POST['lastname']);
	$header .= "&username=" . urlencode($_POST['username']);
	$header .= "&pantry_id=" . $_POST['pantry_id'];
	if (isset($_POST['access_level']))
		$header .= "&access_level=" . $_POST['access_level'];
	elseif ($users=getUsersRow( $control['db'], $control['users_id'] ))
		$header .= "&access_level=" . $users['access_level'];
	else
		die("ERROR USERS TABLE!!!");
	$header .= "&email=" . urlencode($_POST['email']);
	$header .= "&phone=" . $_POST['phone'];
	$header .= "&is_active=" . urlencode($_POST['is_active']);
	
	return $header;	
} 

function sendNewAccountMsg($firstname, $email, $username, $header) {
	global $control;
	
// Select most recently added user account	

	$user_id=0;	
	$sql = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetch();	
	$user_id=$users['id'];	
	
// Delete any existing tokens

	$sql = "DELETE FROM password_reset WHERE email = :email";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':email', $email, PDO::PARAM_STR);	
	$stmt->execute();
	
// Create new tokens

	$currTime=date("Y-m-d H:i:s");	
	$expires = date("Y-m-d H:i:s", strtotime("$currTime + 3 days"));	
	
	$token = md5($email);
	$addToken = substr(md5(uniqid(rand(),1)),3,10);
	$token = $token . $addToken; 
	if ($control['isTesting'])	
		$token = "123";	// for testing on localhost		
   
// Insert token into database
			
	$sql = "INSERT INTO password_reset (user_id, email, token, expires)
			VALUES ( :user_id, :email, :token, :expires )";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':user_id', $users['id'], PDO::PARAM_INT);		
	$stmt->bindParam(':email', $email, PDO::PARAM_STR);	
	$stmt->bindParam(':token', $token, PDO::PARAM_STR);	
	$stmt->bindParam(':expires', $expires, PDO::PARAM_STR);		
	$stmt->execute();
			
// Re-write password set-up link to work with any server.	

	$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] 
				=== 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']; 
	$link = str_replace("addupdateuser.php", "../signinhelp.php?new=1&token=$token", $link);

// Format email content

	$typeDecl="
	<!doctype html> <html lang='en'> 
	<head> 
	<meta charset='utf-8'> <meta name='viewport' content='width=device-width, initial-scale=1'>
	<meta http-equiv='X-UA-Compatible' content='IE=edge'/>
	</head>";

	$body="
	<body style='font-family:Helvetica Neue, Helvetica, Arial, sans-serif;'>	
	<div style='font-size:1.0em;padding-top:20px;'>
	Hi $firstname,
	<p>
	A new Pepbase account was registered for you, but you still need to setup your password.
	<p>
	<p>Your sign-in name is <b>$username</b>.
	<p>
	Click on the link below to create your password.
	<p>
	<a target='_blank' href='$link'>Setup Password</a>
	<p>
	If you didn't make this request, please contact your pantry coordinator.
	<p>
	</div>";

	$footer="
	<table border='1' style='margin-top:20px;border:1px solid;width:770px;font-family:Helvetica Neue, Helvetica, Arial, sans-serif;'>
	<tr><td style='border-width:0;'>
	<img style='border:0px;margin:0px 10px 2px 3px;vertical-align:top;height:125px;' src='https://www.essentialspantry.org/pepbase/images/logo.png' alt='PEP logo'  /></td>
	<td style='border:0;padding:5px;'><span style='font-size:1.3em;'>
	<a style='text-decoration:none;color:#831D07;' href='https://www.essentialspantry.org'>Pepartnership, Inc.</a></span>
	<p style='color:#606060;'>
	Working together to help persons in need with the essentials of personal and household hygiene.
	<p>
	www.essentialspantry.org</td>
	</tr></table>
	</div>
	</body>
	";

	$msgHTML = $typeDecl . $body . $footer;
	
	// Create a new PHPMailer instance
	$mail = new PHPMailer;
	
	// Set PHPMailer to use the sendmail transport
	$mail->isSendmail();

	// Set who the message is to be sent from
	$mail->setFrom('admin@essentialspantry.org', 'Pepartnership, Inc.');

	// Set an alternative reply-to address
	//$mail->addReplyTo('replyto@example.com', 'First Last');

	// Set who the message is to be sent to
	//$mail->addAddress('rolfs@hotmail.com', 'John Doe');
	$mail->addAddress($email);

	// Set the subject line
	$mail->Subject = 'Pepbase Account Registration';

	// Set the body
	$mail->msgHTML($msgHTML);

	// Send the message, check for errors
	
	if ($control['isTesting'])
		echo "testing other stuff, message not sent.";	
	else
		if (!$mail->send()) 
			$header .= "&errCode=35";
		
//	$header .= "&errCode=35";		
	return $header;		
}
?> 