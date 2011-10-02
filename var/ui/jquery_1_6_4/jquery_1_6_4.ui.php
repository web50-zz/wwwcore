<?php
/**
*	UI The JQuery 1.6.4. lib
*
* @author	9* 9@u9.ru
* @access	public
* @package	SBIN Diesel	
*/
class ui_jquery_1_6_4 extends user_interface
{
	public $title = 'JS jquery v 1.6.4  12 sept 2011'; 
	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_main()
	{
		return '<!-- Jquery  1.6.4 Included-->';
	}
}
?>
