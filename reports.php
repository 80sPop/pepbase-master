<?php
/**
 * reports.php
 * written: 10/3/2020
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
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "reports");
	$control=loadAccessLevels();	
	
	$control=setFocus($control);	
	doHeader("Reports");
	doNavbar();	
	doReportsNavBar();	
	
	if ($control['tab'] == "consumption" && $control['reports_con_browse'])
		listConsumptionReports();
		
	elseif ($control['tab'] == "demographic" && $control['reports_demo_browse'])
		listDemographicReports();	
	
	elseif ($control['tab'] == "graphs" && $control['reports_charts_browse']) 
		listChartsAndGraphs();	
		
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	
function setFocus($arr) {
	global $control;
	
	$arr["focus"] = "nofocus";
	if (isset($_GET['errCode'])) 
		$arr['errCode'] = $_GET['errCode'];	

	return $arr;		
}	

function doReportsNavBar() {
	global $control;
	
	$link="reports.php?hhID=$control[hhID]";
	
	$cActive="";
	$dActive="";	
	$gActive="";
	
	if ($control['tab'] == "consumption")
		$cActive="active";
	elseif ($control['tab'] == "demographic")
		$dActive="active";	
	elseif ($control['tab'] == "graphs")
		$gActive="active";	
	
	echo "
	<div class='container-fluid pt-3'>
		<ul class='nav nav-tabs'>\n";
		if ($control['reports_con_browse'])	
			echo "	
			<li class='nav-item'>
				<a class='nav-link $cActive text-dark' href='" . $link . "&tab=consumption'>Consumption</a>
			</li>\n";
		if ($control['reports_demo_browse'])	
			echo "				
			<li class='nav-item'>
			<a class='nav-link $dActive text-dark' href='" . $link . "&tab=demographic'>Demographic</a>
			</li>\n";
		if ($control['reports_charts_browse'])	
			echo "					
			<li class='nav-item'>
			<a class='nav-link $gActive text-dark' href='" . $link . "&tab=graphs'>Charts and Graphs</a>
			</li>\n";
			
	echo "		
		</ul>
	</div>";
}

function listConsumptionReports() {
	
?>	
	<div class="container-fluid bg-gray-2 p-5"> 

		<ul>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP001.php'>PEP001 - Household Consumption by Number of Visits</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP007.php'>PEP007 - Households by Number of Visits</a></li>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP014.php'>PEP014 - Product Consumption by Time Period</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP015.php'>PEP015 - Product Consumption for Households With at Least 1 Child Under 3 Years of Age</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP024.php'>PEP024 - Product Ordering Guidelines</a></li>	
		</ul>		
		
	</div>
	
<?php		
}

function listDemographicReports() {	
?>	
	<div class="container-fluid bg-gray-2 p-5"> 
	
		<ul>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP002.php'>PEP002 - Household Members by Age and Gender</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP005.php'>PEP005 - Households By Gender of Primary Shopper</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP009.php'>PEP009 - Households with Age Difference Greater Than 30 years Or Less Than 15 years</a></li>		
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP013.php'>PEP013 - Households By Language and Zip Code</a></li>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP026.php'>PEP026 - Household Directory</a></li>		
		</ul>
	
	</div>
	
<?php		
}

function listChartsAndGraphs() {	
?>	
	<div class="container-fluid bg-gray-2 p-5"> 
	
		<ul>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP007c.php'>PEP007c - Households by Number of Visits</a></li>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP019.php'>PEP019 - New Households Registered</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP020.php'>PEP020 - Household Visits</a></li>
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP021.php'>PEP021 - Product Consumption</a></li>	
		<li><a target='_blank' style='color:#841E14;' href='reports/PEP025.php'>PEP025 - In Stock Percentage</a></li>	
		</ul>
	
	</div>
	
<?php		
}
?>