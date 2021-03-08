<?php
/**
 * addupdatemembers.php
 * written: 6/18/2020
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

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	$control=fillControlArray($control, $config, "members");
	
	$header = "Location: ../households.php?hhID=" . $control['hhID'] . "&tab=members";
	
	if ($control['isreg'])
		$header .= "&isreg=1"; 		
	
	if (!isset($_POST['cancel'])) {
		$members = getValues();
		if (!$control['err']) {
			if (isset($_POST['add'])) 
				$members['id']=insertMember($members); 		
			else
				updateMember($members);	
			rotatePrimary($members);
		} else	
			$header=redirect($header,$control['err']);				
	} 
	
	header($header);	

/**
 * getValues( )
 * written: 6-18-2020
 *
 */
function getValues() {
	global $control;

	$arr = [	
		'id'			=> 0,
		'householdID'	=> 0,
		'firstname'		=> "",
		'lastname'		=> "",
		'initial'		=> "",
		'gender'		=> "male",			
		'dob'			=> "",		
		'dod'			=> "",
		'in_household'	=> "Yes",
		'is_primary'	=> 0,		
		'allergies'		=> "No",		
		'incontinent'	=> "no",		
	];
	
	if (empty($_POST['firstname']) || empty($_POST['lastname']))
		$control['err'] = 17;	
	elseif (empty($_POST['initial']))
		$control['err'] = 42;			
	elseif (!isValidDate($_POST['dob'], 'Y-m-d')) 	
		$control['err'] = 20;	
	elseif (!primaryExists())
		$control['err'] = 56;	
	else {
		if (isset($_POST['id']))
			$arr['id'] =$_POST['id'];
		$arr['householdID'] = $control['hhID'];
		$arr['firstname'] = strtolower(trim($_POST['firstname']));	
		$arr['lastname'] = strtolower(trim($_POST['lastname']));
		$arr['initial'] = strtolower(trim($_POST['initial']));
		$arr['gender'] = $_POST['gender'];			
		$arr['dob'] = $_POST['dob'];
		$arr['dod'] = $_POST['dod'];		
		$arr['gender'] = $_POST['gender'];	
		$arr['in_household'] = $_POST['in_household'];
		if (isValidDate($_POST['dod'], 'Y-m-d'))
			$arr['in_household'] =	"No";		
		$arr['allergies'] = $_POST['allergies'];
		$arr['incontinent'] = $_POST['incontinent'];
		if ($_POST['is_primary'] == "Yes")
			$arr['is_primary']=1;
		else
			$arr['is_primary']=0;	
	}	

	return $arr;
}

function primaryExists() {
	global $control;
	
	$retval=true;
	if (isset($_POST['id']) && $_POST['is_primary'] == "No") {
		$sql = "SELECT * FROM members WHERE id != :id AND householdID = :householdID AND is_primary=1";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);			
		$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
		$stmt->execute();		
		$total = $stmt->rowCount();	
		if ( $total < 1 )
			$retval=false;
	}
	return $retval;
}	
	
function insertMember($members) { 
	global $control;

	$data = [	
		'householdID'	=> $members['householdID'],
		'firstname'		=> $members['firstname'],
		'lastname'		=> $members['lastname'],
		'initial'		=> $members['initial'],
		'gender'		=> $members['gender'],			
		'dob'			=> $members['dob'],		
		'dod'			=> $members['dod'],
		'in_household'	=> $members['in_household'],
		'is_primary'	=> $members['is_primary'],		
		'allergies'		=> $members['allergies'],		
		'incontinent'	=> $members['incontinent']		
	];	
	
	$sql = "INSERT INTO members 

			(householdID,
			firstname,
			lastname,
			initial,
			gender,			
			dob,		
			dod,
			in_household,
			is_primary,		
			allergies,		
			incontinent)	

			VALUES (:householdID,
					:firstname,
					:lastname,
					:initial,
					:gender,			
					:dob,		
					:dod,
					:in_household,
					:is_primary,		
					:allergies,		
					:incontinent)";				

	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);
	
	$sql = "SELECT id FROM members ORDER BY id DESC";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$members =$stmt->fetch();
	addSurnames($members['id']);
	addNicknames($members['id'], $data['firstname']);	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
//	writeUserLog( $control['db'], $date, $time, $control['users_id'], $control['users_pantry_id'], "ADD MEMBER", $control['hhID'], $members['id'] );
	writeUserLog( $control['db'], $date, $time, $control['hhID'], "members", $members['id'],  "ADD");		

	return $members['id'];
}		

function updateMember($members) {
	global $control;

	$data = [
		'id'			=> $members['id'],
		'firstname'		=> $members['firstname'],
		'lastname' 		=> $members['lastname'],
		'initial'		=> $members['initial'],
		'gender'		=> $members['gender'],		
		'dob'			=> $members['dob'],
		'dod'			=> $members['dod'],	
		'in_household'	=> $members['in_household'],		
		'is_primary' 	=> $members['is_primary'],
		'allergies'		=> $members['allergies'],		
		'incontinent'	=> $members['incontinent']		
	];	
		
	$sql = "UPDATE members 
			SET firstname=		:firstname,
				lastname=		:lastname, 
				initial=		:initial,
				gender=			:gender,
				dob=			:dob,
				dod=			:dod,
				in_household=	:in_household,		
				is_primary=		:is_primary,
				allergies=		:allergies,		
				incontinent=	:incontinent					
			WHERE id =:id";
					
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);

	$date = date('Y-m-d');
	$time = date('H:i:s');		
	writeUserLog( $control['db'], $date, $time, $control['hhID'], "members", $members['id'],  "UPDATE");		

}	

function addNicknames( $id, $firstname ) {
	global $control;

	$data=array();
	$sql = "SELECT * FROM nicknames WHERE firstname=:firstname";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
	$stmt->execute();
	$total = $stmt->rowCount();	
	if ($total > 0) {	
		$nicknames = $stmt->fetch();
		$sql = "UPDATE members SET ";
		for ($n = 1; $n <= 15; $n++) {
			if ($n==1) $comma=""; else $comma=",";
			$index=	"nick" . $n;
			$sql .= "$comma $index='" . $nicknames[$index] . "'";
		}
		$sql .=	" WHERE id = :id";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
		$stmt->execute();		
	}	
}	

function rotatePrimary($members) {
	global $control;
	
// if primary shopper has changed:
// 		1. remove primary status from previous primary member
//		2. update household table with new primary shopper's name	
	
	if ($members['is_primary']) {
		$sql = "UPDATE members SET is_primary = 0 WHERE id !=:id AND householdID = :householdID";	
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id', $members['id'], PDO::PARAM_INT);			
		$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);						
		$stmt->execute();
		
		$sql = "UPDATE household 
				SET firstname = :firstname,
				lastname = :lastname,
				initial = :initial
				WHERE id = :id";	
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':firstname', $members['firstname'], PDO::PARAM_STR);		
		$stmt->bindParam(':lastname', $members['lastname'], PDO::PARAM_STR);		
		$stmt->bindParam(':initial', $members['initial'], PDO::PARAM_STR);		
		$stmt->bindParam(':id', $members['householdID'], PDO::PARAM_INT);			
		$stmt->execute();		
	}	
}
	
function redirect($header,$err) {
	
	if (isset($_POST['add']))
		$header .= "&add=1";		
	else		
		$header .= "&edit=1&id=" . $_POST['id'];
	
	$header .= "&errCode=" . $err;
	$header .= "&firstname=" . urlencode($_POST['firstname']);	
	$header .= "&lastname=" . urlencode($_POST['lastname']);
	$header .= "&initial=" . urlencode($_POST['initial']);	
	$header .= "&dob=" . urlencode($_POST['dob']);	
	$header .= "&dod=" . urlencode($_POST['dod']);	
	$header .= "&gender=" . urlencode($_POST['gender']);	
	$header .= "&in_household=" . $_POST['in_household'];
	$header .= "&allergies=" . $_POST['allergies'];
	$header .= "&incontinent=" . $_POST['incontinent'];
	$header .= "&is_primary=" . $_POST['is_primary'];

	return $header;	
} 
?> 