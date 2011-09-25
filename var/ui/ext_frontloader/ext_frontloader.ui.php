<?php
/**
*	UI The ext_frontloader lib - simple dynamic js  loader
*
* @author	9* 9@u9.ru
* @access	public
* @package	SBIN Diesel
* @dependecies  ext_core.ui.php
*/
class ui_ext_frontloader extends user_interface
{
	public $title = 'Библитека Экст фронтлоадер';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_main()
	{
		return '<!-- Ext front loader Included-->';
	}
}
?>
