<?php
/**
 * alreadyprinted.php
 * written: 10/31/2020
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
 * called from okToPrintAnother( hhID ) in js/ajax.js
 *
 * Print Shopping List button will display an alert for the following conditions:
 *
 *  	1. Shopping list already printed in same day       
 *		2. Household doesn't have a valid zip code
 *		3. All active members of household must have a valid date of birth
 *		4. A household member is also active in another household
 *		5. No active members in household.
*/

	require_once('config.php'); 
	require_once('common_vars.php');
	require_once('functions.php');
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "households");

	$hhID=$_GET['hhID'];
	$today = date('Y-m-d');
	$use_overrides="No";
	$isValidDob=true;
	$retval=array();
	$retval['alreadyshopped']= false;
	$retval['ziperror']="";
	$retval['doberror']="";
	$retval['activeinanother']="";
	$retval['noactive']="";

	// already printed 
	$sql = "SELECT * FROM consumption WHERE date = '$today' AND household_id = $hhID";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();	
	if ($total > 0)	
		$retval['alreadyshopped']= true;

	// zip code
	$err=0;
	$row=getHouseholdRow( $control['db'], $hhID );
	if ( empty($row['zip_four']) || intval($row['zip_four']) <= 0 )
		$zip=$row['zip_five'];
	else
		$zip=$row['zip_five'] . "-" . $row['zip_four'];	
	$values=editZipcode($control['db'], strtolower($row['city']), strtolower($row['county']), strtolower($row['state']), $zip);
	$err=$values['errCode'];
	if ($err > 0) 
		$retval['ziperror']=$errMsg[$err];

	// check valid d.o.b. and activity in another household 
	$count=0;
	$sql = "SELECT * FROM members WHERE householdID = $hhID AND in_household = 'Yes'";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	foreach($result as $row) {			
		if (!isValidDate($row['dob'], 'Y-m-d')) {
			$retval['doberror']="All active household members must have a valid date of birth.";
			$retval['doberror'].=" Enter D.O.B. in Members tab, or switch to override values.";	
		}	
		$duplicate= inAnotherHousehold($row['firstname'], $row['lastname'], $row['dob']);
		if ( $duplicate['memberID'] > 0) {	
			$retval['activeinanother']="One or more members are active in another household.";
			$retval['activeinanother'].=" Move or de-activate duplicate member(s) before printing.";	
		}	
		$count++;
	}	
	if ( $count == 0 ) {
		$retval['noactive']="No active members in household.";
		$retval['noactive'].=" Re-activate in Members tab.";			
	}	
			
	//echo result as json
	echo json_encode($retval);
?>