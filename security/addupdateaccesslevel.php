<?php
/**
 * security/addupdateaccesslevel.php
 * written: 9/2/2020
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
	
	$control=fillControlArray($control, $config, "security");
	
	$header = "Location: ../security.php?tab=access&hhID=" . $control['hhID'];

	if (!isset($_POST['cancel'])) {
		$access_levels = editAccessLevel();
		if (!$control['err']) {
			if (isset($_POST['id']))
				updateAccessLevel($_POST['id'], $access_levels); 				
			else 
				insertAccessLevel($access_levels); 
		} else	
			$header=redirect($header,$control['err'],$access_levels);				
	} 
	
	header($header);	

function editAccessLevel() {
	global $control;
	
	$arr = [	
		'level'	=> 1,
		'name' => "",
		'hh_profile_update' => "",
		'hh_profile_delete' => "",
		'hh_profile_browse' => "",
		'hh_members_update' => "",
		'hh_members_delete' => "",
		'hh_members_browse' => "",
		'hh_eligible_update' => "",
		'hh_eligible_delete' => "",
		'hh_eligible_browse' => "",
		'hh_history_update' => "",
		'hh_history_delete' => "",
		'hh_history_browse' => "",
		'prod_def_update' => "",
		'prod_def_delete' => "",
		'prod_def_browse' => "",
		'instock_update' => "",
		'instock_delete' => "",
		'instock_browse' => "",
		'prod_setup_update' => "",
		'prod_setup_delete' => "",
		'prod_setup_browse' => "",
		'access_level_update' => "",
		'access_level_delete' => "",
		'access_level_browse' => "",
		'users_update' => "",
		'users_delete' => "",
		'users_browse' => "",
		'languages_update' => "",
		'languages_delete' => "",
		'languages_browse' => "",
		'measures_update' => "",
		'measures_delete' => "",
		'measures_browse' => "",
		'containers_update' => "",
		'containers_delete' => "",
		'containers_browse' => "",		
		'shelters_update' => "",
		'shelters_delete' => "",
		'shelters_browse' => "",
		'changepantry_browse' => "",
		'userlog_browse' => "",
		'convert_browse' => "",		
		'pantries_update' => "",
		'pantries_delete' => "",
		'pantries_browse' => "",
		'themes_update' => "",
		'themes_delete' => "",
		'themes_browse' => "",
		'advanced_update' => "",
		'advanced_delete' => "",
		'advanced_browse' => "",
		'reports_con_browse' => "",
		'reports_demo_browse' => "",
		'reports_charts_browse' => "",
		'reports_admin_browse' => ""		
	]; 	
	
	$control['err'] = editAccessLevelName();

	if (!$control['err']) {
		$arr['name'] = $_POST['name'];		
		if (isset($_POST['id']))
			$arr['level'] = $_POST['level'];
		else
			$arr['level'] = rotateAccessLevels();	
	}

	if (isset($_POST['hh_profile_update']))	$arr['hh_profile_update'] = $_POST['hh_profile_update'];
	if (isset($_POST['hh_profile_delete'])) $arr['hh_profile_delete'] =  $_POST['hh_profile_delete'];
	if (isset($_POST['hh_profile_browse'])) $arr['hh_profile_browse'] =  $_POST['hh_profile_browse'];
	if (isset($_POST['hh_members_update'])) $arr['hh_members_update'] =  $_POST['hh_members_update'];
	if (isset($_POST['hh_members_delete'])) $arr['hh_members_delete'] =  $_POST['hh_members_delete'];
	if (isset($_POST['hh_members_browse'])) $arr['hh_members_browse'] =  $_POST['hh_members_browse'];
	if (isset($_POST['hh_eligible_update'])) $arr['hh_eligible_update'] =  $_POST['hh_eligible_update'];
	if (isset($_POST['hh_eligible_delete'])) $arr['hh_eligible_delete'] =  $_POST['hh_eligible_delete'];
	if (isset($_POST['hh_eligible_browse'])) $arr['hh_eligible_browse'] =  $_POST['hh_eligible_browse'];
	if (isset($_POST['hh_history_update'])) $arr['hh_history_update'] =  $_POST['hh_history_update'];
	if (isset($_POST['hh_history_delete'])) $arr['hh_history_delete'] =  $_POST['hh_history_delete'];
	if (isset($_POST['hh_history_browse'])) $arr['hh_history_browse'] =  $_POST['hh_history_browse'];
	if (isset($_POST['prod_def_update'])) $arr['prod_def_update'] =  $_POST['prod_def_update'];
	if (isset($_POST['prod_def_delete'])) $arr['prod_def_delete'] =  $_POST['prod_def_delete'];
	if (isset($_POST['prod_def_browse'])) $arr['prod_def_browse'] =  $_POST['prod_def_browse'];
	if (isset($_POST['instock_update'])) $arr['instock_update'] =  $_POST['instock_update'];
	if (isset($_POST['instock_delete'])) $arr['instock_delete'] =  $_POST['instock_delete'];
	if (isset($_POST['instock_browse'])) $arr['instock_browse'] =  $_POST['instock_browse'];
	if (isset($_POST['prod_setup_update'])) $arr['prod_setup_update'] =  $_POST['prod_setup_update'];
	if (isset($_POST['prod_setup_delete'])) $arr['prod_setup_delete'] =  $_POST['prod_setup_delete'];
	if (isset($_POST['prod_setup_browse'])) $arr['prod_setup_browse'] =  $_POST['prod_setup_browse'];
	if (isset($_POST['access_level_update'])) $arr['access_level_update'] =  $_POST['access_level_update'];
	if (isset($_POST['access_level_delete'])) $arr['access_level_delete'] =  $_POST['access_level_delete'];
	if (isset($_POST['access_level_browse'])) $arr['access_level_browse'] =  $_POST['access_level_browse'];
	if (isset($_POST['users_update'])) $arr['users_update'] =  $_POST['users_update'];
	if (isset($_POST['users_delete'])) $arr['users_delete'] =  $_POST['users_delete'];
	if (isset($_POST['users_browse'])) $arr['users_browse'] =  $_POST['users_browse'];
	if (isset($_POST['languages_update'])) $arr['languages_update'] =  $_POST['languages_update'];
	if (isset($_POST['languages_delete'])) $arr['languages_delete'] =  $_POST['languages_delete'];
	if (isset($_POST['languages_browse'])) $arr['languages_browse'] =  $_POST['languages_browse'];
	if (isset($_POST['measures_update'])) $arr['measures_update'] =  $_POST['measures_update'];
	if (isset($_POST['measures_delete'])) $arr['measures_delete'] =  $_POST['measures_delete'];
	if (isset($_POST['measures_browse'])) $arr['measures_browse'] =  $_POST['measures_browse'];
	if (isset($_POST['containers_update'])) $arr['containers_update'] =  $_POST['containers_update'];
	if (isset($_POST['containers_delete'])) $arr['containers_delete'] =  $_POST['containers_delete'];
	if (isset($_POST['containers_browse'])) $arr['containers_browse'] =  $_POST['containers_browse'];	
	if (isset($_POST['shelters_update'])) $arr['shelters_update'] =  $_POST['shelters_update'];
	if (isset($_POST['shelters_delete'])) $arr['shelters_delete'] =  $_POST['shelters_delete'];
	if (isset($_POST['shelters_browse'])) $arr['shelters_browse'] =  $_POST['shelters_browse'];
	if (isset($_POST['changepantry_browse'])) $arr['changepantry_browse'] =  $_POST['changepantry_browse'];
	if (isset($_POST['userlog_browse'])) $arr['userlog_browse'] =  $_POST['userlog_browse'];
	if (isset($_POST['convert_browse'])) $arr['convert_browse'] =  $_POST['convert_browse'];	
	if (isset($_POST['pantries_update'])) $arr['pantries_update'] =  $_POST['pantries_update'];
	if (isset($_POST['pantries_delete'])) $arr['pantries_delete'] =  $_POST['pantries_delete'];
	if (isset($_POST['pantries_browse'])) $arr['pantries_browse'] =  $_POST['pantries_browse'];
	if (isset($_POST['themes_update'])) $arr['themes_update'] =  $_POST['themes_update'];
	if (isset($_POST['themes_delete'])) $arr['themes_delete'] =  $_POST['themes_delete'];
	if (isset($_POST['themes_browse'])) $arr['themes_browse'] =  $_POST['themes_browse'];
	if (isset($_POST['advanced_update'])) $arr['advanced_update'] =  $_POST['advanced_update'];
	if (isset($_POST['advanced_delete'])) $arr['advanced_delete'] =  $_POST['advanced_delete'];
	if (isset($_POST['advanced_browse'])) $arr['advanced_browse'] =  $_POST['advanced_browse'];
	if (isset($_POST['reports_con_browse'])) $arr['reports_con_browse'] =  $_POST['reports_con_browse'];
	if (isset($_POST['reports_demo_browse'])) $arr['reports_demo_browse'] =  $_POST['reports_demo_browse'];
	if (isset($_POST['reports_charts_browse'])) $arr['reports_charts_browse'] =  $_POST['reports_charts_browse'];
	if (isset($_POST['reports_admin_browse'])) $arr['reports_charts_browse'] =  $_POST['reports_charts_browse'];			
	
	
	return $arr;
}

function editAccessLevelName() {
	global $control;
	
	$err=0;
	
	if (empty($_POST['name']))
		$err = 71;	
	else {
		$sql = "SELECT * FROM access_levels WHERE name = :name";
		$stmt = $control['db']->prepare($sql);
		$stmt->bindParam(':name', $_POST['name'], PDO::PARAM_STR);		
		$stmt->execute();	
		$access_levels = $stmt->fetch();		
		$total= $stmt->rowCount();	
		if ($total > 0) 
			if (isset($_POST['id'])) {
				if ($access_levels['id'] != $_POST['id'])
					$err=6;
			} else
				$err=6;	
	}
	
	return $err;
}

function rotateAccessLevels() {
	global $control;
	
	$newLevel=$_POST['level'] +1;

	$sql = "SELECT * FROM access_levels WHERE level >= :newLevel";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':newLevel', $newLevel, PDO::PARAM_INT);		
	$stmt->execute();	
	$result = $stmt->fetchAll();			
	foreach($result as $access_levels)		
		bumpLevel($access_levels['level'], $access_levels['id']);
		
	return $newLevel;	
	
}

function bumpLevel($level, $id) {
	global $control;
	
	$bump=$level+1;
	$sql = "UPDATE access_levels SET level= :level WHERE id =:id";		
	$stmt= $control['db']->prepare($sql);
	$stmt->bindParam(':level', $bump, PDO::PARAM_INT);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);		
	$stmt->execute();	
}	

function insertAccessLevel($access_levels) { 
	global $control;
	
	$sql = "INSERT INTO access_levels 	
	
		(level,	
		name,	
		hh_profile_update,
		hh_profile_delete,
		hh_profile_browse,
		hh_members_update,
		hh_members_delete,
		hh_members_browse,
		hh_eligible_update,
		hh_eligible_delete,
		hh_eligible_browse,
		hh_history_update,
		hh_history_delete,
		hh_history_browse,
		prod_def_update,
		prod_def_delete,
		prod_def_browse,
		instock_update,
		instock_delete,
		instock_browse,
		prod_setup_update,
		prod_setup_delete,
		prod_setup_browse,
		access_level_update,
		access_level_delete,
		access_level_browse,
		users_update,
		users_delete,
		users_browse,
		languages_update,
		languages_delete,
		languages_browse,
		measures_update,
		measures_delete,
		measures_browse,
		containers_update,
		containers_delete,
		containers_browse,		
		shelters_update,
		shelters_delete,
		shelters_browse,
		changepantry_browse,
		userlog_browse,
		convert_browse,				
		pantries_update,
		pantries_delete,
		pantries_browse,
		themes_update,
		themes_delete,
		themes_browse,
		advanced_update,
		advanced_delete,
		advanced_browse,
		reports_con_browse,
		reports_demo_browse,
		reports_charts_browse,
		reports_admin_browse)	
		
		VALUES 
		(:level,
		:name,
		:hh_profile_update,
		:hh_profile_delete,
		:hh_profile_browse,
		:hh_members_update,
		:hh_members_delete,
		:hh_members_browse,
		:hh_eligible_update,
		:hh_eligible_delete,
		:hh_eligible_browse,
		:hh_history_update,
		:hh_history_delete,
		:hh_history_browse,
		:prod_def_update,
		:prod_def_delete,
		:prod_def_browse,
		:instock_update,
		:instock_delete,
		:instock_browse,
		:prod_setup_update,
		:prod_setup_delete,
		:prod_setup_browse,
		:access_level_update,
		:access_level_delete,
		:access_level_browse,
		:users_update,
		:users_delete,
		:users_browse,
		:languages_update,
		:languages_delete,
		:languages_browse,
		:measures_update,
		:measures_delete,
		:measures_browse,
		:containers_update,
		:containers_delete,
		:containers_browse,		
		:shelters_update,
		:shelters_delete,
		:shelters_browse,
		:changepantry_browse,
		:userlog_browse,
		:convert_browse,			
		:pantries_update,
		:pantries_delete,
		:pantries_browse,
		:themes_update,
		:themes_delete,
		:themes_browse,
		:advanced_update,
		:advanced_delete,
		:advanced_browse,
		:reports_con_browse,
		:reports_demo_browse,
		:reports_charts_browse,
		:reports_admin_browse)";
		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($access_levels);
	
	$sql = "SELECT * FROM access_levels ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$access_levels = $stmt->fetch();	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "access_levels", $access_levels['id'], "ADD");		
	
}		

function updateAccessLevel($id, $access_levels) { 
	global $control;

	$access_levels['id']=$id;
	
	$sql = "UPDATE access_levels
			SET level= :level,
				name=:name,
				hh_profile_update=:hh_profile_update,
				hh_profile_delete=:hh_profile_delete,
				hh_profile_browse =:hh_profile_browse,
				hh_members_update=:hh_members_update,
				hh_members_delete =:hh_members_delete,
				hh_members_browse=:hh_members_browse,
				hh_eligible_update=:hh_eligible_update,
				hh_eligible_delete =:hh_eligible_delete,
				hh_eligible_browse =:hh_eligible_browse,
				hh_history_update =:hh_history_update,
				hh_history_delete =:hh_history_delete,
				hh_history_browse =:hh_history_browse,
				prod_def_update =:prod_def_update,
				prod_def_delete =:prod_def_delete,
				prod_def_browse	=:prod_def_browse,
				instock_update =:instock_update,
				instock_delete =:instock_delete,
				instock_browse =:instock_browse,
				prod_setup_update =:prod_setup_update,
				prod_setup_delete =:prod_setup_delete,
				prod_setup_browse =:prod_setup_browse,
				access_level_update =:access_level_update,
				access_level_delete =:access_level_delete,
				access_level_browse =:access_level_browse,
				users_update =:users_update,
				users_delete =:users_delete,
				users_browse =:users_browse,
				languages_update =:languages_update,
				languages_delete =:languages_delete,
				languages_browse =:languages_browse,
				measures_update =:measures_update,
				measures_delete =:measures_delete,
				measures_browse =:measures_browse,
				containers_update =:containers_update,
				containers_delete =:containers_delete,
				containers_browse =:containers_browse,				
				shelters_update =:shelters_update,
				shelters_delete =:shelters_delete,
				shelters_browse =:shelters_browse,
				changepantry_browse	=:changepantry_browse,
				userlog_browse	=:userlog_browse,
				convert_browse	=:convert_browse,					
				pantries_update =:pantries_update,
				pantries_delete =:pantries_delete,
				pantries_browse =:pantries_browse,
				themes_update =:themes_update,
				themes_delete =:themes_delete,
				themes_browse =:themes_browse,
				advanced_update=:advanced_update,
				advanced_delete =:advanced_delete,
				advanced_browse =:advanced_browse,
				reports_con_browse =:reports_con_browse,
				reports_demo_browse =:reports_demo_browse,
				reports_charts_browse =:reports_charts_browse,
				reports_admin_browse =:reports_admin_browse
			WHERE id =:id";		
	
	$stmt= $control['db']->prepare($sql);
	$stmt->execute($access_levels);	
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "access_levels", $access_levels['id'], "UPDATE");		
	

}	

function redirect($header, $err, $arr) {
	global $control;
	
	if (isset($_POST['id']))	
		$header .= "&edit=1&id=$_POST[id]";
	else
		$header .= "&add=1";		
	$header .= "&errCode=" . $err;
	$header .= "&name=" . urlencode($arr['name']);
	$header .= "&level=" . urlencode($arr['level']);
	$header .= "&hh_profile_update=" . $arr['hh_profile_update'];
	$header .= "&hh_profile_delete=" . $arr['hh_profile_delete'];
	$header .= "&hh_profile_browse=" . $arr['hh_profile_browse'];
	$header .= "&hh_members_update=" . $arr['hh_members_update'];
	$header .= "&hh_members_delete=" . $arr['hh_members_delete'];
	$header .= "&hh_members_browse=" . $arr['hh_members_browse'];
	$header .= "&hh_eligible_update=" . $arr['hh_eligible_update'];
	$header .= "&hh_eligible_delete=" . $arr['hh_eligible_delete'];
	$header .= "&hh_eligible_browse=" . $arr['hh_eligible_browse'];
	$header .= "&hh_history_update=" . $arr['hh_history_update'];
	$header .= "&hh_history_delete=" . $arr['hh_history_delete'];
	$header .= "&hh_history_browse=" . $arr['hh_history_browse'];
	$header .= "&prod_def_update=" . $arr['prod_def_update'];
	$header .= "&prod_def_delete=" . $arr['prod_def_delete'];
	$header .= "&prod_def_browse=" . $arr['prod_def_browse'];
	$header .= "&instock_update=" . $arr['instock_update'];
	$header .= "&instock_delete=" . $arr['instock_delete'];
	$header .= "&instock_browse=" . $arr['instock_browse'];
	$header .= "&prod_setup_update=" . $arr['prod_setup_update'];
	$header .= "&prod_setup_delete=" . $arr['prod_setup_delete'];
	$header .= "&prod_setup_browse=" . $arr['prod_setup_browse'];
	$header .= "&access_level_update=" . $arr['access_level_update'];
	$header .= "&access_level_delete=" . $arr['access_level_delete'];
	$header .= "&access_level_browse=" . $arr['access_level_browse'];
	$header .= "&users_update=" . $arr['users_update'];
	$header .= "&users_delete=" . $arr['users_delete'];
	$header .= "&users_browse=" . $arr['users_browse'];
	$header .= "&languages_update=" . $arr['languages_update'];
	$header .= "&languages_delete=" . $arr['languages_delete'];
	$header .= "&languages_browse=" . $arr['languages_browse'];
	$header .= "&measures_update=" . $arr['measures_update'];
	$header .= "&measures_delete=" . $arr['measures_delete'];
	$header .= "&measures_browse=" . $arr['measures_browse'];
	$header .= "&containers_update=" . $arr['containers_update'];
	$header .= "&containers_delete=" . $arr['containers_delete'];
	$header .= "&containers_browse=" . $arr['containers_browse'];	
	$header .= "&shelters_update=" . $arr['shelters_update'];
	$header .= "&shelters_delete=" . $arr['shelters_delete'];
	$header .= "&shelters_browse=" . $arr['shelters_browse'];
	$header .= "&changepantry_browse=" . $arr['changepantry_browse'];
	$header .= "&userlog_browse=" . $arr['userlog_browse'];
	$header .= "&convert_browse=" . $arr['convert_browse'];	
	$header .= "&pantries_update=" . $arr['pantries_update'];
	$header .= "&pantries_delete=" . $arr['pantries_delete'];
	$header .= "&pantries_browse=" . $arr['pantries_browse'];
	$header .= "&themes_update=" . $arr['themes_update'];
	$header .= "&themes_delete=" . $arr['themes_delete'];
	$header .= "&themes_browse=" . $arr['themes_browse'];
	$header .= "&advanced_update=" . $arr['advanced_update'];
	$header .= "&advanced_delete=" . $arr['advanced_delete'];
	$header .= "&advanced_browse=" . $arr['advanced_browse'];
	$header .= "&reports_con_browse=" . $arr['reports_con_browse'];
	$header .= "&reports_demo_browse=" . $arr['reports_demo_browse'];
	$header .= "&reports_charts_browse=" . $arr['reports_charts_browse'];
	$header .= "&reports_admin_browse=" . $arr['reports_admin_browse'];	
	

	return $header;	
} 
?> 