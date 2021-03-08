<?php
/**
 * members.php
 * written: 6/17/2020
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

function viewMembers() {
	global $control;
	
	$link="households.php?tab=members&hhID=" . $control['hhID'];
	if ($control['isreg'])	
		$link.="&isreg=1";
?>	
	<div class="container-fluid bg-gray-2 m-0 p-0"> 	
<?php	
	if ($control['isreg']) {	
		$household=getHouseholdRow( $control['db'], $control['hhID'] );
		$hName=stripslashes(ucname($household['firstname']));
		$hName.= " " . stripslashes(ucname($household['lastname']));		
		echo "
		<div class='bg-gray-5 p-2'>
			<h5 class=' text-center'>Add members to household of <b>$hName</b></h5>
		</div>";	
	}	
?>		
		<div class="row border-dark border-bottom m-0 p-2">
			<div class='col-sm form-inline' style="color:#841E14;">
				<i class='fa fa-plus pr-2'></i><a style='color:inherit;' href='<?php echo $link; ?>&add=1'>Add Member</a>
<?php
				if ($control['isreg'])					
					echo "	
					<form class='m-0 p-0' method='post' action='households.php'>  	
						<button class='btn btn-primary text-white ml-3 mb-0' type='submit' name='regliteracy'>Continue with Registration</button>
						<input type= 'hidden' name= 'tab' value= 'profile'>	
						<input type= 'hidden' name= 'hhID' value= '$control[hhID]'>	
						<input type= 'hidden' name= 'isreg' value= '1'>	
					</form>\n";
?>						
			</div>
			<div class='col-sm pt-2'>
				<i class='fa fa-shopping-cart fa-lg'></i> - Primary Shopper	
			</div>
<?php
	$sql = "SELECT * FROM members WHERE householdID = :householdID AND in_household = 'Yes'";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);						
	$stmt->execute();	
	$total = $stmt->rowCount();	
?>
			<div class='col-sm pt-2 text-right'>
				<b><?php echo $total; ?></b> active members in household	
			</div>			
		</div>		
<?php	
	$sql = "SELECT * FROM members WHERE householdID = :householdID ORDER BY dob";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);						
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach ($result as $members) {	

		$data=fillMemberView($members);	
//		$link.="&id=" . $members['id'];		
?>	
<!--		<div class='row border-dark border-bottom m-0 p-2'> -->
		<div class='row m-0 p-2'> 
			<div class='col-sm form-inline'><?php echo "$data[img] $data[name]"; ?></div>
		
			<div class='col-sm'> 
				<div class='row'>
				
					<div class ='col-sm-4'>Date of Birth:</div>
					<div class='col'><?php echo $data['dob']; ?></div>
					<div class='w-100'></div>				
					<div class='col-sm-4'>Active:</div>
					<div class='col'><?php echo $data['active']; ?></div>		
					<div class='w-100'></div>
					<div class ='col-sm-4'>Allergies:</div>
					<div class='col'><?php echo $data['allergies']; ?></div>				
					<div class='w-100'></div>
					<div class ='col-sm-4'>Incontinence:</div>
					<div class='col'><?php echo $data['incontinent']; ?></div>				
				
				</div>
			</div>			
			
			<div class='col-sm text-right pr-4'>
				<a class='text-dark' href="<?php echo $link . "&edit=1&id=$members[id]"; ?>"><i class='fa fa-edit fa-lg' title='edit'></i></a>
				<a class='text-dark pl-2' onclick="return OKToDeleteMember(<?php echo $data['is_primary']; ?>);" 
				href="<?php echo $link . "&deletemember=1&id=$members[id]"; ?>"><i class='fa fa-times fa-lg' title='delete'></i></a>			
			</div>	
		</div>	
		
		<div class='border-dark border-bottom m-0 p-2'>	
<?php	

		$duplicate=inAnotherHousehold($members['firstname'], $members['lastname'], $members['dob']);
		if ($duplicate['memberID'] AND $data['active']=="Yes" ) {
			
			$otherLink="households.php?tab=members&hhID=" . $duplicate['otherHHID'];			
			echo "
			<div class='form-inline'>
				<form class='m-0 p-0' method='post' action='households.php'>  
					<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i>					
					$data[firstname] is also active in household of 
					<a style='color:#841E14;text-decoration:underline'; href='$otherLink'>$duplicate[otherHHFirst] $duplicate[otherHHLast] </a>
					<button class='btn btn-primary btn-sm ml-3 text-white' type='submit' name='moveMember'>Move to this household</button>
					<input type= 'hidden' name= 'dupMemberID' value= '$duplicate[memberID]'>	
					<input type= 'hidden' name= 'hhID' value= '$control[hhID]'>		
					<input type= 'hidden' name= 'tab' value= 'members'>";
//					if ( (isset($_GET['new']) && $_GET['new'] == "regmembers") || isset($_POST['newreg']) )
					if ($control['isreg'])							
						echo "<input type= 'hidden' name= 'isreg' value= '1'>";
				echo "		
				</form>
			</div>";
		}	
?>					
		</div>	
<?php		

	}
	echo "
	</div>\n";	
}

function fillMemberView($members) {
	
	$arr=array();
	$arr['firstname']= stripslashes(ucname($members['firstname']));
	$arr['name']="";
	$arr['name']=stripslashes(ucname($members['firstname']));
	$arr['name'].= " " . stripslashes(ucname($members['lastname']));		



	if (!isValidDate($members['dod'], 'Y-m-d')) {	
		if ($members['gender'] == "male")	
			$icon="male-icon.png";
		else
			$icon="female-icon.png";			
		$age=CalcAge($members['dob'], 0);
		if ($age >= 18)
			$height="65px;";
		elseif ($age >= 12)
			$height="55px;";
		elseif ($age >= 4)
			$height="45px;";
		else {
			$height="35px;";
			$icon="baby-icon.png";
			if ($age < 1) 
				$age=CalcAge($members['dob'], 1);				
		}	
		$arr['img'] = "<img style='margin:10px;height:" . $height . "' src='images/" . $icon . "' />";
		$arr['name'].= " ($age)";
	} else {
		
		$arr['img'] = "<img style='margin:10px;height:55px;' src='images/golden-cross.png' />";	
		$arr['name'] = "
		<div class='pl-3'>
		IN MEMORY OF<br>
		$arr[name]<br>" . date('M j, Y', strtotime("$members[dob]")) . " - " . date('M j, Y', strtotime("$members[dod]")) . "
		</div>\n";			
	}	
	
	$arr['is_primary'] = $members['is_primary']; 	
	if ( $members['is_primary'] ) 
		$arr['name'].=  "<i class='fa fa-shopping-cart fa-lg' style='padding-left:10px;'></i>";	
	
	$arr['active'] = $members['in_household'];
	if ($members['in_household']=="No")	
		$arr['active']="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> No";
		
	$arr['dob'] = date('M j, Y', strtotime("$members[dob]"));	
	$arr['allergies']=$members['allergies'];	
	if ($members['allergies']=="Yes")	
		$arr['allergies']="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Yes";

	$arr['incontinent']= ucname($members['incontinent']);
	if (ucname($members['incontinent']) == "Yes")	
		$arr['incontinent']="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Yes";	
	
	return $arr;
}

	
function membersForm($action, $errMsg) {
	global $control;
	
	$values=getMembersValues($action);
	
	$household=getHouseholdRow($control['db'], $control['hhID']);	
	if ($action=="add")
		$head="Add Member to Household of <b>" . ucname($household['firstname']) . " " . ucname($household['lastname']) . "</b>\n";
	else
		$head="Edit Member";		
?>
<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo $head;  ?></h5>
	  <div class="card-body bg-gray-4">
<?php	  
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}		  
?>	  
	  
	<form method='post' action='households/addupdatemember.php'>  	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="firstname" id="efirstname" value='<?php echo htmlentities(ucname($values['firstname']), ENT_QUOTES); ?>' ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* LAST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="lastname" id="lastname"  value='<?php echo htmlentities(ucname($values['lastname']), ENT_QUOTES); ?>'></div>
			</div>	
		</div>		
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* MIDDLE INITIAL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="initial" id="initial"  value='<?php echo ucname($values['initial']); ?>'></div>
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
				<div class="form-group text-right mb-1"><label class='pt-2'>* DATE OF BIRTH:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="dob" id="dob"  value='<?php echo $values['dob']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>DECEASED DATE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="dod" id="dod"  value='<?php echo $values['dod']; ?>'></div>
			</div>	
		</div>			
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ACTIVE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php yesNoRadio('in_household', $values['in_household']); ?></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PRIMARY SHOPPER:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php yesNoRadio('is_primary', $values['is_primary']); ?></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ALLERGIES:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php yesNoRadio('allergies', $values['allergies']); ?></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>INCONTINENT:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php yesNoRadio('incontinent', $values['incontinent']); ?></div> 
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-lg text-center">* required field</div>	
		</div>		
		
		<div class='text-center mt-3'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='<?php echo $action; ?>'>Save</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
		</div>	
		<input type= 'hidden' name= 'tab' value= 'profile'>	
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>			
<?php
//		if (isset($_POST['isreg']) || isset($_GET['isreg']) || (isset($_GET['new']) && $_GET['new'] == "regmembers"))
		if ($control['isreg'])	
			echo "<input type= 'hidden' name= 'isreg' value= '1'>\n";
		
		if ($action == "edit")
			echo "<input type= 'hidden' name= 'id' value= '$_GET[id]'>\n";		
?>
		
		</form>		  

	  </div>
	</div>
</div>	
</div>	

<?php	
}

function getMembersValues($action) {
	global $control;
	
	$arr = [	
		'firstname'		=> "",
		'lastname'		=> "",
		'initial'		=> "",
		'gender'		=> "male",			
		'dob'			=> "",		
		'dod'			=> "",
		'in_household'	=> "Yes",
		'is_primary'	=> "No",		
		'allergies'		=> "No",		
		'incontinent'	=> "no",		
	];	
	
	if (isset($_GET['errCode'])) {
		
		$arr['firstname']		= $_GET['firstname'];
		$arr['lastname']		= $_GET['lastname'];
		$arr['initial']			= $_GET['initial'];	
		$arr['gender']			= $_GET['gender'];
		$arr['dob']				= $_GET['dob'];	
		$arr['dod']				= $_GET['dod'];	
		$arr['in_household']	= $_GET['in_household'];		
		$arr['is_primary']		= $_GET['is_primary'];
		$arr['allergies']		= $_GET['allergies'];
		$arr['incontinent']		= $_GET['incontinent'];	
		
	} elseif ($action == "edit") {

		$members=getMemberRow( $control['db'], $_GET['id'] );
		$arr['firstname']		= $members['firstname'];
		$arr['lastname']		= $members['lastname'];
		$arr['initial']			= $members['initial'];	
		$arr['gender']			= $members['gender'];
		$arr['dob']				= $members['dob'];	
		$arr['dod']				= $members['dod'];	
		$arr['in_household']	= $members['in_household'];		
		if ($members['is_primary']) 
			$arr['is_primary'] = "Yes";
		else
			$arr['is_primary'] = "No";			
		$arr['allergies']		= $members['allergies'];
		$arr['incontinent']		= $members['incontinent'];			
	}

// "0000-00-00" does not conform to the browser date format
	if ($arr['dob'] == "0000-00-00")
		$arr['dob']="";		
	if ($arr['dod'] == "0000-00-00")
		$arr['dod']="";
	
	return $arr;
}	

function deleteMember() {
	global $control;
	
	$sql = "DELETE FROM members WHERE id = :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);				
	$stmt->execute();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
	writeUserLog( $control['db'], $date, $time, $control['hhID'], "members", $_GET['id'],  "DELETE");		
}	

function moveMember() {
	global $control;
// when duplicate member is moved to current household, deactivates member in other household	
	
	$sql = "UPDATE members SET in_household='No' WHERE id =:id";	
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_POST['dupMemberID'], PDO::PARAM_INT);				
	$stmt->execute();	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
	writeUserLog( $control['db'], $date, $time, $control['users_id'], $control['users_pantry_id'], "MOVE MEMBER", $control['hhID'], $_POST['dupMemberID'] );	
}	
?>