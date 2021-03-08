<?php
/**
 * tables/shelters.php
 * written: 9/5/2020
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

function listShelters() {
	global $control;
	
	$link="tables.php?tab=shelters&hhID=$control[hhID]";

?>	
	<div class="container-fluid bg-gray-2 m-0 p-0"> 	

		<div class="row border-dark border-bottom m-0 p-2">
<?php		
	if ($control['shelters_update'])
		echo "			
		<div class='col-sm form-inline' style='color:#841E14;'><i class='fa fa-plus pr-2'></i><a style='color:inherit;' href='$link&add=1'>Add Shelter</a></div>";
?>		
			<div class='col-sm pt-2 text-right'><?php doSheltersCount(); ?></div>
		</div>		
<?php	
	$sql = "SELECT * FROM shelters ORDER BY name";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach ($result as $shelters) {	

		$data=getShelterData($shelters);	
//		$elink= $link."&id=" . $shelters['id'];	
		$elink= $link . "&id=$shelters[id]&edit=1";
		$dlink= $link . "&id=$shelters[id]&delete=1";		
?>	
<!--		<div class='row border-dark border-bottom m-0 p-2'> -->
		<div class='row m-0 p-2 border-dark border-bottom'> 
			<div class='col-sm-5'><?php echo $data['col1']; ?></div>
		
			<div class='col-sm-3'><?php echo $data['col2']; ?></div>
			
			<div class='col-sm-3'><?php echo $data['col3']; ?></div>			

			<div class='col-sm text-right pr-4'>
<?php			
			if ($control['shelters_update'])			
				echo "<a class='text-dark' href='$elink'><i class='fa fa-edit fa-lg' title='edit'></i></a>\n";
?>			
<?php				
			// add security here
			if ($control['shelters_delete'])
				echo "<a class='text-dark pl-3' onclick='return OKToDeleteShelter();' href='$dlink'><i class='fa fa-times fa-lg' title='delete'></i></a>\n";		
?>				
			</div>	

		</div>	
		
<!--		<div class='border-dark border-bottom m-0'>&nbsp;</div>	-->
<?php		
	}
	echo "
	</div>\n";	
}

function doSheltersCount() {
	global $control;
	
	$sql = "SELECT id FROM shelters";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();
	echo "<b>$total</b> total shelter(s)."; 
}	

function getShelterData($shelters) {
	global $control;
	
	$hasAccountAccess = 1;	// *** CODE SECURITY LATER ****
	
	$arr=array();
	
	$mapLink = "http://maps.google.com/maps?q=";	
	if ($shelters['streetnum']) 
		$mapLink .= $shelters['streetnum'];
	if ($shelters['streetname']) 
		$mapLink .= " " . $shelters['streetname'];	
	if ($shelters['city']) 
		$mapLink .= "+" . $shelters['city'];	
	if ($shelters['state']) 
		$mapLink .= "+" . $shelters['state'];	
	if ($shelters['zip_five']) 
		$mapLink .= "+" . $shelters['zip_five'];	
	$accountslink = "households.php?hhID=$control[hhID]";	
	
// column 1	
	$arr['col1'] = "<b>$shelters[name]</b>"; 	
	$arr['col1'] .= "<br>";	
	$arr['col1'] .= $shelters['streetnum'] . " " . $shelters['streetname'] . "<br>";
	$arr['col1'] .= $shelters['city'] . ", " . $shelters['state'] . " " . $shelters['zip_five'];
	$arr['col1'] .= "<br>";
	$arr['col1'] .= "<a style='color:#841E14;' target='_blank' href='$mapLink'>map</a>";
	if (!empty($shelters['web_site'])) 
		$arr['col1'] .= "&#160;&#160;<a style='color:#841E14;' target='_blank' href='http://$shelters[web_site]'>website</a>";

//	if ($hasAccountAccess) {
//		$sql = "SELECT id FROM household WHERE shelter = $shelters[id]";
//		$stmt = $control['db']->prepare($sql);
//		$stmt->execute();	
//		$total = $stmt->rowCount();
//		if ($total > 0)	{
//			$arr['col1'] .= "<br><i>$total household(s)</i>";
//			$arr['col1'] .= "&#160; [ <a  style='color:#841E14;' href='$accountslink" . "&shelter=$shelters[id]" . "'>list</a> ]";
//		}
//	}

// column 2
	$arr['col2'] = "<u>Contact</u><br>";
	if ($shelters['contact_first'] || $shelters['contact_last'])
		$arr['col2'] .= $shelters['contact_first'] . " " . $shelters['contact_last'] . "<br>";
	if ($shelters['phone'])
		$arr['col2'] .= $shelters['phone'] . "<br>";
	if ($shelters['cell_phone'])
		$arr['col2'] .= $shelters['cell_phone'] . " (C)<br>";	
	if ($shelters['email'])
		$arr['col2'] .= "<a style='color:#841E14;' href='mailto:$pantries[email]'>$pantries[email]</a>";

// column 3	
	$arr['col3'] = "<u>Stay Limit (Days)</u>";
	$arr['col3'] .= "<br>$shelters[staytime]";	

	return $arr;
}


function sheltersForm($action, $errMsg) {
	global $control;
	

	
	$values=fillShelterForm($action);
	$address = "$values[streetnum] $values[streetname]";
?>
<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo ucname($action); ?> Shelter</h5>
	  <div class="card-body bg-gray-4">	
<?php
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
?>
	  
	<form method='post' action='tables/addupdateshelter.php'>  
	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* SHELTER NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="name" id="name" value='<?php echo htmlentities($values['name'], ENT_QUOTES); ?>' ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* ADDRESS:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="address" id="address"  value='<?php echo htmlentities($address, ENT_QUOTES); ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* CITY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="city" id="city"  value='<?php echo htmlentities($values['city'], ENT_QUOTES); ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* STATE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php SelectState('state', $values['state']); ?></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* ZIP CODE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="zip_five" id="zip_five"  value='<?php echo $values['zip_five']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>EMAIL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="email" id="email"  value='<?php echo $values['email']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>WEBSITE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="web_site" id="web_site"  value='<?php echo $values['web_site']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>CONTACT FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="contact_first" id="contact_first"  value='<?php echo htmlentities($values['contact_first'], ENT_QUOTES); ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>CONTACT LAST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="contact_last" id="contact_last"  value='<?php echo htmlentities($values['contact_last'], ENT_QUOTES); ?>'></div>
			</div>	
		</div>				

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PHONE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="phone" id="phone" value='<?php echo $values['phone']; ?>'></div> 
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>STAY LIMIT (DAYS):</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="staytime"  id="staytime" value='<?php echo $values['staytime']; ?>'></div>
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

function fillShelterForm($action) {
	global $control;
	
	$arr = [		
		'name' => "",
		'streetnum'=> "",
		'streetname'=> "",
		'city'=> "",
		'county'=> "",
		'state' => "WI",
		'zip_five'=> "",
		'zip_four'=> "",
		'email'=> "",
		'web_site'=> "",
		'contact_first'=> "",
		'contact_last' => "",
		'phone'=> "",
		'staytime' => "",
	];
	
	if (isset($_GET['errCode'])) {
		$arr['name'] =$_GET['name'];
		$street = splitAddress( $_GET['address'] );	
		$arr['streetnum'] = $street['num'];
		$arr['streetname'] = $street['name'];		
		$arr['city'] =$_GET['city'];
		$arr['state'] = $_GET['state'];
		$arr['zip_five'] =$_GET['zip_five'];
		$arr['email'] =$_GET['email'];
		$arr['web_site'] =$_GET['web_site'];
		$arr['contact_first'] =$_GET['contact_first'];
		$arr['contact_last'] =$_GET['contact_last'];
		$arr['phone'] = $_GET['phone'];
		$arr['staytime'] =$_GET['staytime'];
	
	} elseif ($action == "edit") {
		$sql = "SELECT * FROM shelters WHERE id = $_GET[id]";	
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();
	}

	return $arr;
}	


function deleteShelter() {
	global $control;
	
	$sql = "DELETE FROM shelters WHERE id = :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);				
	$stmt->execute();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "shelters", $_GET['id'], "DELETE");	
}	

?>