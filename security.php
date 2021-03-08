<?php
/**
 * security.php
 * written: 8/24/2020
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
*/ 
	require_once('config.php'); 
	require_once('header.php'); 
	require_once('navbar.php'); 		
	require_once('functions.php');	
	require_once('common_vars.php');
	require_once('security/users.php');
	require_once('security/accesslevels.php');	
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "security");
	$control=loadAccessLevels(); 	
	
	doSaveUpdate();
	$control=setFocus($control);	
	doHeader("Security");
	doNavbar();	
	doSecurityNavBar();	
	
	if ($control['tab'] == "users")
		if (isset($_GET['delete'])) {
			deleteUser();	
			listUsers($errMsg);		
		} elseif (isset($_GET['id']))
			userForm("edit", $errMsg);	
		elseif (isset($_GET['add']))
			userForm("add", $errMsg);			
		else
			listUsers($errMsg);
	elseif ($control['tab'] == "access") {
		if (isset($_GET['delete'])) {
			rotateLevels();
			deleteAccessLevel();	
			listAccessLevels();			
		} elseif (isset($_GET['id']))
			accessLevelForm("edit", $errMsg);
		elseif (isset($_GET['add']))
			accessLevelForm("add", $errMsg);		
		else
			listAccessLevels();	
	}
	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	
function setFocus($arr) {
	global $control;
	
	$arr["focus"] = "nofocus";
	if (isset($_GET['errCode'])) 
		$arr['errCode'] = $_GET['errCode'];		
	
	if ($control['tab'] == "users") {
		if (isset($_GET['delete']))
			$arr["focus"] = "nofocus";			
		elseif (isset($_GET['add']) || isset($_GET['id']))
			$arr["focus"] = "firstname";
		if ($arr['errCode'] == 67)
			$arr["focus"] = "lastname";	
		elseif ($arr['errCode'] == 2)
			$arr["focus"] = "username";	
		elseif ($arr['errCode'] == 69)
			$arr["focus"] = "username";	
		elseif ($arr['errCode'] == 24)
			$arr["focus"] = "email";		
		elseif ($arr['errCode'] == 25)
			$arr["focus"] = "email";
		elseif ($arr['errCode'] == 54)
			$arr["focus"] = "phone";	
		elseif ($arr['errCode'] == 28)
			$arr["focus"] = "password";	
		elseif ($arr['errCode'] == 27)
			$arr["focus"] = "password";			
		elseif ($arr['errCode'] == 26)
			$arr["focus"] = "password";		
		elseif ($arr['errCode'] == 70)
			$arr["focus"] = "password";				
	} elseif ($control['tab'] == "access") 
		if (isset($_GET['add']) || isset($_GET['edit']))
			$arr["focus"] = "name";	

	return $arr;		
}	

function doSecurityNavBar() {
	global $control;
	
	$link="security.php?hhID=$control[hhID]";
	
	$uActive="";
	$aActive="";	
	
	if ($control['tab'] == "users")
		$uActive="active";
	elseif ($control['tab'] == "access")
		$aActive="active";	
	
	echo "
	<div class='container-fluid pt-3'>
		<ul class='nav nav-tabs'>
		  <li class='nav-item'>
			<a class='nav-link $uActive text-dark' href='" . $link . "&tab=users'>Users</a>
		  </li>\n";
		  if ($control['access_level_update'] || $control['access_level_delete'] || $control['access_level_browse'])
			  echo "
			  <li class='nav-item'>
				<a class='nav-link $aActive text-dark' href='" . $link . "&tab=access'>Access Levels</a>
			  </li>\n";
	echo "		  
		</ul>
	</div>";
}

function doSaveUpdate() {
	global $control;
	
	if (isset($_POST['saveSetup']))			// products/pantryinfo.php	
		updatePantryInfo();
	if (isset($_POST['saveInstock']))		// products/instock.php	
		updateInstockStatus();
}	
?>

<script>	

	$("#phone1").inputmask({"mask": "(999) 999-9999"});	
	$("#phone2").inputmask({"mask": "(999) 999-9999"});		
	$("#phone").inputmask({"mask": "(999) 999-9999"});		

	$("[name='is_active']").bootstrapSwitch();	
	$("[name='hypoallergenic']").bootstrapSwitch();		
	$("[name='for_incontinence']").bootstrapSwitch();

	function OKToDeleteUser() {
	
		ConfirmMsg = "Delete removes user from entire Pepbase system. OK to delete?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}	

	function OKToDeleteAccessLevel() {
	
		ConfirmMsg = "Delete removes access level from entire Pepbase system. OK to delete?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}		
	
	
	
	
</script>	