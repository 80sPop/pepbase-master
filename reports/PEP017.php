<?php 
/**
 * PEP017.php - Update shelter data in 'shelters', 'household' tables
 * written: 5-11-12
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 *
 * 10-10-12: version 3.1 updates - Add code for new variable '$themeId'.   -mlr
 * 
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
	$shelterTally=0;
	
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

    doHeader("PEP017 - Initialize Shelter Data", "isReport");
	
/* MAINLINE */

	echo "<center>";
	echo "<h2>PEP017 - Initialize Shelter Data in 'shelter' and 'household' Tables</h2>\n";
	standardizeShelterTable();
    scanHouseholdTable();
    printFootNote();

	echo "</center>";
	echo "</body>";
	echo "</html>";
	mysqli_close( $conn );
}

	
/** standardizeShelterTable()
  * written: 5-12-12 -mlr
  */	
function standardizeShelterTable()
{
    $sql = "SELECT * FROM shelters";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
		$newName = ucname( standarizeSteetName( $row['streetname'] ) );
		$sql2= "UPDATE shelters                                                  
				SET streetname = '$newName'           
				WHERE id = " . $row['id'];
		$UpdateOK = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));		
    }
}	
	
function scanHouseholdTable()
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $shelterTally;

    $sql = "SELECT * FROM household";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
        if ($shelter = isShelter($row['streetnum'], $row['streetname'])) {
            UpdateHousehold($row['id'],$shelter);
            $shelterTally++;
        }
    }
}


function UpdateHousehold($id, $shelter)
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{

    $sql = "UPDATE household                                                  
            SET shelter = $shelter           
            WHERE id = $id";

    $UpdateOK = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));

}


function printFootNote()
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $shelterTally;


    $TodaysDate = date('M j, Y');

    $sql = "SELECT COUNT(*) FROM household";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result))
        $Tothouseholds = $row['COUNT(*)'];
?>
    <p>
    Date: <?php echo $TodaysDate; ?>
    <p>
    <u>'household' table</u><br>
    <p>
    row(s) where 'shelter' updated: <?php echo number_format($shelterTally); ?>
    <p>
    Total row(s) in table: <?php echo number_format($Tothouseholds); ?>
<?php
}
?>