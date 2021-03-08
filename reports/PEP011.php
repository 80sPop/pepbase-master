<?php
# PEP011.php
# written: 11-15-10    -mlr
#
# 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
# 
# 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
# 
# 10-10-12: version 3.1 updates - Add code for variable '$themeId'.  -mlr
#
# 7-15-11: version 2.5 updates.
#          - Due to random timeouts on commercial server (php.ini settings), login now verified
#            with a single cookie (see function "ValidLogin") rather than php's session variables.    -mlr
#
# 2-10-11: Modify session verification and MYSQL connection to be 
#           compatable with existing pep system. -mlr  


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

    set_time_limit(1900);
    $ValidRows=0;
    $IsFirstMatched=1;

	$sql = "SELECT * FROM access_levels WHERE al_id = " . HOST_ACCESS_LEVEL;
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    $accessLevelRow = mysqli_fetch_assoc($result);
	
	$sql = "SELECT * FROM signin_accounts WHERE sa_id = " . HOST_SIGNIN_ID;
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result)) 
	    $hostPantryId = $row['sa_pantry_id'];
	else
		die('host signin not found in signin_accounts table');
		
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
		
	defineThemeConstants();					// defined in Themes.php	

		
/* XHTML HEADER */

    doHeader("PEP011 - Error Report: Members in Multiple Households", "isReport");	

$today = getDate();

######################################################################
######################################################################
#                      M A I N L I N E                               #
######################################################################

    CalcDateLimits();

?>
    <CENTER>


    <FONT style="font-size:12pt;font-weight:bold;">Error Report - Members in Multiple Households - PEP011</FONT>
    <p><i>
    Study based on data from PEPbase 'members' table. <br>
    </i><p>


<?php

    TableHeadings();

    $sql = "SELECT * FROM members
            WHERE dob > '0000-00-00'
            AND householdID > 0 
            ORDER BY householdID"; 

    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {
        if ($row['householdID'])
            $householdID=$row['householdID'];
        else
            $householdID=0;                                      
        if ($row['firstname'])
        {
            $firstname=$row['firstname'];
            $firstname=mysqli_real_escape_string( $conn, $firstname);     // escape special characters, so they're safe for MySQL   
        }                                                        
        else
            $firstname="";
        if ($row['lastname'])
        {
            $lastname=$row['lastname'];                          
            $lastname=mysqli_real_escape_string( $conn, $lastname);       // php's mysqli_real_escape_string( $conn, ) doesn't seem to like 
        }                                                        // associative arrays for an argument   -mlr
        else
            $lastname="";
        if ($row['dob'])
            $dob=$row['dob'];
        else
            $dob="0000-00-00";

    // Here, single quotes around character strings $firstname and 
    // $lastname also needed for correct MySQL interpolation, so 
    // string values like 'Muhammad' are not mistaken for field names

        $sql2 = "SELECT * FROM members
                 WHERE firstname = '$firstname'
                 AND lastname = '$lastname' 
                 AND dob = '$dob'
                 AND householdID > 0
                 AND householdID <> $householdID";              
                                                                
        $result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));    
        while ($row2 = mysqli_fetch_assoc($result2))
        {
?>
            <TR>
            <TD class="leftjust"><?php
                echo $row['firstname']." ".$row['lastname']; ?>
            <TD class="rightjust"><?php
                echo $householdID; ?>
            <TD class="rightjust"><?php
                echo $row2['householdID']; ?></TR>
<?php
        }
    }
	mysqli_close( $conn );
}	

//    PrintFootNote();

?>
    </TABLE>
    </CENTER>

<?php

function CalcDateLimits()
##################################################################################
#   written: 10-31-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $UpperLimit, $UpperLimitFmt, $LowerLimit, $LowerLimitFmt, $UpperActive, $LowerActive;
   
    $TodaysDate  = date("Y-m-d");
    $TodaysMonth = substr($TodaysDate,5,2);
    $TodaysDay   = substr($TodaysDate,8,2);
    $TodaysYear  = substr($TodaysDate,0,4);

    $UpperActive   = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear)); 
    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));
    $UpperMonth    = substr($UpperActive,5,2);
    $UpperDay      = substr($UpperActive,8,2);
    $UpperYear     = substr($UpperActive,0,4); 

    $LowerActive   = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
 
}



function TableHeadings()
##################################################################################
#   written: 11-15-10                                                            #
#                                                                                #
##################################################################################
{
?>
    <TABLE border=1 style="padding:5px;">

    <TR>
    <TH class="TopHead">Guest
    <TH class="TopHead">householdID
    <TH class="TopHead">Also <br>Exists in</TR>

<?php
}


function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $LowerActive,$UpperActive,$ValidRows; 


    $sql = "SELECT COUNT(*)
            FROM members";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result))
        $TotMembers = $row['COUNT(*)'];

    $InValidRows = $TotMembers - $ValidRows;

?>
    <p>
    <u><b>SUMMARY</b></u>
    <p>
    <TABLE style="font-size:10pt;">


    <TR>
    <TD>Active 'members' table row(s) used in study 
    <TD class="rightjust">
    <?php
#        $sql = "SELECT count(*)
#                FROM members
#                JOIN household
#                ON members.householdID = household.id
#                WHERE members.householdID = household.id
#                AND household.lastactivedate >= '$LowerActive'
#                AND household.lastactivedate <= '$UpperActive'
#                AND household.id > 0
#                AND household.streetname NOT LIKE 'duplicate%'
#                AND members.dob > '0000-00-00'  
#                AND members.dob <= '$UpperActive' 
#                AND members.householdID > 0
#                AND (members.gender = 'male' OR members.gender = 'female')";

#        $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
#        if ($row = mysqli_fetch_assoc($result))
#            echo number_format($row['count(*)']); 

            echo number_format($ValidRows); ?>

    <TR>
    <TD>Inactive or invalid 'members' table row(s) 
    <TD class="rightjust">
    <?php
#        $sql = "SELECT COUNT(*)
#                FROM members
#                JOIN household
#                ON members.householdID = household.id
#                WHERE household.lastactivedate < '$LowerActive'
#                OR household.lastactivedate > '$UpperActive'
#                OR household.id <= 0
#                OR household.streetname LIKE 'duplicate%'
#                OR members.dob <= '0000-00-00'  
#                OR members.dob > '$UpperActive' 
#                OR members.householdID <= 0
#                OR (members.gender <> 'male' AND members.gender <> 'female')";

#        $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
#        if ($row = mysqli_fetch_assoc($result))
#            echo number_format($row['COUNT(*)']); 

            echo number_format($InValidRows); ?>


    <TR>
    <TD>Total row(s) in 'members' table 
    <TD class="rightjust">
        <?php echo number_format($TotMembers); ?>


    </TABLE>

<?php

}

?>

</body>
</html>