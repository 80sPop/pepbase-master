<?php
/**
 * documentation/index.php
 * written: 1/29/2021
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
 
/* CONSTANT DECLARATIONS   */

	define('Host_ACCESS_LEVEL', '0');
	define('Host_SIGNIN_ID', '0');
	
/* INCLUDE FILES */
	require_once("../config.php" ); 
	require_once("../common_vars.php"); 
	require_once("../functions.php");	
	require_once("../header.php");	

/* INITIALIZE VARS */

?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Documentation</title>

	<link rel="icon" type="image/x-icon" href="../images/favicon-index.ico" />


    <!-- Bootstrap CSS -->
<!--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->

	<!-- Sass Bootstrap override -->
<?php
	echo "	
	<link rel='stylesheet' href='doc.css?v=" . md5_file("doc.css") . "' >\n";
?>	
	<!-- custom css -->
    <link rel="stylesheet" href="../css/sticky-footer.css" >	

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

	<!-- smartresize js for responsive charts 
	<script type="text/javascript" src="../javascript/jquery.debouncedresize.js"></script>
	<script type="text/javascript" src="../javascript/jquery.throttledresize.js"></script>-->

	<!-- Google Charts API 
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>-->

</head>

<?php
echo "

<body class='bg-gray-4' >";

paintDocBanner();
echo "	
	<div class='container'>
	
<h1 class='mt-3'>User Manual</h1>
<h3>version 4.0 </h3>
<h5 class='mt-4'><u>Table of Contents</u></h5>
	

<p>
<a href='#Introduction'>Introduction</a>
<br>
<a href='#Terminology'>Terminology</a>
<br>
<a href='#Security'>Security</a>
<br>
<span style='padding-left:2rem;'><a href='#SAccess'>Access Levels</a></span>
<br>
<span style='padding-left:2rem;'><a href='#SignIn'>Sign In</a></span>
<br>
<span style='padding-left:2rem;'><a href='#Changing'>Changing Passwords</a></span>
<br>
<span style='padding-left:2rem;'><a href='#PasswordReset'>Password Reset</a></span>
<br>



<a href='#StaffRoles'>Staff Roles and Functions</a>
<br>
<span style='padding-left:2rem;'><a href='#HostRoles'>Host</a></span>
<br>
<span style='padding-left:4rem;'><a href='#Greeting'>Greeting the Guest</a></span>
<br>
<span style='padding-left:4rem;'><a href='#GuestLookup'>Guest Search</a></span>
<br>
<span style='padding-left:4rem;'><a href='#AdvancedSearch'>Advanced Search</a></span>
<br>
<span style='padding-left:4rem;'><a href='#Registering'>Registering New Households</a></span>
<br>
<span style='padding-left:4rem;'><a href='#PrintingRegistration'>Printing Registration Form</a></span>
<br>
<span style='padding-left:4rem;'><a href='#PrintingShopping'>Printing Shopping List</a></span>
<br>
<span style='padding-left:4rem;'><a href='#ChangingPrimary'>Changing Primary Shopper</a></span>

<br>
<span style='padding-left:2rem;'><a href='#PickerFiller'>Picker/Filler</a></span>
<br>
<span style='padding-left:4rem;'><a href='#Filling'>Filling the Order</a></span>
<br>
<span style='padding-left:4rem;'><a href='#Recording'>Recording Products Received</a></span>
<br>
<span style='padding-left:2rem;'><a href='#DataEntry'>Data Entry</a></span>
<br>
<span style='padding-left:4rem;'><a href='#EnteringShopping'>Entering Shopping History</a></span>
<br>

<span style='padding-left:2rem;'><a href='#Coordinator'>Coordinator</a></span>
<br>
<span style='padding-left:4rem;'><a href='#PantrySetup'>Pantry Setup</a></span>
<br>
<span style='padding-left:4rem;'><a href='#ChangingInstock'>Changing In-stock Status</a></span>
<br>
<span style='padding-left:4rem;'><a href='#PortionOverrides'>Portion Overrides</a></span>
<br>
<span style='padding-left:4rem;'><a href='#EligibilityOverrides'>Eligibility Overrides</a></span>
<br>
<span style='padding-left:4rem;'><a href='#SigninAccountSetup'>User Account Setup</a></span>
<br>
<span style='padding-left:4rem;'><a href='#Themes'>Themes</a></span>
<br>

<span style='padding-left:2rem;'><a href='#Administrator'>Administrator</a></span>
<br>
<span style='padding-left:4rem;'><a href='#MoreThanOne'>More Than One</a></span>
<br>
<span style='padding-left:4rem;'><a href='#PasswordOverride'>Password Override</a></span>
<br>
<span style='padding-left:4rem;'><a href='#DefiningProducts'>Defining Products</a></span>
<br>
<span style='padding-left:4rem;'><a href='#AddingPantries'>Adding/Updating Pantries</a></span>
<br>
<span style='padding-left:4rem;'><a href='#DefiningAccessLevels'>Defining Access Levels</a></span>
<br>
<span style='padding-left:4rem;'><a href='#ChangingPantries'>Changing Pantries</a></span>
<br>
<span style='padding-left:4rem;'><a href='#TableUpdates'>Tables</a></span>
<br>
<span style='padding-left:4rem;'><a href='#AdvancedTools'>Tools</a></span>
<br>

<a href='#Reporting'>Reporting</a>
<br>
<span style='padding-left:2rem;'><a href='#Consumption'>Consumption</a></span>
<br>
<span style='padding-left:2rem;'><a href='#Demographic'>Demographic</a></span>
<br>
<span style='padding-left:2rem;'><a href='#ChartsGraphs'>Charts and Graphs</a></span>
<br>

<a href='#RoutineUse'>Pepbase in Routine Use</a>
<br>
<span style='padding-left:2rem;'><a href='#Example1'>Example 1 - Registering a New Household</a></span>
<br>
<span style='padding-left:2rem;'><a href='#Example2'>Example 2 - Avoiding Duplicate Households</a></span>
<br>
<span style='padding-left:2rem;'><a href='#Example3'>Example 3 - Multiple Last Names</a></span>
<br>


<a href='#KnownIssues'>Known Issues</a>


</p>
<h2 id='Introduction'>Introduction</h2>
<p>
Pepbase is software for community food and essentials pantries. It allows pantry coordinators to manage product inventories, track household visits,
and view timely consumption or demographic reports. Designed with flexibility in mind, administrators can define eligibility rules for the products they carry,
giving guests unlimited visits within a month, and a shopping list tailored specifically for their household needs.
<p>
Deployable in the cloud or a local server, Pepbase has no limit to the number of users, so data can be stored at a single pantry, or shared by a network of pantries
through the internet. In addition to printing a shopping list, registration form, and several reports, Pepbase can work with an unlimited number of languages. It currently 
supports English, Spanish, Hmong, French, and Arabic. This manual has been revised to cover the latest version of Pepbase: Pepbase 4. For more information on how you can 
access Pepbase, please send an email to <a href='mailto:info@essentialspantry.org'>info@essentialspantry.org</a>. 
<p>
<h2 id='Terminology'>Terminology</h2>
<p>
This manual is full of references to the specific terminology that's developed over time. In order to ease confusion, a list of commonly used terms and definitions are included here. These are always capitalized in the running text; Primary Shopper, for example, should always appear as Primary Shopper, not as primary shopper.		
<p>
<span id='PrimaryShopper'><b>Primary Shopper:</b></span> This is the household member who registers the household and visits the pantry. Pepbase allows this member to change, depending on who is currently picking up product for the household.
<p>
<span id='SharedProducts'><b>Shared Products:</b></span> These products usually come in a sealed container, and can be used by all members of the household (ex. laundry detergent). The household only receives one package per visit. Shared products appear on the shopping list with a checkbox. 
<p>
<span id='PersonalProducts'><b>Personal Products:</b></span> These are products that touch the skin (ex. lip balm). Every member in the household gets a personal product of their own, providing they meet the other eligibility requirements (age, gender, etc.). Personal products appear on the shopping list with a blank line, where the guest can write an amount up to the limit shown.
<p>	
	
<h2 id='Security'>Security</h2>
<p>	
<h4 id='SAccess'>Access Levels</h4>
<p>
Each sign in account holder is assigned an access level when the account is created. This access level defines what privileges on the database a user is granted. The three database 
privileges are: Add/Update, Delete, and Browse. Every screen in Pepbase checks the access level of the user, and will only allow them to perform actions defined by this access level. 
Only users with an Administrator access level can define other access levels. The following access levels are currently defined in Pepbase: Administrator, Coordinator, and Host.
<p>
<h4 id='SignIn'>Sign In</h4> 
<p>
Pepbase is located on the internet at <a target='_blank' href='https://www.essentialspantry.org/pepbase'>www.essentialspantry.org/pepbase</a>. After entering this URL in the pantry's 
browser, the Coordinator can also add it to the browser's bookmarks, or set it as the browser's start page for the Host's convenience. Once there, the user is presented with a 
Sign In screen asking for a signin name and password. Each pantry should have at least one Coordinator account set up by the Administrator. Coordinators, in turn, can set up individual Host accounts for their volunteers. There is no self registration for Pepbase - all sign in accounts must be set up by a Coordinator or the Administrator. The access level (what each user can do with Pepbase) is also determined by the Administrator or pantry Coordinator, and is set at sign in. If needed, an " . '"I forgot my password"' . "
link is included where instructions are sent to their email.
<p>
<b>Note: Both signin name and password are case-sensitive</b>
<p>
<h4 id='Changing'>Changing Passwords</h4>
<p>
Every account holder using Pepbase can change their password. This is probably a good idea for those who share their password with other staff, then have one of their staff members leave the agency. You don't want people who are no longer involved with your pantry having access to Pepbase. There are two ways of changing your password. One is by clicking the 'I forgot my password' link on the Sign In page, as described in the previous section. The other is by clicking your account menu in the upper right portion of the screen (where your name is displayed next to a down arrow), and choose 'edit account'. Here, any account holder can change their password along with their own name and email address.
<p>
<h4 id='PasswordReset'>Password Reset</h4>
<p>
An " . '"I forgot my password or sign-in name"' ." link is included on the Pepbase Sign In screen. This allows any account holder who forgot their sign-in name or password to reset their password, but requires the account holder to have a valid email to send reset instructions to. Because of this, each account holder must be assigned a unique signin name and email address during account creation. Pepbase will not allow new accounts with duplicate signin names or email address. This is a fairly recent development in Pepbase, and there are some legacy accounts which still do not have an email address. These accounts can still access Pepbase, but will not be able to use the 'I forgot my password' routine until an email address is added to their account. 
<p>
<h2 id='StaffRoles'>Staff Roles and Functions</h2>
<p>
<h3 id='HostRoles'>Host</h3>
<p>
<h4 id='Greeting'>Greeting the Guest</h4>
<p>
Obviously, guests coming to your pantry/agency are either already registered guests, in which case they are already (or should be!) in the Pepbase system, or they are new guests who need to be registered.  For busy pantries, the Host may want to use a numbering system, where guests take a number when they arrive, so they are taken care of in a fair manner. When a Host greets the guest, they should ask them if they have visited an agency pantry before, or if this is their first visit. It would seem that the strategies for handling these two circumstances would be quite similar, but in fact almost diametrically opposite strategies need to be used.
<p>
<h4 id='GuestLookup'>Guest Search</h4>
<p>

When you are searching for returning guests - that is, guests or households who are already registered - less is more! Pepbase is designed to be as thorough as possible in identifying registered guests, or guests who are members of an already registered household. Hosts should therefore use the minimal information necessary to locate a registered guest. This use of minimal information is not only sufficient, it helps prevent erroneous duplication of households - that is, registering an existing household as a new household. Use the Search form right below the menu bar to enter minimal information - usually only the first couple of letters of the <a href='#PrimaryShopper'>Primary Shopper's</a> first and last name should be entered. 
<p>
Some pantries give their guests ID cards, which have household IDs printed on them. This makes it easier for Hosts to find households when dealing with a speaking impaired shopper, or a shopper who speaks a foreign language. Here, simply entering the household ID in the Guest Look-up form should produce a unique household in the Households > Profile screen.
<p>
<h4 id='AdvancedSearch'>Advanced Search</h4>
<p>
In some cases, entering parts of the first and last name in Guest search still won't return the correct household. Usually, this is the result of a misspelling in the form, or a misspelling when the household was first entered, or both. For these instances, there is an Advanced Search option you can use to find someone. This gives you the ability to search on all fields in the members table. So, if first and last names aren't working, try entering date of birth or address instead. After all, considering a population of 42,000 members, what are the chances of more than one of them having the same initials and date of birth?
<p>
<h4 id='Registering'>Registering New Households</h4>
<p>
When registering new guests - that is, guests who inform you that they have not been to an essentials pantry before - more is better! The fullest detail possible should be entered. Begin by clicking the 'New Household' button located in the upper right hand of the screen, and the Register New Household form should now display. The following fields are required for inputting new households:
<p>
 		- First Name<br>
		- Last Name<br>
		- Middle Initial<br>
		- Date Of Birth<br>
		- Gender<br>
		- Address<br>
		- City, County, State<br>
		- Zip Code<br>
<p> 
The city, county, and state are verified against a zip code database, so they must match the given zip code. If a household is homeless, just type 'homeless' for the address, and use the pantry's city and zip code. The <a href='#PrimaryShopper'>Primary Shopper's</a> email is not required, but, if entered, must have a valid format. Although phone number is also not required, ask for it anyway, as it will make the household easier to identify for duplicate matching and Guest search. You enter full information for a new guest because Pepbase is going to first try to match a household that already exists in the database. Therefore, we enter full first name and full last name, as well as address, date of birth, etc. If we don't follow this guideline, the result will be exactly the same - a household that is in fact registered doesn't get picked up as an existing household, and gets re-registered as a duplicate household. For more information about duplicates, refer to <a href='#Example2'>Example 2</a> in the 'Pepbase in Routine Use' section.
<p>
For now, we'll assume no matches are found for a new household. After entering the contact information and pressing the Continue button, an Add Members to Household screen appears. This is used to gather demographic info about the household. In this screen, the Host needs to enter name, gender, and date of birth information for each household member. This information will be used to determine what and how many of each product is printed on the shopping list. Some pantries carry hypo-allergenic and bladder control products, so there are fields for allergies and incontinence, as well. After entering all member values, click 'Continue with Registration' to advance to the screen concerning language and reading difficulty. 
<p>
The next screen in the registration process asks for information about household language and literacy. Pepbase currently prints shopping lists in Arabic, English, French, Hmong, and Spanish simply because these are the only languages we've encountered so far with our households of need. Although a bi-lingual staff member is not always available, it is recommend that the pantry offers assistance filling out forms and shopping lists for shoppers with reading difficulty. Once this information has been entered, the Host clicks Continue for the Proof of Identity / Residence Screen.
<p>
Some pantries require their guests to produce a document proving their identity. A state ID or drivers license is usually sufficient for both identity and residence. There are other documents a pantry can use for this purpose, and it's really up to pantry Coordinator to decide if identification is necessary. Either way, Pepbase will allow guests to register with or without ID, but will alert the Host of unidentified guests in the Households > Profile screen.  
<p>	
<h4 id='PrintingRegistration'>Printing Registration Form</h4>
<p>
After clicking the Complete and Print Registration Form button, a Registration Form is displayed in printable, PDF format. The Host can then print the form, attach it to the shopping list (see Print Shopping List), and give it to the guest. Here, the Host should also explain what's on the registration form, and how to complete it.
<p>
<h4 id='PrintingShopping'>Printing Shopping List</h4>
<p>
Whether you're registering a new household, or working with the <a href='#PrimaryShopper'>Primary Shopper</a> of a returning household, the end result will be a printed shopping list where the guest can make their selections. Once the Household's information is loaded into Pepbase, a green 'Print Shopping List' button can be clicked from any sub menu under the Households tab. This will open a new browser tab, where product eligibility overrides are entered. Overrides are used when there are special circumstances in a household, and the software defined eligibility doesn't match a need. Whatever the case, the pantry coordinator can approve more or less of a product here. Once all overrides are approved, the Host clicks the 'Print Approved Shopping List' button, producing a printable list of essential products the guest is approved for. It is recommended that the Host secure the printed shopping list to a clipboard, and have plenty of pens on hand for the guest's convenience. When handing the shopping list to the guest, make sure they are familiar with the process, and briefly tutor them if they are not.	
<p>	
<h4 id='ChangingPrimary'>Changing Primary Shopper</h4>
As stated in the form printed during registration, anyone in a household who is age 18 or over can pick up items for that household. At the time of registration, the Primary Shopper defaults to the person registering the household. This is the name that will appear on the shopping list and the Profile screen in Pepbase. To change the Primary Shopper, navigate to the Households > Members tab and click the edit icon for the member who will become Primary. Now put a check mark in the 'Yes' circle for Primary Shopper, and click Save. If you instead try clicking 'No' for the current PS, the following error message will display: 'Household must have a Primary Shopper'.  
<p>
<h3 id='PickerFiller'>Picker/Filler</h3>
<p>
<h4 id='Filling'>Filling the Order</h4>
<p>
Once the <a href='#PrimaryShopper'>Primary Shopper</a> has made their selections, it's now the Picker/Filler's turn to gather the products, record the products received, place the products in a bag, and place the bag in the guest's hands. Depending on how busy the pantry is, there may be an in/out tray for shopping lists that need to be filled, and those that have been completed by the Picker/Filler staff. When the Picker/Filler receives the shopping list, they can begin assembling products from a series of stocked shelves and bins, which should be organized in the same fashion they are entered in the Products > Pantry Setup screen. The products appearing on the guest's shopping list will also be listed by shelf/bin number in that same order. This way, the staff member can simply work their way from the upper left bin to the lower right bin, much like reading a book.	
<p>	
<h4 id='Recording'>Recording Products Received</h4>
<p>
When the Picker/Filler places the requested product in the shopping bag, they also need to mark the shopping list so the Data Entry staff member can record what was received in the Shopping History screen of Pepbase. Pepbase products are defined as either <a href='#PersonalProducts'>Personal</a>, or <a href='#SharedProducts'>Shared</a>. On the shopping list, personal products have a blank line next to them, with a limit, where shoppers write in the amount of product to receive, up the the limit shown. Shared products, on the other hand, have a box next to them, where a check mark is made if it's something the shopper wants. To the right of the product, there is a gray vertical bar marked 'Staff Only' where the Picker/Filler records what the <a href='#PrimaryShopper'>Primary Shopper</a> received. 
<p>
Since guests are far too apt to use both the shaded and unshaded sides of the shopping list, it is recommended that the Picker/Filler use a soft felt tip marker, so the data entry personnel can distinguish the writing between the guest and the staff member. Obviously, your agency can set its own policy. Moving right along, each Picker/Filler puts a largish dot for any shared products that are given to the guest, and writes the number of any personal products that were given out. Once the shopping list is fulfilled, the Picker/Filler writes their initials in the gray box provided in the upper right hand of the shopping list, which is then forwarded to the Data Entry staff.	
<p>	
	
<h3 id='DataEntry'>Data Entry</h3>

<h4 id='EnteringShopping'>Entering Shopping History</h4>
<p>
After the order is filled, the shopping list is sent to data entry, where products received by the household are entered into Pepbase. Depending on the volume of guests served, the pantry coordinator can designate a staff member solely for the purpose of data entry, or just enter the data themselves. To edit or delete shopping records, click the 'Shopping History' tab in the Households menu. 
<p>
Notice that all shopping history records are sorted by date and time, with the most recent listed first. For staff who enter data in batch, that is, enter all shopping lists at once
after hours, they may enter the pantry name and date in the Search form, and organize the shopping lists by time. This way, they can easily go right down the list.
<p>
To begin, click the edit icon (pencil and paper) in the far right Action column of the record you wish to update. The next screen lists the date and time of visit, the pantry Abbreviation, and the product name. <b>Note: To allow for overrides, all products carried by the pantry will appear on this screen</b>. Next to the products are the following four columns: Eligible For, Approved For, In Stock, and Received. The Approved For and In Stock columns should already have values filled in, but products received still need to be entered for the household. To speed things up, there is a 'Copy In Stock' button above the Received column. Pressing this button will copy the values from the In Stock column to the Received column. If a household requests and receives everything on their shopping list, entering shopping history is as simple as clicking the Copy In Stock button. Click the Save button at the bottom of the screen after all the data is entered.
<p>	
	
<h3 id='Coordinator'>Coordinator</h3>
<p>	
The Coordinator handles higher-level, behind-the-scenes work with Pepbase. This person is usually assigned with the Coordinator access level, and can perform all duties previously defined here in the manual (Host, Picker/Filler, and Data Entry). They also will be the primary contact with the Administrator and Board of Directors in case of policy changes or updates to the Pepbase software. Each pantry can have one or more Coordinators.
</p>
<h4 id='PantrySetup'>Pantry Setup</h4>
<p>
All pantries connected through the Pepbase software have access to an essentials product list which contains eligibility and duration definitions for each product. It is the responsibility of the pantry Coordinator to choose which of these products their pantry will carry, and to organize them on their shelves in a logical manner. Once the products are chosen and placed in their respective bins, the Coordinator can enter this information into Pepbase through the Products > Pantry Setup tab. This screen lists the entire set of products defined for all affiliated pantries, whether they are carried by the pantry, their shelf and bin number, and if there is a <a href='#PortionOverrides'>Portion Limit</a>. Notice that some of the column headings for the list are underlined. By clicking on one of these headings, the list can be sorted in either ascending or descending order by that heading. The way the products are organized in the Pantry Setup screen is the way they will be organized on the shopping list.
<p>
<h4 id='ChangingInstock'>Changing In-stock Status</h4>
<p>
In the course of a pantry session, the situation may arise where the supply of a certain product is exhausted. When this happens, it is the responsibility of the Picker/Filler to inform the Coordinator, who can then click 'OUT' for in stock status through the Products > In-Stock Status screen. Out of stock products will still be printed on the shopping list, but will be struck through with a line, and have 'out' displayed to the left of them. The instock status will also be displayed, in real-time, on the website under 
<a target='_blank' href='https://www.essentialspantry.org/home/index.php/products/the-essentials'>Products > The Essentials</a>.
<p> 	
<h4 id='PortionOverrides'>Portion Limits</h4>
<p>
As mentioned earlier, products are defined as either personal or shared. In the case of personal products (toothbrushes, for example) each member of the household is eligible to receive their own. Since not all pantries are well stocked, there may be a shortage of certain products, and the Coordinator could decide to limit the number of personal items given to each household. The idea here is that by limiting the portion, even households that arrive late to the pantry will still get served. Click Change Pantry Setup in the Products > Pantry Setup screen to update Portion Limits.	
<p>
<h4 id='EligibilityOverrides'>Eligibility Overrides</h4> 
<p>
Pepbase is a useful tool for controlling inventory and tracking pantry guests, but doesn't always have to follow the rigid set of rules programmed into it. There may be circumstances where the Coordinator wants to override product eligibility for a certain household. Let's say, for instance, a household has a 10 year old boy with a rare thyroid condition,
and this condition is causing him to have full beard. According to the product rules, he is not eligible for a man's razor until the age of 12. In this situation, the pantry Coordinator can override the computer's calculated eligibility, and allow correct entry of the razor being requested and received for that shopping visit. Refer to <a href='#PrintingShopping'>Printing Shopping List</a> for details on how to override eligibility.
<p>	
<h4 id='SigninAccountSetup'>User Account Setup</h4>
<p>
When an Administrator adds a new pantry to Pepbase, they will also setup one user account for the Coordinator of that pantry. In turn, the Coordinator can set up an unlimited number of accounts for other Coordinators and Hosts who volunteer at their pantry. This task can be accomplished through the Security > Users screen. Once there, click the Add User link. All fields are required here (except phone number), and the Activated selector must show active in order for the account holder to use Pepbase. Notice the selection box for Access Levels. Each Access Level is assigned a number, where Administrator=1, Coordinator=2, and Host=3. This defines a hierarchical structure, so a Coordinator can only assign an access level with a number greater than or equal to their own. Once all information for the new account is entered, click the Save button, and an email with password setup instructions will be sent to the new user. To update or delete a user, click the appropriate icon in the action column to the far right.	
<p>	
	
<h3 id='Administrator'>Administrator</h3>
<p>
Administrator is a term used more to describe the person(s) who has top access level to the Pepbase system. They rarely perform day-to-day functions described in the previous roles, but have the database authority to sign in to any pantry, define access levels, add/delete pantries in the system, or disable any user if necessary. They basically have full view of everything that happens in the database (sometimes referred to as 'God-view'). There can be one or more Administrators in Pepbase.
<p>
<h4 id='MoreThanOne'>More Than One</h4>
<p>
As mentioned in the previous section, there can be more than one
Administrator in Pepbase, however, the security rules that govern the
relationship among Administrators is slightly different. The following rules apply to
all Pepbase Administrators:
<ul>
<li>The Administrator Access Level grants full privileges to the entire Pepbase system
<li>The Administrator Access Level may not be edited or deleted
<li>Administrators may not edit or delete another Administrator's user account
</ul>
These measures were implemented to correct a previous coding error, where
it was possible for an Administrator to remove their own ability to
update other Access Levels. An Administrator account can still be
removed, but it must be done by a webmaster who has root access to
the server. Essentially, it's an extra layer of protection to prevent
Administrators from locking themselves out of their own system.
<p>
<h4 id='PasswordOverride'>Password Override</h4>
<p>
Remembering that all staff members who have a sign in account can update their own password, one would think there is no need for a password override. On the other hand, consider the staff member who has forgot their password, and also has lost access to their own email account. They are now locked out of Pepbase, and have no way to get back in. For these situations, Pepbase has a password override function available only to Administrators. The Administrator can update any staff member's password by editing their account through the Security > Users Accounts tab. 

<p>	
<h4 id='DefiningProducts'>Defining Products</h4>
<p>
In order for Pepbase to determine a shopper's eligibility for a product, the product must first be defined with parameters such as duration, age group, and gender. The task of defining products is reserved for the Administrator, and can be done through the Products > Definitions tab. Here, every product available to a pantry is listed, and the order of the list can be changed by clicking on the column you want the list sorted by. Click on any product to update, and click the Add Product link to add. The new and edit screens behave differently, but contain the same values. When adding a new product, the Administrator is first asked for the name of the product in all supported languages. Use <a href='https://translate.google.com' target='_blank'>Google Translate</a> for product names in a foreign language.	
<p>	
Next, the properties are defined. The product may only be for a certain age group (ex. baby diapers are for ages 0-3). Some items, like pantiliners, are gender specific. Select 'YES' for Hypoallergenic when the product is specifically intended for household members with skin allergies, like non-fragranced shampoo, soap, and skin lotion. Incontinence pads and
adult diapers are examples of products for incontinence. Containers, Amounts, and Measures are used to describe how a product will print on the shopping list. For dish soap, if Container is bottle, Amount is 12, and Measure is ounces, then it will appear on the shopping list as 'dish soap (12 oz bottle)'. Containers, Amounts, and Measures are stored in separate tables, and can be edited under the 'Tables' menu option. 
<p>
The first field under Sizes/Types asks for the number of sizes and types. Leave this field zero if there are no sizes and types. Whatever number you choose here, a separate input field will display in relation to that number, and whatever text you enter into that field is what will display on the shopping list.
<p>
Finally, we need to tell Pepbase whether the product is <a href='#PersonalProducts'>Personal</a> or <a href='#SharedProducts'>Shared</a>. An example of a shared product is laundry soap, which can be used by everyone in the household. Personal products are products that touch the skin (ex. toothbrush), so everyone in the household is eligible for their own, providing that they meet the other requirements. Since the amount of use of shared products will depend on the household size, their durations must be defined accordingly. 
<p>	
<h4 id='AddingPantries'>Adding/Updating Pantries</h4>
<p>
Pepbase is designed to connect and share data with any pantry who wishes to join the network. When a pantry decides to join (or leave) Pepbase, or has a change in status (new hours, contact information, etc.), it is the Administrator's responsibility to update this information in Pepbase. Pantries are added and updated through the (you guessed it) 'Pantries' tab. Keep in mind that the pantries database table is read by The Alliance > Affiliated Pantries page on the <a target='_blank' href='https://www.essentialspantry.org'>Pepartnership, Inc.</a> website. So, every time an Administrator updates a pantry through Pepbase, it is immediately updated on the website.	
<p>	
<h4 id='DefiningAccessLevels'>Defining Access Levels</h4>
<p>
Access levels define what a user can see and do in Pepbase, and can only be updated by the Administrator. This manual has already defined what roles there are in an essentials pantry: Host, Data Entry, and Coordinator. It would only seem logical, then, that there would be an access level defined for each of these roles, and assigned to each user who carries out these roles. Access levels are defined in Pepbase through the Security > Access Levels tab. By clicking the edit icon in the Action column, another table displays showing all the database elements in Pepbase, along with the privileges a user can have with the database elements. Again, the three privileges are: Add/Update, Delete, and Browse. Notice that some of the privileges are grayed out, since they are not applicable (for instance, a report can't be deleted). <b>To protect the Administrator from being locked out of this screen, Pepbase will not allow the Administrator access level to be updated, as the Administrator has full rights on all screens in the database anyway.</b>
<p>
Pepbase uses a hierarchical structure when defining access levels, so the Administrator must also choose a position in addition to name when adding a new level. This structure is used when adding and updating users. Refer to the following table:
<table class='table'>
<tr><th>Access Level</th><th>Position</th>
<tr><td>Level A</td><td>1</td></tr>
<tr><td>Level B</td><td>2</td></tr>
<tr><td>Level C</td><td>3</td></tr>
</table>
</p>	
When a user with Level B adds another user, they can only assign that user a Level B or Level C access level (i.e. a user with a position number value greater than or equal to their own.)	
		
<p>	
<h4 id='ChangingPantries'>Changing Pantries</h4>
<p>
When an Administrator signs in, their pantry location will always be at the last pantry they were signed into. So, if an Administrator prints a guest's shopping list, it will be for that pantry. In the case where a Coordinator contacts an Administrator about a problem they are having with Pepbase, it might helpful for the Administrator to be signed in at that pantry. To change pantries, click on the account menu in the upper right corner of Pepbase (The one with the user's name) and choose 'change pantry'.
<p>
<h4 id='TableUpdates'>Tables</h4>
<p>
From the Tables menu item, an Administrator can edit the following system database tables: Languages, Measures, Containers, and Shelters. The Languages table is used during household registration, and for product definitions. Measures and Contains are also used for product definitions, and control how an item is printed on the shopping list. The shelters table contains agency shelters (most in the Dane county area) which provide temporary housing for people afflicted with drug/alcohol abuse, domestic abuse, or homelessness. When a household matches the address of one of these shelters, an alert is displayed in the households/profile screen. This helps the Host in determining whether a <a href='#PrimaryShopper'>Primary Shopper</a> should register themselves as a separate household, or a member of the shelter.
<p>
<h4 id='AdvancedTools'>Tools</h4>
<p>
The remaining Administrator functions are found under the Tools menu item. The Change Pantry tab is used for instances where an Administrator needs to see data specific to a certain pantry in the network, such as Pantry Setup or In-stock Status. Administrators can also change pantries through the User menu in the upper right hand corner of the screen. The User Log can be used to diagnose errors in the system, and displays the actions for each user such as when they sign in and out, or when shopping lists are printed. The Convert option should only be used once when migrating tables from Pepbase 3 to Pepbase 4.	
<p>	
<h2 id='Reporting'>Reporting</h2>
<p>
Keeping track of statistics - about money spent, about guest demographics, about products or services provided - often gets overlooked in the busy-ness of things, or downplayed because it's considered too cold or business-like for social work. We think that the more we know about what we're doing, who we're serving, and how well what we do matches the needs of those we serve is important. To that end, we keep track of a lot of numbers, and we run a lot of analyses on a pretty routine basis. Depending on how the Administrator has defined access levels, not all staff can view the reports. Currently, only Administrators and Coordinators can view all reports, where Administrators are allowed to see data from all agency pantries, and Coordinators can only see data from their own pantry as compared to the agency as a whole.
<p>
There are several reports available in Pepbase, and they are organized into three categories: <b>Consumption</b>, <b>Demographic</b>, and <b>Charts and Graphs</b>. The default definition for the data to include in these reports is data from households who were active within the last 18 months, with a 2 week offset. The 2 week offset is a grace period which allows pantries time to enter all shopping history, ensuring that fulfillment ratios and household demographics are up to date. For example, a report generated on September 17, 2019, would include data from March 3, 2018 through September 3, 2019. These dates can, however, be manually redefined at the user's request. The 'frame' for the data will be reflected in the report header.
<p>
During the initial build of Pepbase, several reports were made available per Coordinator request, however, they are not all relevant to every pantry in the alliance, so not all of them will be discussed in this manual. Also, some reports were written using an out of date styling schema, and appear awkward in modern browsers. These can be improved as time and resources permit. If you have questions about a report that was not explained here, please send a request to  <a href='mailto:info@essentialspantry.org'>info@essentialspantry.org</a>	
<p>	
<h4 id='Consumption'>Consumption</h4>	
</p>
<p>
<b>PEP007 - Households by Number of Visits</b>
starts with the number of households in Pepbase that have only visited a pantry once, followed by those who have visited twice, and so on. In the case of this agency, it explains the fact that the majority of households only visit once. This report is better illustrated by it's graph (see <a href='#PEP007c'>PEP007c</a> under Charts and Graphs).
</p>	
<p>
<b>PEP014 - Product Consumption by Time Period</b>
This report gives a breakdown of all products distributed in a given time period. Each product is listed with household eligibility statistics, along with the percentage of fulfillment (amount received / amount requested) and percentage of products instock (amount in stock / amount eligible for). The later percentage is more meaningful as far as showing actual need for products, because a household can't request a product that is out of stock. The summary also shows the number of visits for the given time period.
</p>	
<p>	
<b>PEP024 - Product Ordering Guidelines</b> 
One of a Coordinator's most important duties is ordering products for their pantry. PEP024 counts the number of products each household was eligible for in the selected time range, then breaks down that number into weeks, so you can better forecast the demand for a product the next time your pantry is open. Using a greater time range will increase the sample size, making a more accurate report.	
</p>
<p>
<h4 id='Demographic'>Demographic</h4>
<p>
<b>PEP002 - Household Members by Age and Gender</b>
Pepbase doesn't collect data about a household member's race or military status. It does, however, track other demographic statistics such as age, gender, and language spoken in the household. PEP002 displays a breakdown of house members by age and gender. Notice that data for this report cannot be filtered by a date range or pantry. PEP002 will always display results for all households who were active within the last 18 months, with a 2 week offset. 
</p>
<p>
<b>PEP009 - Households with Age Difference > 30 OR < 15</b>
Here, PEP009 tries to see if there is a correlation between age gaps in a household and poverty. In other words, is poverty more prevalent in households involving teen pregnancy, and households where minors are living with a third generation of family (Kids living with their grandparents).
</p>
<p>
<b>PEP013 - Households by Language and Zip Code</b>
Pepbase currently has language support for English, Spanish, Hmong, French, and Arabic speaking households (in the order they were introduced to Pepbase), and more languages can be added whenever needed. PEP013 shows us where these households are located, by zip code, from a population of households who have visited an affiliated pantry within the specified date range.
</p>
<p>
<h4 id='ChartsGraphs'>Charts and Graphs</h4>
<p>
<span id='PEP007c'><b>PEP007c - Households by Number of Visits</b></span>
This graph also appears under Consumption reports in table form, and models the distribution of households by the number of times they visit a pantry. This classic curve (also referred to as the Parento Distribution) illustrates the fact that the majority of households only visit an essentials pantry once, and is also available to the public on the <a target='_blank' href='https://www.essentialspantry.org'>Pepartnership, Inc.</a> website under Reporting. 
</p>
<p>
<b>PEP021 - Product Consumption</b>
The Product Consumption report is a good way to gauge products distributed by a pantry as compared to the alliance as a whole. It displays a pie chart for each interval in the selected time period, as well as a line graph in the report summary. If you're a pantry Coordinator who needs to report product distributions for grant funding, this is the report to use. To see these values in table form, use PEP014 under Consumption reports.
</p>
<p>
<b>PEP025c - In Stock Percentage</b>
Instead of describing the demand of products, PEP025c better illustrates the supply of these goods to guests in need, by tracking the ratio of in-stock essential products to eligible members of a household. It implements the line graph from Google Charts API, and draws a chart for any selected time period and pantry. PEP025c is also available to the public under the <a target='_blank' href='https://www.essentialspantry.org'>alliance's website</a>.
</p>
<p>
<h2 id='RoutineUse'>Pepbase in Routine Use</h2>
<p>
Now that all staff roles and functions have been explained, it's time to see Pepbase in action. Starting with a simple household registration, and moving to more complex issues, this section hopes to resolve any issue a staff member might have in daily routine use of Pepbase. Furthermore, instead of using real people as examples, this manual will use those lovable characters from the popular PBS children's series <a target='_blank' href='https://sesamestreet.org'>Seasame Street</a>. Consider the following three pantry guests:

<div class='row mt-4 pl-3' style='font-family:monospace;'>
	<div class='col-sm  p-1'>
    FIRST NAME: Big 
<br>     LAST NAME: Bird
<br>MIDDLE INITIAL: B
<br>         D.O.B: 02/15/1973
<br>       GENDER: Male
<br>       ADDRESS: 101 Sesame St
<br>    APT NUMBER: 8
<br>          CITY: Madison	
<br>        STATE: WI
<br>      ZIP CODE: 53703
<br>         EMAIL:
<br>       PHONE 1: 608-987-6543
<br>       PHONE 2:	
	</div>
	<div class='col-sm  p-1'>
    FIRST NAME: Cookie 
<br>     LAST NAME: Monster
<br>MIDDLE INITIAL: C
<br>         D.O.B: 07/03/1973
<br>        GENDER: Male
<br>       ADDRESS: 101 Sesame St
<br>    APT NUMBER: 8
<br>          CITY: Madison	
<br>        STATE: WI
<br>      ZIP CODE: 53703
 <br>        EMAIL:
<br>       PHONE 1: 608-123-4567
<br>       PHONE 2:	
	</div>
	<div class='col-sm  p-1'>
    FIRST NAME: Count  
<br>    LAST NAME: Von Count
<br>MIDDLE INITIAL: D
<br>         D.O.B: 02/15/1973
<br>        GENDER: Male
<br>       ADDRESS: 101 Sesame St
<br>   APT NUMBER: 7
<br>         CITY: Madison	
<br>         STATE: WI
<br>      ZIP CODE: 53703
<br>         EMAIL:
<br>       PHONE 1: 111-222-3333
<br>       PHONE 2:	 	
	</div>
</div>	
<p>	
<h4 id='Example1'>Example 1 - Registering a New Household</h4>
<p>
For the first example, let's say Big Bird notices that his apartment is low on toilet paper. He then visits the nearest essentials pantry to pick up a roll for both him and his roommate, Cookie Monster. When Big Bird arrives at the pantry, he is greeted by the pantry Host, who then asks him if he has visited a pantry before. Bird replies 'No', and the Host begins the registration process by clicking the 'New Household' button in the Households > Profile screen. When the Host asks for an I.D. with contact information, Big explains that he left it at his apartment, and will bring it with him on his next visit. For now, he can verbally communicate this information to the host.
<p>
Once all the required fields are entered in the Register New Household screen, the Host clicks Continue, and now needs to enter the other members of Big Bird's household. Big Bird should already be listed in the members screen, so the Host then asks Bird if there are any other members in his household, and he replies 'Yes', and describes the age and gender of his roommate, Cookie Monster. The Host also needs to ask if there are any allergies or bladder control (incontinence) problems in the household. Big Bird and Cookie Monster don't have these issues, so the Host can continue to the next screen.
<p>
The Edit Literacy Information screen asks for the language spoken in the household, and whether or not the <a href='#PrimaryShopper'>Primary Shopper</a> has difficulty reading. Big Bird's household doesn't have difficulty reading, but in cases where there is difficulty, the Host or another staff member can help the guest with filling out the registration and shopping list, which is discussed later in this section.
<p>
The last screen in the registration process asks for proof of identity and residence. We will leave these fields empty for now, since Big Bird's Identification card is in his wallet, which he left on the coffee table in his apartment. Pepbase allows these fields to be left blank for just such occasions. It's really up to the Coordinator of each pantry to decide if identification is necessary before giving out essential goods. Usually, the guest is asked to bring along identification on their next visit, and the Host makes a note to that effect in the Households > profile screen. Make sure to print Big's registration for after clicking 'Complete and Print Registration Form', as it will later be assembled with his shopping list.
<p>
The profile screen contains all of Big Bird's contact information, the number of members in his household, if his home is a shelter, his literacy information, and whether or not he has proved his identity and residence. There will be a warning sign (yellow triangle with exclamation point) alerting the Host if a household needs attention in certain areas, or if more information is needed. Since this is Big Bird's first visit, we can ignore the warnings about Proof of Identity and Residence. After verifying Bird's profile, the Host clicks the green Print Shopping List button, and adds it to the clipboard along with the Registration Form printed earlier, then hands it to Big Bird for completion. This is an example of a typical registration, where a household is added with no problems. Let's see what happens, though, when Big Bird's roommate visits another agency pantry in Example 2. 
<p>	
<h4 id='Example2'>Example 2 - Avoiding Duplicate Households</h4>
<p>
In the previous example, we showed how a Household with two members is registered. Now, we will discuss how household duplication can happen, and how it can be avoided. Let's say Big Bird's roommate Cookie Monster runs out of toothpaste a week after Bird's registration, and visits another affiliated pantry for more. Also, let's assume Big Bird neglected to inform his roommate about his own visit to the pantry. When Cookie arrives, the Host asks him if he has visited an essentials pantry before, and he replies 'No.' The Host then clicks the New Household button, enters Cookie's contact info, then clicks the Continue button. Normally, this would create a duplicate of Big Bird's household, with Cookie being an active member in both households, and making him eligible to receive products in both households. Pepbase, however, will first search for possible matches in the system, and return a warning screen to alert the Host that Cookie might already exist in Bird's household.
<p>
At this point, the Host should ask Monster if he is a member of Bird's household. If Cookie says that he is NOT a member of Big's household, the Host can click on 'continue with new registration', and register Cookie as a separate household. If Cookie says he IS a member of Big Bird's household, the Host can click on Bird's name in the matched listing, and continue with printing the shopping list. This is important to note, because it is possible for several people to live at the same address, but be registered as separate households. Also notice that all matching fields are highlighted in the matching household list.
<p>
Continuing on, let's assume Cookie chooses to register himself as a separate household. That's fine, and the Host clicks 'continue with new registration', but, on the next screen (Add Members), Cookie's data is followed by a message alerting the host that Cookie is also active in Bird's household. By clicking 'Move to this household', Cookie is de-activated in Big Bird's household, and a duplicate household is avoided.	
<p>
<h4 id='Example3'>Example 3 - Multiple Last Names</h4>
<p>
For the final example, we'll use Count Von Count to briefly explain how to enter and search for households who have members with two or more last names. Count is taking care of his niece and nephew for a while, whose mother is setting up living arrangements in a new town due to a job transfer. He is already registered with an agency pantry, and so, needing more laundry detergent, arrives at the nearest pantry with his extended family. 
<p>
After greeting Count, the Host asks for his name, then enters 'c' for his first name, and 'count' for his last name in the Households search form. Pepbase finds Count in the database, regardless of the fact that he has a double last name, and the Host used his second last name in the search. Pepbase would also have found Count's household if the Host had used 'c' for first name and 'Von' for last name.
<p class='pb-4'>
Pepbase is now loaded with Count's information in the Households > Profile screen, and the Host asks him if anything has changed in his household. He replies 'Yes', and explains that his niece and nephew, whose last name is Frog-Piggy, are now living with him. The Host clicks on the Members tab to enter Count's family members, and uses 'Frog-Piggy' (hyphen included) for their last names. Now, a month later, Count's 18 year old niece returns to the pantry for more essentials. This time, however, the Host types 'Frog' for the last name in the search form, and Count's household is still found. Furthermore, since the Count's niece is now picking up product for the family, the host can change the <a href='#PrimaryShopper'>Primary Shopper</a> by editing her data in the Households > Members screen. To summarize, Pepbase will search for all parts of a multiple last name, regardless of whether a hyphen is used or not, and regardless of the order they were entered in the search.	
</p>	
<p>
<h2 id='KnownIssues'>Known Issues</h2>
<p>	
<ul>
<li><b>Households in counties with multiple last names (i.e. Eau Claire county) not able to register.</b><br>
discovered: March 26, 2020 by Lois Roth<br>
status: <span style='color:green;'>resolved</span>
</li>
<li><b>Date selection field not displaying correctly in reports PEP007c and PEP025c</b><br>
discovered: March 11, 2020 by Michael Rolfsmeyer<br>
status: <span style='color:green;'>resolved</span>
</li>

<li><b>Date input fields not supported by Internet Explorer and Safari (desktop version) browsers</b><br>
discovered: Jan 24, 2020 by Nancy Baumgardner<br>
status: <span style='color:red;'>pending</span>
</li>

<li><b>Administrator access level allowed to be edited, creating possible lock-out from Pepbase system.</b><br>
discovered: Sep 30, 2019 by Michael Rolfsmeyer<br>";
//echo "status: <span style='color:red;'>pending</span>";
echo "status: <span style='color:green;'>resolved</span>";
echo "
</li>
<li><b>Wrong error message for counties not matching zip code when zip code shared by other cities/counties</b><br>
discovered: Sep 28, 2019 by Nancy Baumgardner<br>
status: <span style='color:green;'>resolved</span>
</li>

<li><b>Shopping list not printing with Apple Safari browser</b><br>
discovered: Sep 14, 2019 by Nancy Baumgardner<br>
status: <span style='color:green;'>resolved</span>
</li>

<li><b>Addresses rejected when city/town/county share zip code with another city/town/county</b><br>
discovered: Aug 9, 2019 by Kathy Schuett<br>
status: <span style='color:green;'>resolved</span>
</li>




</u>
</p>

	</div>
</div>

	<div class='container-fluid bg-gray-7 p-2' style='color:#d0d0d0;background-color:#841E14 !important;'>
		last updated: February 22, 2021
	</div>




</body>";
bFooter(); 
?>
</html>