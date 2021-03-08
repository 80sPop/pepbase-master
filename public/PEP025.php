<?php
/**
 * reports/PEP025.php
 * written: 10/10/2020
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
	doReportHeader("PEP025");	
?>
<div class="container p-0">


	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>In Stock Percentage - PEP025</h4>
		
		<h6 class='text-center'><i>Tracks ratio of in-stock essential products to eligible members of a household. See PEP014 for data on each
		individual product.<?php echo $control['instock_dagger']; ?></i></h6> 
		</div>
		<div class='card-body bg-gray-2 rounded-0'>


<?php
	bSearchForm();

	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
    	echo "<div id='chart_div' style='margin:0;padding:0;'></div>";
		
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

 	if ( $control['dateType'] == "before" || $control['dateType'] == "onorbefore" || $control['start'] <= "2011-03-24")
		echo "<div class='mt-4' style='font-size:0.9rem;'>" . DAGGER_FOOTNOTE . " In-Stock values were not recorded prior to March 24, 2011.</div>";
	
	if ( $control['dateType'] == "last18months" )
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
	
 	if ( $arr['dateType'] == "before" || $arr['dateType'] == "onorbefore" )
		$arr['start']	= consumptionDateLimit("start");	
	
	$arr['instock_dagger']="";
 	if ( $arr['dateType'] == "before" || $arr['dateType'] == "onorbefore" || $arr['start'] <= "2011-03-24")
		$arr['instock_dagger'] = '<sup>&#8224;</sup>';	


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
					<label>Interval</label>";
					bSelectInterval( "interval", "$control[interval]" );
		echo "     
				</div>
				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Draw Chart</button>

			</form>";
}


function addDataRows() {
	global	$control, 
			$cTicks;

	$cTicks = array();

//    if ( $control['pantry'] == "All" )
    if ( $control['pantry'] == 0 )	
		$pantryQ="1";
	else
		$pantryQ="pantry_id = $control[pantry]";

	$interval['start'] = $control['start'];

	if ( $control['dateType'] == "last18months" || $control['dateType'] == "range" ) {
		$endOfConsumption=consumptionDateLimit("end");
		if ($control['end'] > $endOfConsumption)
			$control['end']	= consumptionDateLimit("end");	// prevent null values in chart when no pantry data for interval			
		$dateQ = "date >= '$control[start]' AND date <= '$control[end]'";

	} elseif ( $control['dateType'] == "equalto" )
		$dateQ = "date = '$control[start]'";

 	elseif ( $control['dateType'] == "after" ) {
		$dateQ = "date > '$control[start]'";
		$control['end']	= consumptionDateLimit("end");	

 	} elseif ( $control['dateType'] == "onorafter" ) {
		$dateQ = "date >= '$control[start]'";
		$control['end']	= consumptionDateLimit("end");	
	
 	} elseif ( $control['dateType'] == "before" ) {
		$dateQ = "date < '$control[start]'";
		$control['start']	= consumptionDateLimit("start");	
		$interval['start']	= $control['start'];	

 	} elseif ( $control['dateType'] == "onorbefore" ) {
		$dateQ = "date <= '$control[start]'";
		$control['start']	= consumptionDateLimit("start");	
		$interval['start']	= $control['start'];		
	}


	$interval['end'] = nextInterval( $control['interval'], $interval['start'] );

	$firstRow=1;
	$c=0;

	while ( $interval['start'] <= $control['end'] ) {
		$y=substr($interval['start'],0,4);		
		if ( $control['interval'] == "quarterly" )	// show last month of quarter	
			$m=intval(substr($interval['start'],5,2)) +1;		
		else
			$m=intval(substr($interval['start'],5,2)) -1;
		$dataDate="new Date($y, $m)";
		$pct=calcPct( $interval['start'], $interval['end'], $pantryQ );
		if (!$firstRow)
			echo ",\n";
		echo "[$dataDate, " . number_format($pct) . " ]";
		$cTicks[$c]=$dataDate;
		$interval['start'] = date("Y-m-d", strtotime("$interval[end] + 1 day"));
		$interval['end'] = nextInterval( $control['interval'], $interval['start'] );
		$firstRow=0;
		$c++;
	}
}

function nextInterval( $type, $start ) {
	
	$today=date("Y-m-d");
	$rArray = array();
	$startY=substr($start,0,4);
	$startM=substr($start,5,2);

	if ( $type == "annual" )
		$intEnd = substr($start,0,4) . "-12-31";

	elseif ( $type == "quarterly" ) 

		if ( intval($startM) < 4 )
			$intEnd = $startY . "-03-31";
		elseif ( intval($startM) < 7 )
			$intEnd = $startY . "-06-30";
		elseif ( intval($startM) < 10 )
			$intEnd = $startY . "-09-30";
		else
			$intEnd = $startY . "-12-31";

	else // monthly
		$intEnd = $startY . "-" . $startM . date("-t", strtotime("$start"));

	if ( $intEnd > $today )
		$intEnd = $today;
	
	return $intEnd;
}

function calcPct( $start, $end, $pQ ) {
	global $control;

	$pct=0;

	$sql = "SELECT SUM(quantity_oked) AS quantity_oked_sum, SUM(instock) AS instock_sum FROM consumption
			WHERE household_id > 0
			AND date >= '$start'
			AND date <= '$end'
			AND $pQ";
			
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();
	$total = $stmt->rowCount();	
	if ($total > 0) {
		$row = $stmt->fetch();				
		if ( $row['quantity_oked_sum'] > 0 )
        	$pct = ( $row['instock_sum'] / $row['quantity_oked_sum'] ) * 100;
		else
        	$pct = 0;
	}

	return $pct;
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

	google.charts.load('current', {packages: ['corechart', 'line']});
	google.charts.setOnLoadCallback(drawCurveTypes);

	function drawCurveTypes() {
		
		var data = new google.visualization.DataTable(); 
		
		data.addColumn('date', 'Month');
		data.addColumn('number', 'Pecentage');

		data.addRows([

			<?php addDataRows(); ?>

		]);

		var options = {

			title: "Products In Stock - <?php echo ucfirst($control['interval']); ?>",
			titleTextStyle: {fontSize: 16},			
			subtitle: "subtitle",
			chartArea:{top:60,bottom:80},
			height: 600,
			backgroundColor: '#E8EBEE',
			is3D: true,

			hAxis: {
/*				title: 'Date', */
				titleTextStyle: {fontSize: 16},
				textStyle : {fontSize: 16},
				format: "<?php echo $control['hAxis']; ?>",


		      ticks: [
<?php
				$isFirst=1;
				foreach ($cTicks as $v) {
					if (! $isFirst) echo ",\n";
					echo $v;
					$isFirst=0;
				}
?>
		             ]

			},

			vAxis: {
				title: '% Products In-Stock',
				titleTextStyle: {fontSize: 16},
				textStyle : {fontSize: 16}
			},

			series: {
			  	0: {color: '#495057' }
			},

			legend: { 
				position: 'none',
				textStyle : {fontSize: 16}
			}

		};

		// format date in tooltip
		var formatter = new google.visualization.DateFormat({pattern: '<?php echo $control['tooltip']; ?>'}); 
//		var formatter = new google.visualization.DateFormat({pattern: 'YYYY'}); 
		formatter.format(data, 0);				
		
<?php
// 3-18-2020: v 3.9.4.2 update: only define Google chart object when "Draw Chart" button is clicked.	-mlr		
	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) 
		echo "
		var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		chart.draw(data, options);

		$(window).on('debouncedresize', function( event ) {
	    chart.draw(data, options);
		});\n";
?>

	}

</script>
