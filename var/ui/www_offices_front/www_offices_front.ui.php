<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>  13022014
* @package	SBIN Diesel
*/
class ui_www_offices_front extends user_interface
{
	public $title = 'www: Офисы фронт';

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
		$template = $this->get_args('tmpl', 'default.html');
                $data = array(
			'records' => data_interface::get_instance('www_offices')
				->push_args(array())
				->set_order('id', 'ASC')
				->_get()
				->pop_args()
				->get_results()
		);
                return $this->parse_tmpl($template, $data);
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
