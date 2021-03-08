<?php
/**
 * reports/PEP002.php
 * written: 10/11/2020
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
	require_once('../functions.php');

	if ($isPublic)	
		require_once('../reports/bFunctions.php');
	else
		require_once('bFunctions.php');		

	if (!$isPublic) {
		if (!$control=validUser())
			die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");	
	} else
		$control=array();	
	
	$control=fillControlArray($control, $config, "reports");	
	$control=addReportControl($control);
	$control['isPublic']=$isPublic;	
	
	define ( 'DAGGER_FOOTNOTE', '<sup>&#8224;</sup>' );			

    set_time_limit(900);
//	setGlobals();			// get vars $PantryID and $themeId

//	defineThemeConstants();					// defined in Themes.php	

	$errCode=0;	
	doReportHeader("PEP002");	
?>
<div class="container p-0">

	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Household Members by Age and Gender - PEP002</h4>
		
		<h6 class='text-center'><i>Illustrates members by age and gender for households active within the selected date range.</i></h6> 
		</div>
		<div class='card-body bg-gray-2 rounded-0'>


<?php
	bSearchForm();
	
	$slices="";
	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
		$bars=addBars();	
		echo "
		<div id='bar_div' style='margin:0;padding:0;'></div>";
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
					<label>Last Active Date</label>";
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

				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Draw Chart</button>

			</form>";
}

function addBars() {
	global $control; 

    $TodaysDate  = date("Y-m-d");
    $TodaysMonth = substr($TodaysDate,5,2);
    $TodaysDay   = substr($TodaysDate,8,2);
    $TodaysYear  = substr($TodaysDate,0,4);
	$tMale=0;
	$tFemale=0;
	
#####################
# ADULTS 65+        #
#####################

    $UpperYear = $TodaysYear - 65;
    $UpperLimit = $UpperYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $LowerLimit = '0000-00-00';
    $Male65over = countAgeGroup($LowerLimit,$UpperLimit,'male');
    $Female65over = countAgeGroup($LowerLimit,$UpperLimit,'female');
	$tMale+=$Male65over;
	$tFemale+=$Female65over;
	
#####################
# ADULTS 18-65      #
#####################

    $UpperYear = $TodaysYear - 18;
    $UpperLimit = $UpperYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $LowerYear = $TodaysYear - 66;
    $LowerLimit = $LowerYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $Male18to65 = countAgeGroup($LowerLimit,$UpperLimit,'male');
    $Female18to65 = countAgeGroup($LowerLimit,$UpperLimit,'female');
	$tMale+=$Male18to65;
	$tFemale+=$Female18to65;	
  
#####################
# TEENS 12-17       #
#####################

    $UpperYear = $TodaysYear - 12;
    $UpperLimit = $UpperYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $LowerYear = $TodaysYear - 18;
    $LowerLimit = $LowerYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $Male12to17 = countAgeGroup($LowerLimit,$UpperLimit,'male');
    $Female12to17 = countAgeGroup($LowerLimit,$UpperLimit,'female');
	$tMale+=$Male12to17;
	$tFemale+=$Female12to17;
	
#####################
# YOUTH 4-11        #
#####################

    $UpperYear = $TodaysYear - 4;
    $UpperLimit = $UpperYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $LowerYear = $TodaysYear - 12;
    $LowerLimit = $LowerYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $Male4to11 = countAgeGroup($LowerLimit,$UpperLimit,'male');
    $Female4to11 = countAgeGroup($LowerLimit,$UpperLimit,'female');
	$tMale+=$Male4to11;
	$tFemale+=$Female4to11;	
  
#####################
# INFANTS 0-3       #
#####################

    $UpperYear = $TodaysYear - 0;
    $UpperLimit = $UpperYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $LowerYear = $TodaysYear - 4;
    $LowerLimit = $LowerYear.'-'.$TodaysMonth.'-'.$TodaysDay;
    $Male0to3 = countAgeGroup($LowerLimit,$UpperLimit,'male');
    $Female0to3 = countAgeGroup($LowerLimit,$UpperLimit,'female');	
	$tMale+=$Male0to3;
	$tFemale+=$Female0to3;		

//	['', 'Male', { role: 'style' }, 'Female',{ role: 'style' }],
//	['Adults 65+', $Male65over, '#FF8628', $Female65over, '#FCB856'],
//	['Adults 18-64', $Male18to65, '#FF8628', $Female18to65, '#FCB856'],
//	['Teens 12-17', $Male12to17, '#FF8628', $Female12to17, '#FCB856'],
//	['Youth 4-11',  $Male4to11, '#FF8628', $Female4to11, '#FCB856'],		  
//	['Infants 0-3', $Male0to3, '#FF8628', $Female0to3, '#FCB856']\n";	

	$dataR="
	['', 'Male', 'Female'],		
	['Adults 65+', $Male65over, $Female65over ],
	['Adults 18-64', $Male18to65, $Female18to65 ],
	['Teens 12-17', $Male12to17,  $Female12to17],
	['Youth 4-11',  $Male4to11,  $Female4to11],		  
	['Infants 0-3', $Male0to3, $Female0to3, ],	
	['Total', $tMale, $tFemale, ]\n";	
	return $dataR;
}

function countAgeGroup($floor, $ceiling, $gender) {
	global $control; 
		
	if ( $control['dateType'] == "last18months" || $control['dateType'] == "range" ) {
		$dateQ = "lastactivedate >= '$control[start]' AND lastactivedate <= '$control[end]'";
	} elseif ( $control['dateType'] == "equalto" )
		$dateQ = "lastactivedate = '$control[start]'";
 	elseif ( $control['dateType'] == "after" ) {
		$dateQ = "lastactivedate > '$control[start]'";
 	} elseif ( $control['dateType'] == "onorafter" ) {
		$dateQ = "lastactivedate >= '$control[start]'";
 	} elseif ( $control['dateType'] == "before" ) {
		$dateQ = "lastactivedate < '$control[start]'";
 	} elseif ( $control['dateType'] == "onorbefore" ) {
		$dateQ = "lastactivedate <= '$control[start]'";
	}		
		
    $count=0;
//	$curious="1"; // All	
//	$curious="pantry_id = 1"; // Atwood
//	$curious="pantry_id = 3"; // CMC
//	$curious="pantry_id = 6"; // Stoughton
//	$curious="pantry_id = 8"; // Watertown	
//	$curious="pantry_id = 11"; // Good Neighbors
//	$curious="pantry_id = 15"; // Grace Episcopal	

    if ( $control['pantry'] == 0 )	
		$pantryQ="1";
	else
		$pantryQ="pantry_id = $control[pantry]";
	

    $sql = "SELECT householdID FROM members
            WHERE dob > '$floor'  
            AND dob <= '$ceiling' 
            AND householdID > 0
            AND gender = '$gender'";
	$stmt = $control['db']->query($sql);			
	$total = $stmt->rowCount();	
	while ($row = $stmt->fetch()) {				
			
		// remove duplicate households from results
		$sql2 = "SELECT id FROM household
				 WHERE id = $row[householdID]
				 AND $dateQ
				 AND $pantryQ
				 AND streetname NOT LIKE 'duplicate%'";
		$stmt2 = $control['db']->prepare($sql2);
		$stmt2->execute();
		$total = $stmt2->rowCount();	
		if ($total > 0) 
			$count++;
    }

    return $count;
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


<script>
/*      google.charts.load('current', {'packages':['bar']}); */
	  google.charts.load('current', {'packages':['corechart', 'bar']});	  
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
		
		<?php echo $bars; ?>

	  
        ]);

        var options = {

			titleTextStyle: {fontSize:20},
			titleTextStyle: {color:'#000000'},		  
			textStyle : {fontSize: 16},
			height: 600,
			backgroundColor: '#E8EBEE',		   
			bars: 'horizontal', // Required for Material Bar Charts.
			
			series: {
			  0:{color: '#FF8628'},
			  1:{color: '#FCB856'}
			},		
	  
 			vAxis: {

				titleTextStyle: {fontSize: 16},
				textStyle : {fontSize: 16}			
			},		  
			  
			hAxis: {
				title: 'Household Members',			
				gridlines:{color: '#bbb'},
				titleTextStyle: {fontSize: 16},			
				textStyle : {fontSize: 16}			
			}			  
        };

/*        var chart = new google.charts.Bar(document.getElementById('bar_div')); */

/*        chart.draw(data, google.charts.Bar.convertOptions(options)); */
		
<?php
// only define Google chart object when "Draw Chart" button is clicked.		-mlr		
	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) 
		echo "
        var chart = new google.visualization.BarChart(document.getElementById('bar_div'));
        chart.draw(data, options)
		
		$(window).on('debouncedresize', function( event ) {
	    chart.draw(data, options);
		});\n";		
		
		
?>				
      }

</script>