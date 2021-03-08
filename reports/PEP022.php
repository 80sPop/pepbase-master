<?php 
/**
 * PEP022.php - Product Consumption by Household Composition
 * 		written: 2-26-13 for Mark Peterson at PEP Immanuel		-mlr
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 * 
 */
 
// $isPublic = 0   	: /Reports folder (for signed-in Pepbase users)
// $isPublic = 1	: /Public folder (for "Demographics and Statistics" public web page; no password security)	
$isPublic = 0;     

 /* VERIFY ACCESS LEVEL */
 
if 	(	!$isPublic &&
		( (!isset($_COOKIE["accessLevel"])) ||  ((isset($_COOKIE["accessLevel"])) && $_COOKIE["accessLevel"] < 1)) 
	)
    echo "<b>! UNAUTHORIZED ACCESS. PLEASE CONTACT SYSTEM ADMINISTRATOR</b>";	
else {	  

/* CONSTANT DECLARATIONS   */

	if 	( !$isPublic ) {
		define('HOST_ACCESS_LEVEL', $_COOKIE["accessLevel"]);
		define('HOST_SIGNIN_ID', $_COOKIE["signinId"]);
	}	
	define ( 'DAGGER_FOOTNOTE', '<sup>&#8224;</sup>' );			

/* INCLUDE FILES */

	require_once('../MySQLConfig.php'); 
	require_once(ROOT . 'common_vars.php');
	require_once(ROOT . 'functions.php');	
	require_once(ROOT . 'Header.php');
	require_once(ROOT . 'Themes.php');	
	require_once(ROOT . 'Reports/ReportFunctions.php');	

/* INITIALIZE VARS */

	if 	( !$isPublic ) {
		$sql = "SELECT * FROM access_levels WHERE al_id = " . HOST_ACCESS_LEVEL;
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		$accessLevelRow = mysqli_fetch_assoc($result);
		
		$sql = "SELECT * FROM signin_accounts WHERE sa_id = " . HOST_SIGNIN_ID;
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		if ($row = mysqli_fetch_assoc($result)) 
			$hostPantryId = $row['sa_pantry_id'];
		else
			die('host signin not found in signin_accounts table');
	} else 
		$hostPantryId = -1;

    set_time_limit(900);
//	error_reporting(E_ALL);	// useful when testing on production servers (by default, their php.ini is set to only display fatal errors)
//	ini_set('error_reporting', E_ALL);	
//	error_reporting(-1);

// set global variable '$themeId'	
	if ( isset($_GET['Login']) )	
		$themeId = hostPantryTheme();		// defined in Themes.php
	elseif ( isset($_GET['themeId']) )
		$themeId = $_GET['themeId'];
	elseif ( isset($_POST['themeId']) )		
		$themeId = $_POST['themeId'];
	else	
		$themeId=0;	
		
// set global var $pantryID	
	if ( isset($_POST['pantryID']) ) 
		$pantryID = $_POST['pantryID'];
	elseif ( isset($_GET['pantryID']) )
		$pantryID = $_GET['pantryID'];			
	elseif ( $hostPantryId == 0 ) {
		$pantryID = firstPantry();
	} else
		$pantryID = $hostPantryId;	

	defineThemeConstants();					// defined in Themes.php		
	
	$totals['num_households']=0;		
	$totals['elderly']=0;
	$totals['adult']=0;		
	$totals['teen']=0;
	$totals['youth']=0;
	$totals['infant']=0;
	$totals['quantity_used']=0;	

/* XHTML HEADER */

    doHeader("PEP022 - Product Consumption by Household Composition", "isBoardReport");	

/* MAINLINE */

	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Product Consumption by Household Composition - PEP022</h3>";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on data from PEPbase 'consumption', 'household', and 'members' tables.</i></p>\n";
	
	calc18MonthOffset();
	reportSearch();
	
    if ( isset($_POST['search']) ) {
	
		getReportDates();
		buildWorkTable();
		reportHeadings();
		
		displayLine("=");
		displayLine(">");
		displayTotals();

		echo "</table>\n";	
    }	
	echo "</center></body></html>"; 
	mysqli_close( $conn );	
}	
	
/* FUNCTIONS */

/** reportSearch()
  * written: 2-26-13 -mlr
  */
function reportSearch()
{
	global $conn, $themeId, $pantryID;
	
	$pantry_id = "All";
	if ( isset( $_POST['search']) ) {
		$pantry_id = $_POST['pantry_id'];
	}		
	
    echo "<form name='reportSearchForm' method='post' action='$_SERVER[PHP_SELF]' />";
    echo "<table border='0' cellspacing='0' class='reportSearchTbl34' >\n";	
    echo "<tr><td id='rSTitle' colspan='5'><i>Search By</i></td></tr>\n"; 	
    echo "<tr><td id='rSCol1'><i>Date:</i></td>\n";                   
	echo "<td id='rSCol2'>";
	selectReportDates();	
    echo "<td id='rSCol3'><i>Pantry:</i>&#160;&#160;"; 
	selectReportPantry( "pantry_id", $pantry_id );	
    echo "</td><td id='rSCol4'><input style='none;' type='submit' name='search' value= 'Search' /></td>\n";
    echo "<input type='hidden' name='themeId' value= '$themeId' /></td>\n";	
	echo "</tr></table></form>\n";
}

/** buildWorkTable()
  * written: 2-27-13 -mlr
  */
function buildWorkTable()
{
global $conn, $themeId, $reportBeginDate, $reportEndDate, $numVisits, $id, $quantity_used;
	
    $sql = "DELETE FROM report_work";
    $deleteOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	
    $firstRow = 1;
	$pantry_q = 1;	

    if ( isset($_POST['pantry_id']) ) 
		if ( $_POST['pantry_id'] != "All" )
			$pantry_q = " pantry_id = $_POST[pantry_id] ";

// first, fill an array with total products used for each household in the selected date range and pantry
	$quantityArr=array();
	$sql = "SELECT household_id, sum( quantity_used ) FROM consumption
			WHERE date >= '$reportBeginDate'
			AND date <= '$reportEndDate'		
			AND household_id > 0
			AND $pantry_q
			GROUP BY household_id";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ( $row = mysqli_fetch_assoc($result) ) {
		$id=$row['household_id'];
		$quantityArr[$id]=$row['sum( quantity_used )'];
	}
	
// next, tally the visits for each household and build work table
	foreach ( $quantityArr as $id => $quantity_used ) {	

		$firstRow = 1;
		$sql = "SELECT * FROM consumption
				WHERE household_id = $id
				ORDER BY date, time";
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		while ( $row = mysqli_fetch_assoc($result) ) {

			if ( $firstRow ) {
				$currDate = $row['date'];
				$currTime = $row['time'];
				$firstRow = 0;
				$numVisits=1;
			} elseif ( $row['date'] > $currDate || $row['time'] > $currTime ) {
				$currDate = $row['date'];
				$currTime = $row['time'];
				$numVisits++;					
			} 
		}

		if (! $firstRow )                     // ignore empty query results
			insertWorkRow();
	}		
}

/** reportHeadings()
  * written: 2-26-13 -mlr
  */
function reportHeadings()
{
	echo "<table border='0' cellspacing='0' class='reportMainTbl34'>\n";
	echo "<tr><th rowspan='2'>frequency of visit</th><th rowspan='2'>number of<br>households</th><th colspan='5'>household members by age</th><th rowspan='2'>products distributed</th></tr>\n";
	echo "<tr><th>65+</th><th>18-64</th><th>12-17</th><th>4-11</th><th>0-3</th></tr>\n";
}

function insertWorkRow()
{
global $conn, $id, $numVisits, $quantity_used;

    $sql = "INSERT INTO report_work ( RW_household_id, RW_num_visits, quantity_used )
            VALUES ( '$id', '$numVisits', '$quantity_used')";     
    $insertOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
}

/** displayLine()
  * written: 3-2-13 -mlr
  */
function displayLine( $comparesTo )
{
global $conn, $totals;

	$quantity_used=0;
	$num_households=0;
	$elderly=0;
	$adult=0; 
	$teen=0; 
	$youth=0; 
	$infant=0; 
	$memCount=array();
	
	if ( $comparesTo == "=" )
		$visits="first time";
	else
		$visits="repeat";	
    $sql = "SELECT * FROM report_work WHERE RW_num_visits $comparesTo 1";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ( $row = mysqli_fetch_assoc($result) ) {
		$num_households++;		
		$elderly += countMembers( $row['RW_household_id'], UPPER_LIMIT_ELDERLY, LOWER_LIMIT_ELDERLY );
		$adult += countMembers( $row['RW_household_id'], UPPER_LIMIT_ADULT, LOWER_LIMIT_ADULT );		
		$teen += countMembers( $row['RW_household_id'], UPPER_LIMIT_TEEN, LOWER_LIMIT_TEEN );
		$youth += countMembers( $row['RW_household_id'], UPPER_LIMIT_YOUTH, LOWER_LIMIT_YOUTH );
		$infant += countMembers( $row['RW_household_id'], UPPER_LIMIT_INFANT, LOWER_LIMIT_INFANT );
		$quantity_used+=$row['quantity_used'];	
	}	
	
	echo "<tr><td style='text-align:center;'>$visits</td>";
	echo "<td>" . number_format( $num_households ) . "</td>";
	echo "<td>" . number_format( $elderly ) . "</td>";
	echo "<td>" . number_format( $adult ) . "</td>";
	echo "<td>" . number_format( $teen ) . "</td>";
	echo "<td>" . number_format( $youth ) . "</td>";
	echo "<td>" . number_format( $infant ) . "</td>";
	echo "<td>" . number_format( $quantity_used ) . "</td></tr>\n";
	
	$totals['num_households']+=$num_households;
	$totals['elderly']+=$elderly;
	$totals['adult']+=$adult;
	$totals['teen']+=$teen;
	$totals['youth']+=$youth;
	$totals['infant']+=$infant;
	$totals['quantity_used']+=$quantity_used;
}

/** displayTotals()
  * written: 3-2-13 -mlr
  */
function displayTotals()
{
global $totals;

	echo "<tr><th style='text-align:center;'>totals</th>";

	echo "<th style='text-align:right;'>" . number_format( $totals['num_households'] ) . "</th>";
	echo "<th style='text-align:right;'>" . number_format( $totals['elderly'] ) . "</th>";
	echo "<th style='text-align:right;'>" . number_format( $totals['adult'] ) . "</th>";
	echo "<th style='text-align:right;'>" . number_format( $totals['teen'] ) . "</th>";
	echo "<th style='text-align:right;'>" . number_format( $totals['youth'] ) . "</th>";
	echo "<th style='text-align:right;'>" . number_format( $totals['infant'] ) . "</th>";
	echo "<th style='text-align:right;'>" . number_format( $totals['quantity_used'] ) . "</th></tr>\n";
}

/** countMembers( $householdID )
  * written: 3-2-13 -mlr
  */
function countMembers( $householdID, $upperLimit, $lowerLimit )
{
global $conn;

    $sql = "SELECT count(*) FROM members 
			WHERE householdID = $householdID
			AND dob > 	'$lowerLimit'
			AND dob <= 	'$upperLimit'";			
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result)) 
		return $row['count(*)'];
	else	
		return 0;	
}
?>