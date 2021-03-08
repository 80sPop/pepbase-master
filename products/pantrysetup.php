<?php
/**
 * products/pantrysetup.php
 * written: 8/1/2020
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

function pantrySetupForm() {
	global $control;
	
	$disabled="";
	if (!$control['prod_setup_update'])
		$disabled="disabled='disabled'";	
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=setup&add=1";


	if ( $control['field'] == "shelf_bin" )
//		$field="shelf, bin";
		$order="shelf $control[order], bin $control[order], products_pantryinfo.productID, products_pantryinfo.typenum";	
	elseif ( $control['field'] == "carried" || $control['field'] == "name" )
		$order = "$control[field] $control[order], products_pantryinfo.productID, products_pantryinfo.typenum";	
	
	else
//		$field = $control['field'];
		$order = "$control[field] $control[order], products_pantryinfo.typenum";
		
// no need for sort_products table, use INNER JOIN instead 
	$sql = "SELECT * FROM products_pantryinfo
			INNER JOIN products ON products.id = products_pantryinfo.productID AND products.active = 1
			INNER JOIN products_nameinfo ON products_pantryinfo.productID = products_nameinfo.productID AND products_nameinfo.languageID=1
			WHERE pantry = $control[users_pantry_id]
			ORDER BY $order";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 	
	
	
		<div class='row p-3'>
			<div class='col-sm'></div>
			<div class='col-sm text-right'><?php doShowSetupCount(); ?></div>			
		</div>		
		
		<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'> 		
		<table class='table mb-2'>
<?php
			doSetupHeadings();	
			$currProductID = 0;
			foreach($result as $row) {	
				$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=definitions&productID=$row[productID]";
				$p = $row['productID'] . "T" . $row['typenum'];
				$carried="carried" . $p;
				$portion_limit="portion_limit" . $p;
				$type="";
				if ( $row['typenum'] > 0 ) {
//					$products_typeinfo=getPantryTypeInfoRow( $control['db'], $row['productID'], $row['typenum'] );	
					if ($products_typeinfo=getPantryTypeInfoRow( $control['db'], $row['productID'], $row['typenum'] ))	
						$type=$products_typeinfo['type'];
				}	
				
				if ($row['productID'] != $currProductID) {
					echo "
					<tr>
					<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>
						<a style='color:#841E14;text-decoration:underline' href='$link'>$row[name]</a>
					</td>		
					<td class='border border-dark border-right-0 bg-gray-3 p-1'>"; 
						yesNoSwitch($carried, $row['carried'], $disabled);
					echo "
					</td>";
					$currProductID=$row['productID'];
					
				} else {
					echo "
					<tr>
					<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>&nbsp;</td>
					<td class='border border-dark border-right-0 bg-gray-3 p-1'>&nbsp;</td>";					
				}	
					
				echo "
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$type</td>	

				<td class='border border-dark border-right-0 bg-gray-3 p-1'>
					<div class='form-group mb-1 '>
						<input type='text' class='form-control d-inline mr-2' style='width:50px;' name='shelf$p' id='shelf$p' value='$row[shelf]' $disabled> / 
						<input type='text' class='form-control d-inline ml-2' style='width:50px;' name='bin$p' id='bin$p' value='$row[bin]' $disabled>
					</div>
				</td>

				<td class='border border-dark bg-gray-3 p-1'>";
					if ( $row['personal'] == "Yes" )
						selectPortionLimit( $portion_limit, $row['portion_limit'], $disabled );
				echo "
				</td>
				</tr>\n";
			}	 
?>		
		</table>

		<div class='mt-3 text-center'>
<?php					
		if ($control['prod_setup_update'])
			echo "<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='saveSetup'>Save</button>";
?>			
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
		</div>	

		<input type= 'hidden' name= 'tab' value= 'setup'>	
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>	
		<input type= 'hidden' name= 'field' value= '<?php echo $control['field']; ?>'>	
		<input type= 'hidden' name= 'order' value= '<?php echo $control['order']; ?>'>			
	
		</form>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function doShowSetupCount() {
	global $control;
	
	$sql = "SELECT * FROM products_pantryinfo
			INNER JOIN products ON products.id = products_pantryinfo.productID
			WHERE pantry = $control[users_pantry_id] AND products.active = 1	
			GROUP BY productID";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$tActive = $stmt->rowCount();
	$tCarried = 0;	
	$result = $stmt->fetchAll();	
	foreach($result as $row) 
		if ($row['carried'] == "Yes") $tCarried++;
			
	echo "<b>$tCarried</b> of $tActive active product(s) are carried by pantry."; 
}	

function doSetupHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=setup";

	if ($control['order'] == "asc")
		$link .= "&order=desc";
	else
		$link .= "&order=asc";	
	
	$sbCarrot="";	
	$nCarrot="";
	$cCarrot="";	
	$pCarrot="";			

	if ( $control['order'] == "asc" ) {	
		if ($control['field'] == "shelf_bin")
			$sbCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "name")
			$nCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";
		elseif ($control['field'] == "carried")
			$cCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "portion_limit")
			$pCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
	
	} elseif ( $control['order'] == "desc" ) {	
		if ($control['field'] == "shelf_bin")
			$sbCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "name")
			$nCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";
		elseif ($control['field'] == "carried")
			$cCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "portion_limit")
			$pCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
	}		

	echo "
	<thead>
	<tr>

	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=name'>Name</a>$nCarrot</th>		
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=carried'>Carried</a>$cCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Sizes / Types</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=shelf_bin'>Shelf / Bin</a>$sbCarrot</th>	
	<th class='border border-dark bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=portion_limit'>Portion Limit</a>$pCarrot</th>
	</tr>
	</thead>";	
}

function selectPortionLimit( $name, $value, $disabled ) {
	global $control;
	
	echo "<select class='form-control bg-gray-1' name='$name' $disabled/>\n"; 	
	
	echo "<option";
	if ( $value == -1 ) echo " selected ";
	echo " value ='-1' -1 >no limit</option>\n";	
	
	for ($n = 1; $n <= MAX_PORTION_LIMIT; $n++) {
		echo "<option";
		if ( $value == $n ) echo " selected ";
		echo " value ='$n' $n >$n</option>\n";
	}	
	
    echo "</select>";
}

function updatePantryInfo() {
	global $control;
	
	$err=0;			
	$sql = "SELECT * FROM products_pantryinfo
			INNER JOIN products ON products.id = products_pantryinfo.productID AND products.active = 1
			INNER JOIN products_nameinfo ON products_pantryinfo.productID = products_nameinfo.productID AND products_nameinfo.languageID=1
			WHERE pantry = $control[users_pantry_id]
			ORDER BY products_pantryinfo.productID, products_pantryinfo.typenum";
	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	$isFirstProduct=true;

	foreach($result as $row) {	
		if (!$err) {
			$p=$row['productID'] . "T" . $row['typenum'] ;
			$shelf="shelf" . $p;
			$bin="bin" . $p;
			$carried="carried" . $p;
			$portion_limit="portion_limit".$p;	
			
			if (!is_numeric(trim($_POST[$shelf])) || !is_numeric(trim($_POST[$bin])) )	
				$err=62;
			else {
				
// extra logic needed here for when product types are not arranged together by shelf and bin number				
				if ($isFirstProduct || $row['productID'] > $currProductID) {	
					$isFirstProduct=false;				
					$currProductID=$row['productID'];
					$dataCarried = "No";					
					if (isset($_POST[$carried]))
						$dataCarried="Yes";
				} else { 
					if ($row['typenum']==1) {
						$dataCarried = "No";					
						if (isset($_POST[$carried]))
							$dataCarried="Yes";
					} elseif ($dataCarried == "No" && isset($_POST[$carried]))
						$dataCarried="Yes";
				}		
				
				$data=fillData($row, $shelf, $bin, $portion_limit, $dataCarried);

// begin debug 
//echo "product=$row[productID] type=$row[typenum] carried=$dataCarried <br>";
// end de-bug 	

				writePantryInfo($data);		
						
			} 
		}	
	}	
	
	$control['errCode'] = $err;
}

function fillData($row, $shelf, $bin, $portion_limit, $dataCarried) {
	global $control;
	
	$data = [	
		'pantry' => $control['users_pantry_id'],
		'productID' => $row['productID'],
		'typenum' => $row['typenum'],
		'shelf' => $_POST[$shelf],
		'bin' => $_POST[$bin],
		'carried' => $dataCarried,
		'instock' => $row['instock'],
		'portion_limit' => -1
	];
	
	if ( $row['personal'] == "Yes" )
		$data['portion_limit']=$_POST[$portion_limit];
	if ( $data['carried'] == "No" )
		$data['instock']=0;		
	
	return $data;
}	

function writePantryInfo($data) {
	global $control;
	
	$sql = "UPDATE products_pantryinfo 
			SET shelf = :shelf,
				bin = :bin,
				carried = :carried,
				instock = :instock,
				portion_limit = :portion_limit			
			WHERE pantry =:pantry AND productID = :productID AND typenum = :typenum";
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);	
	
}

function updateAllTypes($data) {
	global $control;
	$sql = "UPDATE products_pantryinfo 
			SET carried = :carried
			WHERE pantry =:pantry AND productID=:productID";
	$stmt= $control['db']->prepare($sql);
	$stmt->bindParam(':carried', $data['carried'], PDO::PARAM_INT);	
	$stmt->bindParam(':pantry', $data['pantry'], PDO::PARAM_INT);	
	$stmt->bindParam(':productID', $data['productID'], PDO::PARAM_INT);	
	$stmt->execute();	

}
?>