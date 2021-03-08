/**
  * okToPrintAnother( hhID )
  *
  * use ajax to check MariaDB tables and display an alert for the following conditions when 
  * "Print Shopping List" is clicked:
  *
  *	1. Shopping list already printed in same day       
  *	2. Household must have a valid zip code before shopping list is printed
  *	3. All active members of household must have a valid date of birth
  * 4. A household member is also active in another household
  * 5. No active members in household.
  *  
  *
  * 9-30-2019: fix for Apple Safari browser.		-mlr
  *
  */
function okToPrintAnother( hhID ) {

	var retval = false;
	
	$.ajax({
		async: false,		
		type: "get",
		url: "alreadyprinted.php?hhID=" + hhID,
		success: function(data) {
			
			household = $.parseJSON(data);

			if ( household.alreadyshopped ) {
				ConfirmMsg = "A shopping list has already printed for the household today. Ok to print another?";
				retval = confirm( ConfirmMsg );

			} else if ( household.ziperror ) {
				alert(household.ziperror);	
				retval=false;
				
			} else if ( household.doberror ) {
				alert(household.doberror);	
				retval=false;	

			} else if ( household.activeinanother ) {
				alert(household.activeinanother);	
				retval=false;	

			} else if ( household.noactive ) {
				alert(household.noactive);	
				retval=false;					
				
			} else 
				retval=true;
		}
	}); 

// 9-30-2019: fix for Apple Safari browser - AddHistory.php now called via 'GET' method in HouseholdProfile.php,
//		don't submit here.			-mlr 	
//	if (retval)	
//		document.getElementById("printShoppingList").submit(); 

	return retval;
}		