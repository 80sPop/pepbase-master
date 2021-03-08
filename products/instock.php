<?php
/**
 * products/instock.php
 * written: 8/4/2020
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

function instockForm() {
	global $control;
	
	if ( $control['field'] == "shelf_bin" )
		$order = "shelf $control[order], bin $control[order], products_nameinfo.name";
	elseif ( $control['field'] == "instock" )
		$order = "instock $control[order], products_nameinfo.name";		
	else
		$order = "$control[field] $control[order]";	

	$sql = "SELECT * FROM products_pantryinfo
			INNER JOIN products ON products.id = products_pantryinfo.productID AND products.active = 1
			INNER JOIN products_nameinfo ON products_pantryinfo.productID = products_nameinfo.productID AND products_nameinfo.languageID=1
			WHERE pantry = $control[users_pantry_id] AND products_pantryinfo.carried = 'Yes'
			group by products.id			
			ORDER BY $order";
			
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$tCarried = $stmt->rowCount();	
	$tInstock = instockCount();	
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 	
	
		<div class='row p-3'>
			<div class='col-sm'></div>
			<div class='col-sm text-right'><?php echo "<b>$tInstock</b> of $tCarried carried products are in stock."; ?></div>			
		</div>		
		
		<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'> 		
		<table class='table mb-2'>
<?php
			doInstockHeadings();	
			foreach($result as $row) {	
				$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=definitions&productID=$row[productID]";
				$name="<a style='color:#841E14;text-decoration:underline' href='$link'>$row[name]</a>";
				if ($row['typenum'] == 0) 
					doInstockLine($name, $row['productID'], 0, $row['shelf'], $row['bin'], "", $row['instock']);					
				else
					doInstockFormSizeType($name, $row['productID'], $row['pantry'], $row['instock']);		

			}	 
?>		
		</table>

		<div class='mt-3 text-center'>
<?php					
		if ($control['instock_update'])
			echo "<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='saveInstock'>Save</button>";
?>							

		</div>	

		<input type= 'hidden' name= 'tab' value= 'instock'>	
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>	
		<input type= 'hidden' name= 'field' value= '<?php echo $control['field']; ?>'>	
		<input type= 'hidden' name= 'order' value= '<?php echo $control['order']; ?>'>			
	
		</form>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function instockCount() {
	global $control;
	
	$total=0;
	$isInstock=0;	
	
	$sql = "SELECT * FROM products_pantryinfo
			INNER JOIN products ON products.id = products_pantryinfo.productID AND products.active = 1
			WHERE pantry = $control[users_pantry_id] AND products_pantryinfo.carried = 'Yes'
			ORDER BY shelf, bin";
			
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();
	$first=1;
	foreach($result as $row) {	
		if ($first) {
			$first=0;
			$currProductID=$row['productID'];
		}
		if ($currProductID !=  $row['productID']) {
			if ($isInstock)
				$total++;
			$currProductID=$row['productID'];
			$isInstock=0;
		}
		if ($row['instock'])
			$isInstock=1;
	}	
	if ($isInstock)
		$total++;
		
	return $total;
}	

function doInstockFormSizeType($name, $productID, $pantry, $value) {
	global $control;
	
	if ( $control['field'] == "shelf_bin" )
		$order = "shelf $control[order], bin $control[order]";
	else
		$order = "shelf, bin";		

	$sql = "SELECT * FROM products_pantryinfo
			JOIN products_typeinfo ON products_typeinfo.productID = products_pantryinfo.productID 
			AND products_pantryinfo.typenum = products_typeinfo.typenum
			WHERE products_pantryinfo.productID = $productID and products_pantryinfo.pantry=$pantry
			ORDER BY $order";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	$first=1;
	foreach($result as $row) {
		if ($first) 
			doInstockLine($name, $productID, $row['typenum'], $row['shelf'], $row['bin'], $row['type'], $row['instock']);
		else
			doInstockLine("", $productID, $row['typenum'], $row['shelf'], $row['bin'], $row['type'], $row['instock']);			
		$first=0;
	}	
}	

function doInstockHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=instock";

	if ($control['order'] == "asc")
		$link .= "&order=desc";
	else
		$link .= "&order=asc";	
	
	$sbCarrot="";	
	$nCarrot="";
	$inCarrot="";			

	if ( $control['order'] == "asc" ) {	
		if ($control['field'] == "shelf_bin")
			$sbCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "name")
			$nCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";
		elseif ($control['field'] == "instock")
			$inCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
	
	} elseif ( $control['order'] == "desc" ) {	
		if ($control['field'] == "shelf_bin")
			$sbCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "name")
			$nCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";
		elseif ($control['field'] == "instock")
			$inCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
	}		

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=name'>Name</a>$nCarrot</th>		
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=shelf_bin'>Shelf / Bin</a>$sbCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Size or Type</th>
	<th class='border border-dark bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=instock'>In Stock Status</a>$inCarrot</th>
	</tr>
	</thead>";	
}

function doInstockLine($name, $productID, $typenum, $shelf, $bin, $type, $instock) {
	global $control;

	$index="instock" . $productID ."T" . $typenum;
	echo "
	<tr>
	<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$name</a>
	</td>
	<td class='border border-dark border-right-0 bg-gray-3 p-1'>$shelf / $bin</td>
	<td class='border border-dark border-right-0 bg-gray-3 p-1'>$type</td>
	<td class='border border-dark bg-gray-3 p-1'>";
	inOutSwitch($index, $instock);
	echo "</td></tr>\n";		
}

function inOutSwitch($name, $value) {
	global $control;
	
	$disabled="";
	if (!$control['instock_update'])
		$disabled="disabled='disabled'";
	
	$yc ="";
	$value=ucname(strval($value));
		
	if ($value == "Yes" || $value == "1")
		$yc = "checked='checked'";
	
	echo "<input type='checkbox' name='$name' data-toggle='switch' data-on-color='primary' data-on-text='IN' data-off-color='default' data-off-text='OUT' $yc $disabled>\n";	
}

function updateInstockStatus() {
	global $control;
	
	$err=0;			

	$sql = "SELECT * FROM products_pantryinfo
			INNER JOIN products ON products.id = products_pantryinfo.productID AND products.active = 1
			WHERE pantry = $control[users_pantry_id] AND products_pantryinfo.carried = 'Yes'";			
	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	foreach($result as $row) {	
		$index = "instock" . $row['productID'] . "T" . $row['typenum'];
		$instock=0;
		if (isset($_POST[$index]))
			$instock=1;
		$sql = "UPDATE products_pantryinfo 
				SET instock = :instock
				WHERE pantry =:pantry AND productID = :productID AND typenum = :typenum";
		$stmt= $control['db']->prepare($sql);
		$stmt->bindParam(':instock', $instock, PDO::PARAM_INT);
		$stmt->bindParam(':pantry', $control['users_pantry_id'], PDO::PARAM_INT);		
		$stmt->bindParam(':productID', $row['productID'], PDO::PARAM_INT);			
		$stmt->bindParam(':typenum', $row['typenum'], PDO::PARAM_INT);		
		$stmt->execute();	
	}	

}
?>