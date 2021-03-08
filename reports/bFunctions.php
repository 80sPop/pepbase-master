<?php
/* Report functions using Bootstrap 4 framework */

function bSelectDateType( $selectName, $selectValue, $is18 ) {

	echo "<select id='$selectName' class='form-control bg-gray-1'  name='$selectName' onchange='onSelectDate()'/>\n";

// 8-5-2018: version 3.9.1 update - add $is18 parameter to omit last 18 months option for certain reports.		-mlr
	if ($is18) {
		echo "<option  "; 
		if ( $selectValue == "last18months" ) echo "selected ";  
		echo "value= 'last18months'>last 18 months &#42;</option>\n";
	}	

	echo "<option  "; 
	if ( $selectValue == "equalto" ) echo "selected ";  
	echo "value= 'equalto'>equal to</option>\n";

	echo "<option  "; 
	if ( $selectValue == "after" ) echo "selected ";  
	echo "value= 'after'>after</option>\n";	

	echo "<option  "; 
	if ( $selectValue == "onorafter" ) echo "selected ";  
	echo "value= 'onorafter'>on or after</option>\n";
	
	echo "<option  "; 
	if ( $selectValue == "before" ) echo "selected ";  
	echo "value= 'before'>before</option>\n";	

	echo "<option  "; 
	if ( $selectValue == "onorbefore" ) echo "selected ";  
	echo "value= 'onorbefore'>on or before</option>\n";	

	echo "<option  "; 
	if ( $selectValue == "range" ) echo "selected ";  
	echo "value= 'range'>range</option>\n";	

	echo "</select>";	
}


function bSelectPantry( $selectName, $selectValue ) {
	global $conn, $control, $hostPantryId, $pantryID; 

	if (isset($control['users_pantry_id']))
		$hostPantryId=$control['users_pantry_id'];

	echo "<select name='$selectName' class='form-control bg-gray-1'>";
	
// 11-03-2015: version 3.6.0 update - add PEP024 to list of reports where coordinators can only see data from their pantry.		-mlr
// 7-22-13: version 3.4.4 upgrade - for the new PEP023 report, coordinators can only see data from their pantry.
// The "All" option is reserved soley for the administrator.	-mlr
	if ( 	( 	(!strpos($_SERVER['PHP_SELF'], "PEP023")) &&
				(!strpos($_SERVER['PHP_SELF'], "PEP024"))
			)	
	|| $hostPantryId == 0 ) {
		echo "<option ";
		if ( $selectValue == "All" ) echo "selected ";  	
		echo "value ='All' All>---- All ----</option>";
	}	
	
// 1-16-13: version 3.4 upgrade - reports in /Public folder can only select "All" pantries	
	if ( $hostPantryId >= 0 ) {
	
		if ( $hostPantryId == 0 ) 				// here, host has been given access to all pantries (probably the Administrator) 
			$sql = "SELECT * FROM pantries ORDER BY name";
		else
			$sql = "SELECT * FROM pantries 
					WHERE pantryID = $pantryID
					ORDER BY name";	
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		while ($row = mysqli_fetch_assoc($result)) {

	// 1-14-13: version 3.4 upgrade - "(inactive)" added to all inactive pantry names 
			$expandedName = $row['name'];	
			if ( !$row['is_active'] )
				$expandedName .= " (inactive)"; 
			echo "<option "; 
			if ( $selectValue == $row['pantryID'] ) { echo "selected "; } 
			echo "value = '" . $row['pantryID'] ."' " . $row['pantryID'] . "> " . $expandedName . "</option>";
		} 
	}	
	echo "</select>";
}

/* cSelectPantry() should be used with all new Pepbase 4 reports */
function cSelectPantry( $selectName, $selectValue ) {
	global $control; 

 	echo "<select name='$selectName' class='form-control bg-gray-1'>";	
	
	echo "<option ";
	if ( $selectValue == "All" ) echo "selected ";  	
	echo "value ='All' All>---- All ----</option>\n";

	if (! $control['isPublic']) {
//		if ( $control['users_pantry_id'] == 0 )  
		if ( $control['access_level'] == 1 )  	
			$sql = "SELECT * FROM pantries ORDER BY name";
		else
			$sql = "SELECT * FROM pantries 
					WHERE id = $control[users_pantry_id]
					ORDER BY name";	

		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$result = $stmt->fetchAll();	
		foreach ($result as $pantries) {
			$expandedName = $pantries['name'];	
			if ( !$pantries['is_active'] )
				$expandedName .= " (inactive)"; 
			echo "<option "; 
			if ( $selectValue == $pantries['id'] ) { echo "selected "; } 
			echo "value =" . $pantries['id'] ." " . $pantries['id'] . "> " . $expandedName . "</option>\n";
		} 
	}	

	echo "</select>\n";				

}

function bSelectInterval( $selectName, $selectValue )
{
	echo "<select id='$selectName' class='form-control bg-gray-1'  name='$selectName' />\n";

	echo "<option  "; 
	if ( $selectValue == "monthly" ) echo "selected ";  
	echo "value= 'monthly'>monthly</option>\n";
	
	echo "<option  "; 
	if ( $selectValue == "quarterly" ) echo "selected ";  
	echo "value= 'quarterly'>quarterly</option>\n";	
	
	echo "<option  "; 
	if ( $selectValue == "annual" ) echo "selected ";  
	echo "value= 'annual'>annual</option>\n";

	echo "</select>";	
}

function consumptionDateLimit( $boundry ) {
	global $control;

	$limit= "0000-00-00";
	
	if ( $boundry == "start" ) $order = "ASC"; else $order = "DESC";

	$sql = "SELECT date FROM consumption WHERE date > '0000-00-00' ORDER BY date $order LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$total = $stmt->rowCount();	
	if ($total > 0) {	
		$row = $stmt->fetch();	
		$limit=$row['date'];
	}

	return $limit;
}

/** countVisits( $begin, $end, $householdId, $includeDuplicates, $pantry_id )
  *
  * written 10-29-12 for version 3.3 upgrade - accounts
  *		for multiple visits in 1 day
  *
  * $begin ( date: 0 - count all dates  )
  * $end ( date: 0 - count only $begin date )
  * $householdId ( int: 0 - count all households )
  * $includeDuplicates ( int: 	1 - include duplicate households;
  * 							0 - ommitt duplicate households )
  * $householdId ( int: 0 - count all households ) 
  *
  * the following were added for the 3.4 Board of Directors reports
  * 	$pantry_id ( int: 0 - count all pantries )  
  *		$reportBegin ( date: 0 - count all report dates  )
  *		$reportEnd ( date: 0 - count all report dates  )
  
  
  - added 2-11-13 for 3.4 upgrade
  * 
 */
function countVisits( $begin, $end, $householdId, $includeDuplicates=1, $pantry_id=0, $reportBegin=0, $reportEnd=0 ) {
	global $control, $cRow, $cNumVisits, $cCurrDate, $cCurrTime, $currPantry, $cCurrHH;

    $cCurrDate='0000-00-00';
    $cCurrTime='00:00:00';	
    $currPantry=0;	
	$cCurrHH=0;	
    $isFirstVisit=1;
	$cNumVisits=0;
	
	if ( $begin == '0' ) $beginQ = "1"; else $beginQ = "date >= '$begin'";
	if ( $end == '0' ) $endQ = "1"; else $endQ = "date <= '$end'";
	if ( $householdId == '0' ) $householdIdQ = "1"; else $householdIdQ = "household_id = '$householdId'";

// 2-11-13: version 3.4 upgrade 	
	if ( $pantry_id == 0 ) $pantry_idQ = "1"; else $pantry_idQ = "pantry_id = '$pantry_id'";
	if ( $reportBegin == '0' ) $reportBeginQ = "1"; else $reportBeginQ = "date >= '$reportBegin'";
	if ( $reportEnd == '0' ) $reportEndQ = "1"; else $reportEndQ = "date <= '$reportEnd'";	

	$sql = "SELECT * FROM consumption
				WHERE $beginQ
				AND $endQ
				AND $householdIdQ
				AND household_id > 0
				AND pantry_id > 0	
				AND $pantry_idQ
				AND $reportBeginQ		
				AND $reportEndQ	
				ORDER BY household_id, date, time, pantry_id";
//echo "sql=$sql";			
//    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
//    $result = mysqli_query( $conn, $sql ) or die("Allowed memory size exhausted, please limit the scope of your search.");
//	$result = mysqli_query($conn, $sql, MYSQLI_USE_RESULT) or die(mysqli_error( $conn ));	
	$stmt = $control['db']->query($sql);
	while ($cRow = $stmt->fetch()) {
//    while ($cRow = mysqli_fetch_assoc($result)) {

		if ( ! $includeDuplicates )	{
			$sql2 = "SELECT * FROM household
					 WHERE id = $cRow[household_id]
					 AND streetname NOT LIKE 'duplicate%'";
			$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
			if ( $row2 = mysqli_fetch_assoc($result2) ) 
			
				breakDateTime();
		} else
			
			breakDateTime();
	}
	
	return $cNumVisits;
}

function breakDateTime() {
	global $cRow, $cNumVisits, $cCurrDate, $cCurrTime, $currPantry, $cCurrHH;
  
	if (	$cRow['date'] != $cCurrDate	|| 
			$cRow['time'] != $cCurrTime	||		
			$cRow['pantry_id'] != $currPantry ||
			$cRow['household_id'] != $cCurrHH		
			
		) {					

		$cNumVisits++;
		$cCurrDate = $cRow['date'];
		$cCurrTime = $cRow['time'];			
		$currPantry = $cRow['pantry_id'];
		$cCurrHH = $cRow['household_id'];	
	}
}
?>