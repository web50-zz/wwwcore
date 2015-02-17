<?php
/**
*	ПИ "WWW: Персоны"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru> 25012013
* @package	SBIN Diesel
*/
class ui_www_client extends user_interface
{
	public $title = 'www: Клиенты';

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
		$template = $this->get_args('template', 'content.html');
		$data = array();
		$data = array_merge($data, $this->args);
		$data['clients'] =  $this->get_clients();
		$data['path']  = data_interface::get_instance('www_client')->path_to_storage;
                return $this->parse_tmpl($template, $data);
        }

	public function pub_map()
	{
		$template = $this->get_args('template', 'map.html');
		$di = data_interface::get_instance('www_client');
		$di->_flush();
		$di->set_group('location_id');
		$di->where = 'location_id>0' ;
		$res = $di->extjs_grid_json(array('location_id'),false);
		foreach($res['records'] as $key=>$value)
		{
			$ids[] = $value['location_id'];
		}
		$di = data_interface::get_instance('locations');
		$di->_flush();
		$di->set_args(array('_sid'=>$ids));
		$data = $di->extjs_grid_json(false,false);
                return $this->parse_tmpl($template, $data);
	}

	public function pub_result()
	{
		$part = $this->get_args('part',false);
		$location_id = request::get('id');
		$di = data_interface::get_instance('www_client');
		$di->_flush();
		$di->set_args(array(
				'_slocation_id'=>$location_id,
		));
		$data = $di->extjs_grid_json(array('*'),false);

		$di = data_interface::get_instance('locations');
		$di->_flush();
		$di->set_args(array('_sid'=>$location_id));
		$res = $di->extjs_form_json(false,false);
		$data['city'] = $res['data']['title'];
                $html =  $this->parse_tmpl('search_result.html', $data);
		if($part == true)
		{
			response::send(array('success'=>true,'html'=>$html),'json');
		}
		else
		{
			return $html; 
		}
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
		$di2 = $di->join_with_di('www_recomendations',array('id'=>'client_id'),array('id'=>'recom_id','real_name'=>'recom_real_name','description'=>'recom_description'));
		$what = array(
			'*',
			array('di'=>$di2,'name'=>'id'),
			array('di'=>$di2,'name'=>'real_name'),
			array('di'=>$di2,'name'=>'description'),
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
