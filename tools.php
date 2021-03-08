<?php
/**
 * tools.php
 * written: 9/7/2020
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
	require_once('tools/changepantry.php');
	require_once('tools/about.php');
	require_once('tools/userlog.php');	
	require_once('tools/table_conversions.php');	
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "tools");
	$control=loadAccessLevels();	
	doSaveUpdate();
	$control=setFocus($control);	
	doHeader("Security");
	doNavbar();	
	doToolsNavBar();	
	
	if ($control['tab'] == "change")
		changePantryForm();	
	if ($control['tab'] == "userlog")
		listUserLog();
	elseif ($control['tab'] == "about")
		aboutPepbase();
	elseif ($control['tab'] == "convert")
		covertMenu();		
	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	
function setFocus($arr) {
	global $control;
	
	$arr["focus"] = "nofocus";
	if (isset($_GET['errCode'])) 
		$arr['errCode'] = $_GET['errCode'];		
	
	if ($control['tab'] == "change") {
		$arr["focus"] = "users_pantry_id";		
		
	} elseif ($control['tab'] == "users") {
		if (isset($_GET['add']) || isset($_GET['edit']))
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

function doToolsNavBar() {
	global $control;
	
	$link="tools.php?hhID=$control[hhID]";
	$tab="";
	
	$chActive="";	
	$uActive="";
	$aActive="";	
	$cActive="";	
	
	if ($control['tab'] == "change")
		$chActive="active";	
	elseif ($control['tab'] == "userlog")
		$uActive="active";
	elseif ($control['tab'] == "about")
		$aActive="active";	
	elseif ($control['tab'] == "convert")
		$cActive="active";	

//	if ($control['changepantry_browse'])
//		$link.= "&tab=change";
//	elseif ($control['userlog_browse'])			
//		$link.= "&tab=userlog";
//	elseif ($control['convert_browse'])	
//		$link.= "&tab=convert";	
//	else
//		$link.= "&tab=about";		
	
	echo "
	<div class='container-fluid pt-3'>
		<ul class='nav nav-tabs'>\n";
		if ($control['changepantry_browse'])
			echo "		
			<li class='nav-item'>
			<a class='nav-link $chActive text-dark' href='" . $link . "&tab=change'>Change Pantry</a>
			</li>";	
		if ($control['userlog_browse'])			
			echo "
			<li class='nav-item'>
			<a class='nav-link $uActive text-dark' href='" . $link . "&tab=userlog'>User Log</a>
			</li>";
		if ($control['convert_browse'])
			echo "
			<li class='nav-item'>
			<a class='nav-link $cActive text-dark' href='" . $link . "&tab=convert'>Convert</a>
			</li>";		
			
		echo "
			<li class='nav-item'>
			<a class='nav-link $aActive text-dark' href='" . $link . "&tab=about'>About Pepbase</a>
			</li>		  
		</ul>
	</div>";
}


function doSaveUpdate() {
	global $control;
	
	if (isset($_POST['apply']))			
		$control['users_pantry_id']=$_POST['users_pantry_id'];
//	if (isset($_POST['saveInstock']))		// products/instock.php	
//		updateInstockStatus();
}	


?>

<script>	

	$("#phone1").inputmask({"mask": "(999) 999-9999"});	
	$("#phone2").inputmask({"mask": "(999) 999-9999"});		
	$("#phone").inputmask({"mask": "(999) 999-9999"});		

	$("[name='is_active']").bootstrapSwitch();	
	$("[name='hypoallergenic']").bootstrapSwitch();		
	$("[name='for_incontinence']").bootstrapSwitch();


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