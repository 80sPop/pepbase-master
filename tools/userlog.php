<?php
/**
 * tools/userlog.php
 * written: 9/7/2020
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

function listUserLog() {
	global $control;
	
	if ($control['field'] == "shopping_date")
		$order="shopping_date $control[order], shopping_time $control[order], id $control[order]";
	else
		$order="$control[field] $control[order]";		
	
	$link= $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=definitions&add=1";
	$sql = "SELECT * FROM user_log ORDER BY $order";
			
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();				
	$result = $stmt->fetchAll();		
?>	
	<div class="container-fluid bg-gray-2"> 	
	
		<div class='row p-3'>
			<div class='col-sm text-right'><?php doUserLogCount(); ?></div>			
		</div>		
	
		<table class='table mb-2'>
<?php
			doUserLogHeadings();			
			foreach($result as $row) {	
				echo "
				<tr>
				<td class='border border-dark bg-gray-3 p-1'>$row[date_time]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[user_id]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[pantry_id]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[db_table]</td>
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[table_id]</td>	
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[household_id]</td>					
				<td class='border border-dark border-right-0 bg-gray-3 p-1'>$row[action]</td>	
				<td class='border border-dark bg-gray-3 p-1'>$row[shopping_date] $row[shopping_time]</td>		
				</tr>";	
			}	 
?>		
		</table>
		<div class='p-2'>&nbsp;</div>
	</div>
	
<?php	
}

function doUserLogCount() {
	global $control;
	
	$sql = "SELECT date_time FROM user_log";
	$stmt = $control['db']->prepare($sql);
	$stmt->execute();	
	$total = $stmt->rowCount();		
	
	echo "<b>" . number_format($total) . "</b> entries in user log."; 
}	

function doUserLogHeadings() {
	global $control;
	
	$link = $_SERVER['PHP_SELF'] . "?hhID=$control[hhID]&tab=userlog";

	if ($control['order'] == "asc")
		$link .= "&order=desc";
	else
		$link .= "&order=asc";	

	$tCarrot="";	
	$uCarrot="";		
	$pCarrot="";	
	$dbCarrot="";		
	$tiCarrot="";
	$hiCarrot="";	
	$aCarrot="";		
	$iCarrot="";	

	$direction = "fa-sort-down";
	if ( $control['order'] == "asc" ) 	
		$direction = "fa-sort-up";

	if ($control['field'] == "date_time")
		$tCarrot="<i class='fa $direction pl-2 align-middle'></i>";
	elseif ($control['field'] == "user_id")
		$uCarrot="<i class='fa $direction pl-2 align-middle'></i>";	
	elseif ($control['field'] == "pantry_id")
		$pCarrot="<i class='fa $direction pl-2 align-middle'></i>";	
	elseif ($control['field'] == "db_table")
		$dbCarrot="<i class='fa $direction pl-2 align-middle'></i>";			
	elseif ($control['field'] == "table_id")
		$tiCarrot="<i class='fa $direction pl-2 align-middle'></i>";	
	elseif ($control['field'] == "household_id")
		$hiCarrot="<i class='fa $direction pl-2 align-middle'></i>";			
	elseif ($control['field'] == "action")
		$aCarrot="<i class='fa $direction pl-2 align-middle'></i>";		
	elseif ($control['field'] == "ip_address")
		$iCarrot="<i class='fa $direction pl-2 align-middle'></i>";			
	
	echo "
	<thead>
	<tr>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=date_time'>Time</a>$tCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=user_id'>User ID</a>$uCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=pantry_id'>Pantry ID</a>$pCarrot</th>
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=db_table'>Table</a>$dbCarrot</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=table_id'>Table ID</a>$tiCarrot</th>	
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=household_id'>HH ID</a>$hiCarrot</th>		
	<th class='border border-dark border-right-0 bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=action'>Action</a>$aCarrot</th>	
	<th class='border border-dark bg-gray-4 p-1'><a class='text-dark' style='text-decoration:underline;' href='$link&field=shopping_date'>Shopping Date</a>$iCarrot</th>		
	</tr>
	</thead>";	
}
?>