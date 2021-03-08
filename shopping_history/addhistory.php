<?php
/**
 * shopping_history/addhistory.php
 * written: 9/24/2020
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
	require_once('../config.php'); 
	require_once('../functions.php');
	require_once('../households/eligibility.php');	

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	
	$control=fillControlArray($control, $config, "tables");
	
	$today = date('Y-m-d');
	$time = date('H:i:s');		
	
	
/* MAINLINE */

	$header = "Location: ../shopping_list.php?hhID=" . $control['hhID'] . "&date=$today" . "&time=$time";
	
	$sql = "SELECT * FROM products 
			INNER JOIN products_nameinfo
			INNER JOIN products_pantryinfo			
			ON products.id = products_nameinfo.productID 
			AND languageID = 1
			AND products.id = products_pantryinfo.productID 
			AND products_pantryinfo.pantry = $control[users_pantry_id]
			WHERE products.active=1
			AND FIELD(`carried`, 'yes')	
			GROUP BY products.id	
			ORDER BY shelf, bin";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
	foreach($result as $row) 
		writeShoppingHistory($row, $today, $time);
		
	writeUserLog( $control['db'], $today, $time, $control['hhID'], "consumption", 0,  "PRINT SHOPPING", $today, $time);	
	
	header($header);	

function writeShoppingHistory($row, $date, $time) {
	global $control;	
	
	$list=determineEligibility($row);
	
	$approved="approved".$row['productID'];
	
// In order to allow overrides after the shopping list is printed, all carried products are written to the consumption table.	
//	if ($_POST[$approved] > 0 || $list['num_eligible'] > 0) {	
	
		// if a product is instock, the number of products instock is equal to the number the guest is approved for. 
		// This value may change during data entry.
//		if ( $list['instock'] > 0 ) 
		if ( $list['instock'] > 0 || $list['num_eligible'] == 0) 	
			$instock = $_POST[$approved];
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
				quantity_oked,
				quantity_approved)
				
				VALUES 
				($control[hhID],
				$control[users_pantry_id],
				$row[productID],
				$row[shelf],
				$row[bin],					  
				'$date',
				'$time',
				$instock,
				$list[num_eligible],
				$_POST[$approved])";
						
		$stmt= $control['db']->prepare($sql);
		$stmt->execute();	

		$sql = "UPDATE household SET lastactivedate = '$date' WHERE id = $control[hhID]";		
		$stmt= $control['db']->prepare($sql);
		$stmt->execute();
//	}	
}
?> 