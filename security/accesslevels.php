<?php
/**
 * security/accesslevels.php
 * written: 8/29/2020
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

function listAccessLevels() {
	global $control;
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=access&add=1";

	$sql = "SELECT * from access_levels ORDER BY level";	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 
	
		<div class='row p-3'>
<?php
		if ($control['access_level_update']) 
		  echo "			
			<div class='col-sm' style='color:#841E14;'><i class='fa fa-plus pr-2 p-1'></i><a style='color:inherit;' href='$link' >Add Access Level</a></div>";
?>			
			<div class='col-sm text-right'><?php doLevelCount(); ?></div>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doAccessLevelHeadings();			
			foreach($result as $row) {	
				$elink= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=access&id=$row[id]&edit=1";
				$dlink=  $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=access&id=$row[id]&level=$row[level]&delete=1";				
				
				echo "
				<tr>
				<td class='border border-dark bg-gray-3 p-1'>$row[level]</td>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[name]</td>		
				<td class='border border-dark bg-gray-3 p-1'>";
				
				// may NOT edit or delete Administrator Access Level
				if ($row['id'] != 1 && ($control['access_level_update'] || $control['access_level_browse']))
					echo "<a class='text-dark pl-2' href='$elink'><i class='fa fa-edit fa-lg' title='edit'></i></a>";
				
				if ($row['id'] != 1 && $control['access_level_delete'])
					echo "<a class='text-dark pl-3' onclick='return OKToDeleteAccessLevel();' href='$dlink'><i class='fa fa-times fa-lg' title='delete'></i></a>";

				echo "	
				</td>
				</tr>";	
			}	 
?>		
		</table>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function doLevelCount() {
	global $control;
	
	$sql = "SELECT level FROM access_levels";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();
	echo "<b>$total</b> total access level(s)."; 
}	

function doAccessLevelHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=access";

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=name'>Level</a></th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=username'>Name</a></th>		
	<th class='border border-dark bg-gray-4 p-1'>Action</th>
	</tr>
	</thead>";	
}

function AccessLevelForm($action, $errMsg) {
	global $control;
	
	$values= getAccessLevelValues($action);
	$seeAll=0;
	if ($control['access_level'] == 1)
		$seeAll=1;
	
	$disabled="";
	if (!$control['access_level_update'])
		$disabled = "disabled='disabled'";
	
	if ($action == "edit") {
		$levelLabel="LEVEL:";
		$sql = "SELECT * FROM access_levels WHERE id = $_GET[id]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$row = $stmt->fetch();	
	} else 
		$levelLabel = "AFTER:";		
		
?>

<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo ucname($action); ?> Access Level</h5>
	  <div class="card-body bg-gray-4">	
<?php
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
?>
 
	  
	<form method='post' action='security/addupdateaccesslevel.php'>  
	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input <?php echo $disabled; ?> type="text" class="form-control" name="name" id="name" value='<?php echo $values['name']; ?>'></div>
			</div>	
		</div>		
<?php
	if ($action == "add") {
		echo "
		<div class='form-row'>
			<div class='col-5'>
				<div class='form-group text-right mb-1'><label class='pt-2'>$levelLabel</label></div>
			</div>
			<div class='col-3'>";
				selectAccessLevel("level", $values['level']);
		echo "
			</div>	
		</div>\n";
	} else	
		echo "<input type= 'hidden' name= 'level' value= '$values[level]'>\n";		
?>		

	
	<table class='table mb-2 mt-3'>

	<tr>
	<td class='border border-dark border-right-0 bg-gray-6 text-light text-center pt-4' rowspan='2'>DATABASE ELEMENTS</td>
	<td class='border border-dark border-bottom-0 bg-gray-6 text-light text-center' colspan='3'>PRIVILEGES</td></tr>

	<tr>
	<td class='border border-dark border-right-0 bg-gray-6 text-light text-center'>Add/Update</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-light text-center'>Delete</td>
	<td class='border border-dark bg-gray-6 text-light text-center'>Browse</td></tr>

	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>HOUSEHOLDS
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Profile </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_profile_update', $values['hh_profile_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_profile_delete', $values['hh_profile_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('hh_profile_browse', $values['hh_profile_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Members </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_members_update', $values['hh_members_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_members_delete', $values['hh_members_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('hh_members_browse', $values['hh_members_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Eligibility </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_eligible_update', $values['hh_eligible_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('hh_eligible_browse', $values['hh_eligible_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Shopping History </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_history_update', $values['hh_history_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('hh_history_delete', $values['hh_history_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('hh_history_browse', $values['hh_history_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>PRODUCTS</td>
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Definitions </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('prod_def_update', $values['prod_def_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('prod_def_delete', $values['prod_def_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('prod_def_browse', $values['prod_def_browse']); ?></td>	
	</tr>		
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>In-stock Status </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('instock_update', $values['instock_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('instock_delete', $values['instock_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('instock_browse', $values['instock_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Pantry Setup </td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('prod_setup_update', $values['prod_setup_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('prod_setup_delete', $values['prod_setup_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('prod_setup_browse', $values['prod_setup_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>REPORTS</td>
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Consumption</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('reports_con_browse', $values['reports_con_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Demographic</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('reports_demo_browse', $values['reports_demo_browse']); ?></td>	
	</tr>		
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Charts and Graphs</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('reports_charts_browse', $values['reports_charts_browse']); ?></td>	
	</tr>		
	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>SECURITY</td>
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Users</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('users_update', $values['users_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('users_delete', $values['users_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('users_browse', $values['users_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Access Levels</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('access_level_update', $values['access_level_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('access_level_delete', $values['access_level_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('access_level_browse', $values['access_level_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>TABLES</td>
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Languages</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('languages_update', $values['languages_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('languages_delete', $values['languages_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('languages_browse', $values['languages_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Measures</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('measures_update', $values['measures_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('measures_delete', $values['measures_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('measures_browse', $values['measures_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Containers</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('containers_update', $values['containers_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('containers_delete', $values['containers_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('containers_browse', $values['containers_browse']); ?></td>	
	</tr>		
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Shelters</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('shelters_update', $values['shelters_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('shelters_delete', $values['shelters_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('shelters_browse', $values['shelters_browse']); ?></td>	
	</tr>
	
	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>TOOLS</td>
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Change Pantry</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('changepantry_browse', $values['changepantry_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>User Log</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('userlog_browse', $values['userlog_browse']); ?></td>	
	</tr>	
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Convert</td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark border-right-0 bg-gray-6 text-center p-1'></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('convert_browse', $values['convert_browse']); ?></td>	
	</tr>		

	<tr>
	<td class='border border-dark bg-gray-5' colspan='4'>OTHER</td>
	</tr>
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Pantries</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('pantries_update', $values['pantries_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('pantries_delete', $values['pantries_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('pantries_browse', $values['pantries_browse']); ?></td>	
	</tr>		
	<tr>
	<td class='border border-dark border-right-0 bg-gray-3 pb-2'>Themes</td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('themes_update', $values['themes_update']); ?></td>
	<td class='border border-dark border-right-0 bg-gray-3 text-center p-1'><?php accessCheckbox('themes_delete', $values['themes_delete']); ?></td>
	<td class='border border-dark bg-gray-3 text-center p-1'><?php accessCheckbox('themes_browse', $values['themes_browse']); ?></td>	
	</tr>	

</table>



		
		<div class="form-row">
			<div class="col-lg text-center">* required field</div>	
		</div>		
		
		<div class='text-center mt-3'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='save' <?php echo $disabled; ?>>Save</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
	
		</div>	
<?php			
		if ($action == "edit")
			echo "<input type= 'hidden' name= 'id' value= '$_GET[id]'>\n";
		echo "<input type= 'hidden' name= 'hhID' value= '$control[hhID]'>\n";	
?>		
		</form>		  

	  </div>
	</div>
</div>	
</div>

<?php	
}

function getAccessLevelValues($action) {
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

	if (isset($_GET['errCode'])) {
		
		$arr['level']=$_GET['level'];
		$arr['name']=$_GET['name'];
		$arr['hh_profile_update']=$_GET['hh_profile_update'];
		$arr['hh_profile_delete']=$_GET['hh_profile_delete'];
		$arr['hh_profile_browse']=$_GET['hh_profile_browse'];
		$arr['hh_members_update']=$_GET['hh_members_update'];
		$arr['hh_members_delete']=$_GET['hh_members_delete'];
		$arr['hh_members_browse']=$_GET['hh_members_browse'];
		$arr['hh_eligible_update']=$_GET['hh_eligible_update'];
		$arr['hh_eligible_delete']=$_GET['hh_eligible_delete'];
		$arr['hh_eligible_browse']=$_GET['hh_eligible_browse'];
		$arr['hh_history_update']=$_GET['hh_history_update'];
		$arr['hh_history_delete']=$_GET['hh_history_delete'];
		$arr['hh_history_browse']=$_GET['hh_history_browse'];
		$arr['prod_def_update']=$_GET['prod_def_update'];
		$arr['prod_def_delete']=$_GET['prod_def_delete'];
		$arr['prod_def_browse']=$_GET['prod_def_browse'];
		$arr['instock_update']=$_GET['instock_update'];
		$arr['instock_delete']=$_GET['instock_delete'];
		$arr['instock_browse']=$_GET['instock_browse'];
		$arr['prod_setup_update']=$_GET['prod_setup_update'];
		$arr['prod_setup_delete']=$_GET['prod_setup_delete'];
		$arr['prod_setup_browse']=$_GET['prod_setup_browse'];
		$arr['access_level_update']=$_GET['access_level_update'];
		$arr['access_level_delete']=$_GET['access_level_delete'];
		$arr['access_level_browse']=$_GET['access_level_browse'];
		$arr['users_update']=$_GET['users_update'];
		$arr['users_delete']=$_GET['users_delete'];
		$arr['users_browse']=$_GET['users_browse'];
		$arr['languages_update']=$_GET['languages_update'];
		$arr['languages_delete']=$_GET['languages_delete'];
		$arr['languages_browse']=$_GET['languages_browse'];
		$arr['measures_update']=$_GET['measures_update'];
		$arr['measures_delete']=$_GET['measures_delete'];
		$arr['measures_browse']=$_GET['measures_browse'];
		$arr['containers_update']=$_GET['containers_update'];
		$arr['containers_delete']=$_GET['containers_delete'];
		$arr['containers_browse']=$_GET['containers_browse'];		
		$arr['shelters_update']=$_GET['shelters_update'];
		$arr['shelters_delete']=$_GET['shelters_delete'];
		$arr['shelters_browse']=$_GET['shelters_browse'];
		$arr['changepantry_browse']=$_GET['changepantry_browse'];
		$arr['userlog_browse']=$_GET['userlog_browse'];
		$arr['convert_browse']=$_GET['convert_browse'];
		$arr['pantries_update']=$_GET['pantries_update'];
		$arr['pantries_delete']=$_GET['pantries_delete'];
		$arr['pantries_browse']=$_GET['pantries_browse'];
		$arr['themes_update']=$_GET['themes_update'];
		$arr['themes_delete']=$_GET['themes_delete'];
		$arr['themes_browse']=$_GET['themes_browse'];
		$arr['advanced_update']=$_GET['advanced_update'];
		$arr['advanced_delete']=$_GET['advanced_delete'];
		$arr['advanced_browse']=$_GET['advanced_browse'];
		$arr['reports_con_browse']=$_GET['reports_con_browse'];
		$arr['reports_demo_browse']=$_GET['reports_demo_browse'];
		$arr['reports_charts_browse']=$_GET['reports_charts_browse'];
		$arr['reports_admin_browse']=$_GET['reports_admin_browse'];		
		
	} elseif ($action == "edit") {

		$sql = "SELECT * FROM access_levels WHERE id = $_GET[id]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();	
	}
	
	return $arr;
}	

function selectPosition($name, $level) {
	global $control;

//<!--	<div class="col-3">AFTER:
///		<select class ='form-control bg-gray-1' name='position' >
//		<option  value = 'after'>after</option>		
//		<option  value = 'before'>before</option>
//		</select> 
//	</div>	-->
	if (isset($_GET['id']))
		echo "<input type= 'hidden' name= '$name' value= '$level'>\n";		
	else 
		selectAccessLevel($name, $level);
}	

function accessCheckbox($name, $checked) {
	global $control;

	$disabled="";
	if (!$control['access_level_update'])
		$disabled = "disabled='disabled'";	

	echo "
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='checkbox' id='$name" . "1" . "' name='$name' value='checked' $checked $disabled>
		<label for ='$name" . "1" . "'></label>
	</div>";
}

function rotateLevels() {
	global $control;
	
	$sql = "SELECT * FROM access_levels WHERE level > :cutLevel";
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':cutLevel', $_GET['level'], PDO::PARAM_INT);		
	$stmt->execute();	
	$result = $stmt->fetchAll();			
	foreach($result as $access_levels)		
		moveUp($access_levels['level'], $access_levels['id']);
}

function moveUp($level, $id) {
	global $control;
	
	$up=$level-1;
	$sql = "UPDATE access_levels SET level= :up WHERE id =:id";		
	$stmt= $control['db']->prepare($sql);
	$stmt->bindParam(':up', $up, PDO::PARAM_INT);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);		
	$stmt->execute();	
}	

function deleteAccessLevel() {
	global $control;
	
	$sql = "DELETE FROM access_levels WHERE id = :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);				
	$stmt->execute();
	
	$date = date('Y-m-d');
	$time = date('H:i:s');			
	writeUserLog( $control['db'], $date, $time, 0, "access_levels", $_GET['id'], "DELETE");		
}	
?>