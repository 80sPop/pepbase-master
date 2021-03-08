<?php 
/**
 * chart001.php - Chart Examples using LibChart (http://naku.dohcrew.com/libchart/pages/introduction/)
 * copied from \PEP2\PEPReports on 7-22-12
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr
 * 
 * 10-27-12: version 3.3 updates - set $pantryID as global $conn,  to entire system.   
 *		- visit counter now detects multiple visits in 1 day
 *		- added time and pantry to first line of visit listing
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

    if (substr_count($_SERVER['PHP_SELF'],'/') == 3)                
        define('ROOT','../');
	else
		define('ROOT','');

/* INCLUDE FILES */

	require_once(ROOT . 'MySQLConfig.php'); 
	require_once(ROOT . 'common_vars.php');
	require_once(ROOT . 'functions.php');	
	require_once(ROOT . 'Header.php');
	require_once(ROOT . 'Themes.php');	
	require_once(ROOT . 'Reports/AdminReports.php');	
	require_once(ROOT . 'libchart/libchart/classes/libchart.php');

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
        $InNumVisits = $_POST['InNumVisits'];
    else
        $InNumVisits = 1;
    $Max=0;
    $Min=0;
    $ValidData=0;
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

    doHeader("PEP001 - Household Consumption by Number of Visits", "isReport"); 
 
/* MAINLINE */

	$chart = new VerticalBarChart(500, 250);

// Create some bars
// We add 4 bars to our chart. 

	$dataSet = new XYDataSet();
	$dataSet->addPoint(new Point("Jan 2005", 273));
	$dataSet->addPoint(new Point("Feb 2005", 321));
	$dataSet->addPoint(new Point("March 2005", 442));
	$dataSet->addPoint(new Point("April 2005", 711));

// Then we link the data set to the chart:

	$chart->setDataSet($dataSet);

// Display the chart
// We set the title and then render the chart to a PNG image.

	$chart->setTitle("Monthly usage for www.example.com");
	$chart->render("generated/demo1.png");


	echo "</font></body></html>\n";
	mysqli_close( $conn );
}	

######################################################################
######################################################################
#                    F U N C T I O N S                               #
######################################################################
?>

