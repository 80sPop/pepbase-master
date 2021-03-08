<?php
/**
 * reports/PEP013.php
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
	doReportHeader("PEP013");	
?>
<div class="container p-0">


	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Households by Language and Zip Code - PEP013</h4></div>
		<div class='card-body bg-gray-2 rounded-0'>

<?php
	bSearchForm();

	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
 		doReport();

	}
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

	return $arr;
}


function bSearchForm() {
	global $control; 

//	$showDate="display:block;";
//	$showRange="display:none;";
//	if ( $control['dateType'] == "range" ) {
//		$showRange="display:block;";
//		$showDate="display:none;";	
//	} 
	
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

				</div>\n";
		echo "
				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Draw Chart</button>

			</form>";
}

function doReport() {
	global $control, $citystate, $totals;

	doHeadings();

// initialize totals array
	$totals=array();
	
    $sql = "SELECT * FROM languages ORDER BY id";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	foreach($result as $languages)	
		$totals[$languages['id']]=0;

	$citystate="";
	
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

	$sql = "SELECT zip_five
			FROM household
			WHERE $dateQ
			AND id > 0
			AND zip_five > 0
			GROUP BY zip_five";
			
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	foreach($result as $row)				
		if (! is_numeric($row['zip_five'])) 
			echo "ERROR in household table zip code=$row[zip_five]<br>";
		else	
			printReport($row['zip_five'], $dateQ);	
		
	printTotals($totals);	

}	

function doHeadings() {
	global $control;

	echo " 
	<div class='row mt-4'>
		<div class='col-sm border border-dark border-right-0 p-1'>Zip Code</div>\n";
		
    $sql = "SELECT * FROM languages ORDER BY id";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	foreach($result as $row) {    
        $i=$row['id'];
        $LanguageTot[$i]=0;
		echo "<div class='col-sm border border-dark border-right-0 p-1'>$row[name]</div>\n";
	}		
	echo "<div class='col-sm border border-dark p-1'>Total</div>
	</div>";
}

function printReport($ZipCode, $dateQ) {
	global $control, $citystate, $totals;

	$total=0;
	$isBegin=1;
    $sql = "SELECT * FROM languages ORDER BY id";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
//	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();
	foreach($result as $languages) {   	
 
        $sql2 = "SELECT count(zip_five)  
                 FROM household
				 WHERE language = $languages[id]
                 AND zip_five = $ZipCode
                 AND $dateQ
                 AND id > 0";		

		$stmt2 = $control['db']->prepare($sql2);
		$stmt2->execute();	
		$total2 = $stmt2->rowCount();	
		if ($total2 > 0 ) {	
			$household = $stmt2->fetch();
			
			$sql3 ="SELECT * FROM us_zip_codes WHERE zip = $ZipCode";
			$stmt3 = $control['db']->prepare($sql3);
			$stmt3->execute();	
			$total3 = $stmt3->rowCount();
			if ($total3 > 0 ) {
				$us_zip_codes = $stmt3->fetch();
				$newcitystate=$us_zip_codes['primary_city'] . ", " . $us_zip_codes['state'];
			} else
				$newcitystate= $ZipCode . " - ZIP CODE NOT FOUND";	
		
			if ( $newcitystate != $citystate ) {
				$citystate = $newcitystate;
				echo "<div class='row'>		
						<div class='col-sm border border-dark border-top-0 bg-gray-4 p-1'>$citystate</div>
					</div>\n";
			}				
			
			if ($isBegin) {
				echo "<div class='row'>
						 <div class='col-sm border border-dark border-right-0 border-top-0 p-1'>" . $ZipCode . "</div>\n";
				$isBegin=0;
			}	
		
			if ($household['count(zip_five)'])
				echo "<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>" . number_format($household['count(zip_five)']) . "</div>";
			else
				echo "<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>&#160;</div>";
			
			$total += $household['count(zip_five)'];
			$totals[$languages['id']]+= $household['count(zip_five)'];
	
        }
    }
	echo "<div class='col-sm border border-dark border-top-0 p-1'>" . number_format($total) . "</div>";
	echo "</div>\n";	

}	

function printTotals($totals) {
	global $control;

	$grand=0;
	
	echo "
	<div class='row'>		
		<div class='col-sm border text-center border-dark border-top-0 bg-gray-4 p-1'>TOTALS</div>
	</div>
	<div class='row'>	
		<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>&nbsp;</div>\n";		

    $sql = "SELECT * FROM languages ORDER BY id";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$result = $stmt->fetchAll();
	foreach($result as $languages) {  	
		$grand+=$totals[$languages['id']];
		echo "<div class='col-sm border border-dark border-right-0 border-top-0 p-1'>" . number_format($totals[$languages['id']]) . "</div>\n";
	}	
	echo "
		<div class='col-sm border border-dark border-top-0 p-1'>" . number_format($grand) . "</div>
	</div>";	
}
?>

<!-- Place any per-page javascript here -->

<script>
		
	function onSelectDate() {

		if ( document.getElementById("dateType").value == "last18months" ) {
			document.getElementById("hide-date-1").style.display="none";
			document.getElementById("hide-range-1").style.display="none";
		} else if ( document.getElementById("dateType").value == "range" ) {
			document.getElementById("hide-date-1").style.display="none";
			document.getElementById("hide-range-1").style.display="block";
			document.getElementById("date2").focus();			
		} else {
			document.getElementById("hide-date-1").style.display="block";
			document.getElementById("hide-range-1").style.display="none";
			document.getElementById("date1").focus();				
		}
	
	}

</script>