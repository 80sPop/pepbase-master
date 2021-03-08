<?php
/**
 * pantries.php
 * written: 8/9/2020
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

	require_once('config.php'); 
	require_once('header.php'); 
	require_once('navbar.php'); 		
	require_once('functions.php');	
	require_once('common_vars.php');
	require_once('products/definitions.php');	
	require_once('products/instock.php');		
	require_once('products/pantrysetup.php');		
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "pantries");
	$control=loadAccessLevels();	
	
//	doSaveUpdate();
	$control=setFocus($control);	
	doHeader("Pantries");
	doNavbar();	
//	doProductsNavBar();	
	
	if (isset($_GET['add']))
		pantryForm("add", $errMsg);	
	elseif (isset($_GET['edit']))
		pantryForm("edit", $errMsg);		
	else
		listPantries();

	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	
function setFocus($arr) {
	global $control;
	
	$arr["focus"] = "nofocus";
	if (isset($_GET['errCode'])) {
		$arr['errCode'] = $_GET['errCode'];	
		if ($arr['errCode'] == 63)
			$arr["focus"] = "name";		
		elseif ($arr['errCode'] == 64)
			$arr["focus"] = "abbrev";	
		elseif ($arr['errCode'] == 40)
			$arr["focus"] = "start_date";				
		elseif ($arr['errCode'] == 65)
			$arr["focus"] = "address_1";
		elseif ($arr['errCode'] == 49)
			$arr["focus"] = "city";	
		elseif ($arr['errCode'] == 50)
			$arr["focus"] = "county";	
		elseif ($arr['errCode'] == 51)
			$arr["focus"] = "zip_5";	
		elseif ($arr['errCode'] == 24)
			$arr["focus"] = "email";		
		elseif ($arr['errCode'] == 66)
			$arr["focus"] = "contact_first";	
		elseif ($arr['errCode'] == 67)
			$arr["focus"] = "contact_last";		
		elseif ($arr['errCode'] == 54)
			$arr["focus"] = "phone";	
		elseif ($arr['errCode'] == 55)
			$arr["focus"] = "cell_phone";				

			
	} elseif ( isset($_GET['add']) || isset($_GET['edit']) )
		$arr["focus"] = "name";		
			
	return $arr;		
}

function listPantries() {
	global $control;
	
	$link="pantries.php?tab=members&hhID=" . $control['hhID'];

?>	
	<div class="container-fluid bg-gray-2 m-0 p-0"> 	
		<div class="row border-dark border-bottom m-0 p-2">
			<div class='col-sm form-inline' style="color:#841E14;">
<?php			
	if ($control['pantries_update'])			
		echo "<i class='fa fa-plus pr-2'></i><a style='color:inherit;' href='$link" . "&add=1'>Add Pantry</a>\n";
?>	
			</div>
			<div class='col-sm pt-2 text-right'><?php displayPantryCount(); ?></div>
		</div>		
<?php	
	$sql = "SELECT * FROM pantries ORDER BY name";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach ($result as $pantries) {	

		$data=getPantryData($pantries);	
		$elink= $link."&id=" . $pantries['id'];		
?>	
		<div class='row m-0 p-2 border-dark border-bottom'> 
			<div class='col-sm-5'><?php echo $data['col1']; ?></div>
		
			<div class='col-sm-3'><?php echo $data['col2']; ?></div>
			
			<div class='col-sm-3'><?php echo $data['col3']; ?></div>			
<?php
		if ($control['pantries_update'])
			echo "
			<div class='col-sm text-right pr-4'>
				<a class='text-dark' href='$elink" . "&edit=1'><i class='fa fa-edit fa-lg' title='edit'></i></a>
			</div>\n";
?>
		</div>	
<?php		
	}
	echo "
	</div>\n";	
}

function displayPantryCount() {
	global $control;
	
	$sql = "SELECT is_active FROM pantries";
		
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$tPantries = $stmt->rowCount();
	$tActive = 0;	
	$result = $stmt->fetchAll();	
	foreach($result as $row) 
		if ($row['is_active']) $tActive++;
			
	echo "<b>$tActive</b> of $tPantries pantries are active."; 
}	

function getPantryData($pantries) {
	global $control;
	
	$hasAccountAccess = 1;	// *** CODE SECURITY LATER ****
	
	$arr=array();
	
	$mapLink = "http://maps.google.com/maps?q=";	
	if ($pantries['address_1']) 
		$mapLink .= $pantries['address_1'];
	if ($pantries['city']) 
		$mapLink .= "+" . $pantries['city'];	
	if ($pantries['state']) 
		$mapLink .= "+" . $pantries['state'];	
	if ($pantries['zip_5']) 
		$mapLink .= "+" . $pantries['zip_5'];	
	$accountslink = "tools.php?hhID=$control[hhID]";	
	
// column 1	
	$arr['col1'] = "<b>$pantries[name] ($pantries[abbrev])</b>"; 	
	if (! $pantries['is_active'])
		$arr['col1'] .=	"<span class='alert alert-warning ml-2 p-1 border border-dark' role='alert'>INACTIVE</span>";				
	$arr['col1'] .= "<br>";	
	if ($pantries['address_1']) 
		$arr['col1'] .= $pantries['address_1'] . "<br>";
	if ($pantries['address_2'])
		$arr['col1'] .= $pantries['address_2'] . "<br>";
	$arr['col1'] .= $pantries['city'] . ", " . $pantries['state'] . " " . $pantries['zip_5'];
	if ($pantries['zip_4'])
		$arr['col1'] .= $pantries['zip_4']; 
	$arr['col1'] .= "<br>";
	$arr['col1'] .= "<a style='color:#841E14;' target='_blank' href='$mapLink'>map</a>";
	if (!empty($pantries['web_site'])) 
		$arr['col1'] .= "&#160;&#160;<a style='color:#841E14;' target='_blank' href='http://$pantries[web_site]'>website</a>";

//	if ($hasAccountAccess) {
//		$sql = "SELECT pantry_id FROM users WHERE pantry_id = $pantries[id]";
//		$stmt = $control['db']->prepare($sql);
//		$stmt->execute();	
//		$total = $stmt->rowCount();
//		if ($total > 0)	{
//			$arr['col1'] .= "<br><i>$total signin account(s)</i>";
//			$arr['col1'] .= "&#160; [ <a  style='color:#841E14;' href='$accountslink'>list</a> ]";
//		}
//	}

// column 2
	$arr['col2'] = "<u>Contact</u><br>";
	if ($pantries['contact_first'] || $pantries['contact_last'])
		$arr['col2'] .= $pantries['contact_first'] . " " . $pantries['contact_last'] . "<br>";
	if ($pantries['phone'])
		$arr['col2'] .= $pantries['phone'] . "<br>";
	if ($pantries['cell_phone'])
		$arr['col2'] .= $pantries['cell_phone'] . " (C)<br>";	
	if ($pantries['email'])
		$arr['col2'] .= "<a style='color:#841E14;' href='mailto:$pantries[email]'>$pantries[email]</a>";

// column 3	
	$arr['col3'] = "<u>Hours</u>";
	if ($pantries['hours_1'] || $pantries['hours_2'] || $pantries['hours_3'] || $pantries['hours_4']) {
		if ($pantries['hours_1'])
			$arr['col3'] .= "<br>$pantries[hours_1]";	
		if ($pantries['hours_2'])						
			$arr['col3'] .= "<br>$pantries[hours_2]";					
		if ($pantries['hours_3'])
			$arr['col3'] .= "<br>$pantries[hours_3]";	
		if ($pantries['hours_4'])						
			$arr['col3'] .= "<br>$pantries[hours_4]";
	}
	return $arr;
}

function pantryForm($action, $errMsg) {
	global $control;
	
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
	
	$values=fillPantryForm($action);
?>

<div class="container p-3">
	<div class="card">
	  <h5 class="card-header bg-gray-4 text-center"><?php echo ucname($action); ?> Pantry</h5>
	  <div class="card-body bg-gray-2">
	  
	<form method='post' action='pantries/addupdatepantry.php'>  	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* PANTRY NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="name" id="name" value='<?php echo htmlentities($values['name'], ENT_QUOTES); ?>' ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* ABBREVIATION:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="abbrev" id="abbrev"  value='<?php echo $values['abbrev']; ?>'></div>
			</div>	
		</div>		
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* START DATE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="start_date" id="start_date"  value='<?php echo $values['start_date']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>INACTIVE DATE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="inactive_date" id="inactive_date"  value='<?php echo $values['inactive_date']; ?>'></div>
			</div>	
		</div>			
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* ADDRESS 1:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="address_1" id="address_1"  value='<?php echo htmlentities($values['address_1'], ENT_QUOTES); ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ADDRESS 2:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="address_2" id="address_2"  value='<?php echo htmlentities($values['address_2'], ENT_QUOTES); ?>'></div>
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
				<div class="form-group text-right mb-1"><label class='pt-2'>* COUNTY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="county" id="county" value='<?php echo htmlentities($values['county'], ENT_QUOTES); ?>'></div>
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
				<div class="form-group mb-1"><input type="text" class="form-control" name="zip_5" id="zip_5"  value='<?php echo $values['zip_5']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* EMAIL:</label></div>
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
				<div class="form-group text-right mb-1"><label class='pt-2'>* CONTACT FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="contact_first" id="contact_first"  value='<?php echo htmlentities($values['contact_first'], ENT_QUOTES); ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* CONTACT LAST NAME:</label></div>
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
				<div class="form-group text-right mb-1"><label class='pt-2'>CELL PHONE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="cell_phone" id="cell_phone" value='<?php echo $values['cell_phone']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (1):</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="hours_1" value='<?php echo $values['hours_1']; ?>'></div>
			</div>	
		</div>		

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (2):</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="hours_2" value='<?php echo $values['hours_2']; ?>'></div>
			</div>	
		</div>		

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (3):</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="hours_3" value='<?php echo $values['hours_3']; ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>HOURS (4):</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="hours_4" value='<?php echo $values['hours_4']; ?>'></div>
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

<?php	


	
}	

function fillPantryForm($action) {
	global $control;
	
	$arr = [		
		'name' => "",
		'abbrev' => "",
		'start_date' => "",
		'is_active' => 1,
		'inactive_date' => "",
		'is_food_pantry'=> 0,
		'theme_id' => "",
		'address_1'=> "",
		'address_2'=> "",
		'city'=> "",
		'county'=> "",
		'state' => "WI",
		'zip_5'=> "",
		'zip_4'=> "",
		'email'=> "",
		'web_site'=> "",
		'contact_first'=> "",
		'contact_last' => "",
		'phone'=> "",
		'cell_phone' => "",
		'hours_1' => "",
		'hours_2' => "",
		'hours_3'=> "",
		'hours_4'=> ""
	];
	
	if (isset($_GET['errCode'])) {
		$arr['name'] =$_GET['name'];
		$arr['abbrev'] =$_GET['abbrev'];
		$arr['start_date'] = $_GET['start_date'];
		$arr['inactive_date'] = $_GET['inactive_date'];
		$arr['address_1'] =$_GET['address_1'];
		$arr['address_2'] =$_GET['address_2'];
		$arr['city'] =$_GET['city'];
		$arr['county'] =$_GET['county'];
		$arr['state'] = $_GET['state'];
		$arr['zip_5'] =$_GET['zip_5'];
		$arr['email'] =$_GET['email'];
		$arr['web_site'] =$_GET['web_site'];
		$arr['contact_first'] =$_GET['contact_first'];
		$arr['contact_last'] =$_GET['contact_last'];
		$arr['phone'] = $_GET['phone'];
		$arr['cell_phone'] = $_GET['cell_phone'];
		$arr['hours_1'] =$_GET['hours_1'];
		$arr['hours_2'] =$_GET['hours_2'];
		$arr['hours_3'] =$_GET['hours_3'];
		$arr['hours_4'] =$_GET['hours_4'];			
	
	} elseif ($action == "edit") {
		$sql = "SELECT * FROM pantries WHERE id = $_GET[id]";	
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();
		if ($arr['inactive_date'] == "0000-00-00")
			$arr['inactive_date'] = "";
	}

	return $arr;
}


//function doSaveUpdate() {
//	global $control;
//	
//	if (isset($_POST['saveSetup']))			// products/pantryinfo.php	
//		updatePantryInfo();
//	if (isset($_POST['saveInstock']))		// products/instock.php	
//		updateInstockStatus();
//}	

?>

<script>	

	$("#phone").inputmask({"mask": "(999) 999-9999"});	
	$("#cell_phone").inputmask({"mask": "(999) 999-9999"});		
	
</script>	