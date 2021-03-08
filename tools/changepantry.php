<?php
/**
 * tools/changepantry.php
 * written: 10/30/2020
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

function changePantryForm() {
	global $control;
	
	$seeAll=0;
	if ($control['access_level'] == 1)
		$seeAll=1;	
?>	
	<form method='post' action='tools/pantrycookie.php'> 
		<input type= 'hidden' name= 'hhID' value= '<?php echo $control['hhID']; ?>'>
	
		<div class="container-fluid bg-gray-2 p-3"> 
			<center>
			<div class='bg-gray-2 p-3 m-3 w-50 border border-dark'> 

				<div class='form-group text-left'>
					<label>Select Pantry</label>			
					<?php selectPantry( "users_pantry_id", $control['users_pantry_id'], $seeAll, 0, 0 ); ?>
				</div>	
				
				<div class='mt-3 text-center'>
					<button class='btn btn-primary my-2 my-sm-0 mr-sm-2 text-white' type='submit' name='apply'>Apply</button>	
				</div>					
				
			</div> 
			</center>
		</div>
		
	</form>
<?php	
}

?>