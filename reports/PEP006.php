<?php 
/**
 * PEP006.php - Household Number of Visits by Month
 * copied from \PEP2\PEPReports on 7-23-12
 *
 * 1-10-2019: deprecated in version 3.7, use PEP020 in Charts and Graphs instead.		-mlr
 * 
 * 4-26-14: version 3.5.1 update - call function setGlobals() to initialize pantryID.	-mlr
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 *
 * 1-7-13: version 3.4 upgrade 
 *		- replaced all MYSQL functions with MYSQLI extension.
 *		- Add function PeriodAndPantryForm() 
 *		- Add a footnote for all inactive pantries (Salvation Army)
 *
 * 11-06-12: version 3.3 updates 
 * 		- visit counter now detects multiple visits in 1 day
 *		- remove filter for duplicate households - already removed in Tools->Advanced tab.	-mlr 
 *
 * 10-10-12: version 3.1 updates - Add code for new variable '$themeId'.   -mlr
 * 
 *
 */
$isPublic = 0;	//Reports folder (for signed-in Pepbase users)
// $isPublic = 1;	//Public folder (for "Demographics and Statistics" public web page; no password security)	

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

    $ValidData=0;
    $TodaysDate = date('M j, Y');

// 4-26-14: version 3.5.1 update - call setGlobals() to initialize pantryID	
	setGlobals();	
	
	defineThemeConstants();					// defined in Themes.php	
	
/* XHTML HEADER */

    doHeader("PEP006 - Household Number of Visits by Month", "isReport");	

######################################################################
######################################################################
#                      M A I N L I N E                               #
######################################################################

    CalcDateLimits();
	
	echo "<center>";
	echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Household Number of Visits by Month - PEP006</h3>\n";
    echo "<p style='margin:10px;font-size:10pt;'>";
    echo "<i>Study based on households from PEPbase 'consumption' table.</i></p>\n"; 	
	
    PeriodAndPantryForm();	
	
    if ( isset($_POST['CompileResults']) && $DateRangeOK ) {
	    BuildWorkBench();
		PrintTable();
		PrintFootNote();
    }
	mysqli_close( $conn );	
}	




######################################################################
######################################################################
#                    F U N C T I O N S                               #
######################################################################

//function CalcDateLimits()
//##################################################################################
//#   written: 10-31-10                                                            #
//#                                                                                #
//##################################################################################
//{
//global $conn,  $UpperLimit, $UpperLimitFmt, $LowerLimit, $LowerLimitFmt, $UpperActive, $LowerActive;
   
//    $TodaysDate  = date("Y-m-d");
//    $TodaysMonth = substr($TodaysDate,5,2);
//    $TodaysDay   = substr($TodaysDate,8,2);
//    $TodaysYear  = substr($TodaysDate,0,4);

//    $UpperActive   = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear)); 
//    $UpperLimit    = $UpperActive;
//    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));

//    $UpperMonth    = substr($UpperActive,5,2);
//    $UpperDay      = substr($UpperActive,8,2);
//    $UpperYear     = substr($UpperActive,0,4); 

//    $LowerActive   = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
//    $LowerLimit = $LowerActive;
//    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
 
//}

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
# 7-6-11: Add global $conn,  vars $JACSUpperLimit,$JACSLowerLimit       -mlr             #
#                                                                                #
##################################################################################
{
global $conn, $themeId, $pantryID, $InNumVisits, $InStartDate, $InEndDate, $UpperLimitFmt, $LowerLimitFmt, 
       $UpperLimit, $LowerLimit, $RangeType, $InPantry, $UpperLimit18, $LowerLimit18,
       $UpperLimitFmt18, $LowerLimitFmt18, $JACSUpperLimit, $JACSLowerLimit, $DateRangeOK, $hostPantryId;

    $DateRangeOK=1;
    $UpperLimitFmt18 = YYYYMMDDToEng($UpperLimit18);           // 4-13-11: -mlr
    $LowerLimitFmt18 = YYYYMMDDToEng($LowerLimit18);
	
    if (isset($_POST['CompileResults'])) 
    {
        $InPantry  = $_POST['InPantry'];
        $RangeType = $_POST['RangeType'];

        if ($RangeType == 'Custom')
        {
            $JACSUpperLimit = ($_POST['InUpperLimit']);                 // 7-6-11:  -mlr
            $JACSLowerLimit = ($_POST['InLowerLimit']);                 // 7-6-11:  -mlr
            $UpperLimit  = MMDDYYYToMySQL($_POST['InUpperLimit']);      // 7-6-11:  -mlr
            $LowerLimit  = MMDDYYYToMySQL($_POST['InLowerLimit']);      // 7-6-11:  -mlr

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

    <TD class="leftjust"><?php selectReportPantry( "InPantry", $InPantry );  ?>   


    <TD class="leftjust">
    <INPUT TYPE = 'SUBMIT' NAME = 'CompileResults' VALUE = 'Compile Results'> </TD>

    </TR>
    </TABLE>
    </FORM> 
<?php
// 7-15-11: check for valid date range.  -mlr

// 2-6-12 - version 2.9 upgrade: MySQL date 'YYYY-MM-DD' now used to verify the upper and lower date ranges

    if ($RangeType == 'Custom' && $UpperLimit !='' && $LowerLimit > $UpperLimit) {
        $DateRangeOK = 0;
        echo "<p style='color:red;font-weight:bold;'>! error - start date must occurr before end date.";
    }
?> 
    </CENTER>

<?php
}


function BuildWorkBench()
##################################################################################
#   written: 11-8-10      -mlr                                                   #
#                                                                                #
##################################################################################
{
global $conn, $LowerLimit, $UpperLimit, $household_id, $CurrYearMonth, $NumVisits, $ValidData;

    $NumHouseholds=0;
    $NumVisits =0;
    $HHVisitsPerMonth=0;
    $FirstRow=1;

    $sql = "DELETE FROM work_bench";                             // delete all rows, but table remains                 
    $DeleteOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));

#    $sql = "SELECT consumption.date, consumption.household_id, household.id
#            FROM consumption
#            JOIN household
#            ON consumption.household_id = household.id
#            WHERE consumption.date >= '$LowerLimit'
#            AND consumption.date <= '$UpperLimit'
#            AND household.id > 0
#            AND household.streetname NOT LIKE 'duplicate%'
#            AND consumption.household_id > 0
#            ORDER BY consumption.household_id, consumption.date";


// version 3.4 patch - added period/pantry form, so filter selected pantry 
    if ($_POST['InPantry'] == "All")

		$sql = "SELECT * FROM consumption
				WHERE date >= '$LowerLimit'
				AND date <= '$UpperLimit'
				AND household_id > 0
				ORDER BY household_id, date, time";
	else
	
		$sql = "SELECT * FROM consumption
				WHERE date >= '$LowerLimit'
				AND date <= '$UpperLimit'
				AND household_id > 0
                AND pantry_id = $_POST[InPantry]				
				ORDER BY household_id, date, time";
				
// 1-10-2019: version 3.7 update - use unbuffered queries for large data sets.		-mlr
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));	
//	$result = mysqli_query($conn, $sql, MYSQLI_USE_RESULT) or die(mysqli_error( $conn ));	
	
    while ($row = mysqli_fetch_assoc($result))
    {
        $ConsumeHouseID = $row['household_id'];

        $sql2 = "SELECT * FROM household
                 WHERE id = $ConsumeHouseID";
				 
// 11-06-12: version 3.3 updates - remove filter for duplicate households - already removed in Tools->Advanced tab 				 
//                 AND streetname NOT LIKE 'duplicate%'";

        $result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));

        if ($row2 = mysqli_fetch_assoc($result2))
        {
        $ValidData++;

        $NewYearMonth = substr($row['date'],0,7);

        if ($FirstRow)
        {
            $CurrYearMonth = $NewYearMonth;
            $household_id = $row['household_id'];
            $currDate = $row['date'];
			$currTime = $row['time'];		
            $NumVisits++;
            $NumHouseholds++;
            $FirstRow=0;
        }

        if ( ($CurrYearMonth < $NewYearMonth) || ($household_id < $row['household_id']) ) // if new month
        {
            InsertWorkRow();

            $NumVisits=0;
            $CurrYearMonth = $NewYearMonth;
        }

        if ($household_id < $row['household_id'])          // if new household
        {    
            $NumHouseholds++;
            $NumVisits=1;
            $household_id = $row['household_id'];
            $currDate = $row['date'];
			$currTime = $row['time'];			
            $CurrYearMonth = $NewYearMonth;
        }

        if ( $currDate < $row['date'] || $currTime != $row['time'] ) 	// if new visit
        {   
            $NumVisits++;
            $currDate = $row['date'];
			$currTime = $row['time'];			
        }

        }
            
    } // end loop

    if ($ValidData)
        InsertWorkRow();

}

function InsertWorkRow()
##################################################################################
#   written: 11-8-10      -mlr                                                   #
#                                                                                #
##################################################################################
{
global $conn,  $household_id, $CurrYearMonth, $NumVisits;

    $sql = "INSERT INTO work_bench                                        

        (WB_hh_id,
         WB_year_month,
         WB_num_visits) 

         VALUES 
        ('$household_id', 
         '$CurrYearMonth',
         '$NumVisits')";

     $InsertOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));

}

function PrintTable()
##################################################################################
#   written: 11-8-10      -mlr                                                   #
#                                                                                #
##################################################################################
{
global $conn;

	echo "<table border='1' class='reportDetail'>\n";
?>
    <TR>
    <TH class="HouseHead">Month
    <TH class="HouseHead">Visits
    <TH class="HouseHead">Households
    <TH class="HouseHead">Min Visits/HH
    <TH class="HouseHead">Max Visits/HH
    <TH class="HouseHead">Ave Visit/HH
    </TR> 
<?php

    $FirstRow=1;
    $HHPerMonth=0;

    $TotHHPerMonth=0;
    $TotNumVisits=0;
    $TotMaxVisits=0;
    $TotMinVisits=0;
    $MaxVisits=0;
    $MinVisits=0;

    $sql = "SELECT * FROM work_bench ORDER BY WB_year_month, WB_hh_id";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
        if ($FirstRow)
        {
			$MaxVisits=1;
			$MinVisits=9;
			$TotMaxVisits=1;
			$TotMinVisits=9;			
            $YearMonth = $row['WB_year_month'];
            $NumVisits=0;
            $FirstRow=0;
        }

        if ($YearMonth < $row['WB_year_month'])     // new month
        {
            $TableDate  = $YearMonth.'01';
            $TableMonth = substr($TableDate,5,2);
            $TableDay   = substr($TableDate,8,2);
            $TableYear  = substr($TableDate,0,4);
            $MonthFmt   = date('M Y', mktime(0, 0, 0, $TableMonth,  $TableDay, $TableYear));
?>
            <TR>
            <TD style='text-align:left;'><?php echo $MonthFmt; ?>
            <TD class="rightjust"><?php echo number_format($NumVisits); ?>
            <TD class="rightjust"><?php echo number_format($HHPerMonth); ?>
            <TD class="rightjust"><?php echo number_format($MinVisits); ?>
            <TD class="rightjust"><?php echo number_format($MaxVisits); ?>
            <TD class="rightjust"><?php echo number_format($RndAveVisits,2); ?>
            </TR> 
<?php
            $NumVisits=0;
            $HHPerMonth=0;
            $MaxVisits=1;
            $MinVisits=9;
            $YearMonth = $row['WB_year_month'];
        }


        if ($row['WB_num_visits'] < $MinVisits)
        {
            $MinVisits=$row['WB_num_visits'];
        }
        if ($row['WB_num_visits'] > $MaxVisits)
        {
            $MaxVisits=$row['WB_num_visits'];
        } 

        if ($row['WB_num_visits'] < $TotMinVisits)
        {
            $TotMinVisits=$row['WB_num_visits'];
        }
        if ($row['WB_num_visits'] > $TotMaxVisits)
        {
            $TotMaxVisits=$row['WB_num_visits'];
        }

        $HHPerMonth++;
        $NumVisits=$NumVisits + $row['WB_num_visits'];

        $TotHHPerMonth++;
        $TotNumVisits=$TotNumVisits+ $row['WB_num_visits'];

        $AveVisits= $NumVisits / $HHPerMonth;
        $RndAveVisits = round($NumVisits * 100 / $HHPerMonth) / 100;

    } // end loop
	
	$TotAve=0.0;
	if ( $TotHHPerMonth > 0 )	
		$TotAve= round($TotNumVisits * 100 / $TotHHPerMonth) / 100;
		
// print last month			
	if ( !$FirstRow ) {	
		$TableDate  = $YearMonth.'01';
		$TableMonth = substr($TableDate,5,2);
		$TableDay   = substr($TableDate,8,2);
		$TableYear  = substr($TableDate,0,4);
		$MonthFmt   = date('M Y', mktime(0, 0, 0, $TableMonth,  $TableDay, $TableYear));

		echo "<tr><td style='text-align:left;'>$MonthFmt</td>\n";
		echo "<td>" . number_format( $NumVisits ) . "</td>\n"; 
		echo "<td>" . number_format( $HHPerMonth ) . "</td>\n";
		echo "<td>" . number_format( $MinVisits ) . "</td>\n"; 
		echo "<td>" . number_format( $MaxVisits ) . "</td>\n";
		echo "<td>" . number_format( $RndAveVisits, 2 ) . "</td></tr>\n";
	} else {
	
		$MaxVisits=0;
		$MinVisits=0;	
	}	
	
?>
    <TR style='font-weight:bold;'>
    <td>totals</td>
    <td><?php echo number_format($TotNumVisits); ?></td>
    <td><?php echo number_format($TotHHPerMonth); ?></td>
    <td><?php echo number_format($TotMinVisits); ?></td>
    <td><?php echo number_format($TotMaxVisits); ?></td>
    <td><?php echo number_format($TotAve,2); ?></td>
    </tr>

    </table>
<p>
<?php



}   // end function

function PrintFootNote()
##################################################################################
#   written: 11-8-10         -mlr                                                #
#                                                                                #
##################################################################################
{
	global $conn, $LowerLimit, $UpperLimit, $LowerActive, $UpperActive, $ValidData, 
	$LowerLimitFmt, $UpperLimitFmt; 

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
			$inactiveFoot = "";			
			if ( !$row['is_active'] ) {
				$foundInactive=1;			
				$inactiveFoot = DAGGER_FOOTNOTE;
			}	
            $pantryName = "$row[name] $inactiveFoot";			
        } else
            $pantryName = "{ name not found }";
    } 		

	$sql = "SELECT COUNT(*) FROM consumption";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	if ($row = mysqli_fetch_assoc($result))
		$TotalConsumption=$row['COUNT(*)'];

    echo "<table border='1' class='reportSummary'>\n";	
    echo "<tr><th colspan='2'>Summary</th></tr>\n";
    echo "<tr><td id='col1'>pantry</td>\n"; 
    echo "<td id='col3' style='text-align:center;'>$pantryName</td></tr>\n";
    echo "<tr><td id='col1'>report period</td>\n"; 
    echo "<td id='col3'>$reportPeriod</td></tr>\n";	
    echo "<tr><td id='col1'>'consumption' table row(s) used in study</td>\n";
    echo "<td id='col3'>" . number_format( $ValidData ) . "</td></tr>\n";
    echo "<tr><td id='col1'>total row(s) in 'consumption' table</td>\n";  
    echo "<td id='col3'>" . number_format( $TotalConsumption ) . "</td></tr>\n";
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
