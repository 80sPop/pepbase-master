<?php
/**
 * shopping_list.php
 * written: 9/23/2020
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
require_once('TCPDF-master/tcpdf.php');
//require_once('MySQLConfig.php');
require_once('functions.php');	
require_once('common_vars.php');
//require_once( 'HouseholdsEligibleFor.php');
//require_once( 'HouseholdsProfile.php');
require_once('households/eligibility.php');

if (!$control=validUser())
	die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");
$control=fillControlArray($control, $config, "security");

//ini_set('default_charset', 'utf-8');
//header('Content-Type: text/html; charset=utf-8' );
//mysqli_query($control, "SET CHARACTER_SET_CLIENT='utf8'");
//mysqli_query($control, "SET CHARACTER_SET_RESULTS='utf8'");
//mysqli_query($control, "SET CHARACTER_SET_CONNECTION='utf8'");

define("COL1_START_X", 10);
define("COL2_START_X", 105);
define("COL_START_Y", 32);
define("PRODUCT_LINE_HEIGHT", 7);
define("PRODUCT_SPACING", 8);
define("INST_OFFSET_Y", 18);
//define("LIST_HEIGHT", 450);
define("LIST_HEIGHT", 435);

if ( isset($_GET['hhID']) )
	$hhID=$_GET['hhID'];
else
	die("ERROR - HOUSEHOLD ID");

// get primary shopper name and household language

$row= getHouseholdRow($control['db'], $control['hhID']);
$hh_lang = $row['language'];
$hh_fullname = stripslashes( $row['firstname'] ) . " " . stripslashes( $row['lastname'] );	
$hh_fullname = ucname( $hh_fullname );

// set font for Arabic speaking households
if ( $hh_lang == 5 )
	$ffamily="aefurat";
else
	$ffamily="helvetica";	

/**
 * Extend TCPDF to work with multiple columns
 */
class MC_TCPDF extends TCPDF {
	
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)		
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
// Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')	
// Rect($x, $y, $w, $h, $style='', $border_style=array(), $fill_color=array())
	
	/**
	 * Header()
	 * @public
	 */		
	public function Header() {
		
		global $control, $hhID, $hh_fullname;

		$style4 = array('L' => array('width' => 0.10, 'cap' => 'round', 'join' => 'miter', 'dash' => 0),
						'T' => array('width' => 0.10, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 127)),
						'R' => array('width' => 0.10, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 127)),
						'B' => array('width' => 0.10, 'cap' => 'round', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 127)));

// 9-5-2019: v 3.9.3 update - draw a series of boxes within boxes for Header.		-mlr						
		$this->Rect(9, 4, 192, 19, 'null', $style4, array(255, 255, 255));	
		$this->Rect(56, 4, 100, 19, 'null', $style4, array(255, 255, 255));		
		$this->Rect(56, 4, 100, 9.5, 'null', $style4, array(255, 255, 255));		
		
		// logo
		$image_file = "images/pep_logo.jpg";
		$this->Image($image_file, 10, 5, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

// 9-5-2019: v 3.9.3 update - move date and pantry name to footer, reposition household name and id, 
//		increase window size of staff initials.		-mlr		
		
		// column 1
		$pName=$this->doPantryName( $control );
		$this->SetFont('helvetica', '', 8);			
		$this->writeHTMLCell(95, 10, 57, 4.5, "primary shopper", 0,0,0,true,"L",true);			
		$this->SetFont('courier', 'b', 16);			
		$this->writeHTMLCell(95, 10, 57, 7, $hh_fullname, 0,0,0,true,"L",true);
		$this->SetFont('helvetica', '', 8);			
		$this->writeHTMLCell(95, 10, 57, 14, "household id", 0,0,0,true,"L",true);		
		$this->SetFont('courier', 'B', 14);			
		$this->writeHTMLCell(95, 10, 57, 17, $hhID, 0,0,0,true,"L",true);		
		
		// column 2
		$this->SetFont('helvetica', '', 11);
		$this->writeHTMLCell(95, 10, 157, 5, "Essentials Shopping List", 0,0,0,true,"L",true);	
		$this->setColor("draw", 175,175,175);	// border color
		$this->SetFillColor(238, 238, 238);			
		$this->writeHTMLCell(43, 11, 157, 11, " ", "",0,1,true,"R",true);
		$this->setColor("text", 130,130,130);
		$this->SetFont('helvetica', 'IB', 12);		
		$this->setColor("text", 200,200,200);
		$this->writeHTMLCell(50, 7, 153, 14, "STAFF INITIALS", 0,0,0,true,"C",true);	
		$this->setColor("draw");
		
		// Underline Heading		
//		$this->writeHTMLCell(190, 2, 10, 18.5, " ", "B",0,0,true,"R",true);		
	}

	/**
	 * Footer()
	 * @public
	 */	
	public function Footer() {
		
		global $control;		
		
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', '', 10);
		
// 9-5-2019: v 3.9.3 update - add date and pantry name to footer.		-mlr		
		$pName=$this->doPantryName( $control );
		$this->Cell(60, 10, date("D, n/j/Y g:i A"), 0, false, 'l', 0, '', 0, false, 'T', 'M');	
		$this->Cell(115, 10, $pName, 0, false, 'l', 0, '', 0, false, 'T', 'M');
		$this->Cell(10, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'r', 0, '', 0, false, 'T', 'M');		

	}	

	/**
	 * doShoppingList()
	 * @public
	 */
	public function doShoppingList() {
		
		global 	$control, 
				$hh_lang,
				$sort_products,
				$printedHeight,				
				$column,
				$pageNum,
				$ffamily;		

		$this->AddPage();
		$this->printInstructions();	
		$printedHeight=15;			

	// set font
		$this->SetFont('helvetica', '', 10);
		$this->SetFillColor(255, 255, 255);		
		
	// position first line
		$lang_offset=0;
		if ( $hh_lang > 1 )
			$lang_offset=5;
		$this->SetXY( COL1_START_X, COL_START_Y + INST_OFFSET_Y + $lang_offset );

	// print products
		$column=1;
		$pageNum=1;		
		$this->doStaffOnlyMsgBar( $pageNum, $column, $hh_lang );
		$today = date('Y-m-d');
		$time = date('H:i:s');		
		
		$sql = "SELECT * FROM products 
				INNER JOIN products_nameinfo
				INNER JOIN products_pantryinfo			
				ON products.id = products_nameinfo.productID 
				AND languageID = 1
				AND products.id = products_pantryinfo.productID 
				AND products_pantryinfo.pantry = $control[users_pantry_id]
				WHERE products.active=1
				AND FIELD(`carried`, 'yes')	
				GROUP BY products.id				
				ORDER BY shelf, bin";	
				
		$stmt = $control['db']->prepare($sql);
		$stmt->execute();	
//		$total = $stmt->rowCount();				
		$result = $stmt->fetchAll();		
		foreach($result as $sort_products) 	{		
			
			$list=determineEligibility($sort_products);
			
			// use shopping override values from consumption table
			$sql2="SELECT quantity_approved FROM consumption		
				   WHERE household_id =$control[hhID]
				   AND product_id = $sort_products[productID]
				   AND date='$_GET[date]'
				   AND time='$_GET[time]'";
			$stmt2 = $control['db']->prepare($sql2);
			$stmt2->execute();	
			$total = $stmt2->rowCount();	
			if ($total > 0) {	
				$row = $stmt2->fetch();
//				$list['num_eligible']= $row['quantity_oked'];
				$list['num_eligible']= $row['quantity_approved'];
			} else
				$list['num_eligible']= 0;			

			if ($list['num_eligible'] > 0) 	{	

//				$this->writeHTMLCell(250,30, 10, 30, "** ELIGIBLE **", 0,0,1,true,"L",true);	
				
				$this->writeProduct($list);
			} else {
//				$this->writeHTMLCell(250,30, 10, 30, "** NOT ELIGIBLE **", 0,0,1,true,"L",true);				
				
			}	


		}
		
//		$this->writeHTMLCell(250,30, 10, 60, $sql, 0,0,1,true,"L",true);
	}

	/**
	 * printInstructions()
	 * @public
	 */
	public function printInstructions() {
		
	global  $hh_lang,
			$ffamily;	

		$y_offset=7.5;
		$y_fill=21;
		if ( $hh_lang > 1 ) { 	
			$y_offset=7.5;
			$y_fill=26;
		}	

	// fill instruction box with background color
		$this->SetFillColor(255,221,150);
		$this->writeHTMLCell(192,$y_fill, 9, 24," ", 0,0,1,true,"C",true);	
		
	// print instruction text	
		$txt[1]="MAKE A CHECK MARK for products with boxes. WRITE A NUMBER for products with a blank line, up to the limit shown.";
		$txt[2]="HAGA UNA MARCA DE CONTROL para productos con cajas. ESCRIBA UN NÚMERO para productos con una línea en blanco, hasta el límite que se muestra.";	
		$txt[3]="UA HAUJ LWM MUAJ RAU COV KHOOM TXOJ KEV POV THAWJ. NTAWV SAU IB TUG TUS NAB NPAWB RAU COV KHOOM NOJ LI CAS XWB, mus txog ntawm cov kev txwv.";
		$txt[4]="FAITES UNE MARQUE DE CONTRÔLE pour les produits avec des boîtes. ECRIRE UN NUMERO pour les produits avec une ligne vierge, jusqu'à la limite indiquée.";	
		$txt[5]="جعل علامة الاختيار للمنتجات مع صناديق. اكتب رقمًا للمنتجات ذات الخط الفارغ ، حتى الحد الموضح.";	
		$exam1[1]="example 1:";
		$exam1[2]="Ejemplo 1:";
		$exam1[3]="Piv txwv 1:";
		$exam1[4]="Exemple 1:";	
		$exam1[5]="مثال 1:";	
		$exam2[1]="example 2:";
		$exam2[2]="Ejemplo 2:";
		$exam2[3]="Piv txwv 2:";
		$exam2[4]="Exemple 2:";	
		$exam2[5]="مثال 2:";	
		$prod1[1]="dish soap";		
		$prod1[2]="jabón para platos";	
		$prod1[3]="xab npum";	
		$prod1[4]="savon de vaiselle";	
		$prod1[5]="صابون أطباق";	
		$prod2[1]="toilet paper";	
		$prod2[2]="papel de baño";
		$prod2[3]="ntaub hoob nab";	
		$prod2[4]="papier hygenique";	
		$prod2[5]="ورق التواليت";

// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)		
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
			
		// print written instructions
		$this->SetFont($ffamily, 'I', 12);
		$this->writeHTMLCell(189,7, 11, 25, $txt[$hh_lang], 0,0,1,true,"L",true);	

		// print example 1	
		$this->SetFont($ffamily, 'I', 12);
		$this->writeHTMLCell(30,5, 11, 30+$y_offset, $exam1[$hh_lang], 0,0,1,true,"L",true);
		$this->writeProductLine( 35, 38, 0, 1, 0, $prod1[$hh_lang], $prod1[1], "(1 bottle)" );
		$this->Image("images/check-mark.png", 35.5, 36.5, 5, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		// print example 2	
		$this->SetFont($ffamily, 'I', 12);		
		$this->writeHTMLCell(30,5, 102, 30.5+$y_offset, $exam2[$hh_lang], 0,0,1,true,"L",true);
		$this->writeProductLine( 126, 38, 1, 1, 0, $prod2[$hh_lang], $prod2[1], "(Limit 5)" );
		$this->Image("images/comic-3.png", 128, 36.5, 4, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

	}

	/**
	 * @public
	 */	
	public function writeProductLine( $x, $y, $isPersonal, $inStock, $hasTypes, $name, $nameEnglish, $amount ) {
	global  $hh_lang,
			$ffamily;	


	// get width of product and amount
		$w_name=$this->GetStringWidth($name, $ffamily, "", 14)+3;
		$w_name_english=$this->GetStringWidth($nameEnglish, $ffamily, "I", 12)+3;
		$w_amount=$this->GetStringWidth($amount, $ffamily, "", 10)+3;

	// to prevent word wrap, reduce font size for lengthy product names 
		$fontSize=14;
		if ( $w_name + $w_amount > 80 ) {
			$fontSize=10;
			$w_name=$this->GetStringWidth($name, $ffamily, "", 10)+3;
		}
		elseif ( $w_name + $w_amount > 70 ) {
			$fontSize=12;
			$w_name=$this->GetStringWidth($name, $ffamily, "", 12)+3;
		}

	// write "out" warning for out of stock products
	    if ( ! $inStock ) {
			$this->SetFont('helvetica', 'IB', 8);
			$this->writeHTMLCell(10,5, $x, $y, "out", 0,0,1,true,"L",true);
			$name= "<s>" . $name . "</s>";
			$amount= "<s>" . $amount . "</s>";

	// write blank line or checkbox
		}  elseif ( ! $hasTypes )
			if ( $isPersonal ) 
				$this->Image("images/blank-line.png", $x, $y+4, 7, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			else 
				$this->Image("images/checkbox.png", $x, $y, 4, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

	// write product name and amount message
		$this->SetFont($ffamily, '', $fontSize);
		$this->writeHTMLCell($w_name,7, $x+8, $y-1, $name, 0,0,1,true,"L",true);
		$this->SetFont('helvetica', 'I', 10);			
		$this->writeHTMLCell($w_amount,5, $x+$w_name+8,$y, $amount, 0,0,1,true,"L",true);	

	// write English translation for non-english speaking households 
		if ( $hh_lang > 1 )	{	
			$this->SetFont('helvetica', 'I', 12);
			$this->writeHTMLCell(50,5, $x+8, $y+4.5, $nameEnglish, 0,0,1,true,"L",true);	
		}
	}

	/**
	 * @public
	 */	
	public function writeTypeLine( $x, $y, $isPersonal, $inStock, $name ) {

	global 	$ffamily;

	$fontSize=12;

	// get width of type
		$w_name=$this->GetStringWidth($name, $ffamily, "", $fontSize)+3;

	// write "out" message for out of stock types
	    if ( ! $inStock ) {
			$this->SetFont('helvetica', 'IB', 8);
			$this->writeHTMLCell(10,5, $x, $y, "out", 0,0,1,true,"L",true);
			$name= "<s>" . $name . "</s>";

	// write blank line or checkbox
		} elseif ( $isPersonal ) 
			$this->Image("images/blank-line.png", $x, $y+4, 7, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		else 
			$this->Image("images/checkbox.png", $x, $y, 4, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

	// write type
		$this->SetFont($ffamily, '', $fontSize);
		$this->writeHTMLCell($w_name,7, $x+8, $y, $name, 0,0,1,true,"L",true);
	}


	/**
	 * writeProduct()
	 * @public
	 */	
	public function writeProduct($list ) 
	{
		global 	$control, 
				$sort_products,
				$hhID,	
				$products,
				$customTextMsg,
				$hh, 
				$hh_lang,
				$numTypes,
				$hh_fullname,
				$printedHeight,
				$column,		
				$pageNum,
				$isPersonal,
				$ffamily;


		$quantity =  $list['num_eligible'];
		$isType = 0;
		$isPersonal=0;
		$isInStock = 1;	
		$isPortionLimit = 0;
		$prodWidth = ( PAGE_WIDTH / 2 ) - 72;
		$default = $this->GetLineWidth();
		$fat = $default * 1.5;
		$maxHeight=110;
		$productLineHeight=PRODUCT_LINE_HEIGHT;
		$productSpacing=PRODUCT_SPACING;
//		$pageHeight=LIST_HEIGHT;
		
		$customTextMsg['print_shopping_maxqty'][1] = "limit ";
		$customTextMsg['print_shopping_maxqty'][2] = "límite ";
		$customTextMsg['print_shopping_maxqty'][3] = "limit ";
		$customTextMsg['print_shopping_maxqty'][4] = "limite ";
		$customTextMsg['print_shopping_maxqty'][5] = "limit ";	
				
	// check if portion limits are set for the product 	
		if ( $sort_products['portion_limit'] != -1 )
			if ( $sort_products['portion_limit'] < $list['num_eligible'] ) {
				$quantity = $sort_products['portion_limit'];
				$isPortionLimit = 1;
			}	

	// calculate needed height	
		if ( $hh_lang == 1 )
			$neededHeight = PRODUCT_LINE_HEIGHT + PRODUCT_SPACING;
		else 
			$neededHeight= PRODUCT_LINE_HEIGHT + PRODUCT_SPACING + 10; // non-english language household, add 5 for translation
		$numTypes = 0; 
		$sql2 = "SELECT * FROM products_typeinfo WHERE productID = " . $sort_products['productID'];
		$stmt = $control['db']->prepare($sql2);
		$stmt->execute();	
		$result = $stmt->fetchAll();		
		foreach($result as $row2) {				
			$isType = 1;
			$numTypes++;		
			$neededHeight = $neededHeight + PRODUCT_LINE_HEIGHT + PRODUCT_SPACING;
		}	
		
	// column, page break	
		if ( ($printedHeight + $neededHeight) > LIST_HEIGHT ) {
			$printedHeight=0;
			if ( $column == 1 ) {
				$column = 2;
				if ( $pageNum > 1 ) {
					$this->SetXY(COL2_START_X,COL_START_Y);
				} elseif ( $hh_lang == 1 ) {
					$this->SetXY(COL2_START_X, COL_START_Y + INST_OFFSET_Y);
				} else {
					$this->SetXY(COL2_START_X, COL_START_Y + INST_OFFSET_Y + 5);
				}	
			} else {
				$this->AddPage();
				$this->SetY(COL_START_Y);				
				$pageNum++;
				$column = 1;
			}	
			$this->doStaffOnlyMsgBar( $pageNum, $column, $hh_lang );
		} elseif ( $column == 2 ) 	
			$this->SetX(COL2_START_X);

	// check in stock status
		if ( !$isType && $sort_products['instock'] == 0 ) 
			$isInStock=0;

	// get product name
		$prod_englishName = $sort_products['name'];
		$prod_displayName = $prod_englishName;	
		if ( $hh_lang != 1 ) {
			$sql2 = "SELECT * FROM products_nameinfo 
					 WHERE productID = $sort_products[productID]
					 AND languageID = $hh_lang";
			$stmt = $control['db']->prepare($sql2);
			$stmt->execute();	
			$total2 = $stmt->rowCount();			
			if ($total2 > 0) { 
				$row2 = $stmt->fetch();
				if ( $row2['name'] != "" )
					$prod_displayName = $row2['name'];
				else
					$prod_displayName = $prod_englishName;			
			} else
				$prod_displayName = $prod_englishName;
		}		

	// get quantity limit or shared portion message  
		$measures=getMeasureRow($control['db'], $sort_products['measure']);	
		if (empty($measures['abbrev']))
			$measure=$measures['name'];
		else
			$measure=$measures['abbrev'];				
		$container =getContainer($control['db'], $sort_products['container']);		
	
		if ( $sort_products['personal'] == "Yes" ) 
			$message = "($quantity $container " . $customTextMsg['print_shopping_maxqty'][$hh_lang] . ")";
		else  
			$message = "($sort_products[amount] $measure $container)";

	// write product 
		$start_x=$this->GetX();
		$start_y=$this->GetY();
		$isPersonal= ( $sort_products['personal'] == "Yes" );				
			
		if ( $numTypes  == 0 ) {
			$this->writeProductLine( $start_x, $start_y, $isPersonal, $isInStock, 0, $prod_displayName, $prod_englishName, $message );
 			if ( $isInStock ) {
				$this->SetXY($start_x,$start_y);
				$this->doStaffFillCircle($isPersonal);
				if ( $hh_lang > 1 )
					$this->SetXY($start_x,$start_y+5);
			}
		} else {
			$this->writeProductLine( $start_x, $start_y, $isPersonal, $isInStock, 1, $prod_displayName, $prod_englishName, $message );
			$save_x=$this->GetX();
			$save_y=$this->GetY();
            $isInStock = ( $this->doProductTypes() > 0 );
			$save_x_2=$this->GetX();
			$save_y_2=$this->GetY()+2;
			if ( $isInStock ) {
				$this->SetXY($save_x,$save_y);		
				$this->doStaffFillCircle($isPersonal);
			}
			$this->SetXY($save_x_2,$save_y_2);	
		}

	// carriage return and line feed		
		$this->Ln(PRODUCT_SPACING);
		$printedHeight += $neededHeight;
	}

	/**
	 * doProductTypes()
	 * @public
	 */		
	public function doProductTypes( ) {	
	
	global 	$control, 
			$sort_products,
			$hh_lang,
			$column,
			$numTypes,
			$ffamily,
			$isPersonal;

		$this->Ln(PRODUCT_LINE_HEIGHT);
		if ( $column == 2 ) 	
			$this->SetX(COL2_START_X);

		$start_x=$this->GetX();
		$start_y=$this->GetY();			
				
	// print gray line with hook
		$bar_x=$start_x+2;
		$bar_y=$start_y-6;
		
		$barheight = 4;
		if ( $hh_lang != 1 ) {  
			$barheight += 4;  
			$bar_y=$start_y-10;
		}	
		for ( $loop = 1; $loop<=$numTypes; $loop++ ) 
			$barheight += 7; 			
		
		$this->Image("images/greyhook.png", $bar_x, $bar_y, 3, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);				
		$this->Image("images/greyline.png", $bar_x+2, $bar_y, 1, $barheight, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$inStockTotal = 0;
		$sql2= "SELECT * from products_pantryinfo 
				WHERE productID = $sort_products[productID]
				AND pantry=$control[users_pantry_id]
				ORDER BY shelf,bin";
		$stmt = $control['db']->prepare($sql2);
		$stmt->execute();	
		$result2 = $stmt->fetchAll();		
		foreach($result2 as $pantryinfo) {
			$typeinfo=getPantryTypeInfoRow( $control['db'], $sort_products['productID'], $pantryinfo['typenum'] );
			$isPersonal = ( $sort_products['personal'] == "Yes" );
			$this->writeTypeLine( $start_x+9, $start_y, $isPersonal, $pantryinfo['instock'], $typeinfo['type'] );
			$inStockTotal+=$pantryinfo['instock'];			
			$start_y+=PRODUCT_LINE_HEIGHT;			
		}
		return $inStockTotal;
	}
	
	/**
	 * doStaffOnlyMsgBar()
	 * @public
	 */		
	public function doStaffOnlyMsgBar( $page, $col, $lang ) {
		
		$save_x=$this->GetX();
		$save_y=$this->GetY();	
		
		if ( $col == 1 )
			$x= COL2_START_X - 16;
		else
			$x=COL2_START_X +83;
		
		$h=235;

		if ( $page == 1 && $lang != 1 )
			$h=230;
		$y=$save_y-2;

	// fill staff only bar		
		$this->SetFillColor(220,220,220);
		$this->writeHTMLCell(12,$h, $x, $y,"", 0,0,1,true,"C",true);
		
	// repeat "STAFF ONLY" 4 times	
		$this->setColor("text", 130,130,130);
		$this->SetFont('helvetica', 'IB', 8);		
		$str="STAFF ONLY";
		$y+=2;
		for ( $pos = 0; $pos<strlen($str); $pos++ ) {
			$this->writeHTMLCell(10,5, $x+.5, $y,substr($str,$pos,1), 0,0,1,true,"L",true);
			$y+=4;
		}	
		$y+=20;
		for ( $pos = 0; $pos<strlen($str); $pos++ ) {
			$this->writeHTMLCell(10,5, $x+.5, $y,substr($str,$pos,1), 0,0,1,true,"L",true);
			$y+=4;
		}		
		$y+=20;
		for ( $pos = 0; $pos<strlen($str); $pos++ ) {
			$this->writeHTMLCell(10,5, $x+.5, $y,substr($str,$pos,1), 0,0,1,true,"L",true);
			$y+=4;
		}	
		$y+=20;
		for ( $pos = 0; $pos<strlen($str); $pos++ ) {
			$this->writeHTMLCell(10,5, $x+.5, $y,substr($str,$pos,1), 0,0,1,true,"L",true);
			$y+=4;
		}			

	// restore default fill and text colors	
		$this->SetFillColor(255,255,255);
		$this->setColor("text");		
		$this->SetXY( $save_x, $save_y );
	}
	
	/**
	 * doStaffFillCircle()
	 * @public
	 */		
	public function doStaffFillCircle( $isPersonal ) {	
	
	global 	$column, $hh_lang;

		if ($column == 1)
			$start_x=COL2_START_X - 10;
		else
			$start_x=COL2_START_X + 89;
		$start_y=$this->GetY();	
		
	// shade background
		$this->SetFillColor(220,220,220);
		$this->writeHTMLCell(8,8, $start_x-2, $start_y-2," ", 0,0,1,true,"C",true);	
		$this->SetFillColor(255,255,255);	
		if ( $isPersonal )
			$this->Image("images/blank-line.png", $start_x, $start_y+3.5, 5, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);					
		else	
			$this->Image("images/fill-circle.png", $start_x, $start_y, 4, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);	

		$this->SetY( $start_y );
	}

	
	/**
	 * doPantryName($control)
	 * @public
	 */		
	public function doPantryName( $control ) {
	
		$row=getPantryRow($control['db'], $control['users_pantry_id']);			
		return $row['name'];
	}
	
} // end of extended class

/* MAINLINE */

// create new PDF document
	$pdf = new MC_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
	$pdf->SetCreator(PDF_CREATOR);
//	$pdf->SetAuthor('Nicola Asuni');
	$pdf->SetTitle('');
//	$pdf->SetSubject('TCPDF Tutorial');
//	$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 010', PDF_HEADER_STRING);
	$pdf->SetHeaderData("pep_logo.jpg", PDF_HEADER_LOGO_WIDTH, "Shopping List", "PDF_HEADER_STRING");

// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
	//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetAutoPageBreak(false);

// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'TCPDF-master/lang/eng.php')) {
		require_once(dirname(__FILE__).'TCPDF-master/lang/eng.php');
		$pdf->setLanguageArray($l);
	}


// 8-27-2018: version 3.7 update - sort table already loaded in AddHistory.php
//	fillSortTableEF( 1 );	// defined in HouseholdsEligibleFor.php	

	$pdf->doShoppingList();	

	$pdf->Output('shopping_list.pdf', 'I');
	
/* END MAINLINE */	
