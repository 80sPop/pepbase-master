<?php
/**
 * tables.php
 * written: 9/4/2020
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
	require_once('tables/languages.php');
	require_once('tables/measures.php');
	require_once('tables/containers.php');	
	require_once('tables/shelters.php');	
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "security");
	$control=loadAccessLevels();		
	doSaveUpdate();
	$control=setFocus($control);	
	doHeader("Security");
	doNavbar();	
	doTablesNavBar();	
	
	if ($control['tab'] == "languages")
		if (isset($_GET['delete'])) {
			deleteLanguage();	
			listLanguages();		
		} elseif (isset($_GET['id']))
			languageForm("edit", $errMsg);	
		elseif (isset($_GET['add']))
			languageForm("add", $errMsg);			
		else
			listLanguages();
		
	elseif ($control['tab'] == "measures")
		if (isset($_GET['delete'])) {
			deleteMeasure();	
			listMeasures();			
		} elseif (isset($_GET['id']))
			measureForm("edit", $errMsg);
		elseif (isset($_GET['add']))
			measureForm("add", $errMsg);		
		else
			listMeasures();	
	
	elseif ($control['tab'] == "containers") 
		if (isset($_GET['delete'])) {
			deleteContainer();	
			listContainers();			
		} elseif (isset($_GET['id']))
			containerForm("edit", $errMsg);
		elseif (isset($_GET['add']))
			containerForm("add", $errMsg);		
		else
			listContainers();	
		
	elseif ($control['tab'] == "shelters") 
		if (isset($_GET['delete'])) {
			deleteShelter();	
			listShelters();			
		} elseif (isset($_GET['id']))
			sheltersForm("edit", $errMsg);
		elseif (isset($_GET['add']))
			sheltersForm("add", $errMsg);		
		else
			listShelters();	
	
	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	
function setFocus($arr) {
	global $control;
	
	$arr["focus"] = "nofocus";
	if (isset($_GET['errCode'])) 
		$arr['errCode'] = $_GET['errCode'];	

	if ($control['tab'] == "shelters") { 
		if (isset($_GET['add']) || isset($_GET['edit']))
			$arr["focus"] = "name";	
		if (isset($_GET['errCode']))
			if ($arr['errCode'] == 65)
				$arr["focus"] = "address";	
			elseif ($arr['errCode'] == 49)
				$arr["focus"] = "city";	
			elseif ($arr['errCode'] == 51)
				$arr["focus"] = "zip_five";		
			elseif ($arr['errCode'] == 54)
				$arr["focus"] = "zip_five";		
			elseif ($arr['errCode'] == 75)
				$arr["focus"] = "staytime";						
	} elseif (isset($_GET['add']) || isset($_GET['edit']))
		$arr["focus"] = "name";

	return $arr;		
}	

function doTablesNavBar() {
	global $control;
	
	$link="tables.php?hhID=$control[hhID]";
	
	$lActive="";
	$mActive="";	
	$cActive="";
	$sActive="";		
	
	if ($control['tab'] == "languages")
		$lActive="active";
	elseif ($control['tab'] == "measures")
		$mActive="active";	
	elseif ($control['tab'] == "containers")
		$cActive="active";	
	elseif ($control['tab'] == "shelters")
		$sActive="active";			
	
	echo "
	<div class='container-fluid pt-3'>
		<ul class='nav nav-tabs'>\n";
		if ($control['languages_update'] || $control['languages_delete'] || $control['languages_browse'])
			echo "
			<li class='nav-item'>
			<a class='nav-link $lActive text-dark' href='" . $link . "&tab=languages'>Languages</a>
			</li>";
		if ($control['measures_update'] || $control['measures_delete'] || $control['measures_browse'])
			echo "			
			<li class='nav-item'>
			<a class='nav-link $mActive text-dark' href='" . $link . "&tab=measures'>Measures</a>
			</li>";
		if ($control['containers_update'] || $control['containers_delete'] || $control['containers_browse'])
			echo "				
			<li class='nav-item'>
			<a class='nav-link $cActive text-dark' href='" . $link . "&tab=containers'>Containers</a>
			</li>";
		if ($control['shelters_update'] || $control['shelters_delete'] || $control['shelters_browse'])
			echo "					
			<li class='nav-item'>
			<a class='nav-link $sActive text-dark' href='" . $link . "&tab=shelters'>Shelters</a>
			</li>";
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


	function OKToDeleteLanguage() {
	
		ConfirmMsg = "Delete removes language from entire Pepbase system. OK to delete?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}	
	
	function OKToDeleteContainer() {
	
		ConfirmMsg = "Delete removes container from entire Pepbase system. OK to delete?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}		
	
	function OKToDeleteMeasure() {
	
		ConfirmMsg = "Delete removes measure from entire Pepbase system. OK to delete?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}		

	function OKToDeleteShelter() {
	
		ConfirmMsg = "Delete removes shelter from entire Pepbase system. OK to delete?";
		input_box=confirm(ConfirmMsg);
		if (input_box==true) { 
			return true;
		} else {
			return false;
		}
	}			
	
	
	
	
</script>	