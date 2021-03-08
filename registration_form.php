<?php
/**
 * registration form.php
 * written: 7/8/2020
 *
 * Description : Prints household registration form using TCPDF. 
 *				 Supported languages: English, Spanish, Hmong, French, and Arabic.
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
	require_once('TCPDF-master/tcpdf.php');
	
	if (!$control=validUser())
		die("UNAUTHORIZED ACCESS. YOU MUST SIGN IN TO USE PEPBASE.");

	$control=fillControlArray($control, $config, "regform");
	$pantries= getPantryRow( $control['db'], $control['users_pantry_id'] );
	$household=getHouseholdRow( $control['db'], $control['hhID'] );

	$sql = "SELECT * FROM members WHERE householdID = :householdID AND is_primary = 1";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();
	$total = $stmt->rowCount();			
	if ($total > 0) {  
		$members = $stmt->fetch();
		showForm( $household, date("m/d/Y", strtotime("$members[dob]")), ucname($members['gender']), ucname($members['allergies']), ucname($members['incontinent']), $pantries['name'] );
	}	

function showForm( $household, $dob, $gender, $allergies, $incontinence, $pName ) {
	global $control;
	
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	//$pdf->SetCreator(PDF_CREATOR);
	//$pdf->SetAuthor('Nicola Asuni');
	//$pdf->SetTitle('TCPDF Example 048');
	//$pdf->SetSubject('TCPDF Tutorial');
	//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

	// set default header data
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 048', PDF_HEADER_STRING);

	// set header and footer fonts
	//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}
		
	// add a page
	$pdf->AddPage();
	
	

	//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

	$pdf->setRTL(false);
	//$pdf->SetFont('helvetica', '', 8);

	$lang=$household['language'];
	if ( $lang == 5 )
		$ffamily="aefurat";
	else
		$ffamily="helvetica";
	
	$fullName=stripslashes(ucname($household['firstname'])) . " " . stripslashes(ucname($household['lastname']));

// Heading	
	$head1[1]="PERSONAL ESSENTIALS PANTRY REGISTRATION FORM";
	$head1[2]="Formulario de Registro de Despensa de Artículos Esenciales Personales";		
	$head1[3]="TXHEEJ TXHEEM YWJ YIM DAB TSI YUAV TAU NPE THAWJ";
	$head1[4]="ESSENTIELS PERSONNELS FORMULAIRE D'INSCRIPTION DE PANTRY";
	$head1[5]= "استمارة التسجيل الشخصية لبطاقات الهوية الشخصية";
	
	$head2[1] = "PLEASE PRINT ALL INFORMATION"; 
	$head2[2] = "POR FAVOR, ESCRIBA TODA LA INFORMACIÓN EN LETRA DE MOLDE";
	$head2[3] = "THOV TXHUA QHIA TXOG TAG NRHO COV NTAUB NTAWV";
	$head2[4] = "VEUILLEZ IMPRIMER TOUTES LES INFORMATIONS";
	$head2[5] = "يرجى طباعة جميع المعلومات";		
	
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

	$pdf->SetFont($ffamily,'B',14);	
	$pdf->Cell(0,9,$head1[$lang],'LTR',1,'C');
	$pdf->SetFont($ffamily,'I',10);	
	$pdf->Cell(0,7,$head2[$lang],'LBR',1,'C');
	
// Section A - Primary Shopper (please verify your information and write in any needed corrections)
	$instr[1]=" Section A - Primary Shopper (verify your information and write in any needed corrections)";
	$instr[2]=" Sección A - Usuario principal (verifique su información y escriba cualquier corrección necesaria)";
	$instr[3]=" Seem A - Thawj Tus Khw Tshaj Lij (xyuas koj cov ntaub ntawv thiab sau rau hauv cov kev kho kom raug)";
	$instr[4]=" Section A - Acheteur principal (vérifier vos informations et écrire les corrections nécessaires)";
	$instr[5]= " القسم أ - المتسوق الأساسي (تحقق من معلوماتك واكتب أي تصحيحات مطلوبة)";

	$pdf->SetFont($ffamily,'B',10);	
	$pdf->Cell(0,7,$instr[$lang],'BRL',1,'L');
	$pdf->SetFont($ffamily,'',8);	
	$label1[1]=	'last name';	
	$label1[2]=	'apellido';		
	$label1[3]=	'lub xeem';
	$label1[4]=	'nom de famille';
	$label1[5]=	"لقب";	
	
	$label2[1]=	'first name';	
	$label2[2]=	'primer nombre';		
	$label2[3]=	'thawj lub npe';
	$label2[4]=	'Prénom';
	$label2[5]=	"الأسم الأول";		
	
	$label3[1]=	'date of birth';	
	$label3[2]=	'fecha de nacimiento';		
	$label3[3]=	'hnub yug';
	$label3[4]=	'date de naissance';	
	$label3[5]=	"تاريخ الميلاد";		
	
	$label4[1]=	'household id';	
	$label4[2]=	'household id';		
	$label4[3]=	'household id';
	$label4[4]=	'household id';		
	$label4[5]=	"household id";				

	$pdf->Cell(65,4,$label1[$lang],'TRL',0,'L');
	$pdf->Cell(65,4,$label2[$lang],'TRL',0,'L');	
	$pdf->Cell(32,4,$label3[$lang],'TRL',0,'L');	
	$pdf->Cell(28,4,$label4[$lang],'TR',1,'L');
	$pdf->SetFont('Courier','',12);	
	$pdf->Cell(65,6,stripslashes(strtoupper($household['lastname'])),'BRL',0,'L');
	$pdf->Cell(65,6,stripslashes(strtoupper($household['firstname'])),'BRL',0,'L');
	
	$pdf->Cell(32,6,$dob,'BR',0,'L');	
	$pdf->Cell(28,6,$household['id'],'BR',1,'L');		
		
// address, apt #
	$address= strtoupper($household['streetnum']) . " " . strtoupper($household['streetname']);
	$label5[1]=	'address (number and street)';	
	$label5[2]=	"dirección (número y calle)";		
	$label5[3]=	'chaw nyob (naj npawb thiab txoj kev)';
	$label5[4]=	"adresse (numéro et rue)";
	$label5[5]=	"العنوان (رقم وشارع)";		
	
	$label6[1]=	'apartment number';	
	$label6[2]=	"Número de apartamento";		
	$label6[3]=	'chav tsev nyob';
	$label6[4]=	"numéro d'appartement";		
	$label6[5]=	"رقم الشقة";			
	
	$pdf->SetFont($ffamily,'',8);	
	$pdf->Cell(150,4,$label5[$lang],'TRL',0,'L');
	$pdf->Cell(40,4,$label6[$lang],'TR',1,'L');	
	$pdf->SetFont('Courier','',12);	
	$pdf->Cell(150,6,$address,'BRL',0,'L');
	$pdf->Cell(40,6,$household['apartmentnum'],'BRL',1,'L');

// city, state, zip, email
	$label7[1]=	'city';	
	$label7[2]=	"ciudad";		
	$label7[3]=	'lub nroog';
	$label7[4]=	"ville";		
	$label7[5]=	"مدينة";			
	
	$label8[1]=	'state';	
	$label8[2]=	"estado";		
	$label8[3]=	'lub xeev';
	$label8[4]=	"Etat";	
	$label8[5]=	"دولة";			

	$labelz[1]=	'zip code';	
	$labelz[2]=	"código postal";		
	$labelz[3]=	'zip code';
	$labelz[4]=	"code postal";
	$labelz[5]=	"الرمز البريدي";		
	

	$zip=$household['zip_five'];
	if ($household['zip_four'])
		$zip.= "-" . $household['zip_four']; 	
	$pdf->SetFont($ffamily,'',8);	
	$pdf->Cell(100,4,$label7[$lang],'TRL',0,'L');
	$pdf->Cell(30,4,$label8[$lang], 'TRL',0,'L');	
	$pdf->Cell(60,4,$labelz[$lang],'TRL',1,'L');
	$pdf->SetFont('Courier','',12);	
	$pdf->Cell(100,6,strtoupper($household['city']),'BRL',0,'L');
	$pdf->Cell(30,6,strtoupper($household['state']),'BRL',0,'L');
	$pdf->Cell(60,6,$zip,'BRL',1,'L');

// phone1, phone2, email
	$phone1="";
	$phone2="";
	if (is_numeric($household['phone1']))
		$phone1= ExpandPhone($household['phone1']);		
	if (is_numeric($household['phone2']))
		$phone2= ExpandPhone($household['phone2']); 
	
	$label9[1]=	'phone 1';	
	$label9[2]=	"teléfono 1";		
	$label9[3]=	'xov tooj 1';
	$label9[4]=	"téléphone 1";	
	$label9[5]=	"الهاتف 1";		

	$label10[1]='phone 2';	
	$label10[2]="teléfono 2";		
	$label10[3]='xov tooj 2';
	$label10[4]="téléphone 2";	
	$label10[5]="الهاتف 2";		

	$label11[1]='email';	
	$label11[2]="correo electrónico";		
	$label11[3]='email';
	$label11[4]="email";	
	$label11[5]="البريد الإلكتروني";	
	
	$pdf->SetFont($ffamily,'',8);	
	$pdf->Cell(40,4,$label9[$lang],'TRL',0,'L');
	$pdf->Cell(40,4,$label10[$lang], 'TRL',0,'L');	
	$pdf->Cell(110,4,$label11[$lang],'TR',1,'L');		
	$pdf->SetFont('Courier','',12);	
	$pdf->Cell(40,6,$phone1,'BRL',0,'L');
	$pdf->Cell(40,6,$phone2,'BRL',0,'L');
	$pdf->Cell(110,6,$household['email'],'BRL',1,'L');

// Section B - Household Members
	$instr2[1]=" Section B - Household Members (list all household members, use reverse side if necessary)";
	$instr2[2]=" Sección B - Miembros del hogar (escriba todos los miembros de la familia, use el dorso si es necesario)";
	$instr2[3]=" Section B - Household Members (sau tag nrho cov neeg hauv tsev neeg, siv sab nraud yog tias tsim nyog)";
	$instr2[4]=" Section B - Membres du ménage (liste tous les membres du ménage, utilisez le verso si nécessaire)";
	$instr2[5]=" القسم (ب) - أفراد الأسرة (قائمة بجميع أفراد الأسرة ، استخدم الجانب العكسي إذا لزم الأمر)";

	$label12[1]='full name';	
	$label12[2]="nombre completo";		
	$label12[3]='tag nrho lub npe';
	$label12[4]="nom complet";	
	$label12[5]="اسم كامل";
	
	$label13[1]='gender';	
	$label13[2]="género";		
	$label13[3]='poj niam txiv neej';
	$label13[4]="le genre";	
	$label13[5]="جنس";		

	$label14[1]='allergies?';	
	$label14[2]="alergias?";		
	$label14[3]='ua xua?';
	$label14[4]="allergies?";
	$label14[5]="الحساسية؟";		
	
	$label15[1]='incontinence?';	
	$label15[2]="incontinencia?";		
	$label15[3]='kev tswj tsis tau?';
	$label15[4]="incontinence?";
	$label15[5]="سلس البول";		
	
	$pdf->SetFont($ffamily,'B',10);	
	$pdf->Cell(0,7,$instr2[$lang],'BRL',1,'L');
	$pdf->SetFont($ffamily,'',10);	
	$pdf->Cell(80,7,$label12[$lang],'BRL',0,'C');
	$pdf->Cell(27,7,$label3[$lang],'BRL',0,'C');
	$pdf->Cell(27,7,$label13[$lang],'BRL',0,'C');
	$pdf->Cell(27,7,$label14[$lang],'BRL',0,'C');	
	$pdf->Cell(29,7,$label15[$lang],'BRL',1,'C');	
	$pdf->Cell(5,8,'1.','BL',0,'L');	
	$pdf->Cell(75,8, $fullName,'BR',0,'L');
	$pdf->Cell(27,8,$dob,'BRL',0,'L');
	$pdf->Cell(27,8,$gender,'BRL',0,'L');
	$pdf->Cell(27,8,$allergies,'BRL',0,'L');
	$pdf->Cell(29,8,$incontinence,'BRL',1,'L');	

// 6-7-2018: Print household members.		-mlr

	$numMembers=1;
	
	$sql = "SELECT * FROM members WHERE householdID = :householdID AND is_primary = 0 ORDER BY dob";	
	$stmt = $control['db']->prepare($sql);
	$stmt->bindParam(':householdID', $control['hhID'], PDO::PARAM_INT);	
	$stmt->execute();	
	$result = $stmt->fetchAll();		
	foreach ($result as $members) {	
	
		$numMembers++;  
		$memberName      = stripslashes(ucname($members['firstname']) . " " . ucname($members['lastname']));
		$memberDOB = date('m/d/Y', strtotime($members['dob']));		
		$memberGender    = ucname($members['gender']);
		$memberAllergies = ucname($members['allergies']);
		$memberIncontinent = ucname($members['incontinent']);

		$pdf->Cell(5,8,$numMembers . '.','BL',0,'L');	
		$pdf->Cell(75,8, $memberName,'BR',0,'L');
		$pdf->Cell(27,8,$memberDOB,'BRL',0,'L');
		$pdf->Cell(27,8,$memberGender,'BRL',0,'L');
		$pdf->Cell(27,8,$memberAllergies,'BRL',0,'L');		
		$pdf->Cell(29,8,$memberIncontinent,'BRL',1,'L');
	}

	$numMembers++;
	for ($y = $numMembers; $y <= 9; $y++) {	
		$num=$y.".";
		$pdf->Cell(5,8,$num,'BL',0,'L');	
		$pdf->Cell(75,8,'','BR',0,'L');
		$pdf->Cell(27,8,'','BRL',0,'L');
		$pdf->Cell(27,8,'','BRL',0,'L');
		$pdf->Cell(27,8,'','BRL',0,'L');		
		$pdf->Cell(29,8,'','BRL',1,'L');
	}

// Section C - Pantry Guest Agreement
	$instr3[1]=" Section C - Pantry Guest Agreement";	
	$instr3[2]=" Sección C - Acuerdo de usuario de despensa";		
	$instr3[3]=' Seem C - Daim Ntawv Pom Zoo Ntawm Cov Neeg Qhua';
	$instr3[4]=" Section C - Accord de l'invité de Pantry";	
	$instr3[5]="القسم C - اتفاقية Guest House";		

	$agree[1][1]="I, $fullName, am of legal age to receive essentail goods from the $pName in accordance with the terms of this Agreement as follows:";	
	$agree[1][2]="Yo, $fullName, soy mayor de edad para recibir artículos esenciales de $pName de acuerdo con los términos de este Acuerdo de la siguiente manera:";
	$agree[1][3]="Kuv, $fullName, kuv hnub nyoog muaj hnub nyoog kom tau txais cov khoom tseem ceeb ntawm $pName raws li cov nqe lus ntawm Daim Ntawv Pom Zoo no raws li nram no:";
	$agree[1][4]="Moi, $fullName, j'ai l'âge légal pour recevoir des biens essentiels de l'$pName conformément aux termes du présent Accord comme suit:";
	$agree[1][5]="أنا ، الاسم الكامل بالدولار ، أنا في سن قانونية لاستلام السلع الأساسية من $pName وفقًا لبنود هذه الاتفاقية كما يلي:";
	
	$agree[2][1]="The $pName welcomes persons of any race, belief, sexual orientation, gender or disability.";
	$agree[2][2]="El $pName da la bienvenida a personas de cualquier raza, creencia, orientación sexual, género o discapacidad.";		
	$agree[2][3]="$pName txais tos cov neeg ntawm ib haiv neeg, kev ntseeg, kev plees kev yig, poj niam los txiv neej lossis xiam oob khab.";
	$agree[2][4]="L'$pName accueille les personnes de toute race, croyance, orientation sexuelle, genre ou handicap.";	
	$agree[2][5]="ترحب $pName بأشخاص من أي عرق أو عقيدة أو توجه جنسي أو جنس أو إعاقة.";	
	
	$agree[3][1]="I accept that I am here because of financial need, and that the $pName uses 200% of the federally approved HHS (Health and Human Services Department) poverty guidelines.";
	$agree[3][2]="Accepto que estoy aquí por necesidad económica, y que el $pName sigue las normas aprobadas del gobierno federal de 200% de pobreza del Departamento de Salud y Servicios Humanos (HHS por sus siglas en inglés).";
	$agree[3][3]="Kuv lees tias kuv nyob ntawm no vim kev xav tau nyiaj txiag, thiab tias tus $pName siv 200% ntawm tsoomfwv HHS tau txais kev tso cai (Kev Noj Qab Haus Huv thiab Pab Tib Neeg) cov kev qhia txog kev txom nyem.";
	$agree[3][4]="J'accepte que je sois ici pour des raisons financières et que le $pName utilise 200% des lignes directrices sur la pauvreté approuvées par le gouvernement fédéral (Santé et Services à la personne).";
	$agree[3][5]="أقبل بأنني هنا بسبب الحاجة المالية ، وأن $pName يستخدم 200 ٪ من المبادئ التوجيهية للفقر (إدارة الصحة والخدمات البشرية) المعتمدة من الحكومة الفيدرالية.";

	$agree[4][1]="I understand that my participation in the $pName is voluntary. Any personally identifiable information collected is required for participation and will be used for that purpose only.";
	$agree[4][2]="Entiendo que mi participación en $pName es voluntaria. Cualquier información de identificación personal recopilada es necesaria para la participación y se utilizará solo para ese fin.";
	$agree[4][3]="Kuv to taub tias kuv kev koom tes hauv $pName yog nyob ntawm siab yeem. Cov ntaub ntawv pov thawj ntawm tus kheej yog yuav tsum muaj rau kev koom tes thiab yuav siv rau qhov hom phiaj no nkaus xwb.";
	$agree[4][4]="Je comprends que ma participation au $pName est volontaire. Toute information personnelle identifiable collectée est requise pour la participation et sera utilisée uniquement à cette fin.";
	$agree[4][5]="أتفهم أن مشاركتي في $pName اختياري. أي معلومات تعريف شخصية يتم جمعها مطلوبة للمشاركة وستستخدم لهذا الغرض فقط.";

	$agree[5][1]="I consent permission to anyone in my household who is age 18 or over to pick up items for my household. Any goods received will be added to my household's shopping history.";
	$agree[5][2]="Doy mi consentimiento para que cualquier persona en mi hogar que tenga 18 años o más pueda recoger artículos para mi hogar. Cualquier producto recibido se agregará al historial de compras de mi hogar.";
	$agree[5][3]="Kuv tso cai rau ib tus neeg hauv kuv tsev neeg uas muaj hnub nyoog 18 xyoo lossis tshaj saud yam khoom rau kuv tsev neeg. Txhua yam khoom uas tau txais yuav raug ntxiv rau kuv houisehold cov khoom tajlaj.";
	$agree[5][4]="Je consens à ce que toute personne de mon ménage âgée de 18 ans ou plus soit autorisée à ramasser des articles pour mon ménage. Toutes les marchandises reçues seront ajoutées à l'historique d'achat de mon houisehold.";
	$agree[5][5]="أوافق على السماح لأي شخص في عائلتي يبلغ من العمر 18 عامًا أو أكثر بالتقاط أشياء لعائلتي. ستتم إضافة أي بضائع مستلمة إلى سجل التسوق الخاص بي.";
	
	$agree[6][1]="I am allowed to pick up products for my household only. Exceptions may be made (for example, I may be able to pick up for a disabled person who can't come to the Pantry in person).";
	$agree[6][2]="Solo puedo recoger productos para mi hogar. Se pueden hacer excepciones (por ejemplo, puedo recoger para una persona discapacitada que no puede ir personalmente a la despensa).";
	$agree[6][3]="Kuv raug tso cai tuaj tos cov khoom siv rau kuv tsev neeg nkaus xwb. Tej zaum yuav raug (piv txwv, kuv tuaj yeem nqa tuaj rau tus cev tsis tsheej uas tsis tuaj yeem tuaj mus rau Pawg Neeg Saib Xyuas).";
	$agree[6][4]="Je suis autorisé à ramasser des produits pour mon ménage seulement. Des exceptions peuvent être faites (par exemple, je peux être capable de chercher une personne handicapée qui ne peut pas venir à l'office en personne).";
	$agree[6][5]="يُسمح لي بالتقاط المنتجات لأفراد أسرتي فقط. قد يتم إجراء استثناءات (على سبيل المثال ، قد أكون قادراً على التقاط شخص معاق لا يستطيع الحضور إلى المخزن شخصياً).";
	
	$agree[7][1]="I assume the products I receive from the $pName are safe. However, under the Good Samaritan Law, I release the $pName, it's Board, officers, and agents of any liability for harm or damage that may result from the use these products.";
	$agree[7][2]="Supongo que los productos que recibo de $pName son seguros. Dejo libre de cualquier responsabilidad a $pName, a su consejo, oficiales y agentes por daños que puedan ocurrir como resultado del uso de estos productos.";
	$agree[7][3]="Kuv xav tias cov khoom uas kuv tau txais los ntawm $pName yeej zoo. Kuv tso $pName, nws lub Rooj Tswjhwm Saib cov tub ceev xwm, cov tub ceev xwm, thiab cov neeg sawv cev ntawm kev raug mob rau kev puas tsuaj lossis kev puas tsuaj uas yuav tshwm sim los ntawm kev siv cov khoom no.";
	$agree[7][4]="Je suppose que les produits que je reçois du $pName sont sûrs. Je libère le $ pName, c'est le conseil, les dirigeants, et les agents de toute responsabilité pour les dommages ou les dommages qui peuvent résulter de l'utilisation de ces produits.";
	$agree[7][5]="أفترض أن المنتجات التي أتلقاها من $pName آمنة. ومع ذلك ، بموجب قانون السامري الصالح ، أفرج عن $pName ، وهو مجلس الإدارة ، والموظفين ، والوكلاء لأي مسؤولية عن الضرر أو الضرر الذي قد ينتج عن استخدام هذه المنتجات.";

	$agree[8][1]="I understand that any false information I provide to the $pName concerning myself or the members of my household may disqualify me from participation in the pantry.";
	$agree[8][2]="Entiendo que cualquier información falsa que yo proporcione al $pName sobre mí o de los miembros de mi hogar puede descalificar mi participación en la despensa.";
	$agree[8][3]="Kuv to taub tias cov lus qhia tsis tseeb kuv muab rau $pName txog kuv tus kheej los yog cov neeg hauv kuv tsev neeg yuav tsis tsim nyog kuv koom rau hauv lub pantry.";
	$agree[8][4]="Je comprends que toute fausse information que je fournis au $pName concernant moi-même ou les membres de mon ménage peut disqualifier ma participation au garde-manger.";	
	$agree[8][5]="أتفهم أن أي معلومات خاطئة أقدمها لـ $pName تتعلق بنفسي أو أفراد أسرتي قد تستبعد مشاركتي في مخزن المؤن.";
	
	$pdf->SetFont($ffamily,'B',10);
	$pdf->Cell(0,7,$instr3[$lang],'RL',1,'L');
	$pdf->SetFont($ffamily,'',9);

	printAgreements($pdf, $agree, $lang, 8);

	// Signature
	$label15[1]='print full name';			
	$label15[2]="escriba en letra de molde y legible su nombre completo";
	$label15[3]='sau tag nrho lub npe';			
	$label15[4]="imprimer le nom complet";	
	$label15[5]="طباعة الاسم الكامل";		
	
	$label16[1]='signature';			
	$label16[2]="firma";
	$label16[3]='kos npe';			
	$label16[4]="Signature";
	$label16[5]="التوقيع";		

	$label17[1]='date';			
	$label17[2]="fecha";
	$label17[3]='hnub tim';			
	$label17[4]="date";		
	$label17[5]="تاريخ";			
		
	$pdf->SetFont($ffamily,'',12);
	$pdf->Cell(190,4,' ','LR',1,'L');
	$pdf->Cell(2,7,' ','L',0,'L');
	$pdf->Cell(75,7,$fullName,'',0,'L');
	$pdf->Cell(75,7,' ','',0,'L');
	$pdf->Cell(38,7,' ','R',1,'L');
	$pdf->SetFont($ffamily,'',8);	
	$pdf->Cell(2,6,' ','TBL',0,'L');
	$pdf->Cell(75,6,$label15[$lang],'TB',0,'L');
	$pdf->Cell(75,6,$label16[$lang],'TB',0,'L');
	$pdf->Cell(38,6,$label17[$lang],'TBR',0,'L');		
	
	//Close and output PDF document
	$pdf->Output('example_048.pdf', 'I');
}

function printAgreements($pdf, $agreements, $lang, $size) {	

// save y	
	$y = $pdf->getY()+2;
	$top_y=$y-2;

// write agreements	
	$intro=$agreements[1][$lang];
	$pdf->writeHTMLCell(185, 5, '12', $y, $intro, 0,1,0,true,'L',true);
	$y = $pdf->getY()+2;	
	for ($bullet=2; $bullet<=$size; $bullet++) {
//		$txt_width = $pdf->GetStringWidth($agreements[$bullet][$lang]);		
		$txt=$agreements[$bullet][$lang];
		$html="<ul><li>$txt</li></ul>";
		// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)	
		$pdf->writeHTMLCell(190, 5, '5', $y, $html, 0,1,0,true,'L',true);	
		$y = $pdf->getY()+2;	
	}	
	
// print box around bullets	
	$tot_h = $y - $top_y +2;
	$pdf->writeHTMLCell(190, $tot_h, '', $top_y, " ", 1,1,0,true,'L',true);	
	
}
?>