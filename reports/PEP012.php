<?php 
/**
 * PEP012.php - Households by Number of Visits and Zip Code
 * copied from \PEP2\PEPReports on 7-23-12
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 * 
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script;
 *			add date and pantry search form; replace pantry names with abbreviations.	-mlr
 *
 * 1-15-13: version 3.4 upgrade
 *		- added footnote for inactive pantries   -mlr
 * 
 * 10-10-12: version 3.1 updates - Add code for new variable '$themeId'.   -mlr 
 *
 */
 
/* VERIFY ACCESS LEVEL */
 
if ( (!isset($_COOKIE["accessLevel"])) ||  ((isset($_COOKIE["accessLevel"])) && $_COOKIE["accessLevel"] < 1)) 
    echo "<b>! UNAUTHORIZED ACCESS. PLEASE CONTACT SYSTEM ADMINISTRATOR</b>";	
else {	

/* CONSTANT DECLARATIONS   */

    define('HOST_ACCESS_LEVEL', $_COOKIE["accessLevel"]);
	define('HOST_SIGNIN_ID', $_COOKIE["signinId"]);
	define ( 'DAGGER_FOOTNOTE', '<sup>&#8224;</sup>' );		

/* INCLUDE FILES */

	require_once('../MySQLConfig.php'); 
	require_once(ROOT . 'common_vars.php');
	require_once(ROOT . 'functions.php');	
	require_once(ROOT . 'Header.php');
	require_once(ROOT . 'Themes.php');	
	require_once(ROOT . 'Reports/ReportFunctions.php');	
	
/* INITIALIZE VARS */

	$sql = "SELECT * FROM access_levels WHERE al_id = " . HOST_ACCESS_LEVEL;
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    $accessLevelRow = mysqli_fetch_assoc($result);
	
	$sql = "SELECT * FROM signin_accounts WHERE sa_id = " . HOST_SIGNIN_ID;
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result)) 
	    $hostPantryId = $row['sa_pantry_id'];
	else
		die('host signin not found in signin_accounts table');
 
    set_time_limit(900);
    if (isset($_POST['CompileResults'])) 
        $inNumVisits = $_POST['inNumVisits'];
    else
        $inNumVisits = 1;
    $Max=0;
    $Min=0;
    $ValidData=0;
    $TodaysDate = date('M j, Y');
	$foundInactive =0;				// 1-15-13: version 3.4 upgrade	
	
// set global $conn,  variable '$themeId'	
	if ( isset($_GET['Login']) )	
		$themeId = hostPantryTheme();		// defined in Themes.php
	elseif ( isset($_GET['themeId']) )
		$themeId = $_GET['themeId'];
	elseif ( isset($_POST['themeId']) )		
		$themeId = $_POST['themeId'];
	else	
		$themeId=0;	
		
// 1-30-14: version 3.4.82 update - set global var $pantryID	
	if ( isset($_POST['pantryID']) ) 
		$pantryID = $_POST['pantryID'];
	elseif ( isset($_GET['pantryID']) )
		$pantryID = $_GET['pantryID'];			
	elseif ( $hostPantryId == 0 ) {
		$pantryID = firstPantry();
	} else
		$pantryID = $hostPantryId;				
		
	defineThemeConstants();					// defined in Themes.php	
	
/* XHTML HEADER */

//    doHeader("PEP012 - Households by Number of Visits and Zip Code", "isReport");	
    doHeader("PEP012 - Households by Number of Visits and Zip Code", "isBoardReport");	

/* MAINLINE */			
	
//    CalcDateLimits();
	
 	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Households by Number of Visits and Zip Code - PEP012</h3>\n";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on households from the 'consumption' and 'household' tables who were active within the selected
	date range.</i></p>\n"; 	

//    DateRangeForm();
	
	calc18MonthOffset();
	reportSearch();	

    if (isset($_POST['search'])) {
		getReportDates();
        FindVisitsPerHH();
        TableHeading();
        PrintZipTable();
        PrintFootNote();
    }
	mysqli_close( $conn );
}	
      
/* FUNCTIONS */	

/** reportSearch()
  * written: 1-30-14 -mlr
  */
function reportSearch()
{
	global $conn, $themeId, $hostPantryId, $pantryID, $inNumVisits;
	
	$pantry_id = "All";
	if ( isset( $_POST['search']) ) {
		$pantry_id = $_POST['pantry_id'];
	}		
	$inNumVisits = "1";
	if ( isset( $_POST['search']) ) {
		$inNumVisits = $_POST['inNumVisits'];
	}			
    echo "<form name='reportSearchForm' method='post' action='$_SERVER[PHP_SELF]' />";
    echo "<table border='0' cellspacing='0' class='reportSearchTbl34' >\n";	
    echo "<tr><td id='rSTitle' colspan='5'><i>Search By</i></td></tr>\n"; 	
    echo "<tr><td id='rSCol1'><i>Date:</i></td>\n";                   
	echo "<td id='rSCol2'>";
	selectReportDates();	
    echo "<td id='rSCol3'><i>Pantry:</i>&#160;&#160;"; 
	selectReportPantry( "pantry_id", $pantry_id );	
    echo "<p style='margin:10px;'><i>Visits:</i>&#160;&#160;"; 
    echo "<input type='text' name='inNumVisits' value='$inNumVisits'></p></td>";	
    echo "</td><td id='rSCol4'><input style='none;' type='submit' name='search' value= 'Search' /></td>\n";
    echo "<input type='hidden' name='themeId' value= '$themeId' /></td>\n";	
	echo "</tr></table></form>\n";
}

function FindVisitsPerHH()
##################################################################################
#   written: 3-30-10                                                             #
#                                                                                #
##################################################################################
{
global $conn, $CurrHousehold_id, $CurrZip_five, $CurrPantry_id, $NumVisits, $reportBeginDate, 
$reportEndDate, $ValidData;

    $FirstRow = 1;

    $sql = "DELETE FROM report_work";
    $DeleteOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	
	$pantry_q=1;	
	if ( isset($_POST['pantry_id']) ) 
		if ( $_POST['pantry_id'] != "All" )
			$pantry_q = " pantry_id = $_POST[pantry_id] ";	

//    $sql = "SELECT * FROM consumption
//            WHERE date >= '$LowerLimit'
//            AND date <= '$UpperLimit'
//            AND household_id > 0
//            ORDER BY household_id, date";
			
    $sql = "SELECT * FROM consumption
            WHERE date >= '$reportBeginDate'
            AND date <= '$reportEndDate'
            AND household_id > 0
			AND $pantry_q		
            ORDER BY household_id, date";			

    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
	
		$sql2 = "SELECT * FROM household WHERE id = $row[household_id]";
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		while ($row2 = mysqli_fetch_assoc($result2)) {	

			$ValidData++;
			if ( $FirstRow ) {
				$currDate = $row['date'];
				$currTime = $row['time'];			
				$CurrHousehold_id = $row['household_id'];
				$CurrZip_five     = $row2['zip_five'];
//				$CurrPantry_id    = $row2['pantry_id'];
				$CurrPantry_id    = $row['pantry_id'];				
				$FirstRow = 0;
				$NumVisits=1;
			} else {
				if ( $row['household_id'] > $CurrHousehold_id ) {
					InsertWorkRow();
					$currDate = $row['date'];
					$currTime = $row['time'];					
					$CurrHousehold_id = $row['household_id'];
					$CurrZip_five     = $row2['zip_five'];
//					$CurrPantry_id    = $row2['pantry_id'];
					$CurrPantry_id    = $row['pantry_id'];					
					$NumVisits=1;
				} else {
					if ( $row['date'] > $currDate || $row['time'] > $currTime ) {
						$NumVisits++;
						$currDate = $row['date'];
						$currTime = $row['time'];					
					} 
				}
			}
		}	
    } 

    if ( !$FirstRow )                     // ignore empty query results
        InsertWorkRow();
}

function InsertWorkRow()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn, $CurrHousehold_id, $CurrZip_five, $CurrPantry_id, $NumVisits;


    $sql = "INSERT INTO report_work                                   

                       (RW_household_id, 
                        RW_num_visits,
                        RW_zip_five,
                        RW_pantry_id)

                VALUES ('$CurrHousehold_id',          
                        '$NumVisits',
                        '$CurrZip_five',
                        '$CurrPantry_id')";     

    $InsertOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
}


function TableHeading()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $PantryTot, $foundInactive;
?>
    <TABLE border=1 style="padding:5px;">

    <TR>
    <TH class="HouseHead">Zip Code
<?php
    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {    
        $i=$row['pantryID'];
        $PantryTot[$i]=0;
		
// 1-14-13: version 3.4 upgrade - add asterisk to inactive pantries		
		$inactiveFoot = "";			
		if ( !$row['is_active'] ) {
			$foundInactive=1;				
			$inactiveFoot = DAGGER_FOOTNOTE;
		}					
//        echo "<th class='HouseHead'>$row[name] $inactiveFoot</th>\n"; 
        echo "<th class='HouseHead' title='$row[name]'>$row[abbrev] $inactiveFoot</th>\n";		
    }
?>
    <TH class="HouseHead">Total
    </TR>
<?php
}

function PrintZipTable()
##################################################################################
#   written: 12-13-10      -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn, $inNumVisits;

    $sql = "SELECT RW_zip_five
            FROM report_work
            WHERE RW_num_visits = '$inNumVisits'
            GROUP BY RW_zip_five";

    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
        PrintRow($row['RW_zip_five']);
}


function PrintRow($ZipCode)
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $inNumVisits, $PantryTot, $TotHH, $Grand;
?>
    <TR>
    <TH style="font-size:10pt;font-weight:bold;text-align:left;padding-left:10px;">
    <?php if ($ZipCode == NULL)
              echo "&#160;";
          else 
              echo $ZipCode; 

    $Total=0; 


    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {   
        $pantry_id = $row['pantryID'];
//        if (!$ZipCode)
//            $ZipCode=0;
?>
        <TD class="rightjust">
<?php
        $sql2 = "SELECT count(RW_zip_five)  
                 FROM report_work
                 WHERE RW_num_visits = '$inNumVisits'
                 AND RW_pantry_id = $pantry_id
                 AND RW_zip_five = '$ZipCode'";

        $result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
        if ($row2 = mysqli_fetch_assoc($result2))
        {   
            if ($row2['count(RW_zip_five)'])
                echo number_format($row2['count(RW_zip_five)']);
            else
                echo "&#160;";

            $Total= $Total + $row2['count(RW_zip_five)'];
            $TotHH=$TotHH + $row2['count(RW_zip_five)'];
            $Grand=$Grand+$row2['count(RW_zip_five)'];
            $PantryTot[$pantry_id] = $PantryTot[$pantry_id]+ $row2['count(RW_zip_five)'];
        }
    }
?>
    <TD class="rightjust"><?php echo number_format($Total); ?>
    </TR>

<?php


}

function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $inNumVisits, $PantryTot, $LowerLimit, $UpperLimit, $TotHH, $LowerLangID,
	$UpperLangID, $Grand, $foundInactive, $reportBeginDate, $reportEndDate;
	
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

	echo "<tr>";
    echo "<th class='TopHead'>TOTALS</th>";
    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {    
        $i=$row['pantryID'];
        echo "<th class='rightjust'>" . number_format($PantryTot[$i]) . "</th>\n"; 
    }
	
    echo "<th class='rightjust'>" . number_format($Grand) . "</th>\n";
    echo "</tr></table>";

	$tableSize = 0;
	$sql = "SELECT count(*) FROM household";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result))
		$tableSize = $row['count(*)'];   
	
    echo "<table border='1' class='reportSummary'>\n";	
    echo "<tr><th colspan='2'>Summary</th></tr>\n";
	
    echo "<tr><td id='col1'>Report Period</td>\n"; 
    echo "<td id='col3'>$reportPeriod</td></tr>\n";	
	
    echo "<tr><td id='col1'>Pantry</td>\n"; 
    echo "<td id='col3'>$title</td></tr>\n";		
	
	
    echo "<tr><td id='col1'>household(s) with $inNumVisits visit(s)</td>\n"; 
    echo "<td id='col3'>" . number_format($TotHH) . "</td></tr>\n";
    echo "<tr><td id='col1'>total row(s) in 'household' table</td>\n"; 
    echo "<td id='col3'>" . number_format($tableSize) . "</td></tr>\n";	
    echo "</table>";
	
// 1-14-13: version 3.4 upgrade - add footnote for inactive pantries
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
?>
</FONT>
</body>
</html>