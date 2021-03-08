<?php
/**
 * tables/addupdatelanguage.php
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
	require_once('../config.php'); 
	require_once('../functions.php');	

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
	$control=fillControlArray($control, $config, "tables");
	
	$header = "Location: ../tables.php?tab=languages&hhID=" . $control['hhID'];

	if (!isset($_POST['cancel'])) {
		$languages = editLanguage();
		if (!$control['err']) {
			if (isset($_POST['id']))
				updateLanguage($_POST['id'], $languages); 				
			else 
				insertLanguage($languages); 
		} else	
			$header=redirect($header,$control['err']);				
	} 
	
	header($header);	

function editLanguage() {
	global $control;
	
	$arr = [	
		'name' => "",
	]; 	
	
	if (!empty($_POST['name']))
		$arr['name'] = $_POST['name'];		
	else
		$control['err'] = 73;
	
	return $arr;
}

function insertLanguage($languages) { 
	global $control;
	
	$sql = "INSERT INTO languages (name) VALUES (:name)";
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($languages);
	
	$sql = "SELECT * FROM languages ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$languages = $stmt->fetch();		
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "languages", $languages['id'], "ADD");	
}		

function updateLanguage($id, $languages) { 
	global $control;

	$languages['id']=$id;
	
	$sql = "UPDATE languages SET name= :name WHERE id =:id";		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($languages);

	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "languages", $languages['id'], "UPDATE");	
}	

function redirect($header,$err) {
	global $control;
	
	if (isset($_POST['id']))	
		$header .= "&edit=1&id=$_POST[id]";
	else
		$header .= "&add=1";		
	$header .= "&errCode=" . $err;
	$header .= "&name=" . urlencode($_POST['name']);
	
	return $header;	
} 
?> 