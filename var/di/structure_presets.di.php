<?php
/**
*	Интерфейс данных "Струткура пресеты  вью поинтов"
*
* @author	9*	
* @package	SBIN Diesel
*/
class di_structure_presets extends data_interface
{
	public $title = 'Site Structure: пресеты вьюпоинтов';

	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	protected $db = 'db1';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = 'structure_presets';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'created_datetime' => array('type' => 'datetime'),
		'creator_uid' => array('type' => 'integer'),
		'changed_datetime' => array('type' => 'datetime'),
		'changer_uid' => array('type' => 'integer'),
		'deleted_datetime' => array('type' => 'datetime'),
		'deleter_uid' => array('type' => 'integer'),
		'title'=>array('type'=>'string'), 
		'preset_data'=>array('type'=>'string'), 
		);

	public function __construct()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}
	
	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush(true);
		if (!empty($this->args['query']) && !empty($this->args['field']))
		{
			$this->args["_s{$this->args['field']}"] = "%{$this->args['query']}%";
		}
		$cr = $this->join_with_di('user', array('creator_uid' => 'id'), array('name' => 'str_creator_name'));
		$flds = array(
			'id',
			'created_datetime',
			'title',
			array('di' => $cr, 'name' => 'name')
		);
		$this->extjs_grid_json($flds);
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$data = $this->extjs_form_json(false,false);
		if($data['data']['id']>0)
		{
			response::send($data,'json');
		}
		response::send(array('success'=>'true'),'json');
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	protected function sys_item()
	{
		$this->_flush();
		$this->extjs_form_json();
	}
	
	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_set()
	{
		$this->_flush();
		$this->insert_on_empty = true;
		if ($this->args['_sid']>0)
		{
			$this->set_args(array('changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('changer_uid' => UID), true);
		}
		else
		{
			$this->set_args(array('created_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('changer_uid' => UID), true);
			$this->set_args(array('creator_uid' => UID), true);
		}
		if($this->args['pid']>0)
		{
			$preset_data = $this->get_vp_data($this->args['pid']);
			$this->set_args(array('preset_data'=>$preset_data),true);
		}
		$this->extjs_set_json();
	}
	
	protected function get_vp_data($page)
	{
		$vp = data_interface::get_instance('ui_view_point');
		$vp->set_args(array('_spage_id'=>$page));
		$data = $vp->get_list_data();
		if($data['success'] == true && count($data['records'])>0)
		{
			foreach($data['records'] as $key=>$value)
			{
				$data['records'][$key]['id'] = '';
				$data['records'][$key]['page_id'] = '';
			}
			return  serialize($data['records']);
		}
		return array();
	}

	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_mset()
	{
		$records = (array)json_decode($this->get_args('records'), true);

		foreach ($records as $record)
		{
			$record['_sid'] = $record['id'];
			unset($record['id']);
			$this->_flush();
			$this->push_args($record);
			$this->insert_on_empty = true;
			$data = $this->extjs_set_json(false);
			$this->pop_args();
		}

		response::send(array('success' => true), 'json');
	}
	
	/**
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$this->extjs_unset_json();
	}
	
	protected function sys_load()
	{
		$this->_flush();
		$this->set_args(array('_sid'=>$this->args['id']),true);
		$data = $this->extjs_form_json(false,false);
		$preset_data = unserialize($data['data']['preset_data']);
		if(count($preset_data)>0)
		{
			$vp = data_interface::get_instance('ui_view_point');	
			if($this->args['type'] == 'loadclean')
			{
				$vp->drop_by_page_id($this->args['pid']);
			}
			foreach($preset_data as $key=>$value)
			{
				$value['page_id'] = $this->args['pid'];
				unset($value['id']);
				$vp->set_args($value);
				$vp->_flush();
				$vp->insert_on_empty = true;
				$vp->extjs_set_json(false,false);
			}
		}
		response::send(array('success' => true), 'json');
	}
}
?>
