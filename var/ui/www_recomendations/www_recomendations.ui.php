<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru> 07022014
* @package	SBIN Diesel
*/
class ui_www_recomendations extends user_interface
{
	public $title = 'WWW: Рекомендательные  письма';

	protected $deps = array(
		'main' => array(
			'www_recomendations.grid',
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
		$data['records'] =  $this->get_recomendations();
		$data['path']  = data_interface::get_instance('www_recomendations')->path_to_storage;
                return $this->parse_tmpl('content.html', $data);
        }

	public function pub_list_parametric()
	{
                $di = data_interface::get_instance('www_recomendations');
		$template = $this->get_args('template','content.html');
		$limit = $this->get_args('limit','10');
		$order = $this->get_args('order','order');
		$dir = $this->get_args('dir','ASC');
		$di->push_args(array());
		$di->_flush();
		$di->set_order($order,$dir);
		$di->set_limit($limit);
		$di->_get();
		$di->pop_args();
		$data['records'] = $di->get_results();
                return $this->parse_tmpl($template, $data);
	}
	/**
	*	Получить все доступные услуги   9* Не понмю может это вообще не нужно  надо бы коцнуть
	*/
	private function get_recomendations()
	{
                $di = data_interface::get_instance('www_recomendations');
		$di->push_args(array());
		$di->_flush();
		$di->set_order('order');
		$di->_get();
		$di->pop_args();
		return $di->get_results();
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'www_recomendations.js');
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
