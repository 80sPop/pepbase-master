<?php
/**
 * households/history.php
 * written: 9/25/2020
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

function doHistorySearchForm() {
	global $control;
	
	$control=addHistorySearchControl($control);	
	
	$hideStartLabel="display:block;";		
	$showStartLabel="display:none;";
	$showDate1="display:none;";
	$showDate2="display:none;";	
	$startLabel="";
	if ( $control['dateType'] == "range" ) {
		$showDate1="display:block;";
		$showDate2="display:block;";
		$showStartLabel="display:block;";
		$hideStartLabel="display:none;";			
	
	} elseif ( $control['dateType'] != "all" && $control['dateType'] != "last18months" )
		$showDate1="display:block;";	
	
	if ($control['hhID']==0)	
		$hhID="";
	else
		$hhID=$control['hhID'];
?>
	
	<form method='post' action='<?php echo $_SERVER['PHP_SELF'] . "?tab=history"; ?>'> 
	
	<div class="container-fluid bg-gray-2 pt-3"> 
		<div class='bg-gray-2 m-0 p-0 border border-dark'> 
		
			<div class="container-fluid bg-gray-4 m-0 p-1 border-bottom border-dark text-center"><h5>Search</h5></div>
			
			<div class="row p-2">
			
				<div class="col-md-auto">
					<div class='form-group'>
						<label>ID</label>
						<input class='form-control mr-sm-2' style='width:100px;' type='search' name='hhID' id='hhID' value='<?php echo $hhID; ?>'  placeholder='--- all ---' aria-label='Search'>		
					</div>
				</div>
				
				<div class="col-md-auto">
					<div class='form-group'>
						<label>Pantry</label>			
						<?php selectPantry( "pantry_id", $control['pantry_id'], 1 ); ?>
					</div>	
				</div>			
				
				<div class="col-md-auto">
					<div class='form-group'>
						<label>Date</label>
						<?php echo selectDateType( "dateType", "$control[dateType]", 1 ); ?>
					</div>				
				</div>
				
				<div class="col-md-auto">
					<div class='form-group' style='<?php echo $showDate1; ?>' id='hide-date-1'>			
						<div style='<?php echo $showStartLabel; ?>' id='show-start-label'><label>Start</label></div>	
						<div style='<?php echo $hideStartLabel; ?>' id='hide-start-label'><label>&nbsp;</label></div>
						<input type='date' name='date1' id='date1' value='<?php echo $control['date1']; ?>' class='form-control bg-gray-1'>
					</div>
				</div>	
				
				<div class="col-md-auto">
					<div class='form-group' style='<?php echo $showDate2; ?>' id='hide-date-2'>
						<label>End</label>
						<input type='date' name='date2' id='date2' value='<?php echo $control['date2']; ?>' class='form-control bg-gray-1'>
					</div>
				</div>
					
				<div class="col-md-auto">
					<div class='form-group' style='margin-top:32px;'>
						<button title="search" class='btn btn-outline-secondary my-2 my-sm-0 mr-sm-2' type='submit' name='hSearch'><i class='fa fa-search'></i></button> 
					</div>	
				</div>					

			</div>
		</div> 
	</div>
	</form>

<?php
}	

function addHistorySearchControl($arr) {

	if ( isset($_GET['dateType']) )
		$arr['dateType']=$_GET['dateType'];
	elseif ( isset($_POST['dateType']) )		
		$arr['dateType']=$_POST['dateType'];
	else	
//		$arr['dateType']="last18months";
		$arr['dateType']="all";

	if ( isset($_GET['pantry_id']) )
		$arr['pantry_id']=$_GET['pantry_id'];
	elseif ( isset($_POST['pantry_id']) )		
		$arr['pantry_id']=$_POST['pantry_id'];
	else	
		$arr['pantry_id']=0;

	if ( isset($_GET['date1']) ) 
		$arr['date1']=$_GET['date1'];
	elseif ( isset($_POST['date1']) ) 
		$arr['date1']=$_POST['date1'];
	else
		$arr['date1']="";
	
	if ( isset($_GET['date2']) )	
		$arr['date2']=$_GET['date2'];	
	elseif ( isset($_POST['date2']) )	
		$arr['date2']=$_POST['date2'];
	else
		$arr['date2']="";

	$today=date("Y-m-d");
	$arr['error'] = "";
	$arr['focus'] = "dateType";
	if ( $arr['dateType'] == "range" ) {
		$arr['start']=$arr['date1'];
		$arr['end']=$arr['date2'];
		if (! isValidDate($arr['date1'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date1";
		} elseif (! isValidDate($arr['date2'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date2";
		} elseif ( $arr['date2'] < $arr['date1'] ) {
			$arr['error'] = "rDate";
			$arr['focus'] = "date2";		
		}
		
	} elseif ( $arr['dateType'] == "last18months" ) {
		$arr['end']=date( "Y-m-d", strtotime( "$today - 14 days" ));		
		$arr['start']=date( "Y-m-d", strtotime( "$arr[end] - 18 months" ));		


	} else {
		$arr['start']=$arr['date1'];
		$arr['end']=$arr['date1'];
		if (! isValidDate($arr['date1'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date1";
		}
	}
	
// prevent array overload when household search bar is cleared or shopping history is called from menu with no household selected
	$today=date("Y-m-d");
	if ( isset($_POST['clear']) || (isset($_GET['menu']) && $arr['hhID'] < 1) ) {	
		$arr['dateType']="equalto";
		$arr['date1']=$today;
		$arr['start']=$today;
	}	
	
	return $arr;
}

function listHistory() {
	global $control;
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=history";
	$hhIDQ="household_id = $control[hhID]";
	$pantryQ=1;
	$dateQ=1;
	
	if ($control['hhID'] > 0)
		$hhIDQ="household_id = $control[hhID]";	
	else
		$hhIDQ=1;		
	
    if ( $control['pantry_id'] == 0 ) {
		$pantryQ="1";
	} else {
		$pantryQ="pantry_id = $control[pantry_id]";
	}	

	if ( $control['dateType'] == "last18months" || $control['dateType'] == "range" ) {
		$dateQ = "date >= '$control[start]' AND date <= '$control[end]'";

	} elseif ( $control['dateType'] == "equalto" )
		$dateQ = "date = '$control[start]'";

 	elseif ( $control['dateType'] == "after" ) {
		$dateQ = "date > '$control[start]'";
//		$control['end']	= consumptionDateLimit("end");	

 	} elseif ( $control['dateType'] == "onorafter" ) {
		$dateQ = "date >= '$control[start]'";
//		$control['end']	= consumptionDateLimit("end");	
	
 	} elseif ( $control['dateType'] == "before" ) {
		$dateQ = "date < '$control[start]'";
//		$control['start']	= consumptionDateLimit("start");	
//		$interval['start']	= $control['start'];	

 	} elseif ( $control['dateType'] == "onorbefore" ) {
		$dateQ = "date <= '$control[start]'";
//		$control['start']	= consumptionDateLimit("start");	
//		$interval['start']	= $control['start'];		
	}
	
	$sql = "SELECT household_id, date, time, abbrev, SUM(quantity_oked) s_quantity_oked, SUM(quantity_approved) s_quantity_approved, SUM(instock) s_instock, SUM(quantity_used) s_quantity_used 
			FROM consumption
			INNER JOIN pantries ON pantries.id = consumption.pantry_id
			WHERE $hhIDQ
			AND $pantryQ
			AND $dateQ
			AND product_id > 0
			AND (quantity_oked > 0 OR quantity_approved > 0)
			GROUP BY date, time
			ORDER BY date DESC, time DESC, pantry_id, shelf, bin"; 

	$stmt = $control['db']->query($sql);			
	$total = $stmt->rowCount();		
?>	
	<div class="container-fluid bg-gray-2"> 
	
		<div class='row p-3'>
			<div class='col-sm text-right'>search found <b><?php echo $total; ?></b> visit(s)</div>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doHistoryHeadings();	
			while ($row = $stmt->fetch()) {				
				$elink= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&household_id=$row[household_id]&tab=history&date=$row[date]&time=$row[time]&edit=1";
				$elink.= "&pantry_id=$control[pantry_id]&dateType=$control[dateType]&date1=$control[date1]&date2=$control[date2]";				
				$dlink=  $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&household_id=$row[household_id]&tab=history&date=$row[date]&time=$row[time]&delete=1";
				$dlink.= "&pantry_id=$control[pantry_id]&dateType=$control[dateType]&date1=$control[date1]&date2=$control[date2]";	
				$hhLink= $_SERVER['PHP_SELF'] . "?hhID=$row[household_id]";	
				
				echo "
				<tr>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>" . date('D m-d-Y', strtotime("$row[date]")) . "</td>	
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>" . date('g:i a', strtotime("$row[date] $row[time]")) . "</td>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'><a style='color:#841E14;text-decoration:underline;' href='$hhLink'>$row[household_id]</a></td>	
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[abbrev]</td>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[s_quantity_oked]</td>	
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[s_quantity_approved]</td>				
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[s_instock]</td>				
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[s_quantity_used]</td>	
				<td class='border border-dark bg-gray-3 p-1'>
				<a class='text-dark pl-2' href='$elink'><i class='fa fa-edit fa-lg' title='edit'></i></a>";
				
				// add security here
				if (1)
					echo "<a class='text-dark pl-3' onclick='return OKToDeleteVisit();' href='$dlink'><i class='fa fa-times fa-lg' title='delete'></i></a>";

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

function doHistoryHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=measures";

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Date</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Time</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>HH ID</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Pantry</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Eligible For</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Approved For</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>In Stock *</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Received</th>		
	<th class='border border-dark bg-gray-4 p-1'>Action</th>
	</tr>
	</thead>";	
}

function historyUpdateForm($action, $errMsg) {
	global $control;
		
	$isFirst=1;
	$household=getHouseholdRow($control['db'], $_GET['household_id']);
?>	
	<div class="container-fluid bg-gray-2"> 
	
		<div class='row p-3'>
			<div class='col-sm text-center'><h5>Edit Shopping History for <b><?php echo ucname($household['firstname']) . " " . ucname($household['lastname']); ?></b></h5></div>			
		</div>	

<?php
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
?>		
		<table class='table mb-2'>
		<form method='post' action='households/updatehistory.php'> 		
<?php

 		historyUpdateFormHeadings();	
//		$sql = "SELECT consumption.id id, date, time, abbrev, products_nameinfo.name product_name, quantity_oked, quantity_approved, quantity_used FROM consumption
//				INNER JOIN pantries ON pantries.id = consumption.pantry_id	
//				INNER JOIN products_nameinfo ON products_nameinfo.productID = consumption.product_id				
//				WHERE household_id = '$_GET[household_id]'
//				AND products_nameinfo.languageID=1
//				AND (instock IS NULL OR instock > 0)
//				AND product_id > 0 
//				AND date = '$_GET[date]'
//				AND ( time IS NULL OR time = '$_GET[time]')
//				ORDER BY shelf, bin"; 
//
// Allow out of stock items in case of override				
		$sql = "SELECT consumption.id id, date, time, abbrev, products_nameinfo.name product_name, instock, quantity_oked, quantity_approved, quantity_used FROM consumption
				INNER JOIN pantries ON pantries.id = consumption.pantry_id	
				INNER JOIN products_nameinfo ON products_nameinfo.productID = consumption.product_id				
				WHERE household_id = '$_GET[household_id]'
				AND products_nameinfo.languageID=1
				AND product_id > 0 
				AND date = '$_GET[date]'
				AND ( time IS NULL OR time = '$_GET[time]')
				ORDER BY shelf, bin"; 				
				
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$total = $stmt->rowCount();				
		$result = $stmt->fetchAll();
		foreach($result as $row) {	
			$date="";
			$time="";
			$pantry="";
			$quantity_approved_id="";			
			$quantity_used_id="";		
			if ($isFirst) {
				$date=$row['date'];
				$time=$row['time'];			
				$pantry=$row['abbrev'];
				$isFirst=0;
				$quantity_approved_id="id='quantity_approved_id'";
				$instock_id="id='instock_id'";				
				$quantity_used_id="id='quantity_used'";					
			}
			
			$y="quantity_approved" . $row['id'];
			$x="quantity_used" . $row['id'];
			$z="instock" . $row['id'];			
			
			if (isset($_GET['copy'])) {
				$quantity_approved=$_GET[$y];
				$quantity_used=$_GET[$x];
				$instock=$_GET[$z];				
			} elseif (isset($_GET['errCode'])) {
				$quantity_approved=$_GET[$y];
				$quantity_approved_id="id='$y'";		
				$instock=$_GET[$z];
				$instock_id="id='$z'";	
				$quantity_used=$_GET[$x];
				$quantity_used_id="id='$x'";					
			} else {
				$quantity_approved=$row['quantity_approved'];
				$instock=$row['instock'];					
				$quantity_used=$row['quantity_used'];
			}	
			echo "
			<input type='hidden' name='id[]' value='$row[id]'>
			<tr>
			<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>";
			if (!empty($date))
				echo "
				<input id='party' type='date' name='date' value='$date'>
				<input id='party' type='time' name='time' value='$time' step='1'>";
			else
				echo "";
			echo "
			<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$pantry</td>				
			<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[product_name]</td>	
			<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[quantity_oked]</td>				
 			<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1' style='width:150px;'>
				<input type='text' class='form-control' name='$y' $quantity_approved_id value='$quantity_approved'></td>
 			<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1' style='width:150px;'>
				<input type='text' class='form-control' name='$z' $instock_id value='$instock'></td>				
 			<td class='border border-bottom-1 border-dark bg-gray-3 p-1' style='width:150px;'>
				<input type='text' class='form-control' name='$x' $quantity_used_id value='$quantity_used'></td></tr>\n";					
		}		
?>			
		</table>
		<div class='text-center mt-3'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='save'>Save</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
	
		</div>	
<?php			
		$control=addHistorySearchControl($control);
		
		echo "
		<input type= 'hidden' name= 'hhID' value= '$control[hhID]'>
		<input type= 'hidden' name= 'household_id' value= '$_GET[household_id]'>
		<input type= 'hidden' name= 'pantry_id' value= '$control[pantry_id]'>
		<input type= 'hidden' name= 'dateType' value= '$control[dateType]'>
		<input type= 'hidden' name= 'date1' value= '$control[date1]'>	
		<input type= 'hidden' name= 'date2' value= '$control[date2]'>\n";
?>		
		</form>		  
	<div>&nbsp;</div>
	</div>


<?php	
}

function getConsumptionData($products) {
	global $control;
	
	$arr = [	
		'id'				=> 0,
		'date'				=> "",
		'time'				=> "",
		'abbrev'			=> $products['abbrev'],
		'product_name'		=> $products['product_name'],
		'quantity_oked'		=> 0,		
		'quantity_approved'	=> 0,
		'quantity_used'		=> 0		
	]; 	

	$sql = "SELECT consumption.id id, date, time, abbrev, products_nameinfo.name product_name, quantity_oked, quantity_approved, quantity_used FROM consumption
			INNER JOIN pantries ON pantries.id = consumption.pantry_id	
			INNER JOIN products_nameinfo ON products_nameinfo.productID = consumption.product_id				
			WHERE household_id = '$_GET[household_id]'
			AND products_nameinfo.languageID=1
			AND (instock IS NULL OR instock > 0)
			AND product_id = $products[id] 
			AND date = '$_GET[date]'
			AND ( time IS NULL OR time = '$_GET[time]')";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();	
	if ($total > 0)  
		$row = $stmt->fetch();	
}	

function historyUpdateFormHeadings() {
	global $control;
	
	$cLink= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&household_id=$_GET[household_id]&tab=history&date=$_GET[date]&time=$_GET[time]&edit=1&copy=1";
	
	echo "
	<thead>
	<tr>
	<th colspan='6' class='border border-dark border-right-0 border-bottom-0 bg-gray-4 p-1'>&nbsp;</th>	
	<th class='border border-dark bg-gray-4 p-1 text-center'>";
		echo "<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='copy'>Copy In Stock</button>";		

	echo "		
	</th></tr>	
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Date/Time</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Pantry</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Product</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Eligible For</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Approved For</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>In Stock</th>	
	<th class='border border-dark bg-gray-4 p-1'>Received</th>
	</tr>
	</thead>";		
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
}	
?>