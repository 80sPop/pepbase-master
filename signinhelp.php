<?php
/**
 * signinhelp.php
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
 
	require_once('config.php'); 
	require_once('common_vars.php'); 
	require_once('functions.php');	
	require_once('header.php');	
	
	$control=array();
	$control['db'] = getDB($config);	
	$control=setFocus($control);		
	
	doSignInHeader("Sign In Help");

	$signInName = "";
	$password = "";
?>
	<div class="container p-3">
<?php

    if ( isset($_GET['token']) || isset($_POST['resetPass']) )
		doResetPasswordForm($errMsg);

    elseif (isset($_GET['sent'])) 
		doEmailSentMessage();
		
	else {
	
		echo "
		<div class='card rounded-0'>
			<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Get help with sign-in name or password</h4></div>
				<div class='card-body bg-gray-2 rounded-0'>";

		if (isset($_GET['errCode'])) {
			$errCode=$_GET['errCode'];
			echo "
				<div class='alert alert-danger' role='alert'>
				  $errMsg[$errCode]
				</div>";
		} 	

		echo "
		<form method='post' name='login' action='sendresetmessage.php'>
			<div class='form-group'>
				<label>Enter your email address</label>
				<input type='text' class='form-control' id='email' name='email' >
			</div>
			<a style='text-decoration:none;color:#841E14;' href='index.php'>Cancel</a>
			<button type='submit' class='btn btn-primary text-white ml-3' name= 'Continue'>Continue</button>
		</form>";
		
		echo "	
				</div>
			</div>
		</div>";		
	}	


?>		
	</div>
</body>
<?php bFooter(); ?>
</html>

<?php

function setFocus($arr) {
	
	if (isset($_GET['errCode'])) {
		$arr['errCode'] = $_GET['errCode'];	
		if ($arr['errCode']==24 || $arr['errCode']==32)
			$arr['focus'] = "email";
    } elseif ( isset($_GET['token']) || isset($_POST['resetPass']) ) 
		$arr['focus'] = "newPassword";
	elseif (isset($_GET['sent']))
		$arr['focus'] = "nofocus";	
	else
		$arr['focus'] = "email";		

	return $arr;		
}	

function doResetPasswordForm($errMsg) {
	global $control;
	
	$errCode=0;	
	$user_id=0;
	$passwordChanged=0;
	$resetHead="Reset Password";
	if (isset($_GET['new']))
		$resetHead="Setup Password";		
	
	if ( isset($_POST['resetPass']) ) {

		$errCode = editPassword("", $_POST['newPassword'], $_POST['confirmPassword'], 1 );
		if ( !$errCode ) {
			doInsertNewPassword($_POST['user_id'], $_POST['newPassword'] );
			$passwordChanged=1;	
		} else
			$user_id = $_POST['user_id'];
		
	} else { 
		// verify token	
		$currTime=date("Y-m-d H:i:s");	
		$sql = "SELECT * FROM password_reset WHERE token =:token";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);		
		$stmt->execute();
		$total = $stmt->rowCount();	
		if ($total > 0) {			
			$row = $stmt->fetch();			
			if ( $currTime > $row['expires'] )
				$errCode=33;
			else
				$user_id=$row['user_id'];
		} else
			$errCode=34;	
	}
	
	if (!$passwordChanged) {
		
		echo "
		<div class='card rounded-0'>
			<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>$resetHead</h4></div>
				<div class='card-body bg-gray-2 rounded-0'>\n";
		
		if ( $errCode > 0 ) {
			echo "
				<div class='alert alert-danger' role='alert'>
				  $errMsg[$errCode]
				</div>\n";
				
				if ( $errCode != 27 && $errCode != 28 ) 
					echo "<div class='p-2'><a style='text-decoration:none;color:#841E14;' href='signinhelp.php'>Try again</a></div>\n";	
		} 
		
		if ( $errCode == 0 || $errCode == 27 || $errCode == 28 ) {
			
//			$sql = "SELECT * FROM signin_accounts WHERE user_id = $user_id";
			$sql = "SELECT * FROM users WHERE id = $user_id";
			$stmt = $control['db']->prepare($sql);
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {	
				$row = $stmt->fetch();			
				$username = $row['username'];
			}	
			
			echo "	
			<form method='post' name='login' action='signinhelp.php'>
				<div class='form-group'>
					<label>Signin Name</label>
					<input type='text' class='form-control font-weight-bold' id='signInName' name='signInName' value='$username' disabled=disabled>				
				</div>			
			
				<div class='form-group'>
					<label>New Password</label>
					<input type='password' class='form-control' name='newPassword' id='newPassword'>
				</div>
				<div class='form-group'>
					<label>Confirm Password</label>
					<input type='password' class='form-control' name='confirmPassword' >
				</div>	
				<input type='hidden' name='user_id' value='$user_id' >";
				if (isset($_GET['new']))
					echo "<input type='hidden' name='newAccount' >\n";				
				echo "
				<button type='submit' class='btn btn-primary text-white' name= 'resetPass' >Create Password</button>
			</form>\n";
		
		} 
		
		echo "
				</div>
			</div>
		</div>\n";	
	}	
}	

function doEmailSentMessage() {

	echo "
	<div class='card rounded-0'>
		<div class='card-body bg-gray-2 rounded-0'>
			<div class='p-2'><h2>Check your email</h2></div>
			<div class='p-2'><h5>A message was sent to your email address with signin name and password reset instructions.
			If you didn't receive it, check your spam or junk email folder.</h5></div>
			<div class='p-2'><h6>Didn't receive an email? 
			<a style='text-decoration:none;color:#841E14;' href='signinhelp.php'>Try again</a>
			</h6></div>
		</div>
	</div>\n";	
}	

function doInsertNewPassword($user_id, $password) {
	global $control;
	
	$hPassword = password_hash($password, PASSWORD_DEFAULT);
	
	$sql = "UPDATE users
			SET password= :password
			WHERE id = $user_id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':password', $hPassword, PDO::PARAM_STR);				
	$stmt->execute();				
	
	echo "
	<div class='card rounded-0'>
		<div class='card-body bg-gray-2 rounded-0'>\n";
		if ( isset($_POST['newAccount']) )
			echo"
			<div class='p-2'><h2>Password Created</h2></div>
			<div class='p-2'><h5>You have successfully entered your password. </h5></div>\n";
		else
			echo "	
			<div class='p-2'><h2>Password Changed</h2></div>
			<div class='p-2'><h5>You have successfully changed your password. </h5></div>\n";			
		echo "	
			<div class='p-2'><h6><a style='text-decoration:none;color:#841E14;' href='index.php'>Sign in with new password</a>
			</h6></div>
		</div>
	</div>\n";	
}	
?>