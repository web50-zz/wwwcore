<?php
/**
*	ПИ "Индексатор поселещний страниц"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_visitor_indexer extends user_interface
{
	public $title = 'Индексатор поселещний страниц';

	protected $deps = array(
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_indexer()
	{
		data_interface::get_instance('www_visitor_indexer')->register_visitor();
		return '';
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
