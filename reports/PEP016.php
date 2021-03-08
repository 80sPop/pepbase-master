<?php 
/**
 * PEP016.php - New Households Registered by Month
 * copied from \PEP2\PEPReports and modified on 8-1-12
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 *
 * 1-16-13: version 3.4 upgrade 
 *		- replaced all MYSQL functions with MYSQLI extension. 
 *		- Make MYSQLI link variable '$conn' global to all functions.
 * 		- add footnote for inactive pantries  
 *		- new css for Summary 
 *
 * 10-28-12: version 3.3 updates - set $pantryID as global $conn,  to entire system.   -mlr
 * 
 * 10-10-12: version 3.1 updates - Add code for new variable '$themeId'.   -mlr
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

    $quantity_oked=0;
    $quantity_reqd=0;
    $quantity_used=0;
    $TotVisits=0;
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
		
// 10-24-12: version 3.3 update - set global $conn,  var $pantryID	
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

    doHeader("PEP016 - New Households Registered by Month", "isReport");	

/* MAINLINE */

    CalcDateLimits();
	
	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>New Households Registered by Month - PEP016</h3>";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on data from PEPbase 'household' table.</i></p>\n"; 	

    PeriodAndPantryForm();

    if ( isset($_POST['CompileResults']) && $DateRangeOK ) {
        TableHeadings();
        CrawlHouseholdTable();
        PrintFootNote();
    }
	mysqli_close( $conn );
}

/* FUNCTIONS */

function CalcDateLimits()
##################################################################################
#   written: 10-31-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $UpperLimit, $UpperLimitFmt, $LowerLimit, $LowerLimitFmt,$UpperLimit18,$LowerLimit18;
   
    $TodaysDate  = date("Y-m-d");
    $TodaysMonth = substr($TodaysDate,5,2);
    $TodaysDay   = substr($TodaysDate,8,2);
    $TodaysYear  = substr($TodaysDate,0,4);

    $UpperLimit    = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear)); 
    $UpperLimit18  = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));     // 4-13-11: -mlr
    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));
    $UpperMonth    = substr($UpperLimit,5,2);
    $UpperDay      = substr($UpperLimit,8,2);
    $UpperYear     = substr($UpperLimit,0,4); 

    $LowerLimit    = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear));
    $LowerLimit18  = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear));        // 4-13-11: -mlr
    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
}

function PeriodAndPantryForm()
##################################################################################
#   written: 3-30-10                                                             #
#                                                                                #
##################################################################################
{
global $conn,  $themeId, $pantryID, $InNumVisits, $InStartDate, $InEndDate, $UpperLimitFmt, $LowerLimitFmt, 
       $UpperLimit, $LowerLimit, $RangeType,$InPantry,$UpperLimit18,$LowerLimit18,
       $UpperLimitFmt18,$LowerLimitFmt18,$JACSUpperLimit,$JACSLowerLimit,$DateRangeOK;

    $DateRangeOK=1;
    $UpperLimitFmt18 = YYYYMMDDToEng($UpperLimit18);           // 4-13-11: -mlr
    $LowerLimitFmt18 = YYYYMMDDToEng($LowerLimit18);

    if (isset($_POST['CompileResults'])) 
    {
        $InPantry  = $_POST['InPantry'];
        $RangeType = $_POST['RangeType'];


        if ($RangeType == 'Custom')
        {
            $JACSUpperLimit = ($_POST['InUpperLimit']);                 // 7-7-11:  -mlr
            $JACSLowerLimit = ($_POST['InLowerLimit']);                 // 7-7-11:  -mlr
            $UpperLimit  = MMDDYYYToMySQL($_POST['InUpperLimit']);      // 7-7-11:  -mlr
            $LowerLimit  = MMDDYYYToMySQL($_POST['InLowerLimit']);      // 7-7-11:  -mlr

            if (!$_POST['InUpperLimit'])                          // Logic for single dates
                $UpperLimit = $LowerLimit;

            $UpperLimitFmt = YYYYMMDDToEng($UpperLimit);
            $LowerLimitFmt = YYYYMMDDToEng($LowerLimit);
        }
        else
        {
            $_POST['InUpperLimit'] = $UpperLimit18;           // 4-13-11: -mlr  
            $_POST['InLowerLimit'] = $LowerLimit18;
        }                                                         
    }                                                             
    else                                                          
    {
        $_POST['InUpperLimit']= $UpperLimit;
        $_POST['InLowerLimit']= $LowerLimit; 
        $RangeType = "Standard";
        $InPantry  = "All";
    }
?>
    <p>
    <CENTER>

    <FORM NAME   =DateRangeForm
          METHOD =POST
          ACTION ="<?php echo $_SERVER['PHP_SELF']; ?>">

    <TABLE border=1 class="reportForm">

    <TR>
    <TD class="leftjust"><b>Period:</b>  
    <TD style="text-align:left;"><?php GetPeriod(); ?>                 

    <INPUT TYPE  = HIDDEN
           NAME  = HideLowerLimit
           SIZE  = 8 
           VALUE = "<?php echo $_POST['InLowerLimit']; ?>">

    <INPUT TYPE  = HIDDEN
           NAME  = HideUpperLimit
           SIZE  = 8 
           VALUE = "<?php echo $_POST['InUpperLimit']; ?>">
		   
    <INPUT TYPE  = HIDDEN
           NAME  = themeId
           VALUE = "<?php echo $themeId; ?>">			   

    <TD class="leftjust"><b>Pantry:</b> 

    <TD class="leftjust"><?php selectReportPantry( "InPantry", $InPantry ); ?>   


    <TD class="leftjust">
    <INPUT TYPE = SUBMIT
           NAME = CompileResults
          VALUE = "Compile Results"> </TD>

    </TR>
    </TABLE>
    </FORM> 
<?php
// 7-15-11: check for valid date range.  -mlr
// 2-6-12 - version 2.9 upgrade: MySQL date 'YYYY-MM-DD' now used to verify the upper and lower date ranges. -mlr

    if ($RangeType == 'Custom' && $UpperLimit !='' && $LowerLimit > $UpperLimit) {
        $DateRangeOK = 0;
        echo "<p style='color:red;font-weight:bold;'>! error - start date must occurr before end date.";
    }
?> 
    </CENTER>

<?php
}

function CrawlHouseholdTable()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#                                                                                #
##################################################################################
{
//line328
global $conn,  $LowerLimit,$UpperLimit,$row,$FirstRow,$CurrRegMonth,$NumRegistered;

    $FirstRow=1;
    $NumRegistered=0;

    if ($_POST['InPantry'] == "All")

        $sql = "SELECT *
                FROM household
                WHERE regdate >= '$LowerLimit'
                AND regdate <= '$UpperLimit'
                AND regdate > '0000-00-00'
                AND id > 0
                ORDER BY regdate";
    else
    {
        $pantry_id = $_POST['InPantry'];
        $sql = "SELECT *
                FROM household
                WHERE regdate >= '$LowerLimit'
                AND regdate <= '$UpperLimit'
                AND regdate > '0000-00-00'
                AND id > 0
                AND pantry_id = $pantry_id
                ORDER BY regdate";
    }

    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
        if ($FirstRow) {
            $CurrRegMonth = substr($row['regdate'],0,7);
            $FirstRow = 0;
            $NumRegistered=1;
        }
        elseif (substr($row['regdate'],0,7) > $CurrRegMonth) {
            PrintRow();
            $CurrRegMonth = substr($row['regdate'],0,7);
            $NumRegistered=1;
        }
        else $NumRegistered++;
    }

    if (!$FirstRow)                     // count last visit
        PrintRow();


}

function TableHeadings()
##################################################################################
#   written: 11-3-10   -mlr                                                      #
#                                                                                #
##################################################################################
{
?>
    <TABLE border=1 class="ReportBox;" style="margin:15px;">
    <TR>
    <TH class="HouseHead">Month
    <TH class="HouseHead">Registered
    </TR>

<?php
}

function PrintRow()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn,  $CurrRegMonth,$NumRegistered,$TotRegistered;

    $RegYear = substr($CurrRegMonth,0,4);
    $RegMonth = substr($CurrRegMonth,5,2);
    $FmtMonth= date('F Y', mktime(0,0,0,$RegMonth,1,$RegYear)); 
?>
    <TR>
    <TD class="leftjust"><?php echo $FmtMonth; ?>
    <TD class="rightjust"><?php echo number_format($NumRegistered); ?>
<?php
    $TotRegistered += $NumRegistered;
}



function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $TotRegistered, $LowerLimit, $UpperLimit, $LowerLimitFmt, $UpperLimitFmt;

// first, print the registration total and end table
    echo "<tr><th class='rightjust'>total</th>";
    echo "<td class='rightjust'>" . number_format( $TotRegistered ) . "</td></tr></table>\n";
	
// get values for Summay

	if ( (!$_POST['InUpperLimit']) || $LowerLimit == $UpperLimit )
		$reportPeriod = $LowerLimitFmt;
	else
		$reportPeriod = $LowerLimitFmt." thru ".$UpperLimitFmt;
	
	$foundInactive = 0;		
	if ($_POST['InPantry'] == "All")
        $pantryName = "All";	
    else {
        $pantry_id = $_POST['InPantry'];
        $sql = "SELECT * FROM pantries WHERE pantryID = $pantry_id";
        $result = mysqli_query( $conn, $sql );
        if ($row = mysqli_fetch_assoc($result)) {
		
// 1-14-13: version 3.4 upgrade - add footnote character for inactive pantries		
			$inactiveFoot = "";			
			if ( !$row['is_active'] ) {
				$foundInactive=1;			
				$inactiveFoot = DAGGER_FOOTNOTE;
			}	
            $pantryName = "$row[name] $inactiveFoot";			
        } else
            $pantryName = "{ name not found }";
    } 	
	
	$sql = "SELECT count(*) FROM household";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result))
		$houseRowsTotal = $row['count(*)'];
			
// print Summary 			

    echo "<table border='1' class='reportSummary'>\n";	
    echo "<tr><th colspan='2'>Summary</th></tr>\n";
    echo "<tr><td id='col1'>pantry</td>\n"; 
    echo "<td id='col3' style='text-align:center;'>$pantryName</td></tr>\n";
    echo "<tr><td id='col1'>report period</td>\n"; 
    echo "<td id='col3'>$reportPeriod</td></tr>\n";	
    echo "<tr><td id='col1'>new households registered</td>\n";
    echo "<td id='col3'>" . number_format( $TotRegistered ) . "</td></tr>\n";	
    echo "<tr><td id='col1'>total row(s) in 'household' table</td>\n";  
    echo "<td id='col3'>" . number_format( $houseRowsTotal ) . "</td></tr>\n";
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

</body>
</html>   
