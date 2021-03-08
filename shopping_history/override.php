<?php
/**
 * shopping_history/override.php
 * written: 10/18/2020
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
	require_once('../header.php'); 	
	require_once('../functions.php');
	require_once('../households/eligibility.php');	
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	
	$control=fillControlArray($control, $config, "tables");

/* MAINLINE */
	
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
			ORDER BY name, shelf, bin";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	doOverrideHeader($total, $result);	
	
	$household=getHouseholdRow( $control['db'], $control['hhID'] );
?>	
	<form method='post' action='addhistory.php'> 
		<input type='hidden' name='hhID' value='<?php echo $control['hhID']; ?>'>
		
		<div class="container p-3"> 
			<div class="card border border-dark">
				<h5 class="card-header bg-gray-5 text-center">Override Shopping Eligibility</h5>
				<div class="card-body bg-gray-4">	
					<div class='row p-0'>
						<div class='col-sm'>HOUSEHOLD OF: <b><?php echo ucname($household['firstname']) . " " . ucname($household['lastname']); ?></b></div>
						<div class='col-sm text-right'><button class='btn btn-primary mb-2 text-white' type='submit' name='print'>Print Approved Shopping List</button></div>			
					</div>	

					<table class='table mb-2'>
<?php
	doOverrideHeadings();	

	foreach($result as $row) {
		
		$list=determineEligibility($row);		
	
		echo "
		<tr>
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[name]</td>		
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$list[num_eligible]</td>			
		<td class='border border-dark bg-gray-3 p-1'>
			<input class='form-control mr-sm-2' type='text' name='approved$row[productID]' id='approved$row[productID]' value='$list[num_eligible]'>		
		</td>
		</tr>
		<input type='hidden' name='quantity_oked$row[productID]' value='$list[num_eligible]'>\n";		
	}	
?>	
					</table>
				</div>
			</div>
		</div> 
	</form>
	

<?php	
function doOverrideHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=containers";

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Product</a></th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Eligible For</a></th>	
	<th class='border border-dark bg-gray-4 p-1'>Approved</th>
	</tr>
	</thead>";	
}	
?> 