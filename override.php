<?php
/**
 * households.php
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

	require_once('config.php'); 
	require_once('header.php'); 
	require_once('navbar.php'); 		
	require_once('functions.php');	
	require_once('search.php');		
	require_once('advanced.php');	
	require_once('common_vars.php');		
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	
	$control=fillControlArray($control, $config, "households");
	
	doHeader("Households");
	doNavbar();	
	
	if ( isset($_GET['new']) || (isset($_GET['errType']) && $_GET['errType'] == "new") )
		if (isset($_GET['errCode']) && $_GET['errCode'] == 14)
			displayMatchedResults();
		else
			newHHForm($errMsg);	
	elseif (isset($_GET['advanced']))
		advancedSearchForm();
	else {
		doSearchBar();
		if ( (isset($_POST['search']) || isset($_POST['advancedSearch'])) && $control['hhID'] == 0) 
			findHousehold();			
		else
			viewHousehold();
	}	
	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";

	
function viewHousehold() {	
	global $control;
	
	if (empty($control['hhID']) || $control['hhID'] == 0)
		echo "<div class='p-3'>*** no household selected ***</div>";
	
	elseif ( $control['tab'] == "profile" )
		if ($control['action'] == "view")
			viewProfile();
}





function fillControlArray($control, $config, $tab) {
	
	$arr=array();
	$arr['hhID']=0;	
	$arr['db']=getDB($config);
	$stmt = $arr['db']->prepare("select * from users where id = :id");
	$stmt->execute(array(':id' => $control['hostId']));
	$users = $stmt->fetch();
	if ($users['pantry_id'] == 0)	// administrators default to Atwood pantry	
		$arr['hostPanId'] = 1;
	else
		$arr['hostPanId'] = $users['pantry_id'];	
	$arr['access_level_id']= $users['access_level_id'];
	$arr['hostFName'] = $users['firstname'];
	$arr["focus"] = "firstname";	
	$arr['firstname'] = "";
	$arr['lastname'] = "";	
	$arr['id'] = "";		
	
	if (isset($_POST['clear'])) { 
		$arr['hhID']=0;	
		$arr['firstname'] = "";
		$arr['lastname'] = "";
		$arr['id'] = "";		
		
	} elseif (isset($_POST['search'])) {
		$arr['hhID'] = exactMatch($arr['db']);
		$arr['firstname'] = $_POST['firstname'];
		$arr['lastname'] = $_POST['lastname'];

//	} elseif (isset($_POST['advancedSearch'])) {		
//		$arr['hhID'] = exactMatch($arr['db'], "advanced");
//		$arr['firstname'] = $_POST['firstname'];
//		$arr['lastname'] = $_POST['lastname'];
//		$arr['id'] = "";		
		
	} elseif (isset($_GET['hhID']))
		$arr['hhID'] = $_GET['hhID'];
		
//	if (isset($_POST['id']))
//		$arr['id'] = $_POST['id'];		

	if ($arr['hhID'] != 0) { 
		$sql = "SELECT * FROM household	WHERE id = :id"; 
		$stmt = $arr['db']->prepare($sql);
		$stmt->bindParam(':id', $arr['hhID'], PDO::PARAM_INT);	
		$stmt->execute();
		$household = $stmt->fetch();			
		$arr['firstname']=stripslashes(ucname($household['firstname']));
		$arr['lastname']=stripslashes(ucname($household['lastname']));
		$arr['id']=$arr['hhID'];	
	}	
	
//	} else {	
	
//		if ( isset($_POST['firstname'] ))
//			$arr['firstname'] = $_POST['firstname'];
//		elseif (isset($_GET['firstname'])) 
//			$arr['firstname'] = $_GET['firstname'];	
//		else
//			$arr['firstname'] = "";	

//		if ( isset($_POST['lastname'] ))
//			$arr['lastname'] = $_POST['lastname'];
//		elseif (isset($_GET['lastname'])) 
//			$arr['lastname'] = $_GET['lastname'];	
//		else
//			$arr['lastname'] = "";		

//		if ( isset($_POST['id'] ))
//			$arr['id'] = $_POST['id'];
//		elseif (isset($_GET['id'])) 
//			$arr['id'] = $_GET['id'];	
//		else
//			$arr['id'] = "";	



//	}

	if (isset($_GET['errCode']))
		if ($_GET['errCode'] == 17)
			$arr["focus"] = "firstname";
		elseif ($_GET['errCode'] == 42)
			$arr["focus"] = "initial";		
		elseif ($_GET['errCode'] == 20)
			$arr["focus"] = "dob";	
		elseif ($_GET['errCode'] == 43)
			$arr["focus"] = "address";					
		elseif ($_GET['errCode'] == 49)
			$arr["focus"] = "city";	
		elseif ($_GET['errCode'] == 50)
			$arr["focus"] = "county";	
		elseif ($_GET['errCode'] == 51)
			$arr["focus"] = "zip";	
		elseif ($_GET['errCode'] == 36)
			$arr["focus"] = "zip";				

	$arr["tab"] = "profile";
	$arr["action"] = "view";

	
	return $arr;
}

function doSearchBar() {
	global $control;

	$aLink="households.php?hhID=$control[hhID]&advanced=1";
	$nLink="households.php?hhID=$control[hhID]&new=1";	
	
	echo "
	<nav class='navbar navbar-light bg-gray-2'>
	  <form class='form-inline' method='post' action='households.php'>  
		<input class='form-control mr-sm-2' type='search' name='firstname' id='firstname' value='$control[firstname]' placeholder='First Name' aria-label='Search'>
		<input class='form-control mr-sm-2' type='search' name='lastname' id='lastname' value='$control[lastname]' placeholder='Last Name' aria-label='Search'>
		<input class='form-control mr-sm-2' style='width:100px;' type='search' name='id' id='id' value='$control[id]'  placeholder='ID' aria-label='Search'>	
		<button class='btn btn-outline-secondary my-2 my-sm-0 mr-sm-2' type='submit' name='search'><i class='fa fa-search'></i></button>
		<button class='btn btn-outline-secondary my-2 my-sm-0' type='submit' name='clear'>Clear</button>	
		<div class='ml-sm-3'><a class='text-dark' style='font-weight:bold;' href='$aLink' >Advanced...</a></div>
	  </form>
		<div class='text-right pr-2'><a class='btn btn-secondary btn-sm' href='$nLink' ><i class='fa fa-plus fa-lg'></i> New Household</a></div>
	</nav>";

}	


function viewProfile() {
	global $control;
	
	$sql = "SELECT * FROM household	WHERE id = :id"; 
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) { 
		$household = $stmt->fetch();
		$pName=stripslashes(ucname($household['firstname']));
//		if (!empty($row['initial']))
//			$pName.= stripslashes(ucname($household['initial'])) . ". ";
		$pName.= " " . stripslashes(ucname($household['lastname']));	
		
		$contact="";
		if ( ($household['streetnum']) || ($household['streetname']) )
			$contact.= $household['streetnum'] . ' ' . stripslashes(ucname($household['streetname']));
		if ($household['apartmentnum'])
			$contact.=  "<br>Apt " . ucname(stripslashes($household['apartmentnum']));
		$contact.="<br>";			
		$contact.= ucname(stripslashes($household['city'])) . ", " . strtoupper($household['state']) . " " . $household['zip_five'];
		if ($household['zip_four'])
			$contact.= "-" . $household['zip_four']; 
		$contact.="<br>";				
		if (is_numeric($household['phone1']))
			$contact.= ExpandPhone($household['phone1']) . "<br>";		
		if (is_numeric($household['phone2']))
			$contact.= ExpandPhone($household['phone2']) . "<br>"; 
		if ($household['email']) 
			$contact.="<a style='color:#841E14;text-decoration:underline;' href='mailto:$household[email]'>$household[email]</a><br>";		
	}	
	
//	$sql = "SELECT * FROM members WHERE householdID = :householdID AND in_household = 'Yes'";
	$sql = "SELECT * FROM members WHERE householdID = :householdID";  
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$numActive = $stmt->rowCount();			
	if ($numActive > 0) {
		$result = $stmt->fetchAll();		
		foreach($result as $members) {	
			if ($members['is_primary'])
				if ($members['gender'] == "male")
					$avatar="images/male-avatar.png";
				else
					$avatar="images/female-avatar.png";	
		}
	}	

?>
	<div class='container-fluid p-3'>
		<div class='row'>
			<div class='col-sm-auto'>
				<img src='<?php echo $avatar; ?>' class='p-1 m-2 bg-gray-6' style='height:auto;' /> 
			</div>
			<div class ='col-sm-auto p-3'>
				<span style='font-weight:bold;font-size:1.2rem;'><?php echo $pName; ?></span><br>
				<?php echo $contact; ?>
			</div>
			<div class ='col-sm p-3'>
				<div class='text-right'>
					<a class='text-dark pr-3' href='#' ><i class='fa fa-edit fa-lg'></i> edit</a>			
				</div>
				<div class='pt-4'>		
					<button class='btn btn-outline-dark my-2 my-sm-0 btn-lg' type='submit'>Print Shopping List</button>	
				</div>	
			</div>	
		</div>	
	</div>

	<div class="container-fluid">
		<ul class="nav nav-tabs">
		  <li class="nav-item">
			<a class="nav-link active text-dark" href="#">Profile</a>
		  </li>
		  <li class="nav-item">
			<a class="nav-link text-dark" href="#">Members</a>
		  </li>
		  <li class="nav-item">
			<a class="nav-link text-dark" href="#">Eligibility</a>
		  </li>
		  <li class="nav-item">
			<a class="nav-link text-dark" href="#">Shopping History</a>
		  </li>
		</ul>
	</div>	

	<div class="container-fluid bg-gray-2 m-0">

		<div class ='pl-3 pt-3 pb-0 mb-2 alert alert-danger bg-gray-2 border-0'>				
		<i class='fa fa-bell pr-2'></i>view notes
		</div>


		<div class='bg-gray-6 text-light pl-3 mt-3'><b>DEMOGRAPHIC</b></div>
		<div class='row pl-3'>
			<div class='col-sm pt-3'>Household size: <b>1</b></div>	
			<div class='col-sm pr-3 pt-3 text-right'><a class='text-dark pr-4' href="#" ><i class='fa fa-edit fa-lg'></i> edit</a></div>	
		</div>	
		<div class ='pl-3 pt-2'>Is this address a shelter? <b>No</b></div>
		<div class ='pl-3 pt-2'>Allergies in household? <b>No</b></div>
		<div class ='pl-3 pt-2'>Language: <b>English</b></div>	
		<div class ='pl-3 pt-2'>Difficulty Reading? <b>No</b></div>

		<div class='bg-gray-6 text-light pl-3 mt-3'><b>REGISTRATION</b></div>
		<div class='row pl-3'>
			<div class='col-sm pt-3'>Registration Date: <b>Jan 1, 2013</b></div>	
			<div class='col-sm pr-3 pt-3 text-right'><a class='text-dark pr-4' href="#" ><i class='fa fa-edit fa-lg'></i> edit</a></div>	
		</div>					


		<div class ='pl-3 pt-2'>Proof of Identity: <b>Provided on Jan 1, 2013</b></div>	
		<div class ='pl-3 pt-2 pb-3'>Proof of Reidence: <b>Provided on Jan 1, 2013</b></div>	

	</div>

<?php
}

function newHHForm($errMsg) {
	global $control;
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		echo "<center><div class='alert alert-danger text-center w-50 mt-3' role='alert'>	$errMsg[$err]</div></center>";
	}	
	
	$values=fillHHForm();
?>

<div class="container p-3">
	<div class="card">
	  <h5 class="card-header bg-gray-4 text-center">Register New Household</h5>
	  <div class="card-body bg-gray-2">
	  
	<form method='post' action='addhousehold.php'>  	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="firstname" id="firstname" value='<?php echo $values['firstname']; ?>' ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* LAST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="lastname" id="lastname"  value='<?php echo $values['lastname']; ?>'></div>
			</div>	
		</div>		
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* MIDDLE INITIAL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="initial" id="initial"  value='<?php echo $values['initial']; ?>'></div>
			</div>	
		</div>				
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* DATE OF BIRTH:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="dob" id="dob"  value='<?php echo $values['dob']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* GENDER:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php selectGender( "gender", $values['gender'] ); ?></div>
			</div>	
		</div>			
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* ADDRESS:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="address" id="address"  value='<?php echo $values['address']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>APT NUMBER:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="apartmentnum" id="apartmentnum"  value='<?php echo $values['apartmentnum']; ?>'></div>
			</div>	
		</div>		

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* CITY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="city" id="city"  value='<?php echo $values['city']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* COUNTY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="county" id="county" value='<?php echo $values['county']; ?>'></div>
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
				<div class="form-group mb-1"><input type="text" class="form-control" name="zip" id="zip"  value='<?php echo $values['zip']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>EMAIL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="email" id="email"  value='<?php echo $values['email']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PHONE 1:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="phone1" id="phone1" value='<?php echo $values['phone1']; ?>'></div> 
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right"><label class='pt-2'>PHONE 2:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group"><input type="text" class="form-control" name="phone2" id="phone2" value='<?php echo $values['phone2']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-lg text-center">* required field</div>	
		</div>		
		
		<div class='text-center mt-3'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='continue'>Continue</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
		</div>	
		<input type= 'hidden' name= 'tab' value= 'profile'>		
		</form>		  

	  </div>
	</div>
</div>	

<?php	
}	

function fillHHForm() {
	global $control;
	
	$arr = [	
		'firstname'			=> "",
		'lastname'			=> "", 
		'initial'			=> "", 
		'dob'				=> "",
		'gender'			=> "",	
		'address'			=> "",		
		'apartmentnum'		=> "",		
		'city'				=> "",
		'county'			=> "",
		'state'				=> "",
		'zip'				=> "", 
		'email'				=> "",
		'phone1'			=> "",
		'phone2'			=> ""
	];	
	
	if (isset($_GET['errCode'])) {	
		$arr['firstname']		=$_GET['firstname'];
		$arr['lastname']		=$_GET['lastname'];
		$arr['initial']			=$_GET['initial']; 
		$arr['dob']				=$_GET['dob'];
		$arr['gender']			=$_GET['gender'];	
		$arr['address']			=$_GET['address'];			
		$arr['apartmentnum']	=$_GET['apartmentnum'];		
		$arr['city']			=$_GET['city'];
		$arr['county']			=$_GET['county'];	
		$arr['state']			=$_GET['state'];
		$arr['zip']				=$_GET['zip'];	
		$arr['email']			=$_GET['email'];	
		$arr['phone1']			=$_GET['phone1'];
		$arr['phone2']			=$_GET['phone2'];
	} else {
		$sql = "SELECT * FROM pantries WHERE pantryID = :pantryID";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':pantryID', $control['hostPanId'], PDO::PARAM_INT);	
		$stmt->execute();
		$pantries = $stmt->fetch();		
		$arr['county']=$pantries['county'];
		$arr['state']=$pantries['state'];		
	}	

	return $arr;	
}
	
function getPantries( $db )
{
	$sql = "SELECT * FROM pantries WHERE is_active = 1";
	$db->setQuery($sql);
	try { $rows = $db->loadObjectList(); }
	catch ( RuntimeException $e ) {
		JError::raiseWarning( 500, $e->getMessage() );
		return false;
	} 
	return $rows;
}	

function sratch() {
	
?>	
<ul class="nav nav-tabs m-0 p-0" style='position: absolute;bottom:0;left:0;'>
  <li class="nav-item">
	<a class="nav-link active" href="#">Active</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="#">Link</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="#">Link</a>
  </li>

</ul>		
	
<?php	
	
	
}	

?>

	<script>	
		$("#phone1").inputmask({"mask": "(999) 999-9999"});	
		$("#phone2").inputmask({"mask": "(999) 999-9999"});			
	</script>	



