<?php
/**
 * setup/writeconfig.php
 * written: 12/16/2020
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

	$control=array();	
	$control['err'] = 0;	
	
/* MAINLINE */

	if (isset($_POST['host'])) {
		$control['err'] = verifyConnection();
		if (!$control['err']) 	
			$header=writeConfigFile();
		if (!$control['err']) 	
			buildDatabase();		
		if ($control['err'])
			$header=redirect($control['err']);
	}	
	
	header($header);	

function verifyConnection() {
	global $control;

	$err=0;	
	
	if (empty($_POST['host']) || empty($_POST['dbname']) || empty($_POST['user']) || empty($_POST['pswd']))
		$err= 79;	
	else {

		try {
			$db = new PDO("mysql:host=$_POST[host]", $_POST['user'], $_POST['pswd']);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		} catch (PDOException $e) {
			$err= 79;
//			die('Connection failed: ' . $e->getMessage());				// development
//			die("CONNECTION ERROR! Check database configuration.");		// production
		}	
		
		if (!$err) 
			$control['db'] = $db;	
	}	
	
	return $err;
}

function buildDatabase() {
	global $control;

	$sql = "DROP DATABASE IF EXISTS `$_POST[dbname]`;
			CREATE DATABASE `$_POST[dbname]`;
			FLUSH PRIVILEGES;";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	
	try {
		$db = new PDO("mysql:dbname=$_POST[dbname];host=$_POST[host]", $_POST['user'], $_POST['pswd']);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		$control['err']= 79;		
//		die('Database failed: ' . $e->getMessage());				// development
//		die("CONNECTION ERROR! Check database configuration.");		// production
	}		
	
	if (!$control['err']) {
		ob_start();             		// start capturing output
		include('pep4.sql');   			// execute the file
		$sql = ob_get_contents();		// get the contents from the buffer
		ob_end_clean(); 
		$stmt = $db->prepare($sql);
		$stmt->execute();	
	}	
}	

function writeConfigFile() {
	global $control;
	
	$content = "<?php
$" . "config=array();

// $" . "config['isTraining']
// displays training message in header
// 0 - default
// 1 - display training message 
$" . "config['isTraining']=0;

// $" . "config['isTesting']
// used for testing mail server
// 0 - production 
// 1 - development 
$" . "config['isTesting']=0;

// VERIFICATION_KEY
// multiplication key used on cookies before encryption
// this can be any integer value
define( 'VERIFICATION_KEY', 54321 );
define( 'MAX_SIZES_TYPES', 20 );
define( 'MAX_PORTION_LIMIT', 20 );

// MySQL database connection
$" . "config['host'] = '" . $_POST['host'] . "';
$" . "config['dbname'] = '" . $_POST['dbname'] . "';
$" . "config['user'] = '" . $_POST['user'] . "';
$" . "config['pswd'] = '" . $_POST['pswd'] . "';
$" . "root_depth = 3;

// set time zone
date_default_timezone_set('" . $_POST['timezone'] . "');

// define root path for nested directories 
$" . "path='';
for ( $" . "depth = $" . "root_depth; $" . "depth <= substr_count($" . "_SERVER['PHP_SELF'],'/'); $" . "depth++ ) 
    $" . "path .= '../';
define( 'ROOT', $" . "path );
?>";
	
	$fp = fopen( "../config.php","wb");
	if (!fwrite($fp,$content)) {
		$control['err'] = 81;		
		$header = "";	
	} else {
		fclose($fp);		
		$header = "Location: index.php?addpantry=1";
	}	
	
	return $header;
}	

function redirect($err) {

	$header = "Location: index.php?dbconfig=1&errCode=$err";
	$header.= "&host=$_POST[host]"; 
	$header.= "&dbname=$_POST[dbname]"; 
	$header.= "&user=$_POST[user]";
	$header.= "&pswd=$_POST[pswd]";	
	$header.= "&timezone=$_POST[timezone]";	
	
	return $header;	
} 
?> 