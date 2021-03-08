<?php
/**
 * products.php
 * written: 7/15/2020
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

	$control=fillControlArray($control, $config, "products");
	$control=loadAccessLevels();	
	doSaveUpdate();
	$control=setFocus($control);	
	doHeader("Households");
	doNavbar();	
	doProductsNavBar();	
	
	if ($control['tab'] == "definitions")
		if (isset($_GET['productID']))
			productForm("edit", $errMsg);	
		elseif (isset($_GET['add']))
			productForm("add", $errMsg);			
		else
			listProductsDefinitions();
	elseif ($control['tab'] == "instock")
		instockForm();		
	elseif ($control['tab'] == "setup")
		pantrySetupForm();
	
	echo "</body>\n";
	bFooter(); 
	echo "</html>";
	
function setFocus($arr) {
	global $control;
	
	$arr["focus"] = "nofocus";
	if (isset($_GET['errCode'])) 
		$arr['errCode'] = $_GET['errCode'];		
	
	if ($control['tab'] == "definitions") {
		if (isset($_GET['add']) || isset($_GET['edit']))
			$arr["focus"] = getProductNameFocus();
		if ($arr['errCode'] == 58)
			$arr["focus"] = "amount";	
		elseif ($arr['errCode'] == 61)
			$arr["focus"] = "typenum1";	
		elseif ($arr['errCode'] == 59)
			$arr["focus"] = "duration_2";				
	}

	return $arr;		
}	

function getProductNameFocus() {
	global $control;

	$sql = "SELECT * FROM languages ORDER BY name";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$row = $stmt->fetch();
	$field = "name" . $row['id'];
	return $field;
}	

function doProductsNavBar() {
	global $control;
	
	$link="products.php?hhID=$control[hhID]";
	
	$dActive="";
	$iActive="";	
	$pActive="";	
	
	if ($control['tab'] == "definitions")
		$dActive="active";
	elseif ($control['tab'] == "instock")
		$iActive="active";	
	elseif ($control['tab'] == "setup")
		$pActive="active";	
		
		
	$seeDefinitions= ($control['prod_def_update'] == "checked" || $control['prod_def_delete'] == "checked" || $control['prod_def_browse'] == "checked");
	$seeInstock= ($control['instock_update'] == "checked" || $control['instock_delete'] == "checked" || $control['instock_browse'] == "checked"); 
	$seeSetup=($control['prod_setup_update'] == "checked" || $control['prod_setup_delete'] == "checked" || $control['prod_setup_browse'] == "checked"); 

	echo "
	<div class='container-fluid pt-3'>
		<ul class='nav nav-tabs'>";
		if ($seeDefinitions)
			echo "
			<li class='nav-item'>
			<a class='nav-link $dActive text-dark' href='" . $link . "&tab=definitions'>Definitions</a>
			</li>\n";
		if ($seeInstock)
			echo "
			<li class='nav-item'>
			<a class='nav-link $iActive text-dark' href='" . $link . "&tab=instock'>In-Stock Status</a>
			</li>\n";
		if ($seeSetup)
			echo "
			<li class='nav-item'>
			<a class='nav-link $pActive text-dark' href='" . $link . "&tab=setup'>Pantry Setup</a>
			</li>\n";
		echo "	
		</ul>
	</div>";
}


function doSaveUpdate() {
	global $control;
	
	if (isset($_POST['saveSetup']))			// products/pantryinfo.php	
		updatePantryInfo();
	if (isset($_POST['saveInstock']))		// products/instock.php	
		updateInstockStatus();
}	

?>

<script>	

	$("#phone1").inputmask({"mask": "(999) 999-9999"});	
	$("#phone2").inputmask({"mask": "(999) 999-9999"});		

	$("[name='active']").bootstrapSwitch();	
	$("[name='hypoallergenic']").bootstrapSwitch();		
	$("[name='for_incontinence']").bootstrapSwitch();

<?php

	if ( $control['tab'] == "setup" || $control['tab'] == "instock" ) {
		$sql = "SELECT * FROM products_pantryinfo
				INNER JOIN products ON products.id = products_pantryinfo.productID AND products.active = 1
				INNER JOIN products_nameinfo ON products_pantryinfo.productID = products_nameinfo.productID AND products_nameinfo.languageID=1
				WHERE pantry = $control[users_pantry_id]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$total = $stmt->rowCount();				
		$result = $stmt->fetchAll();		
		foreach($result as $row) {	
			if ( $control['tab'] == "setup" )
				$name = "carried" . $row['productID'] . "T" . $row['typenum'];
			elseif ( $control['tab'] == "instock" )
				$name = "instock" . $row['productID'] . "T" . $row['typenum'];

			echo '$("[name=' . "'$name'" . ']").bootstrapSwitch();';
		}	
	}	
?>	
	
	$('#personal1').click(function () { 
	   $("#dur_2_col").addClass('d-none');
	   $("#dur_2_col_na").removeClass('d-none');
	   $("#dur_4_row").addClass('d-none');
	   $("#dur_6_row").addClass('d-none');	
	   $("#dur_8_row").addClass('d-none');
	   $("#dur_10_row").addClass('d-none');   
	   $("#dur_12_row").addClass('d-none');
	   $("#dur_14_row").addClass('d-none');	   
	})	
	
	$('#personal2').click(function () { 
	   $("#dur_2_col").removeClass('d-none');	
	   $("#dur_2_col_na").addClass('d-none');	
	   $("#dur_4_row").removeClass('d-none');
	   $("#dur_6_row").removeClass('d-none');	
	   $("#dur_8_row").removeClass('d-none');
	   $("#dur_10_row").removeClass('d-none');   
	   $("#dur_12_row").removeClass('d-none');
	   $("#dur_14_row").removeClass('d-none');	 	   
	})		
	
	$('#numSizesTypes').click(function () { 
	
		var num_sizes = document.getElementById("numSizesTypes").value;
		var start = Number(num_sizes) + 1;
		for (n = 1; n <= <?php echo MAX_SIZES_TYPES; ?>; n++) {
			id = "size_" + n; 	

			var el = document.getElementById(id);
			
			if (n < start) { 
				$("#" + id).removeClass('d-none');			
			} else {
				$("#" + id).addClass('d-none');				
			}	
		}
	})	
	
</script>	