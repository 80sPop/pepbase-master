<?php
/**
 * AdvancedSearch.php
 * Written: 6-30-12
 *
 * 7-19-2019: version 3.9.1 update - replace JACS datepicker with HTML5 date type, combine 
 * 		zip 5 and zip 4 into one field, combine street num and name into one address field.		-mlr
 *
 * 3-18-14: version 3.5.0 update 
 *		- in function advancedSearch(), add wildcard ("%") to begining of 
 * 			streetname search parameter.
 *		- mask input for phone1 and phone2 input fields	-mlr
 *
 * 10-25-12: version 3.3 updates - set $pantryID as global. 	 -mlr
 *
 * 9-27-12: version 3.1 updates 
 *		- add global variable '$themeId'    -mlr
 * 
 * The following is a set of functions used by the Advanced Search option.
 */
 
/** advancedSearchForm()
  * written: 6-30-12 -mlr
  */
function advancedSearchForm() {
	global $control;

	$firstname='';
	$lastname='';
	$address="";
	$streetnum='';
	$streetname='';
	$apartmentnum="";	
	$city='';
	$county='';
	$state='';
	$zip="";
	$zip_five='';
	$zip_four='';
	$email='';
	$phone1='';
	$phone2='';
	$errCode=0;
	
    if ( isset($_GET['errCode']) ) {
	    $errCode=$_GET['errCode'];
	    displayErr($errCode);
	}

?>
<div class="container p-3">
	<div class="card">
	  <h5 class="card-header bg-gray-4 text-center">Advanced Search</h5>
	  <div class="card-body bg-gray-2">
	  
	<form method='post' action='households.php'>  	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="firstname" id="firstname" ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>LAST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="lastname" id="lastname" ></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>MIDDLE INITIAL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="initial" id="initial" ></div>
			</div>	
		</div>				
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>DATE OF BIRTH:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="dob" id="dob" ></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ADDRESS:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="address" id="address" ></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>APT NUMBER:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="apartmentnum" id="apartmentnum" ></div>
			</div>	
		</div>		

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>CITY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="city" id="city" ></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>COUNTY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="county" id="county" ></div>
			</div>	
		</div>			

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>STATE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php SelectState('state',''); ?></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ZIP CODE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="zip" id="zip" ></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>EMAIL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="email" id="email" ></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PHONE 1:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="phone1" id="phone1" ></div> 
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right"><label class='pt-2'>PHONE 2:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group"><input type="text" class="form-control" name="phone2" id="phone2" ></div>
			</div>	
		</div>					

		<input type= 'hidden' name= 'tab' value= 'profile'>
		<div class='text-center'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='search'>Search</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
		</div>	
		</form>		  

	  </div>
	</div>
</div>

<?php		
}

/** advancedSearch( $isCheckingSingleMatch )
  * written: 7-8-12 -mlr
  */
function advancedSearch( $isCheckingSingleMatch )
{
global $conn, $hhID, $themeId, $pantryID, $row, $sql, $count, $outerTab, $innerTab, $searchFirstName, 
	$searchLastName, $searchDob, $searchStreetNum, $searchStreetName, $searchApartmentNum, $searchCity,
	$searchCounty, $searchState, $searchZipFive, $searchZipFour, $searchEmail, $searchPhone1, 
	$searchPhone2, $isSingleMatch, $searchCase;
	
global $inZipFive, $inZipFour;	

	set_time_limit(900);
	$temphhID = 0;
	$isSingleMatch = 0;	
	
	$sql = "DELETE FROM matched_display";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));	
	
	$searchFirstName	= "%";
	$searchLastName		= "%";
	$searchDob			= "%";		// 10-20-2015: version 3.6.0 update - add d.o.b.		-mlr
	$searchStreetNum	= "%";
	$searchStreetName	= "%";
	$searchApartmentNum	= "%";
	$searchCity			= "%";
	$searchCounty		= "%";
	$searchState		= "%";
	$searchZipFive		= "%";
	$searchZipFour		= "%";
	$searchEmail		= "%";
	$searchPhone1		= "%";
	$searchPhone2		= "%";
	
	if ( isset($_POST['firstname']) ) 	$searchFirstName = trim($_POST['firstname']) . "%";	
	if ( isset($_POST['lastname']) ) 		$searchLastName = trim($_POST['lastname']) . "%";
	
// 10-14-2015: version 3.6.0 update - add d.o.b. to advanced search.	-mlr
	$isDobEntered = 0;
// 7-20-2019: version 3.9.1 update - replace JACS datepicker with HTML5 date type	
	if ( validateDate($_POST['dob'], 'Y-m-d') ) {	
//		$searchDob  = MMDDYYYToMySQL( $_POST['inDob'] );	// JACS dates are in "mm/dd/yyyy" format
		$searchDob  = $_POST['dob'];
//		$searchQueryDob  = "dob = '$searchDob'";		
//		$searchQueryDob  = "dob = '" . MMDDYYYToMySQL( $_POST['inDob'] ) . "'";
		$searchQueryDob  = "dob = '" . $_POST['dob'] . "'";
		$isDobEntered = 1;
	} else 		
		$searchQueryDob  = "1";
// end version 3.6.0 update		

// 7-20-2019: v 3.9.1 update - combine street num and name into one address field.
	if (isset($_POST['address'])) {
		$street = splitAddress( $_POST['address'] );	
		$searchStreetNum = trim($street['num']) . "%";			
		$searchStreetName = standardStreetName(trim($street['name'])) . "%";			
	}	
	
//	if ( isset($_POST['inStreetNum']) ) 	$searchStreetNum = trim($_POST['inStreetNum']) . "%";	
// 3-22-14: version 3.5.0 upgrade - standardize street name before searching	
//	if ( isset($_POST['inStreetName']) )	$searchStreetName = standardStreetName(trim($_POST['inStreetName'])) . "%";	
	if ( isset($_POST['apartmentnum']) )	$searchApartmentNum = trim($_POST['apartmentnum']) . "%";
	if ( isset($_POST['city']) ) 			$searchCity = trim($_POST['city']) . "%";
	if ( isset($_POST['county']) ) 			$searchCounty = trim($_POST['county']) . "%";
	if ( isset($_POST['state']) ) 			$searchState = trim( $_POST['state'] ) . "%";
	
// 7-20-2019: v 3.9.1 update - combine zip 5 and zip 4 into one zip code field, keep separate to search MariaDB tables.		-mlr	
	$err=editZipcode($conn, strtolower($_POST['city']), strtolower($_POST['county']), strtolower($_POST['state']), $_POST['zip']);
	$searchZipFive = trim($inZipFive) . "%";
	$searchZipFour = trim($inZipFour) . "%";	
		
//	if ( isset($_POST['inZipFive']) ) 		$searchZipFive = trim($_POST['inZipFive']) . "%";
//	if ( isset($_POST['inZipFour']) ) 		$searchZipFour = trim($_POST['inZipFour']) . "%";
	if ( isset($_POST['email']) )			$searchEmail = trim($_POST['email']) . "%";
	if ( isset($_POST['phone1']) ) 			$searchPhone1 = trim(CrunchPhone( $_POST['phone1'] )) . "%";
	if ( isset($_POST['phone2']) ) 			$searchPhone2 = trim(CrunchPhone( $_POST['phone2'] )) . "%";

	$isAddressEntered = 	( 
							$searchStreetNum	!= "%"	||
							$searchStreetName	!= "%"	||
							$searchApartmentNum	!= "%"	||
							$searchCity			!= "%"	||
							$searchCounty		!= "%"	||
							$searchState		!= "%"	||
							$searchZipFive		!= "%"	||
							$searchZipFour		!= "%"	||
							$searchEmail		!= "%"	||
							$searchPhone1		!= "%"	||
							$searchPhone2		!= "%"	
							);
							
	$isNameEntered = 	( $searchFirstName != "%"	|| $searchLastName != "%" );
	
// 10-14-2015: version 3.6.0 update - add d.o.b. to advanced search.	-mlr
	$isNameEntered = ( $isNameEntered || $isDobEntered );	

// 3-18-14: version 3.5.0 update 
//	- add wildcard ("%") to begining of streetname search parameter.
//	- search against new standarized street name field.
//  - escape special characters in search fields.		-mlr	

	$escFirstName = mysqli_real_escape_string( $conn, $searchFirstName );
	$escLastName = mysqli_real_escape_string( $conn, $searchLastName );
	$escStreetName = mysqli_real_escape_string( $conn, $searchStreetName );
	$escCity = mysqli_real_escape_string( $conn, $searchCity );
	$escCounty = mysqli_real_escape_string( $conn, $searchCounty );
	
// 4-23-2020: v 3.9.5 update - add special character escaping to the following fields.		-mlr 	
	$escApartmentNum= mysqli_real_escape_string( $conn, $searchApartmentNum);
	$escZipFive = mysqli_real_escape_string( $conn,$searchZipFive);
	$escZipFour = mysqli_real_escape_string( $conn,$searchZipFour);
	$escEmail	= mysqli_real_escape_string( $conn,$searchEmail);	
	

	if ( $isAddressEntered && $isNameEntered ) {
		$searchCase ="nameAndAddress";
		$count = 0;
		$sql = "SELECT * FROM household
					WHERE streetnum LIKE '$searchStreetNum'
					AND std_streetname 	LIKE '%" . $escStreetName . "'
					AND city 		LIKE '$escCity'
					AND county 		LIKE '$escCounty'
					AND state 		LIKE '$searchState'						
					AND zip_five 	LIKE '$searchZipFive'
					AND zip_four 	LIKE '$searchZipFour'
					AND email 		LIKE '$searchEmail'	
 					AND ( phone1 LIKE '$searchPhone1' OR phone2 LIKE '$searchPhone1' )					
					AND ( phone1 LIKE '$searchPhone2' OR phone2 LIKE '$searchPhone2' )";					
		$result = mysqli_query( $conn, $sql ) or die("SQL error #1 - AdvancedSearch.php");				
		while ($row = mysqli_fetch_assoc($result)) {
	
			$householdID = $row['id'];	
// 10-14-2015: version 3.6.0 update - add d.o.b. ($searchQueryDob) to members table search.		-mlr			
			$sql2 = "SELECT * FROM members 
						WHERE householdID = '$householdID'
						AND $searchQueryDob	
						AND firstname LIKE '$escFirstName' 
						AND ( lastname LIKE '$escLastName' 
						OR sur1 LIKE '$escLastName'
						OR sur2 LIKE '$escLastName'
						OR sur3 LIKE '$escLastName'
						OR sur4 LIKE '$escLastName' )"; 
			$result2 = mysqli_query( $conn, $sql2 ) or die("SQL error #2 - AdvancedSearch.php");
			while ($row2 = mysqli_fetch_assoc($result2)) 
				insertMatchedDisplay( "advanced" );			// defined in functions.php	
		}		
		
	} elseif ( $isAddressEntered ) {
		
	
		$searchCase = "addressOnly";
		$sql = "SELECT * FROM household
					WHERE streetnum LIKE '$searchStreetNum'
					AND std_streetname LIKE '%" . $escStreetName ."'
					AND apartmentnum LIKE '$escApartmentNum'
					AND city 		LIKE '$escCity'
					AND county 		LIKE '$escCounty'
					AND state 		LIKE '$searchState'						
					AND zip_five 	LIKE '$escZipFive' 
					AND zip_four 	LIKE '$escZipFour'
					AND email	 	LIKE '$escEmail'	
 					AND ( `phone1` LIKE '$searchPhone1' OR `phone2` LIKE '$searchPhone1' )					
					AND ( `phone1` LIKE '$searchPhone2' OR `phone2` LIKE '$searchPhone2' )";	
		$result = mysqli_query( $conn, $sql ) or die("SQL ERROR #3 in AdvancedSearch.php");
		while ($row = mysqli_fetch_assoc($result)) 
			insertMatchedDisplay( "foo" );				// defined in functions.php	
			
	} elseif ( $isNameEntered ) {
	
// 3-25-14: version 3.5.0 upgrade - sql for first and last name now defined in nameSearch().	-mlr 
// 10-14-2015: version 3.6.0 upgrade - add $searchQueryDob to nameSearch parameters.		-mlr
		$sql = nameSearch( $searchFirstName, $searchLastName, $searchQueryDob );
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		while ($row = mysqli_fetch_assoc($result)) {
			$sql2 = "SELECT * FROM matched_display WHERE householdID = " . $row['householdID'];
			$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));				
			if ($row2 = mysqli_fetch_assoc($result2))
				updateMatchedDisplay( "name" );			// defined in functions.php
			else
				insertMatchedDisplay( "name" );			// defined in functions.php	
		}		

// 10-19-2015: version 3.6.0 update - add special case when only d.o.b. is entered in advanced search.		-mlr	
	} elseif ( $isDobEntered ) {
	
		$sql = nameSearch( $searchFirstName, $searchLastName, $searchQueryDob );
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		while ($row = mysqli_fetch_assoc($result)) {
			$sql2 = "SELECT * FROM matched_display WHERE householdID = " . $row['householdID'];
			$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));				
			if ($row2 = mysqli_fetch_assoc($result2))
				updateMatchedDisplay( "name" );			// defined in functions.php
			else
				insertMatchedDisplay( "name" );			// defined in functions.php	
		}		
	} 	
	
	$sql = "SELECT count(*), householdID FROM matched_display";				
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result)) {				
		$count = $row['count(*)'];
		$temphhID = $row['householdID'];
	}	
	
	if ( $count == 0 && !$isCheckingSingleMatch ) 
		notFoundOrRegisterNew();		// defined in GuestLookUp.php	
	elseif ( $count == 1 ) {
		$isSingleMatch = 1;
		$hhID = $temphhID;
	} elseif ( $count > 1 && !$isCheckingSingleMatch ) 
		displayMatchedResults();		// defined in GuestLookUp.php		

}
?>