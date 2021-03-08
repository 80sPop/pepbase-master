<?php 
/**
 * PEP004.php - Product Consumption for Household (x)
 * Written: 3-22-12
 *
 * 1-30-2016 version 3.6.2 update - fix MariaDB bug by replacing all max range date and time comparison operands 
 *		with acceptable values.		-mlr
 *
 * 4-30-14: version 3.5.1 update - remove TIME_ZONE_OFFSET constant.		-mlr
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 *
 * 1-15-13: version 3.4 upgrade
 *		- add footnote for inactive pantries
 *		- instock value now calculated when shopping list is printed, so
 *			we don't need to do it here. 
 *
 * 12-25-12: version 3.3.3 patch 
 *		- Summary moved to top of report
 *		- Household IDs are now masked with "XXX" and generated randomly for the public version 
 *			(Reports & Resources->Demographics--and--Statistics)	-mlr
 *
 * 11-06-12: version 3.3 updates
 *		- visit counter now detects multiple visits in 1 day
 *		- added time to first line of individual visit listing.		-mlr
 *
 * 10-10-12: version 3.1 updates - Add code for new variable '$themeId'.   -mlr 
 *
 */
 
$isPublic = 0;		// Reports folder (for signed-in Pepbase users)
// $isPublic = 1;	// Public folder (for "Demographics and Statistics" public web page; no password security)	
 
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

	if (isset($_GET['field'])) {
        $field = $_GET['field'];
        $order = $_GET['order'];
	} elseif (isset($_POST['field'])) {
        $field = $_POST['field'];
        $order = $_POST['order'];		
    } else {
        $field = "staffName";				// dumb. default $field and $order should be set in appropriate tag file
        $order = "asc";
    }
	
	
	if ( isset($_GET['hhID']) )
		$hhID = $_GET['hhID'];
	elseif ( isset($_POST['hhID']) )		
		$hhID = $_POST['hhID'];
	else
		$hhID = 0;
		
    $quantity_oked=0;
    $quantity_reqd=0;
    $quantity_used=0;
    $quantity_instock=0;       // 4-13-11:   -mlr
    $total_oked=0;
    $total_reqd=0;
    $total_used=0;
    $total_instock=0;	
    $TotVisits=0;

    $TodaysDate = date('M j, Y');
	$foundInactive=0;	
	
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

    doHeader("PEP004 - Product Consumption for Household (x)", "isReport");
	
/* MAINLINE */

    CalcDateLimits();

    echo "<center>";
    echo "<h3 style='margin:10px;font-size:12pt;font-weight:bold;'>Product Consumption for Household (x) - PEP004</h3>\n";
    echo "<p style='margin:10px;font-size:10pt;'><i>\n";
    echo "Study based on households from PEPbase 'consumption' table."; 
	if ( $isPublic ) {
		$link = $_SERVER['PHP_SELF'] . "?themeId=$themeId&generate=1";	
		echo " For security purposes, the household ID will be generated randomly and masked to protect its identity.</i></p>";
		echo "<p style='margin:10px;font-size:10pt;'>";
		echo "<a href='" . $link . "'>Generate Random Household ID</a></p>\n";		
	} else
		echo "</i></p>\n";

    HouseholdIDForm();

    if ( ((isset($_POST['CompileResults'])) && $DateRangeOK) || (isset($_GET['date'])) ) {

// 12-21-12: version 3.3.3 patch: Summary now printed at top of report
		printSummary();
        printDetail();
        PrintFootNote();
    }
	mysqli_close( $conn );
}	
echo "</body>";
echo "</html>";  

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
global $conn,  $UpperLimit, $UpperLimitFmt, $LowerLimit, $LowerLimitFmt,$UpperLimit18,$LowerLimit18;

    $TodaysDate  = date("Y-m-d");
    $TodaysMonth = substr($TodaysDate,5,2);
    $TodaysDay   = substr($TodaysDate,8,2);
    $TodaysYear  = substr($TodaysDate,0,4);

    $UpperLimit    = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear)); 
    $UpperLimit18  = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));     // 7-7-11: -mlr
    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));
    $UpperMonth    = substr($UpperLimit,5,2);
    $UpperDay      = substr($UpperLimit,8,2);
    $UpperYear     = substr($UpperLimit,0,4); 

    $LowerLimit    = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
    $LowerLimit18  = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear));        // 7-7-11: -mlr
    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
}



function HouseholdIDForm()
##################################################################################
#   written: 3-30-10                                                             #
#                                                                                #
##################################################################################
{
global $conn,  $themeId, $InNumVisits, $InStartDate, $InEndDate, $UpperLimitFmt, $LowerLimitFmt, 
       $UpperLimit, $LowerLimit, $RangeType,$UpperLimit18,$LowerLimit18,$UpperLimitFmt18,
       $LowerLimitFmt18, $JACSUpperLimit,$JACSLowerLimit,$DateRangeOK,$InHouseholdID,$isPublic;

    $DateRangeOK=1;                                            // 7-15-11: -mlr
    $UpperLimitFmt18 = YYYYMMDDToEng($UpperLimit18);           // 7-7-11: -mlr
    $LowerLimitFmt18 = YYYYMMDDToEng($LowerLimit18);

    if ( (isset($_POST['CompileResults'])) || (isset($_GET['hhID'])) ) 
    {
        if (isset($_GET['hhID'])) {
            $InHouseholdID = $_GET['hhID'];
            $RangeType = 'Custom';
        } else { 
            $InHouseholdID = $_POST['InHouseholdID'];
            $RangeType = $_POST['RangeType'];
        }

        if ($RangeType == 'Custom')
        {
            if (isset($_GET['date'])) {
                $_POST['InUpperLimit'] = '';                                // 8-22-11:  -mlr
                $_POST['InLowerLimit'] = MySQLToMMDDYYY($_GET['date']);     // 8-22-11:  -mlr
            }

            $JACSUpperLimit = ($_POST['InUpperLimit']);                 // 7-7-11:  -mlr
            $JACSLowerLimit = ($_POST['InLowerLimit']);                 // 7-7-11:  -mlr
            $UpperLimit  = MMDDYYYToMySQL($_POST['InUpperLimit']);      // 7-7-11:  -mlr
            $LowerLimit  = MMDDYYYToMySQL($_POST['InLowerLimit']);      // 7-7-11:  -mlr

            if (!$_POST['InUpperLimit'])                          // Logic for single dates
                $UpperLimit = $LowerLimit;
        }
        else
        {
            $_POST['InUpperLimit'] = $_POST['HideUpperLimit'];    // Here, the $_POST array for 'InUpperLimit'
            $_POST['InLowerLimit'] = $_POST['HideLowerLimit'];    // and 'InLowerLimit' has no value, because they
        }                                                         // were disabled by the '18 months' radio button. 
    }                                                             // So, to keep the old custom date values, they must
    else                                                          // be passed as type=HIDDEN. -mlr
    {
        $_POST['InUpperLimit']= $UpperLimit;
        $_POST['InLowerLimit']= $LowerLimit; 
        $RangeType = "Standard";
		
// 12-24-12: version 3.3.3 patch		
		if ( $isPublic )
			$InHouseholdID = randomHousehold();		
        else
			$InHouseholdID = 0;
    }
?>
    <p>
    <center>

    <form NAME   ="DateRangeForm"
          METHOD ="post"
          ACTION ="<?php echo $_SERVER['PHP_SELF']; ?>">

    <table border=1 class="reportForm">
    <tr>
    <td class="leftjust"><b>Period:</b>    
    <td style="text-align:left;"><?php GetPeriod(); ?>  

    <input type  = HIDDEN
           NAME  = "HideLowerLimit"
           SIZE  = 8 
           VALUE = "<?php echo $_POST['InLowerLimit']; ?>">

    <input type  = HIDDEN
           NAME  = "HideUpperLimit"
           SIZE  = 8 
           VALUE = "<?php echo $_POST['InUpperLimit']; ?>"></td>
		   
    <INPUT TYPE  = HIDDEN
           NAME  = themeId
           VALUE = "<?php echo $themeId; ?>">			   

    <td class="leftjust"><b>Household ID:</b>
<?php
// 12-24-12: version 3.3.3 patch - mask the household ID for public report	

	if ( $isPublic ) {
		echo "<input type='hidden' name='InHouseholdID' value='$InHouseholdID'>";
		$maskedID = "00" . strval( $InHouseholdID );
		$maskedID = "XXX" . substr( $maskedID, strlen($maskedID) - 2, 2 );
		echo "<input type='text' style='width:50px;text-align:left;padding-left:3px;' ";
		echo "id='reportsFormhhID' name='maskedID' value='$maskedID' disabled='disabled'></td>\n";
	} else {	
		echo "<input type='text' style='width:50px;text-align:left;padding-left:3px;' ";
		echo "id='reportsFormhhID' name='InHouseholdID' value= '$InHouseholdID'></td>";
	}
?>	

    <td class="leftjust">
    <input type = SUBMIT
           NAME = "CompileResults"
          VALUE = "Compile Results"> </td>

    </tr>
    </table>

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

/**
 * printSummary()
 * Written: 12-21-12 for version 3.3.3 patch
 *
 */
function printSummary()
{
global $conn,  $LowerLimit, $UpperLimit, $total_oked, $total_reqd, $total_used,$total_instock, $TotVisits,
	$InHouseholdID, $isPublic;

    if ( isset($_POST['InHouseholdID']) )
		$household_id = $_POST['InHouseholdID'];
	elseif ( isset($_GET['hhID']) )
		$household_id = $_GET['hhID'];
	else
		$household_id = 0;
		
	$oked=0; $reqd=0; $used=0; $instock=0;		

// As part of the initial version 3.3 upgrade, for cases where a host might override a household's eligibility, when a 
// shopping list is printed, both eligible and non eligible products are now written to the consumption table. So, for
// the majority of households, more rows are being written than necessary. To shorten the length of the report, the 
// version 3.3.3 patch will omitt non-eligible products from the detailed view.
	
    $sql = "SELECT *
            FROM consumption
            WHERE date >= '$LowerLimit'
            AND date <= '$UpperLimit'
            AND household_id = $household_id
            AND product_id > 0
			AND quantity_oked > 0";                         

    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ( $row = mysqli_fetch_assoc($result) ) {
	
// since instock values are stored in a packed format, tally all non zero occurences	
//		if ($row['instock'] <> NULL && $row['instock'] > 0)
//			$instock += $row['quantity_oked'];	
			
// 1-19-13: version 3.4 upgrade - instock value now calculated when shopping list is printed, so
//		we don't need to do it here. 
			
		$instock += $row['instock'];	
		$oked+=$row['quantity_oked']; 
		$reqd+=$row['quantity_reqd']; 
		$used+=$row['quantity_used']; 
	}	

// avoid php warning for division by zero	
	$perFufill = 0;
	if ( $reqd > 0 )
		$perFufill = 100 * $used / $reqd;
	$perInstock = 0;		
	if ( $oked > 0 )		
		$perInstock = 100 * $instock / $oked;
		
// 12-24-12: version 3.3.3 updates - mask household ID	
	$maskedID = $household_id;
	if ( $isPublic ) {	
		$maskedID = "00" . strval( $household_id );
		$maskedID = "XXX" . substr( $maskedID, strlen($maskedID) - 2, 2 );	
	}	
    echo "<table border='1' class='reportSummary'>\n";
    echo "<tr><th colspan='3'>Summary For Household ID = $maskedID</th></tr>\n";
    echo "<tr><td colspan='2' id='col1'>report period</td><td id='col3'>";
	echo date( 'D m-d-Y', strtotime("$LowerLimit") );
	if ( $LowerLimit < $UpperLimit )
		echo " thru " . date( 'D m-d-Y', strtotime("$UpperLimit") );
	echo "</td></tr>\n";
    echo "<tr><td colspan='2' id='col1'>total visits</td><td id='col3'>" . number_format( countVisits( $LowerLimit, $UpperLimit, $household_id, 0 ) ) . "</td></tr>\n";		
	echo "<tr><td id='col1'>products</td><td id='col2'>eligible for</td><td id='col3'>" . number_format( $oked ) . "</td></tr>\n";
	echo "<tr><td id='col1'>''</td><td id='col2'>in stock &#42;</td><td id='col3'>" . number_format( $instock ) . "</td></tr>\n";	
	echo "<tr><td id='col1'>''</td><td id='col2'>requested</td><td id='col3'>" . number_format( $reqd ) . "</td></tr>\n";
	echo "<tr><td id='col1'>''</td><td id='col2'>received</td><td id='col3'>" . number_format( $used ) . "</td></tr>\n";		
    echo "<tr><td colspan='2' id='col1'>% fufillment = received / requested</td><td id='col3'>" . number_format( $perFufill ) . "</td></tr>\n";		
    echo "<tr><td colspan='2' id='col1'>% in stock = in stock / eligible for &#42;</td><td id='col3'>" . number_format( $perInstock ) . "</td></tr>\n";		
    echo "</table>";
}



function printDetail()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn,  $row,$row2,$quantity_oked,$quantity_reqd,$quantity_used, $quantity_instock,$UpperLimit, $LowerLimit, 
       $InNumVisits,$IsFirstRow, $IsFirstHousehold,$total_oked, $total_reqd, $total_used,$total_instock,
       $grand_oked, $grand_reqd, $grand_used, $grand_instock,$currDate,$currTime, $InHouseholdID, $foundInactive;

	echo "<table border='1' class='reportDetail' />\n";

    $IsFirstHousehold=1;
    TableHeadings();
	
    if (isset($_GET['date'])) $household_id = $_GET['hhID'];
    elseif ( isset($_POST['InHouseholdID']) ) $household_id = $_POST['InHouseholdID'];
	else $household_id = 0;
	
// 01-30-2016 version 3.6.2 update: replace max date and time limits with acceptable MariaDB values. 		-mlr
//    $currDate='9999-99-99';
//    $currTime='99:99:99';			
	$currDate='9999-12-31';
	$currTime='23:59:59';	

    $FirstDate=1;
    $IsFirstRow=1;
    $CurrPantry=0;
	
// As part of the initial version 3.3 upgrade, for cases where a host might override a household's eligibility, when a 
// shopping list is printed, both eligible and non eligible products are now written to the consumption table. So, for
// the majority of households, more rows are being written than necessary. To shorten the length of the report, the 
// version 3.3.3 patch will omitt non-eligible products from the detailed view.

    $sql = "SELECT *
            FROM consumption
            WHERE date >= '$LowerLimit'
            AND date <= '$UpperLimit'
            AND household_id = $household_id
            AND product_id > 0
			AND quantity_oked > 0 			
            ORDER BY date DESC, time DESC, pantry_id, product_id";                               // 8-22-11: -mlr

    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
// 11-06-12: version 3.3 updates -mlr	
        if ( $row['date'] < $currDate || $row['time'] != $currTime || $row['pantry_id'] > $CurrPantry ) {
            if (!$FirstDate)
                PrintTotals();

            $FirstDate=0;
            $quantity_oked=$row['quantity_oked'];
            $quantity_reqd=$row['quantity_reqd'];
            $quantity_used=$row['quantity_used'];
			
// 1-19-13: version 3.4 upgrade - 'instock' value now calculated when shopping list is printed, so
//		we don't need to do it here. 			
//            if ($row['instock'] <> NULL && $row['instock'] > 0)
//                $quantity_instock = $row['quantity_oked'];                            // 4-13-11:   -mlr
//            else
//                $quantity_instock = 0;
            $quantity_instock = $row['instock'];

            PrintRow();
            $currDate = $row['date'];
			$currTime = $row['time'];
            $CurrPantry = $row['pantry_id'];                                          // 8-22-11: -mlr
        }
        else
        {
            PrintRow();

            $quantity_oked+=$row['quantity_oked'];
            $quantity_reqd+=$row['quantity_reqd'];
            $quantity_used+=$row['quantity_used'];
// 1-19-13: version 3.4 upgrade - 'instock' value now calculated when shopping list is printed, so
//		we don't need to do it here. 				
//            if ($row['instock'] <> NULL && $row['instock'] > 0)
//                $quantity_instock = $quantity_instock+$row['quantity_oked'];         // 4-13-11:   -mlr
			$quantity_instock+=$row['instock'];
        }
    }

    PrintTotals();
}



function TableHeadings()
##################################################################################
#   written: 11-3-10   -mlr                                                      #
#                                                                                #
##################################################################################
{
global $conn,  $row, $IsFirstHousehold, $total_oked, $total_reqd, $total_used, $total_instock,
	$InHouseholdID, $isPublic;


    if (!$IsFirstHousehold)
    {
?>
        <tr>
        <th COLSPAN=3 class="rightjust">total for household</th>
        <th class="rightjust"><?php echo $total_oked; ?></th>
        <th class="rightjust"><?php echo $total_instock; ?></th>
        <th class="rightjust"><?php echo $total_reqd; ?></th>
        <th class="rightjust"><?php echo $total_used; ?></th>
        </tr>
<?php
    }
    else
        $IsFirstHousehold=0;
		
// 12-24-12: version 3.3.3 updates - mask household ID	
	$maskedID = $InHouseholdID;
	if ( $isPublic ) {	
		$maskedID = "00" . strval( $InHouseholdID );
		$maskedID = "XXX" . substr( $maskedID, strlen($maskedID) - 2, 2 );	
	}			
?>
    <tr><th COLSPAN=7 class="HouseHead">Detail for Household ID = <?php echo $maskedID; ?></th></tr>
    <tr class="HouseHead">
    <th class="TopHead">visited on &#42;</th>
    <th class="TopHead">pantry</th>
    <th class="TopHead">product</th>
    <th class="TopHead">eligible for</th>
    <th class="TopHead">in stock &#42;</th>
    <th class="TopHead">requested</th>
    <th class="TopHead">received</th>

<?php
    $total_oked=0;
    $total_reqd=0;
    $total_used=0;
    $total_instock=0;
}

function PrintRow()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#                                                                                #
##################################################################################
{
global $conn, $row, $IsFirstRow, $total_oked, $total_reqd, $total_used, $total_instock, $TotVisits,
	$currDate, $currTime, $foundInactive;

	$highlightRow = "";
	if ( isset($_GET['productID']) )
		if ( $row['product_id'] == $_GET['productID'] )
			$highlightRow = " style='background:url(" . ROOT ."images/no-radio-back.gif)'";
			
	echo "<tr" . $highlightRow . ">"; 	
    echo "<td class='leftjust'>";
	
	if ($IsFirstRow) {
		$TotVisits++;
		$IsFirstRow = 0;
		
// 11-06-12: version 3.3 updates - 'TIME_ZONE_OFFSET' is defined in MySQLConfig.php			
		if ( $row['date'] != $currDate || $row['time'] != $currTime )	
//			echo "<b>" . date( 'D M j, Y g:i a', strtotime("$row[date] $row[time]") + TIME_ZONE_OFFSET ) . "</b>\n";
// 4-30-14: version 3.5.1 update - remove TIME_ZONE_OFFSET constant.		-mlr	
			echo "<b>" . date( 'D M j, Y g:i a', strtotime("$row[date] $row[time]") ) . "</b>\n";
		else
			echo '&#160';

		$pantryID = $row['pantry_id'];                                   // 3-15-11:   -mlr
		$sql2 = "SELECT * FROM pantries WHERE pantryID = $pantryID";
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		if ($row2 = mysqli_fetch_assoc($result2)) {
		
// 1-14-13: version 3.4 upgrade - add footnote character for inactive pantries		
			$inactiveFoot = "";			
			if ( !$row2['is_active'] ) {
				$foundInactive=1;				
				$inactiveFoot = DAGGER_FOOTNOTE;
			}		
			
			echo "<td style='text-align:center;'><b><abbr title='$row2[name]'>$row2[abbrev] $inactiveFoot</abbr></b>"; 
		} else
			echo "<td style='text-align:center;'>{ not found }"; 
	} else {
		echo '&#160';  
		echo '<td class="leftjust">&#160';  
	}
?>
    <td class="leftjust">
    <?php $id = $row['product_id'];
          $sql2 = "SELECT * FROM jtproducts_nameinfo 
                   WHERE productID = $id
                   AND   languageID = 1";
          $result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
          if ($row2 = mysqli_fetch_assoc($result2))
              echo stripslashes($row2['name']);                       // 4-23-11:  -mlr

// 1-19-13: version 3.4 upgrade - 'instock' value now calculated when shopping list is printed, so
//		we don't need to do it here. 
	echo "<td class='rightjust'>$row[quantity_oked]</td>\n";
    echo "<td class='rightjust'>$row[instock]</td>\n";
    echo "<td class='rightjust'>$row[quantity_reqd]</td>\n";
    echo "<td class='rightjust'>$row[quantity_used]</td></tr>\n";

//    if ($row['instock'] <> NULL && $row['instock'] > 0)
//        $total_instock += $row['quantity_oked'];
    $total_instock += $row['instock'];
    $total_oked+= $row['quantity_oked'];
    $total_reqd+= $row['quantity_reqd'];
    $total_used+= $row['quantity_used'];



}

function PrintTotals()
##################################################################################
#   written: 3-30-10       -mlr                                                  #
#	                                                                             #
# 12-21-12: version 3.3.3 patch - add column headings for each visit in the      #
# detailed listing     -mlr                                                      #                                                                                #
##################################################################################
{
global $conn, $quantity_oked, $quantity_reqd, $quantity_used, $quantity_instock, $IsFirstRow;

    $IsFirstRow=1;
?>
    <tr>
    <td colspan='3' style='font-weight:bold;'>total for visit
    <td style='font-weight:bold;'><?php echo $quantity_oked; ?>
    <td style='font-weight:bold;'><?php echo $quantity_instock; ?>
    <td style='font-weight:bold;'><?php echo $quantity_reqd; ?>
    <td style='font-weight:bold;'><?php echo $quantity_used; ?>
    </tr>
	
	
    <tr class="HouseHead">
    <th class="TopHead">visited on &#42;</th>
    <th class="TopHead">pantry</th>
    <th class="TopHead">product</th>
    <th class="TopHead">eligible for</th>
    <th class="TopHead">in stock &#42;</th>
    <th class="TopHead">requested</th>
    <th class="TopHead">received</th></tr>	

<?php
}

function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn, $LowerLimit, $UpperLimit, $total_oked, $total_reqd, $total_used,$total_instock, $TotVisits,
	$InHouseholdID, $foundInactive;

    if ( isset($_POST['InHouseholdID']) )
		$household_id = $_POST['InHouseholdID'];
	elseif ( isset($_GET['hhID']) )
		$household_id = $_GET['hhID'];
	else
		$household_id = 0;	
?>
    <tr>
    <td colspan='3' style='font-weight:bold;'>total for household
    <td style='font-weight:bold;'><?php echo $total_oked; ?>
    <td style='font-weight:bold;'><?php echo $total_instock; ?>
    <td style='font-weight:bold;'><?php echo $total_reqd; ?>
    <td style='font-weight:bold;'><?php echo $total_used; ?>
    </tr>


    </table>
    <p style="margin:10px; font-size:10pt;">
 &#42; The 'visited on' time and 'in stock' values were not recorded prior to March 24, 2011.</p>
<?php
 
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

/**
 * randomHousehold( )
 * written: 12-24-12 for version 3.3.3 patch
 *
 * returns a random id for households active in the last 18 months with a two week offset
 */
function randomHousehold()
{
global $conn,  $UpperLimit18, $LowerLimit18;

	$found = 0;
	$random = 0;

	while ( !$found ) {
	
		// To obtain a random integer R in the range i <= R < j, use the expression FLOOR(i + RAND() * (j – i)).
		$sql = "SELECT FLOOR( MIN(id) + RAND() * ( MAX(id) - MIN(id) ) ) FROM household"; 
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		if ($row = mysqli_fetch_assoc($result)) {			
			$random = $row['FLOOR( MIN(id) + RAND() * ( MAX(id) - MIN(id) ) )'];
			
			$sql2 = "SELECT COUNT(id)
					FROM consumption
					WHERE date >= '$LowerLimit18'
					AND date <= '$UpperLimit18'
					AND household_id = $random
					AND product_id > 0
					AND quantity_oked > 0"; 
			$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
			if ($row2 = mysqli_fetch_assoc($result2)) {			
				if ( $row2['COUNT(id)'] >= 1 )
					$found = 1;	
			}
		} else {
			$found = 1;
			$random = -1;	// error household table
		}
	}
	return $random;
}	    
?>

 
