<?php

require_once( 'pdf.php' );

try {
	$pdf = new PDF(array(
		'title' => 'PDF.php Demo',
		'author' => 'Shreve',
		'subject' => 'PDF Generation',
		'keywords' => 'github demo pdf class'
	));

} catch ( Exception $e ) {
	echo "$e";
	echo '<h3>'.$e->getMessage().'</h3>';
	echo '<pre>';
	echo $e->getTraceAsString();
	echo '</pre>';
}
