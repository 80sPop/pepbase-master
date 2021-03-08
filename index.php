<?php
/**
 * index.php
 * written: 5/6/2020
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

	if (!file_exists('config.php'))  
		header("Location: setup/index.php");

	require_once('config.php'); 
	require_once('common_vars.php'); 
	require_once('functions.php');	
	require_once('header.php');	
	
	$control=array();
	$control['db'] = getDB($config);
	$control['hostId'] = 0;
	$control['users_pantry_id']=0;		
	$control=fillControlArray($control, $config, "households");	
	
	$control=setFocus($control);		
	
	doSignInHeader("Sign In");

	$signInName = "";
	$password = "";
?>
	<div class="container p-3">
	
		<div class='card rounded-0'>
			<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Sign In</h4></div>
				<div class='card-body bg-gray-2 rounded-0'>

<?php
// $isTraining defined in config.php		
	if ($control['isTraining'])
		echo "
		<div>
			<b>*** FOR TRAINING ONLY ***</b>
		</div>";	

    if (isset($_GET['errCode'])) {
		$errCode=$_GET['errCode'];
		echo "
			<div class='alert alert-danger' role='alert'>
			  $errMsg[$errCode]
			</div>";
	}	

	echo "
	<form method='post' name='login' action='signin.php'>
	  <div class='form-group'>
		<label>Sign In Name</label>
		<input type='text' class='form-control' id='signInName' name='signInName' >
	  </div>
	  <div class='form-group'>
		<label>Password</label>
		<input type='password' class='form-control' name='password' >
	  </div>

	  <button type='submit' class='btn btn-primary text-white' name= 'SignIn'>Sign In</button>
	</form>
	
	<div class='pt-4 text-center'>
		<a style='text-decoration:none;color:#841E14;' href='signinhelp.php?pass=1'><h6>I forgot my sign-in name or password</h6></a>
	</div>";	
	
?>

			</div>
		</div>
	</div>
</div>
</body>
<?php bFooter(); ?>
</html>
<?php

function setFocus($arr) {
	
	$arr['focus'] = "signInName";		

	return $arr;		
}	


