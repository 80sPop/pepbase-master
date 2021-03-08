<?php
/**
 * reports/PEP001.php
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

	$errCode=0;	
	doReportHeader("PEP001");	
?>
<div class="container p-0">


	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Household Consumption by Number of Visits - PEP001</h4>
		
		<h6 class='text-center'><i>Tracks detailed information about households by the number of pantry visits. Use PEP007 for the same information without details.</i></h6> 
		</div>
		<div class='card-body bg-gray-2 rounded-0'>


<?php
	bSearchForm();

	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
		doReport();
		if ( $control['dateType'] == "last18months" )
			doFootnotes();				
	} else
		doFootnotes();	

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

	if ( isset($_GET['dateType']) )
		$arr['dateType']=$_GET['dateType'];
	elseif ( isset($_POST['dateType']) )		
		$arr['dateType']=$_POST['dateType'];
	else	
		$arr['dateType']="last18months";

	if ( isset($_GET['pantry']) )
		$arr['pantry']=$_GET['pantry'];
	elseif ( isset($_POST['pantry']) )		
		$arr['pantry']=$_POST['pantry'];
	else	
//		$arr['pantry']="All";
		$arr['pantry']=0;	

	if ( isset($_POST['date1']) ) {	
		$arr['date1']=$_POST['date1'];

	}
	else
		$arr['date1']="";
	if ( isset($_POST['date2']) )	
		$arr['date2']=$_POST['date2'];
	else
		$arr['date2']="";
	if ( isset($_POST['date3']) )	
		$arr['date3']=$_POST['date3'];
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
		if (! isValidDate($arr['date1'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date1";
		}
	}
	
// 	if ( $arr['dateType'] == "before" || $arr['dateType'] == "onorbefore" )
//		$arr['start']	= consumptionDateLimit("start");	

	return $arr;
}

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
					<label>Date</label>";
					bSelectDateType( "dateType", "$control[dateType]", 1 ); 
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
					<label>Pantry</label>";
//					bSelectPantry( "pantry", "$control[pantry]" );
					cSelectPantry( "pantry", "$control[pantry]" );					
		echo "     
				</div>

				<div class='form-group'>
					<label>Number of Visits</label>
					<input type='text' id='visits' name='visits' value='$control[visits]' class='form-control bg-gray-1'>					
				</div>

				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Search</button>

			</form>";
}

function doReport() {
	global $control; 
		
	countVisitsPerHH();
	showHouseholds();  
}	

function countVisitsPerHH() {
	global $control;
		
    $numVisits =0;		
    $firstRow = 1;
	
//	if ( $control['pantry'] ==	"All" )
	if ( $control['pantry'] ==	0 )	
		$pantryQ =1;
	else
		$pantryQ = "pantry_id = $control[pantry]";		
	
	if ( $control['dateType'] == "last18months" || $control['dateType'] == "range" )
		$dateQ = "date >= '$control[start]' AND date <= '$control[end]'";

	elseif ( $control['dateType'] == "equalto" )
		$dateQ = "date = '$control[start]'";

 	elseif ( $control['dateType'] == "after" )
		$dateQ = "date > '$control[start]'";

 	elseif ( $control['dateType'] == "onorafter" )
		$dateQ = "date >= '$control[start]'";

 	elseif ( $control['dateType'] == "before" )
		$dateQ = "date < '$control[start]'";

 	elseif ( $control['dateType'] == "onorbefore" )
		$dateQ = "date <= '$control[start]'";	
	
	$sql = "DELETE FROM report_work";		
	$stmt= $control['db']->prepare($sql);
	$stmt->execute();		

	$sql = "SELECT * FROM consumption
			WHERE household_id > 0
			AND $dateQ
			AND $pantryQ
			GROUP BY household_id, date, time
			ORDER BY household_id";

	$stmt = $control['db']->query($sql);			
	$total = $stmt->rowCount();	
	while ($row = $stmt->fetch()) {	

		if ( $firstRow ) {
			$household_id = $row['household_id'];
			$numVisits++;
			$firstRow=0;
		} elseif ( $household_id < $row['household_id'] ) {	
			if ( $numVisits == $control['visits'] )
				$found=InsertWorkRow($household_id, $numVisits );		
			$household_id = $row['household_id'];		
			$numVisits=1;
		} else
			$numVisits++;
    } 

}

function InsertWorkRow($id, $visits) {
	global $control;

    $sql = "INSERT INTO report_work (RW_household_id, RW_num_visits)
            VALUES ('$id', '$visits')";  
	$stmt= $control['db']->prepare($sql);
	$stmt->execute();		
}

function showHouseholds() {
	global $control;

    $sql = "SELECT * FROM report_work";
	$stmt = $control['db']->query($sql);			
	$found = $stmt->rowCount();	
	doHeadings($found);		
	while ($report_work = $stmt->fetch()) {		

		$sql2 = "SELECT * FROM household WHERE id = $report_work[RW_household_id]";
		$stmt2 = $control['db']->query($sql2);			
		$total2 = $stmt2->rowCount();
		if ($total2 > 0) {
			$household = $stmt2->fetch();
			$pName=stripslashes(ucname($household['firstname'])) . " " . stripslashes(ucname($household['lastname']));		
			$active=date("m-d-Y", strtotime($household['lastactivedate']));				
		
			$sql3 = "SELECT COUNT(*) FROM members WHERE householdID = $report_work[RW_household_id] AND in_household = 'Yes'";
			$stmt3 = $control['db']->query($sql3);			
			$total3 = $stmt3->rowCount();
			if ($total3 > 0) {
				$members = $stmt3->fetch();	
				$membersIn=$members['COUNT(*)'];	
				echo " 
				<div class='row'>
					<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>$pName</div>
					<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>$report_work[RW_household_id]</div>
					<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>$membersIn</div>	
					<div class='col-sm border border-dark border-top-0 p-1'>$active</div>		
				</div>";
			}	
		}	
	}	
}

function doHeadings($found) {

	echo " 
	<div class='mt-4 mb-2 p-0'>search found <b>$found</b> household(s)</div>	
	
	<div class='row'>
		<div class='col-sm border border-dark border-right-0 bg-gray-4 p-1'>Primary Shopper</div>
		<div class='col-sm border border-dark border-right-0 bg-gray-4 p-1'>Household ID</div>
		<div class='col-sm border border-dark border-right-0 bg-gray-4 p-1'>Members</div>	
		<div class='col-sm border border-dark bg-gray-4 p-1'>Last Visit</div>		
	</div>";
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