<?php 
/**
 * InStockPrintout.php - Print In-Stock status report
 * Written: 9-24-12
 *
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script.	-mlr 
 *
*/
 
 /* VERIFY ACCESS LEVEL */
 
if ( (!isset($_COOKIE["accessLevel"])) ||  ((isset($_COOKIE["accessLevel"])) && $_COOKIE["accessLevel"] < 1)) 
    echo "<b>! UNAUTHORIZED ACCESS. PLEASE CONTACT SYSTEM ADMINISTRATOR</b>";	
else {	

/* CONSTANT DECLARATIONS   */

    define('HOST_ACCESS_LEVEL', $_COOKIE["accessLevel"]);
	define('HOST_SIGNIN_ID', $_COOKIE["signinId"]);
	define('LIGHT_LIST_LINE', '#eeeeee');
	define('DARK_LIST_LINE', '#dddddd');	
	define('BASE_FONT', 'black');	

/* INCLUDE FILES */

	require_once('../MySQLConfig.php'); 
	require_once(ROOT . 'common_vars.php');
	require_once(ROOT . 'functions.php');	
	require_once(ROOT . 'Header.php');

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

	if (isset($_GET['field'])) {
        $field = $_GET['field'];
        $order = $_GET['order'];
	} elseif (isset($_POST['field'])) {
        $field = $_POST['field'];
        $order = $_POST['order'];		
    } else {
        $field = "staffName";				
        $order = "asc";
    }
	
	if ( isset($_GET['hhID']) )
		$hhID = $_GET['hhID'];
	elseif ( isset($_POST['hhID']) )		
		$hhID = $_POST['hhID'];
	else	
		$hhID = 0;
		
// here, the pantry id for accounts with access to all pantries (i.e. Administrator) will default to Zion. Eventually, 
// households->profile will allow these accounts to select a pantry before printing the shopping list.
		
	if ( $hostPantryId == 0 ) {
		$hostPantryId = 1;			// Zion
		$pantryID = $hostPantryId;	
	} elseif ( isset($_POST['pantryID']) )
		$pantryID = $_POST['pantryID'];
	elseif ( isset($_GET['pantryID']) )
		$pantryID = $_GET['pantryID'];			
	else
		$pantryID = $hostPantryId;		

	$today = date('Y-m-d');
	$time = date('H:i:s');		
	
/* XHTML HEADER */

	doHeader("PEP3 - In-Stock Status", "isGuestForm");	
	
/* MAINLINE */
	
	printInStockStatus( );
		
	echo "</body>";
	echo "</html>";	
	mysqli_close( $conn );
}

	

######################################################################
######################################################################
#                    F U N C T I O N S                               #
######################################################################


/** printInStockStatus( )
  * written: 9-24-12 -mlr
  */
function printInStockStatus( )
{
global $conn,  $hhID,$accessLevelRow, $errCode, $errSaId, $pantryID, $hostPantryId, $viewType, $sql, $field, $order, $newProductNameOrder, 
		$newShelfBinOrder, $newInStockStatusOrderOrder;
		
	
    tableHeadings();
	
	$i=0;

	$firstRow=1;
	$firstLine=1;
	$lineShade = LIGHT_LIST_LINE;	
	
	$sql = "SELECT * FROM sort_products ORDER BY sp_shelf, sp_bin, sp_name, sp_typenum";	
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result)) {
	
		if ( $firstRow ) {
			$prevName = $row['sp_name'];	
			$firstRow = 0;
		}	

		if ( $row['sp_name'] != $prevName || $firstLine ) {             // break on product name
			$breakBorder = "style='padding-top:1px;padding-bottom:1px;'";
			$breakBorderCntr = "style='padding-right:0px;padding-top:1px;padding-bottom:1px;'";			
			$printName = $row['sp_name'];
			$printShelfBin = $row['sp_shelf']. " / " .  $row['sp_bin'];
			$firstLine = 0;		
		} else {	
			$breakBorder = "style='padding-top:1px;padding-bottom:2px;border-top: 0px'";
			$breakBorderCntr = "style='padding-right:0px;padding-top:1px;padding-bottom:2px;border-top: 0px'";			
			$printName = "";
			$printShelfBin = "";			
		}	

		$prevName = $row['sp_name'];			

        echo "<tr style='background-color:" . $lineShade . ";'>";
        echo "<td $breakBorder>";
		if ( $row['sp_shelf'] )
			echo $printShelfBin; 
		else
			echo "&#160;";
		echo "</td>	\n";	
        echo "<td $breakBorder>";
		if ( $row['sp_name'] )
			echo $row['sp_name'];
		else
			echo '&#160;';
		echo "</td>\n";
        echo "<td $breakBorder>";
		if ( $row['sp_type'] )
			echo $row['sp_type']; 
		else
			echo "&#160;";
		echo "</td>\n";						
        echo "<td $breakBorderCntr>";
		if ( $row['sp_instock'] )
			echo "X";
		else
			echo "&#160;";
		echo "</td></tr>\n";					
		if ( $lineShade == LIGHT_LIST_LINE )
			$lineShade = DARK_LIST_LINE;
		else
			$lineShade = LIGHT_LIST_LINE;	
    } 
	echo "</table>";
}

/** tableHeadingsPI()
  * written: 2-24-12 -mlr
  */
function tableHeadings()
{
global $conn,  $hhID, $pantryID, $hostPantryId, $viewType, $sql, $field, $order, $accessLevelRow, $newProductNameOrder, $newShelfBinOrder, $newInStockStatusOrder;


?>

	<table border="0" cellspacing="0" class="listBox">
	<tr><th>shelf / bin</th>	
	<th>product name</th>
	<th>size / type</th>	
	<th>in stock?</th></tr>

<?php
}
