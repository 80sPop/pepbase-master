<?php
/**
 * setup/addpantry.php
 * written: 8/11/2020
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

	$control=fillControlArray($control, $config, "households");	
	$control['err'] = 0;

	if (isset($_POST['continue'])) {
		$pantries = editPantry();
		if (!$control['err']) 	
			$header=insertPantry($pantries, $header); 			
		else
			$header=redirect($control['err']);
	}	
	
	header($header);	

function editPantry() {
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
	
	if (empty($_POST['name']))
		$control['err'] = 63;	
	elseif (empty($_POST['abbrev']))
		$control['err'] = 64;	
	elseif (!isValidDate($_POST['start_date'], 'Y-m-d')) 	
		$control['err'] = 40;
	elseif (!empty($_POST['inactive_date']) && !isValidDate($_POST['inactive_date'], 'Y-m-d')) 	
		$control['err'] = 68;		
	elseif (empty($_POST['address_1']))
		$control['err'] = 65;
	elseif (empty($_POST['city'])) 	
		$control['err'] = 49;	
	elseif (empty($_POST['county'])) 	
		$control['err'] = 50;			
	if (!$control['err']) {
		$zip=editZipcode($control['db'], strtolower($_POST['city']), strtolower($_POST['county']), strtolower($_POST['state']), $_POST['zip_5']);
		$control['err']=$zip['errCode'];	
	}	

	if (!$control['err'])
		if ( empty($_POST['email']) || (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ) 
			$control['err'] = 24;	
		
	if (!$control['err'])
		if ( empty($_POST['contact_first']))
			$control['err'] = 66;	
		
	if (!$control['err'])
		if ( empty($_POST['contact_last']))
			$control['err'] = 67;			
		
	if (!$control['err'])
		if (!isValidPhone($_POST['phone']))		
			$control['err'] = 54;	
	if (!$control['err'])
		if (!isValidPhone($_POST['cell_phone']))		
			$control['err'] = 55;	

	if (!$control['err']) {

		$arr['name'] = $_POST['name'];
		$arr['abbrev'] = $_POST['abbrev'];
		$arr['start_date'] = $_POST['start_date'];
		$arr['inactive_date'] = $_POST['inactive_date'];
		if (!empty($_POST['inactive_date']))
			$arr['is_active'] = 0;		
		$arr['address_1'] = $_POST['address_1'];
		$arr['address_2'] = $_POST['address_2'];
		$arr['city'] = $_POST['city'];
		$arr['county'] = $_POST['county'];
		$arr['state'] = $_POST['state'];
		$arr['zip_5'] = $_POST['zip_5'];
		$arr['email'] = $_POST['email'];
		$arr['web_site'] = $_POST['web_site'];
		$arr['contact_first'] = $_POST['contact_first'];
		$arr['contact_last'] = $_POST['contact_last'];
		$arr['phone'] = $_POST['phone'];
		$arr['cell_phone'] = $_POST['cell_phone'];
		$arr['hours_1'] = $_POST['hours_1'];
		$arr['hours_2'] = $_POST['hours_2'];
		$arr['hours_3'] = $_POST['hours_3']; 
		$arr['hours_4'] = $_POST['hours_4'];
	}	
	
	return $arr;
}

function insertPantry($pantries) { 
	global $control;
	
	$sql = "INSERT INTO pantries 	
	
		(name,
		abbrev,
		start_date,
		is_active,
		inactive_date,
		is_food_pantry,
		theme_id,
		address_1,
		address_2,
		city,
		county,
		state,
		zip_5,
		zip_4,
		email,
		web_site,
		contact_first,
		contact_last,
		phone,
		cell_phone,
		hours_1,
		hours_2,
		hours_3,
		hours_4)
	
		VALUES 
		(:name,
		:abbrev,
		:start_date,
		:is_active,
		:inactive_date,
		:is_food_pantry,
		:theme_id,
		:address_1,
		:address_2,
		:city,
		:county,
		:state,
		:zip_5,
		:zip_4,
		:email,
		:web_site,
		:contact_first,
		:contact_last,
		:phone,
		:cell_phone,
		:hours_1,
		:hours_2,
		:hours_3,
		:hours_4)";	

	$stmt= $control['db']->prepare($sql);
	$stmt->execute($pantries);
	
	$sql = "SELECT id FROM pantries ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$pantries =$stmt->fetch();	
	
	$header = "Location: index.php?addadmin=1";	
	return $header;		
	
}	

function redirect($err) {
	global $control;
	
	$header = "Location: index.php?addpantry=1&errCode=$err";	
	$header .= "&name=" . urlencode($_POST['name']);
	$header .= "&abbrev=" . urlencode($_POST['abbrev']);
	$header .= "&start_date=" . $_POST['start_date'];
	$header .= "&inactive_date=" . $_POST['inactive_date'];
	$header .= "&address_1=" . urlencode($_POST['address_1']);
	$header .= "&address_2=" . urlencode($_POST['address_2']);
	$header .= "&city=" . urlencode($_POST['city']);
	$header .= "&county=" . urlencode($_POST['county']);
	$header .= "&state=" . $_POST['state'];
	$header .= "&zip_5=" . urlencode($_POST['zip_5']);
	$header .= "&email=" . urlencode($_POST['email']);
	$header .= "&web_site=" . urlencode($_POST['web_site']);
	$header .= "&contact_first=" . urlencode($_POST['contact_first']);
	$header .= "&contact_last=" . urlencode($_POST['contact_last']);
	$header .= "&phone=" . $_POST['phone'];
	$header .= "&cell_phone=" . $_POST['cell_phone'];
	$header .= "&hours_1=" . urlencode($_POST['hours_1']);
	$header .= "&hours_2=" . urlencode($_POST['hours_2']);
	$header .= "&hours_3=" . urlencode($_POST['hours_3']);
	$header .= "&hours_4=" . urlencode($_POST['hours_4']);	
	
	return $header;	
} 
?> 