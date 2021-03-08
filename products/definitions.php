<?php
/**
 * products/definitions.php
 * written: 7/16/2020
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

function listProductsDefinitions() {
	global $control;
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=definitions&add=1";
// no need for sort_products table, use INNER JOIN instead 
	$sql = "SELECT * FROM products 
			INNER JOIN products_nameinfo
			ON products.id = products_nameinfo.productID AND products_nameinfo.languageID=1
			ORDER BY $control[field] $control[order]";
			
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 	
		<div class='row p-3'>
<?php		
		if ($control['prod_def_update'])
			echo "<div class='col-sm' style='color:#841E14;'><i class='fa fa-plus pr-2 p-1'></i><a style='color:inherit;' href='$link' >Add Product</a></div>\n";
?>		
			<div class='col-sm text-right'><?php doDefinitionsCount(); ?></div>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doDefinitionsHeadings();			
			foreach($result as $row) {	
				$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=definitions&edit=1&productID=$row[productID]";
				
				$inactive="";
				if (!$row['active'])
					$inactive= "<span class='alert alert-warning ml-2 p-1 border border-dark' role='alert'>INACTIVE</span>";				
				
				if ($row['personal'] == "Yes")
					$portion="personal";
				else
					$portion="shared";
				
				$containers=getTableRow( "containers", $control['db'], $row['container'] );
				$measures=getTableRow( "measures", $control['db'], $row['measure'] );					
					
				echo "
				<tr>
				<td class='border border-dark bg-gray-3 p-1'><a style='color:#841E14;text-decoration:underline' href='$link'>$row[name]</a> $inactive</td>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[productID]</td>				
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[age_group]</td>		
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[for_gender]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$portion</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$containers[name]</td>	
				<td class='border border-dark bg-gray-3 p-1'>$row[amount] $measures[name]</td>		
				</tr>";	
			}	 
?>		
		</table>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function doDefinitionsCount() {
	global $control;
	
	$sql = "SELECT active FROM products";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$tProducts = $stmt->rowCount();
	$tActive = 0;	
	$result = $stmt->fetchAll();	
	foreach($result as $row) 
		if ($row['active']) $tActive++;
			
	echo "<b>$tActive</b> of $tProducts total product(s) are active."; 
}	

function doDefinitionsHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=definitions";

	if ($control['order'] == "asc")
		$link .= "&order=desc";
	else
		$link .= "&order=asc";	

	$nCarrot="";
	$agCarrot="";	
	$piCarrot="";		
	$fgCarrot="";		
	$pCarrot="";	
	$p2Carrot="";		
	$emCarrot="";	
	$laCarrot="";

	if ( $control['order'] == "asc" ) {	
		if ($control['field'] == "name")
			$nCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";
		elseif ($control['field'] == "productID")
			$piCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
		elseif ($control['field'] == "age_group")
			$agCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
		elseif ($control['field'] == "for_gender")
			$fgCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "personal")
			$pCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
	
	} elseif ( $control['order'] == "desc" ) {	
		if ($control['field'] == "name")
			$nCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";
		elseif ($control['field'] == "age_group")
			$agCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
		elseif ($control['field'] == "productID")
			$piCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
		elseif ($control['field'] == "for_gender")
			$fgCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "personal")
			$pCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
	}		

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=name'>Name</a>$nCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=productID'>Id</a>$piCarrot</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=age_group'>Age Group</a>$agCarrot</th>		
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=for_gender'>Gender</a>$fgCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=personal'>Portion</a>$pCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Container</th>	
	<th class='border border-dark bg-gray-4 p-1'>Amount</th>		
	</tr>
	</thead>";	
}

function productForm($action, $errMsg) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";	
	
	$values= getProductsValues($action);
	
?>
	<div class="container-fluid bg-gray-2 m-0">
		<div class="container p-3">
			<div class="card border border-dark">
				<h5 class="card-header bg-gray-5 text-center"><?php echo ucname($action); ?> Product</h5> 
				<div class="card-body bg-gray-4">
<?php	  
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}		  
?>	  				
					<form method='post' action='products/addupdateproduct.php'>  				
				
					<div class='col-2'><h3><?php echo $values['name']; ?></h3></div>

					<div class='col-3 text-right p-3'><h5>Names</h5></div><div><?php doNames($action); ?></div>

					<div class='col-3 text-right p-3'><h5>Properties</h5></div><div><?php doProperties($values); ?></div>
					
					<div class='col-3 text-right p-3'><h5>Sizes / Types</h5></div><div><?php doSizesTypes($action, $disabled); ?></div>
			
					<div class='col-3 text-right p-3'><h5>Duration</h5></div><div><?php doDuration($values); ?></div>
						
					<div class='mt-3 text-center'>
<?php					
					if ($control['prod_def_update'])
						echo "<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='$action'>Save</button>";
?>					
						<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
					</div>	
					<input type= 'hidden' name= 'tab' value= 'definitions'>	
					<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>
<?php				if ($action=="edit")					
						echo "<input type= 'hidden' name= 'productID' value= '$control[productID]'>";
?>					
					</form>
				</div>
			</div>
		</div>	
	</div>	

<?php	
}

function doNames($action) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";
	
	$sql = "SELECT * FROM languages ORDER BY name";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach($result as $row) {	
	
		$name="";
		
		if (isset($_GET['errCode'])) {
			$n = "name" . $row['id'];
			
			$name =$_GET[$n];			
			
		} elseif ($action == "edit") {
			$sql2 = "SELECT * FROM products_nameinfo WHERE productID = :productID AND languageID = :languageID";
			$stmt2 = $control['db']->prepare($sql2);
			$stmt2->bindParam(':languageID', $row['id'], PDO::PARAM_INT);
			$stmt2->bindParam(':productID', $_GET['productID'], PDO::PARAM_INT);				
			$stmt2->execute();	
			$total = $stmt2->rowCount();
			if ($total > 0)	{
				$row2 = $stmt2->fetch();
				$name = $row2['name'];
			} 
		}	

		$name =htmlEntities($name, ENT_QUOTES);			
			
		echo "
		<div class='form-row'>
			<div class='col-4'>
				<div class='form-group text-right mb-1'><label class='pt-2'>$row[name]</label></div>
			</div>	
			<div class='col-3'>
				<div class='form-group mb-1'><input type='text' class='form-control' name='name$row[id]' id='name$row[id]' value='$name' $disabled></div>		
			</div>	
		</div>\n";				
	}		
}	

function doProperties($values) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";	
	
?>	
	<div class='form-row'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>Active</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><?php yesNoSwitch('active', $values['active'], $disabled); ?></div>		
		</div>	
	</div>	

	<div class='form-row'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>Age Group</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><?php selectAgeGroup( "age_group", $values['age_group'] ); ?></div>		
		</div>	
	</div>	

	<div class="form-row">
		<div class="col-4">
			<div class="form-group text-right mb-1"><label class='pt-2'>For Gender</label></div>
		</div>	
		<div class="col-3">
			<div class="form-group mb-1"><?php genderRadio('for_gender', $values['for_gender']); ?></div>
		</div>	
	</div>		

	<div class="form-row">
		<div class="col-4">
			<div class="form-group text-right mb-1"><label class='pt-2'>Hypoallergenic</label></div>
		</div>	
		<div class="col-3">
			<div class="form-group mb-1"><?php yesNoSwitch('hypoallergenic', $values['hypoallergenic'], $disabled); ?></div>
		</div>	
	</div>		

	<div class="form-row">
		<div class="col-4">
			<div class="form-group text-right mb-1"><label class='pt-2'>For Incontinence</label></div>
		</div>	
		<div class="col-3">
			<div class="form-group mb-1"><?php yesNoSwitch('for_incontinence', $values['for_incontinence'], $disabled); ?></div>
		</div>	
	</div>	

	<div class='form-row'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>Container</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><?php selectContainer( "container", $values['container'] ); ?></div>		
		</div>	
	</div>	

	<div class='form-row'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>Amount</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><input type='text' class='form-control' name='amount' id='amount' value='<?php echo $values['amount']; ?>' <?php echo $disabled; ?>></div>		
		</div>	
	</div>						

	<div class='form-row'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>Measure</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><?php selectMeasure( "measure", $values['measure'] ); ?></div>		
		</div>	
	</div>		
	
<?php	
	
}

function doSizesTypes($action, $disabled) {
	global $control;

	if (isset($_GET['errCode']))
		$numSizes=$_GET['numSizes'];
	elseif ($action == "edit") {	
		$sql = "SELECT * FROM products_typeinfo WHERE productID = $_GET[productID] ORDER BY typenum";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$numSizes = $stmt->rowCount();	
		$result = $stmt->fetchAll();		
	} else 
		$numSizes=0;	
?>

	<div class='form-row'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>Number of Sizes/Types</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><?php selectNumSizesTypes( "numSizes", $numSizes, $disabled ); ?></div>		
		</div>	
	</div>	

<?php
	if (isset($_GET['errCode']))
		for ($n = 1; $n <= $numSizes; $n++) {
			$type= "typenum" . $n;	
			showSizeType($n, $_GET[$type], $disabled);	
		}	
	elseif ($action == "edit")
		foreach($result as $row) 
			showSizeType($row['typenum'], $row['type'], $disabled);				
	
	$start=$numSizes+1;
	for ($n = $start; $n <= MAX_SIZES_TYPES; $n++) {
		echo "
		<div class='form-row d-none' id='size_$n'>
			<div class='col-4'>
				<div class='form-group text-right mb-1'><label class='pt-2'>$n.</label></div>
			</div>	
			<div class='col-3'>
				<div class='form-group mb-1'><input type='text' class='form-control' name='typenum$n' value='' ></div>		
			</div>	
		</div>\n";					
	}	
}

function showSizeType($n, $type, $disabled) {	

	$type = htmlEntities($type, ENT_QUOTES);	

	echo "
	<div class='form-row' id='size_$n'>
		<div class='col-4'>
			<div class='form-group text-right mb-1'><label class='pt-2'>$n.</label></div>
		</div>	
		<div class='col-3'>
			<div class='form-group mb-1'><input type='text' class='form-control' name='typenum$n' id='typenum$n' value='$type' $disabled></div>		
		</div>	
	</div>\n";
}


function selectNumSizesTypes( $name, $value, $disabled ) {
	global $control;
	
/* jQuery controls for selector input are located in products.php */	
	echo "<select class='form-control bg-gray-1' id= 'numSizesTypes' name= '$name' $disabled/>\n"; 	
	
	for ($n = 0; $n <= MAX_SIZES_TYPES; $n++) {
		echo "<option";
		if ( $value == $n ) echo " selected ";
		echo " value ='$n' $n >$n</option>\n";
	}	
	
    echo "</select>";
}

function doDuration($values) {
	global $control;
		
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";	
	
	$naclass="";
	$oneclass="";
	if ($values['personal'] == "Yes")
		$oneclass="d-none";
	else
		$naclass="d-none";		
?>	
	<div class="form-row">
		<div class="col-4">
			<div class="form-group text-right mb-1"><label class='pt-2'></label></div>
		</div>	
		<div class="col-3">
			<div class="form-group mb-1"><?php portionRadio('personal', $values['personal']); ?></div>
		</div>	
	</div>							

	<div class='form-row'>
		<div class='col-4 text-right'><b>Household Size</b></div>
		<div class='col-3 text-center'><b>Duration (days)</b></div>
	</div>
	
	<div class='form-row'>
		<div class='col-4'>
			<div id='dur_2_col' class='<?php echo $oneclass; ?> form-group text-right mb-1'><label class='pt-2'>1 - 2</label></div>
			<div id='dur_2_col_na' class='<?php echo $naclass; ?> form-group text-right mb-1'><label class='pt-2'>N/A</label></div>						
		
		</div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_2' id='duration_2' value='<?php echo $values['duration_2']; ?>' <?php echo $disabled; ?> ></div>		
		</div>	
	</div>
	
	<div class='form-row <?php echo $oneclass; ?>' id='dur_4_row'>
		<div class='col-4'><div class='form-group text-right mb-1'><label class='pt-2'>3 - 4</label></div></div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_4' value='<?php echo $values['duration_4']; ?>'  <?php echo $disabled; ?> ></div>		
		</div>	
	</div>	
	
	<div class='form-row <?php echo $oneclass; ?>' id='dur_6_row'>
		<div class='col-4'><div class='form-group text-right mb-1'><label class='pt-2'>5 - 6</label></div></div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_6' value='<?php echo $values['duration_6']; ?>'  <?php echo $disabled; ?> ></div>		
		</div>	
	</div>						
	<div class='form-row <?php echo $oneclass; ?>' id='dur_8_row'>
		<div class='col-4'><div class='form-group text-right mb-1'><label class='pt-2'>7 - 8</label></div></div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_8' value='<?php echo $values['duration_8']; ?>'  <?php echo $disabled; ?> ></div>		
		</div>	
	</div>	
	<div class='form-row <?php echo $oneclass; ?>' id='dur_10_row'>
		<div class='col-4'><div class='form-group text-right mb-1'><label class='pt-2'>9 - 10</label></div></div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_10' value='<?php echo $values['duration_10']; ?>'  <?php echo $disabled; ?> ></div>		
		</div>	
	</div>	
	<div class='form-row <?php echo $oneclass; ?>' id='dur_12_row'>
		<div class='col-4'><div class='form-group text-right mb-1'><label class='pt-2'>11 - 12</label></div></div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_12' value='<?php echo $values['duration_12']; ?>'  <?php echo $disabled; ?> ></div>		
		</div>	
	</div>	
	<div class='form-row <?php echo $oneclass; ?>' id='dur_14_row'>
		<div class='col-4'><div class='form-group text-right mb-1'><label class='pt-2'>13+</label></div></div>
		<div class='col-3'><div class='form-group mb-1'><input type='text' class='form-control' 
		name='duration_14' value='<?php echo $values['duration_14']; ?>'  <?php echo $disabled; ?> ></div>		
		</div>	
	</div>		
<?php					
}	

function getProductsValues($action) {
	global $control;
	
	$arr = [	
		'name'				=> "",	
		'active'			=> 1,
		'age_group' 		=> "all",		
		'personal'			=> "Yes",	
		'hypoallergenic' 	=> "N/A",
		'for_gender' 		=> "Both",
		'for_incontinence'	=> "No",
		'container'			=> "bar",
		'amount'			=> "",
		'measure'			=> "ct",
		'duration_2'		=> "",	
		'duration_4'		=> "",	
		'duration_6'		=> "",	
		'duration_8'		=> "",	
		'duration_10'		=> "",	
		'duration_12'		=> "",	
		'duration_14'		=> ""		
	];	

	if (isset($_GET['errCode'])) {
		
		$arr['active']			= $_GET['active'];		
		$arr['age_group']		= $_GET['age_group'];
		$arr['personal']		= $_GET['personal'];
		$arr['hypoallergenic']	= $_GET['hypoallergenic'];	
		$arr['for_gender']		= $_GET['for_gender'];
		$arr['for_incontinence']= $_GET['for_incontinence'];	
		$arr['container']		= $_GET['container'];	
		$arr['amount']			= $_GET['amount'];		
		$arr['measure']			= $_GET['measure'];
		$arr['duration_2']		= $_GET['duration_2'];	
		$arr['duration_4']		= $_GET['duration_4'];	
		$arr['duration_6']		= $_GET['duration_6'];	
		$arr['duration_8']		= $_GET['duration_8'];	
		$arr['duration_10']		= $_GET['duration_10'];		
		$arr['duration_12']		= $_GET['duration_12'];		
		$arr['duration_14']		= $_GET['duration_14'];			

	} elseif ($action == "edit") {

		$sql = "SELECT * FROM products 
				INNER JOIN products_nameinfo
				ON products.id = products_nameinfo.productID AND products_nameinfo.languageID=1 AND products.id = $_GET[productID]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();	
	}	
	return $arr;
}	


function selectAgeGroup( $name, $value ) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";	
	
//all', 'infant', 'youth', 'teen', 'adult', 'inf_youth', 'youth_teen_adult', 'teen_adult'
	echo "<select class='form-control bg-gray-1' name= '$name' $disabled/>\n"; 

	echo "<option";
	if ( $value == "all" )
		echo " selected";
	echo " value ='all' all >all</option>\n";
	echo "<option";
	if ( $value == "infant" )
		echo " selected ";
	echo " value ='infant' infant >infant&#160;&#160;(0-3)</option>\n";	
	echo "<option";
	if ( $value == "inf_youth" )
		echo " selected ";
	echo " value ='inf_youth' inf_youth >inf_youth&#160;&#160;(0-11)</option>\n";	
	echo "<option";
	if ( $value == "youth" )
		echo " selected ";
	echo " value ='youth' youth >youth&#160;&#160;(4-11)</option>\n";	
	echo "<option";
	if ( $value == "youth_teen_adult" )
		echo " selected ";
	echo " value ='youth_teen_adult' youth_teen_adult >youth_teen_adult&#160;&#160;(4 +)</option>\n";	
	echo "<option";
	if ( $value == "teen" )
		echo " selected ";
	echo " value ='teen' teen >teen&#160;&#160;(12-17)</option>\n";
	echo "<option";
	if ( $value == "teen_adult" )
		echo " selected ";
	echo " value ='teen_adult' teen_adult >teen_adult&#160;&#160;(12 +)</option>\n";		
	echo "<option";
	if ( $value == "adult" )
		echo " selected ";
	echo " value ='adult' adult >adult&#160;&#160;(18 +)</option>\n";
    echo "</select>";

}

function selectContainer( $name, $value ) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";	
	
	echo "<select class='form-control bg-gray-1' name= '$name' $disabled/>\n"; 
	
	$sql = "SELECT * FROM containers ORDER BY name";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach($result as $row) {
		echo "<option";
		if ( $value == "$row[id]" ) echo " selected ";
		echo " value ='$row[id]' >$row[name]</option>\n";
	}	
    echo "</select>\n";
}

function selectMeasure( $name, $value ) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";		
	
	echo "<select class='form-control bg-gray-1' name= '$name' $disabled/>\n"; 
	
	$sql = "SELECT * FROM measures ORDER BY name";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach($result as $row) {
		$abbrev="";
		if (!empty($row['abbrev']))
			$abbrev=" ($row[abbrev])";
		echo "<option";
		if ( $value == "$row[id]" ) echo " selected ";
		echo " value ='$row[id]' >$row[name] $abbrev</option>\n";
	}	
    echo "</select>\n";
}

function genderRadio($name, $value) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";			
	
	$bc ="";
	$mc ="";
	$fc ="";
	
	if (ucname($value) == "Both")
		$bc = "checked='checked'";	
	elseif (ucname($value) == "Male")
		$mc = "checked='checked'";
	else
		$fc = "checked='checked'";		

	echo "
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "1" . "' name='$name' value='Both' $bc $disabled>
		<label for ='$name" . "1" . "'>Both</label>
	</div>
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "2" . "' name='$name' value='Male' $mc $disabled>
		<label for ='$name" . "2" . "'>Male</label>
	</div>	
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "3" . "' name='$name' value='Female' $fc $disabled>
		<label for ='$name" . "3" . "'>Female</label>
	</div>\n";	
}

function portionRadio($name, $value) {
	global $control;
	
	$disabled="";
	if (!$control['prod_def_update'])
		$disabled="disabled='disabled'";		
	
	$pc ="";
	$sc ="";
	
	if (ucname($value) == "Yes")
		$pc = "checked='checked'";	
	else
		$sc = "checked='checked'";		

	echo "
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "1" . "' name='$name' value='Yes' $pc onclick='document.getElementById(" . '"duration_2"' . ").focus()' $disabled>
		<label for ='$name" . "1" . "'>Personal</label>
	</div>
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "2" . "' name='$name' value='No' $sc onclick='document.getElementById(" . '"duration_2"' . ").focus()' $disabled>
		<label for ='$name" . "2" . "'>Shared</label>
	</div>\n";	
}

?>