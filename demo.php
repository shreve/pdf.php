<?php

date_default_timezone_set('America/Detroit');
require_once( 'pdf.php' );

try {
	$pdf = new PDF(array(
		'title' => 'PDF.php Demo',
		'author' => 'Shreve',
		'subject' => 'PDF Generation',
		'keywords' => 'github demo pdf class'
	));
	$pdf->item_header_row(array(
		'Date' => 1,
		'Customer' => 3,
		'Purchase' => 3,
		'Ammount' => 1
	));

	$sales = array(
		array('2013-08-15', 'Jim', 'Hummus', 3.35),
		array('2013-08-15', 'Aziz', 'Jack Daniels, Coke, Condoms, Blank CDs', 45.13),
		array('2013-08-16', 'Lindsey', 'Bug Spray, Marshmallows', 9.87)
	);

	foreach($sales as $s)
	{
		$pdf->item_row(array(
			$s[0] => 1,
			$s[1] => 3,
			$s[2] => 3,
			$s[3] => 1
		));
	}

	$pdf->render('pdf-php.pdf');

} catch ( Exception $e ) {
	echo "$e";
	echo '<h3>'.$e->getMessage().'</h3>';
	echo '<pre>';
	echo $e->getTraceAsString();
	echo '</pre>';
	die();
}
