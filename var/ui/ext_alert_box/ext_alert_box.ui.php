<?php
/**
*	UI The ext_alert_box lib
*
* @author	9* 9@u9.ru
* @access	public
* @package	SBIN Diesel	
* @dependincies ext_core.ui
*/
class ui_ext_alert_box extends user_interface
{
	public $title = 'Библитека Экст коре alert Box';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_main()
	{
		return '<!-- Extalert_box Included-->';
	}
}
?>
