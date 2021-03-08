<?php
/**
 * profile.php
 * written: 5/29/2020
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


function viewProfile() {
	global $control;
	
	$link="households.php?hhID=$control[hhID]";
	$pLink="registration_form.php?hhID=$control[hhID]";	
	$profile=fillProfile($link);
?>

	<div class="container-fluid bg-gray-2 m-0">

		<div class ='p-2 mb-0 alert alert-danger bg-gray-2 border-0'>
<?php		
			if (isset($_GET['notes'])) 
				doNotesForm($profile['notes']);
			else 
				echo $profile['notes']; 
?>		
		</div>

		<div class='bg-gray-6 text-light pl-3 '><b>DEMOGRAPHIC</b></div>
		<div class='row pl-3'>
			<div class='col-sm-3 pl-3 pt-2'>Household size:</div>
			<div class='col pt-2'><b><?php echo $profile['size']; ?></b></div>
			<div class='w-100'></div>
			<div class ='col-sm-3 pl-3 pt-2'>Is this address a shelter? </div>
			<div class ='col pt-2'><b><?php echo $profile['isShelter']; ?></b></div>
			<div class='w-100'></div>			
			<div class ='col-sm-3 pl-3 pt-2'>Allergies in household? </div>
			<div class ='col pt-2'><b><?php echo $profile['allergies']; ?></b></div>
		</div>	
		
		<div class='bg-gray-6 text-light pl-3 mt-3'><b>LITERACY</b></div>		
		<div class='row pl-3'>
			<div class='col-sm-3 pl-3 pt-2'>Language:</div>
			<div class='col pt-2'><b><?php echo $profile['language']; ?></b></div>		
			<div class='col-sm pr-3 pt-3 text-right'><a class='text-dark pr-4' href="<?php echo $link . "&editLit=1"; ?>"><i class='fa fa-edit fa-lg' title='edit'></i></a></div>
			<div class='w-100'></div>
			<div class ='col-sm-3 pl-3 '>Difficulty Reading? </div>
			<div class='col'><b><?php echo $profile['diffic_reading']; ?></b></div>
		</div>	
		
		<div class='bg-gray-6 text-light pl-3 mt-3'><b>REGISTRATION</b></div>		
		<div class='row pl-3'>
			<div class='col-sm-3 pl-3 pt-2'>Proof of Identity:</div>
			<div class='col pt-2'><b><?php echo $profile['id_verified']; ?></b></div>		
			<div class='col-sm pr-3 pt-3 text-right'><a class='text-dark pr-4' href="<?php echo $link . "&editId=1"; ?>"><i class='fa fa-edit fa-lg' title='edit'></i></a></div>
			<div class='w-100'></div>
			<div class ='col-sm-3 pl-3 '>Proof of Residence:</div>
			<div class='col'><b><?php echo $profile['addr_verified']; ?></b></div>
			<div class='w-100'></div>
			<div class ='col-sm-3 pl-3 pt-2 pb-3'>Date of Registration:</div>
			<div class='col pt-2'><b><?php echo $profile['regdate']; ?></b></div>
			<div class='col-sm pr-3 pt-2 text-right'>
				<a target='_blank' style='color:#841E14;text-decoration:underline;' class='pr-4' href="<?php echo $pLink; ?>"><i class='fa fa-print fa-lg pr-2' ></i>print registration form</a>
			</div>			
		</div>			
		
	</div>

<?php
}

function fillProfile($link) {
	global $control;
	
	$arr=array();
	$arr['notes']="";
	$arr['size']=0;	
	$arr['isShelter']="No";		
	$arr['allergies']="No";
	$arr['language']="";
	$arr['diffic_reading']="";	
	$arr['regdate']="0000-00-00";	
	$arr['id_verified']="0000-00-00";
	$arr['addr_verified']="0000-00-00";	
	
// household table	
	$sql = "SELECT * FROM household	WHERE id = :id"; 
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) { 
		$household = $stmt->fetch();
		
// notes
		if (isset($_GET['notes'])) 
			$arr['notes']=$household['notes'];
		elseif ( empty($household['notes']))
			$arr['notes']="<i class='fa fa-plus pr-2 p-1'></i><a style='color:inherit;' href='" . $link . "&notes=add' >Add Notes</a>";
		else
			$arr['notes']="<i class='fa fa-bell pr-2 p-1'></i><a style='color:inherit;' href='" . $link . "&notes=view' >View Notes</a>";			

// shelter		
		if ( $household['shelter'] > 0 ) {
			if ($shelters=getShelterRow($control['db'],$household['shelter']))	
				$arr['isShelter']="<i class='fa fa-home fa-lg pr-1 text-info'></i> " . $shelters['name'];
		}	
			
// language			
		$arr['language']=getLanguage( $control['db'], $household['language'] );
		
// difficulty reading
		if ($household['diffic_reading']=="Yes")	
			$arr['diffic_reading']="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Yes";
		else
			$arr['diffic_reading']="<i class='fa fa-check-circle fa-lg pr-1 text-success'></i> No";
		
// proof of identity
		if (isValidDate($household['id_verified'],'Y-m-d')) 	
			$arr['id_verified']= "<i class='fa fa-check-circle fa-lg pr-1 text-success'></i> provided on " . date("M j, Y", strtotime($household['id_verified']));
		else 
			$arr['id_verified']= "<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> not provided";		

// proof of residence		
		if (isValidDate($household['addr_verified'],'Y-m-d')) 	
			$arr['addr_verified']=  "<i class='fa fa-check-circle fa-lg pr-1 text-success'></i> provided on " . date("M j, Y", strtotime($household['addr_verified']));
		else 
			$arr['addr_verified']= "<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> not provided";	
		
// registration
		if (isValidDate($household['regdate'],'Y-m-d')) {	
			$arr['regdate']= "<i class='fa fa-check-circle fa-lg pr-1 text-success'></i> " . date("M j, Y", strtotime($household['regdate']));
			if ($pantries = getPantryRow( $control['db'], $household['pantry_id'] ))
				$arr['regdate']	.= " at $pantries[name]";		
		} else 
			$arr['regdate']= "<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> no registration date for household";

		
	}	

// members table		
	$sql = "SELECT * FROM members WHERE householdID = :householdID";  
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$numMembers = $stmt->rowCount();			
	$result = $stmt->fetchAll();
	foreach($result as $members) {	
		if ($members['in_household'] == "Yes")
			$arr['size']++;	
		if ($members['allergies'] == "Yes")
			$arr['allergies']="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Yes";	
	}	
	
	return $arr;
}

function doNotesForm($notes) {
	global $control;
	
	$link="households.php?hhID=$control[hhID]";
	
	if ($_GET['notes'] == "view") {
		echo "
		<div class='row text-dark'>
			<div class='col-sm'><h5>Notes</h5></div>	
			<div class='col-sm text-right'>
				<a class='text-dark' href='$link' ><i class='fa fa-eye-slash fa-lg'></i> hide</a>			
				<a class='text-dark pl-3' href='" . $link . "&notes=add' ><i class='fa fa-edit fa-lg'></i> edit</a>
			</div>	
		</div>";	
		echo "<textarea readonly nocursor class='alert alert-danger w-100'>$notes</textarea>\n";
		
	} elseif ($_GET['notes'] == "add") {
		echo "<div class='text-dark'><h5>Edit Notes</h5></div>\n";
?>	
		<form method='post' action='households.php'>  
		<textarea name='notes' id='notes' class='alert alert-danger w-100'><?php echo $notes; ?></textarea>
			<div class='mt-1'>
				<button class='btn btn-outline-secondary my-2 my-sm-0 mr-sm-2' type='submit' name='saveNotes'>Save</button>	
				<button class='btn btn-outline-secondary my-2 my-sm-0 mr-sm-2' type='submit' name='cancel'>Cancel</button>				
			</div>	
			<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>	
			<input type= 'hidden' name= 'tab' value= 'profile'>			
		</form>
<?php	
	}
}

function saveNotes($notes) {
	global $control;	
	
    $sql = "UPDATE household
            SET notes = :notes
            WHERE id = :id";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':notes', $notes, PDO::PARAM_STR);	
	$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_STR);	
	$stmt->execute();	
}

function literacyForm() {
	global $control;
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
	
	$values=fillLiteracyForm();
	$head="";
	if (isset($_POST['regliteracy']))	
		$head="Literacy Information for household of <b>$values[fName]</b>";
	else
		$head="Edit Literacy Information";		
?>
<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo $head; ?></h5>
	  <div class="card-body bg-gray-4">
	  
	<form method='post' action='households.php'>  	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>LANGUAGE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php selectLanguage($control['db'], "language", $values['language']); ?></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>DIFFICULTY READING:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php selectReadingDifficulty("diffic_reading", $values['diffic_reading']); ?></div>
			</div>	
		</div>		
		
		<div class='text-center mt-3'>
<?php
		if ($control['isreg'])
			echo "
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='saveLit'>Continue</button>
			<input type= 'hidden' name= 'isreg' value= '$control[isreg]'>\n";
		else
			echo "
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='saveLit'>Save</button>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>\n";
?>			
		</div>	
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>	
		<input type= 'hidden' name= 'tab' value= 'profile'>		
		</form>		  

	  </div>
	</div>
</div>	
</div>

<?php		
}	

function fillLiteracyForm() {
	global $control;
	
	$arr = [
		'fName'				=> "",
		'language'			=> "",
		'diffic_reading'	=> ""
	];	
	$sql = "SELECT * FROM household WHERE id = :id";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$household = $stmt->fetch();	
	$arr['fName']=stripslashes(ucname($household['firstname']));
	$arr['fName'].= " " . stripslashes(ucname($household['lastname']));		
	$arr['language']=$household['language'];
	$arr['diffic_reading']= $household['diffic_reading'];	
	return $arr;	
}

function updateLiteracy() {
	global $control;
	
	$sql = "UPDATE household
			SET language = :language,
			diffic_reading = :diffic_reading
			WHERE id = :id";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':language', $_POST['language'], PDO::PARAM_STR);	
	$stmt->bindParam(':diffic_reading', $_POST['diffic_reading'], PDO::PARAM_STR);		
	$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	
	if (!$control['isreg']) {
		$date = date('Y-m-d');
		$time = date('H:i:s');		
		writeUserLog( $control['db'], $date, $time, $control['hhID'], "household", $control['hhID'], "UPDATE LITERACY");
	}	
}

function idForm() {
	global $control;
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
	
	$values=fillIdForm();
	$head="";
	if ($control['isreg'])	
		$head="Registration Information for household of <b>$values[fName]</b>";	
	else
		$head="Edit Registration Information";		
?>
<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo $head; ?></h5>
	  <div class="card-body bg-gray-4">
	  
	<form method='post' action='households.php'>  	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PROOF OF IDENTITY PROVIDED ON:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="id_verified" id="id_verified"  value='<?php echo $values['id_verified']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PROOF OF RESIDENCE PROVIDED ON:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="addr_verified" id="addr_verified"  value='<?php echo $values['addr_verified']; ?>'></div>
			</div>	
		</div>		
		
		<div class='text-center mt-3'>
		
<?php
		if ($control['isreg'])
			echo "
			<button onclick='openInNewTab(" . '"registration_form.php?hhID=' . $control['hhID'] . '"' . ");' class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' 
			type='submit' name='saveId'>Complete and Print Registration Form</button>";		
		else
			echo "
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='saveId'>Save</button>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>\n";
?>				
		</div>	
		<input type= 'hidden' name= 'tab' value= 'profile'>	
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>			
		</form>		  

	  </div>
	</div>
</div>	
</div>

<?php		
}	

function fillIdForm() {
	global $control;
	
	$arr = [	
		'fName'				=> "",
		'id_verified'		=> "",
		'addr_verified'		=> ""
	];	
	
	if (isset($_GET['errCode'])) {	
		$arr['id_verified']		=$_GET['id_verified'];
		$arr['addr_verified']	=$_GET['addr_verified'];

	} else {
		$household= getHouseholdRow($control['db'], $control['hhID']);
		$arr['fName']=stripslashes(ucname($household['firstname']));
		$arr['fName'].= " " . stripslashes(ucname($household['lastname']));		
		if (!$control['isreg']) {		
			$arr['id_verified']=$household['id_verified'];
			$arr['addr_verified']= $household['addr_verified'];	
		}	
	}

// "0000-00-00" does not conform to the browser date format
	if ($arr['id_verified'] == "0000-00-00")
		$arr['id_verified']="";		
	if ($arr['addr_verified'] == "0000-00-00")
		$arr['addr_verified']="";	
		
	return $arr;	
}

function updateId() {
	global $control;
	
	$err=0;	
	if (isset($_POST['id_verified'])) {
		if ($_POST['id_verified'] != "0000-00-00" && $_POST['id_verified'] != "")
			if (!isValidDate($_POST['id_verified'], "Y-m-d"))	
				$err=52;
	}		
			
	if ((!$err) && isset($_POST['addr_verified']))
		if ($_POST['addr_verified'] != "0000-00-00" && $_POST['addr_verified'] != "")
			if (!isValidDate($_POST['addr_verified'], "Y-m-d"))	
				$err=53; 		
	
	if (!$err) {
		$sql = "UPDATE household
				SET id_verified = :id_verified,
				addr_verified = :addr_verified
				WHERE id = :id";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id_verified', $_POST['id_verified'], PDO::PARAM_STR);	
		$stmt->bindParam(':addr_verified', $_POST['addr_verified'], PDO::PARAM_STR);		
		$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
		$stmt->execute();
		
		if (!$control['isreg']) {
			$date = date('Y-m-d');
			$time = date('H:i:s');		
			writeUserLog( $control['db'], $date, $time, $control['hhID'], "household", $control['hhID'], "UPDATE REGISTRATION");
		}	
	}	
	return $err;
}
?>