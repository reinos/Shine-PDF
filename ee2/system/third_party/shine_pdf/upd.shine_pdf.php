<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shine_pdf_upd {

	var $version = '0.1';
	
	function __construct()
	{
		$this->EE =& get_instance();
	}
	
	function install()
	{
		$this->EE->load->dbforge();
		
		$data = array(
			'module_name' => 'Shine_pdf',
			'module_version' => $this->version,
			'has_cp_backend' => 'n',
			'has_publish_fields' => 'n'
		);
		
		$this->EE->db->insert('modules', $data);
		
		return TRUE;
	}
	
	function uninstall()
	{
		$this->EE->load->dbforge();
		$this->EE->db->select('module_id');
		
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Shine_pdf'));
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', 'Shine_pdf');
		$this->EE->db->delete('modules');
			
		return TRUE;
	}
	
	function update($current = '')
	{
		return TRUE;
	}

}
// END CLASS Shine_pdf_upd

/* End of file upd.shine_pdf.php */
/* Location: ./system/expressionengine/third_party/modules/shine_pdf/upd.shine_pdf.php */