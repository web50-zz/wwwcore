<?php
/**
*	"Схема хранения пресетов  вью поинтов в структуре UI"
*
* @author	9*	
* @package	SBIN Diesel
*/
class ui_structure_presets extends user_interface
{
	public $title = 'Структура пресеты';

	protected $deps = array(
		'main' => array(
			'structure_presets.grid',
			'structure_presets.item_form',
		),
	);
	
	public function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	/**
	*       Основной JS
	*/
	protected function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*      ExtJS - Grid
	*/
	protected function sys_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'grid.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	protected function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
