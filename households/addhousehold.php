<?php
/**
 * addhousehold.php
 * written: 5/20/2020
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
	require_once('../search.php');		

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	$control=fillControlArray($control, $config, "households");	
	
//	$control['db']=getDB($config);
	$control['err'] = 0;	
	
/* MAINLINE */

	if (!isset($_POST['cancel'])) {
		$household = cleanHousehold();
		if (!$control['err'] && !isset($_GET['continue'])) 		
			findPotentialMatch($household);			
		if (!$control['err']) {
			insertHousehold($household); 		
			$header=insertMembers($household); 
		} else
			$header=redirect($control['err']);			
	} else
		$header = "Location: ../households.php?tab=profile";
	
	header($header);	

/**
 * cleanHousehold( )
 *
 * Edit new household inputs, and return array of household table fields for table insert.
 * 
 * Rules for new household:
 *
 * 	1. Required Fields
 *
 *		- First Name
 *		- Last Name
 *		- Middle Initial
 *		- Date Of Birth
 *		- Gender
 *		- Address
 *		- City, County, State
 *		- Zip Code
 *
 *  2. Email is not required, but must have valid format if entered
 *  3. Zip code must match city, county, and state 
 *  4. Primary shopper may not be a member of another household
 *
 */		
function cleanHousehold() {
	global $control;

	$today=date("Y-m-d");
	
	$sql = "SELECT * FROM users WHERE id = :id"; 
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':id', $control['users_id'], PDO::PARAM_INT);	
	$stmt->execute();
	$users = $stmt->fetch();
	if ($users['pantry_id'] == 0)	// administrators default to Atwood pantry	
		$pantry_id = 1;
	else
		$pantry_id = $users['pantry_id'];		
	
	$arr = [	
		'firstname'			=> "",
		'lastname'			=> "", 
		'initial'			=> "", 
		'dob'				=> "",
		'gender'			=> "",	
		'streetnum'			=> "",		
		'streetname'		=> "",
		'std_streetname'	=> "",	
		'apartmentnum'		=> "",		
		'city'				=> "",
		'county'			=> "",
		'state'				=> "",
		'zip_five'			=> "", 
		'zip_four'			=> "",
		'email'				=> "",
		'use_overrides'		=> "",
		'phone1'			=> "",
		'phone2'			=> "",
		'shelter'			=> "",
		'pantry_id'			=> "",
		'regdate'			=> "",
		'lastactivedate'	=> "",					
		'addchangedate'		=> ""
	];	
	
	if (isset($_POST['continue'])) {
		$firstname = $_POST['firstname'];
		$lastname=$_POST['lastname'];
		$dob=$_POST['dob'];
		$gender=$_POST['gender'];		
		$initial=$_POST['initial'];
		$address=$_POST['address'];
		$apartmentnum=$_POST['apartmentnum'];
		$city=$_POST['city'];
		$county=$_POST['county'];
		$state=$_POST['state'];
		$zip=$_POST['zip'];
		$email=$_POST['email'];
		$phone1=$_POST['phone1'];
		$phone2=$_POST['phone2'];
	} else {	
		$firstname=$_GET['firstname'];
		$lastname=$_GET['lastname'];
		$dob=$_GET['dob'];	
		$gender=$_GET['gender'];		
		$initial=$_GET['initial'];
		$address=$_GET['address'];
		$apartmentnum=$_GET['apartmentnum'];
		$city=$_GET['city'];
		$county=$_GET['county'];
		$state=$_GET['state'];
		$zip=$_GET['zip'];
		$email=$_GET['email'];
		$phone1=$_GET['phone1'];
		$phone2=$_GET['phone2'];	
	}	
	
	if (empty($firstname) || empty($lastname))
		$control['err'] = 17;	
	elseif (empty($initial))
		$control['err'] = 42;		
	elseif (!isValidDate($dob, 'Y-m-d')) 	
		$control['err'] = 20;
	elseif (empty($address)) 	
		$control['err'] = 43;
	elseif (empty($city)) 	
		$control['err'] = 49;	
	elseif (empty($county)) 	
		$control['err'] = 50;			
	if (!$control['err']) {	
		$zip=editZipcode($control['db'], strtolower($city), strtolower($county), strtolower($state), $zip);
		$control['err']=$zip['errCode'];	
	}	
	
	if (!$control['err'])
		if ( !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) ) 
			$control['err'] = 24;	
		
	if (!$control['err'])
		if (!isValidPhone($phone1))		
			$control['err'] = 54;	

	if (!$control['err'])
		if (!isValidPhone($phone2))		
			$control['err'] = 55;	
		
	if (!$control['err']) {		

		$arr['firstname'] = strtolower(trim($firstname));	
		$arr['lastname'] = strtolower(trim($lastname));
		$arr['initial'] = strtolower(trim($initial));
		$arr['dob'] = $dob;
		$arr['gender'] = $gender;		
		$street = splitAddress( $address );	
		$arr['streetnum'] = $street['num'];
		$arr['streetname'] = strtolower($street['name']);
		$arr['std_streetname'] = standardStreetName($street['name']);
		$arr['apartmentnum'] = strtolower($apartmentnum);		
		$arr['city'] = strtolower(trim($city));
		$arr['county'] = strtolower(trim($county));		
		$arr['state'] = $state;		
		$arr['zip_five'] = $zip['zip_five']; 
		$arr['zip_four'] = $zip['zip_four'];
		$arr['email'] =	$email;
		$arr['use_overrides'] =	"Yes";
		$arr['phone1'] =CrunchPhone($phone1);		
		$arr['phone2'] =CrunchPhone($phone2);
		$arr['shelter'] = isShelter( $street['num'], $street['name'] );	
		$arr['pantry_id'] =	$pantry_id;
		$arr['regdate'] = $today;
		$arr['lastactivedate'] = $today;
		$arr['addchangedate'] =	$today;
	}
	
	return $arr;
}

function findPotentialMatch($in) {
	global $control;

	$stmt = $control['db']->prepare("DELETE FROM matched_display");
	$stmt->execute();		
	
// address 
	if ( !empty($in['streetnum']) && !empty($in['std_streetname']) && !$in['shelter'] ) {
		$streetnum= "%" . $in['streetnum'] . "%";	
		$std_streetname= "%" . $in['std_streetname'] . "%";
		$apartmentnum= $in['apartmentnum'] . "%";	
		$sql = "SELECT * FROM household
				WHERE streetnum LIKE :streetnum
				AND std_streetname LIKE :std_streetname
				AND apartmentnum LIKE :apartmentnum";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':streetnum', $streetnum, PDO::PARAM_STR);	
		$stmt->bindParam(':std_streetname', $std_streetname, PDO::PARAM_STR);				
		$stmt->bindParam(':apartmentnum', $apartmentnum, PDO::PARAM_STR);				
		$stmt->execute();
		$result = $stmt->fetchAll();			
		foreach ($result as $household) {
			$control['err']=14;
			insertMatchedDisplay( "address", $household, "foo" );
		}	
	}

// email	
	if (!empty($in['email'])) {
		$sql = "SELECT * FROM household	WHERE email = :email";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':email', $in['email'], PDO::PARAM_STR);	
		$stmt->execute();
		$result = $stmt->fetchAll();			
		foreach ($result as $household) { 
			$sql = "SELECT * FROM matched_display WHERE householdID = :householdID";
			$stmt = $control['db']->prepare($sql);
			$stmt->bindParam(':householdID', $household['id'], PDO::PARAM_INT);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total == 0) {
				$control['err']=14;
				insertMatchedDisplay( "email", $household, "foo" );
			}	
		}	
	}	
	
// phone1	
	if (is_numeric($in['phone1'])) {
		$sql = "SELECT * FROM household 
				WHERE phone1 = :phone1
				OR phone2 = :phone1";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':phone1', $in['phone1'], PDO::PARAM_STR);	
		$stmt->execute();
		$result = $stmt->fetchAll();			
		foreach ($result as $household) { 
			$sql = "SELECT * FROM matched_display WHERE householdID = :householdID";
			$stmt = $control['db']->prepare($sql);
			$stmt->bindParam(':householdID', $household['id'], PDO::PARAM_INT);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total == 0) { 
				$control['err']=14;
				insertMatchedDisplay( "phone1", $household, "foo" );
			}	
		}
	}	
	
// phone2	
	if (is_numeric($in['phone2'])) {
		$sql = "SELECT * FROM household 
				WHERE phone1 = :phone2
				OR phone2 = :phone2";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':phone2', $in['phone2'], PDO::PARAM_STR);	
		$stmt->execute();
		$result = $stmt->fetchAll();			
		foreach ($result as $household) { 
			$sql = "SELECT * FROM matched_display WHERE householdID = :householdID";
			$stmt = $control['db']->prepare($sql);
			$stmt->bindParam(':householdID', $household['id'], PDO::PARAM_INT);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total == 0) {
				$control['err']=14;
				insertMatchedDisplay( "phone2", $household, "foo" );
			}	
		}
	}		
	
// name	
	if ( !empty($in['firstname']) || !empty($in['lastname'])) {	
		$firstname= $in['firstname'] . "%";
		$lastname= $in['lastname'] . "%";	
		$sql = "SELECT * FROM members 
				WHERE (firstname LIKE :firstname ";
				for ($n = 1; $n <= 15; $n++) 
					$sql .= "OR nick" . $n . " LIKE :firstname ";
		$sql.=") AND ( lastname LIKE :lastname
				OR sur1 LIKE :lastname
				OR sur2 LIKE :lastname
				OR sur3 LIKE :lastname
				OR sur4 LIKE :lastname )";	
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
		$stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);			
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $members) { 
			$control['err'] = 14;		
			$sql = "SELECT * FROM matched_display WHERE householdID = :householdID";
			$stmt = $control['db']->prepare($sql);
			$stmt->bindParam(':householdID', $members['householdID'], PDO::PARAM_INT);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total == 0) 
				insertMatchedDisplay( "name", "foo", $members );
			else
				updateMatchedDisplay($members['householdID'],$members['firstname'],$members['lastname'],$members['initial'],$members['dob']);
		}
	}	
}

function insertHousehold($household) { 
	global $control;
	
	$data = [	
		'firstname'			=> $household['firstname'],
		'lastname'			=> $household['lastname'], 
		'initial'			=> $household['initial'], 
		'streetnum'			=> $household['streetnum'],		
		'streetname'		=> $household['streetname'],
		'std_streetname'	=> $household['std_streetname'],	
		'apartmentnum'		=> $household['apartmentnum'],		
		'city'				=> $household['city'],
		'county'			=> $household['county'],
		'state'				=> $household['state'],
		'zip_five'			=> $household['zip_five'], 
		'zip_four'			=> $household['zip_four'],
		'email'				=> $household['email'],
		'use_overrides'		=> $household['use_overrides'],
		'phone1'			=> $household['phone1'],
		'phone2'			=> $household['phone2'],
		'shelter'			=> $household['shelter'],
		'pantry_id'			=> $household['pantry_id'],
		'regdate'			=> $household['regdate'],
		'lastactivedate'	=> $household['lastactivedate'],					
		'addchangedate'		=> $household['addchangedate']
	];	
		
	$sql = "INSERT INTO household 
				  ( firstname,
					lastname, 
					initial, 							
					streetnum,		
					streetname,
					std_streetname,	
					apartmentnum,							
					city,
					county,
					state,
					zip_five, 
					zip_four,
					email,
					use_overrides,
					phone1,
					phone2,
					shelter,
					pantry_id,
					regdate,
					lastactivedate,					
					addchangedate) 
					
			VALUES (:firstname,
					:lastname, 
					:initial, 							
					:streetnum,		
					:streetname,
					:std_streetname,	
					:apartmentnum,							
					:city,
					:county,
					:state,
					:zip_five, 
					:zip_four,
					:email,
					:use_overrides,
					:phone1,
					:phone2,
					:shelter,
					:pantry_id,
					:regdate,
					:lastactivedate,					
					:addchangedate)"; 	

	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);	
}		

function insertMembers($household) {
	global $control;

// find household id of last inserted household
	$sql = "SELECT id FROM household ORDER BY id DESC LIMIT 1";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$grab =$stmt->fetch();
	$household['householdID'] = $grab['id'];		
	$household['is_primary'] = 1;
	
	$data = [
		'householdID'	=> $household['householdID'],
		'firstname'		=> $household['firstname'],
		'lastname' 		=> $household['lastname'],
		'initial'		=> $household['initial'],
		'dob'			=> $household['dob'],
		'gender'		=> $household['gender'],
		'is_primary' 	=> $household['is_primary']
	];
	
	$sql = "INSERT INTO members 
				  ( householdID,
					firstname,
					lastname, 
					initial,
					dob,
					gender,
					is_primary )
			VALUES (:householdID,
					:firstname,
					:lastname,
					:initial,								 
					:dob,
					:gender,
					:is_primary)";
					
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);							 

	$sql = "SELECT id FROM members ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$members =$stmt->fetch();
	addSurnames($members['id']);
	addNicknames($members['id'], $data['firstname']);	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
	writeUserLog( $control['db'], $date, $time, $household['householdID'], "household", $household['householdID'],  "REGISTER");	
//writeUserLog( $db, $date, $time, $household_id, $db_table, $table_id, $action, $shopping_date="0000-00-00", $shopping_time="00:00:00" )	

	$header = "Location: ../households.php?hhID=$household[householdID]&tab=members&isreg=1"; 
	return $header;
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

function redirect($err) {

	$header = "Location: ../households.php?tab=profile";
	$header .= "&isreg=1&newhousehold=1&errCode=" . $err;
	$header .= "&firstname=" . urlencode($_POST['firstname']);	
	$header .= "&lastname=" . urlencode($_POST['lastname']);
	$header .= "&initial=" . urlencode($_POST['initial']);	
	$header .= "&dob=" . urlencode($_POST['dob']);		
	$header .= "&gender=" . urlencode($_POST['gender']);		
	$header .= "&address=" . urlencode($_POST['address']);	
	$header .= "&apartmentnum=" . urlencode($_POST['apartmentnum']);		
	$header .= "&city=" . urlencode($_POST['city']);
	$header .= "&county=" . urlencode($_POST['county']);		
	$header .= "&state=" . urlencode($_POST['state']);		
	$header .= "&zip=" . urlencode($_POST['zip']); 
	$header .= "&email=" . urlencode($_POST['email']);
	$header .= "&phone1=" . urlencode($_POST['phone1']);		
	$header .= "&phone2=" . urlencode($_POST['phone2']);	 
	
	return $header;	
} 
?> 