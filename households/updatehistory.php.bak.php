<?php
/**
 * history/updatehistory.php
 * written: 9/26/2020
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
	require_once('../functions.php');	

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	
	$control=fillControlArray($control, $config, "tables");
	$header = "Location: ../households.php?tab=history&hhID=$control[hhID]";
	$header .= "&pantry_id=$_POST[pantry_id]";
	$header .= "&dateType=$_POST[dateType]";
	$header .= "&date1=$_POST[date1]";	
	$header .= "&date2=$_POST[date2]";	

	if (!isset($_POST['cancel'])) {
		
		$header .= "&household_id=$_POST[household_id]";		
		$sql = "SELECT consumption.id id, quantity_approved FROM consumption
				WHERE household_id = $_POST[household_id]
				AND (instock IS NULL OR instock > 0)
				AND product_id > 0 
				AND date = '$_POST[date]'
				AND ( time IS NULL OR time = '$_POST[time]')
				ORDER BY shelf, bin";  		

		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$result = $stmt->fetchAll();
		
// 1st loop checks for errors		
		foreach($result as $consumption) {	
			if (!$control['err']) {
//				$quantity_used = editHistory($consumption['id']);
				$values = editHistory($consumption['id']);				
				if ($control['err'])
					$header=redirect($header,$control['err'],$consumption['id']);	
			}	
		}
		
// 2nd loop updates table		
		if (!$control['err']) 
			foreach($result as $consumption) {	
//				$quantity_used = editHistory($consumption['id']);
				$values = editHistory($consumption['id']);				
//				updateConsumption($consumption['id'], $quantity_used); 
				updateConsumption($consumption['id'], $values);
			}
			
// write user log			
		if (!$control['err']) { 
			$date = date('Y-m-d');
			$time = date('H:i:s');			
			writeUserLog( $control['db'], $date, $time, $control['hhID'], "consumption", 0, "UPDATE SHOPPING", $_POST['date'], $_POST['time']);
		}	
	}	
	
	header($header);	

function editHistory($id) {
	global $control;
	
	$arr = [	
		'quantity_approved'	=> 0,
		'quantity_used'		=> 0		
	]; 	
	
	$y = "quantity_approved" . $id;	
	$x = "quantity_used" . $id;
	
	$arr['quantity_approved']=	$_POST[$y];
	$arr['quantity_used']=	$_POST[$x];	
	
	if (empty($arr['quantity_approved']))
		$arr['quantity_approved']=0;	
	if (empty($arr['quantity_used']))
		$arr['quantity_used']=0;
	if ( is_numeric($arr['quantity_approved']) && is_numeric($arr['quantity_used']) ) {
		if ( $arr['quantity_used'] > $arr['quantity_approved'])
			$control['err'] = 78;	
	} else
		$control['err'] = 75;		
	
	return $arr;
}

//function updateConsumption($id, $quantity_used) { 
function updateConsumption($id, $values) { 
	global $control;

	$sql = "UPDATE consumption 
			SET quantity_approved= $values[quantity_approved],
			quantity_used= $values[quantity_used]
			WHERE id =$id";		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute();	
}	

function redirect($header, $err, $id) {
	global $control;
	
	$header .= "&edit=1";		
	$header .= "&errCode=" . $err;
	$header .= "&id=$id";
	$header .= "&date=$_POST[date]";	
	$header .= "&time=$_POST[time]";
	
	$sql = "SELECT consumption.id id FROM consumption
			WHERE household_id = $_POST[household_id]
			AND (instock IS NULL OR instock > 0)
			AND product_id > 0 
			AND date = '$_POST[date]'
			AND ( time IS NULL OR time = '$_POST[time]')
			ORDER BY shelf, bin";  		

	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();
	foreach($result as $consumption) {
		$y="quantity_approved" . $consumption['id'];		
		$x="quantity_used" . $consumption['id'];
		$header .= "&$y=$_POST[$y]";		
		$header .= "&$x=$_POST[$x]";
	}	
	
	return $header;	
} 
?> 