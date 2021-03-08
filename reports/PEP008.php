<?
# PEP008.php
# written: 10-26-2010    -mlr
#
# 7-15-11: version 2.5 updates.
#          - Due to random timeouts on commercial server (php.ini settings), login now verified
#            with a single cookie (see function "ValidLogin") rather than php's session variables.    -mlr
#
# 2-10-11: Modify session verification and MYSQL connection to be 
#           compatable with existing pep system. -mlr  

///////////////   BEGIN PEP SESSION VERIFICATION AND MYSQL CONNECTION /////////////////

define('IN_PEP', true);
if (substr_count($_SERVER['PHP_SELF'],'/') == 3)
    define('ROOT','../');
else
    define('ROOT','');
$today = getDate();

include(ROOT .  'extension.inc');
include(ROOT .  'common.'                            .$phpEx);
include(ROOT .  'includes/common_vars.'              .$phpEx);
include(ROOT .  'includes/functions.'                .$phpEx);
include(ROOT .  'includes/household_functions.'      .$phpEx);
include(ROOT .  'includes/household_form_functions.' .$phpEx);

Database_CreateConnection();
$hostInfo = ValidLogin();                                                          // 7-15-11:   -mlr
if ( $hostInfo['is_coordinator'] != 1 && $hostInfo['is_supercoord'] != 1 )         // 7-15-11:   -mlr
    redirect("index.$phpEx");

///////////////   END PEP SESSION VERIFICATION AND MYSQL CONNECTION /////////////////
?>

<HTML>
<HEAD>
<title>PEP008 - Update Pantry ID in Household, Consumption Tables</title>


<STYLE type="text/css">

.leftjust {
    padding-left: 10px;
    padding-right: 10px;
    text-align:left;
}

.rightjust {
    text-align:right;
    padding-right: 10px;
    padding-left: 10px;
}

.TopHead {
    text-align: center;
    padding-right: 15px;
    padding-left: 15px;
}

.PrtTble {
    border-style: solid;
    border-width: 1px;
    border-color: grey
}

h2 { border-bottom: 2px solid black; }


P.pagebreakhere {page-break-before: always}



</STYLE>


</HEAD>
<BODY>

<FONT FACE="Arial">

<p>

<?php

########################
# VERIFY ACCESS LEVEL  #
########################


######################
# INIT VARS          #
######################

    set_time_limit(900);


    $HouseTally3=0;
    $HouseTally5=0;
    $ConsumeTally3=0;
    $ConsumeTally5=0;



######################################################################
######################################################################
#                      M A I N L I N E                               #
######################################################################
?>
    <CENTER>
    <FONT style="font-size:12pt;font-weight:bold;">PEP008 - Update Pantry ID in Household, Consumption Tables</FONT></CENTER>
    <p>

<?php


    ScanHouseholdTable();

    ScanConsumptionTable();


    PrintFootNote();


function ScanHouseholdTable()
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $HouseTally3, $HouseTally5;

    $sql = "SELECT * FROM household";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {
        $RegMonth = substr($row['regdate'],5,2);
        $RegDay   = substr($row['regdate'],8,2);
        $RegYear  = substr($row['regdate'],0,4);

        $jd = GregorianToJD($RegMonth, $RegDay, $RegYear);  // returns Julian date for household registration date 
        $DayOfReg = jddayofweek ($jd);  // Returns the day number as an int (0=Sunday, 1=Monday, etc)

        if (($DayOfReg == 1) && ($row['regdate'] >= '2010-08-02'))
        {
            UpdateHousehold($row['id'],5);
            $HouseTally5++;
        }
        else
            if (($DayOfReg == 2) && ($row['regdate'] >= '2010-08-02'))
            {
                UpdateHousehold($row['id'],3);
                $HouseTally3++;
            }
            else
                UpdateHousehold($row['id'],1);  // for test, init w/Zion's pantry_id 
                
    }


}


function UpdateHousehold($TableId, $PantryId)
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{

    $sql = "UPDATE household                                                  
                SET pantry_id = '$PantryId'           
                WHERE id = $TableId";

    $UpdateOK = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));

}

function ScanConsumptionTable()
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $ConsumeTally3, $ConsumeTally5;

    $sql = "SELECT id,date FROM consumption";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    while ($row = mysqli_fetch_assoc($result))
    {
        $ConsumeMonth = substr($row['date'],5,2);
        $ConsumeDay   = substr($row['date'],8,2);
        $ConsumeYear  = substr($row['date'],0,4);

        $jd = GregorianToJD($ConsumeMonth, $ConsumeDay, $ConsumeYear);  // returns Julian date for household registration date 
        $DayOfConsume = jddayofweek ($jd);  // Returns the day number as an int (0=Sunday, 1=Monday, etc)

        if (($DayOfConsume == 1) && ($row['date'] >= '2010-08-02'))
        {
            UpdateHousehold($row['id'],5);
            $ConsumeTally5++;
        }
        else
            if (($DayOfConsume == 2) && ($row['date'] >= '2010-08-02'))
            {
                UpdateHousehold($row['id'],3);
                $ConsumeTally3++;
            }
    }


}


function UpdateConsumption($TableId, $PantryId)
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{

    $sql = "UPDATE consumption                                                  
                SET pantry_id = '$PantryId'           
                WHERE id = $TableId";

    $UpdateOK = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));

}

function PrintFootNote()
##################################################################################
#   written: 10-26-10                                                            #
#                                                                                #
##################################################################################
{
global $conn,  $HouseTally3, $HouseTally5, $ConsumeTally3, $ConsumeTally5;


    $TodaysDate = date('M j, Y');

    $sql = "SELECT COUNT(*) FROM household";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result))
        $Tothouseholds = $row['COUNT(*)'];

?>
    <p>
    Date: <?php echo $TodaysDate; ?>
    <p>
    <u>'household' table</u><br>
    row(s) where 'pantry_id' updated to CMC: <?php echo number_format($HouseTally3); ?><br>
    row(s) where 'pantry_id' updated to FLC: <?php echo number_format($HouseTally5); ?><br>
    Total row(s) in table: <?php echo number_format($Tothouseholds); ?>


<?php

    $sql = "SELECT COUNT(*) FROM consumption";
    $result = mysqli_query( $conn, $sql ) or die(mysqli_error( $conn ));
    if ($row = mysqli_fetch_assoc($result))
        $Tothouseholds = $row['COUNT(*)'];

?>
    <p>
    <u>'consumption' table</u><br>
    row(s) where 'pantry_id' updated to CMC: <?php echo number_format($ConsumeTally3); ?><br>
    row(s) where 'pantry_id' updated to FLC: <?php echo number_format($ConsumeTally5); ?><br>
    Total row(s) in table: <?php echo number_format($Tothouseholds); ?>

<?php
}


?>

</FONT>
</body>
</html>