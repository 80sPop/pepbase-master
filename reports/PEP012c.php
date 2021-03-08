<?php
/**
 * File name   : /Reports/PEP012c.php
 * Begin       : 7-28-2019
 * Last Update : 
 *
 * Uses Google Charts API and Bootstrap 4 framework to print bar chart of household visits for each zip code
 * in Pepbase. Part of version pepbase 3.9.1 update.
 *
 * PHP version 7.2
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package    pepbase
 * @author     M. Rolfsmeyer <rolfs@hotmail.com>
 * @copyright  1997-2019 Pepartnership, Inc.
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    3.9.1
 * @link       https://www.essentialspantry.org/pepbase
 * @see        NetOther, Net_Sample::Net_Sample()
 * @since      File available since Release 3.9.1
 * @deprecated 
 */
 
 $isPublic=0;

// verify access level
if 	( (!isset($_COOKIE["accessLevel"])) ||  ((isset($_COOKIE["accessLevel"])) && $_COOKIE["accessLevel"] < 1) ) 
	die("Unauthorized access - you must be signed in to print report.");

/* CONSTANT DECLARATIONS   */

	if 	( !$isPublic ) {
		define('HOST_ACCESS_LEVEL', $_COOKIE["accessLevel"]);
		define('HOST_SIGNIN_ID', $_COOKIE["signinId"]);
	}	
//	define('MAX_OFFSET', 100);
	define('MAX_OFFSET', 200);
	define ( 'DAGGER_FOOTNOTE', '<sup>&#8224;</sup>' );			

// include files
	if ( $isPublic )
		require_once('../Public/PublicReportsMySQLConfig.php'); 
	else
		require_once('../MySQLConfig.php'); 

//	require_once(ROOT . 'common_vars.php');
	require_once(ROOT . 'functions.php');	
//	require_once(ROOT . 'Header.php');
	require_once(ROOT . 'Themes.php');	
	require_once('../Reports/bFunctions.php');

/* INITIALIZE VARS */

	if 	( !$isPublic ) {
		$sql = "SELECT * FROM access_levels WHERE al_id = " . HOST_ACCESS_LEVEL;
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		$accessLevelRow = mysqli_fetch_assoc($result);
		
		$sql = "SELECT * FROM signin_accounts WHERE sa_id = " . HOST_SIGNIN_ID;
		$result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
		if ($row = mysqli_fetch_assoc($result)) 
			$hostPantryId = $row['sa_pantry_id'];
		else
			die('host signin not found in signin_accounts table');
	} else 
		$hostPantryId = -1;


    set_time_limit(900);
	setGlobals();			// get vars $PantryID and $themeId

	defineThemeConstants();					// defined in Themes.php	

	$errCode=0;	
	$ArrNumVisits=array();
	$control=fillControlArray();
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>PEP012c</title>

<?php
	echo "<link rel='shortcut icon' type='image/x-icon' href='" .  ROOT . FAVICON . "' />\n";
?>

<!--	<link rel="icon" type="image/x-icon" href="../images/favicon.ico" /> -->


    <!-- Bootstrap CSS -->
<!--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->

	<!-- Sass Bootstrap override -->
	<link rel='stylesheet' href='../main.css?' >
	
	<!-- custom css -->
    <link rel="stylesheet" href="../css/sticky-footer.css" >	

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

	<!-- smartresize js for responsive charts -->
	<script type="text/javascript" src="../javascript/jquery.debouncedresize.js"></script>
	<script type="text/javascript" src="../javascript/jquery.throttledresize.js"></script>

	<!-- Google Charts API -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>


<?php

/* MAINLINE */
		
?>
<body class="bg-gray-5" onload="document.getElementById('<?php echo $control['focus']; ?>').focus()">
<div class="container p-0">


	<div class='card rounded-0'>
		<div class='card-header bg-gray-4 rounded-0'><h4 class='text-center'>Households Visits by Zip Code - PEP012c</h4></div>
		<div class='card-body bg-gray-2 rounded-0'>


<?php
	bSearchForm();

	if ( isset($_POST['drawChartBtn']) && $control['error'] == "" ) {
 		CountVisitsPerHousehold();

    		echo "<div id='chart_div' style='margin:0;padding:0;'></div>";
	}
?>

		</div>
	</div>
</div>
<?php bFooter(); ?>
</body>
</html>

<?php

function fillControlArray() {

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
		$arr['pantry']="All";

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
		if (! validateDate($arr['date2'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date2";
		} elseif (! validateDate($arr['date3'], 'Y-m-d') ) {
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
		if (! validateDate($arr['date1'], 'Y-m-d') ) {
			$arr['error'] = "date";
			$arr['focus'] = "date1";
		}
	}

	return $arr;
}


function bSearchForm()
{

global 	$conn, 
		$control,
		$hostPantryId, 
		$pantryID,
 		$themeId; 

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

				<input type='hidden' name='themeId' value= '$themeId' />
				<input type='hidden' name='pantryID' value= '$pantryID' />

				<div class='form-group'>
					<label>Date</label>";
					bSelectDateType( "dateType", "$control[dateType]" ); 
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
					bSelectPantry( "pantry", "$control[pantry]" );
		echo "     
				</div>

				<button type='submit' name='drawChartBtn' class='btn btn-primary text-white'>Draw Chart</button>

			</form>";
}

 
 
 ?>