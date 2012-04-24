<?php
/**
*	ПИ "Поиск по сайту"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_search extends user_interface
{
	public $title = 'Поиск по сайту';

	protected $deps = array(
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        public function pub_content()
        {
                $di = data_interface::get_instance('search');
		$di->set_args($this->get_args());
		$data = array(
			'args' => $this->get_args(),
			'results' => $di->do_search(),
		);
                return $this->parse_tmpl('content.html', $data);
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
