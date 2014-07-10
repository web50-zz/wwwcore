<?php
/**
*	UI The structure of site
*
* @author	9* <9@u9.ru>
* @access	public
* @package	SBIN Diesel
*/
class ui_navigation extends user_interface
{
	public $title = 'Навигация по сайту';
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*	Общий шаблон, для отрисовки любого типа меню, любой сложности
	*/
	protected function pub_menu()
	{
		// Шаблон
		$template = $this->get_args('template', 'menu.html');

		// Родитель (по умолчанию родителем является корневая нода - Home)
		$parent = $this->get_args('parent', 1);

		// Глубина вложенности (если передаётся NULL, то до бесконечности)
		$deep = $this->get_args('deep', null);

		return $this->parse_tmpl($template, data_interface::get_instance('structure')->get_menu($parent, $deep));
	}

	/**
	*	main menu  tracks  'template' variable to set more templates then default
	*/
	protected function pub_top_menu()
	{
		//9* если задан шаблон то будем брать заданный
		$template = $this->get_args('template', 'main_menu.html');
		//9* если в аргументах задан парент то берем срез чайлдов заданного
		$parent = $this->get_args('parent', FALSE);
		//9* если задано берем вплоть до заданногоуровня  ниже верхнего уровня нод по дефолту берем первый левел
		$level_down = (int)$this->get_args('level_down', 1);
		
		$st = data_interface::get_instance('structure');
		$data['records'] = $st->get_main_menu($parent, $level_down);
		$data['page_id'] = PAGE_ID;
		$data['srch_uri'] = SRCH_URI;
		$data['page_uri'] = PAGE_URI;
		$st =  user_interface::get_instance('structure');
		$data['current'] = $st->get_page_info();
		return $this->parse_tmpl($template, $data);
	}
	
	/**
	*	Sub menu level down nodes for current node
	*/
	protected function pub_sub_menu()
	{
		//9* принудительно задать парента для выбранного вью поинта
		$page = (int)$this->get_args('page', 0);
		$st = data_interface::get_instance('structure');
		return $this->parse_tmpl('sub_menu.html', $st->get_sub_menu($page));
	}
	
	/**
	*	Menu "Thermometer"
	*/
	protected function pub_trunc_menu()
	{
		$st = data_interface::get_instance('structure');
		$data = $st->get_trunc_menu();
		$data = array_merge($data, $this->get_sub_structure($data[count($data) - 1]));
		$this->title_words = '';
		$this->key_words = '';
		$this->description = '';
		$this->title_words = $data[count($data)-1]['title'];
		$this->key_words = $data[count($data)-1]['title'];
		$this->description = $data[count($data)-1]['title'];
		return $this->parse_tmpl('trunc_menu.html', $data);
	}

	/*9* берем первый левел и чайлдов для каждого из топов *. Итогом Будут столбцы топ и его чайлды */	
	protected function pub_top_and_1_down(){
		$parent = (int)$this->get_args('parent',1);
		$level_down =(int)$this->get_args('level_down',1);
		$st = data_interface::get_instance('structure');
		$data['records'] = $st->get_main_menu($parent,$level_down);
		foreach($data['records'] as $key=>$value)
		{
			$tmp = $st->get_main_menu($value['id'],2);
			$data['records'][$key]['childs'] = $tmp;
		}
		foreach($data['records'] as $key=>$value){
			foreach($value['childs'] as $key2=>$value2){
				if($value2['id'] == PAGE_ID){
					$data['records'][$key]['top_parent'] = 'yes';
					$data['records'][$key]['childs'][$key2]['active'] = 'yes';
				}
			}
		}
		$data['page_id'] = PAGE_ID;
		$data['srch_uri'] = SRCH_URI;
		$data['page_uri'] = PAGE_URI;
		$st =  user_interface::get_instance('structure');
		$data['current'] = $st->get_page_info();
		return $this->parse_tmpl('top_and_1_down.html',$data);
	}
	/**
	*
	*/
	private function get_sub_structure($page)
	{
		$vps = data_interface::get_instance('ui_view_point')
			->_flush()
			->set_args(array('_spid' => $page['id'], '_shas_structure' => 1))
			->set_order('view_point')
			->set_order('order')
			->_get()
			->get_results();

		foreach ($vps as $vp)
		{
			$ui = user_interface::get_instance($vp->ui_name);
			return $ui->call('trunc_menu', array('data_only' => 1));
		}

		return array();
	}
	
	/**
	*	Sub menu level down nodes for current node
	*/
	protected function pub_brothers_menu()
	{
		//9* принудительно задать парента для выбранного вью поинта
		$st = data_interface::get_instance('structure');
		$data = $st->get_trunc_menu();
		$level = $this->get_args('level',0);
		$level_down =(int)$this->get_args('level_down',1);
		if($level == 0)
		{
			$level = $data[count($data) - 2]['level'];
		}
		foreach($data as $key=>$value)
		{
			if($value['level'] == $level)
			{
				$parent = $value;
			}
		}
		$st =  data_interface::get_instance('structure');
		$res['records'] = $st->get_main_menu($parent['id'],$level_down);
		$res['parent'] = $parent;
		$std =  user_interface::get_instance('structure');
		$res['current'] = $std->get_page_info();
		return $this->parse_tmpl('brothers_menu.html', $res);
	}

}
?>
