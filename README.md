PDF.php
============================

a php wrapper for TCPDF


## Installation ##

I've included TCPDF bundled in this repository.

**If you're using Kohana**

Place the unziped folder in 
    application/vendors/tcpdf/

Place this php file in 
    application/classes/

**BAM!** You're good to go

**Otherwise, place this whole directory in whatever vendor folder you have.**

## Usage ##

I reccomend building classes that extend PDF, so you can generate individual documents as easy as

    class ExpenseReport extends PDF { }

	$pdf = new ExpenseReport($report);
    $pdf->render($report->title)

the constructor function accepts two arrays, one for attributes, and one for margins

    $attributes = array(
    	'author' => 'Web Design Co.',
    	'title' => $report->title,
    	'subject' => $report->subject,
    	'keywords' => array('reports','expenses','business cat')
	);
	$margins = array(10,25,10,25); // Left, Top, Right, Bottom
	$pdf = new PDF($attributes, $margins);
