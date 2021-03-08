<?php 
/**
 * PEP023.php - Household Consumption by Time Period
 * 		written: 7-21-13 for v 3.4.4 update		-mlr
 *
 * 4-30-2020: version 3.9.5 update - Remove 'products requested' field.		-mlr
 *
 * 4-30-14: version 3.5.1 update - remove TIME_ZONE_OFFSET constant.		-mlr
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 * 
 */
 
$isPublic = 0; // Reports folder (for signed-in Pepbase users)
// $isPublic = 1; // Public folder (for "Demographics and Statistics" public web page; no password security)	

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

    doHeader("PEP023 - Household Consumption by Time Period", "isBoardReport");	

/* MAINLINE */

	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Household Consumption by Time Period - PEP023</h3>";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on data from PEPbase 'consumption' table.</i></p>\n";
	
	calc18MonthOffset();
	reportSearch();
	
    if ( isset($_POST['search']) || isset($_GET['search']) ) {
		getReportDates();
		printReport();
		printSummary();		
    }	
	mysqli_close( $conn );
	echo "</center></body></html>"; 
}	
	
/* FUNCTIONS */

/** reportSearch()
  * written: 2-26-13 -mlr
  */
function reportSearch()
{
	global $conn, $themeId, $pantryID;
	
	$pantry_id = "All";
	if ( isset( $_POST['search']) ) $pantry_id = $_POST['pantry_id'];
	elseif ( isset( $_GET['search']) ) $pantry_id = $_GET['pantry_id'];
	
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

// written 07-21-13 for Pepartnership, Inc.		-mlr
function printReport()
{
global $conn, $sql, $themeId, $grandCount;

	$totOked=0;
	$totInstock=0;
	$totReqd=0;
	$totUsed=0;
	$totVisits=0;	
	
	reportHeadings();

	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	while ($row = mysqli_fetch_assoc($result)) {
	
// 4-30-14: version 3.5.1 update - remove TIME_ZONE_OFFSET constant.		-mlr	
//		$visitTime = date( 'g:i a', strtotime("$row[date] $row[time]") + TIME_ZONE_OFFSET );	
		$visitTime = date( 'g:i a', strtotime("$row[date] $row[time]") );
		$selPantryID = $row['pantry_id'];			
		$sql2 = "SELECT * FROM pantries WHERE pantryID = $selPantryID";
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		if ($row2 = mysqli_fetch_assoc($result2)) {
			$title = $row2['name'];
			$abbrev = $row2['abbrev'];	
		} else {
			$title = "{ not found }";		
			$abbrev = "{ not found }"; 	
		}		
	
		echo "
		<tr>
		<td style='text-align:left;'>" . date('D m-d-Y', strtotime($row['date']) ) . " $visitTime</td>
		<td style='text-align:left;'>$abbrev</td>	
		<td>$row[household_id]</td>	
		<td>" . $row['sum( quantity_oked )'] . "</td>		
		<td>" . $row['sum( instock )'] . "</td>";
// 4-15-2020: v 3.9.5 update - remove products requested field		
//		<td style='text-align:right;'>" . $row['sum( quantity_reqd )'] . "</td>
		echo "
		<td style='text-align:right;'>" . $row['sum( quantity_used )'] . "</td>		
		</tr>";
		
		$totOked+=$row['sum( quantity_oked )'];
		$totInstock+=$row['sum( instock )'];
		$totReqd+=$row['sum( quantity_reqd )'];
		$totUsed+=$row['sum( quantity_used )'];			

		$totVisits++;		
	}	
	if ( !$totVisits )
		echo "<tr><td colspan='7' style='text-align:center;'>{ no visitors for selected dates }</td></tr>";
	echo "
	<tr><td colspan='3' style='text-align:right;font-weight:bold;'>totals:</td>
	<td style='text-align:right;font-weight:bold;'>" . number_format($totOked) . "</td>	
	<td style='text-align:right;font-weight:bold;'>" . number_format($totInstock) . "</td>";
	
// 4-15-2020: v 3.9.5 update - remove products requested field	
//	<td style='text-align:right;font-weight:bold;'>" . number_format($totReqd) . "</td>	
	echo "
	<td style='text-align:right;font-weight:bold;'>" . number_format($totUsed) . "</td></tr>
	</table>";
	$grandCount=$totVisits;
}

/** reportHeadings()
  * written: 7-21-13 -mlr
  */
function reportHeadings()
{
global $conn, $sql, $themeId, $field, $order, $newDateOrder, $newIdOrder, $reportBeginDate, $reportEndDate;

	$sortArrow = fetchSortFieldOrder();
	newOrder( );
	$link = formatSortHeadLink();	

// 4-15-2020: v 3.9.5 update - remove requested field from headings
	echo"
	<table border='0' cellspacing='0' class='reportMainTbl34'>
	<tr><th><a title='sort by date/time' href='" . $link . "&amp;field=date&amp;order=$newDateOrder'>date/time</a>&nbsp;&nbsp;$sortArrow[date]</th>
	<th>pantry</th>
	<th><a title='sort by household id' href='" . $link . "&amp;field=id&amp;order=$newIdOrder'>hh id</a>&nbsp;&nbsp;$sortArrow[id]</th>
	<th>eligible for</th>
	<th>in stock</th>";
//	<th>requested</th>
	echo "
	<th>received</th></tr>";
}

// written 05-20-13 for Pepartnership, Inc.		-mlr
// gets new sort order, and returns array containing the selected column for sort direction arrow
function fetchSortFieldOrder()
{
global $field, $order, $newDateOrder, $newIdOrder;

	$isNewSignIn=0;
	$order="desc";	
	if ( isset($_GET['field']) ) $field=$_GET['field'];
	else $field="date";	
	if ( isset($_GET['order']) ) $order=$_GET['order'];
	
	$retArr = array('date' => "", 'id' => "");

// IE8 doesn't support the small arrow unicode character (U+25B4 and U+25BE), so we paint an image instead.	
	if ( $order == "asc" )	
//		$retArr[$field] = "&#x25B4";
		$retArr[$field] = "<img style='vertical-align:0px;width:12px;height:6px;' src='../images/up_arrow.png' />";
	else	
//		$retArr[$field] = "&#x25BE";
		$retArr[$field] = "<img style='vertical-align:0px;width:12px;height:6px;' src='../images/down_arrow.png' />";
	return $retArr;	
}



/** formatSortHeadLink()
  * written: 7-22-13 for Pepartnership, Inc.		-mlr
  */
function formatSortHeadLink()
{
global $themeId, $reportBeginDate, $reportEndDate;

	$link = $_SERVER['PHP_SELF'] . "?";
	$link .= "themeId=$themeId&amp;search=1&amp;begin=$reportBeginDate&amp;end=$reportEndDate";
	
	if ( isset( $_POST['dateRad'] ) ) {
		$link .= "&amp;dateRad=$_POST[dateRad]";
		if ( isset( $_POST['rangeBeginDateJACS']) )	
			$link .= "&amp;rangeBeginDateJACS=$_POST[rangeBeginDateJACS]";	
		if ( isset( $_POST['rangeEndDateJACS']) )	
			$link .= "&amp;rangeEndDateJACS=$_POST[rangeEndDateJACS]";			
		if ( isset( $_POST['customDateJACS']) )	
			$link .= "&amp;customDateJACS=$_POST[customDateJACS]";		
		if ( isset($_POST['customDateType']) )
			$link .= "&amp;customDateType=$_POST[customDateType]";
		if ( isset($_POST['pantry_id']) )
			$link .= "&amp;pantry_id=$_POST[pantry_id]";		
	} elseif ( isset( $_GET['dateRad']) ) {
		$link .= "&amp;dateRad=$_GET[dateRad]";
		if ( isset( $_GET['rangeBeginDateJACS']) )	
			$link .= "&amp;rangeBeginDateJACS=$_GET[rangeBeginDateJACS]";	
		if ( isset( $_GET['rangeEndDateJACS']) )	
			$link .= "&amp;rangeEndDateJACS=$_GET[rangeEndDateJACS]";			
		if ( isset( $_GET['customDateJACS']) )	
			$link .= "&amp;customDateJACS=$_GET[customDateJACS]";		
		if ( isset($_GET['customDateType']) )
			$link .= "&amp;customDateType=$_GET[customDateType]";
		if ( isset($_GET['pantry_id']) )
			$link .= "&amp;pantry_id=$_GET[pantry_id]";			
	}
	return $link;	
}

// written 07-21-13 for Pepartnership, Inc.		-mlr
function newOrder()
{
global $conn, $sql, $field, $order, $newDateOrder, $newIdOrder, $reportBeginDate, $reportEndDate; 

	$pantry_q = 1;	
    if ( isset($_POST['pantry_id']) ) {
		if ( $_POST['pantry_id'] != "All" )
			$pantry_q = " pantry_id = $_POST[pantry_id] ";	
	} elseif ( isset($_GET['pantry_id']) ) {
		if ( $_GET['pantry_id'] != "All" )
			$pantry_q = " pantry_id = $_GET[pantry_id] ";
	}		

	$date_q ="date >= '$reportBeginDate' AND date <= '$reportEndDate'";

	$newDateOrder = "desc";
	$newIdOrder = "desc";	
    switch ( $field ) {
        case "date": 
			$sql = "SELECT household_id, pantry_id, date, time, sum( quantity_oked ), sum( instock ), sum( quantity_reqd ), sum( quantity_used )
					FROM consumption
					WHERE $date_q AND $pantry_q
					GROUP BY household_id, date, time
					ORDER BY date $order, time $order";			
            if ( $order == "asc" )		
                $newDateOrder = "desc";
			else
				$newDateOrder = "asc";
			break;  
			
        case "id": 
			$sql = "SELECT household_id, pantry_id, date, time, sum( quantity_oked ), sum( instock ), sum( quantity_reqd ), sum( quantity_used )
					FROM consumption
					WHERE $date_q AND $pantry_q
					GROUP BY household_id, date, time
					ORDER BY household_id $order, date $order, time $order";			
            if ( $order == "asc" )
                $newIdOrder = "desc";
			else
                $newIdOrder = "asc";
            break;
    } // switch 
}

/** printSummary()
  * written: 7-22-13 -mlr
  */
function printSummary( )
{
	global $conn, $foundInactive, $reportBeginDate, $reportEndDate, $grandCount;
	
// get values for Summay

	if ( $reportBeginDate == $reportEndDate )
		$reportPeriod = date('M j, Y', strtotime($reportBeginDate));
	else
		$reportPeriod = date('M j, Y', strtotime($reportBeginDate)) . " thru " . date('M j, Y', strtotime($reportEndDate));
	$title="";	
    if ( isset($_POST['pantry_id']) ) 
		if ( $_POST['pantry_id'] == "All" )
			$title="All";
		else {	
			$sql2 = "SELECT * FROM pantries WHERE pantryID = $_POST[pantry_id]";
			$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
			if ($row2 = mysqli_fetch_assoc($result2)) 
				$title = $row2['name'];	
		} elseif ( isset($_GET['pantry_id']) ) 
			if ( $_GET['pantry_id'] == "All" )
				$title="All";
			else {	
				$sql2 = "SELECT * FROM pantries WHERE pantryID = $_GET[pantry_id]";
				$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
				if ($row2 = mysqli_fetch_assoc($result2)) 
					$title = $row2['name'];	
			}	
			
// print Summary 			

    echo "<table border='0' cellspacing='0' class='reportSummary34'>\n";	
    echo "<tr><td id='rSumTitle' colspan='2'><i>Summary</i></td></tr>\n";
    echo "<tr><td>Report Period</td>\n"; 
    echo "<td>$reportPeriod</td></tr>\n";
    echo "<tr><td>Pantry</td>\n"; 
    echo "<td>$title</td></tr>\n";	
    echo "<tr><td>Total Visits</td>\n";
    echo "<td>" . number_format( $grandCount ) . "</td></tr>\n";	
    echo "</td></tr></table>";	
}

?>