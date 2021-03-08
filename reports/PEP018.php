<?php 
/**
 * PEP018.php - Household Average Number of Visits by Zip Code
 * copied from \PEP2\PEPReports and modified on 8-1-12
 * 
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 *
 * 11-19-13: version 3.4.8 patch - In function InsertSortRow(), MySQL now protected from non-numeric zip codes
 *		 in household table.    -mlr
 *
 * 1-16-13: version 3.4 upgrade 
 *		- replaced all MYSQL functions with MYSQLI extension. 
 *		- Make MYSQLI link variable '$conn' global to all functions.
 * 		- add footnote for inactive pantries  
 *		- new css for Summary 
 *
 * 11-07-12: version 3.3 updates 
 *		- set $pantryID as global to entire system.   
 * 		- visit counter now detects multiple visits in 1 day
 *		- remove filter for duplicate households - already removed in Tools->Advanced tab.
 *
 * 10-10-12: version 3.1 updates - Add code for new variable '$themeId'.   -mlr
 *
 */
$isPublic=0; // Reports folder (for signed-in Pepbase users)
//$isPublic = 1;	//Public folder (for "Demographics and Statistics" public web page; no password security)
 
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
	
    if (isset($_GET['Field'])) {
        $Field=$_GET['Field'];
        $Order=$_GET['Order'];
    } else {
        $Field="ZipCode";
        $Order="asc";
    }

    $Max=0;
    $Min=0;
    $consumptionUsed=0;
    $consumptionNoHH=0;	
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

    doHeader("PEP018 - Household Average Number of Visits by Zip Code", "isReport");	

/* MAINLINE */	

    CalcDateLimits();
	
	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Household Average Number of Visits by Zip Code - PEP018</h3>\n";
    echo "<p style='margin:10px;font-size:10pt;'>\n";
    echo "<i>Study based on PEPbase 'household' and 'consumption' tables.</i></p>\n"; 	

    PeriodAndPantryForm();                        

    if (isset($_POST['CompileResults']) && $DateRangeOK) {

        FindVisitsPerHH();
        if(is_array($ReportWork) && count($ReportWork)>0)
            FillSortTable();
    }

    if ( (isset($_POST['CompileResults']) && $DateRangeOK) || (isset($_GET['Field'])) ) {

           PrintTable();
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
    $UpperLimit18  = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));     // 5-17-11: -mlr
    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));
    $UpperMonth    = substr($UpperLimit,5,2);
    $UpperDay      = substr($UpperLimit,8,2);
    $UpperYear     = substr($UpperLimit,0,4); 

    $LowerLimit    = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
    $LowerLimit18  = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear));        // 5-17-11: -mlr
    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
}

function PeriodAndPantryForm()
##################################################################################
#   written: 5-17-11    -mlr                                                     #
#                                                                                #
##################################################################################
{
global $conn,  $themeId, $pantryID, $InNumVisits, $InStartDate, $InEndDate, $UpperLimitFmt, $LowerLimitFmt, 
       $UpperLimit, $LowerLimit, $RangeType,$InPantry,$UpperLimit18,$LowerLimit18,
       $UpperLimitFmt18,$LowerLimitFmt18,$JACSUpperLimit,$JACSLowerLimit,$DateRangeOK;

    $DateRangeOK=1;                                            // 7-15-11: -mlr
    $UpperLimitFmt18 = YYYYMMDDToEng($UpperLimit18);           // 4-13-11: -mlr
    $LowerLimitFmt18 = YYYYMMDDToEng($LowerLimit18);

    if (isset($_POST['CompileResults'])) {
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
                                                          
    elseif (isset($_GET['Field'])) {
        $InPantry  = $_GET['InPantry'];
        $RangeType = $_GET['RangeType'];

        if ($RangeType == 'Custom') {
            $JACSUpperLimit = ($_GET['InUpperLimit']);                 // 7-7-11:  -mlr
            $JACSLowerLimit = ($_GET['InLowerLimit']);                 // 7-7-11:  -mlr
            $UpperLimit  = MMDDYYYToMySQL($_GET['InUpperLimit']);      // 7-7-11:  -mlr
            $LowerLimit  = MMDDYYYToMySQL($_GET['InLowerLimit']);      // 7-7-11:  -mlr

            if (!$_GET['InUpperLimit'])                          // Logic for single dates
                $UpperLimit = $LowerLimit;

            $UpperLimitFmt = YYYYMMDDToEng($UpperLimit);
            $LowerLimitFmt = YYYYMMDDToEng($LowerLimit);
        }
        else {
            $_POST['InUpperLimit'] = $UpperLimit18;           // 4-13-11: -mlr  
            $_POST['InLowerLimit'] = $LowerLimit18;
        } 

    } else {
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

    <TD class="leftjust"><b>Pantry:</b> <?php selectReportPantry( "InPantry", $InPantry ); ?> 

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

function FindVisitsPerHH()
##################################################################################
#   written: 3-30-10                                                             #
#                                                                                #
##################################################################################
{
//line 365
global $conn,  $CurrHousehold_id, $CurrZip_five, $CurrPantry_id, $NumVisits, $UpperLimit, 
       $LowerLimit, $consumptionUsed, $consumptionNoHH, $NewReg,$ReportWork,$Index;

    $FirstRow = 1;
    $Index=0;
    $ReportWork=array();

    $sql = "DELETE FROM sort_018"; 
    $DeleteOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	
    if ( ( isset($_POST['InPantry']) && $_POST['InPantry'] == "All" ) || ( isset($_GET['InPantry']) && $_GET['InPantry'] == "All" ) )
//    if ($_POST['InPantry'] == "All" || $_GET['InPantry'] == "All")

        $sql = "SELECT * FROM consumption
                WHERE date >= '$LowerLimit'
                AND date <= '$UpperLimit'
                AND household_id > 0
                ORDER BY household_id, date, time";
    else {
        if (isset($_GET['InPantry']))
            $pantry_id = $_GET['InPantry'];
        else
            $pantry_id = $_POST['InPantry'];
        $sql = "SELECT * FROM consumption
                WHERE date >= '$LowerLimit'
                AND date <= '$UpperLimit'
                AND household_id > 0
                AND pantry_id = $pantry_id
                ORDER BY household_id, date, time";
    }

//    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    $result = mysqli_query( $conn, $sql ) or die('ERROR SQL type mismatch in FindVisitsPerHH()');	
    while ($row = mysqli_fetch_assoc($result))
    {
        $sql2 = "SELECT * FROM household WHERE id = $row[household_id]";
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		if ($row2 = mysqli_fetch_assoc($result2)) {
			$consumptionUsed++;
			if ( $FirstRow ) {
				$currDate = $row['date'];
				$currTime = $row['time'];
				$currBreakPantry  = $row['pantry_id'];	
				$CurrHousehold_id = $row['household_id'];
				$CurrZip_five     = $row2['zip_five'];
				$CurrPantry_id    = $row2['pantry_id'];
				if ($row2['regdate'] >= $LowerLimit && $row2['regdate'] <= $UpperLimit)
					$NewReg = 1;
				else
					$NewReg = 0;
				$FirstRow = 0;
				$NumVisits=1;
			} else {
				if ( $row['household_id'] > $CurrHousehold_id ) {
					PushWorkArray();

					$currDate = $row['date'];
					$currTime = $row['time'];
					$currBreakPantry  = $row['pantry_id'];					
					$CurrHousehold_id = $row['household_id'];
					$CurrZip_five     = $row2['zip_five'];
					$CurrPantry_id    = $row2['pantry_id'];
					if ($row2['regdate'] >= $LowerLimit && $row2['regdate'] <= $UpperLimit)
						$NewReg = 1;
					else
						$NewReg = 0;
					$NumVisits=1;
				} else {
				
	// 11-07-12: version 3.3 updates - break on both date and time				
					if ( $row['date'] > $currDate || $row['time'] != $currTime || $row['pantry_id'] != $currBreakPantry ) {
						$NumVisits++;
						$currDate = $row['date'];
						$currTime = $row['time'];
						$currBreakPantry = $row['pantry_id'];						
					} 
				}
			}
		} else
			$consumptionNoHH++;
    }
	
    if (!$FirstRow)                     // ignore empty query results
        PushWorkArray();
}

function PushWorkArray()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn,  $CurrHousehold_id, $CurrZip_five, $CurrPantry_id, $NumVisits, $NewReg, 
       $ReportWork,$Index;

	   
	   
	   
    $ReportWork[$Index]['household_id']=$CurrHousehold_id;
    $ReportWork[$Index]['num_visits']=$NumVisits;
    $ReportWork[$Index]['new_registration']=$NewReg;
    $ReportWork[$Index]['zip_five']=$CurrZip_five; 
    $ReportWork[$Index]['pantry_id']=$CurrPantry_id; 
    $Index++;
}


function PrintZipTable()
##################################################################################
#   written: 9-5-11        -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn, $TotHouseholds, $TotVisits;

    $sql = "SELECT RW_zip_five, sum(RW_new_registration), sum(RW_num_visits)
            FROM report_work
            GROUP BY RW_zip_five";

//    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    $result = mysqli_query( $conn, $sql ) or die('ERROR SQL type mismatch in PrintZipTable()');
    while ($row = mysqli_fetch_assoc($result)) {
        PrintRow($row['RW_zip_five'],$row['sum(RW_new_registration)'], $row['sum(RW_num_visits)']);
    }
}

function FillSortTable()
##################################################################################
#   written: 9-5-11        -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn,  $TotHouseholds,$TotVisits,$ReportWork;

    $FirstRow=1;
    $SumNewRegs=0;
    $SumNewVisits=0;

    $SortWork = sortmulti ($ReportWork, 'zip_five', 'asc');
    foreach ($SortWork as $row) { 
	
############ BEGIN DE-BUG ###########
//if ($row['zip_five'] < '11111')
//	echo "zip code=$row[zip_five] num visits=$row[num_visits]";
############# END DE-BUG ############		

        if ($FirstRow) {
            $CurrZip = $row['zip_five'];
            $SumNewRegs=$row['new_registration'];
            $SumNewVisits=$row['num_visits'];
            $FirstRow = 0;
        } elseif ($row['zip_five'] > $CurrZip) {

            InsertSortRow($CurrZip,$SumNewVisits);

            $CurrZip = $row['zip_five'];
            $SumNewRegs=$row['new_registration'];
            $SumNewVisits=$row['num_visits'];

        } else {
            $SumNewRegs+=$row['new_registration'];
            $SumNewVisits+=$row['num_visits'];
        }
    }    

    if (!$FirstRow)                     // ignore empty query results
        InsertSortRow($CurrZip,$SumNewVisits);
}


function InsertSortRow($ZipCode,$NumVisits)
##################################################################################
#   written: 9-5-11                                                              #
#                                                                                #
##################################################################################
{
global $conn;

    $Households = 0;
	
// 11-19-2013: version 3.4.8 patch - logic now checks for non-numeric data in zip code field	-mlr 	
    if (is_numeric($ZipCode)) {
//	if $ZipCode != NULL) {
		if ( ( isset($_POST['InPantry']) && $_POST['InPantry'] == "All" ) || ( isset($_GET['InPantry']) && $_GET['InPantry'] == "All" ) )
//        if ($_POST['InPantry'] == "All" || $_GET['InPantry'] == "All")
            $sql = "SELECT count(id) FROM household
                    WHERE id > 0
                    AND zip_five = $ZipCode";
//                    AND streetname NOT LIKE 'duplicate%'";

        else {
            if (isset($_GET['InPantry']))
                $pantry_id = $_GET['InPantry'];
            else
                $pantry_id = $_POST['InPantry'];
            $sql = "SELECT count(id) FROM household
                    WHERE id > 0
                    AND zip_five = $ZipCode
                    AND pantry_id = $pantry_id";
//                    AND streetname NOT LIKE 'duplicate%'";
        } 
//        $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
        $result = mysqli_query( $conn, $sql ) or die('ERROR 1 SQL type mismatch in InsertSortRow()');
        if ($row = mysqli_fetch_assoc($result))
            $Households = $row['count(id)'];
    } else {
        echo "ERROR households table, zip_five=$ZipCode<br>";
		$ZipCode=0;	
	}	

    if ($Households > 0)
        $AveVisits=$NumVisits/$Households;
    else
        $AveVisits=0;
		
    $sql = "INSERT INTO sort_018                                   
                   (S18_zip_code,S18_households,S18_visits,S18_average)
            VALUES ('$ZipCode','$Households','$NumVisits','$AveVisits')";     

//    $InsertOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    $InsertOk = mysqli_query( $conn, $sql ) or die('ERROR 2 SQL type mismatch in InsertSortRow()');	
}

function PrintTable()
##################################################################################
#   written: 9-5-11                                                              #
#                                                                                #
##################################################################################
{
//line625
global $conn, $sql, $Field, $Order, $NewZipOrder,$NewHouseOrder,$NewVisitOrder,$NewAveOrder,
       $TotHouseholds,$TotVisits, $themeId;

    TableHeading();

//    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    $result = mysqli_query( $conn, $sql ) or die('ERROR SQL type mismatch in PrintTable()');	
    while ($row = mysqli_fetch_assoc($result)) 
        PrintRow($row['S18_zip_code'],$row['S18_households'],$row['S18_visits'],$row['S18_average']);
}

function TableHeading()
##################################################################################
#   written: 9-5-11                                                              #
#                                                                                #
##################################################################################
{
global $conn,  $sql,$Field,$Order,$NewZipOrder,$NewHouseOrder,$NewVisitOrder,$NewAveOrder, $themeId;

    NewOrder();

    if (isset($_GET['Field'])) {
        $FormRefer = "&InPantry=".$_GET['InPantry'];
        $FormRefer.= "&RangeType=".$_GET['RangeType'];
        $FormRefer.= "&InUpperLimit=".$_GET['InUpperLimit'];
        $FormRefer.= "&InLowerLimit=".$_GET['InLowerLimit'];   
    } else {    
        $FormRefer = "&InPantry=".$_POST['InPantry'];
        $FormRefer.= "&RangeType=".$_POST['RangeType'];
        $FormRefer.= "&InUpperLimit=".$_POST['InUpperLimit'];
        $FormRefer.= "&InLowerLimit=".$_POST['InLowerLimit']; 
    }
	
// 11-07-12: version 3.3 updates - add global variable $themeId to query string
	$FormRefer.= "&themeId=$themeId"; 
?>
    <table border='1' class="reportDetail" style="margin:15px;">
    <tr>
    <td class="HouseHead"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?Field=ZipCode&Order=<?php echo $NewZipOrder.$FormRefer; ?>">Zip Code</a></td>
    <td class="HouseHead"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?Field=Households&Order=<?php echo $NewHouseOrder.$FormRefer; ?>">Households</a></td>
    <td class="HouseHead"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?Field=Visits&Order=<?php echo $NewVisitOrder.$FormRefer; ?>">Visits</a></td>
    <td class="HouseHead"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?Field=Average&Order=<?php echo $NewAveOrder.$FormRefer; ?>">Visits / Household</a></td>
    </tr>
<?php
}


function NewOrder()
#####################################################
# written: 12-8-09   -mlr                           #
#                                                   #
# Toggles sort between ascending and descending     #
# order.                                            #
#####################################################
{
global $conn,  $sql,$Field,$Order,$NewZipOrder,$NewHouseOrder,$NewVisitOrder,$NewAveOrder;

    if ($Field == "ZipCode")

        if ($Order == "asc") {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_zip_code";
            $NewZipOrder = "desc";
        } else {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_zip_code DESC";
            $NewZipOrder = "asc";
        }

    elseif ($Field == "Households") 

        if ($Order == "asc") {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_households";
            $NewHouseOrder = "desc";
        } else {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_households DESC";
            $NewHouseOrder = "asc";
        }

    elseif ($Field == "Visits") 

        if ($Order == "asc") {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_visits";
            $NewVisitOrder = "desc";
        } else {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_visits DESC";
            $NewVisitOrder = "asc";
        }

    else // #average

        if ($Order == "asc") {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_average";
            $NewAveOrder = "desc";
        } else {
            $sql = "SELECT * FROM sort_018 ORDER BY S18_average DESC";
            $NewAveOrder = "asc";
        }
}

function PrintRow($ZipCode,$Households,$NumVisits,$Average)
##################################################################################
#   written: 9-5-11                                                              #
#                                                                                #
##################################################################################
{
global $conn, $TotHouseholds, $TotVisits;


    echo "<tr><td style='text-align:left;'>"; 	//  top, right, bottom, left
	if ( $ZipCode )
       echo $ZipCode;
	else
       echo "&#160;";	
	echo "<td style='text-align:right;'>";
    if ($Households)
       echo number_format( $Households );
    else
       echo "&#160;";
    echo "<td style='text-align:right;'>";
    if ($NumVisits)
       echo number_format( $NumVisits );
    else
       echo "&#160;";
    echo "<td style='text-align:right;'>";    
    echo number_format( $Average, 1 ) . "</td></tr>\n";

    $TotHouseholds+=$Households;
    $TotVisits+=$NumVisits;
}

function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $LowerLimit, $UpperLimit, $LowerLimitFmt, $UpperLimitFmt, $TotHouseholds, $TotVisits, $StatArr,
	$consumptionUsed, $consumptionNoHH;

    if ($TotHouseholds > 0)
        $AveVisits=$TotVisits/$TotHouseholds;
    else
        $AveVisits=0;

    echo "<tr>";
    echo "<td style='text-align:right; font-weight:bold;'>Totals</td>\n";
    echo "<td style='text-align:right; font-weight:bold;'>" . number_format($TotHouseholds) . "</td>\n";
    echo "<td style='text-align:right; font-weight:bold;'>" . number_format($TotVisits) . "</td>\n";
    echo "<td style='text-align:right; font-weight:bold;'>" . number_format($AveVisits,1) . "</td>\n";
    echo "</tr></table>";

// get values for Summay

	if ( (!$_POST['InUpperLimit']) || $LowerLimit == $UpperLimit )
		$reportPeriod = $LowerLimitFmt;
	else
		$reportPeriod = $LowerLimitFmt." thru ".$UpperLimitFmt;	
	$foundInactive=0;
    if ( ( isset($_POST['InPantry']) && $_POST['InPantry'] == "All" ) || ( isset($_GET['InPantry']) && $_GET['InPantry'] == "All" ) )
        $pantryName = "All";	
    else {
        if (isset($_GET['InPantry']))
            $pantry_id = $_GET['InPantry'];
        else
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

    LoadStatArray(); 
    $Median = calculate_median($StatArr);
    $Mode = calculate_mode($StatArr);

	$sql = "SELECT count(*) FROM household";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result))
		$houseRowsTotal = $row['count(*)'];   
	
// print Summary 			

    echo "<table border='1' class='reportSummary'>\n";	
    echo "<tr><th colspan='3'>Summary</th></tr>\n";
    echo "<tr><td colspan='2' id='col1'>pantry</td>\n"; 
    echo "<td id='col3' style='text-align:center;'>$pantryName</td></tr>\n";
    echo "<tr><td colspan='2' id='col1'>report period</td>\n"; 
    echo "<td id='col3'>$reportPeriod</td></tr>\n";	
    echo "<tr><td colspan='2' id='col1'>total households</td>\n"; 
    echo "<td id='col3'>" . number_format( $TotHouseholds ) . "</td></tr>\n";
    echo "<tr><td colspan='2' id='col1'>total visits</td>\n"; 
    echo "<td id='col3'>" . number_format( $TotVisits ) . "</td></tr>\n";
    echo "<tr><td id='col1'>visits / household &#42;</td>"; 
    echo "<td id='col2'>mean</td>\n";	
    echo "<td id='col3'>" . number_format( $AveVisits, 1 ) . "</td>\n";
    echo "<tr><td> </td><td id='col2'>median</td>\n";	
    echo "<td id='col3'>" . number_format( $Median, 1 ) . "</td></tr>\n";
    echo "<tr><td> </td><td id='col2'>mode</td>\n";	
    echo "<td id='col3'>" . number_format( $Mode, 1 ) . "</td></tr>\n";	
    echo "<tr><td colspan='2' id='col1'>total row(s) in 'household' table</td>\n";	
    echo "<td id='col3' >" . number_format( $houseRowsTotal ) . "</td></tr>\n";	

// 11-07-12: version 3.3 updates - for zip code information, the consumption data has to match a row in the 
//		household table. Need to report when no match is found.	
    echo "<tr><td colspan='2' id='col1'>row(s) used from 'consumption' table</td>\n";	
    echo "<td id='col3' >" . number_format( $consumptionUsed ) . "</td></tr>\n";		
    echo "<tr><td colspan='2' id='col1'>'consumption' table row(s) without a match in 'household' table</td>\n";	
    echo "<td id='col3' >" . number_format( $consumptionNoHH ) . "</td></tr>\n";
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
?>	
	<table style="font-size:10pt;"><tr><td style="vertical-align:top;">&#42;
	<td> <b>mean:</b> The sum of the values divided by the number of values (average).<br>
	<b>median:</b> The numerical value separating the higher half of a sample from the lower half.<br>
    <b>mode:</b> The value that occurs most frequently in a data set.
    </td></tr></table>

<?php
}

function  LoadStatArray() {
##################################################################################
#   written: 10-6-11   -mlr                                                      #
#                                                                                #
##################################################################################
global $conn,  $StatArr;

    $StatArr=array();
    $i=0;
    $sql = "SELECT * FROM sort_018";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
        $StatArr[$i]=$row['S18_average'];
        $i++;
    }
}

?>
</body>
</html>