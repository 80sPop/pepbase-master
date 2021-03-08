<?php 
/**
 * PEP003.php - Households By Zip Code and Pantry of Registration
 * copied from \PEP2\PEPReports on 7-23-12
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 * 
 * 11-29-2013: version 3.4.81 patch 
 *		- logic now omits non-numeric zip codes from report	-mlr
 * 
 * 1-15-13: version 3.4 upgrade
 *		- add asterisk and footnote for inactive pantries
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

/* INCLUDE FILES */

	require_once('../MySQLConfig.php'); 
	require_once(ROOT . 'common_vars.php');
	require_once(ROOT . 'functions.php');	
	require_once(ROOT . 'Header.php');
	require_once(ROOT . 'Themes.php');		
	
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

    $Tally1=0;
    $Tally2=0;
    $Tally3=0;
    $Tally4=0;
    $Tally5=0;

    $Total41=0;
    $Total42=0;  
    $Total43=0;   
    $Total44=0;  
    $Total45=0;  

    $Grand=0;
	$foundInactive=1;	
    $TodaysDate = date('M j, Y');
	
// set global $conn,  variable '$themeId'	
	if ( isset($_GET['Login']) )	
		$themeId = hostPantryTheme();		// defined in Themes.php
	elseif ( isset($_GET['themeId']) )
		$themeId = $_GET['themeId'];
	elseif ( isset($_POST['themeId']) )		
		$themeId = $_POST['themeId'];
	else	
		$themeId=0;	
		
	defineThemeConstants();					// defined in Themes.php		
	
/* XHTML HEADER */

    doHeader("PEP003 - Households By Zip Code and Pantry of Registration", "isReport"); 
 
/* MAINLINE */	
	
    CalcDateLimits();
	
	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Households By Zip Code and Pantry of Registration - PEP003</h3>\n";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on households from PEPbase 'household' table who were active within the last 18 months, with a 2 week offset"; 
    echo "( $LowerLimitFmt thru $UpperLimitFmt ). Duplicate households and households with id=0 are omitted from the study.</i></p>\n"; 	

    TableHeading();

    $sql = "SELECT zip_five
            FROM household
            WHERE lastactivedate >= '$LowerLimit'
            AND lastactivedate <= '$UpperLimit'
            AND streetname NOT LIKE 'duplicate%'
            AND id > 0
            AND zip_five > 0
            GROUP BY zip_five";

// 11-29-2013: version 3.4.81 patch - logic now omits non-numeric zip codes from report	-mlr
    $result = mysqli_query( $conn, $sql ) or die('SQL ERROR - mainline');	
    while ($row = mysqli_fetch_assoc($result))
		if (! is_numeric($row['zip_five'])) 
			echo "ERROR in household table zip code=$row[zip_five]<br>";
		else	
			PrintRow($row['zip_five']);

    PrintFootNote();
	mysqli_close( $conn );	
    echo "</center>";
}
######################################################################
######################################################################
#                    F U N C T I O N S                               #
######################################################################

function CalcDateLimits()
##################################################################################
#   written: 10-31-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $UpperLimit, $UpperLimitFmt, $LowerLimit, $LowerLimitFmt;
   
    $TodaysDate  = date("Y-m-d");
    $TodaysMonth = substr($TodaysDate,5,2);
    $TodaysDay   = substr($TodaysDate,8,2);
    $TodaysYear  = substr($TodaysDate,0,4);

    $UpperLimit    = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear)); 
    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));
    $UpperMonth    = substr($UpperLimit,5,2);
    $UpperDay      = substr($UpperLimit,8,2);
    $UpperYear     = substr($UpperLimit,0,4); 

    $LowerLimit    = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
}



function TableHeading()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $PantryTot, $foundInactive;

	echo "<table border='1' style='padding:5px;'>";
	echo "<tr><td class='HouseHead'>Zip Code</td>\n";

    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {    
        $i=$row['pantryID'];
        $PantryTot[$i]=0;
		
// 1-14-13: version 3.4 upgrade - add asterisk to inactive pantries		
		$asterisk = "";			
		if ( !$row['is_active'] ) {
			$foundInactive=1;				
			$asterisk = "&#42;";
		}			
		echo "<td class='HouseHead'>$row[name] $asterisk</td>\n";		// notice how php interpolates associative arrays 
	}
	echo "<td class='HouseHead'>TOTAL</td></tr>\n";
}

function PrintRow($ZipCode)
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $UpperLimit, $LowerLimit, $PantryTot, $TotHH, $Grand;
?>
    <TR>
    <TH style="font-size:10pt;font-weight:bold;text-align:left;padding-left:10px;"><?php echo $ZipCode; 

    $Total=0; 

    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {   
        $pantry_id = $row['pantryID'];
?>
        <TD class="rightjust">
<?php
        $sql2 = "SELECT count(zip_five)  
                 FROM household
                 WHERE pantry_id = $pantry_id
                 AND zip_five = $ZipCode
                 AND lastactivedate >= '$LowerLimit'
                 AND lastactivedate <= '$UpperLimit'
                 AND streetname NOT LIKE 'duplicate%'
                 AND id > 0";

//        $result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
        $result2 = mysqli_query( $conn, $sql2 ) or die('SQL ERROR in function PrintRow()');		
        if ($row2 = mysqli_fetch_assoc($result2))
        {   
            if ($row2['count(zip_five)'])
                echo number_format($row2['count(zip_five)']);
            else
                echo "&#160;";

            $Total= $Total + $row2['count(zip_five)'];
            $TotHH=$TotHH + $row2['count(zip_five)'];
            $Grand=$Grand+$row2['count(zip_five)'];
            $PantryTot[$pantry_id] = $PantryTot[$pantry_id]+ $row2['count(zip_five)'];
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
global $conn, $PantryTot, $LowerLimit, $UpperLimit, $TotHH, $LowerLangID, $UpperLangID, $Grand,
	$foundInactive;

    echo "<tr><th class='TopHead'>TOTALS";
    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {    
        $i=$row['pantryID'];
        echo "<th class='rightjust'>" . number_format($PantryTot[$i]); 
    }
    echo "<th class='rightjust'>" . number_format($Grand);
    echo "</tr></table>";
	
// calculate values for Summary 
	$sql = "SELECT count(zip_five)
			FROM household
			WHERE lastactivedate < '$LowerLimit'
			OR lastactivedate > '$UpperLimit'
			OR streetname LIKE 'duplicate%'
			OR id <= 0
			OR zip_five <= 0";
			
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result)) 
		$inactiveRows = $row['count(zip_five)'];

	$sql = "SELECT count(*) FROM household";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result))
		$totalRows = $row['count(*)'];		

    echo "<table border='1' class='reportSummary'>\n";	
    echo "<tr><th colspan='2'>Summary</th></tr>\n";	
    echo "<tr><td id='col1'>Active 'household' table row(s) used in study</td>\n"; 
    echo "<td id='col3'>" . number_format($TotHH) . "</td></tr>\n";
    echo "<tr><td id='col1'>Inactive or invalid 'household' table row(s)</td>\n";
    echo "<td id='col3'>" . number_format($inactiveRows) . "</td></tr>\n";
    echo "<tr><td id='col1'>total row(s) in 'household' table</td>\n";
    echo "<td id='col3'>" . number_format($totalRows) . "</td></tr>\n";
    echo "</table>";
	
// 1-14-13: version 3.4 upgrade - add footnote for inactive pantries
	if ( $foundInactive ) {
		echo "<p style='margin:10px;font-size:10pt;'>";	
		echo "&#42; The following pantry(s) are no longer active within the PEPartnership Alliance:";
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