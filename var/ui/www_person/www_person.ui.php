<?php
/**
*	ПИ "WWW: Персоны"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_person extends user_interface
{
	public $title = 'www: Персоны';

	protected $deps = array(
		'main' => array(
			'www_person.grid',
		)
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
		$data = array();
		$template = $this->get_args('template', 'content.html');
		$category = $this->get_args('category', '0');
		$data['persons'] =  $this->get_persons($category);
		$data['path']  = data_interface::get_instance('www_person')->path_to_storage;
		$data['category'] = $category;
                return $this->parse_tmpl($template, $data);
        }

	/**
	*	Получить все доступные услуги
	*/
	private function get_persons($category =  0)
	{
                $di = data_interface::get_instance('www_person');
		$di->push_args(array());
		$di->_flush();
		$di->set_order('order');
		if($category != 0)
		{
			$di->set_args(array('_scategory'=>$category));
		}
		$di->_get();
		$di->pop_args();
		return $di->get_results();
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'www_person.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'grid.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Page configure form
	*/
	/*
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	*/
}
?>
