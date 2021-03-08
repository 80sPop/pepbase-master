<?php
/**
 * common_vars.php
 * written: 9/26/2020
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

$errMsg[1] = "Please enter a valid password.";
$errMsg[2] = 'Username is already registered.';
$errMsg[3] = 'Please enter a valid sign-in name.';
$errMsg[4] = 'Must enter a password.';
$errMsg[5] = 'Ye do err, for thou may not delete thyself.';
$errMsg[6] = 'Access level name already exists.';
$errMsg[7] = 'Ye do err, for thou may not delete thine own access level.';
$errMsg[8] = 'Insufficient access level. You are not authorized to view this page.';
$errMsg[9] = 'Ye do err, for thou may not delete thine own pantry.';
$errMsg[10] = 'Selected primary shopper is under the age of 12.';
$errMsg[11] = 'Error in members table. Selected primary shopper not found.';
$errMsg[12] = "Ye do err, for thou may not delete thine household's primary shopper.";
$errMsg[13] = "Each household must have at least one primary shopper.";
$errMsg[14] = "You have entered the address of an existing household.";
$errMsg[15] = "Assigned access level exceeds current user's access level.";
$errMsg[16] = "Enter at least one household member";
$errMsg[17] = "First and last name are required fields.";
// 
$errMsg[18] = "Must enter two valid dates for range.";
$errMsg[19] = "Please enter a valid date.";

// 10-13-2015: version 3.6.0 update - d.o.b. now entered during new household registration.	-mlr
$errMsg[20] = "Please enter a valid date of birth.";
$errMsg[21] = "Primary shopper must be at least 16 years of age.";

// 3-19-2019: 3.8.1 update.		-mlr
$errMsg[22] = "Please enter a valid household ID.";
$errMsg[23] = "Start date must occur before end date.";

// 5-1-2019: 3.9.0 update.		-mlr
$errMsg[24] = "Email address is not valid.";
$errMsg[25] = "Email is already registered to another account.";
$errMsg[26] = "The current password is incorrect.";
$errMsg[27] = "New password and confirmed password do not match.";
$errMsg[28] = "New password is not valid.";
$errMsg[29] = "Please enter staff first name.";
$errMsg[30] = "Your account requires activation.";
$errMsg[31] = "Please enter a valid sign-in name and password.";
$errMsg[32] = "Email does not match an existing account. Try again, or contact the system Administrator.";
$errMsg[33] = "Your password reset token has expired.";
$errMsg[34] = "There was an error processing your password reset token.";
$errMsg[35] = "Sendmail transport error. Try again, or contact the system Administrator.";

// 6-20-2019: 3.9.1 update.		-mlr
$errMsg[36] = "Zip code does not match city.";
$errMsg[37] = "Zip code does not match county.";
$errMsg[38] = "Zip code does not match state.";
$errMsg[39] = "Zip code is not valid.";
$errMsg[40] = "Start date is not valid.";

// 8-24-2019: 3.9.2 update.		-mlr
$errMsg[41] = "All active household members must have a valid date of birth.";

// 9-11-2019: 3.9.3 update.		-mlr
$errMsg[42] = "Middle initial is a required field.";
$errMsg[43] = "Address is a required field. For households with no address, use 'Homeless' or your pantry's address.";

// 12-18-2019: 3.9.4 update.	-mlr
$errMsg[44] = "Administrator accounts my not be deleted.";
$errMsg[45] = "An Administrator may not edit another Administrator's account.";

// 12-18-2019: 3.9.4.1 update.		-mlr
$errMsg[46] = "Voting is reserved for Coordinators only.";
$errMsg[47] = "Please sign in before voting.";
$errMsg[48] = "Server array problem. Please contact the website Administrator.";

$errMsg[49] = "City is a required field.";
$errMsg[50] = "County is a required field.";
$errMsg[51] = "Zip code is a required field.";

// PEPBASE 4 ERROR MESSAGES
$errMsg[52] = "Proof of Identity date is not valid.";
$errMsg[53] = "Proof of Residence date is not valid.";
$errMsg[54] = "Phone number is not valid.";
$errMsg[55] = "Phone number is not valid.";
$errMsg[56] = "Household must have a Primary Shopper.";
$errMsg[57] = "Primary Shopper may not be deleted.";
$errMsg[58] = "Amount field must be numeric.";
$errMsg[59] = "Duration field(s) must be numeric.";
$errMsg[60] = "A product name must be entered for each supported language.";
$errMsg[61] = "A description must be entered for each size or type.";
$errMsg[62] = "Shelf and bin must be numeric.";

$errMsg[63] = "Pantry name is a required field.";
$errMsg[64] = "Abbreviation is a required field.";
$errMsg[65] = "Address is a required field.";
$errMsg[66] = "First name is a required field.";
$errMsg[67] = "Last name is a required field.";
$errMsg[68] = "Inactive date is not valid.";

$errMsg[69] = "Username is a required field.";
$errMsg[70] = "The Administrator password is incorrect.";

$errMsg[71] = "Access Level name is a required field.";
$errMsg[72] = "New access level may not preceed the highest access level.";

$errMsg[73] = "Language is a required field.";
$errMsg[74] = "Shelter name is a required field.";
$errMsg[75] = "Field must be numeric.";
$errMsg[76] = "Measure is a required field.";
$errMsg[77] = "Container is a required field.";
$errMsg[78] = "Amount received is greater than amount in-stock.";

$errMsg[79] = "There is an error in the Database setup. Check spelling, and make sure the user has CREATE DATABASE privileges on the MySQL database.";
$errMsg[80] = "All fields are required.";
$errMsg[81] = "Unable to create configuration file. Make sure you have write privileges on folder.";
$errMsg[82] = "Amount in-stock is greater than amount approved.";

/* define report colors */

	define('REPORT_BACK_COLOR','#DDDDDD');
	define('REPORT_TEXT_COLOR','#000000');	
	define('REPORT_TITLE_BACK','#CCCCCC');	

/* define date limit constants for age calculation */

	$todayYear = date('Y');
	$todayMonth = date('m');
	$todayDay = date('d');

/* AGES 65+  */

    $upperYear = $todayYear - 65;
    $upperLimit = $upperYear.'-'.$todayMonth.'-'.$todayDay;
	define('UPPER_LIMIT_ELDERLY', $upperLimit);	
	define('LOWER_LIMIT_ELDERLY', '0000-00-00');	

/* ADULTS 18-65 */

    $upperYear = $todayYear - 18;
    $upperLimit = $upperYear.'-'.$todayMonth.'-'.$todayDay;
    $lowerYear = $todayYear - 66;
    $lowerLimit = $lowerYear.'-'.$todayMonth.'-'.$todayDay;
	define('UPPER_LIMIT_ADULT', $upperLimit);	
	define('LOWER_LIMIT_ADULT', $lowerLimit);		
  
/* TEENS 12-17 */

    $upperYear = $todayYear - 12;
    $upperLimit = $upperYear.'-'.$todayMonth.'-'.$todayDay;
    $lowerYear = $todayYear - 18;
    $lowerLimit = $lowerYear.'-'.$todayMonth.'-'.$todayDay;
	define('UPPER_LIMIT_TEEN', $upperLimit);	
	define('LOWER_LIMIT_TEEN', $lowerLimit);	

/* YOUTH 4-11 */

    $upperYear = $todayYear - 4;
    $upperLimit = $upperYear.'-'.$todayMonth.'-'.$todayDay;
    $lowerYear = $todayYear - 12;
    $lowerLimit = $lowerYear.'-'.$todayMonth.'-'.$todayDay;
	define('UPPER_LIMIT_YOUTH', $upperLimit);	
	define('LOWER_LIMIT_YOUTH', $lowerLimit);	
	
/* INFANTS 0-3 */

    $upperLimit = $todayYear.'-'.$todayMonth.'-'.$todayDay;
    $lowerYear = $todayYear - 4;
    $lowerLimit = $lowerYear.'-'.$todayMonth.'-'.$todayDay;
	define('UPPER_LIMIT_INFANT', $upperLimit);	
	define('LOWER_LIMIT_INFANT', $lowerLimit);
	
	
// 9-17-14: version 3.5.2 upgrade - define print control constants.		-mlr
	define('PAGE_WIDTH', 660);
	define('PAGE_HEIGHT', 865);	
	
	
/* Control Vars from PEP2 */

$customTextMsg = array(
                      'register_household_header1'       => "Enter basic information for new household"
                                                            ,
                      'register_household_header2'       => "Enter initial shopping information for new household"
                                                            ,
                      'register_household_form'          => "<div class=highlight>"                                                                      .
                                                            "Please enter as much information as possible in the fields below.  At the very "        .
                                                            "least, the full name of the guest is required.  Entering additional information helps " .
                                                            "ensure that there isn't already a record for this household, and will save time "       .
                                                            "later on, since the information will eventually need to get entered."                   .
                                                            "</div>"
                                                            ,
                      'register_household_submit'        => "<div class=highlight>"                                                                      .
                                                            "Click the <u>Submit</u> button to check for duplicate entries, and bring up a "         .
                                                            "form with potential matches to the entered data.  If no potential duplicates are "      .
                                                            "found, you will be able to continue entering the data needed to create an initial "     .
                                                            "shopping list for this guest."                                                          .
                                                            "</div>"
                                                            ,
                      'register_household_followup'      => "<div class=highlight>"                                                                     .
                                                            "In order to generate an initial shopping list, more information about the household "   .
                                                            "is needed.  Please ask the guest for information about how many adults, teens, youth "  .
                                                            "and children are in the household.  In addition, please ask whether there are any "     .
                                                            "special needs, such as allergies or incontinence, which should be taken into account. " .
                                                            "<br>Also, if possible, verify the identity and address of the guest.  If this "         .
                                                            "can't be done this visit, it can be done at a later time.  You may also add to, or "    .
                                                            "correct, information in the main household info section."                               .
                                                            "</div>"
                                                            ,
                      'register_household_save'          => "<div class=highlight>"                                                                      .
                                                            "Click the <u>Print/Save</u> button to save the entered information, print a guest "     .
                                                            "registration form, and generate an initial shopping list for the new guest."            .
                                                            "</div>"
                                                            ,
                      'lookup_household_header1'         => "Enter search information for returning household guest"
                                                            ,
                      'lookup_household_header2'         => "Update information for returning household guest"
                                                            ,
                      'lookup_household_form'            => "<div class=highlight>"                                                                  .
                                                            "Please enter enough information to identify the returning guest.  Although a search "   .
                                                            "can be performed on any of the following fields, you should try and use the first "     .
                                                            "and last name of the guest, along with the street number, street name, or phone "       .
                                                            "number.  The more/better information you can enter, the fewer possible matches "        .
                                                            "you will need to select from.  If no matches (or no correct matches) can be located, "  .
                                                            "you'll have the option to change (or clear out) some of the fields, to broaden your "   .
                                                            "search parameters."                                                                     .
                                                            "</div>"
                                                            ,
                      'lookup_household_submit'          => "<div class=highlight>"                                                                  .
                                                            "Click the <u>Submit</u> button to search for records matching this information "        .
                                                            "and to display them for you to select from.  If no matches are found, you will be "     .
                                                            "able to change your search, or switch over to entering this as a new household "        .
                                                            "instead of a returning guest."                                                          .
                                                            "</div>"
                                                            ,
                      'lookup_household_submit_nomatch'  => "<div class=warning>"                                                             .
                                                            "No matches to the entered household information were found.  </div>"                    .
                                                            "<div class=highlight>"                                                                  .
                                                            "Please confirm that the "   .
                                                            "information is correct (be careful with the spelling of names).  If you have entered "  .
                                                            "a lot of information, consider blanking out some of it to broaden the search "          .
                                                            "parameters.  If you're certain that the data is correct, but still can't locate a "     .
                                                            "household record, apologize for the problem, and click on the <u>Register As New "      .
                                                            "Guest</u> submission button instead.  This will let you treat the guest as if "     .
                                                            "he or she is a new guest to the pantry.  We'll resolve the issue later, after the "     .
                                                            "guest has been helped out."                                                             .
                                                            "</div>"
                                                            ,
                      'lookup_household_followup'        => "<div class=highlight>"                                                                      .
                                                            "If needed, you can override the values for household size and composition for this "    .
                                                            "shopping trip.  If you do, please ensure that you print out an update-registration "    .
                                                            "form so the updated information can be gathered for later entry.  Also, if there are "  .
                                                            "any changes to the guest's address, phone number, etc, please print out a form so the " .
                                                            "records can be updated.<br>If it's convenient, changes and corrections can be made "    .
                                                            "directly on this screen, and will be reflected both on the update-registration form "   .
                                                            "that's printed, and in the saved record for the guest."                                 .
                                                            "</div>"
                                                            ,
                      'lookup_household_save'            => "<div class=highlight>"                                                                      .
                                                            "Click the <u>Save Household</u> button to save the entered information and generate "   .
                                                            "a shopping list.  Click the <u>Print/Save</u> button print an update-registration "     .
                                                            "form in addition to saving any changes, and generating a shopping list."                .
                                                            "</div>"
                                                            ,
                      'find_preexisting_household'       => "<div class=highlight>"                                                                      .
                                                            "Please check with the guest to see whether he or she belongs to any of the already "    .
                                                            "existing household records listed above.  If so, select the record, and click on "      .
                                                            "<u>Use Selected</u> to go to the 'returning guest' section, make any changes that are " .
                                                            "needed, and print a shopping list for the guest."                                       .
                                                            "</div>"
                                                            ,
                      'review_shopping_list'             => "<div class=highlight>"                                                                      .
                                                            "You can override the computed quantity values for any of the shopping items listed "    .
                                                            "below.  Clicking on the '???' column will pop up a window with an explanation for "     .
                                                            "why the quantity is as calculated.  (E.g. the product is for adult men only, and the "  .
                                                            "household has no adult men in it.)  When you are done, Click the <u>Print Shopping List</u> "      .
                                                            "button to save the revised values, and print a shopping list for the guest."            .
                                                            "</div>"
                                                            ,
                      'update_shopping_list_main'        => "<div class=highlight>"                                                                      .
                                                            "Enter the pantryID and shopping date from the completed, fulfilled shopping list. "     .
                                                            "Then, click on the 'Fetch Shopping Records' button to locate, display, and update the " .
                                                            "shopping data for that household and shopping date."                                                                   .
                                                            "</div>"
                                                            ,
                      'update_shopping_list_prods'       => "<div class=highlight>"                                                                      .
                                                            "Fill in the quantity-requested and quantity-received values for each of the products. " .
                                                            "If most (or all) of the approved product amounts were requested by the guest, you can " .
                                                            "click on the 'Copy-Approved' button at the top, and copy all the approved quantities "  .
                                                            "into the quantity-requested column.  Similarly, you can click on the 'Copy-Requested' " .
                                                            "button to transfer the requested amounts over to the quantity-received column.  "       .
                                                            "In either case, you can still modify these values (useful, if just one or two are "     .
                                                            "different) before saving the information.  It generally should <i>not</i> be needed, "  .
                                                            "but if the shopping list indicates a product was manually approved, you can choose a "  .
                                                            "new product to add to the list, and fill in the values.<br>"                            .
                                                            "Important: You <i>must</i> click on the 'Save Shopping Data' button at the bottom "     .
                                                            "of the page to save the updated shopping data.  If you simply choose a new pantry-ID "  .
                                                            "or shopping date, all your updates will be lost."                                       .
                                                            "</div>"
                                                            ,
                      'ADMIN_register_household_header1' => "Enter basic information for new household"
                                                            ,
                      'ADMIN_register_household_header2' => "Enter full member information for new household"
                                                            ,
                      'ADMIN_register_household_form'    => "<div class=highlight>"                                                                .
                                                            "Please enter the household information in the fields below.  If you suspect that an "   .
                                                            "existing household record is already present, you might want to enter just some "       .
                                                            "fields, or partial name entries, to perform a search for potential matches."            .
                                                            "</div>"
                                                            ,
                      'ADMIN_register_household_followup' => "<div class=highlight>"                                                               .
                                                            "Please enter the household information in the fields below.  If it's available, enter " .
                                                            "the member information as well.  If it is not available at this point, on the next "    .
                                                            "visit, override values can be used to generate shopping lists.  If you need to enter "  .
                                                            "more members than there are rows, click on the <u>Add Members</u> button to add some "  .
                                                            "new entry lines."                                                                       .
                                                            "</div>"
                                                            ,
                      'ADMIN_register_household_submit'  => "<div class=highlight>"                                                                .
                                                            "Click the <u>Submit</u> button to check for duplicate entries, and bring up a "         .
                                                            "form with potential matches to the entered data.  If no potential duplicates are "      .
                                                            "found, you will be able to continue entering the data for this household."              .
                                                            "</div>"
                                                            ,
                      'ADMIN_register_household_save'    => "<div class=highlight>"                                                                .
                                                            "Click the <u>Save Household</u> button to save the entered information."                .
                                                            "</div>"
                                                            ,
                      'ADMIN_lookup_household_header1'   => "Enter search information for an existing household record"
                                                            ,
                      'ADMIN_lookup_household_header2'   => "Update information for an existing household record"
                                                            ,
                      'ADMIN_lookup_household_form'      => "<div class=highlight>"                                                                .
                                                            "Enter enough information to identify the existing record.  If you have the pantry ID "  .
                                                            "number (from a registration form, perhaps) that is the ideal identifier."               .
                                                            "</div>"
                                                            ,
                      'ADMIN_lookup_household_submit'    => "<div class=highlight>"                                                                .
                                                            "Click the <u>Submit</u> button to search for records matching this information "        .
                                                            "and to display them for you to select from."                                            .
                                                            "</div>"
                                                            ,
                      'ADMIN_lookup_household_submit_nomatch'   => "<div class=warning>"                                                       .
                                                            "No matches to the entered information were found.  </div>"                              .
                                                            "<div class=highlight>"                                                                  .
                                                            "Please confirm that the "                                                               .
                                                            "information is correct (be careful with the spelling of names).  If you have entered "  .
                                                            "a lot of information, consider blanking out some of it to broaden the search "          .
                                                            "parameters.  You can use a '%' as a wildcard match at the start (or in the middle) of " .
                                                            "most string fields.  Phone numbers are also string fields, but are already set to "     .
                                                            "match with a wildcard at the start.<br>"                                                .
                                                            "If you're sure the information is correct, and still can't find a match, you can "      .
                                                            "have this information considered a new household, instead."                             .
                                                            "</div>"
                                                            ,
                      'ADMIN_lookup_household_followup'  => "<div class=highlight>"                                                                .
                                                            "Make any changes, corrections, or additions to the household record."                   .
                                                            "</div>"
                                                            ,
                      'ADMIN_lookup_household_save'      => "<div class=highlight>"                                                                .
                                                            "Click the <u>Save Household</u> button to save the entered information."                .
                                                            "</div>"
                                                         ,
                      'ADMIN_find_preexisting_household' => "<div class=highlight>"                                                                .
                                                            "Find the household record which matches with the household you're searching for."       .
                                                            "</div>"
                                                         ,
                      'ADMIN_select_product'             => "Select the product to edit, or choose <u>New Product</u> to create a new entry."
                                                         ,
                      'ADMIN_select_productdisabled'     => "You are currently editing a product record.  You need to <u>save</u> or "             .
                                                            "<u>abandon</u> it before you can select a different record to work on."
                                                            ,
                      'regform_header'                   => array()
                                                            ,
                      'certification'                    => array()
                                                            ,
                      'print_shopping_hdr'               => array()
                                                            ,
                      'print_shopping_inst'              => array()
                                                            ,
                      'print_shopping_maxqty'            => array()
                     );

#######################################################################
# 5-13-11: version 2.4 updates. Due to increased participation, the   #
#          Pepartnershp affiliate is now refered to as 'this pantry'  #
#          rather than 'Zion Church' pantry. Also, Spanish, Hmong,    #
#          and French tranaslations were added to the voluntary       #
#          participation clause. French translation added to "I       #
#          hereby certify that:" statement.  -mlr                     #
#######################################################################

// 6-28-14: version 3.5.1 update - re-style $customTextMsg for top paragraph of registration form.		-mlr
$customTextMsg['regform_header'][1] = "<h3 style='margin:8px;'>PERSONAL ESSENTIALS PANTRY REGISTRATION FORM</h3>";
$customTextMsg['regform_header'][2] = "<h3 style='margin:8px;'>ESENCIALES DE PERSONAL FORMULARIO DE INSCRIPCIÓN DESPENSA</h3>";
$customTextMsg['regform_header'][3] = "<h3 style='margin:8px;'>PERSONAL ESSENTIALS PANTRY REGISTRATION FORM</h3>";
$customTextMsg['regform_header'][4] = "<h3 style='margin:8px;'>ACCESSOIRES PERSONNELS FORMULAIRE D'INSCRIPTION OFFICE</h3>";

$customTextMsg['regform_inst1'][1] = "Participation in this Pantry is voluntary. Personally identifiable information collected is 
									  required for participation and will be used for that purpose only.";		
$customTextMsg['regform_inst1'][2] = "La participación en esta Despensa es voluntaria. La información personalmente
                                      identificable completa es requerida para la participación y será utilizada para ese propósito sólo."; 
$customTextMsg['regform_inst1'][3] = "Participation in this Pantry is voluntary. Personally identifiable information collected is 
									  required for participation and will be used for that purpose only.";	
$customTextMsg['regform_inst1'][4] = "La participation dans ce Garde-manger est volontaire. Les informations personnellement
                                      identifiables complètes sont exigées pour la participation et seront utilisées pour ce but seulement.";
									  
$customTextMsg['regform_inst2'][1] =  "Please Print All Information";									  
$customTextMsg['regform_inst2'][2] =  "Imprima por favor toda la información"; 
$customTextMsg['regform_inst2'][3] =  "Please Print All Information";	
$customTextMsg['regform_inst2'][4] =  "S'il vous plaît imprimer toutes les informations";	
// end 3.5.1 updates			  
									  

// 1-8-2018: version 3.6.3 update - remove garbage characters for php 5.6 server update.	-mlr									  
// 4-7-13: version 3.4.3 update - change to Second Harvest Food Bank's eligibility requirements 
$customTextMsg['certification'][1]  = "<center><b>Welcome to our Pantry!</b></center><font size=-2><ul>"                                                   .
                                      "<li>It doesn't matter who you are or where you are from.  We welcome persons of any race, belief, " . 
									  "sexual orientation, gender or disability."															.
                                      "<li>We accept that you are here because of financial need. We use Second Harvest Food Bank's "		.
									  "eligibilty requirements, or 200% of the federally approved HHS (Health and Human	Services "			.
									  "Department) poverty guidelines.</li>"																.
                                      "<li>The products we give out are for you and anyone living in your house.  We give out products " .
									  "based on the number of people in your household--on what is appropriate for each one (for example, " . 
									  "no deodorant for babies!), and how long the product will last. "									 .
                                      "<li>Anyone in your family who is age 18 or over can pick up items for your household. Any "      . 
									  "products your household gets will be added to your shopping history."                             .
                                      "<li>You are allowed to pick up products for your household only.  Exceptions may be made (for "   .
									  "example, you may be able to pick up for a disabled person who can't come to the Pantry in "       .
									  "person)."                                           										         .
                                      "<li>We do all we can to make sure that the products we give you are safe.  However, under the "    .
									  "Good Samaritan Law, we are not legally responsible for harm or damage that may happen as you "  .
									  "use these products."                                                                              .
                                      "<li>We expect that you will use this Pantry honestly and reasonably. If we learn that you have "  .
									  "lied or cheated, you may be barred from using the Pantry."                                        .
                                      "</ul></font>\n";

$customTextMsg['certification'][2]  = "<center><b>Bienvenido a nuestra despensa!</b></center><font size=-2><ul>"                                          .                                        
                                      "<li>No importa quién eres o de dónde eres. Damos la bienvenida a personas de cualquier raza, "    .
									  "creencia, orientación sexual, género o discapacidad."		     								  .
									  "<li>Aceptamos que usted está aquí por necesidad económica. Utilizamos los requisitos Second "	.
									  "Harvest Food Bank del ELEGIBILIDAD, o 200% de la aprobación federal del HHS (Health and Human "	.
									  "Services Department) nivel de pobreza.</li>"														.									  
									  "<li>Los productos que damos son para usted y cualquier persona que vive en su casa. Damos "       .
									  "productos basados ??en el número de personas en su hogar - en lo que es apropiado para cada uno " .
									  "(por ejemplo, no hay desodorante para los bebés!), Y por cuánto tiempo el producto va a durar."   .	
									  "<li>Cualquier persona en su familia que es la edad de 18 años puede recoger objetos para su hogar.".
									  " Todos los productos que su familia recibe será añadido a su historial de compras."               .
									  "<li>Usted está autorizado a recoger los productos para su hogar solamente. Se pueden hacer "      .
									  "excepciones (por ejemplo, usted puede ser capaz de recoger a una persona discapacitada que no "   .
									  "puede venir a la despensa en persona)." 															 .
									  "<li>Hacemos todo lo posible para asegurarse de que los productos que le dan son seguros. Sin "    .
									  "embargo, bajo la “Ley del Buen Samaritano”, que no son legalmente responsables por los daños y "  .
									  "perjuicios que puede suceder al utilizar estos productos."                                        .
									  "<li>Esperamos que usted utilice este despensa, honesta y razonablemente. Si nos enteramos de que" .
									  " usted ha mentido o engañado, se le puede prohibir el uso de la despensa."                        .
                                      "</ul></font>\n";									  

$customTextMsg['certification'][3]  = "<center><b>Kuv coglug hab qha tseeb has tas:</b></center><font size=-2><ul>"                                       .
                                      "<li>Kuv le nyaj hli tsuas yog muaj txwm le lossis tsawg dlua tsoomfwv qhov kws "                  .
                                      "    tsoomfwv tau pum zoo rua DCFS siv lug tswj qhov kev paabcuam nuav lawv le kuv "               .
                                      "    tsev tuabneeg kws tau sau tseg rua huv dlaim ntawv nuav."                                     .
                                      "<li>Txhua zag moog kaav khw, tsis has leejtwg yog tug tuaj kaav khw, yuav tsum "                  .
                                      "    sau npe rua huv qaab tug Tswv Tsev ntawm kuv tsev tuabneeg."                                  .
                                      "<li>Cov khoom yuav tau faib lawv le tsev tuabneeg coob lossis tsawg, yaam khoom "                 .
                                      "    hov thev ntev los luv, hab puas tshuav lawm."                                                 .
                                      "<li>Kuv yuav siv cov khoom noj lossis khoom siv kws tau txais nuav rua kuv tsev tuabneeg xwb."    .
                                      "<li>Kuv tso cai has tas Zion Evangelical Lutheran Church hab txhua tug tuabneeg kws faib cov "    .
                                      "    khoom nuav yuav tsis raug teebmeem vim kuv tau txais tej khoom nuav lug ntawm puab lug."      .
                                      "<li>Kuv totaub has tas ua hab qhas tsis ncaaj tej zag yuav raug tsis pub siv "                    .
                                      "    Zion Church Home/Personal Essentials Pantry rua yaav pegsuab."                                .
                                      "<li>Kuv tsuas yog muaj cai khaws khoom rua kuv tsev tuabneeg xwb, hab tsis yog "                  .
                                      "    muab rua phoojywg lossis ruabze."                                                             .
                                      "<li>Kevcai rua kev txais yuav hab kev koomteg rua qhov kev paabcuam nuav zoo ib "                 .
                                      "    yaam rua txhua tug tuabneeg, tsis has haiv tuabneeg dlaabtsis, tawv dlawb, "                  .
                                      "    tawv dlub, lossis tawv dlaaj, noob nyoog, quaspuj, quasyawg, lossis xiam oob qhab."           .
                                      "</ul></font>\n";

$customTextMsg['certification'][4]  = "<center><b>Bienvenue à notre garde-manger!</b></center><font size=-2><ul>"                                         .
									  "<li>Il n'a pas d'importance qui vous êtes ou d'où vous venez. Nous nous félicitons de personnes " .
									  "de toute race, de croyance, l'orientation sexuelle, le sexe ou le handicap."						 .
									  "<li>Nous acceptons que vous êtes ici parce que des besoins financiers. Nous utilisons les DCFS "  .
									  "fédéral approuvés (ministère des Services à l'enfance et la famille) les limites d'un guide, "    .
									  "mais  nous ne faisons pas vous prouver votre besoin."											 .
									  "<li>Les produits que nous leur donnons sont pour vous et toute personne vivant dans votre maison. " .
									  "Nous donnons des produits basés sur le nombre de personnes dans votre ménage - sur ce qui est "   .
									  "approprié pour chacun d'eux (par exemple, pas de déodorant pour les bébés!), Et combien de temps ".
									  "le produit va durer."               																 .
									  "<li>N'importe qui dans votre famille qui est âgé de 18 ans ou plus peuvent ramasser des objets "  .
									  "pour votre ménage. Tous les produits de votre ménage reçoit sera ajouté à votre historique de "   .
									  "shopping."																						 .
									  "<li>Vous êtes autorisé à prendre des produits pour votre ménage seulement. Des exceptions "       .
									  "peuvent être faites (par exemple, vous pourriez être en mesure de ramasser pour une personne "    .
									  "handicapée qui ne peut pas venir à l'office en personne)."										 .
									  "<li>Nous faisons tout notre possible pour s'assurer que les produits que nous vous donnons sont " .
									  "en sécurité. Toutefois, dans le cadre du “Samaritain bonne loi,” nous ne sommes pas légalement "   .
									  "responsable des dommages et préjudices qui peuvent se produire lorsque vous utilisez ces produits.".						 
									  "<li>Nous nous attendons à ce que vous allez utiliser ce garde-manger honnêtement et raisonnablement.".
									  "Si nous apprenons que vous avez menti ou triché, vous pouvez être interdit d'utiliser l'Pantry."  .
                                      "</ul></font>\n";									  


/* 6-26-14: version 3.5.1 upgrade - include language translation for all titles and instruction in registration form.	-mlr */

$customTextMsg['reg_lastname_hdr'][1]	= "last name";
$customTextMsg['reg_lastname_hdr'][2]	= "apellido";
$customTextMsg['reg_lastname_hdr'][3]	= "last name";
$customTextMsg['reg_lastname_hdr'][4]	= "nom";

$customTextMsg['reg_firstname_hdr'][1]	= "first name";
$customTextMsg['reg_firstname_hdr'][2]	= "nombre de pila";
$customTextMsg['reg_firstname_hdr'][3]	= "first name";
$customTextMsg['reg_firstname_hdr'][4]	= "prénom";

$customTextMsg['reg_household_id_hdr'][1]	= "household id";
$customTextMsg['reg_household_id_hdr'][2]	= "household id";
$customTextMsg['reg_household_id_hdr'][3]	= "household id";
$customTextMsg['reg_household_id_hdr'][4]	= "household id";

$customTextMsg['reg_address_hdr'][1]	= "street address (with apartment number)";
$customTextMsg['reg_address_hdr'][2]	= "dirección de la calle (con número de apartamento)";
$customTextMsg['reg_address_hdr'][3]	= "street address (with apartment number)";
$customTextMsg['reg_address_hdr'][4]	= "adresse (avec le numéro d'appartement)";

$customTextMsg['reg_city_hdr'][1]	= "city";
$customTextMsg['reg_city_hdr'][2]	= "ciudad";
$customTextMsg['reg_city_hdr'][3]	= "city";
$customTextMsg['reg_city_hdr'][4]	= "ville";

$customTextMsg['reg_county_hdr'][1]	= "county";
$customTextMsg['reg_county_hdr'][2]	= "condado";
$customTextMsg['reg_county_hdr'][3]	= "county";
$customTextMsg['reg_county_hdr'][4]	= "comté";

$customTextMsg['reg_state_hdr'][1]	= "state";
$customTextMsg['reg_state_hdr'][2]	= "estado";
$customTextMsg['reg_state_hdr'][3]	= "state";
$customTextMsg['reg_state_hdr'][4]	= "état";

$customTextMsg['reg_zip_hdr'][1]	= "zip code";
$customTextMsg['reg_zip_hdr'][2]	= "código postal";
$customTextMsg['reg_zip_hdr'][3]	= "zip code";
$customTextMsg['reg_zip_hdr'][4]	= "code postal";

$customTextMsg['reg_phone1_hdr'][1]	= "phone number (1)";
$customTextMsg['reg_phone1_hdr'][2]	= "número de teléfono (1)";
$customTextMsg['reg_phone1_hdr'][3]	= "phone number (1)";
$customTextMsg['reg_phone1_hdr'][4]	= "numéro de téléphone (1)";

$customTextMsg['reg_phone2_hdr'][1]	= "phone number (2)";
$customTextMsg['reg_phone2_hdr'][2]	= "número de teléfono (2)";
$customTextMsg['reg_phone2_hdr'][3]	= "phone number (2)";
$customTextMsg['reg_phone2_hdr'][4]	= "numéro de téléphone (2)";

$customTextMsg['reg_email_hdr'][1]	= "email";
$customTextMsg['reg_email_hdr'][2]	= "email";
$customTextMsg['reg_email_hdr'][3]	= "email";
$customTextMsg['reg_email_hdr'][4]	= "email";

$customTextMsg['reg_listmembers_hdr'][1]	= "List All Household Members <span style='font-size:0.8em;'>"	.
											  "(use reverse side for additional members, if needed)</span>";
$customTextMsg['reg_listmembers_hdr'][2]	= "Listar Todos los miembros del hogar <span style='font-size:0.8em;'>"	.
											  "(utilizar el reverso para miembros adicionales, si es necesario)</span>";	
$customTextMsg['reg_listmembers_hdr'][3]	= "List All Household Members <span style='font-size:0.8em;'>"	.
											  "(use reverse side for additional members, if needed)</span>";
$customTextMsg['reg_listmembers_hdr'][4]	= "Liste de tous les membres du ménage <span style='font-size:0.8em;'>"	.
											  "(Utiliser le verso pour les membres supplémentaires, si nécessaire)</span>";										  

$customTextMsg['reg_listmembers_fullname'][1] = "Full Name";
$customTextMsg['reg_listmembers_fullname'][2] = "Nombre Completo";
$customTextMsg['reg_listmembers_fullname'][3] = "Full Name";
$customTextMsg['reg_listmembers_fullname'][4] = "Nom et prénom";

$customTextMsg['reg_listmembers_dob'][1] = "Date of Birth";
$customTextMsg['reg_listmembers_dob'][2] = "Fecha de nacimiento";									  
$customTextMsg['reg_listmembers_dob'][3] = "Date of Birth";
$customTextMsg['reg_listmembers_dob'][4] = "Date de naissance";	

$customTextMsg['reg_listmembers_gender'][1] = "Gender";
$customTextMsg['reg_listmembers_gender'][2] = "Desconocido";
$customTextMsg['reg_listmembers_gender'][3] = "Gender";
$customTextMsg['reg_listmembers_gender'][4] = "Inconnu";

$customTextMsg['reg_listmembers_allergies'][1] = "Allergies?";
$customTextMsg['reg_listmembers_allergies'][2] = "Alergias?";
$customTextMsg['reg_listmembers_allergies'][3] = "Allergies?";
$customTextMsg['reg_listmembers_allergies'][4] = "Allergies?";

$customTextMsg['reg_listmembers_notes'][1] = "Notes (staff use only)";
$customTextMsg['reg_listmembers_notes'][2] = "Notas (uso personal)";
$customTextMsg['reg_listmembers_notes'][3] = "Notes (staff use only)";
$customTextMsg['reg_listmembers_notes'][4] = "Notes (utilisation de personnel)";

$customTextMsg['reg_req_size_hdg'][1] = "Household Size";
$customTextMsg['reg_req_size_hdg'][2] = "Tamaño de la Familia";
$customTextMsg['reg_req_size_hdg'][3] = "Household Size";
$customTextMsg['reg_req_size_hdg'][4] = "Taille des ménages";

$customTextMsg['reg_req_income_hdg'][1] = "Household Monthly Income";
$customTextMsg['reg_req_income_hdg'][2] = "Ingresos Mensuales del Hogar";
$customTextMsg['reg_req_income_hdg'][3] = "Household Monthly Income";
$customTextMsg['reg_req_income_hdg'][4] = "Revenu mensuel des ménages";

$customTextMsg['reg_req_income_int'][1] = "No more than";
$customTextMsg['reg_req_income_int'][2] = "No más de";
$customTextMsg['reg_req_income_int'][3] = "No more than";
$customTextMsg['reg_req_income_int'][4] = "Pas plus que";

$customTextMsg['reg_req_income_add'][1] = "Add $677 for each additional person";
$customTextMsg['reg_req_income_add'][2] = "Agregar $677 por cada persona adicional";
$customTextMsg['reg_req_income_add'][3] = "Add $677 for each additional person";
$customTextMsg['reg_req_income_add'][4] = "Ajouter $ 677 pour chaque personne supplémentaire";

$customTextMsg['reg_signature_hdg'][1] = "Signature:"; 
$customTextMsg['reg_signature_hdg'][2] = "Firma:";
$customTextMsg['reg_signature_hdg'][3] = "Signature:";									  
$customTextMsg['reg_signature_hdg'][4] = "Signature:";									  
									  
$customTextMsg['print_shopping_hdr'][1]    = "SHOPPING LIST - ";
$customTextMsg['print_shopping_hdr'][2]    = "LISTA DE COMPRAS - ";
$customTextMsg['print_shopping_hdr'][3]    = "SHOPPING LIST - ";
$customTextMsg['print_shopping_hdr'][4]    = "LA LISTE D'ACHATS - ";


// 9-12-14: version 3.5.2 update - Condense Spanish and French translations so they only use three lines on Shopping List.	-mlr
$customTextMsg['print_shopping_inst'][1]   = "Please use the boxes and lines on the left, and <i>NOT THE GREY RECTANGLES</i>, "   .
                                             "to let us know which and how many items you need.  For products with lines, <i>PLEASE WRITE "     .
                                             "THE NUMBER YOU NEED</i>, up to the limit shown.";											 

// 3-11-13: version 3.4.1 patch								 							 
$customTextMsg['print_shopping_inst'][2]   = "Por favor, utilice los cuadros y líneas en la izquierda, y </i>NO LOS RECTANGULOS GRIS.</i> "	.
											 "Para los productos con líneas, "	.
											 "<i>POR FAVOR ESCRIBA EL NUMERO QUE NECESITA</i>, hasta el límite indicado.";
$customTextMsg['print_shopping_inst'][3]   = "Please use the boxes and lines on the left, and <i>NOT THE GREY RECTANGLES</i>, "   .
                                             "to let us know which and how many items you need.  For products with lines, <i>PLEASE WRITE "     .
                                             "THE NUMBER YOU NEED</i>, up to the limit shown.";
$customTextMsg['print_shopping_inst'][4]   = "S'il vous plaît utiliser les boîtes et les lignes sur la gauche, et non pas le GRIS rectangles.
											Pour les produits avec des lignes, S'IL VOUS PLAÎT écrire le nombre VOUS AVEZ BESOIN jusqu'à la limite indiquée.";

// 4-23-2018: version 3.6.3 update - change to "Limit".		-mlr											
//$customTextMsg['print_shopping_maxqty'][1] = "No more than ";
//$customTextMsg['print_shopping_maxqty'][2] = "No mas que ";
//$customTextMsg['print_shopping_maxqty'][3] = "No more than ";
//$customTextMsg['print_shopping_maxqty'][4] = "Pas plus que";
$customTextMsg['print_shopping_maxqty'][1] = "limit ";
$customTextMsg['print_shopping_maxqty'][2] = "límite ";
$customTextMsg['print_shopping_maxqty'][3] = "limit ";
$customTextMsg['print_shopping_maxqty'][4] = "limite ";
$customTextMsg['print_shopping_maxqty'][5] = "limit ";

// 9-10-14: version 3.5.2 upgrade - include language translation for "other" product.	-mlr 
$customTextMsg['other_prd'][1]	= "other";
$customTextMsg['other_prd'][2]	= "otros";
$customTextMsg['other_prd'][3]	= "lwm yam";
$customTextMsg['other_prd'][4]	= "autre";

$customTextMsg['other_inst'][1]	= "please print";
$customTextMsg['other_inst'][2]	= "en letra de imprenta";
$customTextMsg['other_inst'][3]	= "thov sau";
$customTextMsg['other_inst'][4]	= "s'il vous plaît imprimer";


?>