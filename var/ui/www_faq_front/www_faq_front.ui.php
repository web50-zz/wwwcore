<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>
* @package	SBIN Diesel
*/
class ui_www_faq_front extends user_interface
{
	public $title = 'WWW: Faq front';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*	Вывод контента
	*/
	protected function pub_content()
	{
		$template = $this->get_args('tmpl', 'content.html');

		$data = array(
			'records' => data_interface::get_instance('www_faq')
					->_flush()
					->push_args(array('_nid'=>1))
					->set_order('left', 'asc')
					->_get()
					->pop_args()
					->get_results()
		);
		$data = array_merge($data, (array)$this->args);

                return $this->parse_tmpl($template, $data);
	}

}
?>
