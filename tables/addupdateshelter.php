<?php
/**
 * tables/addupdateshelter.php
 * written: 9/6/2020
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
//	require_once('search.php');		

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	
	$control=fillControlArray($control, $config, "tables");
	
/* MAINLINE */

	$header = "Location: ../tables.php?tab=shelters&hhID=" . $control['hhID'];

	if (!isset($_POST['cancel'])) {
		$shelters = editShelter();
		if (!$control['err']) {
			if (isset($_POST['id']))
				updateShelter($_POST['id'], $shelters); 				
			else 
				insertShelter($shelters); 
		} else	
			$header=redirect($header,$control['err']);				
	} 
	
	header($header);	

function editShelter() {
	global $control;
	
	$arr = [	
		'name' => "",
		'streetnum'=> "",
		'streetname'=> "",
		'std_streetname'=>"",		
		'city'=> "",
		'state' => "WI",
		'zip_five'=> "",
		'email'=> "",
		'web_site'=> "",
		'contact_first'=> "",
		'contact_last' => "",
		'phone'=> "",
		'staytime' => ""	
	]; 	
	
	if (empty($_POST['name']))
		$control['err'] = 74;		
	elseif (empty($_POST['address']))
		$control['err'] = 65;	
	elseif (empty($_POST['city']))
		$control['err'] = 49;	
	elseif (empty($_POST['zip_five']))
		$control['err'] = 51;			
	if (!$control['err'])
		if ( !empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) 
			$control['err'] = 24;	
	if (!$control['err'])
		if (!isValidPhone($_POST['phone']))		
			$control['err'] = 54;
	if (!$control['err'])
		if(!is_numeric($_POST['staytime']))	
			$control['err'] = 75;		

	if (!$control['err']) {	
		$arr['name']=$_POST['name'];
		$street = splitAddress( $_POST['address'] );	
		$arr['streetnum'] = $street['num'];
		$arr['streetname'] = $street['name'];
		$arr['std_streetname'] = standardStreetName($street['name']);	
		$arr['city']=$_POST['city'];
		$arr['state']=$_POST['state'];
		$arr['zip_five']=$_POST['zip_five'];
		$arr['email']=$_POST['email'];
		$arr['web_site']=$_POST['web_site'];
		$arr['contact_first']=$_POST['contact_first'];
		$arr['contact_last']=$_POST['contact_last'];
		$arr['phone']=$_POST['phone'];
		$arr['staytime']=$_POST['staytime'];
	}	
	
	return $arr;
}

function insertShelter($shelters) { 
	global $control;
	
	$sql = "INSERT INTO shelters
				(name,
				streetnum,
				streetname,
				std_streetname,
				city,
				state,
				zip_five,
				email,
				web_site,
				contact_first,
				contact_last,
				phone,
				staytime)
			VALUES 
				(:name,
				:streetnum,
				:streetname,
				:std_streetname,
				:city,
				:state,
				:zip_five,
				:email,
				:web_site,
				:contact_first,
				:contact_last,
				:phone,
				:staytime)";
	
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($shelters);
	
	$sql = "SELECT id FROM shelters ORDER BY id DESC";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$last =$stmt->fetch();
	updateHouseholds($shelters, $last['id']);	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "shelters", $last['id'], "ADD");			
}		

function updateShelter($id, $shelters) { 
	global $control;

	$shelters['id']=$id;
	
	$sql = "UPDATE shelters 
			SET name=:name,
				streetnum=:streetnum,
				streetname=:streetname,
				std_streetname=:std_streetname,
				city=:city,
				state=:state,
				zip_five=:zip_five,
				email=:email,
				web_site=:web_site,
				contact_first=:contact_first,
				contact_last=:contact_last,
				phone=:phone,
				staytime=:staytime
			WHERE id =:id";		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($shelters);
	
	updateHouseholds($shelters, $id);

	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "shelters", $shelters['id'], "UPDATE");		
}	

function updateHouseholds($shelters, $shelter_id) {
	global $control;
	
	$sql = "SELECT * FROM household 
			WHERE std_streetname=:std_streetname 
			AND city=:city
			AND state=:state";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':std_streetname', $shelters['std_streetname'], PDO::PARAM_STR);	
	$stmt->bindParam(':city', $shelters['city'], PDO::PARAM_STR);
	$stmt->bindParam(':state', $shelters['state'], PDO::PARAM_STR);	
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach ($result as $household) {
		$sql = "UPDATE household SET shelter=$shelter_id WHERE id =$household[id]";		
		$stmt= $control['db']->prepare($sql);
		$stmt->execute();
	}	

}	

function redirect($header,$err) {
	global $control;
	
	if (isset($_POST['id']))	
		$header .= "&edit=1&id=$_POST[id]";
	else
		$header .= "&add=1";		
	$header .= "&errCode=" . $err;
	$header .= "&name=" . urlencode($_POST['name']);
	$header .= "&address=" . urlencode( $_POST['address'] );	
	$header .= "&city=" . urlencode($_POST['city']);
	$header .= "&state=$_POST[state]";
	$header .= "&zip_five=$_POST[zip_five]";
	$header .= "&email=" . urlencode($_POST['email']);
	$header .= "&web_site=$_POST[web_site]";
	$header .= "&contact_first=" . urlencode($_POST['contact_first']);
	$header .= "&contact_last=" . urlencode($_POST['contact_last']);
	$header .= "&phone=$_POST[phone]";
	$header .= "&staytime=$_POST[staytime]";	
	
	return $header;	
} 
?> 