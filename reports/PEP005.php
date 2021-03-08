<?php
/**
 * reports/PEP005.php
 * written: 10/15/2020
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
	doReportHeader("PEP005");	
?>

<div class="container p-0">
	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Household Composition by Gender of Primary Shopper - PEP005</h4>
		<!--	<h6 class='text-center'><i>Tracks product consumption for the selected date range and pantry.</i></h6> -->
		</div>
		<div class='card-body bg-gray-2 rounded-0'>
<?php
			bSearchForm();

			if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
				
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
	global $control;

	if ( $control['dateType']=="last18months" )

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
	global 	$control,
			$MPNoAdults,
			$MPSingleNoKids,
			$MPSingleWithKids,
			$MPPlusAdultMaleNoKids,
			$MPPlusAdultMaleWithKids,
			$MPPlusAdultFemaleNoKids,
			$MPPlusAdultFemaleWithKids,
			$MPPlus2OrMoreAdultsNoKids,
			$MPPlus2OrMoreAdultsWithKids,
			$MPNoValidData,
			$FPNoAdults,
			$FPSingleNoKids,
			$FPSingleWithKids,
			$FPPlusAdultMaleNoKids,
			$FPPlusAdultMaleWithKids,
			$FPPlusAdultFemaleNoKids,
			$FPPlusAdultFemaleWithKids,
			$FPPlus2OrMoreAdultsNoKids,
			$FPPlus2OrMoreAdultsWithKids,
			$FPNoValidData,			
			$NoValidData,
			$NoGender,
			$FemaleNoDOB,
			$MaleNoDOB,
			$MembersWithUsefulData,
			$HouseholdsNoData,
			$NumAdults,
			$NumChildren,
			$AdultFemales, 
			$AdultMales,
			$ChildFemales,
			$ChildMales;

    $MPSingleNoKids=0;
    $MPSingleWithKids=0;
    $MPPlusAdultMaleNoKids=0;
    $MPPlusAdultMaleWithKids=0;
    $MPPlusAdultFemaleNoKids=0;
    $MPPlusAdultFemaleWithKids=0;
    $MPPlus2OrMoreAdultsNoKids=0;
    $MPPlus2OrMoreAdultsWithKids=0;
    $MPTotal=0;
    $MPNoValidData=0;

    $FPSingleNoKids=0;
    $FPSingleWithKids=0;
    $FPPlusAdultMaleNoKids=0;
    $FPPlusAdultMaleWithKids=0;
    $FPPlusAdultFemaleNoKids=0;
    $FPPlusAdultFemaleWithKids=0;
    $FPPlus2OrMoreAdultsNoKids=0;
    $FPPlus2OrMoreAdultsWithKids=0;
    $FPNoValidData=0;
    $FPTotal=0;

    $NumSingleFemaleAdult= 0;
    $NumSingleFemaleAdultPlusKids= 0;
    $NumSingleMaleAdult= 0;
    $NumSingleMaleAdultPlusKids= 0;
    $Num1Male1FemaleAdultPlusKids= 0;
    $Num1Male1FemaleAdultNoKids= 0;
    $Num2FemaleAdultPlusKids= 0;
    $Num2FemaleAdultNoKids= 0;
    $Num2MaleAdultPlusKids= 0;
    $Num2MaleAdultNoKids= 0;
    $Num3PlusAdultPlusKids= 0;
    $Num3PlusAdultNoKids= 0;
    $NoGender=0;
    $FemaleNoDOB=0;
    $MaleNoDOB=0;
    $MembersWithUsefulData=0;
    $HouseholdsNoData=0;
    $NoValidData=0;
    $MPNoAdults=0;
    $FPNoAdults=0;

    $NoPrimaryInHH=0;
    $NumMatchingPrimary=0;

    $NumAdults=0;
    $NumChildren=0;
    $NumMembers=0; 
	$GrandTot=0;	
	
    $AdultFemales=0; 
    $AdultMales=0;
    $ChildFemales=0;
    $ChildMales=0;
	
//    $AdultMonth = substr($UpperActive,5,2);
//    $AdultDay   = substr($UpperActive,8,2);
//    $AdultYear  = substr($UpperActive,0,4);



    $NumHouseholds =0;
    $NewHousehold=1;
    $FirstRow=1;
	
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

//    $AdultYear = $AdultYear - 18;
//    $AdultDOB = $AdultYear.'-'.$AdultMonth.'-'.$AdultDay;
	
//	$infantOffset = date('Y-m-d', strtotime("$today - 18 years"));	

// returns data for members who were adults at the time of the report
	$AdultDOB = date('Y-m-d', strtotime("$control[end] - 18 years"));		


		
	
    $sql = "SELECT * FROM household
            WHERE id > 0
			AND $dateQ
            AND $pantryQ
            AND streetname NOT LIKE 'duplicate%'";
			
	$stmt = $control['db']->query($sql);			
	$total = $stmt->rowCount();	

	while ($row = $stmt->fetch()) {				
        $NumHouseholds++;

        $id = $row['id'];
        $PrimaryIsMale = 0;
        $PrimaryIsFemale = 0;

        $NumAdults=0;
        $NumChildren=0;
        $AdultFemales=0; 
        $AdultMales=0;
        $ChildFemales=0;  
        $ChildMales=0;

        $sql2 = "SELECT * FROM members
                 WHERE householdID = $id
                 AND dob > '0000-00-00'
                 AND (gender = 'male' OR gender = 'female')";
		$stmt2 = $control['db']->query($sql2);			
		$total2 = $stmt2->rowCount();	
		while ($row2 = $stmt2->fetch()) {			
            $NumMembers++; 

//            if ( ($row['lastname'] == $row2['lastname']) &&
//                 ($row['firstname'] == $row2['firstname']) )
//            {

			if ($row2['is_primary'])
                if ($row2['gender'] == 'male')
                    $PrimaryIsMale = 1;
                else
                    $PrimaryIsFemale = 1;



            if ($row2['gender'] == 'female')
            {
                if ($row2['dob'] <= $AdultDOB) 
                {
                    $AdultFemales++;
                    $NumAdults++;
                }
                else
                {
                    $ChildFemales++;
                    $NumChildren++;
                }
            }
            else
            {
                if ($row2['gender'] == 'male')
                {
                    if ($row2['dob'] <= $AdultDOB)
                    {
                        $AdultMales++;
                        $NumAdults++;
                    }
                    else
                    {
                        $ChildMales++;
                        $NumChildren++;
                    }
                }
            }

        } // end inner loop

        if ($PrimaryIsMale)
        {
            $NumMatchingPrimary++;
            $MPTotal++;
            TallyMPHouseholdCounters();  
        }     
        else
        {
            if ($PrimaryIsFemale)
            {
                $NumMatchingPrimary++;
                $FPTotal++;
                TallyFPHouseholdCounters();  
            }  
            else
                $NoPrimaryInHH++;

        }
      

    } // end outer loop


?>

	<table class='table mb-2 mt-3'>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1 text-center'>Category</th>
    <th class='border border-dark border-right-0 bg-gray-4 p-1 text-center'>Primary is Male</th>
    <th class='border border-dark border-right-0 bg-gray-4 p-1 text-center'>Primary is Female</th>
    <th class='border border-dark bg-gray-4 p-1 text-center'>Total</th>
	</tr>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>Single (no kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPSingleNoKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPSingleNoKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPSingleNoKids+$FPSingleNoKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>Single (w/kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPSingleWithKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPSingleWithKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPSingleWithKids+$FPSingleWithKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>plus 1 adult male (no kids)</td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPPlusAdultMaleNoKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPPlusAdultMaleNoKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPPlusAdultMaleNoKids+$FPPlusAdultMaleNoKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</td>
	
    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>plus 1 adult male (w/kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPPlusAdultMaleWithKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPPlusAdultMaleWithKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPPlusAdultMaleWithKids+$FPPlusAdultMaleWithKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>plus 1 adult female (no kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPPlusAdultFemaleNoKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPPlusAdultFemaleNoKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPPlusAdultFemaleNoKids+$FPPlusAdultFemaleNoKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>plus 1 adult female (w/kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPPlusAdultFemaleWithKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPPlusAdultFemaleWithKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPPlusAdultFemaleWithKids+$FPPlusAdultFemaleWithKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>							

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>plus 2 or more adults (no kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPPlus2OrMoreAdultsNoKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPPlus2OrMoreAdultsNoKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPPlus2OrMoreAdultsNoKids+$FPPlus2OrMoreAdultsNoKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>plus 2 or more adults (w/kids)</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPPlus2OrMoreAdultsWithKids); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPPlus2OrMoreAdultsWithKids); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPPlus2OrMoreAdultsWithKids+$FPPlus2OrMoreAdultsWithKids; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>							

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1'>No Adults</th>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPNoAdults); ?></td>
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPNoAdults); ?></td>
    <td class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php $RowTot=$MPNoAdults+$FPNoAdults; 
                                $GrandTot = $GrandTot+$RowTot;
                                echo number_format($RowTot); ?></td>
	</tr>							

    <tr>
    <th class='border border-dark border-right-0 bg-gray-4 p-1 text-center'>Totals</th>
    <th class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($MPTotal); ?></th>
    <th class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1 text-right'><?php echo number_format($FPTotal); ?></th>
    <th class='border border-bottom-1 border-dark bg-gray-3 p-1 text-right'><?php echo number_format($GrandTot); ?></th>
	</tr>

    </table>

<?php

//    PrintFootNote();
//	mysqli_close( $conn );

}
######################################################################
######################################################################
#                    F U N C T I O N S                               #
######################################################################

function CalcDateLimits()
##################################################################################
#   written: 10-31-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $UpperLimit, $UpperLimitFmt, $LowerLimit, $LowerLimitFmt, $UpperActive, $LowerActive;
   
    $TodaysDate  = date("Y-m-d");
    $TodaysMonth = substr($TodaysDate,5,2);
    $TodaysDay   = substr($TodaysDate,8,2);
    $TodaysYear  = substr($TodaysDate,0,4);

    $UpperActive   = date('Y-m-d', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear)); 
    $UpperLimit    = $UpperActive;
    $UpperLimitFmt = date('M j, Y', mktime(0, 0, 0, $TodaysMonth,  $TodaysDay-14, $TodaysYear));

    $UpperMonth    = substr($UpperActive,5,2);
    $UpperDay      = substr($UpperActive,8,2);
    $UpperYear     = substr($UpperActive,0,4); 

    $LowerActive   = date('Y-m-d', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
    $LowerLimit = $LowerActive;
    $LowerLimitFmt = date('M j, Y', mktime(0, 0, 0, $UpperMonth-18,  $UpperDay, $UpperYear)); 
 
}



function TallyMPHouseholdCounters()
#################################################################
#################################################################
{ 

global	$MPNoAdults,
		$MPSingleNoKids,
		$MPSingleWithKids,
		$MPPlusAdultMaleNoKids,
		$MPPlusAdultMaleWithKids,
		$MPPlusAdultFemaleNoKids,
		$MPPlusAdultFemaleWithKids,
		$MPPlus2OrMoreAdultsNoKids,
		$MPPlus2OrMoreAdultsWithKids,
		$MPNoValidData,
		$NoValidData,
		$NoGender,
		$FemaleNoDOB,
		$MaleNoDOB,
		$MembersWithUsefulData,
		$HouseholdsNoData,
		$NumAdults,
		$NumChildren,
		$AdultFemales, 
		$AdultMales,
		$ChildFemales,
		$ChildMales;


    if ($NumAdults == 1) 
    {
        if ($NumChildren < 1)
        {
            $MPSingleNoKids++;
        }
        else
        {
            $MPSingleWithKids++;
        }
    }
    else
    {
        if ( ($AdultFemales == 0) AND ($AdultMales == 2) )
        {
            if ($NumChildren >= 1)
            {
                $MPPlusAdultMaleWithKids++;
            }
            else
            {
                $MPPlusAdultMaleNoKids++;
            }
        }

        else
        {
            if ($AdultFemales == 1) 
            {
                if ($NumChildren >= 1)
                {
                    $MPPlusAdultFemaleWithKids++;
                }
                else
                {
                    $MPPlusAdultFemaleNoKids++;
                }
            }
            else
            {
                if ($NumAdults >= 3)
                {
                    if ($NumChildren >= 1)
                    {
                        $MPPlus2OrMoreAdultsWithKids++;
                    }
                    else
                    {
                        $MPPlus2OrMoreAdultsNoKids++;
                    }
                }
                else
                    $MPNoAdults++;                                // No Adults
            }
        }
    }


} // end TallyMPHouseholdCounters()     


function TallyFPHouseholdCounters()
#################################################################
#################################################################
{ 

global $id,
	$FPNoAdults,
	$FPSingleNoKids,
	$FPSingleWithKids,
	$FPPlusAdultMaleNoKids,
	$FPPlusAdultMaleWithKids,
	$FPPlusAdultFemaleNoKids,
	$FPPlusAdultFemaleWithKids,
	$FPPlus2OrMoreAdultsNoKids,
	$FPPlus2OrMoreAdultsWithKids,
	$FPNoValidData,
    $NoValidData,
    $NoGender,
    $FemaleNoDOB,
    $MaleNoDOB,
    $MembersWithUsefulData,
    $HouseholdsNoData,
    $NumAdults,
    $NumChildren,
    $AdultFemales, 
    $AdultMales,
    $ChildFemales,
    $ChildMales;


    if ($NumAdults == 1) 
    {
        if ($NumChildren < 1)
            $FPSingleNoKids++;                         // Single (no kids) 
        else
            $FPSingleWithKids++;                       // Single (w/kids) 
    }
    else
    {
        if ( ($AdultMales == 1) AND ($NumAdults == 2) )
        {
            if ($NumChildren < 1)
                $FPPlusAdultMaleNoKids++;                  // plus 1 adult male (no kids) 
            else
                $FPPlusAdultMaleWithKids++;                // plus 1 adult male (w/kids)
        }
        else
        {

            if (($AdultFemales == 2) AND ($AdultMales == 0))   
            {
                if ($NumChildren < 1)
                    $FPPlusAdultFemaleNoKids++;                // plus 1 adult female (no kids)
                else
                    $FPPlusAdultFemaleWithKids++;              // plus 1 adult female (w/kids)
            }
            else
            {
                if ($NumAdults >= 3)
                {
                    if ($NumChildren < 1)
                        $FPPlus2OrMoreAdultsNoKids++;              // plus 2 or more adults (no kids) 
                    else
                        $FPPlus2OrMoreAdultsWithKids++;            // plus 2 or more adults (w/kids)
                }
                else $FPNoAdults++;                                // No Adults
            }
        }
    }


} 


function PrintFootNote()
##################################################################################
#   written: 10-18-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $LowerActive,$UpperActive,$NoPrimaryInHH,$NumMatchingPrimary; 


    $matched=0;
    $NotFound=0;
    $ValidMembers=0;
?>
    <p style="margin:15px;">
    <u><b>SUMMARY</b></u>
    <p>
    <TABLE style="font-size:10pt;">

    <tr>
    <TD>Active 'household' table row(s) used in study 
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'><?php
        echo number_format($NumMatchingPrimary); ?>

    <tr>
    <TD>Active 'household' table row(s) with no matching primary in 'members' table 
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'><?php
        echo number_format($NoPrimaryInHH); ?>

    <tr>
    <TD>Inactive or invalid 'household' table row(s) 
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>
    <?php
        $sql = "SELECT COUNT(*)
                FROM household
                WHERE lastactivedate < '$LowerActive'
                OR lastactivedate > '$UpperActive'
                OR id <= 0
                OR streetname LIKE 'duplicate%'";

        $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
        if ($row = mysqli_fetch_assoc($result))
            echo number_format($row['COUNT(*)']); ?>

    <tr>
    <TD>total row(s) in 'household' table 
    <td class='border border-bottom-1 border-dark border-right-0 bg-gray-3 p-1'>
    <?php
        $sql = "SELECT COUNT(*)
                FROM household";

        $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
        if ($row = mysqli_fetch_assoc($result))
            echo number_format($row['COUNT(*)']); ?>

    </TABLE>

<?php

}


     
?>
</center>
</body>
</html>   
