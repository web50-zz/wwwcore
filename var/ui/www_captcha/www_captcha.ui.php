<?php
/**
*	ПИ "WWW Captcha"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_captcha extends user_interface
{
	public $title = 'WWW Captcha';

	protected $deps = array(
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_captcha_image()
	{
		require_once INSTANCES_PATH .'wwwcore/lib/dapphp-securimage-3.2RC2/securimage.php';
		$image = new Securimage();
		$image->show();
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
