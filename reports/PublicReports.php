<?php
/**
 * PublicReports.php
 * Written: 9-19-12 for version 3.1 update
 *
 * 1-5-13: version 3.4 upgrade - style and css modifications for new theme.   -mlr
 *
 * 10-24-12: version 3.3 updates - set $pantryID as global $conn, 	 -mlr
 */
function doPublicReports()
{
global $conn,  $hhID, $themeId, $pantryID, $accessLevelRow, $hostPantryId;

	if (! $accessLevelRow['al_reports_pub_browse']) {	
	    $errCode=8;
		displayErr($errCode);  	// insufficient access level to view reports
		
	} else 
	
	    PrintPublicMenu();		

} 

/**
 * PrintPublicMenu()
 * Written: 3-22-12
 *
 */
function PrintPublicMenu()
{
global $conn,  $themeId, $pantryID; 

	$link = "?themeId=$themeId&pantryID=$pantryID";
	
	echo "<center>\n";
	echo "<table class='reportMenu'><tr><td>\n";
    echo "<ul>\n";
	echo "<li><a target='_blank' href='Reports/PEP001.php" .$link . "'>Household Consumption by Number of Visits - PEP001</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP002.php" .$link . "'>Household Members by Age and Gender - PEP002</a></li>\n";		
	echo "<li><a target='_blank' href='Reports/PEP003.php" .$link . "'>Households By Zip Code and Pantry of Registration - PEP003</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP004.php" .$link . "'>Product Consumption for Household (x) - PEP004</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP005.php" .$link . "'>Household Composition by Gender of Primary Shopper - PEP005</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP006.php" .$link . "'>Household Number of Visits by Month - PEP006</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP007.php" .$link . "'>Households by Number of Visits - PEP007</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP009.php" .$link . "'>Households with Age Difference > 30 OR < 15 - PEP009</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP010.php" .$link . "'>Households By Language and Pantry of Registration - PEP010</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP012.php" .$link . "'>Households by Number of Visits and Zip Code - PEP012</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP013.php" .$link . "'>Households By Language and Zip Code - PEP013</a></li>\n";
	echo "<li><a target='_blank' href='Reports/PEP014.php" .$link . "'>Product Consumption by Time Period - PEP014</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP015.php" .$link . "'>Household Visits With at Least 1 Child <= 3 Years Old - PEP015</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP016.php" .$link . "'>New Households Registered by Month - PEP016</a></li>\n";	
	echo "<li><a target='_blank' href='Reports/PEP018.php" .$link . "'>Household Average Number of Visits by Zip Code - PEP018</a></li>\n";	
    echo "</ul></td></tr></table></center>\n";
}
?>