<?php
/**
 * reports/PEP024.php
 * written: 10/14/2020
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
	doReportHeader("PEP024");	
?>

<div class="container p-0">
	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Product Ordering Guidelines - PEP024</h4>
		<!--	<h6 class='text-center'><i>Tracks product consumption for the selected date range and pantry.</i></h6> -->
		</div>
		<div class='card-body bg-gray-2 rounded-0'>
<?php
			bSearchForm();

			if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
//				getReportDates();
//				printReport();
//				printSummary();					
				doReport();
				
				doFootnotes();
			}	
?>
		</div>
	</div>
</div>

<?php bFooter(); ?>
</body>
</html>
	
<?php

function doFootnotes() {


	echo "<div class='mt-4' style='font-size:0.9rem;'>" . DAGGER_FOOTNOTE . " Average Weekly Eligible For = Eligible For / Weeks in Report Period</div>";
	echo "<div class='mt-4' style='font-size:0.9rem;'>" . DAGGER_FOOTNOTE . DAGGER_FOOTNOTE . " Calculated Weekly Demand = Average Weekly Eligible For x .90";
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
//		$arr['pantry']=$arr['hostPanId'];
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
	
	if ( $arr['dateType'] == "last18months" || $arr['dateType'] == "range" ) 
		$arr['period'] = date( 'M j, Y', strtotime("$arr[start]")) . " thru " . date( 'M j, Y', strtotime("$arr[end]"));
	elseif ( $arr['dateType'] == "equalto" ) 
		$arr['period'] = date( 'M j, Y', strtotime("$arr[start]"));
	elseif ( $arr['dateType'] == "after" )
		$arr['period'] = "after " . date( 'M j, Y', strtotime("$arr[start]"));
	elseif ( $arr['dateType'] == "onorafter" )
		$arr['period'] = "on or after " . date( 'M j, Y', strtotime("$arr[start]"));
	elseif ( $arr['dateType'] == "before" )
		$arr['period'] = "before " . date( 'M j, Y', strtotime("$arr[start]"));	
	elseif ( $arr['dateType'] == "onorbefore" )
		$arr['period'] = "on or before " . date( 'M j, Y', strtotime("$arr[start]"));

// 	if ( $arr['dateType'] == "before" || $arr['dateType'] == "onorbefore" )
//		$arr['start'] = consumptionDateLimit("start");	

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

				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Print Report</button>

			</form>";
}

function doReport() {
	global $control;	
	
	$tOked=0;
	$tInstock=0;
	$tUsed=0; 
	$pctInstock=0;
	$tPctInstock=0;	
	

	if ( $control['pantry'] ==	"All" )
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
		
	$totEligibleFor=0;
	$totWeeklyEligibleFor=0;
	$totCalculatedDemand=0;		
	$totVisits=0;		

			
	$numWeeks = round( (strtotime($control['end']) - strtotime($control['start'])) / 604800 ); // 1 week = 604,800 seconds
	$product_col = "name";
	$eligible_for_col = "SUM( consumption.quantity_oked )";
	$weekly_eligible_for_col = "ROUND( SUM( consumption.quantity_oked ) / $numWeeks )";
	$calculated_demand_col = "ROUND( (SUM( consumption.quantity_oked ) / $numWeeks ) * .9 )";

	$sql = "SELECT consumption.product_id, $product_col, $eligible_for_col, $weekly_eligible_for_col, $calculated_demand_col 
			FROM consumption
			INNER JOIN products_nameinfo
			ON products_nameinfo.productID=consumption.product_id
			AND products_nameinfo.languageID=1
			WHERE $dateQ 
			AND $pantryQ
			GROUP BY product_id
			ORDER BY name";			
			
	$stmt = $control['db']->query($sql);			
	$total = $stmt->rowCount();	
?>	
	<table class='table mb-2 mt-3'>
<?php

	doReportHeadings();	
	while ($row = $stmt->fetch()) {	
		
		echo "
		<tr>
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$row[name]</td>
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>" . number_format($row[$eligible_for_col]) . "</td>	
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>" . number_format($row[$weekly_eligible_for_col]) . "</td>				
		<td class='border border-bottom-1 border-dark  bg-gray-3 p-1'>" . number_format($row[$calculated_demand_col]) . "</td>	
		</tr>";	
		
		$totEligibleFor+=$row[$eligible_for_col];
		$totWeeklyEligibleFor+=$row[$weekly_eligible_for_col];
		$totCalculatedDemand+=$row[$calculated_demand_col];		
		$totVisits++;		
	}	

// totals

	echo "
	<tr>
	<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-center'><b>Totals</b></td>
	<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'><b>" . number_format($totEligibleFor) . "</b></td>	
	<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'><b>" . number_format($totWeeklyEligibleFor) . "</b></td>				
	<td class='border border-bottom-1 border-dark bg-gray-3 p-1'><b>" . number_format($totCalculatedDemand) . "</b></td>	
	</tr>";	

?>		
		</table>
<?php
}

function doReportHeadings() {
	global $control;

	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Product</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Eligible For</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'>Average Weekly Eligible For " . DAGGER_FOOTNOTE . "</th>		
	<th class='border border-dark bg-gray-4 p-1'>Calculated Weekly Demand " . DAGGER_FOOTNOTE . DAGGER_FOOTNOTE . "</th>
	</tr>
	</thead>";		
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