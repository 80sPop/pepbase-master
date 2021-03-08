<?php
/**
 * households/updatehistory.php
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
		
// 1st loop checks for errors		
		foreach( $_POST['id'] as $id ) {		
			if (!$control['err']) {
				$values = editHistory($id);
				if ($control['err'])
					$header=redirect($header,$control['err'],$id);	
			}	
		}
		
// 2nd loop updates table		
		if (!$control['err']) 
			foreach( $_POST['id'] as $id ) {			
				$values = editHistory($id);
				if (isset($_POST['copy']))
					$header=copyInStock($id, $header);					
				else	
					updateConsumption($id, $values); 
			}
			
// write user log			
		if (!$control['err'] && !isset($_POST['copy'])) { 
			$date = date('Y-m-d');
			$time = date('H:i:s');			
			writeUserLog( $control['db'], $date, $time, $control['hhID'], "consumption", 0, "UPDATE SHOPPING", $_POST['date'], $_POST['time']);
		}	
	}	
	
	header($header);	

function editHistory($id) {
	global $control;
	
	$val =0;
	$arr = [	
		'date' 				=> "",
		'time' 				=> "",
		'quantity_approved'	=> 0,
		'instock'			=> 0,		
		'quantity_used'		=> 0,
	];	
	
	// browser date and time input types won't allow bad data, so no need to verify here
	$arr['date']=$_POST['date'];
	$arr['time']=$_POST['time'];	
	
// quantity_approved
	$y = "quantity_approved" . $id;	
	if (empty($_POST[$y]))
		$_POST[$y]=0;
	if (!is_numeric($_POST[$y])) {
		$control['err'] = 75;
		$control['errid'] = $y;		
	} else
		$arr['quantity_approved']=$_POST[$y];
	
// instock	
	if (!$control['err'] && !isset($_POST['copy'])) { 	
		$z = "instock" . $id;
		if (empty($_POST[$z]))
			$_POST[$z]=0;
		if (is_numeric($_POST[$z]) || $_POST[$z] == 0)
			if ( $_POST[$z] <= $_POST[$y] )
				$arr['instock'] = $_POST[$z];
			else {
				$control['err'] = 82;
				$control['errid'] = $z;					
			}	
		else {
			$control['err'] = 75;
			$control['errid'] = $z;				
		}	
	}		
	
// quantity_used	
	if (!$control['err'] && !isset($_POST['copy'])) { 	
		$x = "quantity_used" . $id;
		if (empty($_POST[$x]))
			$_POST[$x]=0;
		if (is_numeric($_POST[$x]) || $_POST[$x] == 0)
			if ( $_POST[$x] <= $_POST[$z] )
				$arr['quantity_used'] = $_POST[$x];
			else {
				$control['err'] = 78;
				$control['errid'] = $x;	
			}	
		else {
			$control['err'] = 75;
			$control['errid'] = $x;			
		}	
	}	
	
	return $arr;
}

function updateConsumption($id, $values) { 
	global $control;

	$sql = "UPDATE consumption 
			SET date='$values[date]',
			time='$values[time]',
			instock= $values[instock], 			
			quantity_used= $values[quantity_used], 
			quantity_approved= $values[quantity_approved]
			WHERE id =$id";		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute();	
}

function copyInStock($id, $header) {
	global $control;
	
	$header .= "&edit=1";		
	$header .= "&copy=1";	
	$header .= "&id=$id";
	$header .= "&date=$_POST[date]";	
	$header .= "&time=$_POST[time]";
	
	$y="quantity_approved" . $id;
	$header .= "&$y=$_POST[$y]";	

	$z="instock" . $id;
	$header .= "&$z=$_POST[$z]";		
	
	$x="quantity_used" . $id;
	$header .= "&$x=$_POST[$z]";
	
	return $header;	
} 

function redirect($header,$err,$id) {
	global $control;
	
	$header .= "&edit=1";		
	$header .= "&errCode=" . $err;
	$header .= "&id=$id";
	$header .= "&date=$_POST[date]";	
	$header .= "&time=$_POST[time]";
	$header .= "&errid=$control[errid]";	
	
	foreach( $_POST['id'] as $id ) {			
		$y="quantity_approved" . $id;
		$header .= "&$y=$_POST[$y]";	

		$z="instock" . $id;
		$header .= "&$z=$_POST[$z]";		
		
		$x="quantity_used" . $id;
		$header .= "&$x=$_POST[$x]";
	}	
	
	return $header;	
} 
?> 