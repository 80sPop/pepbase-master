<?php
/**
 * tables/languages.php
 * written: 9/4/2020
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

function listLanguages() {
	global $control;
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=languages&add=1";

	$sql = "SELECT * from languages ORDER BY name";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 
		<div class='row p-3'>
<?php		
	if ($control['languages_update'])
		echo "<div class='col-sm' style='color:#841E14;'><i class='fa fa-plus pr-2 p-1'></i><a style='color:inherit;' href='$link' >Add Language</a></div>\n";
?>	
			<div class='col-sm text-right'><?php doLanguagesCount(); ?></div>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doLanguagesHeadings();			
			foreach($result as $row) {	
				$elink= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=languages&id=$row[id]&edit=1";
				$dlink=  $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=languages&id=$row[id]&delete=1";				
				
				echo "
				<tr>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[name]</td>		
				<td class='border border-dark bg-gray-3 p-1'>\n";
				if ($control['languages_update'])
					echo "<a class='text-dark pl-2' href='$elink'><i class='fa fa-edit fa-lg' title='edit'></i></a>";
				
				// add security here
				if ($control['languages_delete'])
					echo "<a class='text-dark pl-3' onclick='return OKToDeleteLanguage();' href='$dlink'><i class='fa fa-times fa-lg' title='delete'></i></a>";

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

function doLanguagesCount() {
	global $control;
	
	$sql = "SELECT id FROM languages";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();
	echo "<b>$total</b> total language(s)."; 
}	

function doLanguagesHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=languages";

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Language</a></th>
	<th class='border border-dark bg-gray-4 p-1'>Action</th>
	</tr>
	</thead>";	
}

function LanguageForm($action, $errMsg) {
	global $control;
	
	$values= getLanguageValues($action);

?>

<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo ucname($action); ?> Language</h5>
	  <div class="card-body bg-gray-4">	
<?php
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
?>
 
	  
	<form method='post' action='tables/addupdatelanguage.php'>  
	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* LANGUAGE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="name" id="name" value='<?php echo $values['name']; ?>'></div>
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

function getLanguageValues($action) {
	global $control;
	
	$arr = [	

		'name'				=> ""
	]; 

	if (isset($_GET['errCode'])) {
		
		$arr['name']		= $_GET['name'];		

	} elseif ($action == "edit") {

		$sql = "SELECT * FROM languages WHERE id = $_GET[id]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();	
	}
	
	return $arr;
}	


function deleteLanguage() {
	global $control;
	
	$sql = "DELETE FROM languages WHERE id = :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);				
	$stmt->execute();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "languages", $_GET['id'], "DELETE");		
}	

?>