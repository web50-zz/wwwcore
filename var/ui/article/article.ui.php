<?php
/**
*	ПИ "Статьи"
*
* @author	Litvinenko S. Anthon <crazyfluger@gmail.com>
* @version	1.0
* @access	public
* @package	CFsCMS2(PE)
* @since	2008-12-13
*/
class ui_article extends user_interface
{
	public $title = 'Статьи';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        public function pub_content()
        {
		$tmpl = new tmpl($this->pwd() . 'content.html');
                $di = data_interface::get_instance('article');
		$di->set_args($this->args);
                return $tmpl->parse($di->get());
        }
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'article.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
