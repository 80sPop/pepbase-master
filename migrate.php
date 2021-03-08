<?php

function initNewMembers() {
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
		$sql = "UPDATE new_members SET ";
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
?>