<?php
/**
*
* @author	9@u9.ru 16012014	
* @package	SBIN Diesel
*/
class di_index_processor extends data_interface
{
	public $title = 'INDEX_PROCESSOR_PROTOYPE';

	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	public $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	public $db = 'db1';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	public $name = '';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
	);

	public $settings = array();
	/* config example
	public $settings = array(
		'index_target'=>array(
			'di_name'=>'m2_item',//9* di которыйиндексируем
			'fields_to_index'=>array(
					'id'=>array(
						'alias'=>'item_id'
					),
					'article'=>'',
					'title'=>'',
					'name'=>'',
					'not_available'=>'',
				)

			),
			'composite_fields'=>array(
					array(
					'type'=>'records_by_key',
					'index_field_name'=>'files_list',
					'di_name'=>'m2_item_files',
					'di_key'=>'item_id',
					'order_field'=>'field_name',
					'order_type'=>'ASC',
					'fields'=>array(
						'real_name'=>'',
						'file_type'=>'',
						'type'=>''
						)
					),
					array(
					'type'=>'records_by_key',
					'index_field_name'=>'category_list',
					'di_name'=>'m2_item_category',
					'di_key'=>'item_id',
					'fields'=>array(
						'category_id'=>'',
						),
					'joins'=>array(
						'm2_category'=>array(
								'source_key'=>'category_id',
								'remote_key'=>'id',
								'fields'=>array(
									'title'=>'title'
								)
							)
						)
					),
			)	

	*/

	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}


	public function update_record($id)
	{
		// Собираем основные данные
		$di = data_interface::get_instance($this->settings['index_target']['di_name']);
		$di->_flush(true);
		$what = array();

		foreach($this->settings['index_target']['fields_to_index'] as $key=>$value)
		{
				$what[] = $key;
		}

		foreach($this->settings['composite_fields'] as $key=>$value)
		{
			if($value['type'] == 'records_by_key')
			{
				$composites[$value['index_field_name']] = $this->get_by_key($id,$value);
			}

		}
		$di->what = $what;
		$di->push_args(array('_sid' => $id));
		$di->_get();
		$data = (array)$di->get_results(0);
		$di->pop_args();
		foreach($this->settings['index_target']['fields_to_index'] as $key=>$value)
		{
				if($value == '')
				{
					$args[$key] = $data[$key];
				}
				if(is_array($value))
				{
					if($value['alias'] != '')
					{
						$args[$value['alias']] = $data[$key];
					}
				}					
		}
		foreach($composites as $key=>$value)
		{
			$args[$key] = $value;
		}

		$args['last_changed'] = date('Y-m-d H:i:s');
		$args['changer_uid'] = UID;
		$this->_flush();
		$this->push_args($args);
		$this->set_args(array('_sitem_id' => $id), true);
		$this->insert_on_empty = true;
		$this->_set();
		$this->pop_args();

	}

	public function get_by_key($id,$input)
	{
		$what = array();
		foreach($input['fields'] as $key2=>$value2)
		{
			$what[] = $key2;
		}
		$di = data_interface::get_instance($input['di_name']);
		//$di->_flush(true); 9* 05032015 склейки джойнов были массовые
		$di->_flush();
		// обработаем ка джойны
		$joins_di = array(); //joins $di storage 
		$i = 0;
		if(array_key_exists('joins',$input))
		{
			foreach($input['joins'] as $key3=>$value3)
			{
				$flds = array();
				foreach($value3['fields'] as $key4=>$value4)
				{
					$flds[$key4]=$value4;
				}
				$joins_di[$i] = $di->join_with_di($key3,array($value3['source_key']=>$value3['remote_key']),$flds);
				foreach($value3['fields'] as $key4=>$value4)
				{
					$what[] = array('di'=>$joins_di[$i],'name'=>$key4);
				}
				$i++;
			}
		}
		// теперь получим данные
		$di->what = $what;
		$di->push_args(array('_s'.$input['di_key'] => $id));
		$di->connector->fetchMethod = PDO::FETCH_ASSOC;
		if($input['order_field'] != '')
		{
			if($input['order_type'] != '')
			{
				$tp = $input['order_type'];
			}
			else
			{
				$tp = 'ASC';
			}

			$di->set_order($input['order_field'],$tp);
		}
		$di->_get();
		$data = (array)$di->get_results();
		$di->pop_args();
	//	$ret = json_encode($data);
		$ret = $this->json_enc($data);
		return $ret;
	}

	public function index_target_set($eObj, $ids, $args)
	{
		if (!is_array($ids) && $ids > 0)
		{
			$this->update_record($ids);
		}
		else if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$this->update_record($id);
			}
		}
		else
		{
			// Some error, because unknown const_mat ID
		}
	}

	public function index_target_unset($eObj, $ids, $args)
	{
		if (!empty($args['_sid']))
		{
			$this->_flush();
			$this->push_args(array(
				'_sitem_id' => $args['_sid'],
			));
			$this->_unset();
			$this->pop_args();
		}
	}


	public function update_field($id,$value)
	{
		if(!($id>0))
		{
			return;
		}
		$args[$value['index_field_name']] = $this->get_by_key($id,$value);
		$args['_sitem_id'] = $id;
		$args['last_changed'] = date('Y-m-d H:i:s');
		$this->_flush();
		$this->push_args($args);
		$this->insert_on_empty = false;
		$this->_set();
		$this->pop_args();
	}

	public function index_field_set($eObj, $ids, $args)
	{
		$di_name =  $eObj->get_name();
		foreach($this->settings['composite_fields'] as $key=>$value)
		{
			if($value['di_name'] == $di_name)
			{
				$id = (int)$args[$value['di_key']];
				$this->update_field($id,$value);
			}
		}
	}

	public function index_field_unset($eObj, $ids, $args)
	{
		$this->update_field($this->removeable_id,$this->field_to_update);
	}

	public function index_field_prepare_unset($eObj, $ids, $args = array())
	{
		$di_name =  $eObj->get_name();
		foreach($this->settings['composite_fields'] as $key=>$value)
		{
			if($value['di_name'] == $di_name)
			{
				$id = (int)$args[$value['di_key']];
				$di = clone $eObj;
				$di->_flush();
				$di->set_args(array('_sid' => $ids));
				$di->what = array($value['di_key']);
				$di->_get();
				$this->removeable_id = (int)$di->get_results(0, $value['di_key']);
				$this->field_to_update = $value;
			}
		}
	}

	//9*  custom cyrillic fix. for json_encode
	public function json_enc($arr)
	{
		return str_replace('"','\"',json_encode($arr, JSON_UNESCAPED_UNICODE));// начиная с php 5.4   в более ранних версий этой опции нету JSON_UNESCAPED_UNICODE
		$result = preg_replace_callback(
			'/\\\u([0-9a-fA-F]{4})/', 
			create_function('$_m', 'return mb_convert_encoding("&#" . intval($_m[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
                        str_replace('\n','',str_replace('\t','',str_replace('\r','',str_replace('"','\"',json_encode($arr)))))
		);
		/* 9* старый вариант не учитыввал замены переходов строк  и табов  на \n\t
		$result = preg_replace_callback(
			'/\\\u([0-9a-fA-F]{4})/', 
			create_function('$_m', 'return mb_convert_encoding("&#" . intval($_m[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
			json_encode($arr)
		);
		*/
		return $result;
	}

	public function _listeners()
	{
		if(count($this->settings) == 0)
		{
			return array();
		}
		$listeners =  array(
			array('di' => $this->settings['index_target']['di_name'], 'event' => 'onSet', 'handler' => 'index_target_set'),
			array('di' => $this->settings['index_target']['di_name'], 'event' => 'onUnset', 'handler' => 'index_target_unset'),
		);
		foreach($this->settings['composite_fields'] as $key=>$value)
		{
			if($value['type'] == 'records_by_key')
			{
				$listeners[] = array('di' => $value['di_name'], 'event' => 'onSet', 'handler' => 'index_field_set');
				$listeners[] = array('di' => $value['di_name'], 'event' => 'onUnset', 'handler' => 'index_field_unset');
				$listeners[] = array('di' => $value['di_name'], 'event' => 'onBeforeUnset', 'handler' => 'index_field_prepare_unset');
			}

		}
		return $listeners;
	}

}
?>
