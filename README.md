# pepbase-master
<h2>Introduction</h2>

Pepbase is open-source software for community food and essentials pantries. It allows pantry coordinators to manage product inventories, track household visits, and view timely consumption or demographic reports. Designed with flexibility in mind, administrators can define eligibility rules for the products they carry, giving guests unlimited visits within a month, and a shopping list tailored specifically for their household needs.

Deployable in the cloud or a local server, Pepbase has no limit to the number of users, so data can be stored at a single pantry, or shared by a network of pantries through the Internet. In addition to printing a shopping list, registration form, and several reports, Pepbase can work with an unlimited number of languages. It currently supports English, Spanish, Hmong, French, and Arabic.

<h2>Dependencies</h2>
<ul>
	<li>php 7</li>
	<li>MariaDB 10</li>
	<li>PHPMailer</li>
</ul>	
	
<h2>Browser Requirements</h2>
	
	- Chrome, Firefox, or Edge 

<h2>Setup</h2>

Pepbase includes a setup routine to configure the database, add a pantry, and initialize the administrator account. Since most webhosting services don't grant CREATE DATABASE privileges to MySQL users, you'll want run setup on your own device first, then ftp to the internet. In case you don't have your own XAMPP development stack, you should first create the database with cPanel tools provided by your webhosting service like MySQL Databases before running setup on their server.

1. Unzip pepbase-master to the XAMPP web folder.

2. Load pepbase-master into your browser and follow the instructions.

3. Remember the username and password you gave for Administrator, as you will need them for the initial sign in. 

After setup completes, a file named config.php should exist in the pepbase-master folder. You may need to change ownership and permissions on this file before making changes. To re-run set up, just delete the config.php file. You can rename the pepbase-master folder to anything you want.
