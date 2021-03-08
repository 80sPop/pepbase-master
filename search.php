<?php
/**
 * search.php
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

function doSearchBar() {
	global $control;

	$aLink="households.php?hhID=$control[hhID]&tab=$control[tab]&advanced=1";
	$nLink="households.php?hhID=$control[hhID]&tab=profile&newhousehold=1&isreg=1";	
	
	echo "
	<nav class='navbar navbar-light bg-gray-2'>
	  <form class='form-inline' method='post' action='households.php'>  
		<input type= 'hidden' name= 'tab' value = '$control[tab]' />	  
		<input class='form-control mr-sm-2' type='search' name='firstname' id='firstname' value='$control[firstname]' placeholder='First Name' aria-label='Search'>
		<input class='form-control mr-sm-2' type='search' name='lastname' id='lastname' value='$control[lastname]' placeholder='Last Name' aria-label='Search'>
		<input class='form-control mr-sm-2' style='width:100px;' type='search' name='id' id='id' value='$control[id]'  placeholder='ID' aria-label='Search'>	
		<button class='btn btn-outline-secondary my-2 my-sm-0 mr-sm-2' type='submit' name='search'><i class='fa fa-search'></i></button>
		<button class='btn btn-outline-secondary my-2 my-sm-0' type='submit' name='clear'>Clear</button>	
		<div class='ml-sm-3'><a class='text-dark' style='font-weight:bold;' href='$aLink' >Advanced...</a></div>
	  </form>";
		if ($control['hh_profile_update'])
			echo "<div class='text-right pr-2'><a class='btn btn-secondary btn-sm' href='$nLink' ><i class='fa fa-plus fa-lg pr-1'></i> Add Household</a></div>\n";
	echo "</nav>\n";
}
		
function exactMatch($db) {
// called from: households.php	
// return values:
//		0 - none or more than 1 households found	
//		household id # - when exactly one household is found

	$householdID=0;
	$dobQ="1";	
	$initial="%";
	
	if (isNameOrDOBEntered()) {
		$firstname= $_POST['firstname'] . "%";
		$lastname= $_POST['lastname'] . "%";	
		if (isset($_POST['initial']))
			$initial= $_POST['initial'] . "%";	
		if ( isset($_POST['dob']) && isValidDate($_POST['dob'], 'Y-m-d') ) {
			$dobQ  = "dob = :dob";	
		}	
	}	
	
	if ( isset($_POST['id']) && is_numeric($_POST['id'])) {
			
			$sql = "SELECT id FROM household WHERE id = :id";		
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total == 1) 
				$householdID=$_POST['id'];
			
	} elseif ( isNameOrDOBEntered() && !isAddressEntered() ) {	

			$sql = "SELECT * FROM members WHERE (firstname LIKE :firstname ";
			for ($n = 1; $n <= 15; $n++) 
				$sql .= "OR nick" . $n . " LIKE :firstname ";
			$sql.=")
				AND ( lastname LIKE :lastname
				OR sur1 LIKE :lastname
				OR sur2 LIKE :lastname
				OR sur3 LIKE :lastname
				OR sur4 LIKE :lastname )	
				AND initial LIKE :initial
				AND $dobQ";		
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
			$stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);				
			if ( $dobQ != "1")
				$stmt->bindParam(':dob', $_POST['dob'], PDO::PARAM_STR);	
			$stmt->bindParam(':initial', $initial, PDO::PARAM_STR);				
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total == 1) {
				$members =$stmt->fetch();
				$householdID=$members['householdID'];
			}	
			
	} elseif (isAddressEntered()) { 
		
		$dobQ="1";
		if ( isValidDate($_POST['dob'], 'Y-m-d') ) 	
			$dobQ  = "dob = '" . $_POST['dob'] . "'";			
	
		$found=0;
		$result=searchHousehold($db);
		foreach ($result as $household) {
			$householdID=$household['id'];
			if (isNameOrDOBEntered() || $dobQ != "1") {
				$sql = "SELECT * FROM members WHERE
						householdID = :householdID
						AND (firstname LIKE :firstname ";
				for ($n = 1; $n <= 15; $n++) 
					$sql .= "OR nick" . $n . " LIKE :firstname ";
				$sql.=") AND ( lastname LIKE :lastname
						OR sur1 LIKE :lastname
						OR sur2 LIKE :lastname
						OR sur3 LIKE :lastname
						OR sur4 LIKE :lastname )
						AND initial LIKE :initial						
						AND $dobQ";		
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':householdID', $householdID, PDO::PARAM_INT);						
				$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
				$stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);	
				if ( $dobQ != "1")
					$stmt->bindParam(':dob', $_POST['dob'], PDO::PARAM_STR);	
				$stmt->bindParam(':initial', $initial, PDO::PARAM_STR);					
				$stmt->execute();
				$total = $stmt->rowCount();	
				if ($total > 0)
					$found++;
			} else
				$found++;	
		}
		if ($found > 1)
			$householdID=0;				
	}		

	return $householdID;
}


function findHousehold() {
	global $control;
	
	$stmt = $control['db']->prepare("DELETE FROM matched_display");
	$stmt->execute();
	
	$found=0;
	$dobQ=1;
	$initial="%";

	$firstname= $_POST['firstname'] . "%";
	$lastname= $_POST['lastname'] . "%";
	if (isset($_POST['initial']))		
		$initial= $_POST['initial'] . "%";		
	if ( isset($_POST['dob']) && isValidDate($_POST['dob'], 'Y-m-d') ) 
		$dobQ = "dob = '$_POST[dob]'";	
	
	if ( isNameOrDOBEntered() && !isAddressEntered() ) {		
	
// PDO SELECT with "home cooked" escape function (faster than prepare->execute)
		$sql = "SELECT * FROM members 
				WHERE (firstname LIKE '" . escape($firstname) . "'";
				for ($n = 1; $n <= 15; $n++) 
					$sql .= "OR nick" . $n . " LIKE '" . escape($firstname) . "' ";
		$sql.=") AND ( lastname LIKE '" . escape($lastname) . "'
				OR sur1 LIKE '" . escape($lastname) ."'
				OR sur2 LIKE '" . escape($lastname) ."'
				OR sur3 LIKE '" . escape($lastname) ."'
				OR sur4 LIKE '" . escape($lastname) ."' )
				AND initial LIKE '" . escape($initial) . "'
				AND $dobQ";	
		$stmt = $control['db']->query($sql);		
		$total = $stmt->rowCount();	
		if ($total > 1) {
			$result = $stmt->fetchAll();			
			writeMatchedDisplay("name", "foo", $result);			
			$found=1;
		} 
	
	} elseif (isAddressEntered()) { 

		$result=searchHousehold($control['db']);
		foreach ($result as $household) {
			$sql = "SELECT * FROM members 
					WHERE householdID = $household[id]
					AND (firstname LIKE '" . escape($firstname) . "' "; 
					for ($n = 1; $n <= 15; $n++) 
						$sql .= "OR nick" . $n . " LIKE '" . escape($firstname) . "' ";
			$sql.=") AND ( lastname LIKE '" . escape($lastname) . "'
					OR sur1 LIKE '" . escape($lastname) ."'
					OR sur2 LIKE '" . escape($lastname) ."'
					OR sur3 LIKE '" . escape($lastname) ."'
					OR sur4 LIKE '" . escape($lastname) ."' )
					AND initial LIKE '" . escape($initial) . "'						
					AND $dobQ";				
			$stmt = $control['db']->query($sql);				
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$result2 = $stmt->fetchAll();			
				writeMatchedDisplay("address",$household,$result2);					
				$found=1;
			}	
		}
	}			
	
	if ($found >0)
		displayMatchedResults();
	else {
		$id="";
		if (isset($_POST['id']))
			$id=$_POST['id'];
		echo "
		<div class='p-3'>
			No matches found for <b>$control[firstname] $control[lastname] $id</b>
		</div>";
	}	
}	

function isNameOrDOBEntered() {
	$isName= (isset($_POST['firstname']) && (trim($_POST['firstname']) != "" || trim($_POST['lastname']) != ""));
	$isInitial= ( isset($_POST['initial']) && trim($_POST['initial']) != "" );	
	$isDOB = (isset($_POST['dob']) && isValidDate($_POST['dob'], 'Y-m-d'));
	return ($isName || $isInitial || $isDOB);
}	

function isAddressEntered() {
	if (
		isset($_POST['address']) && trim($_POST['address']) !=""			||
		isset($_POST['apartmentnum']) && trim($_POST['apartmentnum']) !=""	||
		isset($_POST['city']) && trim($_POST['city']) !=""					||
		isset($_POST['county']) && trim($_POST['county']) !=""				||
		isset($_POST['state']) && trim($_POST['state']) !=""				||
		isset($_POST['zip']) && trim($_POST['zip']) !=""   					||
		isset($_POST['email']) && trim($_POST['email']) !=""				||
		isset($_POST['phone1']) && trim($_POST['phone1']) !=""				||
		isset($_POST['phone2']) && trim($_POST['phone2']) !=""		
	)
		return true;
	else
		return false;		
}	

function searchHousehold($db) {
	
	$retVal=array();
	if ( isValidDate($_POST['dob'], 'Y-m-d') ) {	
		$dob  = $_POST['dob'];
		$dobQ  = "dob = '" . $_POST['dob'] . "'";
	} else 		
		$dobQ  = "1";

	$data=array();
	
	if (isset($_POST['address'])) {
		$street = splitAddress( $_POST['address'] );	
// when adding a new household, skip address matching when address is a shelter
		if ( isset($_POST['newHH']) && isShelter($street['num'], $street['name']) ) {		
			$data['streetnum']= "%";			
			$data['std_streetname'] = "%";	
		} else {	
			$data['streetnum'] = trim($street['num']) . "%";			
			$data['std_streetname'] = "%" . standardStreetName(trim($street['name'])) . "%";
		}	
	}	
	if ( isset($_POST['apartmentnum']) )	$data['apartmentnum'] = trim($_POST['apartmentnum']) . "%";
	if ( isset($_POST['city']) ) 			$data['city'] = trim($_POST['city']) . "%";
	if ( isset($_POST['county']) ) 			$data['county'] = trim($_POST['county']) . "%";
	if ( isset($_POST['state']) ) 			$data['state'] = trim( $_POST['state'] ) . "%";
	$zip=editZipcode($db, strtolower($_POST['city']), strtolower($_POST['county']), strtolower($_POST['state']), $_POST['zip']);
	$data['zip_five'] = trim($zip['zip_five']) . "%";
	$data['zip_four'] = trim($zip['zip_four']) . "%";	
	if ( isset($_POST['email']) )			$data['email'] = trim($_POST['email']) . "%";
	if ( isset($_POST['phone1']) ) 			$data['phone1'] = trim(CrunchPhone( $_POST['phone1'] )) . "%";
	if ( isset($_POST['phone2']) ) 			$data['phone2'] = trim(CrunchPhone( $_POST['phone2'] )) . "%";	
	
// PDO with "home cooked" escape() function (faster)
	$sql = "SELECT * FROM household
		WHERE streetnum LIKE '" . escape($data['streetnum']) . "'
		AND std_streetname LIKE '" . escape($data['std_streetname']) . "'
		AND apartmentnum LIKE '" . escape($data['apartmentnum']) . "'
		AND city 		LIKE '" . escape($data['city']) . "'
		AND county 		LIKE '" . escape($data['county']) . "'
		AND state 		LIKE '" . escape($data['state']) . "'
		AND zip_five 	LIKE '" . escape($data['zip_five']) . "'
		AND zip_four 	LIKE '" . escape($data['zip_four']) . "'
		AND email 		LIKE '" . escape($data['email']) . "'
		AND ( phone1 LIKE '" . escape($data['phone1']) . "' OR phone2 LIKE '" . escape($data['phone1']) . "' )					
		AND ( phone1 LIKE '" . escape($data['phone2']) . "' OR phone2 LIKE '" . escape($data['phone2']) . "' )";	
	$stmt = $db->query($sql);	
	
	$total = $stmt->rowCount();	
	if ($total > 0) 
		$retVal = $stmt->fetchAll();
	
	return $retVal;
}	

function writeMatchedDisplay($type, $household, $memberArr) {
	global $control;
	
// first, build table of matching households	
	foreach($memberArr as $members) {	
		$sql = "SELECT * FROM matched_display WHERE householdID = :householdID";		
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':householdID', $members['householdID'], PDO::PARAM_INT);	
		$stmt->execute();
		$total = $stmt->rowCount();	
// before insertion, check if household is already there		
		if ($total == 0) {
			$matched_display = $stmt->fetch();			
			insertMatchedDisplay( $type, $household, $members );		
		} else {
			$result2 = $stmt->fetchAll();				
			foreach($result2 as $matched_display)	
				updateMatchedDisplay($members['householdID'],$members['firstname'],$members['lastname'],$members['initial'],$members['dob']);				
		}		
	}	
}

function insertMatchedDisplay($type, $household, $members) {
// also called from addhousehold.php	
	global $control;

	$matchedfirst = "";
	$matchedlast = "";
	$initial="";
	$dob = '0000-00-00';
	$is_active=1;
	
	if ($type == "name") {
		$matchedfirst=$members["firstname"];
		$matchedlast=$members["lastname"];		
		$initial=$members['initial'];
		$dob=$members['dob'];
		if ($members['in_household'] == "No")
			$is_active = 0;		
		$sql = "SELECT * FROM household WHERE id = :id";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id', $members['householdID'], PDO::PARAM_STR);	
		$stmt->execute();
		$total = $stmt->rowCount();	
		if ($total > 0) 		
			$household = $stmt->fetch();	
		else {
			writeErrorLog();	// table error - member has no matching household ID
			$household=array();
			$household['id']=$members['householdID'];
			$household['firstname']="";
			$household['lastname']="";
			$household['streetnum']="";
			$household['streetname']="";
			$household['std_streetname']="";	
			$household['apartmentnum']="";
			$household['city']="";
			$household['county']="";
			$household['state']="";
			$household['zip_five']="";
			$household['zip_four']="";
			$household['phone1']="";			
			$household['phone2']="";
			$household['email']="";	
		}	
	}	

// split multiple/hyphenated surnames
	$sur[0]=""; $sur[1]=""; $sur[2]=""; $sur[3]="";
	if (!empty($matchedlast)) {
		$matches = preg_split("/[^a-z^0-9]/i", trim($matchedlast));	
		if ( count($matches) > 1 ) 
			for ($i = 0; $i <= count($matches)-1; $i++)
				$sur[$i]=$matches[$i];
	}	

// PDO with home-cooked escape function. (faster than prepare and execute)	
	$firstname=escape($household['firstname']);
	$lastname=escape($household['lastname']);
	$matchedfirst=escape($matchedfirst);
	$matchedlast=escape($matchedlast);
	$initial==escape($matchedlast);
	$dob=escape($dob);
	$sur[0]=escape($sur[0]);
	$sur[1]=escape($sur[1]);
	$sur[2]=escape($sur[2]);
	$sur[3]=escape($sur[3]);
	$streetnum=escape($household['streetnum']);
	$streetname=escape($household['streetname']);
	$std_streetname=escape($household['std_streetname']);
	$apartmentnum=escape($household['apartmentnum']);
	$city=escape($household['city']);
	$county=escape($household['county']);
	$state=escape($household['state']);
	$zip_five=escape($household['zip_five']);
	$zip_four=escape($household['zip_four']);
	$phone1=escape($household['phone1']);
	$phone2=escape($household['phone2']);
	$email=escape($household['email']);
	
// add nicknames
	$nicknames=array();
	$sql = "SELECT * FROM nicknames WHERE firstname=:firstname";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
	$stmt->execute();
	$total = $stmt->rowCount();	
	if ($total > 0) {	
		$nicknames = $stmt->fetch();
		for ($n = 1; $n <= 15; $n++) {
			if ($n==1) $comma=""; else $comma=",";
			$index=	"nick" . $n;
			$sql .= "$comma $index='" . $nicknames[$index] . "'";
		}	
	}	

	$sql = "INSERT INTO matched_display (

				householdID,
				firstname,				
				lastname,		
				matchedfirst,
				matchedlast,
				initial,
				dob,
				sur1,
				sur2,
				sur3,
				sur4,	
				streetnum, 				
				streetname, 
				std_streetname,	
				apartmentnum,
				city,
				county,
				state,
				zip_five,
				zip_four,
				phone1,					
				phone2,					
				email,
				is_active";
				if ($total > 0)
					for ($n = 1; $n <= 15; $n++) 
						$sql.= ", nick" . $n;
			$sql .= ")
			
			VALUES (
				$household[id],
				'$firstname',
				'$lastname',
				'$matchedfirst',
				'$matchedlast',
				'$initial',
				'$dob',
				'$sur[0]',
				'$sur[1]',
				'$sur[2]',
				'$sur[3]',
				'$streetnum',
				'$streetname',
				'$std_streetname',
				'$apartmentnum',
				'$city',
				'$county',
				'$state',
				'$zip_five',
				'$zip_four',
				'$phone1',
				'$phone2',
				'$email',
				$is_active";
				if ($total > 0)
				for ($n = 1; $n <= 15; $n++) {
					$index=	"nick" . $n;
					$sql .= ", '$nicknames[$index]'";
				}	
				$sql.=")";
		
		$stmt = $control['db']->query($sql);
}

function escape($value) {
// make special characters safe for database	
	$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
	$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
	return str_replace($search, $replace, $value);
}	

function updateMatchedDisplay($householdID, $matchedfirst, $matchedlast, $initial, $dob) {
// also called from addhousehold.php	
	global $control;
	
// split multiple/hyphenated surnames
	$sur[0]=""; $sur[1]=""; $sur[2]=""; $sur[3]="";
	if (!empty($matchedlast)) {
		$matches = preg_split("/[^a-z^0-9]/i", trim($matchedlast));	
		if ( count($matches) > 1 ) 
			for ($i = 0; $i <= count($matches)-1; $i++)
				$sur[$i]=$matches[$i];
	}
	
	$sql="UPDATE matched_display 
		  SET matchedfirst = :matchedfirst, 
		  matchedlast = :matchedlast,
		  initial = :initial,
		  dob = :dob,
		  sur1 = :sur1,
		  sur2 = :sur2,
		  sur3 = :sur3,
		  sur4 = :sur4		  
		  WHERE householdID = :householdID";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':matchedfirst', $matchedfirst, PDO::PARAM_STR);	
	$stmt->bindParam(':matchedlast', $matchedlast, PDO::PARAM_STR);	
	$stmt->bindParam(':initial', $initial, PDO::PARAM_STR);		
	$stmt->bindParam(':dob', $dob, PDO::PARAM_STR);		
	$stmt->bindParam(':sur1', $sur[0], PDO::PARAM_STR);	
	$stmt->bindParam(':sur2', $sur[1], PDO::PARAM_STR);	
	$stmt->bindParam(':sur3', $sur[2], PDO::PARAM_STR);	
	$stmt->bindParam(':sur4', $sur[3], PDO::PARAM_STR);		
	$stmt->bindParam(':householdID', $householdID, PDO::PARAM_INT);	
	$stmt->execute();	  
}	

function updateMatchedDisplayxxxx( $members, $matched_display ) {

	global $control;

	$data =array();
	$dobQ="";
	if (isNameOrDOBEntered()) {
		$matchedfirst = $members['firstname'];
		$matchedlast = $members['lastname'];
		$initial=$members['initial'];
		$dobQ=",dob=:dob";
		$data['dob']= $members['dob'];		
	} else {
		$matchedfirst = "";
		$matchedlast = "";
		$initial="";	
	}
	
// split multiple/hyphenated surnames
	$sur[0]=""; $sur[1]=""; $sur[2]=""; $sur[3]="";
	if (!empty($matchedlast)) {
		$matches = preg_split("/[^a-z^0-9]/i", trim($matchedlast));	
		if ( count($matches) > 1 ) 
			for ($i = 0; $i <= count($matches)-1; $i++)
				$sur[$i]=$matches[$i];
	}		
	
	$data['matchedfirst'] = $matchedfirst;
	$data['matchedlast'] = $matchedlast;
	$data['sur1'] = $sur[0];
	$data['sur2'] = $sur[1];
	$data['sur3'] = $sur[2];
	$data['sur4'] = $sur[3];
	$data['initial'] = $initial;	
	$data['householdID'] = $members['householdID'];
		
	$sql = "UPDATE matched_display
			SET	matchedfirst=	:matchedfirst,
				matchedlast = 	:matchedlast,
				sur1 = 			:sur1,
				sur2 = 			:sur2,
				sur3 = 			:sur3,
				sur4 = 			:sur4,
				initial = 		:initial
				$dobQ				
			WHERE householdID = :householdID";				
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);					
}

function displayMatchedResults() {
	global $control;

	if (isset($_GET['errCode']) && $_GET['errCode'] == 14)
		displayAlert("<b>Warning:</b> Household may already exist in database.");

	$search=fillMatchedSearchFields();
	
	echo "<div class='container'>";
	
	matchedResultsHeadings();
	$matchedStyleBegin = "<font style='font-weight:bold;'>";	
	$matchedStyleEnd = "</font>";	
	$sql = "SELECT * FROM matched_display ORDER BY lastname, firstname"; 
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll();		
	foreach($result as $matched_display) {	
		$nameStyleBegin = "";
		$nameStyleEnd = "";		
		$initialStyleBegin = "";
		$initialStyleEnd = "";			
		$dobStyleBegin = "";	
		$dobStyleEnd = "";		
		$addressStyleBegin = "";
		$addressStyleEnd = "";			
		$apartmentnumStyleBegin="";	
		$apartmentnumStyleEnd="";			
		$cityStyleBegin ="";
		$cityStyleEnd = "";			
		$stateStyleBegin ="";
		$stateStyleEnd ="";		
		$zipFiveStyleBegin ="";
		$zipFiveStyleEnd ="";		
		$zipFourStyleBegin ="";
		$zipFourStyleEnd ="";			
		$phone1StyleBegin = "";
		$phone1StyleEnd = "";		
		$phone2StyleBegin = "";
		$phone2StyleEnd = "";		
		$emailStyleBegin = "";
		$emailStyleEnd = "";			

// name 
		if ( $search['firstname'] != "%" || $search['lastname'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND matchedfirst LIKE :firstname ";
						for ($n = 1; $n <= 15; $n++) 
							$sql2 .= "OR nick" . $n . " LIKE :firstname ";						
			$sql2.="AND ( matchedlast LIKE :lastname
						OR sur1 LIKE :lastname
						OR sur2 LIKE :lastname
						OR sur3 LIKE :lastname
						OR sur4 LIKE :lastname )"; 		
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':firstname', $search['firstname'], PDO::PARAM_STR);	
			$stmt->bindParam(':lastname', $search['lastname'], PDO::PARAM_STR);				
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$nameStyleBegin = $matchedStyleBegin;
				$nameStyleEnd = $matchedStyleEnd;	
			}	
		}	
		
// initial 
		if ( $search['initial'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display WHERE id = :id AND initial LIKE :initial"; 						
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);			
			$stmt->bindParam(':initial', $search['initial'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$initialStyleBegin = $matchedStyleBegin;
				$initialStyleEnd = $matchedStyleEnd;	
			}	
		}			
		
// d.o.b.
		if ( $search['dob'] != "%") {	
			$sql2 = "SELECT householdID FROM matched_display WHERE id = :id AND dob = :dob";						
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':dob', $search['dob'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$dobStyleBegin = $matchedStyleBegin;
				$dobStyleEnd = $matchedStyleEnd;
			}	
		}			
		
// street address
		if ( !empty($search['streetnum']) || !empty($search['streetname']) ) {	
			$streetnum=$search['streetnum'] . "%";
			$std_streetname="%" . standardStreetName($search['streetname']) . "%";
			$sql2 = "SELECT householdID FROM matched_display 
					 WHERE id = :id
					 AND streetnum LIKE :streetnum 
					 AND std_streetname LIKE :std_streetname";		
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':streetnum', $streetnum, PDO::PARAM_STR);	
			$stmt->bindParam(':std_streetname', $std_streetname, PDO::PARAM_STR);				
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$addressStyleBegin = $matchedStyleBegin;
				$addressStyleEnd = $matchedStyleEnd;
			}	
		}			

// apartmentnum	
		if ( $search['apartmentnum'] != "%" ) {	
			$sql2 = "SELECT householdID FROM matched_display 
					 WHERE id = :id
					 AND apartmentnum LIKE :apartmentnum";		
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':apartmentnum', $search['apartmentnum'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$apartmentnumStyleBegin = $matchedStyleBegin;
				$apartmentnumStyleEnd = $matchedStyleEnd;
			}	
		}					
		
// city
		if ( $search['city'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND city LIKE :city";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':city', $search['city'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$cityStyleBegin = $matchedStyleBegin;
				$cityStyleEnd = $matchedStyleEnd;
			}	
		}				
		
// state
		if ( $search['state'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND state LIKE :state";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':state', $search['state'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$stateStyleBegin = $matchedStyleBegin;
				$stateStyleEnd = $matchedStyleEnd;
			}	
		}							
		
// zip 5
		if ( $search['zip_five'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND zip_five LIKE :zip_five";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':zip_five', $search['zip_five'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$zipFiveStyleBegin = $matchedStyleBegin;
				$zipFiveStyleEnd = $matchedStyleEnd;
			}	
		}						
		
// zip 4
		if ( $search['zip_four'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND zip_four LIKE :zip_four";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':zip_four', $search['zip_four'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$zipFourStyleBegin = $matchedStyleBegin;
				$zipFourStyleEnd = $matchedStyleEnd;
			}	
		}						
			
// phone1 & phone2	
		if ( $search['phone1'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND phone1 LIKE :phone1";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':phone1', $search['phone1'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$phone1StyleBegin = $matchedStyleBegin;
				$phone1StyleEnd = $matchedStyleEnd;
			}				
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND phone2 LIKE :phone1";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':phone1', $search['phone1'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$phone2StyleBegin = $matchedStyleBegin;
				$phone2StyleEnd = $matchedStyleEnd;
			}					

		}
		
		if ( $search['phone2'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND phone1 LIKE :phone2";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':phone2', $search['phone2'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$phone1StyleBegin = $matchedStyleBegin;
				$phone1StyleEnd = $matchedStyleEnd;
			}				
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND phone2 LIKE :phone2";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':phone2', $search['phone2'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$phone2StyleBegin = $matchedStyleBegin;
				$phone2StyleEnd = $matchedStyleEnd;
			}	
		}	
		
// email	
		if ( $search['email'] != "%" ) {
			$sql2 = "SELECT householdID FROM matched_display 
						WHERE id = :id
						AND email LIKE :email";			
			$stmt = $control['db']->prepare($sql2);
			$stmt->bindParam(':id', $matched_display['id'], PDO::PARAM_INT);	
			$stmt->bindParam(':email', $search['email'], PDO::PARAM_STR);	
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0) {
				$emailStyleBegin = $matchedStyleBegin;
				$emailStyleEnd = $matchedStyleEnd;
			}	
		}				
			
// finally, format display line	
		$primaryMember="";
		$streetAddress="";
		$contact="";
		$link = $_SERVER['PHP_SELF'] . "?tab=profile"; 		
			
// primary shopper			
		$householdName = ucname(stripslashes($matched_display['firstname'])) . " " . ucname(stripslashes($matched_display['lastname']));
		$householdName = trim($householdName);	
		$id = $matched_display['householdID'];
		if ( $householdName != "" ) {  
			$guestLink = $link . "&hhID=$id";
			$primaryMember = "<a href='" . $guestLink . "' style='color:#841E14;text-decoration:underline;'>" . $householdName ."</a>";
		} else 
			$primaryMember  = "<span class='alert-warning p-2'><i class='fa fa-exclamation-circle p-2'></i>ERROR - MEMBERS TABLE</span>";

// matching member
		$matchingMember = $nameStyleBegin . ucname(stripslashes($matched_display['matchedfirst'])) . $nameStyleEnd . " ";
		$matchingMember .= $initialStyleBegin . ucname(stripslashes($matched_display['initial']));
		if (!empty($matched_display['initial']))
			$matchingMember .= ".";
		$matchingMember .= $initialStyleEnd . " ";	
		$matchingMember .= $nameStyleBegin . ucname(stripslashes($matched_display['matchedlast'])) . $nameStyleEnd;
		if (!$matched_display['is_active'])
			$matchingMember .= "<span class='alert-warning ml-2 p-2'><i class='fa fa-exclamation-circle pr-2'></i>INACTIVE</span>";		
		if ( isValidDate($matched_display['dob'], 'Y-m-d') )
			$matchingMember .= "<br>" . $dobStyleBegin . "DOB: " . date('m-d-Y', strtotime($matched_display['dob']) ) . $dobStyleEnd;		

// address
		$streetAddress = $addressStyleBegin . $matched_display['streetnum'] . " ";
		$streetAddress .= ucname(stripslashes($matched_display['streetname']));	
		$streetAddress .= $addressStyleEnd;	
		if ( $matched_display['apartmentnum'] )
			$streetAddress .= $apartmentnumStyleBegin . " Apt " . strtoupper($matched_display['apartmentnum']) . $apartmentnumStyleEnd;
		$streetAddress .= "<br>" . $cityStyleBegin . ucname(stripslashes($matched_display['city'])) . $cityStyleEnd . ", ";
		$streetAddress .= $stateStyleBegin . strtoupper($matched_display['state']) . $stateStyleEnd . " ";
		$streetAddress .= $zipFiveStyleBegin . $matched_display['zip_five'] . $zipFiveStyleEnd;
		if ( $matched_display['zip_four'] )
			$streetAddress .= "-" . $zipFourStyleBegin . $matched_display['zip_four'] . $zipFourStyleEnd;
		$linebreak = "";	
		
// contact		
		if ( is_numeric($matched_display['phone1']) ) {
			$contact .= $phone1StyleBegin . ExpandPhone( $matched_display['phone1'] ) . $phone1StyleEnd;	
			$linebreak = "<br>";
		}	
		if ( is_numeric($matched_display['phone2']) ) {
			$contact .= $linebreak . $phone2StyleBegin . ExpandPhone( $matched_display['phone2'] ) . $phone2StyleEnd;	
			$linebreak = "<br>";
		}		
		if ( $matched_display['email'] )
			$contact .= $linebreak . $emailStyleBegin. $matched_display['email'] .$emailStyleEnd;

		matchedResultsLine($primaryMember, $matchingMember, $streetAddress, $contact);
	}
	
	echo "</div>";
	
}

function fillMatchedSearchFields() {
global $control;

	$arr=[
		'firstname'		=> "%",	
		'lastname'		=> "%",
		'initial'		=> "%",		
		'dob'			=> "%",	
		'streetnum'		=> "",	
		'streetname'	=> "",	
		'apartmentnum'	=> "%",
		'city'			=> "%",
		'county'		=> "%",
		'state'			=> "%",
		'zip_five'		=> "%",
		'zip_four'		=> "%",		
		'email'			=> "%",
		'phone1'		=> "%",
		'phone2'		=> "%",	
	];	
	
// case #1: called from name search or advanced search	
	if (isset($_POST['search'])) {
		
// name	search	
		$arr['firstname'] = trim($_POST['firstname']) . "%";	
		$arr['lastname'] = trim($_POST['lastname']) . "%";

// advanced search
		if (isset($_POST['dob'])) {
			$arr['initial'] = trim($_POST['initial']) . "%";			
			$arr['dob'] = trim($_POST['dob']) . "%";	
			$street	= splitAddress( $_POST['address'] );	
			$arr['streetnum'] = trim($street['num']);	
			$arr['streetname'] = trim($street['name']);	
			$arr['apartmentnum'] = trim($_POST['apartmentnum']) . "%";
			$arr['city'] = trim($_POST['city']) . "%";
			$arr['county']= trim($_POST['county']) . "%";
			$arr['state'] = trim( $_POST['state'] ) . "%";
			$zip=editZipcode($control['db'], strtolower($_POST['city']), strtolower($_POST['county']), strtolower($_POST['state']), $_POST['zip']);
			$arr['zip_five'] = trim($zip['zip_five']) . "%";
			$arr['zip_four'] = trim($zip['zip_four']) . "%";		
			$arr['email'] = trim($_POST['email']) . "%";
			$arr['phone1'] = trim(CrunchPhone( $_POST['phone1'] )) . "%";
			$arr['phone2'] = trim(CrunchPhone( $_POST['phone2'] )) . "%";	
		}	
		
// case #2: called from addhousehold.php		
	} else {
			$arr['firstname'] = trim($_GET['firstname']) . "%";	
			$arr['lastname'] = trim($_GET['lastname']) . "%";
			$arr['initial'] = trim($_GET['initial']) . "%";			
			$arr['dob'] = trim($_GET['dob']) . "%";	
			$street	= splitAddress( $_GET['address'] );	
			$arr['streetnum'] = trim($street['num']);	
			$arr['streetname'] = trim($street['name']);	
			$arr['apartmentnum'] = trim($_GET['apartmentnum']) . "%";
			$arr['city'] = trim($_GET['city']) . "%";
			$arr['county']= trim($_GET['county']) . "%";
			$arr['state'] = trim( $_GET['state'] ) . "%";
			$zip=editZipcode($control['db'], strtolower($_GET['city']), strtolower($_GET['county']), strtolower($_GET['state']), $_GET['zip']);
			$arr['zip_five'] = trim($zip['zip_five']) . "%";
			$arr['zip_four'] = trim($zip['zip_four']) . "%";		
			$arr['email'] = trim($_GET['email']) . "%";
			$arr['phone1'] = trim(CrunchPhone( $_GET['phone1'] )) . "%";
			$arr['phone2'] = trim(CrunchPhone( $_GET['phone2'] )) . "%";	
	}		

	return $arr;
}

function matchedResultsHeadings() {
global $control;
 
	searchResultsMsg();	
	
	if ( isset($_GET['ShelterID']) ) {
		$shelterLink = $_SERVER['PHP_SELF'] . "?hhID=$hhID&themeId=$themeId&pantryID=$pantryID&outerTab=E&innerTab=T&innerSubTab=E&ShelterID=$_GET[ShelterID]";	
		$sql = "SELECT * FROM shelters WHERE id = $_GET[ShelterID]";
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		if ($row = mysqli_fetch_assoc($result)) 	
			echo "<a class='anyLink' href='$shelterLink'>$row[name]</a></b>";	
	} 
?>
	<div class="container-fluid bg-primary">
		<div class='row'>
			<div class='col-sm border border-dark border-right-0 p-2'>Primary Shopper</div>	
			<div class='col-sm border border-dark border-right-0 p-2'>Matching Member</div>				
			<div class='col-sm border border-dark border-right-0 p-2'>Address</div>	
			<div class='col-sm border border-dark p-2'>Contact</div>
		</div>
	</div>	

<?php
}

function searchResultsMsg() {
	global $control;

	$msg=""; 
	
	$sql = "SELECT * FROM matched_display ORDER BY lastname, firstname"; 
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$total = $stmt->rowCount();		
	
	if (isset($_GET['errCode'])) {
		$link = "households/addhousehold.php?continue=1"; 
		$link .= "&firstname=" . urlencode($_GET['firstname']);
		$link .= "&lastname=" .	urlencode($_GET['lastname']);
		$link .= "&initial=" . urlencode($_GET['initial']);
		$link .= "&dob=" . urlencode($_GET['dob']);
		$link .= "&gender=" . urlencode($_GET['gender']);
		$link .= "&address=" . urlencode($_GET['address']);
		$link .= "&apartmentnum=" . urlencode($_GET['apartmentnum']);
		$link .= "&city=" . urlencode($_GET['city']);
		$link .= "&county=" . urlencode($_GET['county']);
		$link .= "&state=" . urlencode($_GET['state']);
		$link .= "&zip=" . urlencode($_GET['zip']);
		$link .= "&phone1=" . urlencode($_GET['phone1']);
		$link .= "&phone2=" . urlencode($_GET['phone2']);
		$link .= "&email=" . urlencode($_GET['email']);
		$msg= "Select from the following <b>" . number_format($total) . "</b> matches, or <a href='" . $link . "' style='color:#841E14;text-decoration:underline;'>
		continue with new registration</a>";		
	} else
		$msg="Search found <b>$total</b> matches."; 	

	echo "
	<div class='mt-3 mb-2'>$msg</div>\n";
}

function matchedResultsLine($primaryMember, $matchingMember, $streetAddress, $contact) {
	echo "
	<div class='container-fluid bg-gray-2'>
		<div class='row'>
			<div class='col-sm border border-dark border-right-0 border-top-0 p-2'>$primaryMember</div>	
			<div class='col-sm border border-dark border-right-0 border-top-0  p-2'>$matchingMember</div>				
			<div class='col-sm border border-dark border-right-0 border-top-0  p-2'>$streetAddress</div>	
			<div class='col-sm border border-dark border-top-0  p-2'>$contact</div>
		</div>
	</div>";
}
?>