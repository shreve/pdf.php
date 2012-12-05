<?php defined('SYSPATH') or die('No direct script access.');

require_once Kohana::find_file('vendor/tcpdf', 'tcpdf');
require_once Kohana::find_file('vendor/tcpdf/config/lang', 'eng');


class PDF
{
    protected $_pdf; // The actual PDF, Object of TCPDF class
    public $width; // Width of content area, not the PDF page itself
    public $height; // Height of content area, not the PDF page itself
    public $keywords = array(); // Default keywords you would want for all documents

    public function __construct($attributes = NULL, $margins = NULL)
    {
    	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', TRUE, 'UTF-8', FALSE);

    	$pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($attributes['author'] ? $attributes['author'] : PDF_AUTHOR);
        $pdf->SetTitle($attributes['title'] ? $attributes['title'] : '');
        $pdf->SetSubject($attributes['subject'] ? $attributes['subject'] : '');
        $this->keywords = (!empty($attributes['keywords'])) ? array_merge($attributes['keywords'], $this->keywords) : $this->keywords;
        $pdf->SetKeywords(implode(', ', $this->keywords));

        // Remove default header/footer
        $pdf->setPrintHeader(FALSE);
        $pdf->setPrintFooter(FALSE);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, (is_int($margins[3]) ? $margins[3] : PDF_MARGIN_BOTTOM));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins((is_int($margins[0]) ? $margins[0] : PDF_MARGIN_LEFT), (is_int($margins[1]) ? $margins[1] : PDF_MARGIN_TOP), (is_int($margins[2]) ? $margins[2] : PDF_MARGIN_RIGHT));
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set font
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0);

        $pdf->AddPage();
        $this->_pdf = $pdf;
        $this->set_pdf_width()
        $this->get_width_height();
    }
    public function set_pdf_width()
    {
    	$pthw = $this->_pdf->getPageSizeFromFormat('LETTER');
    	$ptheight = $pthw['height'];
    	$ptwidth = $pthw['width'];
    	switch(PDF_UNIT)
    	{
    		case 'mm':
    			define('PDF_WIDTH', $ptwidth / 2.834645669);
    			define('PDF_HEIGHT', $ptheight / 2.834645669);
    			break;
    		case 'pt':
    			define('PDF_WIDTH', $ptwidth);
    			define('PDF_HEIGHT', $ptheight);
    			break;
    		case 'cm':
    			define('PDF_WIDTH', $ptwidth / 28.346456693);
    			define('PDF_HEIGHT', $ptheight / 28.346456693);
    			break;
    		case 'in':
    			define('PDF_WIDTH', $ptwidth / 72);
    			define('PDF_WIDTH', $ptheight / 72);
    			break;
    	}
    }
    public function get_width_height()
    {
        $this->width = (PDF_WIDTH - ($this->get_margin('left') + $this->get_margin('right')));
        $this->height = (PDF_HEIGHT - ($this->get_margin('top') + $this->get_margin('bottom')));
    }
    public function get_margin($side='left')
    {
        $margins = $this->_pdf->getMargins();
        return $margins[$side];
    }
    public function new_page()
    {
        $this->_pdf->AddPage();
        $this->_pdf->SetXY($this->get_margin('left'),$this->get_margin('top'));
    }
    public function getPages()
    {
        return count($this->_pdf->pages);
    }
    public function removeBlankPages()
    {
        for($i=1; $i < count($this->_pdf->pages)+1; $i++)
        {
            if(strlen($this->_pdf->pages[$i]) < 500)
            {
                $this->_pdf->deletePage($i);
            }
        }
        return $this->getPages();
    }
    public function render($file='temp')
    {
        $this->removeBlankPages();
        $this->_pdf->Output($file.'.pdf', 'I');
        Request::current()->response()->headers('Content-Type', 'application/pdf');
    }


    // Building Blocks
    /**
     *  $items is an array of the header items in the format array('Heading' => width)
     *   a heading with the width of 5 will increase it's own width by 4, while adding
     *   to the total number of units that fit in 100% width.
     *		calculated width = (content width / total cell count) * cell width
     *	$items = array( 'date'=>1, 'full name'=>3, 'account balance'=>3); etc, etc
     */
    public function item_header_row($items,$height=1,$border='TB',$align='L',$fill=0)
    {
        $total_width = 0;
        foreach($items as $width){ $total_width+=$width; }
        foreach($items as $heading => $width)
        {
            $this->_pdf->MultiCell(($this->width/$total_width)*$width, $height,$heading,$border,$align,$fill,0);
        }
        $this->_pdf->Ln();
    }
    /**
     *  item_row is the same idea as item_header_row
     */
    public function item_row($cells,$height=1,$border=0,$align='L',$fill=0)
    {
        $total_width = 0;
        foreach($cells as $width){ $total_width+=$width; }
        foreach($cells as $value => $width)
        {
            $this->_pdf->MultiCell(($this->width/$total_width)*$width, $height,$value,$border,$align,$fill,0,'','',true,0,true);
        }
        $this->_pdf->Ln();
    }
}