<?php 
/**
 * EligibilityAndShopping.php - Print Eligibility Certificate and Shopping List
 * Written: 5-30-12
 *
 * 3-5-2018: version 3.6.3 update - hardcode instructions for Spanish speaking Households so letters with accents 
 *		print correctly.	-mlr 
 *
 * 1-6-2016: version 3.6.1 update - reformat printed date fields.	-mlr
 * 
 * 12-21-2014: version 3.5.21 patch - remove "other" from ALL shopping lists.		-mlr
 *
 * 10-13-14: version 3.5.21 patch - remove "other" product from Watertown Immanuel's (pantryID=8) shopping list.		-mlr
 *
 * 9-26-14: version 3.5.21 patch - remove "other" product from CMC's shopping list.		-mlr
 *
 * 9-6-14: version 3.5.2 upgrade 
 * 		- add function ShopList_PrintOtherProduct( ) for "other" product on shopping list
 *		- re-write  doShoppingList() to prevent extra page breaks in shopping list
 *
 * 6-28-14: version 3.5.1 upgrade
 *	 	- add mutiple language equivilents and left margin for instructions and contact/address block titles. 
 *		- adjust monthly income limits on registration form for the year 2014.
 *		- add time stamp to shopping list	 -mlr 
 *		
 * 2-8-14: ver 3.4.83 patch - remove 'ROOT' constant declaration. 	-mlr
 *
 * 1-18-14: version 3.4.82 patch - close open mysqli connection ($conn) at the end of the script;
 *		optimize consumption table before writing to it; add function updateLastActiveDate().	-mlr 
 *
 * 7-23-13: version 3.4.4 upgrade - changed page title from "Eligibility Certificate" to 
 * 		- "Registration Form".			-mlr
 * 
 * 4-7-13: version 3.4.3 patch - updated Second Harvest Food Bank's eligibilty requirements.	-mlr
 *
 * 1-19-13: version 3.4 upgrade 
 *		- In writeConsumptionRow(), re-calculate 'instock' value.
 *
 * 10-26-12: version 3.3 updates 
 *		- set $pantryID as global $conn,  to entire system
 * 		- display pantry name at top of shopping list
 * 
 * 9-19-12: version 3.1 updates.
 *		- move 'Generate Shopping List' form button to top of page.  
 *		- add logic for new portion limit overrides.	-mlr
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
	require_once(ROOT . 'HouseholdsEligibleFor.php');
	require_once(ROOT . 'HouseholdsProfile.php');
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

// 10-26-12: version 3.3 update - 'pantryID' now global $conn,  to entire system		
	if ( isset($_POST['pantryID']) )
		$pantryID = $_POST['pantryID'];
	elseif ( isset($_GET['pantryID']) )
		$pantryID = $_GET['pantryID'];			
	else
		$pantryID = 1;	

	if ( isset($_GET['themeId']) )
		$themeId = $_GET['themeId'];
	elseif ( isset($_POST['themeId']) )		
		$themeId = $_POST['themeId'];
	else	
		$themeId=0;		

	defineThemeConstants();					// defined in Themes.php		

	$today = date('Y-m-d');
	$time = date('H:i:s');		
	
/* XHTML HEADER */

	if ( isset($_POST['printEligibility']) ) 
		doHeader("PEP3 - Registration Form", "isGuestForm");
	else
		doHeader("PEP3 - Shopping List", "isGuestForm");	
	
/* MAINLINE */

	if ( isset($_POST['printEligibility']) )
		doEligibilityCertificate();
	elseif ( isset($_POST['printOverShopping']) ) {
		changeOverrideValues();
		doShoppingList();
	} else {
		fillSortTableEF( 1 );	// defined in HouseholdsEligibleFor.php	
		doShoppingList();	
	}	
	
	echo "</body>";
	echo "</html>";	
	mysqli_close( $conn );	
}

	

######################################################################
######################################################################
#                    F U N C T I O N S                               #
######################################################################

/**
  * copied from PEP2/includes/print_registration_form.php on 5-30-12 -mlr
  */
function doEligibilityCertificate()
{
global $conn,  $hhID, $customTextMsg, $themeId, $pantryID;

//	$maxSize=12;
	$maxSize=10;
	$count=0;

	$sql = "SELECT * FROM household WHERE id = $hhID";		
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($hh = mysqli_fetch_assoc($result)) {

		$hh_lang = $hh['language'];

		echo "
		<table class='printer' border='1' cellspacing='0' cellpadding='0'>
		<tr><td id='head1'>" . $customTextMsg['regform_header'][$hh_lang] . "</td></tr>
		<tr><td id='head2'>" . $customTextMsg['regform_inst1'][$hh_lang] . "</td>	
		<tr><td id='head3'>" . $customTextMsg['regform_inst2'][$hh_lang] . "</td>		
		</tr>
		</table>\n";
		
/* 9-19-12 v 3.1 changes: move 'Generate Shopping List' form button here */		
		
		echo "<table class='printer' style='border-width:0px;' border=0 cellspacing=0 cellpadding=0>\n";
		echo "<tr>\n";
		echo "  <td style='border-width:0px;' align=left valign=bottom>\n";
		echo "    <form action=" . $_SERVER['PHP_SELF']. " enctype=\"multipart/form-data\" method=\"post\">\n";
		echo "      <input type=hidden name=hhID         value=\"$hhID\"> \n";
		
// 11-20-12: version 3.3 update - add themeId, pantryID to $_POST array		
		echo "      <input type=hidden name=pantryID     value=\"$pantryID\"> \n";	
		echo "      <input type=hidden name=themeId      value=\"$themeId\"> \n";		
		echo "      <input type=submit value='Generate Shopping List'>\n";
		echo "    </form>\n";
		echo "  </td>\n";
		echo "</tr>\n";
		echo "</table>\n";		
		
		echo "<table class=printer border=1 cellspacing=0 cellpadding=0>\n";
		echo "<tr>\n";
		echo "  <th colspan=1 width=33%> </th>\n";
		echo "  <th colspan=1 width=13%> </th>\n";
		echo "  <th colspan=1 width=20%> </th>\n";
		echo "  <th colspan=1 width=13%> </th>\n";
		echo "  <th colspan=1 width=10%> </th>\n";
		echo "  <th colspan=1 width=*>   </th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		
		
		
/* 6-26-14: version 3.5.1 upgrade - use $customTextMsg[] array for language translations in form titles.	-mlr */		
		echo "  
		<td colspan='2' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_lastname_hdr'][$hh_lang] . "</span><br>" .
		stripslashes(ucname($hh['lastname']))  . "</td>\n
		
		<td colspan='3' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_firstname_hdr'][$hh_lang] . "</span><br>" .
		stripslashes(ucname($hh['firstname']))  . "</td>\n	
		
		<td colspan='3' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_household_id_hdr'][$hh_lang] . "</span><br>" .
		stripslashes(ucname($hh['id']))  . "</td></tr>\n";		
	
		$val = stripslashes(strtoupper($hh['streetnum'])) . " " . stripslashes(ucname($hh['streetname'])) 
		. " " . ( ($hh['apartmentnum']=='') ? "" : "&nbsp;&nbsp;#" )
		. stripslashes(strtoupper($hh['apartmentnum']));
		
		echo "
		<tr><td colspan='6' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_address_hdr'][$hh_lang] . "</span><br>" .
		$val . "</td></tr>\n";	

		echo "
		<tr><td colspan='1' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_city_hdr'][$hh_lang] . "</span><br>" .
		stripslashes(ucname($hh['city']))  . "</td>\n	
		
		<td colspan='2' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_county_hdr'][$hh_lang] . "</span><br>" .
		stripslashes(ucname($hh['county']))  . "</td>\n			
		
		<td colspan='2' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_state_hdr'][$hh_lang] . "</span><br>" .
		stripslashes(strtoupper($hh['state']))  . "</td>\n		

		<td colspan='2' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_zip_hdr'][$hh_lang] . "</span><br>" .
		$hh['zip_five'] . " - " . $hh['zip_four'] . "</td></tr>\n";			
		

		echo "
		<tr><td colspan='1' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_phone1_hdr'][$hh_lang] . "</span><br>" .
		expandPhone($hh['phone1']) . "</td>

		<td colspan='2' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_phone2_hdr'][$hh_lang] . "</span><br>" .
		expandPhone($hh['phone2']) . "</td>\n

		<td colspan='3' class='regAdd'>
		<span class='regAddTitle'>" . $customTextMsg['reg_email_hdr'][$hh_lang] . "</span><br>" .
		stripslashes($hh['email']) . "</td></tr></table>\n";
		
		echo "<br>\n";
		echo "<table class='printer' border='1' cellspacing='0' cellpadding='0'>\n";
		echo "<tr>\n";
		echo "  <th colspan=5 align=center>\n";
		
/* 6-26-14: version 3.5.1 upgrade - use $customTextMsg[] array for language translations in form titles.	-mlr */		
		echo $customTextMsg['reg_listmembers_hdr'][$hh_lang];
//		echo "    List All Household Members <font size=-2>(use reverse side for additional members, if needed)</font>\n";
		echo "  </th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "  <th width=35% align=center><font size=-2>" . $customTextMsg['reg_listmembers_fullname'][$hh_lang] . "</font></th>\n";
		echo "  <th width=20% align=center><font size=-2>" . $customTextMsg['reg_listmembers_dob'][$hh_lang] . "</font></th>\n";
		echo "  <th width=10% align=center><font size=-2>" . $customTextMsg['reg_listmembers_gender'][$hh_lang] . "</font></th>\n";
		echo "  <th width=10% align=center><font size=-2>" . $customTextMsg['reg_listmembers_allergies'][$hh_lang] . "</font></th>\n";
		echo "  <th width=*   align=center bgcolor=#eaeaea> <font size=-2>" . $customTextMsg['reg_listmembers_notes'][$hh_lang] . "</font></th>\n";
		echo "</tr>\n";

		$sql2 = "SELECT * FROM members WHERE householdID = $hhID";		
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		while ($members = mysqli_fetch_assoc($result2)) {		  
			$memberPantry    = "&nbsp;" ;
			if ( strtolower($members['incontinent']) == "yes" ) 
				$memberPantry = "<i>incont</i>"; 
			$memberName      = stripslashes(ucname($members['firstname']) . " " . ucname($members['lastname']));
			
// 1-6-2016: version 3.6.1 update - reformat printed date fields.	-mlr 						
			$memberDOB = date('m/d/Y', strtotime($members['dob']));		
//			$memberDOB       = MySQL2mmddyyyy(stripslashes($members['dob']));
			if ( $memberDOB == '0000-00-00') {
			  $memberDOB       = '';
			  $memberGender    = '';
			  $memberAllergies = '';
			} else {
			  $memberGender    = stripslashes($members['gender']);
			  $memberAllergies = stripslashes($members['allergies']);
			}
			if ( $memberDOB       == '' ) { $memberDOB       = "&nbsp;" ; }
			if ( $memberGender    == '' ) { $memberGender    = "&nbsp;" ; }
			if ( $memberAllergies == '' ) { $memberAllergies = "&nbsp;" ; }

			echo "<tr>  <td class='regMemList'>$memberName</td>\n";
//			echo "<tr>  <td align=left valign=center><font size=+1><b> $memberName </b></font></td>\n";			
			echo "      <td class='regMemList'>$memberDOB</td>\n";
			echo "      <td class='regMemList'>$memberGender</td>\n";
			echo "      <td class='regMemList'>$memberAllergies</td>\n";
			echo "      <td class='regMemList' style='background-color:#EAEAEA;'>$memberPantry</td>\n";
			echo "</tr>\n";
			$count++;
		}
		
		$extraLines = $maxSize - $count;
		for ( $i=1; $i<=$extraLines; $i++ ) {
			echo "<tr>  <td align=left valign=center><font size=+1><b> &nbsp; </b></font></td>\n";
			echo "      <td>                                           &nbsp;            </td>\n";
			echo "      <td>                                           &nbsp;            </td>\n";
			echo "      <td>                                           &nbsp;            </td>\n";
			echo "      <td bgcolor=#eaeaea>                           &nbsp;            </td>\n";
			echo "</tr>\n";	
		}	
		
		
		echo "</table>\n";
		echo "<br>\n";

		echo "<table class=printer border=1 cellspacing=0 cellpadding=0>\n";
		echo "<tr>\n";
		echo "  <td align=left valign=top width=65%>\n";
		echo "    <table border=0 cellspacing=0 cellpadding=0 width=100%>\n";
		echo "      <tr><td align=left>" . $customTextMsg['certification'][$hh_lang] . "</td></tr>\n";
		echo "    </table>\n";
		echo "  </td>\n";
		echo "  <td align=center valign=top width=*>\n";
//		echo "    <table width=100% border=1 cellspacing=0 cellpadding=0 bgcolor=#eaeaea>\n";
		echo "    <table class='eligibility' border='1' cellspacing='0' cellpadding='0'>\n";		
		echo "      
		<tr><th style='text-align:center;'>" . $customTextMsg['reg_req_size_hdg'][$hh_lang] . "</th>
        <th style='text-align:center;'>" . $customTextMsg['reg_req_income_hdg'][$hh_lang] . "</th>     </tr>\n";

/* 4-7-13: version 3.4.3 patch - update  
		We use Second Harvest Food Bank's eligibilty requirements, or 200% of the federally approved HHS (Health and Human 
		Services Department) poverty guidelines effective January 24, 2013. Numbers are from Second Harverst Food Bank's web page 
		http://www.secondharvestmadison.org/AgencyServices/PartnerAgencyResources.aspx
*/ 

/* 6-28-14: version 3.5.1 upgrade - use $customTextMsg[] array for language translations, adjust income limits 
		for the year 2014.	-mlr */
		echo "
		<tr><td>1</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $1,945</td> </tr>
		<tr><td>2</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $2,622</td> </tr>
		<tr><td>3</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $3,298</td> </tr>
		<tr><td>4</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $3,975</td> </tr>
		<tr><td>5</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $4,652</td> </tr>
		<tr><td>6</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $5,328</td> </tr>
		<tr><td>7</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $6,005</td> </tr>
		<tr><td>8</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $6,682</td> </tr>
		<tr><td>9</td><td id='col2'>" . $customTextMsg['reg_req_income_int'][$hh_lang] . " $7,358</td> </tr>
		<tr><td>More</td><td id='col2'><i>" . $customTextMsg['reg_req_income_add'][$hh_lang] . "</i></td> </tr>
		</table>
		</td>
		</tr>
		<tr>
		<td style='text-align:left;padding-left:5px;' valign='bottom' colspan='2'><b><i>" . $customTextMsg['reg_signature_hdg'][$hh_lang] . "<br><br><br></i></b></td>
		</tr>
		</table>\n";

	} else
		echo "!ERROR - households table";
}

/**
  * written: 10-23-12 -mlr
  */
function changeOverrideValues()
{
	$sql = "SELECT * FROM sort_products WHERE sp_carried = 'yes'";	
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ( $row = mysqli_fetch_assoc($result) ) {

		if ( strtolower($row['sp_personal']) == 'yes' ) {
			$postIndex = "inPersonal$row[sp_productID]";		
			$num_eligible = $_POST[$postIndex];
		} else {
			$postIndex = "inSharedVal$row[sp_productID]";		
			if ( $_POST[$postIndex] == "yes" )		
				$num_eligible = 1;		
			else
				$num_eligible = 0;
		}	

		$sql2 = "UPDATE sort_products
					SET sp_num_eligible = '$num_eligible'
					WHERE sp_productID = $row[sp_productID]";
		$UpdateOk = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));		
	}			
}

/**
  * written: 9-15-14 for version 3.5.2 update.			-mlr 	
  */
function doShoppingList()
{
	global $conn;
	global $hostPantryId; 
	global $pantryID;
	global $sort_products;
	global $hhID;
	global $customTextMsg;
	global $hh_lang;
	global $hh_fullname;
	global $column;	
	global $pageNum;
	global $today;
	global $time;

	$pageNum=0;
	$printedHeight=0;

	$sql = "OPTIMIZE TABLE consumption";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));	
	
	$sql = "SELECT * FROM household WHERE id = '$hhID'";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ( $row = mysqli_fetch_assoc($result) ) {
		$hh_lang = $row['language'];
		$hh_fullname = stripslashes( $row['firstname'] ) . " " . stripslashes( $row['lastname'] );	
		$hh_fullname = ucname( $hh_fullname );
	}

	ShopList_PrintHeader( $hhID, $hh_fullname, $hh_lang );
	
	$sql = "SELECT sp_shelf, sp_bin, sp_num_eligible, sp_portion_limit, sp_productID, sp_name, sp_instock, sp_personal, sp_qtyID 
			FROM sort_products  
			WHERE FIELD(`sp_carried`, 'yes')
			ORDER BY sp_shelf, sp_bin";			
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ( $sort_products = mysqli_fetch_assoc($result) ) {
		if ( $sort_products['sp_num_eligible'] > 0 )
			ShopList_PrintProduct( );
			
		writeConsumptionRow();
		updateLastActiveDate();
	}

// 9-26-14: version 3.5.21 patch - remove "other" product from CMC's shopping list.		-mlr
// 10-13-14: version 3.5.21 patch - remove "other" product from Watertown Immanuel's (pantryID=8) shopping list.		-mlr
// 12-21-2014: version 3.5.21 patch - remove "other" from ALL shopping lists.		-mlr
//	if ( $pantryID != 3 && $pantryID != 8 )	
//		ShopList_PrintOtherProduct();	

	echo "</table></center>";
}

/* written for version 3.5.2 update */
function ShopList_PrintHeader( $id, $name, $lang ) 
{
global 	$conn, 
		$customTextMsg, 
		$pantryID, 
		$pageNum;
  
	$pageBreak="";
	if ( $pageNum > 0 )
		$pageBreak = "page-break-before:always";

	$sql = "SELECT name FROM pantries WHERE pantryID = $pantryID";
	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	$row = mysqli_fetch_assoc($result);
	$pantryName = $row['name'];
	
	echo "
	<center>
	<table border='1' cellspacing='0' style='$pageBreak;margin:0px;padding:0px;width:" . PAGE_WIDTH . "px;height:" . PAGE_HEIGHT . "px;border-collapse:collapse;vertical-align:top;'>";
	
	echo "
	<tr>
	<td colspan='1' style='height:20px;font-size:11pt;border-width:0;padding:0;margin:0;width:100%;vertical-align:top;'>
		<table border='0' cellspacing='0' style='margin:0;border-collapse:collapse;width:100%;'><tr>
		<td style='width:1%;border-width:0;'>&nbsp;</td>		
		<td style='width:19%;border-width:0;text-align:left;'>Shopping List for</td>
		<td style='width:60%;border-width:0;text-align:center;'>$pantryName</td>
		<td style='width:19%;border-width:0;text-align:right;'>" . date("D n/j/Y") . "</td>
		<td style='width:1%;border-width:0;'>&nbsp;</td></tr>		
		</table></td></tr>
	<tr>
	
	<td colspan='1' style='font-size:11pt;height:35px;border-width:0;padding:0;margin:0;width:100%;vertical-align:top;'>	
		<table border='0' cellspacing='0' style='margin:0;border-collapse:collapse;width:100%;height:35px;vertical-align:top;'><tr>	
		<td style='width:1%;padding:0;margin:0;border-width:0;'>&nbsp;</td>
		<td style='width:64%;padding:0;margin:0;border-width:0;text-align:left;vertical-align:top;'>" .	
			(( strlen($name) < 20 ) ? "<span style='font-size:18pt;'>" : "<span style='font-size:14pt;'>") .
			$name . "</span><br>
			<span style='font-size:11pt;'>HOUSEHOLD ID: $id</span></td>
		<td style='width:20%;padding:0;margin:0;font-size:11pt;border-width:0;text-align:right;padding-right:5px;'>Filled By:&nbsp;<br><i>(staff initials)</i></td>
		<td style='width:14%;padding:0;margin:0;border:3px solid #aaa;padding-left:5px;'>&nbsp;</td>
		<td style='width:1%;padding:0;margin:0;border-width:0;'>&nbsp;</td></tr>
		</table></td>
  
	<tr><td colspan='1' style='height:75px;font-size:10pt;font-style:italic;width:100%;border-width:0;text-align:center;vertical-align:top;'>
		<table style='width:98%;display:inline-block;'>
		<tr>";

// Background colors don't print for most browser's default settings, so repeat a 1 pixel image and move the text over the
// image using absolute and relative positioning.		-mlr	
	echo "
	<td style='padding:5px;border-width:0;'>\n
	<div style='position: relative;'>
		<img src='" . ROOT . "images/pep-orange.gif' style='width:630px;height:65px;border:1px solid #000;padding:0;' />
		<span style='position: absolute; top: 0%; left: 0%; margin:5px;text-align:left;'>";
		if ( $customTextMsg['print_shopping_inst'][$lang] == '' ) 
			echo $customTextMsg['print_shopping_inst'][1];
		elseif ( $lang == 2 )
// version 3.6.3 upadate: Hardcode instructions for Spanish language. See php documentation for 
// htmlspecialchars() to decode accent characters stored in arrays.		-mlr	
			echo "
			Por favor, utilice los cuadros y l??neas en la izquierda, y </i>NO LOS RECTANGULOS GRIS.</i>
			Para los productos con l??neas,<i>POR FAVOR ESCRIBA EL NUMERO QUE NECESITA</i>, hasta el l??mite indicado.";		
		else
			echo $customTextMsg['print_shopping_inst'][$lang]; 
			
	echo "</span></div>		
	</td></tr></table>
	</td></tr>";	
}

/* written for version 3.5.2 update */
function ShopList_PrintProduct( ) 
{
	global 	$conn, 
			$sort_products,
			$hhID,	
			$products,
			$customTextMsg,
			$hh, 
			$hh_lang,
			$hh_fullname,
			$printedHeight,
			$column,		
			$pageNum;


	$quantity =  $sort_products['sp_num_eligible'];
	$isType = 0;	
	$isPortionLimit = 0;
	$prodWidth = ( PAGE_WIDTH / 2 ) - 72;
	
// check if portion limits are set for the product 	
	if ( $sort_products['sp_portion_limit'] != -1 )
		if ( $sort_products['sp_portion_limit'] < $sort_products['sp_num_eligible'] ) {
			$quantity = $sort_products['sp_portion_limit'];
			$isPortionLimit = 1;
		}	

// calculate needed height in pixels	
	if ( $hh_lang == 1 )
		$neededHeight = 31;
	else 
		$neededHeight= 49; // non-english language household, add 18 pixels for translation
	$numTypes = 0;  
	$sql2 = "SELECT * FROM jtproducts_typeinfo WHERE productID = " . $sort_products['sp_productID'];
	$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
	while ($row2= mysqli_fetch_assoc($result2)) {
		$isType = 1;
		$numTypes++;		
		$neededHeight += 31;
	}	

// if first product, start product section of page	
	if ( $pageNum == 0 ) {
		startProductSection();	
		$column=1;
		$pageNum=1;
	}	

/********** DEBUG AREA **************/
//$sum=$printedHeight + $neededHeight;
//if ( $sort_products['sp_productID'] == 38 )
//	echo "printedHeight=$printedHeight neededHeight=$neededHeight sum=$sum";
/********* END DEBUG AREA ***********/

// column/page break
	if ( $printedHeight + $neededHeight > PAGE_HEIGHT )	
		if ( $column == 1 ) 
			newColumn(); 
		else
			newPage();

// get product name
	$prod_englishName = $sort_products['sp_name'];
	$prod_displayName = $prod_englishName;	
	if ( $hh_lang != 1 ) {
		$sql2 = "SELECT * FROM jtproducts_nameinfo 
				 WHERE productID = " . $sort_products['sp_productID'] . "
				 AND languageID = $hh_lang";
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		if ($row2= mysqli_fetch_assoc($result2))
			if ( $row2['name'] != "" )
				$prod_displayName = $row2['name'];
			else
				$prod_displayName = $prod_englishName;			
		else
			$prod_displayName = $prod_englishName;
	}		

// to prevent word wrap, reduce font size for lengthy product names 	
	if ( strlen($prod_displayName) > 20 ) 
		$prod_displayName = "<span style='font-size:10pt;'>" . $prod_displayName . "</span>"; 

	if ( !$isType && $sort_products['sp_instock'] == 0 ) {
		$outOfStockStartTag = "<s>";
		$outOfStockEndTag   = "</s>";
		$outOfStockPrefix   = "OOS_";
	} else {
		$outOfStockStartTag = "";
		$outOfStockEndTag   = "";
		$outOfStockPrefix   = "";
	}

// start print row  
	echo "<tr><td class='embedded_tc' style='width:36px;'>\n"; 
	if ( $numTypes  == 0 ) {
	
// print check box or blank line for personal products
		if ( $sort_products['sp_personal'] == "yes" ) 
			echo "<img style='margin:4px 0px 0px 2px;vertical-align:text-bottom;vertical-align:middle;width:36px;height:20px;' src='" . ROOT . "images/" . $outOfStockPrefix . "line.gif' >\n";
		else 
			echo "<img style='margin:4px 0px 0px 2px;vertical-align:text-bottom;vertical-align:middle;width:36px;height:20px;' src='" . ROOT . "images/" . $outOfStockPrefix . "u_box.gif' >\n";
	} else { 
		
// print gray horizontal line, then vertical bar for products with multiple types
		$barheight = 0;
		if ( $hh_lang != 1 )  
			$barheight += 20;  
		for ( $loop = 1; $loop<=$numTypes; $loop++ ) 
			$barheight += 24; 

		echo "<img style='margin:4px 0px 0px 2px;width:36px;height:20px;' src='" . ROOT . "images/grayline.gif'>\n";
		echo "<img style='margin:0px 0px 0px 2px;width:36px;height:" . $barheight . "px;' src='" . ROOT . "images/graybar.gif'>\n"; 
	}	
	echo "</td>\n";

	
// print product name (strike through if out of stock)  
	echo "<td class='embedded_tl' style='padding-left:3px;vertical-align:bottom;width:" . $prodWidth . "px;'>";
	echo $outOfStockStartTag . $prod_displayName . $outOfStockEndTag . "\n"; 
  
// print max quantity or shared portion message  
	if ( $sort_products['sp_personal'] == "yes" ) {
		$message = " <span style='font-size:8pt;'><i>(" . 
		($customTextMsg['print_shopping_maxqty'][$hh_lang] == '' ? $customTextMsg['print_shopping_maxqty'][1] : $customTextMsg['print_shopping_maxqty'][$hh_lang]) . $quantity . ")</i></span>\n";
	} else {  

		if ( $sort_products['sp_qtyID'] == NULL )
			$SP_id = 0;
		else
			$SP_id = $sort_products['sp_qtyID'];
		$sql = "SELECT * FROM shared_portions WHERE SP_id = $SP_id";
		$sql_result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		if ($row = mysqli_fetch_array($sql_result))
			$SharedPortion = $row['SP_name'];
		else
			$SharedPortion = "!ERROR - jtproducts table";

		$message = " <span style='font-size:8pt;'><i>(" . $SharedPortion . ")</i></span>\n";
	}  
	echo $outOfStockStartTag . $message . $outOfStockEndTag;

// print translation for non-english language households
	if ( $hh_lang != 1 )
		echo " <br><span style='font-size:10pt;'>" . $outOfStockStartTag . $prod_englishName . $outOfStockEndTag . "</span>\n";
	
// If there are types for this product, print them out below, w/ the line/box next to them.
	if ( $numTypes > 0 ) { 
		$numOutOfStock = 0;
		$tempVal = $sort_products['sp_instock'];		
		$sql2 = "SELECT * from jtproducts_typeinfo WHERE productID = " . $sort_products['sp_productID'] . " ORDER BY typenum ASC";
		$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
		while ($row2= mysqli_fetch_assoc($result2)) {
			$type = $row2['type'];
			$typenum = intval($row2['typenum']);
			$type = $row2['type'];
			$instock = $tempVal %2;
			$tempVal /= 2;	  

			if ( $instock == 0 ) { 
				$outOfStockStartTag = "<s>";  
				$outOfStockEndTag = "</s>"; 
				$outofStockPrefix = "OOS_"; 
				$numOutOfStock ++;				
			} else { 
				$outOfStockStartTag = "";     
				$outOfStockEndTag = "";     
				$outofStockPrefix = "";  
			}
						 
			if ( $sort_products['sp_personal'] == "yes" ) 
				echo "<br><img style='margin:4px 0px 0px 2px;vertical-align:text-bottom;width:36px;height:20px;' src='" . ROOT . "images/" . $outofStockPrefix . "line.gif'>\n";
			else
				echo "<br><img style='margin:4px 0px 0px 2px;vertical-align:text-bottom;width:36px;height:20px;' src='" . ROOT . "images/" . $outofStockPrefix . "u_box.gif'>\n";
													 
			echo " <span style='font-size:10pt;'>" . $outOfStockStartTag . $type . $outOfStockEndTag . "</span>\n";
		}
	}
	echo "</td>\n";  
 
// print pantry-use-only box  
	$OutOfStockPrefix = "OOS_";
	if ( $numTypes == 0 ) {	
		if ( $sort_products['sp_instock'] == 1 ) 
		  $OutOfStockPrefix = "";
	} elseif ( $numTypes > $numOutOfStock ) 
		$OutOfStockPrefix = "";
	echo "<td class='embedded_tc' style='width:36px;'> \n";
	echo "<img style='margin: 4px 2px 0px 0px;width:36px;height:20px;' src='" . ROOT . "images/" . $OutOfStockPrefix . "round_rectangle.gif'></td>\n";

// end row	
	echo "</tr>\n";
	
// increment printed height
	$printedHeight += $neededHeight;
}

/* written for version 3.5.2 update */
function newColumn()
{
global $column,	$printedHeight;

	echo "</table></td><td class='shopCol352'><table cellspacing='0' border='0' style='width:100%;'>";
	$column=2;
	$printedHeight=135;		// header offset
}

/* written for version 3.5.2 update */
function newPage()
{
global $conn, $hhID, $hh_fullname, $hh_lang, $customTextMsg, $pantryID, $column, $pageNum, $printedHeight;

	echo "</table></td></tr></table></td></tr></table>";
	ShopList_PrintHeader( $hhID, $hh_fullname, $hh_lang );
	startProductSection();
	$column=1;	
	$printedHeight=135;		// header offset
	$pageNum++;
}

/* written for version 3.5.2 update */
function startProductSection() {
	echo "
	<tr><td colspan='1' style='vertical-align:top;border-width:0;width:100%;'>
	<table cellspacing='0' border='0' style='display:inline-block;'><tr><td class='shopCol352' style='overflow:visible;'>
	<table cellspacing='0' border='0' style='overflow:visible;width:100%;'>";	
}	

/* written for version 3.5.2 update */
function ShopList_PrintOtherProduct( ) 
{
	global 	$conn, 
			$hhID,	
			$products,
			$customTextMsg,
			$hh, 
			$hh_lang,
			$hh_fullname,
			$printedHeight,
			$column,		
			$pageNum;


	$quantity = 1;
	$numTypes = 0;	
	$isType = 0;	
	$isPortionLimit = 0;
	$prodWidth = ( PAGE_WIDTH / 2 ) - 72;
	

// calculate needed height in pixels	
	if ( $hh_lang == 1 )
		$neededHeight = 31;
	else 
		$neededHeight= 49; // non-english language household, add 18 pixels for translation

// if first product, start product section of page	
	if ( $pageNum == 0 ) {
		startProductSection();	
		$column=1;
		$pageNum=1;
	}	

// column/page break
	if ( $printedHeight + $neededHeight > PAGE_HEIGHT )	
		if ( $column == 1 ) 
			newColumn(); 
		else
			newPage();

// start print row  
	echo "<tr><td class='embedded_tc' style='width:36px;'>\n"; 
	echo "<img style='margin:4px 0px 0px 2px;vertical-align:text-bottom;vertical-align:middle;width:36px;height:20px;' src='" . ROOT . "images/u_box.gif' >\n";
	echo "</td>\n";
	
// print "other" product
	echo "
	<td class='embedded_tl' style='padding-left:3px;vertical-align:bottom;width:" . $prodWidth . "px;'>
	
		<table cellspacing='0'><tr>
		<td style='font-size:12pt;border-width:0px;'>" . $customTextMsg['other_prd'][$hh_lang];
		if ( $hh_lang != 1 )
			echo "<br><span style='font-weight:normal;font-size:0.9em;'>other</span>";
		echo "
		</td>
		<td style='width:5px;border-width:0px;'>&nbsp;</td>
		<td style='padding-left:5px;width:165px;border-width:0 0 1px 0;'>&nbsp;</td></tr>
		<tr><td style='border-width:0;vertical-align:text-top;font-size:0.8em;'></td>
		<td style='border-width:0px;'></td>
		<td style='text-align:center;border-width:0;font-size:0.8em;'><i>(" . $customTextMsg['other_inst'][$hh_lang] . ")</i></td>	
		</tr></table>
		
	</td>";
	
// print pantry-use-only box  
	echo "<td class='embedded_tc' style='width:36px;'> \n";
	echo "<img style='margin: 4px 2px 0px 0px;width:36px;height:20px;' src='" . ROOT . "images/round_rectangle.gif'></td>\n";

// end row	
	echo "</tr>\n";
	
// increment printed height
	$printedHeight += $neededHeight;
}


function ShopList_PrintFooter( $page, $more ) {
// echo "<p style='page-break-after:always'>\n";
  echo "<p style='margin:12px;'>\n";
  echo "<center><font size=-2>\n";
  echo "  Pg-" . $page . " -- \n";
  if ( $more == "more" ) { echo "<i>Continued on next page...</i>\n"; }
                    else { echo "<i>List complete</i>\n"; }
  echo "</font></center>\n";
  echo "</p>\n";
}

/**
  * written on 6-19-12 -mlr
  */
function writeConsumptionRow() {
	global 	$conn,
			$sort_products,
			$hhID,
			$pantryID,	
			$today,
			$time;
	
	$pantry_id		=$pantryID;
	$product_id		=$sort_products['sp_productID'];
	$shelf			=$sort_products['sp_shelf'];
	$bin			=$sort_products['sp_bin']; 
//	$instock		=$sort_products['sp_instock'];
	$quantity_oked	=$sort_products['sp_num_eligible'];
	
// 1-19-13: version 3.4 upgrade - if a product is 'instock', the number of products 'instock' 
// is equal to the number the guest is eligible for. This value may change during data entry 
// in HouseholdsHistory.php
	if ( $sort_products['sp_instock'] <> NULL && $sort_products['sp_instock'] > 0 )
		$instock = $quantity_oked;
	else
		$instock = 0;	
	
    $sql = "INSERT INTO consumption
			(household_id, 
			pantry_id, 
			product_id, 
			shelf, 
			bin, 
			date, 
			time, 
			instock, 
			quantity_oked)
			
			VALUES ($hhID,
					$pantry_id,
                    $product_id,
					$shelf,
					$bin,					  
                    '$today',
                    '$time',
					$instock,
					$quantity_oked)";
					
	$AddOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
}

/*
* written: 1-29-14 			-mlr
*/
function updateLastActiveDate() {
global 	$conn, $hhID, $today;

	$sql = "UPDATE household
			SET lastactivedate = '$today' 
			WHERE id = $hhID";
	$updateOk = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
}	

