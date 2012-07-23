<?php
/**
*	UI Artilce : aticle_type  
* 
* 
*   
*
*
* DIESEL UI:
* @author	9* Fedot B Pozdnyakov <9@u9.ru> 22072012
* @access	public
* @package	SBIN Diesel
*/
class ui_article_type extends user_interface
{
	public $title = 'Статьи: Типы статей';
	protected $deps = array(
		'main' => array(
			'article_type.tree',
		),
		'tree' => array(
			'article_type.node_form',
		)
	);

	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*       ExtJS UI for adm part
	*/
	protected function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'structure.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*	ExtJS UI Site Tree
	*/
	protected function sys_tree()
	{
		$tmpl = new tmpl($this->pwd() . 'site_tree.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*	ExtJS UI Site Tree
	*/
	protected function sys_node_form()
	{
		$tmpl = new tmpl($this->pwd() . 'node_form.js');
		response::send($tmpl->parse($this), 'js');
	}

}
?>
