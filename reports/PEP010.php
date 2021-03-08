<?php 
/**
 * PEP010.php - Households By Language and Pantry of Registration
 * copied from \PEP2\PEPReports on 7-23-12
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
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
    $TotHH=0;
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

    doHeader("PEP010 - Households By Language and Pantry of Registration", "isReport");	

/* MAINLINE */		
	
    CalcDateLimits();
	
 	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Households By Language and Pantry of Registration - PEP010</h3>\n";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on households from PEPbase 'household' table who were active within the last 18 months, with a 2 week offset"; 
    echo " ($LowerLimitFmt thru $UpperLimitFmt). Duplicate households, households with id=0, and households with invalid zip codes are omitted from the study.</i></p>\n"; 	

    $LowerLangID = 0;
    $UpperLangID = 0;
    $FirstRead = 1;

    TableHeading();

    $sql = "SELECT * FROM languages ORDER BY id";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
        if ($FirstRead) {
            $LowerLangID=$row['id'];
            $FirstRead=0;
        }

        PrintRow($row['id'], $row['name']);
        $UpperLangID=$row['id'];
    }

    PrintFootNote();
    echo "</center>";
	mysqli_close( $conn );	
}	

/* FUNCTIONS */	

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
?>
    <TABLE border=1 style="padding:5px;">

    <TR>
    <TH class="HouseHead">Language
<?php
    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {    
        $i=$row['pantryID'];
        $PantryTot[$i]=0;
		
// 1-14-13: version 3.4 upgrade - add asterisk to inactive pantries		
		$inactiveFoot = "";			
		if ( !$row['is_active'] ) {
			$foundInactive=1;				
			$inactiveFoot = DAGGER_FOOTNOTE;
		}					
        echo "<th class='HouseHead'>$row[name] $inactiveFoot</th>\n"; 
    }
?>
    <TH class="HouseHead">TOTAL
    </TR>
<?php
}

function PrintRow($LangID, $Language)
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $UpperLimit, $LowerLimit,$PantryTot,$TotHH;
?>
    <TR>
    <TH style="font-size:10pt;font-weight:bold;text-align:left;padding-left:10px;"><?php echo $Language; 

    $Total=0; 


    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {   
        $pantry_id = $row['pantryID'];
?>
        <TD class="rightjust">
<?php
        $sql2 = "SELECT count(*)  
                 FROM household
                 JOIN languages
                 ON household.language = languages.id 
                 WHERE household.lastactivedate >= '$LowerLimit'
                 AND household.lastactivedate <= '$UpperLimit'
                 AND household.streetname NOT LIKE 'duplicate%'
                 AND household.id > 0
                 AND household.pantry_id = $pantry_id
                 AND household.zip_five > 0
                 AND languages.id = $LangID";                    // 2-14-10: Invalid zip codes omitted 

        $result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
        if ($row2 = mysqli_fetch_assoc($result2))
        {   
            if ($row2['count(*)'])
                echo number_format($row2['count(*)']);
            else
                echo "&#160;";

            $Total= $Total + $row2['count(*)'];
            $TotHH=$TotHH + $row2['count(*)'];
            $i=$row['pantryID'];
            $PantryTot[$i] = $PantryTot[$i]+ $row2['count(*)'];
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
global $conn, $PantryTot, $LowerLimit, $UpperLimit, $TotHH, $LowerLangID, $UpperLangID, 
	$foundInactive;
 
    $GrandTot = 0;
    $LowerPantryID=0;
    $UpperPantryID=0;
    $FirstRead=1;

// print last row

	echo "<tr><th class='TopHead'>TOTALS</th>\n";
    $sql = "SELECT * FROM pantries ORDER BY pantryID";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {    
        if ($FirstRead) {
            $FirstRead=0;
            $LowerPantryID=$row['pantryID'];
        }

        $i=$row['pantryID'];
        $GrandTot= $GrandTot+ $PantryTot[$i];
        $UpperPantryID=$row['pantryID'];
		echo "<th class='rightjust'>" . number_format($PantryTot[$i]) . "</th>\n"; 
    }

	echo "<th class='rightjust'>" . number_format($GrandTot) . "</th>";
    echo "</tr></table>";
	
// print summary	

	$tableSize =0;
	$sql = "SELECT count(*) FROM household";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result))
		$tableSize = $row['count(*)'];
		
    echo "<table border='1' class='reportSummary'>\n";	
    echo "<tr><th colspan='2'>Summary</th></tr>\n";
    echo "<tr><td id='col1'>'household' table row(s) used in study</td>\n"; 
    echo "<td id='col3' style='text-align:center;'>" . number_format($TotHH) . "</td></tr>\n";
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