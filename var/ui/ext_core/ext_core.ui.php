<?php
/**
*	UI The ext_core lib
*
* @author	9* 9@u9.ru
* @access	public
* @package	SBIN Diesel	
*/
class ui_ext_core extends user_interface
{
	public $title = 'Библитека Экст коре';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_main()
	{
		return '<!-- ExtCore Included-->';
	}
}
?>
