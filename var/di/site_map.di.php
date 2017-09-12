<?php
/**
*	Интерфейс данных "Карта сайта"
*
* @author	9* 9@u9.ru 03102011
* @package	SBIN Diesel
*/
class di_site_map extends data_interface
{
	public $title = 'Site map';
	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	protected $db = 'db1';

	protected $data = false;//тут храним данные для кэширования если дераем несколько раз например 
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = 'structure';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
			'id' => array('type' => 'integer', 'serial' => 1, 'readonly' => 1),
			'hidden' => array('type' => 'boolean'),
			'title' => array('type' => 'string'),
			'name' => array('type' => 'string'),
			'uri' => array('type' => 'string'),
			'redirect' => array('type' => 'string'),
			'module' => array('type' => 'string'),
			'params' => array('type' => 'string'),
			'template' => array('type' => 'string'),
			'theme_overload' => array('type' => 'string'),
			'private' => array('type' => 'boolean'),
			'auth_module' => array('type' => 'string'),
			'left' => array('type' => 'integer', 'protected' => 1),
			'right' => array('type' => 'integer', 'protected' => 1),
			'level' => array('type' => 'integer', 'readonly' => 1),
			'mtitle' => array('type' => 'string'),
			'mkeywords' => array('type' => 'string'),
			'mdescr' => array('type' => 'string')
		);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	public function get_all()
	{
		$parent = 1;
		$hidden = 0;
		$this->_flush();
		if($this->args['hidden'] == 1){
			$hidden = 1;
		}
		if($this->args['parent']>0){
			$parent = $this->args['parent'];
		}
		if($hidden == 0)
		{
			$this->where.= 'hidden = 0';
		}
		$this->set_args(array(
				'sort'=>'left',
				'dir'=>'ASC',
				));
		if($this->data == false)
		{
			$this->data =  $this->extjs_grid_json(false,false);
		}
		$level = 1;
		$this->get_childs(0);
		if($parent != 1){
			$this->search_parent($this->data['records'],$parent);
			return $this->result;
		}
		return $this->data['records'][0];
	}

	public function get_childs($index)
	{
		$this->cnt++;
		$this->data['records'][$index]['childs']= array();
		foreach($this->data['records'] as $key=>$value){
			if($value['level'] == $this->data['records'][$index]['level']+1)
			{
				if($value['left']>$this->data['records'][$index]['left'] && $value['right']<$this->data['records'][$index]['right'])
				{
						$this->get_childs($key);
						array_push($this->data['records'][$index]['childs'],$this->data['records'][$key]);
				}
			}
		}
	}

	public function search_parent($array_in,$parent)
	{
		foreach($array_in as $key=>$value)
		{
			if($value['id'] == $parent)
			{
				$this->result = $value;
				return;
			}
			else{
				$this->search_parent($value['childs'],$parent);
			}
		}
	}
}
?>
