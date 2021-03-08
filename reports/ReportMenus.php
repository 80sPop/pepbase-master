<?php
/**
 * ReportMenus.php
 * Written: 2-26-13 for version 3.4.1 patch
 *
 * 9-17-2019: v 3.9.3 update - remove PEP010 from consumption reports (already listed under demographic reports).		-mlr
 *
 * 1-10-2019: version 3.7 update. Deprecate PEP006 in Consumption Menu.		-mlr	
 *
 * 9-3-2018: version 3.7.0 update - add PEP007c and PEP025c to Charts and Graphs menu.		-mlr
 *
 * 12-24-15: version 3.6.1 update - move PEP009 and PEP015 to Demograhic tab.		-mlr
 *
 * 11-3-15: version 3.6.0 update - add PEP024 to consumptionReportsMenu().		-mlr
 *
 * 7-21-13: version 3.4.4 update - add PEP023 to consumptionReportsMenu().		-mlr
 */
 
function doConsumptionReports()
{
global $conn, $hhID, $themeId, $pantryID, $accessLevelRow, $hostPantryId;

	if (! $accessLevelRow['al_reports_con_browse']) {	
	    $errCode=8;
		displayErr($errCode);  	// insufficient access level to view reports
		
	} else 
	
	    consumptionReportsMenu();		
} 

/**
 * ConsumptionReportsMenu()
 * Written: 2-26-13
 *
 */
function consumptionReportsMenu()
{
global $conn, $themeId, $pantryID; 

	$link = "?themeId=$themeId&pantryID=$pantryID";
	
	echo "<center>\n";
	echo "<table class='reportMenu'><tr><td>\n";
    echo "<ul>\n";
	echo "<li><a target='_blank' href='Reports/PEP001.php" .$link . "'>Household Consumption by Number of Visits - PEP001</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP004.php" .$link . "'>Product Consumption for Household (x) - PEP004</a></li>\n";
	
// 1-10-2019: version 3.7 update. Deprecate PEP006.		-mlr	
//	echo "<li><a target='_blank' href='Reports/PEP006.php" .$link . "'>Household Number of Visits by Month - PEP006</a></li>\n";
	echo "<li>Household Number of Visits by Month - PEP006 <i>(Deprecated as of 1-10-2019, use PEP020 under Charts and Graphs instead)</i>\n";
	echo "<li><a target='_blank' href='Reports/PEP007.php" .$link . "'>Households by Number of Visits - PEP007</a></li>\n";
// 12-24-15: version 3.6.1 update - move PEP009 to Demograhic tab.		-mlr	
//	echo "<li><a target='_blank' href='Reports/PEP009.php" .$link . "'>Households with Age Difference > 30 OR < 15 - PEP009</a></li>\n";

// 9-17-2019: v 3.9.3 update - remove PEP010 from consumption reports (already listed under demographic reports)		-mlr
//	echo "<li><a target='_blank' href='Reports/PEP010.php" .$link . "'>Households By Language and Pantry of Registration - PEP010</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP012.php" .$link . "'>Households by Number of Visits and Zip Code - PEP012</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP014.php" .$link . "'>Product Consumption by Time Period - PEP014</a></li>\n";	
// 12-24-15: version 3.6.1 update - move PEP015 to Demograhic tab.		-mlr
//	echo "<li><a target='_blank' href='Reports/PEP015.php" .$link . "'>Household Visits With at Least 1 Child <= 3 Years Old - PEP015</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP018.php" .$link . "'>Household Average Number of Visits by Zip Code - PEP018</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP022.php" .$link . "'>Product Consumption by Household Composition - PEP022</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP023.php" .$link . "'>Household Consumption by Time Period - PEP023</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP024.php" .$link . "'>Product Ordering Guidelines - PEP024</a></li>\n";	
    echo "</ul></td></tr></table></center>\n";
} 
 
 
function doDemographicReports() {
global $conn, $hhID, $themeId, $pantryID, $accessLevelRow, $hostPantryId;

	if (! $accessLevelRow['al_reports_demo_browse']) {	
	    $errCode=8;
		displayErr($errCode);  	// insufficient access level to view reports
		
	} else 
	
	    demographicReportsMenu();		
} 

/**
 * demographicReportsMenu()
 * Written: 2-26-13
 *
 */
function demographicReportsMenu()
{
global $conn, $themeId, $pantryID; 

	$link = "?themeId=$themeId&pantryID=$pantryID";
	
	echo "<center>\n";
	echo "<table class='reportMenu'><tr><td>\n";
    echo "<ul>\n";
	echo "<li><a target='_blank' href='Reports/PEP002.php" .$link . "'>Household Members by Age and Gender - PEP002</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP003.php" .$link . "'>Households By Zip Code and Pantry of Registration - PEP003</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP005.php" .$link . "'>Households By Gender of Primary Shopper - PEP005</a></li>\n";	
// 12-24-15: version 3.6.1 update - move PEP009 to Demograhic tab.		-mlr	
	echo "<li><a target='_blank' href='Reports/PEP009.php" .$link . "'>Households with Age Difference > 30 OR < 15 - PEP009</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP010.php" .$link . "'>Households By Language and Pantry of Registration - PEP010</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP013.php" .$link . "'>Households By Language and Zip Code - PEP013</a></li>\n";
//	echo "<li><a target='_blank' href='Reports/PEP013c.php" .$link . "'>Households By Language and Zip Code - PEP013c</a></li>\n";	
// 12-24-15: version 3.6.1 update - move PEP015 to Demograhic tab.		-mlr	
	echo "<li><a target='_blank' href='Reports/PEP015.php" .$link . "'>Household Visits With at Least 1 Child Under 3 Years of Age - PEP015</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP026.php" .$link . "'>Household Directory - PEP026</a></li>\n";		
    echo "</ul></td></tr></table></center>\n";
} 
 
 
function doChartsGraphsReports()
{
global $conn, $hhID, $themeId, $pantryID, $accessLevelRow, $hostPantryId;

	if (! $accessLevelRow['al_reports_charts_browse']) {	
	    $errCode=8;
		displayErr($errCode);  	// insufficient access level to view reports
		
	} else 
	
	    chartsGraphsReportsMenu();		

} 

/**
 * chartsGraphsReportsMenu()
 * Written: 2-26-13
 *
 */
function chartsGraphsReportsMenu()
{
global $conn, $themeId, $pantryID; 

	$link = "?themeId=$themeId&pantryID=$pantryID";
	
	echo "<center>\n";
	echo "<table class='reportMenu'><tr><td>\n";
    echo "<ul>\n";

// 9-3-2018: version 3.7 update - add PEP007c and PEP025c to Charts and Graphs menu.		-mlr
	echo "<li><a target='_blank' href='Reports/PEP007c.php" . $link . "'>Households by Number of Visits - PEP007c</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP019.php" . $link . "'>New Households Registered - PEP019</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP020.php" . $link . "'>Household Visits - PEP020</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP021.php" . $link . "'>Product Consumption - PEP021</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP025c.php" . $link . "'>In Stock Percentage - PEP025c</a></li>\n";	
    echo "</ul></td></tr></table></center>\n";
} 

function doAdminReports()
{
global $conn, $hhID, $themeId, $pantryID, $accessLevelRow, $hostPantryId;

	if (! $accessLevelRow['al_reports_admin_browse']) {	
	    $errCode=8;
		displayErr($errCode);  	// insufficient access level to view reports
		
	} else {
	
	    adminReportsMenu();		
	}
} 

/**
 * PrintAdminMenu()
 * Written: 3-22-12
 *
 */
function adminReportsMenu()
{
global $conn, $themeId, $pantryID; 

	$link = "?themeId=$themeId&pantryID=$pantryID";
	
	echo "<center>\n";
	echo "<table class='reportMenu'><tr><td>\n";
    echo "<ul>\n";
	echo "<li><a target='_blank' href='Reports/PEP011.php$link'>Error Report - Members in Multiple Households - PEP011 (Takes a while)</a></li>\n";
	echo "</ul></td></tr></table></center>\n";
}
?>
