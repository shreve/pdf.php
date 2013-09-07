<?php

#if(isset(Kohana))
#{
#	require_once Kohana::find_file('vendor/tcpdf', 'tcpdf');
#	require_once Kohana::find_file('vendor/tcpdf/config/lang', 'eng');
#}
if(file_exists(dirname(__file__).'/tcpdf/tcpdf.php'))
{
	require_once dirname(__file__).'/tcpdf/tcpdf.php';
}

class PDF
{
    protected $_pdf; // The actual PDF, Object of TCPDF class
    public $width; // Width of content area, not the PDF page itself
    public $height; // Height of content area, not the PDF page itself
    public $keywords = ''; // Default keywords you would want for all documents
	
	/**
	 * PDF.php Constructor
	 * @param array $attributes author, title, subject, keywords[]
	 * @param array $margins array(left, top, right, bottom)
	 * @return PDF $this
	 */
    public function __construct($attributes = NULL, $margins = NULL)
    {
    	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', TRUE, 'UTF-8', FALSE);

    	$pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($attributes['author'] ?: PDF_AUTHOR);
        $pdf->SetTitle($attributes['title'] ?: '');
        $pdf->SetSubject($attributes['subject'] ?: '');
		$this->keywords = is_string($this->keywords) ? $this->keywords : implode(', ', array_merge($attributes['keywords'], $this->keywords));
        $pdf->SetKeywords($this->keywords);

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
		$this->set_pdf_width();
		$this->get_width_height();
		return $this;
    }

	public function __get($name)
	{
		if(isset($this->$name))
			return $this->$name;
		elseif(isset($this->_pdf->$name))
			return $this->_pdf->$name;
		else
			throw new Exception("Can't get '$name'");
	}

	public function __set($name, $value)
	{
		if(isset($this->$name))
			return $this->$name = $value;
		elseif(isset($this->_pdf->$name))
			return $this->_pdf->$name = $value;
		else
			throw new Exception("Can't set '$name' to '$value'");
	}

	public function __call($method, $params)
	{
		if(method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		elseif( method_exists($this->_pdf, $method) )
			return call_user_func_array(array($this->_pdf, $method), $params);
		elseif( method_exists(TCPDF_STATIC, $method) )
			return call_user_func_array(array(TCPDF_STATIC, $method), $params);
		else
			throw new Exception("No Method '$method'");
	}
	
	public function set_pdf_width()
    {
		$pthw = $this->getPageSizeFromFormat('LETTER');
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
        $margins = $this->getMargins();
        return $margins[$side];
    }
    public function new_page()
    {
        $this->AddPage();
        $this->SetXY($this->get_margin('left'),$this->get_margin('top'));
    }
    public function removeBlankPages()
    {
		$pages = $this->getNumPages();
		for($i=1; $i < count($pages)+1; $i++)
        {
            if(strlen($pages[$i]) < 500)
            {
                $this->deletePage($i);
            }
        }
        return $this->getNumPages();
    }
    public function render($file='temp')
    {
        #$this->removeBlankPages();
        $this->Output($file.'.pdf', 'I');
		header('Content-Type: application/pdf');
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
