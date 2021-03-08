<?php
/**
 * header.php
 * written: 5/7/2020
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

require_once('config.php'); 
require_once('functions.php');	

function doHeader($title) {
	global $control;

	$title=getTitle();
	
	htmlHeader($title, "");
	paintBanner();

	if ($control["focus"] != "nofocus")
		echo "<body class='bg-gray-5' onload='document.getElementById(" . '"' . $control["focus"] . '"' . ").focus()'>";
	else
		echo "<body class='bg-gray-5'>";		

}

function doReportHeader($title) {
	global $control;


	htmlHeader($title, "../");
//	paintBanner();

	if ($control["focus"] != "nofocus")
		echo "<body class='bg-gray-5' onload='document.getElementById(" . '"' . $control["focus"] . '"' . ").focus()'>";
	else
		echo "<body class='bg-gray-5'>";		

}	

function doOverrideHeader($total, $result) {
	global $control;

	htmlHeader("Override Shopping", "../");
	
	$first=1;
	if ($total > 0) {
		foreach($result as $row) 
			if ($first) {
				echo "<body class='bg-gray-6' onload='document.getElementById(" . '"' . "approved$row[productID]" . '"' . ").focus()'>\n";
				$first=0;
			}	
	} else
		echo "<body class='bg-gray-6'>";		

}

function doSignInHeader($title) {
	global $control;


	htmlHeader($title, "");
//	paintBanner();
?>	
	<div class="container-fluid bg-gray-7 p-3 mb-4">
		<img class="img-fluid" style='height:75px;' src="images/logo-light.png" />
	</div>	
<?php
	if ($control["focus"] != "nofocus")
		echo "<body class='bg-gray-5' onload='document.getElementById(" . '"' . $control["focus"] . '"' . ").focus()'>";
	else
		echo "<body class='bg-gray-5'>";		

}

function getTitle() {
	global $control;
	
	$title="Pepbase";
	
	if (isset($_GET['isreg']))
		$title="Registration";
	elseif ($control['tab'] == "profile")
		$title="Profile";
	elseif ($control['tab'] == "members")
		$title="Members";
	elseif ($control['tab'] == "eligibility")
		$title="Eligibility";	
	elseif ($control['tab'] == "history")
		$title="Shopping History";	
	elseif ($control['tab'] == "definitions")
		$title="Product Definitions";
	elseif ($control['tab'] == "instock")
		$title="In Stock Status";	
	elseif ($control['tab'] == "setup")
		$title="Pantry Setup";
	elseif ($control['tab'] == "consumption")
		$title="Consumption Reports";
	elseif ($control['tab'] == "demographic")
		$title="Demographic Reports";	
	elseif ($control['tab'] == "graphs")
		$title="Charts and Graphs";	
	elseif ($control['tab'] == "pantries")
		$title="Pantries";	
	elseif ($control['tab'] == "users")
		$title="Users";		
	elseif ($control['tab'] == "access")
		$title="Access Levels";	
	elseif ($control['tab'] == "languages")
		$title="Languages";			
	elseif ($control['tab'] == "measures")
		$title="Measures";	
	elseif ($control['tab'] == "containers")
		$title="Containers";	
	elseif ($control['tab'] == "shelters")
		$title="Shelters";
	elseif ($control['tab'] == "change")
		$title="Change Pantry";			
	elseif ($control['tab'] == "userlog")
		$title="User Log";	
	elseif ($control['tab'] == "convert")
		$title="Convert";
	elseif ($control['tab'] == "about")
		$title="About";		

	return $title;		
}	

function htmlHeader($title, $root) {
?>
	<!doctype html>
	<html lang='en'>
	<head>
<!-- Required meta tags -->
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <title><?php echo $title; ?></title>
	<link rel='icon' type='image/x-icon' href='<?php echo $root; ?>images/favicon-index.ico?v=2' />
<!-- Bootstrap CSS
	<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css' integrity='sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO' crossorigin='anonymous'> -->
	<!-- Sass Bootstrap override -->
	<link rel='stylesheet' href='<?php echo $root; ?>css/main.css' >
<!-- custom css -->
	<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' />	
    <link rel='stylesheet' href='<?php echo $root; ?>css/sticky-footer.css' >
    <link rel='stylesheet' href='<?php echo $root; ?>css/icheck-bootstrap.min.css' > 
	<link rel='stylesheet' href="<?php echo $root; ?>css/bootstrap-switch.css?v=2" > 

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
<!--    <script src='https://code.jquery.com/jquery-3.3.1.slim.min.js' integrity='sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo' crossorigin='anonymous'></script> -->
<!--	<script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script> -->
	<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js' integrity='sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy' crossorigin='anonymous'></script>
	
	<script src="<?php echo $root; ?>Inputmask-5.x/dist/jquery.inputmask.js"></script>		
	<script src="<?php echo $root; ?>js/bootstrap-switch.js"></script> 	

	<!-- smartresize js for responsive charts -->
	<script type='text/javascript' src='<?php echo $root; ?>js/jquery.debouncedresize.js'></script>
	<script type='text/javascript' src='<?php echo $root; ?>js/jquery.throttledresize.js'></script> 

	<!-- Google Charts API -->
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script> 
	
	<!-- Ajax for function okToPrintAnother() -->
	<script src="<?php echo $root; ?>js/ajax.js"></script> 	

	</head>
	
<?php
}

function paintBanner() {
	global $control;
	
// logo	
	echo "
	<div class='container-fluid p-3 mb-0' style='background-color:#FF8628;'>
		<div class='row'>
			<div class='col-sm'>		
				<img class='img-fluid' style='height:75px;' src='images/logo-light.png' />\n";
				if ($control['isTraining'])
					echo "<span><b>*** FOR TRAINING ONLY ***</b></span>\n";
	echo "			
			</div>	
			<div class='col-sm'>\n";
			adminInfo();

// user menu		
	echo "
				<nav class='navbar navbar-expand-lg navbar-dark justify-content-end' style='background-color:#FF8628;'>
					<div class='collapse navbar-collapse' style='z-index:5000;' id='navbarNavDropdown'>
					<ul class='navbar-nav ml-auto'>
						<li class='nav-item dropdown'>
						<a class='nav-link dropdown-toggle text-white' href='#' id='navbarDropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
						<i class='fa fa-user pr-2' aria-hidden='true'></i>Hi $control[hostFName] !
						</a>
						<div class='dropdown-menu dropdown-menu-right text-left' aria-labelledby='navbarDropdownMenuLink'>
						<a class='dropdown-item' href='security.php?hhID=$control[hhID]&tab=users&id=$control[users_id]'>edit account</a>\n";
						
						if ($control['changepantry_browse'])
							echo "<a class='dropdown-item' href='tools.php?hhID=$control[hhID]&tab=change'>change pantry</a>\n";
						
						echo "
						<a class='dropdown-item' href='signout.php'>sign out</a>
						</div>
						</li>
					</ul>
					</div>
				</nav>";
				
echo "
			</div>
		</div>";		

	echo "
	</div>\n";
}	

function paintDocBanner() {
	
// logo	
	echo "
	<div class='container-fluid p-3 mb-0' style='background-color:#FF8628;'>
		<div class='row'>
			<div class='col-sm'>		
				<img class='img-fluid' style='height:75px;' src='../images/logo-light.png' />
			</div>	
		</div>
	</div>\n";
}	


function adminInfo() {
	global $control;
	
//	if ($control['access_level'] == 1) {
	if ($pantries=getPantryRow( $control['db'], $control['users_pantry_id'] ))
		$name = $pantries['name'];
	else
		$name = "";
	echo "<div class='mb-0 mr-2 p-1 text-right'><b> $name</b></div>";


//	}	
		
}