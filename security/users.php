<?php
/**
 * security/users.php
 * written: 8/24/2020
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

function listUsers($errMsg) {
	global $control;
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=users&add=1";
	
	$pantryQ=1;
	if ($control['access_level'] != 1)
		$pantryQ = "pantry_id = $control[users_pantry_id] AND access_level > 1";
	
	$userQ = 1;
	if (!$control['users_browse'])	
		$userQ = "users.id=$control[users_id]";		
	
	if ($control['field'] == "name")
		$field = "lastname, firstname";
	elseif ($control['field'] == "email")
		$field = "users.email";
	elseif ($control['field'] == "access_level")
		$field = "access_levels.name";	
	elseif ($control['field'] == "pantry")
		$field = "pantries.name";	
	else
		$field = $control['field'];
	
	$sql = "SELECT 	users.id AS users_id,
					firstname,
					lastname,
					username,
					pantry_id,
					access_levels.name AS al_name,
					pantries.name AS pantries_name,
					users.email AS users_email,
					users.is_active AS users_active,
					last_signin
			FROM users
			LEFT JOIN pantries ON pantry_id = pantries.id
			LEFT JOIN access_levels ON access_level = access_levels.level
			WHERE $pantryQ
			AND $userQ
			ORDER BY $field $control[order]";	
	
//	$sql = "SELECT * FROM users ORDER BY $field $control[order]";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 
<?php
	if (isset($_GET['passwordok'])) {
		echo "<div>&nbsp;</div>";
		displayAlert2("success", "Password changed.");
	} elseif (isset($_GET['addok'])) 
		newUserMsg();
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert2("danger", $errMsg[$err]);		
	}			

?>		
		<div class='row p-3'>
<?php
		if ($control['users_update'])	
			echo "<div class='col-sm' style='color:#841E14;'><i class='fa fa-plus pr-2 p-1'></i><a style='color:inherit;' href='$link' >Add User</a></div>\n";
		if ($control['users_browse'])
			echo "<div class='col-sm'><i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i> - Inactive</div>\n";
?>		
			<div class='col-sm text-right'><?php doUsersCount(); ?></div>			
		</div>		
		<table class='table mb-2'>
<?php
			doUsersHeadings();			
			foreach($result as $row) {	
				$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=users&id=$row[users_id]";
				$dlink= $link . "&delete=1";				
				
				$inactive="";
				if (!$row['users_active'])
//					$inactive= "<span class='alert alert-warning ml-2 p-1 border border-dark' role='alert'>INACTIVE</span>";
					$inactive= "<i class='fa fa-exclamation-triangle fa-lg pr-1 text-warning'></i>";
				$pantry="All";
				if ($row['pantry_id'] !=0)
					$pantry=$row['pantries_name'];

				$date="";
				if (isValidDate($row['last_signin'], 'Y-m-d H:i:s'))
					$date=date('m-d-Y', strtotime($row['last_signin']));
				
				echo "
				<tr>
				<td class='border border-dark bg-gray-3 p-1'>$row[firstname] $row[lastname] $inactive</td>
				<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[username]</td>		
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[al_name]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[users_email]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$pantry</td>	
				<td class='border border-dark bg-gray-3 p-1'>$date</td>
				<td class='border border-dark bg-gray-3 p-1'>
				<a class='text-dark pl-2' href='$link'><i class='fa fa-edit fa-lg' title='edit'></i></a>";
				
				// current user may NOT delete their own user account
				if ($control['users_id'] != $row['users_id'])
					echo "<a class='text-dark pl-3' onclick='return OKToDeleteUser();' href='$dlink'><i class='fa fa-times fa-lg' title='delete'></i></a>";

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

function newUserMsg() {
	global $control;
	
	$sql = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetch();	
	$msg="<b>Account created.</b> An email was sent to <u>$users[email]</u> with password set up instructions. If the
	new user didn't receive an email, have them check their spam or junk email folder.";
	echo "<div>&nbsp;</div>";
	displayAlert2("success", $msg);	
}	

function doUsersCount() {
	global $control;
	
	if ($control['access_level'] == 1) // Administrator
		$sql = "SELECT is_active FROM users";
	elseif ($control['users_browse'])	
		$sql = "SELECT is_active FROM users WHERE pantry_id=$control[users_pantry_id] AND access_level > 1";	
	else
		$sql = "SELECT is_active FROM users WHERE id=$control[users_id]";	
	
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$tUsers = $stmt->rowCount();
	$tActive = 0;	
	$result = $stmt->fetchAll();	
	foreach($result as $row) 
		if ($row['is_active']) $tActive++;
			
	echo "<b>$tActive</b> of $tUsers user(s) are active."; 
}	

function doUsersHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=users";
	if ($control['order'] == "asc")
		$link .= "&order=desc";
	else
		$link .= "&order=asc";	

	$nCarrot="";
	$usCarrot="";	
	$alCarrot="";		
	$emCarrot="";	
	$paCarrot="";		
	$laCarrot="";
	
	if ( $control['order'] == "asc" ) 
		$direction="fa-sort-up";
	else
		$direction="fa-sort-down";		

	if ($control['field'] == "name")
		$nCarrot="<i class='fa $direction pl-2 align-middle'></i>";
	elseif ($control['field'] == "username")
		$usCarrot="<i class='fa $direction pl-2 align-middle'></i>";		
	elseif ($control['field'] == "access_level")
		$alCarrot="<i class='fa $direction pl-2 align-middle'></i>";	
	elseif ($control['field'] == "email")
		$emCarrot="<i class='fa $direction pl-2 align-middle'></i>";
	elseif ($control['field'] == "pantry")
		$paCarrot="<i class='fa $direction pl-2 align-middle'></i>";	
	elseif ($control['field'] == "last_signin")
		$laCarrot="<i class='fa $direction pl-2 align-middle'></i>";			

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=name'>Name</a>$nCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=username'>Username</a>$usCarrot</th>		
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=access_level'>Access Level</a>$alCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=email'>Email</a>$emCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=pantry'>Pantry</a>$paCarrot</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=last_signin'>Last Access</a>$laCarrot</th>		
	<th class='border border-dark bg-gray-4 p-1'>Action</th>
	</tr>
	</thead>";	
}

function userForm($action, $errMsg) {
	global $control;
	
	$values= getUsersValues($action);
	$seeAll=0;
	if ($control['access_level'] == 1)
		$seeAll=1;
?>

<div class="container-fluid bg-gray-2 m-0">
<div class="container p-3">
	<div class="card border border-dark">
	  <h5 class="card-header bg-gray-5 text-center"><?php echo ucname($action); ?> User</h5>
	  <div class="card-body bg-gray-4">	
<?php
    if (isset($_GET['errCode'])) {
		$err=$_GET['errCode'];
		displayAlert($errMsg[$err]);
	}	
?>

	  
	  
	<form method='post' action='security/addupdateuser.php'>  	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* FIRST NAME:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="text" class="form-control" name="firstname" id="firstname" value='<?php echo htmlentities($values['firstname'], ENT_QUOTES); ?>' ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* LAST NAME:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="text" class="form-control" name="lastname" id="lastname"  value='<?php echo htmlentities($values['lastname'], ENT_QUOTES); ?>'></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* USERNAME:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="text" class="form-control" name="username" id="username"  value='<?php echo $values['username']; ?>'></div>
			</div>	
		</div>

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* EMAIL:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="text" class="form-control" name="email" id="email"  value='<?php echo $values['email']; ?>'></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PHONE:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="text" class="form-control" name="phone" id="phone"  value='<?php echo $values['phone']; ?>'></div>
			</div>	
		</div>			
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* PANTRY:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><?php selectPantry( "pantry_id", $values['pantry_id'], $seeAll ); ?></div>
			</div>	
		</div>		

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>* ACCESS LEVEL:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><?php selectAccessLevel('access_level', $values['access_level']); ?></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>&nbsp;</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><?php isActiveSwitch('is_active', $values['is_active']); ?></div>
			</div>	
		</div>	

<?php 
		if ($action == "edit")
			doPasswords($values);	
?>

		
		<div class="form-row">
			<div class="col-lg text-center">* required field</div>	
		</div>		
		
		<div class='text-center mt-3'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='save'>Save</button>	
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

function getUsersValues($action) {
	global $control;
	
	$arr = [	
		'firstname'			=> "",	
		'lastname'			=> "",	
		'username'			=> "",		
		'pantry_id'			=> $control['users_pantry_id'],
		'access_level'		=> "",	
		'email'				=> "",
		'phone'				=> "",
		'is_active'			=> 1
	]; 
	
	if ( $control['access_level'] == 1 )
		$arr['pantry_id']=0; // ---- all ----
	
	if (isset($_GET['errCode'])) {
		
		$arr['firstname']		= $_GET['firstname'];		
		$arr['lastname']		= $_GET['lastname'];
		$arr['username']		= $_GET['username'];
		$arr['pantry_id']		= $_GET['pantry_id'];	
		$arr['access_level']	= $_GET['access_level'];
		$arr['email']			= $_GET['email'];	
		$arr['phone']			= $_GET['phone'];	
		$arr['is_active']		= $_GET['is_active'];

	} elseif ($action == "edit") {

		$sql = "SELECT * FROM users WHERE id = $_GET[id]";
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
		$arr = $stmt->fetch();	
	}
	
	return $arr;
}	


function selectAccessLevel($name, $value) {
	global $control;

	$access="";	
	
/***** when select inputs are disabled, their values will be unset, and will be updated incorrectly in addupdateusers.php *****/	
	if ( !$control['users_update'] ) 
		$access="disabled=disabled";

    echo "<select class ='form-control bg-gray-1' name='$name' $access>\n";

/*******************************************************************************
 * A user can only assign an access level equal to or below their own 
 *
 *	level	name
 *  -----	------------
 *  1		Administrator
 *	2		Coordinator
 *  3		Host
 *
 *******************************************************************************/
    $sql = "SELECT * FROM access_levels WHERE level >= $control[access_level] ORDER BY name";
//    $sql = "SELECT * FROM access_levels ORDER BY name";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();	
	foreach ($result as $access_levels) {	
		if ($value == $access_levels['level']) $selected ="selected";
		else $selected =""; 	
		echo "<option $selected value = $access_levels[level] $access_levels[level]>$access_levels[name]</option>\n";
    } 

    echo "</select>\n";
}

function isActiveSwitch($name, $value) {
	
	$yc ="";
	$value=strval($value);
		
	if ($value == "on" || $value == "1")
		$yc = "checked='checked'";
	
	echo "<input type='checkbox' name='$name' data-toggle='switch' data-on-color='primary' data-on-text='ACTIVE' data-off-color='default' data-off-text='INACTIVE' $yc>\n";	
}

function doPasswords($values) {
	global $control;
	
/* $values are table values of the user being edited
/* $control['access_level_id] is the access level of logged in user
/* $control['user_id'] is the user id of logged in user
	
/*************** Password Rules ************
 *
 * 1. Only Administrators can change other user's passwords
 *
 * 1. If user is an Administrator:
 *		- they may NOT change another Administrator's password
 *
 *******************************************/
 
	$labelP1= "CURRENT PASSWORD:";
	if ( $control['access_level'] == 1 )
		if ($control['users_id'] != $_GET['id'])
			$labelP1= "ADMINISTRATOR PASSWORD:";
?>	
		<div class="form-row mt-4">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>&nbsp;</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1">Change Password (Optional)</div>
			</div>	
		</div>	
		
		<div class="form-row mt-0 pt-0">
			<div class="col-5 mt-0 pt-0">
				<div class="form-group text-right mb-1"><label class='pt-2'><?php echo $labelP1; ?></label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="password" class="form-control" name="password" id="password" value=''></div>
			</div>	
		</div>				
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>NEW PASSWORD:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="password" class="form-control" name="new_password" id="new_password" value=''></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>CONFIRM NEW PASSWORD:</label></div>
			</div>	
			<div class="col-4">
				<div class="form-group mb-1"><input type="password" class="form-control" name="confirm_password" id="confirm_password" value=''></div>
			</div>	
		</div>		
	
<?php	
}	


function genderRadio($name, $value) {
	
	$bc ="";
	$mc ="";
	$fc ="";
	
	if (ucname($value) == "Both")
		$bc = "checked='checked'";	
	elseif (ucname($value) == "Male")
		$mc = "checked='checked'";
	else
		$fc = "checked='checked'";		

	echo "
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "1" . "' name='$name' value='Both' $bc>
		<label for ='$name" . "1" . "'>Both</label>
	</div>
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "2" . "' name='$name' value='Male' $mc>
		<label for ='$name" . "2" . "'>Male</label>
	</div>	
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "3" . "' name='$name' value='Female' $fc>
		<label for ='$name" . "3" . "'>Female</label>
	</div>\n";	
}

function portionRadio($name, $value) {
	
	$pc ="";
	$sc ="";
	
	if (ucname($value) == "Yes")
		$pc = "checked='checked'";	
	else
		$sc = "checked='checked'";		

	echo "
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "1" . "' name='$name' value='Yes' $pc onclick='document.getElementById(" . '"duration_2"' . ").focus()'>
		<label for ='$name" . "1" . "'>Personal</label>
	</div>
	<div class='icheck-ron-burgundy icheck-inline'>
		<input type='radio' id='$name" . "2" . "' name='$name' value='No' $sc onclick='document.getElementById(" . '"duration_2"' . ").focus()'>
		<label for ='$name" . "2" . "'>Shared</label>
	</div>\n";	
}

//function yesNoSwitch($name, $value) {
//	
//	$yc ="";
//	$value=ucname(strval($value));
//		
//	if ($value == "Yes" || $value == "1")
//		$yc = "checked='checked'";
//	
//	echo "<input type='checkbox' name='$name' data-toggle='switch' data-on-color='primary' data-on-text='YES' data-off-color='default' data-off-text='NO' $yc>\n";	
//}

function deleteUser() {
	global $control;
	
	$sql = "DELETE FROM users WHERE id = :id";
	$stmt= $control['db']->prepare($sql);	
	$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);				
	$stmt->execute();
}	

?>