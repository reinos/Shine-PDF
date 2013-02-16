<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if( ! class_exists('Channel'))
{
	require_once(PATH_MOD.'channel/mod.channel.php');
}

class Shine_pdf extends Channel {

	function __construct()
	{
		// Derive initial functions from Channel module
		parent::Channel();
	}
	
	/*
	 * Gather output to push to the _process_pdf function
	 */
	function make() {
	
		// Grab our variables and arguments from template tag
		$this->params = array(
			'mode'					=> '',															// Best left alone
			'format'				=> $this->EE->TMPL->fetch_param('format', 'A4'),				// Page format - can be subverted using {...width="" height=""...} params
			'default_font_size'		=> $this->EE->TMPL->fetch_param('default_font_size', 11),		// Base page font size
			'default_font'			=> $this->EE->TMPL->fetch_param('default_font', 'Helvetica'),	// Base page font face
			'margin_left'			=> $this->EE->TMPL->fetch_param('margin_left', 15),				// Left page margin
			'margin_right'			=> $this->EE->TMPL->fetch_param('margin_right', 15),			// Right page margin
			'margin_top'			=> $this->EE->TMPL->fetch_param('margin_top', 16),				// Top page margin - begins below header
			'margin_bottom'			=> $this->EE->TMPL->fetch_param('margin_bottom', 16),			// Bottom page margin - begins from footer
			'margin_header'			=> $this->EE->TMPL->fetch_param('margin_header', 9),			// Margin from page to header
			'margin_footer'			=> $this->EE->TMPL->fetch_param('margin_footer', 9),			// Margin from page to footer
			'orientation'			=> $this->EE->TMPL->fetch_param('orientation', 'P'),			// Page orientation
			'margin_top_auto'		=> $this->EE->TMPL->fetch_param('margin_top_auto', 'no'),		// Automatically calculate top margin
			'margin_bottom_auto'	=> $this->EE->TMPL->fetch_param('margin_bottom_auto', 'no')		// Automatically calculate bottom margin
		);

		// Parse advanced conditionals within parsed {exp:channel:entries} markup
		$input = $this->EE->TMPL->advanced_conditionals( parent::entries() );
		
		// Parse any global variables that might be present in markup
		$input = $this->EE->TMPL->parse_globals( $input );
		
		// Clean tag data
		$this->_clean_tagdata($input);
		
		
		
		// Set custom width and height if applicable
		$this->_custom_width_height();
		
		// Set orientation if applicable
		$this->_set_orientation();
		
		// Process final output using mPDF
		$this->_process_pdf($this->params);
		
	}
	
	/*
	 * Process final output using mPDF through our EE PDF library
	 */
	private function _process_pdf() {
	
		// Get the EE PDF library
		$this->EE->load->library('ee_pdf');
		
		// Push our previously-declared tag data to the library
		$this->pdf = $this->EE->ee_pdf->load($this->params);

		// Set automatic margins if needed
		$this->_set_auto_margins();
		
		// Set PDF header if applicable
		if(isset($this->header))
		{
			$this->pdf->SetHTMLHeader($this->header);
		}
		
		// Set PDF footer if applicable
		if(isset($this->footer))
		{
			$this->pdf->SetHTMLFooter($this->footer);
		}
		
		// Write HTML to mPDF
		$this->pdf->WriteHTML($this->body);
		
		// Output final PDF
		$this->pdf->Output();
		
		exit;
		
	}
	
	/*
	 * Define what manner of automatic margining to use if auto margins are desired
	 */
	private function _set_auto_margins() {
		
		// Set option according to tag input
		switch ($this->params['margin_top_auto'])
		{
			case 'stretch' :
				$margin_auto = 'stretch';
			break;
			case 'pad' :
				$margin_auto = 'pad';
			break;
			default :
				$margin_auto = FALSE;
			break;
		}
		
		// Set mPDF option
		$this->pdf->setAutoTopMargin = $margin_auto;
		
		// Set option according to tag input
		switch ($this->params['margin_bottom_auto'])
		{
			case 'stretch' :
				$margin_auto = 'stretch';
			break;
			case 'pad' :
				$margin_auto = 'pad';
			break;
			default :
				$margin_auto = FALSE;
			break;
		}
		
		// Set mPDF option
		$this->pdf->setAutoBottomMargin = $margin_auto;

	}
	
	/*
	 * Set custom width and height of page if specified
	 */
	private function _custom_width_height() {
		
		$width	= $this->EE->TMPL->fetch_param('width', FALSE);
		$height	= $this->EE->TMPL->fetch_param('height', FALSE);
		
		// Set only if both width and height are declared, otherwise relegate to default format
		if(isset($width) && isset($height) && $width && $height)
		{
			$this->params['format'] = array($width, $height);
		}
		
	}
	
	/*
	 * Set landscape orientation if specified, otherwise default to portrait
	 */
	private function _set_orientation()
	{
		switch($this->params['orientation'])
		{
			case "l" :
				$this->params['orientation'] = 'L';
			break;
			case "L" :
				$this->params['orientation'] = 'L';
			break;
			case "landscape" :
				$this->params['orientation'] = 'L';
			break;
			case "Landscape" :
				$this->params['orientation'] = 'L';
			break;
			default :
				$this->params['orientation'] = 'P';
			break;
		}
		
		if( ! is_array($this->params['format']) && $this->params['orientation'] == 'L')
		{
			$this->params['format'] .= '-L';
		}
	}

	/*
	 * Find first instances of {pdf_header} and {pdf_footer}, parse and eliminate them
	 */
	private function _clean_tagdata($input) {

		// Set Header from {pdf_header} tag pair
		preg_match('/\{pdf_header\b[^}]*\}(.*?)\{\/pdf_header\}/ism',$input,$m);
		if(isset($m[1]))
		{
			$this->header = trim($m[1]);
		}
		
		unset($m);
		
		// Set Footer from {pdf_footer} tag pair
		preg_match('/\{pdf_footer\b[^}]*\}(.*?)\{\/pdf_footer\}/ism',$input,$m);
		if(isset($m[1]))
		{
			$this->footer = trim($m[1]);
		}
		
		// Remove {pdf_header} and {pdf_footer} from output
		$patterns = array(
			'/\{pdf_header\b[^}]*\}(.*?)\{\/pdf_header\}/ism',
			'/\{pdf_footer\b[^}]*\}(.*?)\{\/pdf_footer\}/ism'
		);
		
		$this->body = preg_replace($patterns,'',$input);

	}
	
}
// END CLASS Shine_pdf

/* End of file mod.shine_pdf.php */
/* Location: ./system/expressionengine/third_party/modules/shine_pdf/mod.shine_pdf.php */