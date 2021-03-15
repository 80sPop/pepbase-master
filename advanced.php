<?php
/**
 * advanced.php
 * written: 5/6/2020
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

function advancedSearchForm() {
	global $control;

	$firstname='';
	$lastname='';
	$address="";
	$streetnum='';
	$streetname='';
	$apartmentnum="";	
	$city='';
	$county='';
	$state='';
	$zip="";
	$zip_five='';
	$zip_four='';
	$email='';
	$phone1='';
	$phone2='';
	$errCode=0;
	
    if ( isset($_GET['errCode']) ) {
	    $errCode=$_GET['errCode'];
	    displayErr($errCode);
	}

?>
<div class="container p-3">
	<div class="card">
	  <h5 class="card-header bg-gray-4 text-center">Advanced Search</h5>
	  <div class="card-body bg-gray-2">
	  
	<form method='post' action='households.php'>  	
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>FIRST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="firstname" id="firstname" ></div>		
			</div>	
		</div>
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>LAST NAME:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="lastname" id="lastname" ></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>MIDDLE INITIAL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="initial" id="initial" ></div>
			</div>	
		</div>				
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>DATE OF BIRTH:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="date" class="form-control" name="dob" id="dob" ></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ADDRESS:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="address" id="address" ></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>APT NUMBER:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="apartmentnum" id="apartmentnum" ></div>
			</div>	
		</div>		

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>CITY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="city" id="city" ></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>COUNTY:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="county" id="county" ></div>
			</div>	
		</div>			

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>STATE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><?php SelectState('state',''); ?></div>
			</div>	
		</div>	
		
		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>ZIP CODE:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="zip" id="zip" ></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>EMAIL:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="email" id="email" ></div>
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right mb-1"><label class='pt-2'>PHONE 1:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group mb-1"><input type="text" class="form-control" name="phone1" id="phone1" ></div> 
			</div>	
		</div>	

		<div class="form-row">
			<div class="col-5">
				<div class="form-group text-right"><label class='pt-2'>PHONE 2:</label></div>
			</div>	
			<div class="col-3">
				<div class="form-group"><input type="text" class="form-control" name="phone2" id="phone2" ></div>
			</div>	
		</div>					

		<input type= 'hidden' name= 'tab' value= 'profile'>
		<div class='text-center'>
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='search'>Search</button>	
			<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='cancel'>Cancel</button>
		</div>	
		</form>		  

	  </div>
	</div>
</div>

<?php		
}
?>