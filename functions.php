<?php
/**
 * functions.php
 * written: 11/11/2020
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do so, 
 * subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * 3-23-2021: v 4.1 update - add function pieColors().		-mlr
*/ 

function getDB($config) {
	
	try {
		$db = new PDO("mysql:dbname=$config[dbname];host=$config[host]", $config['user'], $config['pswd']);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		die('Connection failed: ' . $e->getMessage());				// development
//		die("CONNECTION ERROR! Check database configuration.");		// production
	}	

	return $db;
}	

function bFooter() {
// uses Bootstrap 4 framework to display footer with PEPartnership logo and copyright.		
	echo "
	<footer class='footer fixed-bottom p-4 bg-gray-7 text-light'>

			<a href='https://www.essentialspantry.org' style='text-decoration:none;' target='_blank'>
			<img style='border:0px;margin:0px 10px 2px 3px;vertical-align:-50%;' src='" . ROOT . "images/logo.png' alt='PEP logo' height='40' /></a>

			<div class='pt-3'>
				&copy; " . date("Y") . " Pepartnership, Inc.
			</div>
	</footer>";	
}

function writeUserLog( $db, $date, $time, $household_id, $db_table, $table_id, $action, $shopping_date="0000-00-00", $shopping_time="00:00:00" ) {
	global $control;
	
	$date_time=$date . " " . $time;
 	$sql = "INSERT INTO user_log 
			(date_time, 
			user_id, 
			pantry_id, 
			household_id, 
			db_table, 
			table_id, 
			shopping_date, 
			shopping_time, 
			action, 
			ip_address )
			
 			VALUES  
			(:date_time, 
			:user_id, 
			:pantry_id, 
			:household_id, 
			:db_table, 
			:table_id, 
			:shopping_date, 
			:shopping_time, 
			:action, 
			:ip_address )";
			
	$stmt= $db->prepare($sql);
	$stmt->bindParam(':date_time', $date_time, PDO::PARAM_STR);	
	$stmt->bindParam(':user_id', $control['users_id'], PDO::PARAM_INT);
	$stmt->bindParam(':pantry_id', $control['users_pantry_id'], PDO::PARAM_INT);
	$stmt->bindParam(':household_id', $household_id, PDO::PARAM_INT);
	$stmt->bindParam(':db_table', $db_table, PDO::PARAM_STR);
	$stmt->bindParam(':table_id', $table_id, PDO::PARAM_INT);
	$stmt->bindParam(':shopping_date', $shopping_date, PDO::PARAM_STR);	
	$stmt->bindParam(':shopping_time', $shopping_time, PDO::PARAM_STR);	
	$stmt->bindParam(':action', $action, PDO::PARAM_STR);
	$stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);		
	$stmt->execute();
}	

function writeSigninDate( $db, $id, $date, $time ) {	

	$datetime="$date $time";
	$sql = "UPDATE users SET last_signin =? WHERE id=?"; 
	$stmt= $db->prepare($sql);
	$stmt->execute([$datetime, $id]);	
}

function validUser() {
	global $config;
	
	session_start(['cookie_lifetime' => 86400]);	
	
	if (isset($_COOKIE['p_SID'])) {
		$arr=array();
		$arr['users_id'] = base64_decode($_COOKIE['p_SID']) / VERIFICATION_KEY;
		
// in case of early garbage collection, reload pantry_id from table		
		if (isset($_SESSION['users_pantry_id'])) 		
			$arr['users_pantry_id']= $_SESSION['users_pantry_id'];
		else {
			$db=getDB($config);	
			if ($users=getUsersRow( $db, $arr['users_id'] )) {
				$arr['users_pantry_id'] = $users['pantry_id'];
				$_SESSION['users_pantry_id'] = $users['pantry_id'];				
			} else
				die ("SESSION ERROR in function validUser()");	
		}	
		return $arr;
	} else
		return false;		
}

function fillControlArray($control, $config, $screen) {
	
	$arr=$control;
//	$arr=array();
	
	$arr['isTraining']=$config['isTraining'];
	$arr['isTesting']=$config['isTesting'];
	$control['isPublic']=0;	
	$arr['hhID']=0;	
	$arr['db']=getDB($config);

	if (isset($arr['users_id'])) {
		$stmt = $arr['db']->prepare("select * from users where id = :id");
		$stmt->execute(array(':id' => $arr['users_id']));
		$total = $stmt->rowCount();			
		if ($total > 0) {
			$users = $stmt->fetch();
			$arr['users_id']=$users['id'];
			$arr['access_level']=$users['access_level'];
			$arr['hostFName'] = $users['firstname'];
		} else {	
			$arr['users_id']=0;
			$arr['access_level']=1;
			$arr['hostFName'] = "";
		}	
	}

	$arr["focus"] = "firstname";	
	$arr['firstname'] = "";
	$arr['lastname'] = "";	
	$arr['id'] = "";
	$arr['formErr'] = "";	
	$arr['err'] = 0;	

	if (isset($_POST['clear'])) { 
		$arr['hhID']=0;	
		$arr['firstname'] = "";
		$arr['lastname'] = "";
		$arr['id'] = "";		
		
	} elseif (isset($_POST['search'])) {
		$arr['hhID'] = exactMatch($arr['db']);
		$arr['firstname'] = $_POST['firstname'];
		$arr['lastname'] = $_POST['lastname'];

	} elseif (isset($_POST['hhID']))
		$arr['hhID'] = $_POST['hhID'];
		
	elseif (isset($_GET['hhID']))
		$arr['hhID'] = $_GET['hhID'];
		
	if ($arr['hhID'] != 0) { 
		$sql = "SELECT * FROM household	WHERE id = :id"; 
		$stmt = $arr['db']->prepare($sql);
		$stmt->bindParam(':id', $arr['hhID'], PDO::PARAM_INT);	
		$stmt->execute();
		$household = $stmt->fetch();			
		$arr['firstname']=stripslashes(ucname($household['firstname']));
		$arr['lastname']=stripslashes(ucname($household['lastname']));
		$arr['id']=$arr['hhID'];	
	}	
	
	$arr['errCode'] = 0;	
	if (isset($_GET['errCode']))
		$arr['errCode']=$_GET['errCode'];
	elseif (isset($_POST['errCode']))
		$arr['errCode']=$_POST['errCode'];	
	
	$arr["tab"]="profile";
	if (isset($_POST['tab']))		
		$arr["tab"] = $_POST['tab'];
	elseif (isset($_GET['tab']))
		$arr["tab"] = $_GET['tab'];	
		
// product fields
	if ( isset($_GET['productID']) )
		$arr['productID']=$_GET['productID'];
	elseif ( isset($_POST['productID']) )		
		$arr['productID']=$_POST['productID'];
	else	
		$arr['productID']=0;

// sort fields	
	$arr['order']="asc";
	$arr['field']="name";	
	if ( isset($_GET['field']) ) {
		$arr['field']=$_GET['field'];
	} elseif ( isset($_POST['field']) )	{	
		$arr['field']=$_POST['field'];
			
	} elseif ($screen== "products") {
		if ($arr['tab'] == "setup")
			$arr['field']="shelf_bin";	
		
	} elseif ($screen== "tools") {
		if ($arr['tab'] == "userlog") 
			$arr['field']="date_time";	
		$arr['order']="desc";		
	}

	if ($screen== "pantries") 
		$arr['tab'] = "pantries";	

	if ( isset($_GET['order']) )
		$arr['order']=$_GET['order'];
	elseif ( isset($_POST['order']) )		
		$arr['order']=$_POST['order'];
		
	$arr['isreg']=( (isset($_GET['new']) && $_GET['new'] == "regmembers") || isset($_POST['newreg']) || isset($_POST['isreg']) || isset($_GET['isreg']));		

	return $arr;
}

function loadAccessLevels() {
	global $control;
	
	$arr=$control;	
	
	$sql = "SELECT * FROM access_levels WHERE level= :level";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':level', $control['access_level'], PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) {	
		$row=$stmt->fetch();	
		$arr['users_update']=$row['users_update'];
		$arr['users_delete']=$row['users_delete'];
		$arr['users_browse']=$row['users_browse'];
		$arr['access_level_update']=$row['access_level_update'];
		$arr['access_level_delete']=$row['access_level_delete'];
		$arr['access_level_browse']=$row['access_level_browse'];	
		$arr['hh_profile_update']=$row['hh_profile_update'];
		$arr['hh_profile_delete']=$row['hh_profile_delete'];
		$arr['hh_profile_browse']=$row['hh_profile_browse'];
		$arr['hh_members_update']=$row['hh_members_update'];
		$arr['hh_members_delete']=$row['hh_members_delete'];
		$arr['hh_members_browse']=$row['hh_members_browse'];
		$arr['hh_eligible_update']=$row['hh_eligible_update'];
		$arr['hh_eligible_delete']=$row['hh_eligible_delete'];
		$arr['hh_eligible_browse']=$row['hh_eligible_browse'];
		$arr['hh_history_update']=$row['hh_history_update'];
		$arr['hh_history_delete']=$row['hh_history_delete'];
		$arr['hh_history_browse']=$row['hh_history_browse'];
		$arr['prod_def_update']=$row['prod_def_update'];
		$arr['prod_def_delete']=$row['prod_def_delete'];
		$arr['prod_def_browse']=$row['prod_def_browse'];
		$arr['instock_update']=$row['instock_update'];
		$arr['instock_delete']=$row['instock_delete'];
		$arr['instock_browse']=$row['instock_browse'];
		$arr['prod_setup_update']=$row['prod_setup_update'];
		$arr['prod_setup_delete']=$row['prod_setup_delete'];
		$arr['prod_setup_browse']=$row['prod_setup_browse'];
		$arr['languages_update']=$row['languages_update'];
		$arr['languages_delete']=$row['languages_delete'];
		$arr['languages_browse']=$row['languages_browse'];
		$arr['measures_update']=$row['measures_update'];
		$arr['measures_delete']=$row['measures_delete'];
		$arr['measures_browse']=$row['measures_browse'];
		$arr['containers_update']=$row['containers_update'];
		$arr['containers_delete']=$row['containers_delete'];
		$arr['containers_browse']=$row['containers_browse'];
		$arr['shelters_update']=$row['shelters_update'];
		$arr['shelters_delete']=$row['shelters_delete'];
		$arr['shelters_browse']=$row['shelters_browse'];
		$arr['changepantry_browse']=$row['changepantry_browse'];
		$arr['userlog_browse']=$row['userlog_browse'];
		$arr['convert_browse']=$row['convert_browse'];		
		$arr['pantries_update']=$row['pantries_update'];
		$arr['pantries_delete']=$row['pantries_delete'];
		$arr['pantries_browse']=$row['pantries_browse'];
		$arr['themes_update']=$row['themes_update'];
		$arr['themes_delete']=$row['themes_delete'];
		$arr['themes_browse']=$row['themes_browse'];
		$arr['advanced_update']=$row['advanced_update'];
		$arr['advanced_delete']=$row['advanced_delete'];
		$arr['advanced_browse']=$row['advanced_browse'];
		$arr['reports_con_browse']=$row['reports_con_browse'];
		$arr['reports_demo_browse']=$row['reports_demo_browse'];
		$arr['reports_charts_browse']=$row['reports_charts_browse'];
		$arr['reports_admin_browse']=$row['reports_admin_browse'];	
	} else {
	
		$arr['users_update']="";
		$arr['users_delete']="";
		$arr['users_browse']="";
		$arr['access_level_update']="";
		$arr['access_level_delete']="";
		$arr['access_level_browse']="";
		$arr['hh_profile_update']="";
		$arr['hh_profile_delete']="";
		$arr['hh_profile_browse']="";
		$arr['hh_members_update']="";
		$arr['hh_members_delete']="";
		$arr['hh_members_browse']="";
		$arr['hh_eligible_update']="";
		$arr['hh_eligible_delete']="";
		$arr['hh_eligible_browse']="";
		$arr['hh_history_update']="";
		$arr['hh_history_delete']="";
		$arr['hh_history_browse']="";
		$arr['prod_def_update']="";
		$arr['prod_def_delete']="";
		$arr['prod_def_browse']="";
		$arr['instock_update']="";
		$arr['instock_delete']="";
		$arr['instock_browse']="";
		$arr['prod_setup_update']="";
		$arr['prod_setup_delete']="";
		$arr['prod_setup_browse']="";
		$arr['languages_update']="";
		$arr['languages_delete']="";
		$arr['languages_browse']="";
		$arr['measures_update']="";
		$arr['measures_delete']="";
		$arr['measures_browse']="";
		$arr['containers_update']="";
		$arr['containers_delete']="";
		$arr['containers_browse']="";
		$arr['shelters_update']="";
		$arr['shelters_delete']="";
		$arr['shelters_browse']="";
		$arr['changepantry_browse']="";
		$arr['userlog_browse']="";
		$arr['convert_browse']="";
		$arr['pantries_update']="";
		$arr['pantries_delete']="";
		$arr['pantries_browse']="";
		$arr['themes_update']="";
		$arr['themes_delete']="";
		$arr['themes_browse']="";
		$arr['advanced_update']="";
		$arr['advanced_delete']="";
		$arr['advanced_browse']="";
		$arr['reports_con_browse']="";
		$arr['reports_demo_browse']="";
		$arr['reports_charts_browse']="";
		$arr['reports_admin_browse']="";
	}	

	return $arr;
}	

function displayAlert($msg) {
	echo"
		<center>
		<div class='alert alert-danger border border-dark text-center w-75 mt-3' role='alert'>$msg</div>
		</center>\n";	
}

function displayAlert2($color, $msg) {
	
	echo "

	<div class='alert alert-$color alert-dismissible border border-$color show' role='alert' >$msg
		<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>\n";
}

function getUsersRow( $db, $id ) {

	$row=false;
	$sql = "SELECT * FROM users WHERE id= :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0)  
		$row = $stmt->fetch();
	return $row;
}

function getHouseholdRow( $db, $id ) {

	$row=false;
	$sql = "SELECT * FROM household WHERE id= :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0)  
		$row = $stmt->fetch();
	return $row;
}

function getMemberRow( $db, $id ) {

	$row=false;
	$sql = "SELECT * FROM members WHERE id = :id";	
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);						
	$stmt->execute();	
	$total = $stmt->rowCount();			
	if ($total > 0)  
		$row = $stmt->fetch();
	return $row;	
}	

function getLanguage( $db, $id ) {

	$name="";
	$sql = "SELECT * FROM languages WHERE id = :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) { 
		$languages = $stmt->fetch();
		$name=$languages['name']; 
	}	
	return $name;
}	

function getContainer( $db, $id ) {

	$name="";
	$sql = "SELECT * FROM containers WHERE id = :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) { 
		$row = $stmt->fetch();
		$name=$row['name']; 
	}	
	return $name;
}	

function getMeasureRow( $db, $id ) {

	$row=false;
	$name="";
	$sql = "SELECT * FROM measures WHERE id = :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) 
		$row = $stmt->fetch();

	return $row;
}	

function getPantryRow( $db, $pantryID ) {

	$pantries=array();
	$sql = "SELECT * FROM pantries WHERE id = :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $pantryID, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0)  {
		$pantries = $stmt->fetch();
		return $pantries;
	} else 
		return false;
}	

function getProductsNameinfoRow( $db, $productID, $languageID ) {

	$pantries=array();
	$sql = "SELECT * FROM products_nameinfo WHERE productID = :productID AND languageID=:languageID"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);	
	$stmt->bindParam(':languageID', $languageID, PDO::PARAM_INT);		
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0)  {
		$products_nameinfo = $stmt->fetch();
		return $pantries;
	} else 
		return false;
}

function getShelterRow( $db, $id ) {

	$shelters=array();
	$sql = "SELECT * FROM shelters WHERE id= :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) { 
		$shelters = $stmt->fetch();
		return $shelters;
	} else 
		return false;
}	

function getPantryTypeInfoRow( $db, $productID, $typenum ) {

	$row=false;
	$sql = "SELECT * FROM products_typeinfo WHERE productID = :productID AND typenum= :typenum"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);	
	$stmt->bindParam(':typenum', $typenum, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0)  
		$row = $stmt->fetch();
	return $row;
}	

function getTableRow( $table, $db, $id ) {

	if ($table == "containers")
		$row = [ 'id' => $id, 'name' => ""];	
	else
		$row = [ 'id' => $id, 'name' => "", 'abbrev' => ""];	
	
	$sql = "SELECT * FROM $table WHERE id = :id"; 
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0)  
		$row = $stmt->fetch();
	return $row;
}	

function yesNoRadio($name, $value) {
// works with $value: { enum('Yes','No') or tinyint (1,0 boolean) } 	
	
	$yc ="";
	$nc ="";
	
	if (ucname($value) == "Yes")
		$yc = "checked='checked'";
	else
		$nc = "checked='checked'";		

	echo "
	<div class='icheck-ron-burgundy icheck-inline'>
	  <input type='radio' id='$name" . "1" . "' name='$name' value='Yes' $yc>
	  <label for ='$name" . "1" . "'>Yes</label>
	</div>
	<div class='icheck-ron-burgundy icheck-inline'>
	  <input type='radio' id='$name" . "2" . "' name='$name' value='No' $nc>
	  <label for ='$name" . "2" . "'>No</label>
	</div>\n";	
}

function isValidPhone($phone) {
	
	$retVal=true;
	if (!empty($phone)) {
		// Allow +, - and . in phone number
		$filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
		// Remove "-" from number
		$phone_to_check = str_replace("-", "", $filtered_phone_number);
		// Check the lenght of number
		// This can be customized if you want phone number from a specific country
		if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) 
			$retVal=false;
	}
	return $retVal;
}

function inAnotherHousehold($firstname, $lastname, $dob) {
	global $control;
	
	$arr = [
		'memberID'	=> 0,
		'otherHHID'		=> 0,
		'otherHHFirst' 	=> "",
		'otherHHLast'	=> ""
	];	

//			$sql = "SELECT * FROM members WHERE (firstname LIKE :firstname ";
//			for ($n = 1; $n <= 15; $n++) 
//				$sql .= "OR nick" . $n . " LIKE :firstname ";
//			$sql.=")
//				AND ( lastname LIKE :lastname
//				OR sur1 LIKE :lastname
//				OR sur2 LIKE :lastname
//				OR sur3 LIKE :lastname
//				OR sur4 LIKE :lastname )	
//				AND initial LIKE :initial
//				AND $dobQ";	
	
	$sql = "SELECT * FROM members WHERE (firstname = :firstname ";
			for ($n = 1; $n <= 15; $n++) 
				$sql .= "OR nick" . $n . " = :firstname ";
			$sql.=")	
			AND lastname = :lastname
			AND householdID <> :householdID			
			AND dob = :dob
			AND in_household = 'Yes'";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);		
	$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
	$stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);	
	$stmt->bindParam(':dob', $dob, PDO::PARAM_STR);		
	$stmt->execute();	
	$total = $stmt->rowCount();			
	if ($total > 0) {	
		$other=$stmt->fetch();
		$sql = "SELECT * FROM members WHERE householdID = :householdID AND is_primary=1";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':householdID', $other['householdID'], PDO::PARAM_INT);	
		$stmt->execute();			
		$row=$stmt->fetch();	
		$arr['memberID']	=$other['id'];
		$arr['otherHHID'] = $row['householdID'];
		$arr['otherHHFirst'] = stripslashes(ucname($row['firstname']));
		$arr['otherHHLast'] = stripslashes(ucname($row['lastname']));
	}	
	return $arr;
}	

function hasDuplicateMember() {
	global $control;
	
	$retval = false;
	$sql = "SELECT * FROM members WHERE householdID = :householdID AND in_household = 'Yes'";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);		
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach ($result as $members) {	
		$duplicate=inAnotherHousehold($members['firstname'], $members['lastname'], $members['dob']);
		if ($duplicate['memberID'])
			$retval="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Household has duplicate members.";
	}
	return $retval;
}	

function selectGender( $selectName, $selectedAnswer ) {
	
	$maleSelect="";
	$femaleSelect="";
	if ( strtolower($selectedAnswer) == "female" )
		$femaleSelect="selected";		
	else
		$maleSelect="selected";
	
	echo "
    <select name = '$selectName' class='form-control bg-gray-1'>
    <option $maleSelect value ='male' male>male</option>
    <option $femaleSelect value ='female' female>female</option>
    </select>";
}

function SelectState($SelectName,$InState) {

?>
    <SELECT NAME = <?php echo $SelectName; ?> class='form-control bg-gray-1'>

    <OPTION VALUE =" " >--</OPTION>
    <OPTION <?php if ($InState == "AL") { echo "SELECTED"; } ?> VALUE ="AL" AL> AL </OPTION>
    <OPTION <?php if ($InState == "AK") { echo "SELECTED"; } ?> VALUE ="AK" AK> AK </OPTION>
    <OPTION <?php if ($InState == "AZ") { echo "SELECTED"; } ?> VALUE ="AZ" AZ> AZ </OPTION>
    <OPTION <?php if ($InState == "AR") { echo "SELECTED"; } ?> VALUE ="AR" AR> AR </OPTION>
    <OPTION <?php if ($InState == "CA") { echo "SELECTED"; } ?> VALUE ="CA" CA> CA </OPTION>
    <OPTION <?php if ($InState == "CO") { echo "SELECTED"; } ?> VALUE ="CO" CO> CO </OPTION>
    <OPTION <?php if ($InState == "CT") { echo "SELECTED"; } ?> VALUE ="CT" CT> CT </OPTION>
    <OPTION <?php if ($InState == "DE") { echo "SELECTED"; } ?> VALUE ="DE" DE> DE </OPTION>
    <OPTION <?php if ($InState == "FL") { echo "SELECTED"; } ?> VALUE ="FL" FL> FL </OPTION>
    <OPTION <?php if ($InState == "GA") { echo "SELECTED"; } ?> VALUE ="GA" GA> GA </OPTION>
    <OPTION <?php if ($InState == "HI") { echo "SELECTED"; } ?> VALUE ="HI" HI> HI </OPTION>
    <OPTION <?php if ($InState == "ID") { echo "SELECTED"; } ?> VALUE ="ID" ID> ID </OPTION>
    <OPTION <?php if ($InState == "IL") { echo "SELECTED"; } ?> VALUE ="IL" IL> IL </OPTION>
    <OPTION <?php if ($InState == "IN") { echo "SELECTED"; } ?> VALUE ="IN" IN> IN </OPTION>
    <OPTION <?php if ($InState == "IA") { echo "SELECTED"; } ?> VALUE ="IA" IA> IA </OPTION>
    <OPTION <?php if ($InState == "KS") { echo "SELECTED"; } ?> VALUE ="KS" KS> KS </OPTION>
    <OPTION <?php if ($InState == "KY") { echo "SELECTED"; } ?> VALUE ="KY" KY> KY </OPTION>
    <OPTION <?php if ($InState == "LA") { echo "SELECTED"; } ?> VALUE ="LA" LA> LA </OPTION>
    <OPTION <?php if ($InState == "ME") { echo "SELECTED"; } ?> VALUE ="ME" ME> ME </OPTION>
    <OPTION <?php if ($InState == "MD") { echo "SELECTED"; } ?> VALUE ="MD" MD> MD </OPTION>
    <OPTION <?php if ($InState == "MA") { echo "SELECTED"; } ?> VALUE ="MA" MA> MA </OPTION>
    <OPTION <?php if ($InState == "MI") { echo "SELECTED"; } ?> VALUE ="MI" MI> MI </OPTION>
    <OPTION <?php if ($InState == "MN") { echo "SELECTED"; } ?> VALUE ="MN" MN> MN </OPTION>
    <OPTION <?php if ($InState == "MS") { echo "SELECTED"; } ?> VALUE ="MS" MS> MS </OPTION>
    <OPTION <?php if ($InState == "MO") { echo "SELECTED"; } ?> VALUE ="MO" MO> MO </OPTION>
    <OPTION <?php if ($InState == "MT") { echo "SELECTED"; } ?> VALUE ="MT" MT> MT </OPTION>
    <OPTION <?php if ($InState == "NE") { echo "SELECTED"; } ?> VALUE ="NE" NE> NE </OPTION>
    <OPTION <?php if ($InState == "NV") { echo "SELECTED"; } ?> VALUE ="NV" NV> NV </OPTION>
    <OPTION <?php if ($InState == "NH") { echo "SELECTED"; } ?> VALUE ="NH" NH> NH </OPTION>
    <OPTION <?php if ($InState == "NJ") { echo "SELECTED"; } ?> VALUE ="NJ" NJ> NJ </OPTION>
    <OPTION <?php if ($InState == "NM") { echo "SELECTED"; } ?> VALUE ="NM" NM> NM </OPTION>
    <OPTION <?php if ($InState == "NY") { echo "SELECTED"; } ?> VALUE ="NY" NY> NY </OPTION>
    <OPTION <?php if ($InState == "NC") { echo "SELECTED"; } ?> VALUE ="NC" NC> NC </OPTION>
    <OPTION <?php if ($InState == "ND") { echo "SELECTED"; } ?> VALUE ="ND" ND> ND </OPTION>
    <OPTION <?php if ($InState == "OH") { echo "SELECTED"; } ?> VALUE ="OH" OH> OH </OPTION>
    <OPTION <?php if ($InState == "OK") { echo "SELECTED"; } ?> VALUE ="OK" OK> OK </OPTION>
    <OPTION <?php if ($InState == "OR") { echo "SELECTED"; } ?> VALUE ="OR" OR> OR </OPTION>
    <OPTION <?php if ($InState == "PA") { echo "SELECTED"; } ?> VALUE ="PA" PA> PA </OPTION>
    <OPTION <?php if ($InState == "RI") { echo "SELECTED"; } ?> VALUE ="RI" RI> RI </OPTION>
    <OPTION <?php if ($InState == "SC") { echo "SELECTED"; } ?> VALUE ="SC" SC> SC </OPTION>
    <OPTION <?php if ($InState == "SD") { echo "SELECTED"; } ?> VALUE ="SD" SD> SD </OPTION>
    <OPTION <?php if ($InState == "TN") { echo "SELECTED"; } ?> VALUE ="TN" TN> TN </OPTION>
    <OPTION <?php if ($InState == "TX") { echo "SELECTED"; } ?> VALUE ="TX" TX> TX </OPTION>
    <OPTION <?php if ($InState == "UT") { echo "SELECTED"; } ?> VALUE ="UT" UT> UT </OPTION>
    <OPTION <?php if ($InState == "VT") { echo "SELECTED"; } ?> VALUE ="VT" VT> VT </OPTION>
    <OPTION <?php if ($InState == "VA") { echo "SELECTED"; } ?> VALUE ="VA" VA> VA </OPTION>
    <OPTION <?php if ($InState == "WA") { echo "SELECTED"; } ?> VALUE ="WA" WA> WA </OPTION>
    <OPTION <?php if ($InState == "WV") { echo "SELECTED"; } ?> VALUE ="WV" WV> WV </OPTION>
    <OPTION <?php if ($InState == "WI") { echo "SELECTED"; } ?> VALUE ="WI" WI> WI </OPTION>
    <OPTION <?php if ($InState == "WY") { echo "SELECTED"; } ?> VALUE ="WY" WY> WY </OPTION>
    </SELECT>

<?php
}

function isValidDate($date, $format = 'Y-m-d H:i:s') {
	
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function editZipcode( $db, $city, $county, $state, $zipcode ) {
	global $inZipFive, $inZipFour;

	$retArr = array();
	$inZipFive="";
	$inZipFour="";	
	$errCode=0;

// separate zip 5 from zip 4	
	if (!empty($zipcode)) {	
		if (strpos($zipcode, "-"))
			$arr = explode('-',trim($zipcode));	
		else
			$arr = explode(' ',trim($zipcode));
		$inZipFive=$arr[0];
		if (count($arr) > 1) 
			$inZipFour=$arr[1];	
	}		

	if (empty($inZipFive) || !is_numeric($inZipFive) )
		$errCode=51;
	else {

// 9-4-2019: v 3.9.2 update - modify code to accept multiple cities/counties for same zip code. 
//		(ex. Madison and Fitchburg both use 53713).			-mlr

		$found=0;

// 9-29-2019: v 3.9.3 update - separate table look-ups for zip codes with multiple cities and counties.		
//		Resolves issue where wrong error message for counties not matching zip code when zip code shared 
//		by other cities/counties.		-mlr
	
// city		
		$valid=0;
		$sql = "SELECT * FROM us_zip_codes WHERE zip = $inZipFive";		
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':firstname', $inZipFive, PDO::PARAM_STR);	
		$stmt->execute();
		$total = $stmt->rowCount();	
		$result = $stmt->fetchAll();			
		foreach($result as $us_zip_codes) {		
			$found=1;	
			if (!$valid) 
				if ( $city != strtolower($us_zip_codes['primary_city']) )	
					$errCode=36;
				else {
					$valid=1;
					$errCode=0;
				}	
		} 
		
		if (!$found)	
			$errCode=39;
// county		
		elseif (!$errCode) {
			$valid=0;
			foreach($result as $us_zip_codes) {					
				$found=1;	
				if (!$valid) {
					
// 3-26-2020: v 3.9.4.2 patch - include counties with multiple names (ex. Eau Claire).		-mlr					
//					$arr = explode(' ',trim($us_zip_codes['county']));
//					if ( $county != strtolower($arr[0]) )	
//						$errCode=37;	
					$cnty= preg_replace('/\W\w+\s*(\W*)$/', '$1', $us_zip_codes['county']);					
					if ( $county != strtolower($cnty) )	
						$errCode=37;						
					else {
						$valid=1;
						$errCode=0;
					}	
				}	
			} 
		}	

// state		
		if (!$errCode) {
			$valid=0;
			foreach($result as $us_zip_codes) {					
				$found=1;	
				if (!$valid) 
					if ( $state != strtolower($us_zip_codes['state']) )	
						$errCode=38;
					else {
						$valid=1;
						$errCode=0;
					}	
			} 
		}			
		
	}	

	$retArr['zip_five'] =$inZipFive;
	$retArr['zip_four'] =$inZipFour;
	$retArr['errCode']=$errCode;
	return $retArr;

}

function splitAddress( $address ) {
	
	$arr = ['num' => '', 'name' => ''];	
	if (!empty($address)) {	
		$a = explode(' ',trim($address));	

		if (count($a) > 1) {
			if (is_numeric($a[0])) {
				$arr['num']=$a[0];
				$arr['name']=$a[1];	
				for ($k = 2; $k < count($a); $k++) 
					$arr['name'] .= " " . $a[$k];
			} else {
				$arr['name']=$a[0];	
				for ($k = 1; $k < count($a); $k++) 
					$arr['name'] .= " " . $a[$k];
			}				
				
		} elseif (is_numeric($a[0]))
			$arr['num']=$a[0];
		else
			$arr['name']=$a[0];			
	}
	return $arr;	
}

function standardStreetName($inStreetName) {
	global $control;
	
// returns standard street name according to the United States Postal Service's Publication 28
// "Postal Addressing Standards" at http://pe.usps.com/text/pub28/welcome.htm.	

	if (! empty($inStreetName) ) {
	// first, divide the street name into direction and street suffix.
	// i.e. when $inStreetName = "East Washington Ave.",
	// $direction = "East" and $streetSuffix = "Ave"

		$inStreetName = strtolower($inStreetName);
		$inStreetName = str_replace('.','',$inStreetName);
		$streetParts = array();
		$suffix=0;
		$i=0;
		$token = strtok($inStreetName, " ");
		while ($token !== false) {
			$streetParts[$i] = $token;
			$suffix=$i;
			$i++;
			$token = strtok(" ");
		}

	// next, street direction gets abbreviated to "n", "s", "ne", ect.

		if (substr_count($inStreetName," ") > 0) {
			$direction = $streetParts[0];
					
			switch ($direction) {
				case ($direction == "north" || $direction == "n"): 
					$streetParts[0] = "n";
				break;   
				case ($direction == "south" || $direction == "s"): 
					$streetParts[0] = "s";
				break; 
				case ($direction == "east" || $direction == "e"): 
					$streetParts[0] = "e";
				break;    
				case ($direction == "west" || $direction == "w"): 
					$streetParts[0] = "w";
				break; 
				case ($direction == "northwest" || $direction == "nw"): 
					$streetParts[0] = "nw";
				break;   
				case ($direction == "northeast" || $direction == "ne"): 
					$streetParts[0] = "ne";
				break;   			
				case ($direction == "southwest" || $direction == "sw"): 
					$streetParts[0] = "sw";
				break; 
				case ($direction == "southeast" || $direction == "se"): 
					$streetParts[0] = "se";
				break;    
				default: 
					$streetParts[0] = $direction;
			} 
		} 

	// finally, street suffix is abbreviated to "st", "ave", "blvd", ect.
		
		if (substr_count($inStreetName," ") > 0) {

			$sql = "SELECT * FROM street_suffixes 
					WHERE name = :name
					OR com1 = :com1
					OR com2 = :com2
					OR com3 = :com3
					OR com4 = :com4
					OR com5 = :com5
					OR com6 = :com6
					OR com7 = :com7
					OR com8 = :com8
					OR abbrev = :abbrev";	

			$stmt= $control['db']->prepare($sql);
			$stmt->bindParam(':name', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com1', $streetSuffix, PDO::PARAM_STR);	
			$stmt->bindParam(':com2', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com3', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com4', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com5', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com6', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com7', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':com8', $streetSuffix, PDO::PARAM_STR);			
			$stmt->bindParam(':abbrev', $streetSuffix, PDO::PARAM_STR);			
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 1) {
				$row = $stmt->fetch();
				$streetParts[$suffix] = strtolower($row['abbrev']);	
			}	
		}
		$newStreetName = "";
		for ($i=0; $i <= $suffix; $i++) 
			$newStreetName.= $streetParts[$i]." ";
		$newStreetName = trim($newStreetName);
	} else
		$newStreetName='';
		
	return $newStreetName;		
}

function CrunchPhone( $num ) {
  $retVal='          ';
  for ($i=0; $i<strlen($num); $i++) {
//    $x=$num{$i};
		$x=$num[$i];
    if ( $x >= "0" && $x <= "9" ) { $retVal .= $x; }
  }
  return substr($retVal, -10);
}

function isShelter($streetnum, $streetname) { 
	global $control;

	$retval = 0;
	$std_streetname = standardStreetName($streetname);	
	$sql = "SELECT * FROM shelters 
			WHERE streetnum = :streetnum
			AND std_streetname = :std_streetname";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':std_streetname', $std_streetname, PDO::PARAM_STR);
	$stmt->bindParam(':streetnum', $streetnum, PDO::PARAM_INT);		
	$stmt->execute();
	$total = $stmt->rowCount();
	if ($total > 0) {	
		$shelters = $stmt->fetch();
		$retval = $shelters['id'];
	}
	return $retval;
}


function addSurnames( $id ) {
	global $control;
	
// fills surname fields when member has multiple last names

	$sql = "SELECT lastname FROM members WHERE id=$id";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$total = $stmt->rowCount();
	if ($total > 0) {	
		$row = $stmt->fetch();		
		
		if (!empty($row['lastname'])) {
			$matches = preg_split("/[^a-z^0-9]/i", trim($row['lastname']));	
			if ( count($matches) > 1 ) 
				for ($i = 0; $i <= count($matches)-1; $i++) {
					$surNum=$i+1;
					if ($surNum<=4) {
						$sql2 = "UPDATE members
								 SET sur" . $surNum . " = '" . $matches[$i] ."'
								 WHERE id = $id";
						$stmt2 = $control['db']->prepare($sql2);
						$stmt2->execute();								 
					}	
				}	
			else // only 1 last name, clear surname fields
				for ($i = 1; $i <= 4; $i++) {
					$sql2 = "UPDATE members
							 SET sur" . $i . " = ''
							 WHERE id = $id";
					$stmt2 = $control['db']->prepare($sql2);
					$stmt2->execute();								 
				}				
		}	
	}	
}

function ucname($string) {
	
// inputs "little-fish", "MILES O\'BRIEN"
// outputs "Little-Fish", "Miles O'Brien"	
	
    $string =ucwords(strtolower($string));

    foreach (array('-', '\'') as $delimiter) {
      if (strpos($string, $delimiter)!==false) {
        $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
      }
    }
    return $string;
}

function CalcAge($dob, $showMonthsOrDays) {

    $todaysDate  = date("Y-m-d");
    $todaysMonth = substr($todaysDate,5,2);
    $todaysDay   = substr($todaysDate,8,2);
    $todaysYear  = substr($todaysDate,0,4);
    $todaysMonthDay = $todaysMonth."-".$todaysDay;

    $birthMonth = substr($dob,5,2);
    $birthDay   = substr($dob,8,2);
    $birthYear  = substr($dob,0,4);
    $birthMonthDay = $birthMonth."-".$birthDay;

    if ($birthMonthDay <= $todaysMonthDay)
        $years= $todaysYear - $birthYear;
    else
        $years= ($todaysYear - $birthYear) - 1;
		
	if ( intval($years) < 1 && ($showMonthsOrDays) )			// calculate months or days
		if ( ($birthMonth == $todaysMonth && $birthYear == $todaysYear ) ||
		   ( ( ($todaysMonth == 1 && $birthMonth == 12 ) || ( $todaysMonth > $birthMonth && ($todaysMonth - $birthMonth) == 1 ) )
		     && ($birthDay > $todaysDay) ) )	 		
		{
			$nowdate = strtotime($todaysDate);
			$thendate = strtotime($dob);
			$datediff = ($nowdate - $thendate);
			$days = round($datediff / 86400);
			if ($days == 1)
				$retval="1 day";
			else
				$retval= "$days days";					// when days are plural
		} else {
			if ( $birthYear == $todaysYear )
				$months = $todaysMonth - $birthMonth;
			else
				$months = 12 - $birthMonth + $todaysMonth;
			if ( $birthDay > $todaysDay )
				$months--;
			if ( $months == 1 )	
				$retval= "1 month";	
			else
				$retval= "$months months";				// when months are plural
		}	
	else
		$retval= $years;	
		
	return 	$retval;									
}

function selectLanguage($db, $name, $value) {

    $isOther=0;
    $otherFound=0;
?>  
	<select id="<?php echo $name; ?>" class="form-control bg-gray-1" name= "<?php echo $name; ?>">
<?php

	$sql = "SELECT * FROM languages ORDER BY name"; 
	$stmt = $db->query($sql);
	while ($row = $stmt->fetch()) {
        $isOther=(strtolower($row['name']) == 'other');
        if (!$isOther) { 
?>
            <option 
            <?php if ($value == $row['id']) echo "selected"; ?> 
            value ="<?php echo $row['id']; ?>" <?php echo $row['name']; ?> > <?php echo $row['name']; ?>
            </option>
<?php  } else {
            $otherVal = $row['name'];
            $otherId  = $row['id'];
            $otherFound = 1;
        }

    } // end while loop

    if ($otherFound) {
?>
        <option 
        <?php if ($value == $otherId) echo "selected"; ?> 
        value ="<?php echo $otherId; ?>" <?php echo $otherVal; ?> > <?php echo $otherVal; ?>
        </option><?php
    }
?>
    </select>
<?php 
}

function selectReadingDifficulty($name, $value) {
?>
    <select name = "<?php echo $name; ?>" class="form-control bg-gray-1" >
    <option <?php if ($value == "Yes") { echo "selected"; } ?> value ="Yes" yes>Yes </option>
    <option <?php if ($value == "No") { echo "selected"; } ?> value ="No " no> No </option>
    </select>
<?php
}

function hasNoPrimaryMember() {
	global $control;
	
	$retval = false;
	$sql = "SELECT * FROM members 
			WHERE householdID = :householdID 
			AND in_household = 'Yes'
			AND is_primary=1";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();	
	$total = $stmt->rowCount();		
	if ($total < 1)
		$retval="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Household has no active Primary Shopper.";

	return $retval;
}	

function hasNoActiveMembers() {
	global $control;
	
	$retval = false;
	$sql = "SELECT * FROM members 
			WHERE householdID = :householdID 
			AND in_household = 'Yes'";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();	
	$total = $stmt->rowCount();		
	if ($total < 1)
		$retval="<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> Household has no active members.";

	return $retval;
}	

/** 
  * selectPantry( $name, $value, $isSeeAll, $isSeeInactive, $isAllOption )
  * 	$isSeeAll  // 0: user only sees their own pantry; 1: user sees all pantries
  * 	$isSeeInactive  // 0: select from only active pantries; 1: select from both active and inactive pantries
  * 	$isAllOption  // 0: "all" option NOT included; 1: "all" option included 
  */
function selectPantry( $name, $id, $isSeeAll, $isSeeInactive=1, $isAllOption=1 ) {
	global $control; 
	
	echo "\n<select class='form-control bg-gray-1' name='$name' id='$name' >\n";

	if ($isSeeAll && $isAllOption) {
		echo "<option ";
		if ( $id == 0 ) echo "selected ";  	
		echo "value =0 0>---- all ----</option>\n";
	}	
	
	if ($isSeeAll) 	
		if ($isSeeInactive)		
			$sql = "SELECT * FROM pantries ORDER BY name";
		else
			$sql = "SELECT * FROM pantries WHERE is_active=1 ORDER BY name";			
	else
		if ($isSeeInactive)	
			$sql = "SELECT * FROM pantries 
					WHERE id = $id
					ORDER BY name";	
		else
			$sql = "SELECT * FROM pantries 
					WHERE id = $id
					AND is_active=1
					ORDER BY name";				
			
				
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach ($result as $pantries) {
		$expandedName = $pantries['name'];	
		if ( !$pantries['is_active'] )
			$expandedName .= " (inactive)"; 
		echo "<option "; 
		if ( $id == $pantries['id'] ) { echo "selected "; } 
		echo "value =" . $pantries['id'] ." " . $pantries['id'] . "> " . $expandedName . "</option>\n";
	} 

	echo "</select>\n";
}

function selectDateType( $name, $value ) {

	echo "<select id='$name' class='form-control bg-gray-1' name='$name' onchange='onSelectDate()'/>\n";

	echo "<option  "; 
	if ( $value == "all" ) echo "selected ";  
	echo "value= 'all'>---- all ----</option>\n";	
	
	echo "<option  "; 
	if ( $value == "last18months" ) echo "selected ";  
	echo "value= 'last18months'>last 18 months</option>\n";

	echo "<option  "; 
	if ( $value == "equalto" ) echo "selected ";  
	echo "value= 'equalto'>equal to</option>\n";

	echo "<option  "; 
	if ( $value == "after" ) echo "selected ";  
	echo "value= 'after'>after</option>\n";	

	echo "<option  "; 
	if ( $value == "onorafter" ) echo "selected ";  
	echo "value= 'onorafter'>on or after</option>\n";
	
	echo "<option  "; 
	if ( $value == "before" ) echo "selected ";  
	echo "value= 'before'>before</option>\n";	

	echo "<option  "; 
	if ( $value == "onorbefore" ) echo "selected ";  
	echo "value= 'onorbefore'>on or before</option>\n";	

	echo "<option  "; 
	if ( $value == "range" ) echo "selected ";  
	echo "value= 'range'>range</option>\n";	

	echo "</select>";	
}

function ExpandPhone( $num ) {
  return '(' . substr($num, 0, 3) . ') ' . substr($num, 3, 3) . '-' . substr($num, 6, 4);  
}

function yesNoSwitch($name, $value, $disabled) {
	global $control;
	
	$yc ="";
	$value=ucname(strval($value));
		
	if ($value == "Yes" || $value == "1")
		$yc = "checked='checked'";
	
	echo "<input type='checkbox' name='$name' data-toggle='switch' data-on-color='primary' data-on-text='YES' data-off-color='default' data-off-text='NO' $yc $disabled>\n";	
}

function editPassword( $currPassword="", $newPassword, $confirmPassword, $isReset=0 ) {
	global $control;	
	
	$err=0;
	if (!$isReset)
		if (empty($currPassword)) 
			$err=26; 
		elseif (empty($newPassword))
			$err=28;
		elseif (empty($confirmPassword))
			$err=27;			
		else {
			$sql = "SELECT * FROM users WHERE id =:id";	
			$stmt = $control['db']->prepare($sql);
			$stmt->bindParam(':id', $control['users_id'], PDO::PARAM_INT);		
			$stmt->execute();
			$users = $stmt->fetch();		
			$total = $stmt->rowCount();		
			if ($total > 0)		
				if ( !password_verify($currPassword, $users['password']) ) 	
					if ($control['access_level']==1) 
						$err=70;	// Administrator			
					else
						$err=26;
			if ( $newPassword != $confirmPassword ) 
				$err=27;					
		}
			
	elseif ( empty($newPassword) ) 
		$err=28; 
	elseif ( $newPassword != $confirmPassword ) 
		$err=27;

	return $err;		
}	

function my_session_start($timeout = 86400) {
	
	session_start(['cookie_lifetime' => 86400]);	
	
//    ini_set('session.gc_maxlifetime', $timeout);
//    session_start();

//    if (isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < time()) {
//        session_destroy();
//        session_start();
//        session_regenerate_id();
//        $_SESSION = array();
//    }

//    $_SESSION['timeout_idle'] = time() + $timeout;
}	

function writeErrorLog() {
// called from addhousehold.php
}

function selectTimezone( $name, $value ) {
	global $control;
	
	echo "<select id='$name' class='form-control bg-gray-1' name='$name'/>\n";
	
	echo "<option  "; 
	if ( $value == "US/Alaska" ) echo "selected ";  
	echo "value= 'US/Alaska'>US/Alaska</option>\n";

	echo "<option  "; 
	if ( $value == "US/Aleutian" ) echo "selected ";  
	echo "value= 'US/Aleutian'>US/Aleutian</option>\n";

	echo "<option  "; 
	if ( $value == "US/Arizona" ) echo "selected ";  
	echo "value= 'US/Arizona'>US/Arizona</option>\n";	

	echo "<option  "; 
	if ( $value == "US/Central" ) echo "selected ";  
	echo "value= 'US/Central'>US/Central</option>\n";
	
	echo "<option  "; 
	if ( $value == "US/East-Indiana" ) echo "selected ";  
	echo "value= 'US/East-Indiana'>US/East-Indiana</option>\n";		
	
	echo "<option  "; 
	if ( $value == "US/Eastern" ) echo "selected ";  
	echo "value= 'US/Eastern'>US/Eastern</option>\n";	
	
	echo "<option  "; 
	if ( $value == "US/Hawaii" ) echo "selected ";  
	echo "value= 'US/Hawaii'>US/Hawaii</option>\n";	
	
	echo "<option  "; 
	if ( $value == "US/Indiana-Starke" ) echo "selected ";  
	echo "value= 'US/Indiana-Starke'>US/Indiana-Starke</option>\n";		
	
	echo "<option  "; 
	if ( $value == "US/Michigan" ) echo "selected ";  
	echo "value= 'US/Michigan'>US/Michigan</option>\n";		
	
	echo "<option  "; 
	if ( $value == "US/Mountain" ) echo "selected ";  
	echo "value= 'US/Mountain'>US/Mountain</option>\n";	
	
	echo "<option  "; 
	if ( $value == "US/Pacific" ) echo "selected ";  
	echo "value= 'US/Pacific'>US/Pacific</option>\n";

	echo "<option  "; 
	if ( $value == "US/Samoa" ) echo "selected ";  
	echo "value= 'US/Samoa'>US/Samoa</option>\n";	
	
	echo "</select>\n";	
}

function pieColors($numSlices) {
	
	$color=array();
	$color[1]='#ff6f00';
	$color[2]='#ff9a4d';
	$color[3]='#ffad33';
	$color[4]='#f87254';
	$color[5]='#da2e0b';
	$color[6]='#841E14';
	$color[7]='#944dff';
	$color[8]='#4d94ff';
	$color[9]='#33cc33';

// keep color array from going out of bounds	
	if ($numSlices > 9)
		$numSlices = 9;
	
// add contrast 	
	if ($numSlices == 4)
		$color[4] = $color[5];
	
	$isFirst=true;	
	$data="";
	for ($x = 1; $x <= $numSlices; $x++) {
		if (!$isFirst)
			$data.= ",\n";	
		$data.= "{color:'" . $color[$x] . "'}";	
		$isFirst=false;	
	}	
	
	return $data;
}	
	
?>