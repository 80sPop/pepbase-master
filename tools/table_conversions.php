<?php
/**
 * table_conversions.php
 * written: 9/8/2020
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

function covertMenu() {
	global $control;
	
	$link="tools.php?hhID=$control[hhID]&tab=convert";
	
	if (isset($_GET['nicknames'])) {
		
		echo "
		<div class='container-fluid bg-gray-2 p-3'>
		adding nicknames........please wait.
		</div>";
	
		initNicknames();
		
		echo "
		<div class='container-fluid bg-gray-2 p-3'>
		finished updating. <a style='color:#841E14;text-decoration:underline;' href='$link'>Go Back</a>
		</div>";		
		
	
//	} elseif (isset($_GET['copyeligible'])) {
//		
//		echo "
//		<div class='container-fluid bg-gray-2 p-3'>
//		copying eligible for........please wait.
//		</div>";
	
//		initApprovedFor();
		
//		echo "
//		<div class='container-fluid bg-gray-2 p-3'>
//		finished updating. <a style='color:#841E14;text-decoration:underline;' href='$link'>Go Back</a>
//		</div>";
		
	} else 
		
		echo "
		<div class='container-fluid bg-gray-2'>
			<div class='container p-3'>	
				Nickname searching is included with Pepbase 4. This routine only needs to run once after the pep3 database is copied to pep4 and 
				all other table updates have been made.

				<div class='container p-3'>	
					<a style='color:#841E14;text-decoration:underline;' href='$link&nicknames=1'>Add nicknames to members table</a>
				</div>

			</div>	
			
		</div>";	
}	
	
function initNicknames() {
	global $control;
	
	$sql = "SELECT * FROM members"; 
	$stmt = $control['db']->query($sql);
	while ($members = $stmt->fetch()) 
		addNicknames( $members['id'], $members['firstname']); 	
}

function addNicknames( $id, $firstname ) {
	global $control;

	$data=array();
	$sql = "SELECT * FROM nicknames WHERE firstname=:firstname";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);	
	$stmt->execute();
	$total = $stmt->rowCount();	
	if ($total > 0) {	
		$nicknames = $stmt->fetch();
		$sql = "UPDATE members SET ";
		for ($n = 1; $n <= 15; $n++) {
			if ($n==1) $comma=""; else $comma=",";
			$index=	"nick" . $n;
			$sql .= "$comma $index='" . $nicknames[$index] . "'";
		}
		$sql .=	" WHERE id = :id";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
		$stmt->execute();		
	}	
}	

function initApprovedFor() {
	global $control;
	
	$sql = "SELECT * FROM consumption"; 
	$stmt = $control['db']->query($sql);
	while ($consumption = $stmt->fetch()) 
		copyEligibleFor( $consumption['id'], $consumption['quantity_oked']); 	
}

function copyEligibleFor( $id, $quantity_oked ) {
	global $control;

	$sql = "UPDATE consumption SET quantity_approved=quantity_oked WHERE id = :id";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);	
	$stmt->execute();		

}	
?>