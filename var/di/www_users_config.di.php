<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>
* @package	SBIN Diesel
*/
class di_www_users_config extends data_interface
{
	public $title = 'www: Users config';

	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'session';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = 'www_users_config';
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	/**
	*	Список записей
	*/
	public function _list()
	{
		return (array)session::get(null, null, $this->name);
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	public function _get($id, $key = null, $default = null)
	{
		if (($record = session::get($id, FALSE, $this->name)) !== FALSE)
		{
			if ($key)
			{
				if (isset($record[$key]))
					return $record[$key];
				else
					$default;
			}
			else
			{
				return $record;
			}
		}
		return $default;
	}
	
	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	public function _set($id, $record)
	{
		session::set($id, $record, $this->name);
	}
	
	/**
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	public function _unset($id)
	{
		return session::del($id, $this->name);
	}
}
?>
