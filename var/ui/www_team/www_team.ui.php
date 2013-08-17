<?php
/**
*
* @author   9* 9@u9.ru  
* cloned  news ui 02o52013
* SBIN Diesel
*/
class ui_www_team extends user_interface
{
	public $title = 'www команда';

	protected $deps = array(
		'main' => array(
			'www_team.item_form',
		)
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}
 	/**
	*	Вывод списка 
	*/
	public function pub_list()
	{
		$data = array();
		$di = data_interface::get_instance('www_team');
		$di->_flush();
		$data = $di->extjs_grid_json(false,false);
		$data['path'] = $di->get_url();
		foreach($data['records'] as $key=>$value){
//			$data['records'][$key]['title'] = preg_replace('/\s+/','<br>',trim($value['title']));
//			$data['records'][$key]['title_eng'] = preg_replace('/\s+/','<br>',trim($value['title_eng']));
		}
		return $this->parse_tmpl('list.html',$data);
	}
	public function pub_vcard(){
		$parts = explode("/",SRCH_URI);
		$di= data_interface::get_instance('www_team');
		$id = $di->search_by_uri($parts[0]);
		$di->set_args(array('_sid'=>$id));
		$di->_flush();
		$data = $di->extjs_form_json(false,false);
		$data['data']['lang'] = $this->args['lang'];
		$res = $this->parse_tmpl('vcard.html',$data['data']);
		if(!$this->args['lang'])
		{
			$out = iconv('utf8','cp1251',$res);
			$res =  $out;
		}
		header('Content-Type: text/x-vcard');  
		header('Content-Disposition: inline; filename= "vcard.vcf"');  
		header('Content-Length: '.strlen($res));  
		echo($res);
		die();
	}
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
