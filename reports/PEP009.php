<?php
/**
 * reports/PEP009.php
 * written: 10/13/2020
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
	$isPublic=1;

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
	doReportHeader("PEP009");	
?>
<div class="container p-0">


	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Households with Age Difference Greater Than 30 years or Less Than 15 years - PEP009</h4>
		
<!--		<h6 class='text-center'><i>Study based on households who have at least one minor and one adult</i></h6>  -->
		</div>
		<div class='card-body bg-gray-2 rounded-0'>


<?php
	bSearchForm();
	
	$slices="";
	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
		doReport();
		
		echo "<div id='pie_div' style='margin:0;padding:0;'></div>";
		
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

	if ( isset($_GET['interval']) )
		$arr['interval']=$_GET['interval'];
	elseif ( isset($_POST['interval']) )		
		$arr['interval']=$_POST['interval'];
	else	
		$arr['interval']="annual";

// date format for hAxis and tooltip
	if ( $arr['interval'] == "annual" ) {
		$arr['hAxis'] = "YYYY";		
		$arr['tooltip'] = "YYYY";			
	} elseif ( $arr['interval'] == "quarterly" ) {
		$arr['hAxis'] = "MMM YYYY";	
		$arr['tooltip'] = "MMM YYYY";			
	} else {
		$arr['hAxis'] = "MMM YY";				
 		$arr['tooltip'] = "MMM YYYY";	
	}
	
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
					<label>Date of Last Visit</label>";
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
					<label>Pantry of Registration</label>";
//					bSelectPantry( "pantry", "$control[pantry]" );
					cSelectPantry( "pantry", "$control[pantry]" );					
		echo "     
				</div>

				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Print Report</button>

			</form>";
}

function doReport() {
	global $control;
	
    $validHouseholds=0;
    $TodayYYYYMMDD = date('YYYY-MM-DD');

    $adultYear = date('Y') - 18;
//    $adultLimit = $adultYear . "-" . date( "m-d" );	
	
	$control['plus30']=0;
	$control['less15']=0;
	$control['numHouseholds']=0;	
	$control['adultLimit']=$adultYear . "-" . date( "m-d" );		

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

	$isFirstRow=1;		
	$sql = "SELECT householdID, dob, lastactivedate FROM members
			INNER JOIN household ON household.id = members.householdID
			WHERE dob > '0000-00-00'
			AND $dateQ
			AND $pantryQ
			AND householdID > 0
			ORDER BY householdID, dob";			
			
	$stmt = $control['db']->query($sql);			
	$total = $stmt->rowCount();	
	
	if (!$control['isPublic'])
		echo "<table class='table mb-2 mt-3'>\n";
	
	doReportHeadings();
	while ($row = $stmt->fetch()) {					
		if ( $isFirstRow ) {
			$isFirstRow = 0;
			$oldestChildDOB = '9999-12-31';
			$youngestAdultDOB = $row['dob'];
			$control['numHouseholds']++;
			$householdID = $row['householdID'];
			
		} elseif ($householdID < $row['householdID']) {
			findAgeGap($householdID,$oldestChildDOB, $youngestAdultDOB);
			$oldestChildDOB = '9999-12-31';
			$youngestAdultDOB = $row['dob'];
			$control['numHouseholds']++;			
			$householdID = $row['householdID'];
		} elseif ( $row['dob'] <= $control['adultLimit'] )
			$youngestAdultDOB = $row['dob'];
		elseif ( $oldestChildDOB > $row['dob'] )
			$oldestChildDOB = $row['dob'];

	}
	if (!$control['isPublic'])
		echo "</table>\n";	
}


function doReportHeadings() {
	global $control;
	
// don't include table for public reports
	if (!$control['isPublic'])
		echo "
		<thead>
		<tr>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'>Household ID</th>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'>Age of Oldest Minor</th>
		<th class='border border-dark border-right-0 bg-gray-4 p-1'>Age of Youngest Adult</th>	
		<th class='border border-dark border-right-0 bg-gray-4 p-1'>-15 year gap</th>		
		<th class='border border-dark bg-gray-4 p-1'>+30 year gap</th>
		</tr>
		</thead>";		
}	

function findAgeGap($householdID,$oldestChildDOB, $youngestAdultDOB) {
	global $control;
	
    if ( $oldestChildDOB < '9999-12-31' && $youngestAdultDOB <= $control['adultLimit'] ) {	
		$gap = date('Y-m-d', strtotime("$oldestChildDOB - 30 years"));	
		if ($gap > $youngestAdultDOB) {	
			PrintRow('plus30', $householdID, $oldestChildDOB, $youngestAdultDOB);	
		} else {
			$gap = date('Y-m-d', strtotime("$oldestChildDOB - 15 years"));				
			if ($gap < $youngestAdultDOB) {	
                PrintRow('less15', $householdID, $oldestChildDOB, $youngestAdultDOB);	
			}
		}
	}	
}	


function printRow($gap, $householdID, $oldestChildDOB, $youngestAdultDOB) {
	global $control;

    $ageOldestChild = CalcAge( $oldestChildDOB, 0 );
    $ageYoungestAdult = CalcAge( $youngestAdultDOB, 0 );
	$plus30="";
	$less15="";	
	
	if ($gap == "plus30") {
		$control['plus30']++;
		$plus30="x";		
	} else {
		$control['less15']++;	
		$less15="x";
	}	
	
// don't include table for public reports
	if (!$control['isPublic'])
		echo "
		<tr>
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-center'>$householdID</td>
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$ageOldestChild</td>	
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$ageYoungestAdult</td>				
		<td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>$less15</td>	
		<td class='border border-bottom-1 border-dark bg-gray-3 p-1'>$plus30</td>		
		</tr>";			

}

function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $Plus30, $Less15, $NumHouseholds,$validHouseholds,$reportBeginDate, $reportEndDate;

// get values for Summay

	if ( $reportBeginDate == $reportEndDate )
		$reportPeriod = date('M j, Y', strtotime($reportBeginDate));
	else
		$reportPeriod = date('M j, Y', strtotime($reportBeginDate)) . " thru " . date('M j, Y', strtotime($reportEndDate));
	$title="";	
    if ( isset($_POST['pantry_id']) ) 
		if ( $_POST['pantry_id'] == "All" )
			$title="All";
		else {	
			$sql2 = "SELECT * FROM pantries WHERE pantryID = $_POST[pantry_id]";
			$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
			if ($row2 = mysqli_fetch_assoc($result2)) 
				$title = $row2['name'];	
		} elseif ( isset($_GET['pantry_id']) ) 
			if ( $_GET['pantry_id'] == "All" )
				$title="All";
			else {	
				$sql2 = "SELECT * FROM pantries WHERE pantryID = $_GET[pantry_id]";
				$result2 = mysqli_query( $conn, $sql2 ) or die(mysqli_error( $conn ));
				if ($row2 = mysqli_fetch_assoc($result2)) 
					$title = $row2['name'];	
			}	
	$less15Perc = 100 *	( $Less15 /	$validHouseholds );
	$plus30Perc = 100 * ( $Plus30 / $validHouseholds );
	
// print Summary 			

    echo "<table border='0' cellspacing='0' class='reportSummary34'>\n";	
    echo "<tr><td id='rSumTitle' colspan='2'><i>Summary</i></td></tr>\n";
    echo "<tr><td>Report Period</td>\n"; 
    echo "<td>$reportPeriod</td></tr>\n";
    echo "<tr><td>Pantry</td>\n"; 
    echo "<td>$title</td></tr>\n";	
    echo "<tr><td>Active households</td>\n";	
    echo "<td>" . number_format( $validHouseholds ) . "</td></tr>\n";	
    echo "<tr><td>-15 year gap</td>\n";	
    echo "<td>" . number_format( $Less15 ) . " (" . number_format( $less15Perc ) . "%)</td></tr>\n";
    echo "<tr><td>+30 year gap</td>\n";	
    echo "<td>" . number_format( $Plus30 ) . " (" . number_format( $plus30Perc ) . "%)</td></tr>\n";	
    echo "</table>";	
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

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>


<script type="text/javascript">

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Topping');
        data.addColumn('number', 'Slices');
        data.addRows([
<?php
	$normal=$control['numHouseholds'] - ($control['plus30']+$control['less15']);
?>	
		
["Normal age distribution",<?php echo $normal; ?>],	
["More than 30 years between oldest child and youngest adult",<?php echo $control['plus30']; ?>],
["Less than 15 years between oldest child and youngest adult",<?php echo $control['less15']; ?>]			

        ]);

        // Set chart options
        var options = {
			title: <?php echo "'Total Households: " . number_format($control['numHouseholds']) . "'"; ?>,
			titleTextStyle: {fontSize: 16},			
			backgroundColor: '#E8EBEE',
			is3D: true,
            height:600,
			slices: [	
				{color: '#ff6f00'},
				{color: '#ff9a4d'},	
				{color: '#ffad33'},		
				{color: '#f87254'}, 
				{color: '#da2e0b'},	
				{color: '#841E14'},
				{color: '#944dff'}, 
				{color: '#4d94ff'},	
				{color: '#33cc33'}											
			]				
		};

        // Instantiate and draw our chart, passing in some options.
		
<?php
// only define Google chart object when "Draw Chart" button is clicked.		-mlr		
	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) 
		echo "
        var chart = new google.visualization.PieChart(document.getElementById('pie_div'));
        chart.draw(data, options)
		
		$(window).on('debouncedresize', function( event ) {
	    chart.draw(data, options);
		});\n";		
?>		
      }
</script>