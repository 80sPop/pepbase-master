<?php
/**
 * sendresetmessage.php
 * written: 11/1/2020
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
	require_once('vendor/autoload.php');
	require_once('config.php');
	require_once('functions.php');	
	
	$control=array();
	$control['errCode']=0;	
	$db = getDB($config);
	$control['db']=$db;	
	$control=fillControlArray($control, $config, "households");	
	$header = "Location: signinhelp.php";
	
/* MAINLINE */

// Verify email

	$id=0;
	$firstname="";
	$username="";
	$email="";	
	if ( isset($_POST['email']) )

		if (! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
			$control['errCode'] = 24;
		else {	
			$found=0;		
			$sql = "SELECT * FROM users WHERE email = :email";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
			$stmt->execute();
			while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) 	
				if ($users['email'] ==  $_POST['email']) {  // extra logic needed because MariaDB queries are NOT case sensitive
					$id=$users['id'];
					$firstname=$users['firstname'];
					$username=$users['username'];					
					$found=1;
				}	
			if (!$found)
				$control['errCode'] = 32;
			else
				$email=$_POST['email'];
		}
	else	
		$control['errCode'] = 24;		
		
	if (!$control['errCode']) {
		
		// Delete any existing tokens
			
			$sql = "DELETE FROM password_reset WHERE email = :email";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':email', $email, PDO::PARAM_STR);
			$stmt->execute();			
			
		// Create new tokens

			$currTime=date("Y-m-d H:i:s");	
			$expires = date("Y-m-d H:i:s", strtotime("$currTime + 1 hour"));	
			
			$token = md5($email);
			$addToken = substr(md5(uniqid(rand(),1)),3,10);
			$token = $token . $addToken; 
			if ($control['isTesting'])	
				$token = "123";	// for testing on localhost		
		   
		// Insert token into database
					
			$sql = "INSERT INTO password_reset (user_id, email, token, expires)
					VALUES ( $id, :email, :token, :expires )";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':email', $email, PDO::PARAM_STR);
			$stmt->bindParam(':token', $token, PDO::PARAM_STR);
			$stmt->bindParam(':expires', $expires, PDO::PARAM_STR);			
			$stmt->execute();	
			
		// Send message
		
			sendPasswordResetMsg( $email, $firstname, $username, $token );
			
			if (!$control['errCode'])
				$header .= "?sent=1";
			else
				$header .= "?errCode=$control[errCode]";	
	} else
		
		$header .= "?errCode=$control[errCode]";		
	
	header($header);

function sendPasswordResetMsg( $email, $firstname, $signin, $token ) {
	global $control;

// re-write password reset link to work with any server.		-mlr
//	if ($isTesting) 
//		$link="http://localhost/pepbase3.9.0/SignInHelp.php?token=$token"; // for testing with localhost
//	elseif ($isTraining)
//		$link="https://www.essentialspantry.org/training/SignInHelp.php?token=$token";	
//	else
//		$link="https://www.essentialspantry.org/pepbase/SignInHelp.php?token=$token";	

	$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] 
				=== 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']; 
	$link = str_replace("sendresetmessage.php", "signinhelp.php?token=$token", $link);	
	
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
	Your sign-in name is <b>$signin</b>.
	<p>
	Click on the link below to reset your password.
	<p>
	<a target='_blank' href='$link'>Reset Password</a>
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
	
	//Create a new PHPMailer instance
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
	$mail->Subject = 'Pepbase Account';

	// Set the body
	$mail->msgHTML($msgHTML);

	// Send the message, check for errors
	
	if ($control['isTesting'])
		echo "testing other stuff, message not sent.";	
	else
		if (!$mail->send()) 
			$control['errCode']=35;
//	 else 
//		echo "Message sent!";
//	
	
}