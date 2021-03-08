<!DOCTYPE html>
<html>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	
<?php
	echo "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>\n";
	echo "<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css' >\n";
	echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/flexboxgrid/6.3.1/flexboxgrid.min.css' type='text/css' >\n";

	echo "<link rel='stylesheet' href='../css/custom-datepicker.css' >\n";
	echo "<link rel='stylesheet' href='../css/custom-bootstrap.css' >\n";
	echo "<link rel='stylesheet' href='../css/rt-responsive.css' >\n";


//echo "<link rel='stylesheet' href='" . ROOT . "pep3.css' >\n";
//echo "<style>\n";
//require_once("../pep3.css");
//echo "</style>\n"; 


	// Bootstrap Core Javascript
//	echo "<script src='https://code.jquery.com/jquery-3.2.1.slim.min.js' integrity='sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN' crossorigin='anonymous'></script>";
//    echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js' integrity='sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q' crossorigin='anonymous'></script>";
//    echo "<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js' integrity='sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl' crossorigin='anonymous'></script>";







	echo "<script src='https://code.jquery.com/jquery-3.1.1.min.js' integrity='sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=' crossorigin='anonymous'></script>\n";
	echo "<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>\n";


	// Date-Time Picker Javascript
	echo "<script src='//momentjs.com/downloads/moment.js'></script>\n";
	echo "<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js'></script>\n";
	echo "<script src='../javascript/cleave.min.js' type='text/javascript' ></script>\n";
	echo "<script src='../javascript/custom.js' type='text/javascript' ></script>\n";
	echo "<script src='../javascript/pep3.js'></script>\n";






?>


</head>
<body>

	<div id='input-date-1' class='input-group  date' style='width:225px;' >
				<input id='date-input-1' type='text' class='form-control date-g' placeholder='mm/dd/yyyy' style='background-color:#eee;'>			
				<span class='input-group-btn' style='width:0 !important;'>
				<span id='date-clear-1' style='color:#bababa !important;' class='glyphicon glyphicon-remove-circle'></span></span>		
				<span class='input-group-addon'><span class='glyphicon glyphicon-calendar'></span></span>
			</div>


    
</body>

<script>
		
	$('#date-clear-1').click(function(){
		$('#date-input-1').val('').focus();
	})		

	$('#date-clear-2').click(function(){
		$('#date-input-2').val('').focus();
	})		

	$('#date-clear-3').click(function(){
		$('#date-input-3').val('').focus();
	})	

	var cleave = new Cleave('.date-g', {
		date: true,
		datePattern: ['m', 'd', 'Y']
	});	

	$('.input-group.date')
		.datetimepicker({
			format: 'MM/DD/YYYY',
			keepOpen:false,
			showClear:true,
			viewMode:'days'
	});

</script>






</html>
