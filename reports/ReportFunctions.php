<?php
/**
 * ReportFunctions.php
 * Written: 2-12-13 for version 3.4 update
 *
 * 1-30-2016 version 3.6.2 update - fix MariaDB bug by replacing all max range date and time comparison operands 
 *		with acceptable values.		-mlr
 *
 * 6-20-15: version 3.5.3 update - add "region" parameter to public reports search box.	-mlr
 * 
 * 10-18-14: version 3.5.21 patch - add function tableDateLimit($foo).	-mlr
 *
 * 8-21-14: version 3.5.2 update - in getReportDates(), add date validation.		-mlr
 *
 * 9-27-13: version 3.4.7 patch - IE fix - in showPie() and showLineChart(), adding timestamp to pie's src forces an 
 *		updated image to load.		-mlr
 */

/** calc18MonthOffset()
  * written: 2-4-13  -mlr
  */
function calc18MonthOffset()
{
global $beginDate18, $endDate18;

// calculate 18 month offset
    $endDate18	= date('Y-m-d', mktime(0, 0, 0, date("m"),  date("d")-14, date("Y"))); 
    $endMonth18	= substr($endDate18,5,2);
    $endDay18	= substr($endDate18,8,2);
    $endYear18	= substr($endDate18,0,4); 	
    $beginDate18= date('Y-m-d', mktime(0, 0, 0, $endMonth18-18, $endDay18, $endYear18));
} 

/** reportSearch34()
  * written: 2-4-13 -mlr
  */
function reportSearch34()
{
	global $conn, $themeId, $pantryID;
	
    echo "<form name='reportSearchForm' method='post' action='$_SERVER[PHP_SELF]' />";
    echo "<table border='0' cellspacing='0' class='reportSearchTbl34' >\n";	
    echo "<tr><td id='rSTitle' colspan='5'><i>Search By</i></td></tr>\n"; 	
    echo "<tr><td id='rSCol1'><i>Date:</i></td>\n";                   
	echo "<td id='rSCol2'>";
	selectReportDates();	
    echo "<td id='rSCol3'><i>Interval:</i>&#160;&#160;"; 
	selectReportInterval( "interval" );	
	echo "<br>";
	summaryOnlyCheckbox( "summaryOnly" );
    echo "</td><td id='rSCol4'><input style='none;' type='submit' name='search' value= 'Search' /></td>\n";
    echo "<input type='hidden' name='themeId' value= '$themeId' /></td>\n";	
	echo "</tr></table></form>\n";
}

 
/** reportSearch34Public()
  * written: 6-20-15 for version 3.5.3 upgrade. -mlr
  */
function reportSearch34Public()
{
	global $conn, $themeId, $pantryID;
	
    echo "<form name='reportSearchForm' method='post' action='$_SERVER[PHP_SELF]' />";
    echo "<table border='0' cellspacing='0' class='reportSearchTbl34' >\n";	
    echo "<tr><td id='rSTitle' colspan='5'><i>Search By</i></td></tr>\n"; 	
    echo "<tr><td id='rSCol1' style='padding-top:15px;'><i>Date:</i></td>\n";                   
	echo "<td id='rSCol2'>";
	selectReportDates();	
    echo "
	<td id='rSCol3'>";
	echo "
	<table border='0' style='margin:0;'><tr>
	<td><i>region:</i></td><td style='padding-left:4px;'>"; 
	selectReportRegion( "region" );	
	echo "</td></tr><tr><td><i>interval:</i><td style='padding-left:4px;'>";
	selectReportInterval( "interval" );	
	echo "
	</td></tr></td></tr></table>";
	summaryOnlyCheckbox( "summaryOnly" );
    echo "</td><td id='rSCol4'><input style='none;' type='submit' name='search' value= 'Search' /></td>\n";
    echo "<input type='hidden' name='themeId' value= '$themeId' /></td>\n";	
	echo "</tr></table></form>\n";
}	

/** getReportDates()
  * written: 2-7-13 -mlr
  */	
function getReportDates()
{
global $conn, $errCode, $beginDate18, $endDate18, $reportBeginDate, $reportEndDate;

/* first, check the $_POST array */

	if ( isset( $_POST['dateRad'] ) )
		if ( $_POST['dateRad'] == "prev18" ) {
		
	// previous 18 months	
			$reportBeginDate = $beginDate18;
			$reportEndDate = $endDate18;	
		} elseif ( $_POST['dateRad'] == "custom" ) {
		
	// custom	
			if ( isset( $_POST['customDateType'] ) ) {
			
// 8-21-14: version 3.5.2 update - validate custom date.		-mlr		
				if ( validateDate($_POST['customDateJACS'], 'm/d/Y') ) {			
					$customDate  = MMDDYYYToMySQL( $_POST['customDateJACS'] );	// JACS dates are in "mm/dd/yyyy" format
					if ( $_POST['customDateType'] == "equalto" ) {
						$reportBeginDate = $customDate;
						$reportEndDate = $reportBeginDate;	
					} elseif ( $_POST['customDateType'] == "after" ) {
						$reportBeginDate = date('Y-m-d', mktime(0, 0, 0, date("m", strtotime($customDate)), date("d", strtotime($customDate))+1, date("Y", strtotime($customDate))));
						$reportEndDate = tableDateLimit("end");
					} elseif ( $_POST['customDateType'] == "onorafter" ) {
						$reportBeginDate = $customDate;
						$reportEndDate = tableDateLimit("end");   //"9999-99-99";		
					} elseif ( $_POST['customDateType'] == "before" ) {
						$reportBeginDate = tableDateLimit("begin"); // "0000-00-00";
						$reportEndDate = date('Y-m-d', mktime(0, 0, 0, date("m", strtotime($customDate)), date("d", strtotime($customDate))-1, date("Y", strtotime($customDate))));
					} elseif ( $_POST['customDateType'] == "onorbefore" ) {
						$reportBeginDate = tableDateLimit("begin"); //"0000-00-00";
						$reportEndDate = $customDate;	
					}
				} else
					$errCode = 19;
			}
		} else {

	// range 	
			
// 8-21-14: version 3.5.2 update - validate both begin and end dates for range.		-mlr		
			if ( validateDate($_POST['rangeBeginDateJACS'], 'm/d/Y') && validateDate($_POST['rangeEndDateJACS'], 'm/d/Y') ) {
				$reportBeginDate = MMDDYYYToMySQL( $_POST['rangeBeginDateJACS'] );	// convert JACS dates	
				$reportEndDate = MMDDYYYToMySQL( $_POST['rangeEndDateJACS'] );
			} else	
				$errCode = 18;			
		}
		
/* next, check the $_GET array for sort headings */

	elseif ( isset( $_GET['dateRad'] ) )
		if ( $_GET['dateRad'] == "prev18" ) {
		
	// previous 18 months	
			$reportBeginDate = $beginDate18;
			$reportEndDate = $endDate18;	
		} elseif ( $_GET['dateRad'] == "custom" ) {
		
	// custom	
			if ( isset( $_GET['customDateType'] ) ) {
			
// 8-21-14: version 3.5.2 update - validate custom date.		-mlr		
				if ( validateDate($_GET['customDateJACS'], 'm/d/Y') ) {			
					$customDate  = MMDDYYYToMySQL( $_GET['customDateJACS'] );	// JACS dates are in "mm/dd/yyyy" format
					if ( $_GET['customDateType'] == "equalto" ) {
						$reportBeginDate = $customDate;
						$reportEndDate = $reportBeginDate;	
					} elseif ( $_GET['customDateType'] == "after" ) {
						$reportBeginDate = date('Y-m-d', mktime(0, 0, 0, date("m", strtotime($customDate)), date("d", strtotime($customDate))+1, date("Y", strtotime($customDate))));
						$reportEndDate = tableDateLimit("end");
					} elseif ( $_GET['customDateType'] == "onorafter" ) {
						$reportBeginDate = $customDate;
						$reportEndDate = tableDateLimit("end");   //"9999-99-99";		
					} elseif ( $_GET['customDateType'] == "before" ) {
						$reportBeginDate = tableDateLimit("begin"); // "0000-00-00";
						$reportEndDate = date('Y-m-d', mktime(0, 0, 0, date("m", strtotime($customDate)), date("d", strtotime($customDate))-1, date("Y", strtotime($customDate))));
					} elseif ( $_GET['customDateType'] == "onorbefore" ) {
						$reportBeginDate = tableDateLimit("begin"); //"0000-00-00";
						$reportEndDate = $customDate;	
					}
				} else
					$errCode = 19;					
			}
		} else {

	// range ($_GET array)	
			
// 8-21-14: version 3.5.2 update - validate both begin and end dates for range.		-mlr			
			if ( validateDate($_GET['rangeBeginDateJACS'], 'm/d/Y') && validateDate($_GET['rangeEndDateJACS'], 'm/d/Y') ) {
				$reportBeginDate = MMDDYYYToMySQL( $_GET['rangeBeginDateJACS'] );	// convert JACS dates	
				$reportEndDate = MMDDYYYToMySQL( $_GET['rangeEndDateJACS'] );
			} else	
				$errCode = 18;				
		}
		
}	

/** fmtTitle()
  * written: 2-1-13 -mlr
  */
function fmtTitle()
{
	global $interval, $intBegin, $intEnd, $reportBeginDate, $reportEndDate, $isPartial, $intHasInactive;
	
// format the title for display, and append an asterisk to partial intervals
	
	if ( $interval == "annual" ) {
		$returnVal = date( "Y", strtotime($intBegin) );
		if (	( date( "Y", strtotime($intBegin)) == date( "Y", strtotime($reportBeginDate)) && $intBegin != $reportBeginDate ) ||
				( date( "Y", strtotime($intEnd)) == date( "Y", strtotime($reportEndDate)) && $intEnd != $reportEndDate )	
			) {	
			$isPartial = 1;	
			$returnVal .= " &#42;";
		}	
	
	} elseif ( $interval == "quarter" ) {
		$returnVal = date( "M", strtotime($intBegin) ) . " - " . date( "M Y", strtotime($intEnd) );
		$endMonth = substr($reportEndDate,5,2);
		$endYear  = substr($reportEndDate,0,4);
		$quarterLimit = date('Y-m-d', mktime(0, 0, 0, $endMonth+2, 1, $endYear));
		
		if	(	( date( "Ym", strtotime($intBegin)) <= date( "Ym", strtotime($reportBeginDate)) && $intBegin != $reportBeginDate ) ||
				( date( "Ym", strtotime($intEnd))   >= date( "Ym", strtotime($reportEndDate)) && $intEnd != $reportEndDate )
			) {			
			$isPartial = 1;
			$returnVal .= " &#42;";			
		}	
	
	} elseif ( $interval == "month" ){
		$returnVal = date( "M Y", strtotime($intBegin) );
		if	(	( date( "Ym", strtotime($intBegin)) == date( "Ym", strtotime($reportBeginDate)) && $intBegin != $reportBeginDate ) ||
				( date( "Ym", strtotime($intEnd)) == date( "Ym", strtotime($reportEndDate)) && $intEnd != $reportEndDate )	
			) {
			$isPartial = 1;
			$returnVal .= " &#42;";			
		}	
	}
	if ( $intHasInactive )
		$returnVal .= " " . DAGGER_FOOTNOTE;
			
	return $returnVal;		
}

/** getNextInterval()
  * written: 1-29-13 -mlr
  */
function getNextInterval()
{
	global $interval, $intNum, $intBegin, $intEnd, $reportBeginDate, $reportEndDate;;	
	
	$interval = "annual";
	if ( isset( $_POST['search']) ) {
		$interval = $_POST['interval'];
	}	
	
	if ( $intNum == 0 ) {
		$beginMonth = substr($reportBeginDate,5,2);
		$beginYear  = substr($reportBeginDate,0,4);	
		if ( $interval == "annual" ) {
			$intBegin = $beginYear . "-01-01";
			$intEnd = $beginYear . "-12-31";
		} elseif ( $interval == "quarter" ) {
			if ( $beginMonth >= 1 && $beginMonth <= 3 ) {
				$intBegin = $beginYear . "-01-01";
				$intEnd = $beginYear . "-03-31";
			} elseif ( $beginMonth >= 4 && $beginMonth <= 6 ) {
				$intBegin = $beginYear . "-04-01";
				$intEnd = $beginYear . "-06-30";	
			} elseif ( $beginMonth >= 7 && $beginMonth <= 9 ) {
				$intBegin = $beginYear . "-07-01";
				$intEnd = $beginYear . "-09-30";
			} else {
				$intBegin = $beginYear . "-10-01";
				$intEnd = $beginYear . "-12-31";
			}	
		} else {
			$intBegin = "$beginYear-$beginMonth-01";
			$intEnd = "$beginYear-$beginMonth-" . date('t', strtotime($reportBeginDate) );  // 't' - last day in month
		}
		
	} else {	
	
		$beginMonth = substr($intBegin,5,2);
		$beginDay   = substr($intBegin,8,2);
		$beginYear  = substr($intBegin,0,4);
		$endMonth = substr($intEnd,5,2);
		$endDay   = substr($intEnd,8,2);
		$endYear  = substr($intEnd,0,4);	
		$daysInEndMonth = date( 't', strtotime($intEnd) );
		
		if ( $interval == "annual" ) {
			$intBegin = date('Y-m-d', mktime(0, 0, 0, $beginMonth, $beginDay, $beginYear+1));
			$intEnd = date('Y-m-d', mktime(0, 0, 0, $endMonth, 1, $endYear+1));
		} elseif ( $interval == "quarter" ) {
			$intBegin = date('Y-m-d', mktime(0, 0, 0, $beginMonth+3, $beginDay, $beginYear));
			$intEnd = date('Y-m-d', mktime(0, 0, 0, $endMonth+3, 1, $endYear));		
		} else {
			$intBegin = date('Y-m-d', mktime(0, 0, 0, $beginMonth+1, $beginDay, $beginYear));
			$intEnd = date('Y-m-d', mktime(0, 0, 0, $endMonth+1, 1, $endYear));
		}
		
		$intEnd = date('Y-m-d', mktime(0, 0, 0, substr($intEnd,5,2), date( 't', strtotime($intEnd) ), substr($intEnd,0,4)));		
	}	
	$intNum++;
}

/** makeIntervalPie()
  * written: 2-8-13 -mlr
  */
function makeIntervalPie( $count )
{
	global $title, $intNum;
	
	$title = fmtTitle();	
	echo "<table cellspacing='0' style='margin:15px;font-size:0.8em;text-align:center;border: 2px outset #eeeeee;'>\n";
	echo "<tr><td style='width:58%;text-align:center;padding:6px 0 0 15px;'>$title</td>\n";
	echo "<td style='width:42%;text-align:center;padding:6px 15px 0 0;'>total: ". number_format( array_sum($count) ) . "</td></tr>\n";
	echo "<tr><td colspan='2'>";
	showPie( $count, "pie$intNum" );
	echo "</td></tr></table>\n";	
}

/** showPie()
  * written: 1-29-13 -mlr
  */
function showPie( $countArr, $imageName )
{
	global $conn, $themeId, $pantryID;
	
	$chart = new PieChart(650,225);   	//	PieChart($width = 600, $height = 250) defined in libchart\classes\view\chart\PieChart.php
	$dataSet = new XYDataSet();
	$dataTot = 0;
	
	$sql = "SELECT * FROM pantries";	
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
		$pantryId = $row['pantryID'];	
		$dataSet->addPoint(new Point("$row[name] ($countArr[$pantryId])", $countArr[$pantryId]));
		$dataTot += $countArr[$pantryId];	
	}	
	$chart->setDataSet($dataSet);
//	$chart->setTitle("$title");
	$fileName="$imageName.png";
//	$chart->render("generated/$imageName.png");
	$chart->render("generated/$fileName");
//	$fileName="$imageName.png";
//	echo "<img alt='Pie chart' src='generated/$imageName' style='margin:0;padding:0;' />\n";

// 9-27-13: version 3.4.7 patch - adding timestamp to pie's src forces a new image to load in IE.		-mlr
	echo "<img alt='Pie chart' src='generated/$fileName" . "?" . time() . "'  style='margin:0;padding:0;' />\n";
}

/** showLineChart()
  * written: 2-9-13 -mlr
  */
function showLineChart()
{
	global $conn, $themeId, $pantryID, $lineChartInterval;
	
	$chart = new LineChart(650,225);
	$line = array();
	$sql = "SELECT pantryID FROM pantries";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	while ($row = mysqli_fetch_assoc($result)) {
		$pantryId = $row['pantryID'];
		$line[$pantryId] = new XYDataSet();	
	}
	$line[999] = new XYDataSet();	// for now, we'll use 999 for the total's line index

	foreach ( $lineChartInterval as $intDateKey => $pantryId ) {
		$intTotal=0;
		foreach ( $pantryId as $pantryIdKey => $count ) {
			$xPoint = date( "M Y", strtotime($intDateKey));
			$line[$pantryIdKey]->addPoint(new Point("$xPoint", $count));
			$intTotal+=$count;
		}	
		$line[999]->addPoint(new Point("$xPoint", $intTotal));	
	}	

	$dataSet = new XYSeriesDataSet();	
	$sql = "SELECT pantryID, name FROM pantries";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	while ($row = mysqli_fetch_assoc($result)) {
		$pantryId = $row['pantryID'];	
		$dataSet->addSerie("$row[name]", $line[$pantryId]);
	}	
	$dataSet->addSerie("Total", $line[999]);	

	$chart->setDataSet($dataSet);
//	$chart->setTitle("Sales for 2006");
	$chart->render("generated/lines.png");
//	echo "<img alt='Line Chart' src='generated/lines.png' style='margin:0;padding:0;' />\n";

// 9-27-13: version 3.4.7 patch - adding timestamp to line chart's src forces a new image to load in IE.		-mlr
	echo "<img alt='Line Chart' src='generated/lines.png" . "?" . time() . "'  style='margin:0;padding:0;' />\n";
}

/** doSummary()
  * written: 2-1-13 -mlr
  */
function doSummary( $sumTitle )
{
	global $conn, $isPartial, $foundInactive, $reportBeginDate, $reportEndDate, $grandCount, $lineChartInterval;
	
// get values for Summay

	if ( $reportBeginDate == $reportEndDate )
		$reportPeriod = date('M j, Y', strtotime($reportBeginDate));
	else
		$reportPeriod = date('M j, Y', strtotime($reportBeginDate)) . " thru " . date('M j, Y', strtotime($reportEndDate));
	
//	$sql = "SELECT count(*) FROM household";
//	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
//	if ($row = mysqli_fetch_assoc($result))
//		$houseRowsTotal = $row['count(*)'];
		
	$sumTitleFoot="";	
	if ( $foundInactive )
		$sumTitleFoot = " " . DAGGER_FOOTNOTE;		
			
// print Summary 			

    echo "<table border='0' cellspacing='0' class='reportSummary34'>\n";	
    echo "<tr><td id='rSumTitle' colspan='2'><i>Summary for $sumTitle $sumTitleFoot</i></td></tr>\n";
    echo "<tr><td style='text-align:left;'>Report Period</td>\n"; 
    echo "<td style='text-align:right;'>$reportPeriod</td></tr>\n";
    echo "<tr><td style='text-align:left;'>Total $sumTitle</td>\n";
    echo "<td style='text-align:right;'>" . number_format( array_sum($grandCount) ) . "</td></tr>\n";	
//    echo "<tr><td style='text-align:left;'>total row(s) in 'household' table</td>\n";  
//    echo "<td style='text-align:right;'>" . number_format( $houseRowsTotal ) . "</td></tr>\n";
	echo "<tr><td colspan='2' style='padding-left:23px;border-bottom:0px;'>";	
	showPie( $grandCount, "summary_pie" );
	echo "</td></tr><tr><td colspan='2' style='border-top:0px;padding-bottom:15px;'>";	
	showLineChart();	
    echo "</td></tr></table>";	
	
	if ( $isPartial ) 
		echo "<p style='margin:10px;font-size:10pt;'>&#42; partial interval (not all dates were selected)</p>";
		
	if ( $foundInactive ) {
		echo "<p style='margin:10px;font-size:10pt;'>";	
		echo DAGGER_FOOTNOTE . " The following pantry(s) are no longer active within the PEPartnership Alliance:";
		$sql = "SELECT * FROM pantries WHERE is_active = 0 ORDER BY name";
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<br><b>$row[name] ($row[abbrev]) active ";
			echo date( 'M j, Y', strtotime("$row[start_date]")) . " - ";
			echo date( 'M j, Y', strtotime("$row[inactive_date]"));	
		}	
		echo "</b></p>\n";
	}
}

function selectReportDates()
/**
 * written: 2-4-13 for version 3.4 upgrade        -mlr
 *
*/
{
global $beginDate18, $endDate18;

	$customDateType = "equalto";
	$customDateJACS = "";
	$rangeBeginDateJACS	= "";
	$rangeEndDateJACS	= "";
	if ( isset( $_POST['search']) ) {
		if ( isset($_POST['customDateType']) ) $customDateType = $_POST['customDateType'];
		if ( isset($_POST['customDateJACS']) ) $customDateJACS = $_POST['customDateJACS'];
		if ( isset($_POST['rangeBeginDateJACS']) ) $rangeBeginDateJACS = $_POST['rangeBeginDateJACS'];
		if ( isset($_POST['rangeEndDateJACS']) ) $rangeEndDateJACS = $_POST['rangeEndDateJACS'];
	} elseif ( isset( $_GET['search']) ) {
		if ( isset($_GET['customDateType']) ) $customDateType = $_GET['customDateType'];
		if ( isset($_GET['customDateJACS']) ) $customDateJACS = $_GET['customDateJACS'];
		if ( isset($_GET['rangeBeginDateJACS']) ) $rangeBeginDateJACS = $_GET['rangeBeginDateJACS'];
		if ( isset($_GET['rangeEndDateJACS']) ) $rangeEndDateJACS = $_GET['rangeEndDateJACS'];
	}	

// previous 18 months	
	echo "<table border='0'>";
    echo "<tr><td nowrap='nowrap' style='padding:0px;'>";
    echo "<input id='prev18RadId' style='margin:4px;vertical-align:center;' type='radio' name='dateRad' value='prev18' onclick='checkReportPrev18DateRadio();' /></td>\n";
	echo "<td colspan='2'><div id='prev18DivId'>";
    echo "18 months ( " . date('M j, Y', strtotime($beginDate18)) . " thru " . date('M j, Y', strtotime($endDate18)) . " )</div>";
    echo "</td></tr>\n";

// custom
    echo "<tr><td style='padding-left:0px;'>";
    echo "<input id='customDateRadId' style='float:left;margin:5px 4px 4px 4px;' type='radio' name='dateRad' value='custom' onclick='checkReportCustomDateRadio();' /></td>\n";
	echo "<td>";
	selectCustomType( "customDateType", $customDateType );
	echo "</td>\n";
	echo "<td style='padding-left:3px;'>";
    echo "<input id='customDateJACSId' style='width:90px;text-align:left;padding-left:3px;' name='customDateJACS' type='text' value='$customDateJACS' />\n";
    echo "<img style='vertical-align:-3px;' src='" . ROOT . "images/iconCalendar.gif' onclick='callJACS34(" . '"customDateJACSId"' . ");' />\n";
    echo "</td></tr>\n";
	
// range
	echo "<tr><td style='padding-left:0px;'>";	
    echo "<input id='rangeDateRadId' style='float:left;margin:4px;' type='radio' name='dateRad' value='range' onclick='checkReportRangeDateRadio();' /></td>\n";
    echo "<td style='width:157px;'>";
    echo "<input id='rangeBeginDateJACSId' style='float:left;width:90px;text-align:left;padding-left:3px;' name='rangeBeginDateJACS' type='text' value='$rangeBeginDateJACS'/>\n";
	echo "<img style='float:left;margin-left:4px;margin-top:3px;' src='" . ROOT . "images/iconCalendar.gif' onclick='callJACS34(" . '"rangeBeginDateJACSId"' . ");' />\n";
	echo "<div id='rangeDateDivId' style='float:left;margin-left:5px;margin-top:4px;' >&#160;thru&#160";
	echo "</div></td>";
	echo "<td style='padding-left:3px;'>";
    echo "<input id='rangeEndDateJACSId' style='width:90px;text-align:left;padding-left:3px;' name='rangeEndDateJACS' type='text' value='$rangeEndDateJACS'/>\n";
	echo "<img style='margin-left:0px;vertical-align:-3px;' src='" . ROOT . "images/iconCalendar.gif' onclick='callJACS34(" . '"rangeEndDateJACSId"' . ");' />\n";
	echo "</td></tr></table>";

}

/** selectCustomType( $selectName, $selectValue )
  * written: 1-30-13 for version 3.4 upgrade	 -mlr
  */
function selectCustomType( $selectName, $selectValue )
{
	echo "<select id='customDateSelectId' style='width:145px;margin:0px;' name='$selectName' />\n";
	echo "<option onclick='checkReportCustomDateRadio();' "; 
	if ( $selectValue == "equalto" ) echo "selected ";  
	echo "value= 'equalto'>equal to</option>\n";
	echo "<option onclick='checkReportCustomDateRadio();' "; 
	if ( $selectValue == "after" ) echo "selected ";  
	echo "value= 'after'>after</option>\n";	
	echo "<option onclick='checkReportCustomDateRadio();' "; 
	if ( $selectValue == "onorafter" ) echo "selected ";  
	echo "value= 'onorafter'>on or after</option>\n";	
	echo "<option onclick='checkReportCustomDateRadio();' "; 
	if ( $selectValue == "before" ) echo "selected ";  
	echo "value= 'before'>before</option>\n";	
	echo "<option onclick='checkReportCustomDateRadio();' "; 
	if ( $selectValue == "onorbefore" ) echo "selected ";  
	echo "value= 'onorbefore'>on or before</option>\n";	
	echo "</select>";	
}

/** selectReportRegion()
  * written: 6-20-15 for version 3.5.3 upgrade	 -mlr
  */
function selectReportRegion( $selectName )
{
	$region = "Wisconsin";
	if ( isset( $_POST['search']) ) {
		$region = $_POST['region'];
	}	

	echo "<select style='width:135px;' name= '$selectName' >\n";
	echo "<option "; 
	if ( $region == "New York" ) echo "selected ";  
	echo "value= 'New York'>New York</option>\n";
	echo "<option "; 
	if ( $region == "Wisconsin" ) echo "selected ";  
	echo "value= 'Wisconsin'>Wisconsin</option>\n";	
	echo "</select>";
}

/** selectReportInterval()
  * written: 1-30-13 for version 3.4 upgrade	 -mlr
  */
function selectReportInterval( $selectName )
{
	$interval = "summary";
	if ( isset( $_POST['search']) ) {
		$interval = $_POST['interval'];
	}	

	echo "<select style='width:135px;' name= '$selectName' >\n";
	echo "<option "; 
	if ( $interval == "annual" ) echo "selected ";  
	echo "value= 'annual'>annual</option>\n";
	echo "<option "; 
	if ( $interval == "quarter" ) echo "selected ";  
	echo "value= 'quarter'>quarter</option>\n";	
	echo "<option "; 
	if ( $interval == "month" ) echo "selected ";  
	echo "value= 'month'>month</option>\n";	
	echo "</select>";
}

/** summaryOnlyCheckbox( $checkboxName )
  * written: 2-10-13 for version 3.4 upgrade	 -mlr
  */
function summaryOnlyCheckbox( $checkboxName )
{
	echo "<input type='checkbox' style='vertical-align:-2px;margin:10px 4px 0 0;' id='sumOnlyCheckboxId' name='$checkboxName' value='checked' />\n";
	echo "show summary only";
}

/** 10-18-14: version 3.5.21 patch - remove tableDateLimit($foo) from PEP019 and add it here
/** tableDateLimit( $foo )
  * written: 2-7-13 -mlr
  */
function tableDateLimit( $foo )
{
global $conn;

	if ( $foo == "begin" ) $order = "ASC"; else $order = "DESC";

	$sql = "SELECT regdate FROM household WHERE regdate > '0000-00-00' ORDER BY regdate $order";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result)) 				
		return $row['regdate'];
	else
		return "0000-00-00";
}
?>