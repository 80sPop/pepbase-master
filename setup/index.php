<?php
/**
 * setup/index.php
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

require_once('../functions.php'); 
require_once('../common_vars.php'); 

	$control=array();
	
	htmlSetup("Setup", "");
	setFocus();

	if ($control["focus"] != "nofocus")
		echo "<body class='bg-gray-5' onload='document.getElementById(" . '"' . $control["focus"] . '"' . ").focus()'>";
	else
		echo "<body class='bg-gray-5'>";	

	setupSteps();
	
	if (isset($_GET['addok']))
		completeMsg();
	elseif (isset($_GET['addpantry']))
		addPantryForm($errMsg);	
	elseif (isset($_GET['addadmin']))
		adminSetupForm($errMsg);
	else
		databaseConfigForm($errMsg);

function setFocus() {
	global $control;
	
	$control["focus"]="host";
	if (isset($_GET['addadmin']))
		$control["focus"]="firstname";	
	elseif (isset($_GET['addpantry']))
		$control["focus"]="name";
	elseif (isset($_GET['addok']))		
		$control["focus"] = "nofocus";			
}

function setupSteps() {
	
	$check1="fa-check-circle";
	$check2="fa-check-circle";
	$check3="fa-check-circle";	
	
	if (isset($_GET['addadmin'])) 
		$check3="";	
	elseif (isset($_GET['addpantry'])) {
		$check2="";
		$check3="";
	} elseif (!isset($_GET['addok']))	{
		$check1="";
		$check2="";
		$check3="";	
	}	

	echo "
	<div class='text-center p-3'><h2>Pepbase Setup</h2></div>
		
		<center>
		<table class='m-3'>
		<tr><td class='p-1'><i class='fa $check1 fa-lg text-success'></i></td><td>1. Configure Database</td></tr>
		<tr><td class='p-1'><i class='fa $check2 fa-lg text-success'></i></td><td>2. Add Pantry</td></tr>
		<tr><td class='p-1'><i class='fa $check3 fa-lg text-success'></i></td><td>3. Initialize Administrator Account</td></tr>		
		</table>
		</center>\n";
}

function completeMsg() {
?>	
	<div class='text-center p-3'>
	SETUP COMPLETE! CLICK <a style='color:#841E14;text-decoration:underline;' href='../index.php'>HERE</a> TO BEGIN USING PEPBASE.
	</div>	
	
<?php
}

function databaseConfigForm($errMsg) {
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}		
	
	$values=getDbValues();
?>
	<div class="container p-3">
		<div class="card">
			<h5 class="card-header bg-gray-4 text-center">Step 1. Configure Database</h5>
			<div class="card-body bg-gray-2">

				<form method='post' action='writeconfig.php'>  	

					<div class="form-group">
					<label for="exampleInputEmail1">Host</label>
					<input type="text" class="form-control" name="host" id="host" value='<?php echo $values['host']; ?>' >
					<small class="form-text text-muted">(example 'localhost')</small>
					</div>	

					<div class="form-group">
					<label for="exampleInputEmail1">MySQL Database</label>
					<input type="text" class="form-control" name="dbname" id="dbname" value='<?php echo $values['dbname']; ?>' >
					<small class="form-text text-muted">This can be a new or existing database. Caution: Existing databases will be dropped and re-created.</small>			
					</div>	

					<div class="form-group">
					<label for="exampleInputEmail1">MySQL User</label>
					<input type="text" class="form-control" name="user" id="user" value='<?php echo $values['user']; ?>' >
					<small class="form-text text-muted">(example 'root')</small>			
					</div>	

					<div class="form-group">
					<label for="exampleInputEmail1">MySQL User Password</label>
					<input type="text" class="form-control" name="pswd" id="pswd" value='<?php echo $values['pswd']; ?>' >
					</div>	
					
					<div class="form-group">
					<label for="exampleInputEmail1">Time Zone</label>
					<?php selectTimezone( "timezone", $values['timezone']); ?>					
					</div>						
					
					<div class='text-center mt-3'>
						<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='continue'>Continue</button>	
					</div>						
					
					
				</form>
			</div>
		</div>
	</div>	

<?php	
}		

function adminSetupForm($errMsg) {
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}		
	
	$values=getAdminValues();
?>
	<div class="container p-3">
		<div class="card">
			<h5 class="card-header bg-gray-4 text-center">Step 3. Initialize Administrator Account</h5>
			<div class="card-body bg-gray-2">

				<form method='post' action='addadmin.php'>  	

					<div class="form-group">
					<label for="exampleInputEmail1">First Name</label>
					<input type="text" class="form-control" name="firstname" id="firstname" value='<?php echo $values['firstname']; ?>' >
					</div>	

					<div class="form-group">
					<label for="exampleInputEmail1">Last Name</label>
					<input type="text" class="form-control" name="lastname" id="lastname" value='<?php echo $values['lastname']; ?>' >
					</div>	
					
					<div class="form-group">
					<label for="exampleInputEmail1">Email</label>
					<input type="text" class="form-control" name="email" id="email" value='<?php echo $values['email']; ?>' >
					</div>						

					<div class="form-group">
					<label for="exampleInputEmail1">Username</label>
					<input type="text" class="form-control" name="username" id="username" value='<?php echo $values['username']; ?>' >
					<small class="form-text text-muted">(example: 'admin')</small>			
					</div>	

					<div class="form-group">
					<label for="exampleInputEmail1">Password</label>
					<input type="text" class="form-control" name="password" id="password" value='<?php echo $values['password']; ?>' >
					<small class="form-text text-muted">(any combination of letters, numbers, or special characters)</small>					
					</div>	
					
					<div class='text-center mt-3'>
						<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='complete'>Complete Setup</button>	
					</div>						
					
					
				</form>
			</div>
		</div>
	</div>	

<?php	
}	

function addPantryForm($errMsg) {
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}		
	
	$values=getPantryValues();
?>
	<div class="container p-3">
		<div class="card">
			<h5 class="card-header bg-gray-4 text-center">Step 2. Add Pantry</h5>
			<div class="card-body bg-gray-2">

				<form method='post' action='addpantry.php'>  	

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* PANTRY NAME:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="name" id="name" value='<?php echo htmlentities($values['name'], ENT_QUOTES); ?>' ></div>		
					</div>	
				</div>
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* ABBREVIATION:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="abbrev" id="abbrev"  value='<?php echo $values['abbrev']; ?>'></div>
					</div>	
				</div>		
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* START DATE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="date" class="form-control" name="start_date" id="start_date"  value='<?php echo $values['start_date']; ?>'></div>
					</div>	
				</div>	
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>INACTIVE DATE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="date" class="form-control" name="inactive_date" id="inactive_date"  value='<?php echo $values['inactive_date']; ?>'></div>
					</div>	
				</div>			
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* ADDRESS 1:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="address_1" id="address_1"  value='<?php echo htmlentities($values['address_1'], ENT_QUOTES); ?>'></div>
					</div>	
				</div>	
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>ADDRESS 2:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="address_2" id="address_2"  value='<?php echo htmlentities($values['address_2'], ENT_QUOTES); ?>'></div>
					</div>	
				</div>			
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* CITY:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="city" id="city"  value='<?php echo htmlentities($values['city'], ENT_QUOTES); ?>'></div>
					</div>	
				</div>	

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* COUNTY:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="county" id="county" value='<?php echo htmlentities($values['county'], ENT_QUOTES); ?>'></div>
					</div>	
				</div>			

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* STATE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><?php SelectState('state', $values['state']); ?></div>
					</div>	
				</div>	
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* ZIP CODE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="zip_5" id="zip_5"  value='<?php echo $values['zip_5']; ?>'></div>
					</div>	
				</div>	

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* EMAIL:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="email" id="email"  value='<?php echo $values['email']; ?>'></div>
					</div>	
				</div>	
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>WEBSITE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="web_site" id="web_site"  value='<?php echo $values['web_site']; ?>'></div>
					</div>	
				</div>	
				
			

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* CONTACT FIRST NAME:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="contact_first" id="contact_first"  value='<?php echo htmlentities($values['contact_first'], ENT_QUOTES); ?>'></div>
					</div>	
				</div>	

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>* CONTACT LAST NAME:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="contact_last" id="contact_last"  value='<?php echo htmlentities($values['contact_last'], ENT_QUOTES); ?>'></div>
					</div>	
				</div>				

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>PHONE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="phone" id="phone" value='<?php echo $values['phone']; ?>'></div> 
					</div>	
				</div>	

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>CELL PHONE:</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="cell_phone" id="cell_phone" value='<?php echo $values['cell_phone']; ?>'></div>
					</div>	
				</div>	
				
				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (1):</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="hours_1" value='<?php echo $values['hours_1']; ?>'></div>
					</div>	
				</div>		

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (2):</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="hours_2" value='<?php echo $values['hours_2']; ?>'></div>
					</div>	
				</div>		

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (3):</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="hours_3" value='<?php echo $values['hours_3']; ?>'></div>
					</div>	
				</div>	

				<div class="form-row">
					<div class="col-5">
						<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (4):</label></div>
					</div>	
					<div class="col-3">
						<div class="form-group mb-1"><input type="text" class="form-control" name="hours_4" value='<?php echo $values['hours_4']; ?>'></div>
					</div>	
				</div>					
				
				<div class="form-row">
					<div class="col-lg text-center">* required field</div>	
				</div>		
						
				<div class='text-center mt-3'>
					<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='continue'>Continue</button>	
				</div>						
					
					
				</form>
			</div>
		</div>
	</div>	

<?php	
}

function getPantryValues() {
	global $control;
	
	$arr = [		
		'name' => "",
		'abbrev' => "",
		'start_date' => "",
		'is_active' => 1,
		'inactive_date' => "",
		'is_food_pantry'=> 0,
		'theme_id' => "",
		'address_1'=> "",
		'address_2'=> "",
		'city'=> "",
		'county'=> "",
		'state' => "WI",
		'zip_5'=> "",
		'zip_4'=> "",
		'email'=> "",
		'web_site'=> "",
		'contact_first'=> "",
		'contact_last' => "",
		'phone'=> "",
		'cell_phone' => "",
		'hours_1' => "",
		'hours_2' => "",
		'hours_3'=> "",
		'hours_4'=> ""
	];
	
	if (isset($_GET['errCode'])) {
		$arr['name'] =$_GET['name'];
		$arr['abbrev'] =$_GET['abbrev'];
		$arr['start_date'] = $_GET['start_date'];
		$arr['inactive_date'] = $_GET['inactive_date'];
		$arr['address_1'] =$_GET['address_1'];
		$arr['address_2'] =$_GET['address_2'];
		$arr['city'] =$_GET['city'];
		$arr['county'] =$_GET['county'];
		$arr['state'] = $_GET['state'];
		$arr['zip_5'] =$_GET['zip_5'];
		$arr['email'] =$_GET['email'];
		$arr['web_site'] =$_GET['web_site'];
		$arr['contact_first'] =$_GET['contact_first'];
		$arr['contact_last'] =$_GET['contact_last'];
		$arr['phone'] = $_GET['phone'];
		$arr['cell_phone'] = $_GET['cell_phone'];
		$arr['hours_1'] =$_GET['hours_1'];
		$arr['hours_2'] =$_GET['hours_2'];
		$arr['hours_3'] =$_GET['hours_3'];
		$arr['hours_4'] =$_GET['hours_4'];			
	} 

	return $arr;
}
	
function getDbValues() {
	
	$arr=array();
	$arr['host']=""; 
	$arr['dbname']=""; 
	$arr['user']="";
	$arr['pswd']="";
	$arr['timezone']="";	
	
	if (isset($_GET['errCode'])) {	
		$arr['host']=$_GET['host']; 
		$arr['dbname']=$_GET['dbname']; 
		$arr['user']=$_GET['user'];
		$arr['pswd']=$_GET['pswd'];	
		$arr['timezone']=$_GET['timezone'];			
	}	

	return $arr;
}	

function getAdminValues() {
	
	$arr = [	
		'firstname'			=> "",
		'lastname'			=> "",
		'username'			=> "",	
		'email'				=> "",	
		'password'			=> "",
	]; 	
	
	if (isset($_GET['errCode'])) {	
		$arr['firstname']=$_GET['firstname']; 
		$arr['lastname']=$_GET['lastname']; 
		$arr['username']=$_GET['username'];
		$arr['email']=$_GET['email'];	
	}	

	return $arr;
}	

function htmlSetup($title, $root) {
?>
	<!doctype html>
	<html lang='en'>
	<head>
<!-- Required meta tags -->
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <title><?php echo $title; ?></title>
	<link rel='icon' type='image/x-icon' href='../images/favicon-index.ico?v=2' />
<!-- Bootstrap CSS
	<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css' integrity='sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO' crossorigin='anonymous'> -->
	<!-- Sass Bootstrap override -->
	<link rel='stylesheet' href='<?php echo $root; ?>../css/main.css' >
<!-- custom css -->
	<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' />	
    <link rel='stylesheet' href='<?php echo $root; ?>../css/sticky-footer.css' >
    <link rel='stylesheet' href='<?php echo $root; ?>../css/icheck-bootstrap.min.css' > 
	<link rel='stylesheet' href="<?php echo $root; ?>../css/bootstrap-switch.css?v=2" > 

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
<!--    <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js' integrity='sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo' crossorigin='anonymous'></script> -->
<!--	<script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script> -->
	<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js' integrity='sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy' crossorigin='anonymous'></script>
	
	<script src="<?php echo $root; ?>../Inputmask-5.x/dist/jquery.inputmask.js"></script>		
	<script src="<?php echo $root; ?>../js/bootstrap-switch.js"></script> 	

	<!-- smartresize js for responsive charts -->
	<script type='text/javascript' src='<?php echo $root; ?>../js/jquery.debouncedresize.js'></script>
	<script type='text/javascript' src='<?php echo $root; ?>../js/jquery.throttledresize.js'></script> 

	<!-- Google Charts API -->
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script> 
	
	<!-- Ajax for function okToPrintAnother() -->
	<script src="<?php echo $root; ?>../js/ajax.js"></script> 	

	</head>
	
<?php
}	
?>

<!-- Place any per-page style here -->


<!-- Place any per-page javascript here -->

<script>	

	$("#phone").inputmask({"mask": "(999) 999-9999"});	
	$("#cell_phone").inputmask({"mask": "(999) 999-9999"});	

</script>