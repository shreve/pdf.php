PDF.php
============================

a php wrapper for TCPDF stuff


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

this class is meant to be built onto.

I reccomend buinding functions entirely in the class so your controllers can be as simple as

    $report = DB::select()...
    $pdf = new PDF();
    $pdf->buildExpenseReport($report);
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

When working inside the class, it is important to note that the PDF class doens't extend the
TCPDF class. To access TCPDF functions from inside the class, use
    
    $this->_pdf->TCPDFFunction();

