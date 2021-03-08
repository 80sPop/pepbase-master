<?php
/**
 * households/eligibility.php
 * written: 9/21/2020
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

function listEligibility() {
	global $control;
	
	$sql = "SELECT 	productID, 
					name, 
					personal, 
					duration_2,
					duration_4, 
					duration_6,
					duration_8,	
					duration_10,	
					duration_12,	
					duration_14,
					for_gender,	
					hypoallergenic,
					for_incontinence,	
					age_group
			FROM products 
			INNER JOIN products_nameinfo
			ON products.id = products_nameinfo.productID AND products_nameinfo.languageID=1
			WHERE products.active=1
			ORDER BY $control[field] $control[order]";	
			
//	$sql = "SELECT * FROM products 
//			INNER JOIN products_nameinfo
//			ON products.id = products_nameinfo.productID AND products_nameinfo.languageID=1
//			WHERE products.active=1
//			ORDER BY $control[field] $control[order]";				

//	$sql = "SELECT * FROM products 
//			WHERE active=1
//			ORDER BY $control[field] $control[order]";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 
	
		<div class='row p-3'>
			<div class='col-sm'>&nbsp;</div>
			<div class='col-sm text-right'><?php echo "<b>$total</b> active products in Pepbase.</div>"; ?>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doEligibilityHeadings();			
			foreach($result as $products) {	
				$line=determineEligibility($products);
				
				$link= "products.php?hhID=$control[hhID]&tab=definitions&edit=1&productID=$products[productID]";				
				$name= "<a style='color:#841E14;text-decoration:underline;' href='$link'>$line[name]</a>";
				echo "
				<tr>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$name</td>		
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$line[num_eligible]</td>	
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$line[last_received]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$line[next_eligible]</td>	
				<td class='border border-dark bg-gray-3 p-1'>$line[explanation]</td>				
				</td>
				</tr>";	
			}	 
?>		
		</table>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function doEligibilityCount() {
	global $control;
	
//	$sql = "SELECT id FROM eligibility";
//	$stmt = $control['db']->prepare($sql);
//	$stmt->execute();	
//	$total = $stmt->rowCount();
//	echo "<b>$total</b> active products in Pepbase."; 
}	

function doEligibilityHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=eligibility";

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Product</a></th>
	<th class='border border-dark bg-gray-4 p-1'>Num Eligible</th>
	<th class='border border-dark bg-gray-4 p-1'>Last Received</th>	
	<th class='border border-dark bg-gray-4 p-1'>Next Eligible</th>	
	<th class='border border-dark bg-gray-4 p-1'>Explanation</th>	
	</tr>
	</thead>";	
}

function determineEligibility($products) {
	global $control;
	
	$arr = [	
		'isEligible' => 1,	
		'name' => "",
		'num_eligible' => 0,
		'last_received' => "",
		'next_eligible' => "",
		'instock' => 0,
		'explanation' => ""
	]; 	
	
	$arr['name'] = $products['name'];
	
//	$household=getHouseholdRow( $control['db'], $control['hhID'] );
	
// eligible for	
	$sql = "SELECT * FROM members WHERE householdID = $control[hhID] AND in_household=1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$hSize = $stmt->rowCount();		
	if ($products['personal'] == "No")
		$arr['num_eligible'] = 1;
	else {	
		$result = $stmt->fetchAll();		
		foreach($result as $members) {
			$arr['isEligible'] = 1;			
			$arr=filterAge($arr,$products,$members);
			$arr=filterGender($arr,$products,$members);		
			$arr=filterAllergies($arr,$products,$members);
			$arr=filterIncontinence($arr,$products,$members);	
			if ($arr['isEligible'])
				$arr['num_eligible']++;
		}
	}	
	
	if ($arr['num_eligible'] < 1)
		$arr=writeExplanation($arr);
	else {
	
	// duration
		if  ($products['personal'] == "No") {
			if ( $hSize == 1 || $hSize == 2 )
				$duration = $products['duration_2'];
			elseif ( $hSize == 3 || $hSize == 4 )
				$duration = $products['duration_4'];
			elseif ( $hSize == 5 || $hSize == 6 )
				$duration = $products['duration_6'];	
			elseif ( $hSize == 7 || $hSize == 8 )
				$duration = $products['duration_8'];
			elseif ( $hSize == 9 || $hSize == 10 )
				$duration = $products['duration_10'];	
			elseif ( $hSize == 11 || $hSize == 12 )
				$duration = $products['duration_12'];	
			elseif ( $hSize > 12 )
				$duration = $products['duration_14'];
		} else
			$duration = $products['duration_2'];	

	// last received date
		$arr['explanation'] ="";
		$quantity_used=0;
		$today=date("Y-m-d");	
		$limit = date("Y-m-d", strtotime("$today - $duration days"));	
		$sql = "SELECT * FROM consumption 
				WHERE household_id = $control[hhID]
				AND product_id = $products[productID]
				AND quantity_used > 0
				ORDER BY date";	
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$result = $stmt->fetchAll();		
		foreach($result as $consumption) {
			if ($consumption['date'] > "$limit")
				$quantity_used += $consumption['quantity_used'];		
			$arr['last_received'] = $consumption['date'];
		}
		if ($quantity_used > 0) {
			$arr['num_eligible'] = $arr['num_eligible'] - $quantity_used;
			$arr['explanation'] .= "received $quantity_used since " . date("m-d-Y", strtotime($limit)) . "; ";
		}
		if ($arr['num_eligible'] < 0)	
			$arr['num_eligible']=0;	

		
	// next eligible
		$arr['next_eligible'] = "today";
		if ($arr['num_eligible'] < 1)
			 $arr['next_eligible'] = date("Y-m-d", strtotime("$arr[last_received] + $duration days"));
		 
	// instock
		$sql = "SELECT * FROM products_pantryinfo 
				WHERE productID = $products[productID]
				AND pantry = $control[users_pantry_id] 
				AND instock =1";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();
		$total = $stmt->rowCount();	
		if ($total > 0)
			$arr['instock'] = 1;
		
	// carried
		else {
			$sql = "SELECT * FROM products_pantryinfo 
					WHERE productID = $products[productID]
					AND pantry = $control[users_pantry_id] 
					AND carried = 'No'";
			$stmt = $control['db']->prepare($sql);
			$stmt->execute();
			$total = $stmt->rowCount();	
			if ($total > 0)
				$arr['explanation'] .= "not carried by pantry;";
			else
				$arr['explanation'] .= "out of stock;";	
		}	
	}	
	
	if (isValidDate($arr['last_received'], 'Y-m-d'))
		$arr['last_received'] = date('m-d-Y', strtotime($arr['last_received']));	
	if (isValidDate($arr['next_eligible'], 'Y-m-d'))
		$arr['next_eligible'] = date('m-d-Y', strtotime($arr['next_eligible']));	
	
	if (!empty($arr['explanation']))
		$arr['explanation'] = "<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> " . $arr['explanation'];

	return $arr;	
}	

function filterAge($arr,$products,$members) {

	$pass=1;
	$age = CalcAge($members['dob'], 0);	
	
// temporary hack for baby wipes
	if ($products['age_group'] == "infant")
		if ($products['productID'] != 46) {
			if ($age > 3)
				$pass=0;
		} elseif ($age > 3 && $members['incontinent'] == "no")
			$pass=0;			 

//	if ($products['age_group'] == "infant")
//		if ($age > 3)
//			$pass=0;

	if ($products['age_group'] == "inf_youth")
		if ($age > 11)
			$pass=0;
		
	if ($products['age_group'] == "youth")
		if ($age < 4 || $age > 11)
			$pass=0;	

	if ($products['age_group'] == "teen")
		if ($age < 12 || $age > 17)
			$pass=0;

	if ($products['age_group'] == "youth_teen_adult")
		if ($age < 4)
			$pass=0;	

	if ($products['age_group'] == "teen_adult")
		if ($age < 12)
			$pass=0;	

	if ($products['age_group'] == "adult")
		if ($age < 18)
			$pass=0;	

	if ($pass == 0) {
		$arr['explanation'] .= "A";
		$arr['isEligible'] =0;
	}
	
	return $arr;
}	

function filterGender($arr, $products, $members) {

	$pass=1;
	
	if ($products['for_gender'] == "Male")
		if ($members['gender'] == "female") {
			$arr['explanation'] .= "M";
			$arr['isEligible'] =0;
		}			

	if ($products['for_gender'] == "Female")
		if ($members['gender'] == "male") {
			$arr['explanation'] .= "F";
			$arr['isEligible'] =0;
		}			

	return $arr;
}	

function filterAllergies($arr,$products,$members) {
	
	$pass=1;
	
// for now, give non-hypoallergenic products to households with allergies 	
//	if ($products['hypoallergenic'] == "No")
//		if ($members['allergies'] == "Yes")
//			$pass=0;

	return $arr;	
}

function filterIncontinence($arr,$products,$members) {

	$pass=1;
	
//		if ($products['productID']==44)
//			echo "**here*** $products[productID] $members[incontinent] $products[for_incontinence]<br>";	
	
	if ($products['for_incontinence'] == "Yes")

			
			
// temporary hack for baby wipes (product id =46 is for both infant age group and incontinence) 
//		if ($members['incontinent'] == "no") {			
		if ($members['incontinent'] == "no" && $products['productID'] != 46) {			
			$arr['explanation'] .= "I";
			$arr['isEligible'] =0;
		}			

	return $arr;
}

function writeExplanation($arr) {
	
	$ex="";
	
	if (in($arr['explanation'], "A")) {	
		if (in($arr['explanation'], "I"))
			$ex="no incontinence for age group;";
		elseif (in($arr['explanation'], "M"))
			$ex="no males in age group;";
		elseif (in($arr['explanation'], "F"))
			$ex="no females in age group;";	
		else
			$ex="no members in age group;";				
	} elseif (in($arr['explanation'], "I")) {
		if (in($arr['explanation'], "M"))
			$ex="no males with incontinence;";
		elseif (in($arr['explanation'], "F"))
			$ex="no females with incontinence;";	
		else
			$ex="no members with incontinence;";
	} elseif (in($arr['explanation'], "M"))	
		$ex="no males in household;";	
	elseif (in($arr['explanation'], "F"))	
		$ex="no females in household;";		
	
	$arr['explanation']=$ex;
	
	return $arr;
}	

function in($whole, $part) {
	
	$pos = strpos($whole, $part);
	if (is_numeric($pos))
		return true;
	else
		return false;
}
?>