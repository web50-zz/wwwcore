<?php
/**
*	Интерфейс данных "Связь страниц сайта и контента"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_structure_content extends data_interface
{
	public $title = 'Связь страниц сайта и контента';
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
	protected $name = 'structure_content';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'pid' => array('type' => 'integer'),
		'cid' => array('type' => 'integer'),
		'ui_name' => array('type' => 'string'),
	);
	
	public function __construct () {
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	/**
	*	Сохранить ссылку страница - контент
	* @access public
	*/
	public function save_link($pid, $cid, $ui_name){
		$this->_flush();
		$this->insert_on_empty = true;
		$this->set_args(array(
			'pid' => $pid,
			'cid' => $cid,
			'ui_name' => $ui_name
		));
		return $this->extjs_set_json(false);
	}

	/**
	*	Удалить ссылку страница - контент
	* @access public
	*/
	public function remove_link($pid, $cid, $ui_name){
		if (is_array($cid))
		{
			foreach ($cid as $id)
			{
				$this->_flush();
				$this->set_args(array(
					'_spid' => $pid,
					'_scid' => $id,
					'_sui_name' => $ui_name
				));
				$this->extjs_unset_json(false);
			}
		}
		else
		{
			$this->_flush();
			$this->set_args(array(
				'_spid' => $pid,
				'_scid' => $cid,
				'_sui_name' => $ui_name
			));
			$this->extjs_unset_json(false);
		}
	}

	/**
	*	удалить ссылки страница - контент
	* @access public
	*/
	public function remove_by_page($pid){
		$this->_flush();
		$this->set_args(array(
			'_spid' => $pid
		));
		return $this->extjs_unset_json(false);
	}

	/**
	*	удалить ссылки страница - контент
	* @access public
	*/
	public function remove_by_content($cid, $ui_name){
		$this->_flush();
		$this->set_args(array(
			'_scid' => $cid,
			'_sui_name' => $ui_name
		));
		return $this->extjs_unset_json(false);
	}
}
?>
