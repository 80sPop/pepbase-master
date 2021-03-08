<?php
/**
 * tables/measures.php
 * written: 9/18/2020
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

function listMeasures() {
	global $control;
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=measures&add=1";

	$sql = "SELECT * from measures ORDER BY name";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 
	
		<div class='row p-3'>
<?php		
	if ($control['measures_update'])
		echo "		
			<div class='col-sm' style='color:#841E14;'><i class='fa fa-plus pr-2 p-1'></i><a style='color:inherit;' href='$link' >Add Measure</a></div>\n";
?>			
			<div class='col-sm text-right'><?php doMeasuresCount(); ?></div>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doMeasuresHeadings();			
			foreach($result as $row) {	
				$elink= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=measures&id=$row[id]&edit=1";
				$dlink=  $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=measures&id=$row[id]&delete=1";			
				
				echo "
				<tr>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[name]</td>	
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[abbrev]</td>					
				<td class='border border-dark bg-gray-3 p-1'>";
				if ($control['measures_update'])
					echo "<a class='text-dark pl-2' href='$elink'><i class='fa fa-edit fa-lg' title='edit'></i></a>\n";
				
				// add security here
				if ( $control['measures_delete'])
					echo "<a class='text-dark pl-3' onclick='return OKToDeleteMeasure();' href='$dlink'><i class='fa fa-times fa-lg' title='delete'></i></a>\n";

				echo "	
				</td>
				</tr>";	
			}	 
?>		
		</table>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function doMeasuresCount() {
	global $control;
	
	$sql = "SELECT id FROM measures";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();
	echo "<b>$total</b> total measure(s)."; 
}	

function doMeasuresHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=measures";

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Measure</a></th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Abbreviation</a></th>	
	<th class='border border-dark bg-gray-4 p-1'>Action</th>
	</tr>
	</thead>";	
}

function measureForm($action, $errMsg) {
	global $control;
	
	$values= getMeasureValues($action);

?>

<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo ucname($action); ?> Measure</h5>
	  <div class="card-body bg-gray-4">	
<?php
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
?>
 
	  
	<form method='post' action='tables/addupdatemeasure.php'>  
	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* MEASURE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="name" id="name" value='<?php echo $values['name']; ?>'></div>
			</div>	
		</div>		
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ABBREVIATION:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="abbrev" id="abbrev" value='<?php echo $values['abbrev']; ?>'></div>
			</div>	
		</div>			
		
		<div class="form-row">
			<div class="col-lg text-center">* required field</div>	
		</div>		
		
		<div class='text-center mt-3'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='save'>Save</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
	
		</div>	
<?php			
		if ($action == "edit")
			echo "<input type= 'hidden' name= 'id' value= '$_GET[id]'>\n";
		echo "<input type= 'hidden' name= 'hhID' value= '$control[hhID]'>\n";	
?>		
		</form>		  

	  </div>
	</div>
</div>	
</div>

<?php	
}

function getMeasureValues($action) {
	global $control;
	
	$arr = [	

		'name'				=> "",
		'abbrev'			=> ""		
	]; 

	if (isset($_GET['errCode'])) {
		
		$arr['name']		= $_GET['name'];	
		$arr['abbrev']		= $_GET['abbrev'];			

	} elseif ($action == "edit") {

		$sql = "SELECT * FROM measures WHERE id = $_GET[id]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();	
	}
	
	return $arr;
}	


function deleteMeasure() {
	global $control;
	
	$sql = "DELETE FROM measures WHERE id = :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);				
	$stmt->execute();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "measures", $_GET['id'], "DELETE");		
}	

?>