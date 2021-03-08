<?php
/**
 * navbar.php
 * written: 5/7/2020
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

function doNavbar() {
	global $control;

	$hoActive="text-light";
	$paActive="text-light";	
	$prActive="text-light";	
	$reActive="text-light";	
	$seActive="text-light";	
	$taActive="text-light";	
	$toActive="text-light";	

	$tab="";	
	
	if (strpos($_SERVER['PHP_SELF'], "households.php"))	
		$hoActive="active text-dark";
	elseif (strpos($_SERVER['PHP_SELF'], "products.php"))	
		$prActive="active text-dark";	
	elseif (strpos($_SERVER['PHP_SELF'], "reports.php"))	
		$reActive="active text-dark";			
	elseif (strpos($_SERVER['PHP_SELF'], "pantries.php"))	
		$paActive="active text-dark";	
	elseif (strpos($_SERVER['PHP_SELF'], "security.php"))	
		$seActive="active text-dark";
	elseif (strpos($_SERVER['PHP_SELF'], "tables.php"))	
		$taActive="active text-dark";			
	elseif (strpos($_SERVER['PHP_SELF'], "tools.php"))	
		$toActive="active text-dark";			

	echo "		
	<nav class='nav bg-gray-7'>
		<a class='nav-link $hoActive' href='households.php?hhID=$control[hhID]&tab=profile'>Households</a>
		<a class='nav-link $prActive' href='products.php?hhID=$control[hhID]&tab=definitions'>Products</a>";
		if ($control['reports_con_browse'])
			echo "<a class='nav-link $reActive' href='reports.php?hhID=$control[hhID]&tab=consumption'>Reports</a>\n";
		elseif ($control['reports_demo_browse'])
			echo "<a class='nav-link $reActive' href='reports.php?hhID=$control[hhID]&tab=demographic'>Reports</a>\n";	
		elseif ($control['reports_charts_browse'])
			echo "<a class='nav-link $reActive' href='reports.php?hhID=$control[hhID]&tab=graphs'>Reports</a>\n";
			
		if ($control['pantries_update'] || $control['pantries_delete'] || $control['pantries_browse'])	
			echo "<a class='nav-link $paActive' href='pantries.php?hhID=$control[hhID]'>Pantries</a>";			
	echo "		
		<a class='nav-link $seActive' href='security.php?hhID=$control[hhID]&tab=users'>Security</a>";

	if ($control['languages_update'] || $control['languages_delete'] || $control['languages_browse'] 	||
		$control['measures_update'] || $control['measures_delete'] || $control['measures_browse'] 		||
		$control['containers_update'] || $control['containers_delete'] || $control['containers_browse'] ||
		$control['shelters_update'] || $control['shelters_delete'] || $control['shelters_browse']) {
		if ($control['languages_update'] || $control['languages_delete'] || $control['languages_browse'])
			$tab="languages";
		elseif ($control['measures_update'] || $control['measures_delete'] || $control['measures_browse'])
			$tab="measures";
		elseif ($control['containers_update'] || $control['containers_delete'] || $control['containers_browse'])
			$tab="containers";				
		else
			$tab="shelters";
		echo "<a class='nav-link $taActive' href='tables.php?hhID=$control[hhID]&tab=$tab'>Tables</a>\n";
	}	
	
	if ($control['changepantry_browse'])
		$tab="change";
	elseif ($control['userlog_browse'])			
		$tab="userlog";
	elseif ($control['convert_browse'])	
		$tab="convert";	
	else
		$tab="about";		


	echo "	  
		<a class='nav-link $toActive' href='tools.php?hhID=$control[hhID]&tab=$tab'>Tools</a>  
	</nav>";

}	
?>