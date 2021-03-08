<?php
/**
 * addupdateproduct.php
 * written: 7/27/2020
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
	$control=fillControlArray($control, $config, "products");	
	
//	$control['db']=getDB($config);
	$control['err'] = 0;
//	if (isset($_POST['hhID']))
//		$control['hhID']=$_POST['hhID'];	
	if (isset($_POST['edit']))
		$productID= $_POST['productID'];		
	
/* MAINLINE */

	$header = "Location: ../products.php?hhID=" . $control['hhID'] . "&tab=definitions";

	if (!isset($_POST['cancel'])) {
		$products = editProducts();
		if (!$control['err']) {
			if (isset($_POST['add'])) 
				$productID=insertProduct($products); 
			else 
				updateProduct($productID, $products); 

			enterNameInfo($productID); 
			enterTypeInfo($productID); 	
			enterPantryInfo($productID);			
		} else	
			$header=redirect($header,$control['err']);				
	} 
	
	header($header);	

function editProducts() {
	global $control;

	$arr = [	
		'active'				=> 1,
		'personal'				=> "Yes",
		'hypoallergenic'		=> "No",
		'for_gender'			=> "Both",
		'for_incontinence'		=> "No",
		'for_cloth_diapering'	=> "No",
		'age_group'				=> "",
		'duration_2'			=> 0,
		'duration_4'			=> 0,
		'duration_6'			=> 0,
		'duration_8'			=> 0,
		'duration_10'			=> 0,
		'duration_12'			=> 0,
		'duration_14'			=> 0,
		'container'				=> "",
		'amount'				=> 0,
		'measure'				=> ""
	];
	
	if ($control['err']=editNameInfo())
		$foo=$control['err'];
	elseif (!is_numeric(trim($_POST['amount'])))
		$control['err'] = 58;
	elseif ($control['err']	= editTypeInfo())
		$foo=$control['err'];	
	elseif (!is_numeric(trim($_POST['duration_2'])))
		$control['err'] = 59;

	elseif ($_POST['personal'] == "No")		
		if (!is_numeric(trim($_POST['duration_4'])))
			$control['err'] = 59;			
		elseif (!is_numeric(trim($_POST['duration_6'])))
			$control['err'] = 59;			
		elseif (!is_numeric(trim($_POST['duration_8'])))
			$control['err'] = 59;	
		elseif (!is_numeric(trim($_POST['duration_10'])))
			$control['err'] = 59;	
		elseif (!is_numeric(trim($_POST['duration_12'])))
			$control['err'] = 59;
		elseif (!is_numeric(trim($_POST['duration_14'])))
			$control['err'] = 59;

	if (!$control['err']) {
		if (isset($_POST['productID']))
			$arr['id'] =$_POST['productID'];
		if (isset($_POST['active']))
			$arr['active']=1;
		else
			$arr['active']=0;
		$arr['personal']			= $_POST['personal'];
		if (isset($_POST['hypoallergenic']))
			$arr['hypoallergenic'] = "Yes";
		else
			$arr['hypoallergenic'] = "No";			
		$arr['for_gender']			= $_POST['for_gender'];
		if (isset($_POST['for_incontinence']))		
			$arr['for_incontinence']	= "Yes";
		else
			$arr['for_incontinence']	= "No";			
		$arr['age_group']			= $_POST['age_group'];
		$arr['duration_2']			= $_POST['duration_2'];
		$arr['duration_4']			= $_POST['duration_4'];
		$arr['duration_6']			= $_POST['duration_6'];
		$arr['duration_8']			= $_POST['duration_8'];
		$arr['duration_10']			= $_POST['duration_10'];	
		$arr['duration_12']			= $_POST['duration_12'];	
		$arr['duration_14']			= $_POST['duration_14'];
		$arr['container']			= $_POST['container'];
		$arr['amount']				= $_POST['amount'];
		$arr['measure']				= $_POST['measure'];
	}	
	
	return $arr;
}

function editNameInfo() {
	global $control;

	$err=0;
	$sql = "SELECT * FROM languages";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach($result as $row) {	
		$in = "name" . $row['id'];
		if (empty($_POST[$in]))
			$err = 60;
	}	
	return $err;
}	

function editTypeInfo() {
	
	$err=0;
	for ($n = 1; $n <= $_POST['numSizes']; $n++) {	
		$type="typenum" . $n;
		if (empty($_POST[$type]))
			$err=61;
	}	
	return $err;
}

function insertProduct($products) { 
	global $control;

	$sql = "INSERT INTO products 
			(active,
			personal,
			hypoallergenic,
			for_gender,
			for_incontinence,
			for_cloth_diapering,
			age_group,
			duration_2,
			duration_4,
			duration_6,
			duration_8,
			duration_10,
			duration_12,
			duration_14,
			container,
			amount,
			measure)
			
			VALUES
			(:active,
			:personal,
			:hypoallergenic,
			:for_gender,
			:for_incontinence,
			:for_cloth_diapering,
			:age_group,
			:duration_2,
			:duration_4,
			:duration_6,
			:duration_8,
			:duration_10,
			:duration_12,
			:duration_14,
			:container,
			:amount,
			:measure)";			
	
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($products);
	
	$sql = "SELECT id FROM products ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$products =$stmt->fetch();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
//	writeUserLog( $control['db'], $date, $time, $control['users_id'], $control['users_pantry_id'], "REGISTER HOUSEHOLD", $household['householdID'], $members['id'] );	
	writeUserLog( $control['db'], $date, $time, 0, "products", $products['id'], "ADD");	

	return $products['id'];
}		

function updateProduct($id, $products) { 
	global $control;

	$products['id']=$id;
		
	$sql = "UPDATE products
			SET active =:active,
				personal =:personal,
				hypoallergenic =:hypoallergenic,
				for_gender =:for_gender,
				for_incontinence =:for_incontinence,
				for_cloth_diapering	=:for_cloth_diapering,
				age_group =:age_group,
				duration_2 =:duration_2,
				duration_4 =:duration_4,
				duration_6 =:duration_6,
				duration_8 =:duration_8,
				duration_10 =:duration_10,
				duration_12 =:duration_12,
				duration_14 =:duration_14,
				container =:container,
				amount =:amount,
				measure =:measure
			WHERE id =:id";
					
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($products);	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');		
	writeUserLog( $control['db'], $date, $time, 0, "products", $products['id'], "UPDATE");		
	
}	

function enterNameInfo($productID) {
	global $control;
	
	$sql = "SELECT * FROM languages";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach($result as $row) {	
		$in = "name" . $row['id'];
		$name = $_POST[$in];
		$sql2 = "SELECT * FROM products_nameinfo
				 WHERE languageID = :languageID
				 AND productID= :productID";
		$stmt2 = $control['db']->prepare($sql2);
		$stmt2->bindParam(':languageID', $row['id'], PDO::PARAM_INT);
		$stmt2->bindParam(':productID', $productID, PDO::PARAM_INT);		
		$stmt2->execute();	
		$total = $stmt2->rowCount();			
		if ($total > 0) {
			$row2 = $stmt2->fetch();
			updateNameInfo($row2['id'],$productID,$row['id'], $name);
		} else 
			insertNameInfo($productID,$row['id'], $name);
	}
}

function updateNameInfo($id, $productID, $languageID, $name) {
	global $control;
	
	$sql = "UPDATE products_nameinfo 
			SET productID=		:productID,
				languageID=		:languageID, 
				name=			:name
			WHERE id =:id";	
			
	$stmt= $control['db']->prepare($sql);
	$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);		
	$stmt->bindParam(':languageID', $languageID, PDO::PARAM_INT);
	$stmt->bindParam(':name', $name, PDO::PARAM_STR);	
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();				
}	

function insertNameInfo($productID, $languageID, $name) {
	global $control;	
	
	$sql = "INSERT INTO products_nameinfo 
			(productID,
			languageID,
			name)	
			VALUES 
			(:productID,
			 :languageID,
			 :name)";				

	$stmt= $control['db']->prepare($sql);
	$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);		
	$stmt->bindParam(':languageID', $languageID, PDO::PARAM_INT);
	$stmt->bindParam(':name', $name, PDO::PARAM_STR);		
	$stmt->execute();	
}	

function enterTypeInfo($productID) {
	global $control;
	
	$sql = "DELETE FROM products_typeinfo WHERE productID = :productID";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);				
	$stmt->execute();	
	
	for ($n = 1; $n <= $_POST['numSizes']; $n++) {
		$type="typenum" . $n;
		$sql = "INSERT INTO products_typeinfo (productID, typenum, type)	
				VALUES (:productID, :typenum, :type)";				
		$stmt= $control['db']->prepare($sql);
		$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);		
		$stmt->bindParam(':typenum', $n, PDO::PARAM_INT);
		$stmt->bindParam(':type', $_POST[$type], PDO::PARAM_STR);		
		$stmt->execute();			
	}		
}

function enterPantryInfo($productID) {
	global $control;
	
	$sql = "SELECT * FROM pantries";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach($result as $pantries) {	
		$sql = "SELECT * FROM products_pantryinfo
				WHERE pantry = :pantry
				AND productID= :productID";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':pantry', $pantries['id'], PDO::PARAM_INT);
		$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);		
		$stmt->execute();	
		$total = $stmt->rowCount();			
		if ($total == 0) 
			insertPantryInfo($pantries['id'],$productID, 0);
		if ($_POST['numSizes'] > 0)		
			enterPantryInfoSizesTypes($pantries['id'], $productID);		
	}	
}

function enterPantryInfoSizesTypes($pantry, $productID) {
	global $control;
	
// If new product, or existing product had no previous sizes or types, then change typenum from 0 to 1 in first row
		$sql = "SELECT * FROM products_pantryinfo
				WHERE pantry = :pantry
				AND productID= :productID
				AND typenum = 0";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':pantry', $pantry, PDO::PARAM_INT);
		$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);	
		$stmt->execute();	
		$total = $stmt->rowCount();			
		if ($total > 0) {
			$sql = "UPDATE products_pantryinfo
					SET typenum =1 
					WHERE pantry = :pantry AND productID =:productID";
			$stmt= $control['db']->prepare($sql);
			$stmt->bindParam(':pantry', $pantry, PDO::PARAM_INT);
			$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);				
			$stmt->execute();	
		}	
	
// insert new sizes or types	
	for ($n = 1; $n <= $_POST['numSizes']; $n++) {
		$sql = "SELECT * FROM products_pantryinfo
				WHERE pantry = :pantry
				AND productID= :productID
				AND typenum = :typenum";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':pantry', $pantry, PDO::PARAM_INT);
		$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);	
		$stmt->bindParam(':typenum', $n, PDO::PARAM_INT);			
		$stmt->execute();	
		$total = $stmt->rowCount();			
		if ($total == 0) 
			insertPantryInfo($pantry, $productID, $n);
	}

// remove deleted sizes or types
	$sql = "DELETE FROM products_pantryinfo WHERE productID = :productID and typenum > :typenum";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':productID', $productID, PDO::PARAM_INT);	
	$stmt->bindParam(':typenum', $_POST['numSizes'], PDO::PARAM_INT);		
	$stmt->execute();		
}

function insertPantryInfo($pantryID, $productID, $typenum) {
	global $control;
	
	$data = [	
		'pantry' 		=> $pantryID,
		'productID' 	=> $productID,
		'typenum'		=> $typenum,
		'shelf' 		=> 0,
		'bin' 			=> 0,
		'carried' 		=> "No",
		'instock'		=> 0,
		'last_carried'	=> "0000-00-00",
		'portion_limit' => -1
	];	
	
	$sql = "INSERT INTO products_pantryinfo
			(pantry,
			productID,
			typenum,
			shelf,
			bin,
			carried,
			instock,
			last_carried,
			portion_limit)
			
			VALUES
			(:pantry,
			:productID,
			:typenum,
			:shelf,
			:bin,
			:carried,
			:instock,
			:last_carried,
			:portion_limit)";	
			
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($data);	
}		
	
function redirect($header,$err) {
	global $control;
	
	if (isset($_POST['edit']))	
		$header .= "&edit=1&productID=$_POST[productID]";
	else
		$header .= "&add=1";		
	$header .= "&errCode=" . $err;

// properties	
	if (isset($_POST['active']))
		$header .= "&active=1";
	else
		$header .= "&active=0";
	if (isset($_POST['hypoallergenic']))
		$header .= "&hypoallergenic=Yes";
	else
		$header .= "&hypoallergenic=No";			
	if (isset($_POST['for_incontinence']))		
		$header .= "&for_incontinence=Yes";
	else
		$header .= "&for_incontinence=No";		
	$header .= "&for_gender=" . $_POST['for_gender'];	
	$header .= "&personal=" . $_POST['personal'];	
	$header .= "&age_group=" . $_POST['age_group'];
	$header .= "&duration_2=" . $_POST['duration_2'];
	$header .= "&duration_4=" . $_POST['duration_4'];
	$header .= "&duration_6=" . $_POST['duration_6'];
	$header .= "&duration_8=" . $_POST['duration_8'];
	$header .= "&duration_10=" . $_POST['duration_10'];
	$header .= "&duration_12=" . $_POST['duration_12'];
	$header .= "&duration_14=" . $_POST['duration_14'];
	$header .= "&container=" . $_POST['container'];
	$header .= "&amount=" . $_POST['amount'];
	$header .= "&measure=" . $_POST['measure'];	
	
// name info	
	$sql = "SELECT * FROM languages";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach($result as $row) {	
		$in = "name" . $row['id'];
		$header .= "&" . $in . "=" . urlencode($_POST[$in]);
	}	

// type info
	$header .= "&numSizes=" . $_POST['numSizes'];
	for ($n = 1; $n <= $_POST['numSizes']; $n++) {	
		$type="typenum" . $n;
		$header .= "&" . $type . "=" . urlencode($_POST[$type]);
	}		
//echo $header;	
	return $header;	
} 
?> 