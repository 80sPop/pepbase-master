<?php
/**
 * reports/PEP026.php
 * written: 10/12/2020
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
	$isPublic=0;

	require_once('../config.php'); 
	require_once('../header.php'); 	
//	require_once('../common_vars.php');
	require_once('../functions.php');	
	require_once('bFunctions.php');

	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");	
	
	$control=fillControlArray($control, $config, "reports");	
	$control=addReportControl($control);
	$control['isPublic']=$isPublic;	
	
	define ( 'DAGGER_FOOTNOTE', '<sup>&#8224;</sup>' );			

    set_time_limit(900);
//	setGlobals();			// get vars $PantryID and $themeId

//	defineThemeConstants();					// defined in Themes.php	

	$errCode=0;	
	doReportHeader("PEP026");	
?>
<div class="container p-0">


	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Household Directory - PEP026</h4>
		
		<h6 class='text-center'><i>Lists household contact information.</i></h6> 
		</div>
		<div class='card-body bg-gray-2 rounded-0'>


<?php
	bSearchForm();

	if ( (isset($_POST['drawChartBtn']) || isset($_GET['pantry']))   && $control['error'] == "" ) 
		doReport();
//		if ( $control['dateType'] == "last18months" )
//			doFootnotes();				
//	} else
//		doFootnotes();	
	
//	if ( $control['start'] <= "2011-03-24"  && $control['error'] == "" )
//		echo "<div class='text-center' ><i>In-Stock values were not recorded prior to March 24, 2011.</i></div>";
?>

		</div>
	</div>
	
</div>

<?php bFooter(); ?>
</body>
</html>

<?php

/* FUNCTIONS */

function doFootnotes() { 
	global $control;

//	if ( $control['start'] <= "2011-03-24"  && $control['error'] == "" )
//		echo "<div class='text-center' ><i>In-Stock values were not recorded prior to March 24, 2011.</i></div>";
	
	echo "
	<div class='mt-4' style='font-size:0.9rem;'>&#42; Last 18 months includes a two week offset for data entry, so the actual date range is " .
	date("m/d/Y", strtotime($control['start'])) . " - " . date("m/d/Y", strtotime($control['end'])) . "</div>";	
}	


function addReportControl($arr) {

	if ( isset($_GET['field']) )
		$arr['field']=$_GET['field'];
	elseif ( isset($_POST['field']) )		
		$arr['field']=$_POST['field'];
	else	
		$arr['field']="lastname";
	
	if ( isset($_GET['order']) )
		$arr['order']=$_GET['order'];
	elseif ( isset($_POST['order']) )		
		$arr['order']=$_POST['order'];
	else	
		$arr['order']="asc";
	
	if ( isset($_POST['pantryID']) ) 
		$arr['pantryID'] = $_POST['pantryID'];
	elseif ( isset($_GET['pantryID']) )
		$arr['pantryID'] = $_GET['pantryID'];			
	else
		$arr['pantryID'] = 0;	
		
	if ( isset($_GET['themeId']) )
		$arr['themeId'] = $_GET['themeId'];
	elseif ( isset($_POST['themeId']) )		
		$arr['themeId'] = $_POST['themeId'];
	else	
		$arr['themeId']=0;	
	
	if ( isset($_GET['dateType']) )
		$arr['dateType']=$_GET['dateType'];
	elseif ( isset($_POST['dateType']) )		
		$arr['dateType']=$_POST['dateType'];
	else
// report does not use the consumption table, so the two week offset is not necessary		
//		$arr['dateType']="last18months";
		$arr['dateType']="onorafter";

	if ( isset($_GET['pantry']) )
		$arr['pantry']=$_GET['pantry'];
	elseif ( isset($_POST['pantry']) )		
		$arr['pantry']=$_POST['pantry'];
	else	
		$arr['pantry']="All";

	if ( isset($_POST['date1']) ) 	
		$arr['date1']=$_POST['date1'];
	elseif ( isset($_GET['date1']) ) 	
		$arr['date1']=$_GET['date1'];
	else
		$arr['date1']="2019-01-01";
	
	if ( isset($_POST['date2']) )	
		$arr['date2']=$_POST['date2'];
	elseif ( isset($_GET['date2']) ) 	
		$arr['date2']=$_GET['date2'];
	else	
		$arr['date2']="";
	
	if ( isset($_POST['date3']) )	
		$arr['date3']=$_POST['date3'];
	elseif ( isset($_GET['date3']) ) 	
		$arr['date3']=$_GET['date3'];
	else	
		$arr['date3']="";

	if ( isset($_GET['visits']) )
		$arr['visits']=$_GET['visits'];
	elseif ( isset($_POST['visits']) )		
		$arr['visits']=$_POST['visits'];
	else	
		$arr['visits']="0";

	$today=date("Y-m-d");
	$arr['error'] = "";
	$arr['focus'] = "dateType";
	if ( $arr['dateType'] == "range" ) {
		$arr['start']=$arr['date2'];
		$arr['end']=$arr['date3'];
		if (! isValidDate($arr['date2'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date2";
		} elseif (! isValidDate($arr['date3'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date3";
		} elseif ( $arr['date3'] < $arr['date2'] ) {
			$arr['error'] = "rDate";
			$arr['focus'] = "date2";		
		}
		
	} elseif ( $arr['dateType'] == "last18months" ) {
		$arr['end']=date( "Y-m-d", strtotime( "$today - 14 days" ));
		$arr['start']=date( "Y-m-d", strtotime( "$arr[end] - 18 months" ));
	} else {
		$arr['start']=$arr['date1'];
		$arr['end']=$arr['date1'];
		$arr['focus'] = "date1";		
		if (isset($_POST['drawChartBtn']))
			if (! isValidDate($arr['date1'], 'Y-m-d') ) 
				$arr['error'] = "date";

	}
	
// 	if ( $arr['dateType'] == "before" || $arr['dateType'] == "onorbefore" )
//		$arr['start']	= consumptionDateLimit("start");	

	return $arr;
}

//function consumptionDateLimit( $foo )
//{
//global $conn;

//	if ( $foo == "start" ) $order = "ASC"; else $order = "DESC";

//	$sql = "SELECT date FROM consumption WHERE date > '0000-00-00' ORDER BY date $order";
//	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
//	if ($row = mysqli_fetch_assoc($result)) 				
//		return $row['date'];
//	else
//		return "0000-00-00";
//}

function bSearchForm() {
	global $control; 

	$showDate="display:none;";
	$showRange="display:none;";
	if ( $control['dateType'] == "range" ) 
		$showRange="display:block;";
	elseif ( $control['dateType'] != "last18months" )
		$showDate="display:block;";

	if ( $control['error'] == "date" )
		echo "
			<div class='alert alert-danger' role='alert'>
			  Please enter a valid date.
			</div>";
			
	elseif ( $control['error'] == "rDate" )
		echo "
			<div class='alert alert-danger' role='alert'>
			  Start date must occur before end date.
			</div>";			

		echo "
			<form name='searchForm' method='post' action='$_SERVER[PHP_SELF]' />

				<div class='form-group'>
					<label>Last Active Date</label>";
					
// Omit last 18 months date type, since last active date is set immediately in household table when a shopping list is printed.					
					bSelectDateType( "dateType", "$control[dateType]", 0 ); 
		echo "
				</div>

				<div class='form-group' style='$showDate' id='hide-date-1'>

					<div class='form-group'>
					<input type='date' id='date1' name='date1' value='$control[date1]' class='form-control bg-gray-1' >
					</div>

				</div>

				<div class='form-group' style='$showRange;' id='hide-range-1'>

					<div class='form-inline'>

						<div class='form-group' style='width:300px;'>
						<label class='pr-3'>Start</label>
						<input type='date' id='date2' name='date2' value='$control[date2]' class='form-control bg-gray-1'>
						</div>

						<div class='form-group' style='width:300px;'>
						<label class='pr-3'>End</label>
						<input type='date' id='date3' name='date3' value='$control[date3]' class='form-control bg-gray-1'>
						</div>
					</div>

				</div>

				<div class='form-group'>
					<label>Pantry of Registration</label>";
//					bSelectPantry( "pantry", "$control[pantry]" );
					cSelectPantry( "pantry", "$control[pantry]" );					
		echo "     
				</div>

				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Search</button>

			</form>";
}

function doReport() {
	global $control; 
		
//	$found=0;	
//	$found=countVisitsPerHH($found);
	showHouseholds();  
}	

function showHouseholds() {
	global $control;

	if ( $control['pantry'] ==	"All" )
		$pantryQ =1;
	else
		$pantryQ = "pantry_id = $control[pantry]";		
	
	if ( $control['dateType'] == "last18months" || $control['dateType'] == "range" )
		$dateQ = "lastactivedate >= '$control[start]' AND lastactivedate <= '$control[end]'";

	elseif ( $control['dateType'] == "equalto" )
		$dateQ = "lastactivedate = '$control[start]'";

 	elseif ( $control['dateType'] == "after" )
		$dateQ = "lastactivedate > '$control[start]'";

 	elseif ( $control['dateType'] == "onorafter" )
		$dateQ = "lastactivedate >= '$control[start]'";

 	elseif ( $control['dateType'] == "before" )
		$dateQ = "lastactivedate < '$control[start]'";

 	elseif ( $control['dateType'] == "onorbefore" )
		$dateQ = "lastactivedate <= '$control[start]'";	

	$found=0;
//	$sql = "SELECT COUNT(*) FROM household WHERE $pantryQ AND $dateQ";
//	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
	
	$sql = "SELECT * FROM household WHERE $pantryQ AND $dateQ ORDER BY $control[field] $control[order]";
	$stmt = $control['db']->query($sql);			
	$found = $stmt->rowCount();		
//	if ($count = mysqli_fetch_assoc($result)) 
//		$found=$count['COUNT(*)'];
//	if (empty($found))
//		$found=0;
		
	doHeadings($found);	
	while ($household = $stmt->fetch()) {		

//	$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
//	while ($household = mysqli_fetch_assoc($result)) {
//		$pName=stripslashes(ucname($household['firstname'])) . " " . stripslashes(ucname($household['lastname']));		
		$active=date("m-d-Y", strtotime($household['lastactivedate']));				
	
//		$sql3 = "SELECT COUNT(*) FROM members WHERE householdID = $report_work[RW_household_id] AND in_household = 'Yes'";
//		$result3 = mysqli_query( $conn, $sql3 ) or die(mysqli_error( $conn ));
//		if ($members = mysqli_fetch_assoc($result3))
//			$membersIn=$members['COUNT(*)'];	

		echo " 

		<tr>
		<td class='border border-dark border-right-0 border-top-0 p-1'>" . stripslashes(ucname($household['firstname'])) . "</td>
		<td class='border border-dark border-right-0 border-top-0 p-1'>" . stripslashes(ucname($household['lastname'])) . "</td>		
		<td class='border border-dark border-right-0 border-top-0 p-1'>$household[id]</td>
		<td class='border border-dark border-right-0 border-top-0 p-1'>$household[phone1]</td>
		<td class='border border-dark border-right-0 border-top-0 p-1'>$household[phone2]</td>	
		<td class='border border-dark border-right-0 border-top-0 p-1'>$household[email]</td>		
		<td class='border border-dark border-top-0 p-1'>$active</td></tr>";
	}	
	echo "</table>";
}

function doHeadings($found) {
	global $control;

//	$link = $_SERVER['PHP_SELF'] . "?pantryID=$control[pantryID]&themeId=$control[themeId]&dateType=$control[dateType]&pantry=$control[pantry]";
	$link = $_SERVER['PHP_SELF'] . "?dateType=$control[dateType]&pantry=$control[pantry]";
	if ( $control['date1'] != "")
		$link .= "&date1=$control[date1]";
	if ( $control['date2'] != "")
		$link .= "&date2=$control[date2]";	
	if ( $control['date3'] != "")
		$link .= "&date3=$control[date3]";
	if ($control['order'] == "asc")
		$link .= "&order=desc";
	else
		$link .= "&order=asc";	
	
	$fnCarrot="";
	$lnCarrot="";	
	$idCarrot="";		
	$p1Carrot="";	
	$p2Carrot="";		
	$emCarrot="";	
	$laCarrot="";	
	
	if ( $control['order'] == "asc" ) {	
		if ($control['field'] == "firstname")
			$fnCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";
		elseif ($control['field'] == "lastname")
			$lnCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "id")
			$idCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
		elseif ($control['field'] == "phone1")
			$p1Carrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "phone2")
			$p2Carrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		
		elseif ($control['field'] == "email")
			$emCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";	
		elseif ($control['field'] == "lastactivedate")
			$laCarrot="<i class='fa fa-sort-up pl-2 align-middle'></i>";		

	} elseif ( $control['order'] == "desc" )	
		if ($control['field'] == "firstname")
			$fnCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";
		elseif ($control['field'] == "lastname")
			$lnCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "id")
			$idCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
		elseif ($control['field'] == "phone1")
			$p1Carrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "phone2")
			$p2Carrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
		elseif ($control['field'] == "email")
			$emCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";	
		elseif ($control['field'] == "lastactivedate")
			$laCarrot="<i class='fa fa-sort-down pl-2 align-middle'></i>";		
			
	echo " 
	<div class='mt-4 mb-2 p-0'>search found <b>" . number_format($found) . "</b> household(s)</div>	

	<table class='table'>	
		<tr>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=firstname'>First Name</a>$fnCarrot</th>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=lastname'>Last Name</a>$lnCarrot</th>		
		<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=id'>Household ID</a>$idCarrot</th>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=phone1'>Phone 1</a>$p1Carrot</th>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=phone2'>Phone 2</a>$p2Carrot</th>	
		<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=email'>Email</a>$emCarrot</th>		
		<th class='border border-dark bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=lastactivedate'>Last Active Date</a>$laCarrot</th>	
		</tr>";	
}


?>
<!-- Place any per-page style here -->


<!-- Place any per-page javascript here -->

<script>
		
	function onSelectDate() {

		if ( document.getElementById("dateType").value == "last18months" ) {
			document.getElementById("hide-date-1").style.display="none";
			document.getElementById("hide-range-1").style.display="none";
		} else if ( document.getElementById("dateType").value == "range" ) {
			document.getElementById("hide-date-1").style.display="none";
			document.getElementById("hide-range-1").style.display="block";
		} else {
			document.getElementById("hide-date-1").style.display="block";
			document.getElementById("hide-range-1").style.display="none";
		}
	
	}

</script>