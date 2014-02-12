<?php
/**
*	ПИ "WWW: Персоны"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru> 25012013
* @package	SBIN Diesel
*/
class ui_www_client extends user_interface
{
	public $title = 'WWW: Клиенты';

	protected $deps = array(
		'main' => array(
			'www_client.grid',
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
		$data['clients'] =  $this->get_clients();
		$data['path']  = data_interface::get_instance('www_client')->path_to_storage;
                return $this->parse_tmpl('content.html', $data);
        }

	/**
	*	Получить всех клиентов плюс если в  DI www_recomendations есть рекомендательное письмо по клиенту то  и его id
	*/
	private function get_clients()
	{
                $di = data_interface::get_instance('www_client');
		$di->_flush();
		$di->push_args(array());
		$di->set_order('order');
		$di2 = $di->join_with_di('www_recomendations',array('id'=>'client_id'),array('id'=>'recom_id'));
		$what = array(
			'*',
			array('di'=>$di2,'name'=>'id')
		);
		$res = $di->extjs_grid_json($what,false);
		$di->pop_args();
		return $res['records'];
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'www_client.js');
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
