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
	require_once('households/profile.php');
	require_once('households/members.php');	
	require_once('households/eligibility.php');	
	require_once('households/history.php');		
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "households");
	$control=loadAccessLevels();	
	doSaveUpdate();
	$control=setFocus($control);	
	
//////// ALERT! RUN THIS ONCE DURING 4.0 UPDATE THEN COMMENT OUT ////////////////	
//	initNicknames();
/////////////////////////////////////////////////////////////////////////////////	

////// RUN ONCE THEN DESTROY /////////////////////// 				
//	$sql = "SELECT * FROM shelters";
//	$stmt = $control['db']->query($sql);
//	while ($shelters = $stmt->fetch()) {
//		$std_streetname = standardStreetName($shelters['streetname']);			
//		$sql2 = "UPDATE shelters SET std_streetname	= :std_streetname WHERE id = :id";
//		$stmt2 = $control['db']->prepare($sql2);
//		$stmt2->bindParam(':std_streetname', $std_streetname, PDO::PARAM_STR);
//		$stmt2->bindParam(':id', $shelters['id'], PDO::PARAM_INT);		
//		$stmt2->execute();
//	}	
///////// END RUN ONCE DESTROY ////////////////////////////	

	doHeader("Households");
	doNavbar();	

	if (isset($_POST['search']) && $control['hhID'] < 1) {	
		doSearchBar();
		findHousehold();
	
	} elseif (isset($_GET['advanced'])) 
		advancedSearchForm();
	
	elseif ($control['isreg']) {
		
		if (isset($_GET['newhousehold']))
			if ($control['errCode'] == 14)
				displayMatchedResults();
			else
				newHHForm($errMsg);
			
		elseif ($control['tab'] == "members")
			if (isset($_GET['add']))
				membersForm("add", $errMsg);
			elseif (isset($_GET['edit']))
				membersForm("edit", $errMsg);			
			else
				viewMembers();				
			
		elseif (isset($_POST['regliteracy']))
			literacyForm();	

		elseif (isset($_POST['saveLit'])) 
			idForm();	

	} else {
		
		if ($control['tab'] != "history")
			doSearchBar();
		
		if ($control['tab'] == "profile") {	
		
			if (isset($_GET['editContact']))
				contactForm($errMsg);
			elseif (isset($_GET['editLit']))
				literacyForm();		
			elseif (isset($_GET['editId']))
				idForm();				
			elseif ( $control['hhID'] > 0 ) { 
				viewAvatar();
				doHHNavBar();
				viewProfile();			
			} else	
				noHouseholdMsg();
	
		} elseif ($control['tab'] == "members") {
		
			if (isset($_GET['add']))
				membersForm("add", $errMsg);
			elseif (isset($_GET['edit']))
				membersForm("edit", $errMsg);			
			elseif ( $control['hhID'] > 0 ) {
				viewAvatar();
				doHHNavBar();				
				viewMembers();	
			} else	
				noHouseholdMsg();	
		
		} elseif ($control['tab'] == "eligibility") {
		
			if ( $control['hhID'] > 0 ) { 
				viewAvatar();
				doHHNavBar();
				listEligibility();				
			} else	
				noHouseholdMsg();	
	
		} elseif ($control['tab'] == "history") {
		
			if ( isset($_GET['edit'])) 	
				historyUpdateForm("edit", $errMsg);				
			else { 
				if ( isset($_GET['delete'])) 	
					deleteVisit();
				if ( $control['hhID'] == 0 )  
					echo "<div>&nbsp;</div>";
				viewAvatar();
				doHHNavBar();
				doHistorySearchForm();
				listHistory();				
			}
		}	
	}	
	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	

//////// ALERT! RUN THIS ONCE DURING 4.0 UPDATE, THEN COMMENT OUT ////////////////
//require_once('addhousehold.php');
//function initNicknames() {
//	global $control;
//	
//	$sql = "SELECT * FROM members"; 
//	$stmt = $control['db']->query($sql);
//	while ($members = $stmt->fetch()) 
//		addNicknames( $members['id'], $members['firstname']); 	
//}
//
//function addNicknames( $id, $firstname ) {
//	global $control;
//
//	$data=array();
//	$sql = "SELECT * FROM nicknames WHERE firstname=:firstname";
//	$stmt = $control['db']->prepare($sql);
//	$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
//	$stmt->execute();
//	$total = $stmt->rowCount();	
//	if ($total > 0) {	
//		$nicknames = $stmt->fetch();
//		$sql = "UPDATE members SET ";
//		for ($n = 1; $n <= 15; $n++) {
//			if ($n==1) $comma=""; else $comma=",";
//			$index=	"nick" . $n;
//			$sql .= "$comma $index='" . $nicknames[$index] . "'";
//		}
//		$sql .=	" WHERE id = :id";
//		$stmt = $control['db']->prepare($sql);
//		$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
//		$stmt->execute();		
//	}	
//}	
//////////////////////////////////////////////////////


function noHouseholdMsg() {
	global $control;
	
	echo "<div>&nbsp;</div>";			
	doHHNavBar();	

	echo "
	<div class='container-fluid bg-gray-2'>
		<div class='p-3'>*** no household selected ***</div>
	</div>\n";
}
	
function setFocus($arr) {

	$arr["focus"] = "nofocus";	

	if (isset($_GET['errCode'])) 
		$arr['errCode'] = $_GET['errCode'];		

	if ($arr['errCode'] > 0) {
		if ($arr['errCode'] == 14)
			$arr["focus"] = "nofocus";		
		elseif ($arr['errCode'] == 17)
			$arr["focus"] = "efirstname";
		elseif ($arr['errCode'] == 24)
			$arr["focus"] = "email";		
		elseif ($arr['errCode'] == 42)
			$arr["focus"] = "initial";		
		elseif ($arr['errCode'] == 20)
			$arr["focus"] = "dob";	
		elseif ($arr['errCode'] == 39)
			$arr["focus"] = "zip";				
		elseif ($arr['errCode'] == 43)
			$arr["focus"] = "address";					
		elseif ($arr['errCode'] == 49)
			$arr["focus"] = "city";	
		elseif ($arr['errCode'] == 50)
			$arr["focus"] = "county";	
		elseif ($arr['errCode'] == 51)
			$arr["focus"] = "zip";	
		elseif ($arr['errCode'] == 54)
			$arr["focus"] = "phone1";	
		elseif ($arr['errCode'] == 55)
			$arr["focus"] = "phone2";				
		elseif ($arr['errCode'] == 36)
			$arr["focus"] = "zip";	
		elseif ($arr['errCode'] == 37)
			$arr["focus"] = "zip";				
		elseif ($arr['errCode'] == 75 || $arr['errCode'] == 78 || $arr['errCode'] == 82)
//			$arr["focus"] = "quantity_used" . $_GET['id'];	
			$arr["focus"] = $_GET['errid'];		
			
	} elseif (isset($_POST['search']))
		$arr["focus"] = "firstname";	
		
	elseif ($arr['tab'] == "profile") {
		
		$arr["focus"] = "firstname";		
		if ( isset($_GET['newhousehold'])) 
			$arr["focus"] = "nfirstname";	
		elseif (isset($_GET['editContact']))
			$arr["focus"] = "address";		
		elseif ( isset($_GET['editLit']) || isset($_POST['regliteracy']) ) 
			$arr["focus"] = "language";	
		elseif (isset($_GET['editId']) || (isset($_POST['saveLit']) && $arr['isreg']) ) 
			$arr["focus"] = "id_verified";	
		elseif (isset($_GET['notes'])) {
			if ($_GET['notes'] == "view")
				$arr["focus"] = "nofocus";		
			else
				$arr["focus"] = "notes";
		} elseif (isset($_POST['search']))
			$arr["focus"] = "nofocus";
		
	} elseif ($arr['tab'] == "members") {
		$arr["focus"] = "firstname";	
		if ( isset($_GET['add'])) 
			$arr["focus"] = "efirstname";
		elseif ( isset($_GET['edit'])) 
			$arr["focus"] = "efirstname";	
		elseif ($arr['isreg']) 
			$arr["focus"] = "nofocus";			
		
	} elseif ($arr['tab'] == "eligibility") {
		$arr["focus"] = "firstname";	
		if ( isset($_GET['add'])) 
			$arr["focus"] = "efirstname";		
		
	} elseif (isset($_GET['editContact'])) {	
		$arr["focus"] = "address";	
		
	} elseif ($arr['tab']=="history") {
		if (isset($_GET['edit']))
			$arr["focus"] = "quantity_used";
		else
			$arr["focus"] ="hhID";
	}	

	return $arr;		
}	

function viewAvatar() {	
	global $control;
	
	$hh=fillAvatar();
	if ( $hh['avatar'] != "") {
?>
		<div class='container-fluid p-3'>
<?php		
//		if (isset($_POST['moveMember']))
//			moveMember();	
		if ($warning = hasDuplicateMember())
			echo "<div class='alert alert-warning' role='alert'>$warning</div>";
		if ($warning = hasNoPrimaryMember()) 
			echo "<div class='alert alert-warning' role='alert'>$warning</div>";
		if ($warning = hasNoActiveMembers()) 	
			echo "<div class='alert alert-warning' role='alert'>$warning</div>";		
?>		
		
			<div class='row'>
				<div class='col-sm-auto'>
					<img src='<?php echo $hh['avatar']; ?>' class='p-1 m-2 bg-gray-6' style='height:auto;' /> 
				</div>
				<div class ='col-sm-auto p-3'>
					<span style='font-weight:bold;font-size:1.2rem;'><?php echo $hh['name']; ?></span><br>
					<?php echo $hh['contact']; ?>
				</div>
				<div class ='col-sm'>
					<div class='text-right pr-3'>
<?php					
					if ($control['hh_profile_update'])
						echo "<a class='text-dark' href='$hh[eLink]' ><i class='fa fa-edit fa-lg' title='edit contact'></i></a>\n";
					if ($control['hh_profile_delete'])
						echo "<a class='text-dark pl-2' onclick='return OKToDeleteHousehold();' 
						href='$hh[dLink]'><i class='fa fa-times fa-lg' title='delete household'></i></a>\n";	
?>
					</div>
					<div class='pt-4'>	

<?php						
						$aLink = "shopping_history/override.php?hhID=$control[hhID]";		
						echo "<a onclick='return okToPrintAnother(" . $control['hhID'] . ");' 
						style='margin-right:5px;' class='btn btn-success my-2 my-sm-0 btn-lg' href='" . $aLink . "' target='_blank' >
						Print Shopping List</a>";
?>		
						
					</div>	
				</div>	
			</div>	
		</div>		
<?php		
	} 
}

function fillAvatar() {
	global $control;
	
	$arr=array();
	$avatar="";
	$pName="";
	$contact="";	

	$sql = "SELECT * FROM household	WHERE id = :id"; 
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) { 
		$household = $stmt->fetch();
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
	
		$sql = "SELECT * FROM members WHERE householdID = :householdID";  
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
		$stmt->execute();
		$numActive = $stmt->rowCount();			
		if ($numActive > 0) {
			$result = $stmt->fetchAll();		
			foreach($result as $members) {	
				if ($members['is_primary']) {
					$pName=stripslashes(ucname($members['firstname']));
					$pName.= " " . stripslashes(ucname($members['lastname']));					
					if ($members['gender'] == "male")
						$avatar="images/male-avatar.png";
					else
						$avatar="images/female-avatar.png";	
				}	
			}
		}
	}	
	
	$arr['avatar']=$avatar;
	$arr['name']=$pName;
	$arr['contact']=$contact;	
	$arr['eLink']="households.php?hhID=$control[hhID]&editContact=1";
	$arr['dLink']="households.php?deleteHH=$control[hhID]";	
	return $arr;
}

function doHHNavBar() {
	global $control;
	
	$link="households.php?hhID=$control[hhID]";
	
	$pActive="";
	$mActive="";	
	$eActive="";	
	$hActive="";		
	if ($control['tab'] == "profile")
		$pActive="active";
	elseif ($control['tab'] == "members")
		$mActive="active";	
	elseif ($control['tab'] == "eligibility")
		$eActive="active";	
	elseif ($control['tab'] == "history")
		$hActive="active";			
	
	echo "
	<div class='container-fluid'>
		<ul class='nav nav-tabs'>
		  <li class='nav-item'>
			<a class='nav-link $pActive text-dark' href='" . $link . "&tab=profile'>Profile</a>
		  </li>
		  <li class='nav-item'>
			<a class='nav-link $mActive text-dark' href='" . $link . "&tab=members'>Members</a>
		  </li>
		  <li class='nav-item'>
			<a class='nav-link $eActive text-dark' href='" . $link . "&tab=eligibility'>Eligibility</a>
		  </li>
		  <li class='nav-item'>
			<a class='nav-link $hActive text-dark' href='" . $link . "&tab=history&menu=1'>Shopping History</a>
		  </li>
		</ul>
	</div>";
}

function newHHForm($errMsg) {
	global $control;
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
	
	$values=fillHHForm();
?>

<div class="container p-3">
	<div class="card">
	  <h5 class="card-header bg-gray-4 text-center">Register New Household</h5>
	  <div class="card-body bg-gray-2">
	  
	<form method='post' action='households/addhousehold.php'>  	
	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="firstname" id="nfirstname" value='<?php echo $values['firstname']; ?>' ></div>		
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
		<input type= 'hidden' name= 'newHH' value= 'profile'>		
		</form>		  

	  </div>
	</div>
</div>	

<?php	
}

function contactForm($errMsg) {
	global $control;
	
	$household=getHouseholdRow( $control['db'], $control['hhID'] );
	$fname=	ucname($household['firstname']) . " " . ucname($household['lastname']);
?>
<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center">Edit Contact Information for <b><?php echo $fname; ?></b></h5>
	  <div class="card-body bg-gray-4">
<?php	  

    if ($control['errCode'] > 0) {
		$err=$control['errCode'];
		displayAlert($errMsg[$err]);
	}	
	$values=fillHHForm();
?>	
	  
	<form method='post' action='households.php'>  	

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
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='saveContact'>Save</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
		</div>	
		<input type= 'hidden' name= 'tab' value= 'profile'>	
		<input type= 'hidden' name= 'newHH' value= 'profile'>
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>			
		</form>		  

	  </div>
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
	
	if ( isset($_GET['newhousehold'])) {
		
		if ($control['errCode'] > 0 ) {	
			$arr['firstname']	=$_GET['firstname'];
			$arr['lastname']	=$_GET['lastname'];
			$arr['initial']		=$_GET['initial'];
			$arr['dob']			=$_GET['dob'];
			$arr['gender']		=$_GET['gender'];
			$arr['address']		=$_GET['address'];			
			$arr['apartmentnum']=$_GET['apartmentnum'];		
			$arr['city']		=$_GET['city'];
			$arr['county']		=$_GET['county'];	
			$arr['state']		=$_GET['state'];
			$arr['zip']			=$_GET['zip'];	
			$arr['email']		=$_GET['email'];	
			$arr['phone1']		=$_GET['phone1'];
			$arr['phone2']		=$_GET['phone2'];
		} else {
			$sql = "SELECT * FROM pantries WHERE id = :id";
			$stmt = $control['db']->prepare($sql);
			$stmt->bindParam(':id', $control['users_pantry_id'], PDO::PARAM_INT);	
			$stmt->execute();
			$pantries = $stmt->fetch();		
			$arr['county']=$pantries['county'];
			$arr['state']=$pantries['state'];
		}	

	} elseif (isset($_POST['saveContact'])) {	
			
		$arr['address']			=$_POST['address'];			
		$arr['apartmentnum']	=$_POST['apartmentnum'];		
		$arr['city']			=$_POST['city'];
		$arr['county']			=$_POST['county'];	
		$arr['state']			=$_POST['state'];
		$arr['zip']				=$_POST['zip'];	
		$arr['email']			=$_POST['email'];	
		$arr['phone1']			=$_POST['phone1'];
		$arr['phone2']			=$_POST['phone2'];
				
	} else {

		$sql = "SELECT * FROM household WHERE id = :id";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id', $control['hhID'], PDO::PARAM_INT);	
		$stmt->execute();
		$household = $stmt->fetch();	
		
		if (empty($household['streetnum']))
			$arr['address']= htmlentities(ucname($household['streetname']));
		else
			$arr['address']= htmlentities($household['streetnum']) . " " . htmlentities(ucname($household['streetname']), ENT_QUOTES);			
		$arr['apartmentnum']= $household['apartmentnum'];		
		$arr['city']=htmlentities(ucname($household['city']), ENT_QUOTES);
		$arr['county']=htmlentities(ucname($household['county']), ENT_QUOTES);			
		$arr['state']= strtoupper( $household['state'] );
		$arr['zip']=$household['zip_five'];
		if (!empty($household['zip_four']))		
			$arr['zip'].="-" . $household['zip_four'];
		$arr['email']=$household['email'];	
		$arr['phone1']=$household['phone1'];
		$arr['phone2']= $household['phone2'];	
	}	
		
	return $arr;	
}
	
function getPantries( $db ) {
	
	$sql = "SELECT * FROM pantries WHERE is_active = 1";
	$db->setQuery($sql);
	try { $rows = $db->loadObjectList(); }
	catch ( RuntimeException $e ) {
		JError::raiseWarning( 500, $e->getMessage() );
		return false;
	} 
	return $rows;
}	

function doSaveUpdate() {
	global $control;

	if (isset($_POST['moveMember']))
		moveMember();		
	elseif (isset($_POST['saveContact']))	
		updateContact();					// households.php	
	elseif (isset($_POST['saveNotes']))	
		saveNotes($_POST['notes']);			// profile.php
	elseif (isset($_POST['saveLit']))	
		updateLiteracy();					// profile.php		
	elseif (isset($_POST['saveId']))	
		updateId();							// profile.php	
	elseif (isset($_GET['deletemember']))	
		deleteMember();						// members.php	
	elseif (isset($_GET['deleteHH']))	
		deleteHousehold();						// members.php			
}	

function updateContact() {
	global $control;
	
	$err=0;		
	if (empty($_POST['address'])) 	
		$err= 43;
	elseif (empty($_POST['city'])) 	
		$err= 49;	
	elseif (empty($_POST['county'])) 	
		$err= 50;			
	if (!$err) {	
		$zip=editZipcode($control['db'], strtolower($_POST['city']), strtolower($_POST['county']), strtolower($_POST['state']), $_POST['zip']);
		$err=$zip['errCode'];	
	}	
	
	if (!$err)
		if ( !empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) 
			$err= 24;	
	if (!$err)
		if (!isValidPhone($_POST['phone1']))		
			$err= 54;	
	if (!$err)
		if (!isValidPhone($_POST['phone2']))		
			$err= 55;

	if (!$err) {
		$street = splitAddress($_POST['address']);	
		$data = [		
			'id' => $control['hhID'],
			'streetnum' => $street['num'],
			'streetname' => strtolower($street['name']),
			'std_streetname' => standardStreetName($street['name']),
			'apartmentnum' => strtolower($_POST['apartmentnum']),		
			'city' => strtolower(trim($_POST['city'])),
			'county' => strtolower(trim($_POST['county'])),		
			'state' => $_POST['state'],		
			'zip_five' => $zip['zip_five'], 
			'zip_four' => $zip['zip_four'],
			'email' => $_POST['email'],
			'phone1' => CrunchPhone($_POST['phone1']),		
			'phone2' => CrunchPhone($_POST['phone2']),
			'shelter' => isShelter( $street['num'], $street['name'] )			
		];	
		
		$sql = "UPDATE household 
				SET streetnum =:streetnum,
					streetname =:streetname,
					std_streetname =:std_streetname,
					apartmentnum =:apartmentnum,
					city =:city,
					county =:county,
					state =:state,
					zip_five =:zip_five,
					zip_four =:zip_four,
					email =:email,
					phone1 =:phone1,
					phone2 =:phone2,
					shelter =:shelter
				WHERE id =:id";
						
		$stmt= $control['db']->prepare($sql);
		$stmt->execute($data);	
	} else {
		$control['formErr'] = "contact";
		$control['errCode'] = $err;
	}	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
//	writeUserLog( $control['db'], $date, $time, $control['users_id'], $control['users_pantry_id'], "UPDATE HOUSEHOLD", $control['hhID'] );	
	writeUserLog( $control['db'], $date, $time, $control['hhID'], "household", $control['hhID'], "UPDATE CONTACT");	
}

function deleteHousehold() {
	global $control;
	
	$date = date('Y-m-d');
	$time = date('H:i:s');	
	
	$sql = "DELETE FROM household WHERE id= :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['deleteHH'], PDO::PARAM_INT);				
	$stmt->execute();	
	
	$sql = "DELETE FROM members WHERE householdID = :householdID";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':householdID', $_GET['deleteHH'], PDO::PARAM_INT);				
	$stmt->execute();
	
	$sql = "DELETE FROM consumption WHERE household_id = :household_id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':household_id', $_GET['deleteHH'], PDO::PARAM_INT);				
	$stmt->execute();

	writeUserLog( $control['db'], $date, $time, $_GET['deleteHH'], "household", $_GET['deleteHH'], "DELETE"  );
// writeUserLog( $db, $date, $time, $household_id, $db_table, $table_id, $action, $shopping_date="0000-00-00", $shopping_time="00:00:00" ) {	
	

}	

function deleteVisit() {
	global $control;
	
	$sql = "DELETE FROM consumption 
			WHERE household_id =:household_id
			AND date= :date
			AND time =:time";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':household_id', $_GET['household_id'], PDO::PARAM_INT);
	$stmt->bindParam(':date', $_GET['date'], PDO::PARAM_STR);
	$stmt->bindParam(':time', $_GET['time'], PDO::PARAM_STR);	
	$stmt->execute();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
	writeUserLog( $control['db'], $date, $time, $_GET['household_id'], "consumption", 0, "DELETE SHOPPING", $_GET['date'], $_GET['time']);	
	
//	writeUserLog( $control['db'], $date, $time, $control['users_id'], $control['users_pantry_id'], "DELETE SHOPPING", $control['hhID'], 0, $_GET['date'], $_GET['time']);	
}
?>

<!-- Place any per-page style here -->


<!-- Place any per-page javascript here -->

<script>	

	$("#phone1").inputmask({"mask": "(999) 999-9999"});	
	$("#phone2").inputmask({"mask": "(999) 999-9999"});		

	function openInNewTab(url) {
	  var win = window.open(url, '_blank');
	  win.focus();
	}	

	function OKToDeleteMember(is_primary)	{
		
		if (is_primary==1) {
			alert("Primary Member may not be deleted.");
			return false;
		} else {
			ConfirmMsg = "Delete removes all member data from household. OK to delete?";
			input_box=confirm(ConfirmMsg);
			if (input_box==true) { 
				return true;
			} else {
				return false;
			}
		}	
	}
	
	function OKToDeleteHousehold() {
	
		ConfirmMsg = "Delete also removes household members and shopping history from the entire Pepbase system. OK to delete household?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}	

	function OKToDeleteVisit() {
	
		ConfirmMsg = "Delete removes shopping visit from the entire Pepbase system. OK to delete visit?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}		
	
	function onSelectDate() {
		
		if ( document.getElementById("dateType").value == "last18months" || document.getElementById("dateType").value == "all") {
			document.getElementById("hide-date-1").style.display="none";
			document.getElementById("hide-date-2").style.display="none";	
			document.getElementById("show-start-label").style.display="none";
			document.getElementById("hide-start-label").style.display="none";			
		} else if ( document.getElementById("dateType").value == "range" ) {
			document.getElementById("hide-date-1").style.display="block";
			document.getElementById("hide-date-2").style.display="block";
			document.getElementById("show-start-label").style.display="block";
			document.getElementById("hide-start-label").style.display="none";	
			document.getElementById("date1").focus();		
		} else {
			document.getElementById("hide-date-1").style.display="block";
			document.getElementById("hide-date-2").style.display="none";	
			document.getElementById("show-start-label").style.display="none";
			document.getElementById("hide-start-label").style.display="block";	
			document.getElementById("date1").focus();			
		}
	
	}	
	
</script>	