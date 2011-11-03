<?php
/**
*	UI The  site map 
*
* @author	9* <9@u9.ru> 03102011
* @access	public
* @package	SBIN Diesel
*/
class ui_site_map extends user_interface
{
	public $title = 'Карта сайта';
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_ul_map()
	{
		$data =  array();
		$di = data_interface::get_instance('site_map');
		$data_r = $di->get_all();
		$data['records'] = $data_r['childs'];
		return $this->parse_tmpl('default.html',$data);
	}
	
}
?>
