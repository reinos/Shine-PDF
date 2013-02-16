<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ee_pdf {

	var $folder_name = 'shine_pdf';

	public function __construct()
	{
		$this->EE = & get_instance();
		
		// Require the mPDF library
		require_once PATH_THIRD.$this->folder_name.'/libraries/mpdf/mpdf.php';
	}
	
	/*
	 * Creates a CI handler for the mPDF object
	 */
	public function load($params)
	{
		// Gives us our final output
		return new mPDF(
			$params['mode'],
			$params['format'],
			$params['default_font_size'],
			$params['default_font'],
			$params['margin_left'],
			$params['margin_right'],
			$params['margin_top'],
			$params['margin_bottom'],
			$params['margin_header'],
			$params['margin_footer'],
			$params['orientation']
		);
	}
	
}