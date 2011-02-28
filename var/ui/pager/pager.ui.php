<?php
/**
*	ПИ "Pager"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_pager extends user_interface
{
	public $title = 'Пейджер';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        public function get_pager($data)
        {
		if (!empty($data['prefix']))
			$data['prefix'] = preg_replace('/[&]?page=\d+/', '', $data['prefix']);
                return $this->parse_tmpl('default.html', $data);
        }
}
?>
