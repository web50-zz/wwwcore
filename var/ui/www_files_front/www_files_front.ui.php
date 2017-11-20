<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>  09102014
* @package	SBIN Diesel
*/
class ui_www_files_front extends user_interface
{
	public $title = 'www: Файлы фронт';

	protected $deps = array(
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        public function pub_content()
        {
		$template = $this->get_args('tmpl', 'default.html');
		$folder = $this->get_args('folder', '1');
		$limit = $this->get_args('limit',20);
		$enable_pager = $this->get_args('enable_pager',false);
		$page = request::get('page', 1);
		$sort = $this->get_args('sort','id');
		$dir = $this->get_args('dir','DESC');
		$show_title = $this->get_args('show_title',0);
		$this->args = array(
			'sort'=>$sort,
			'dir'=>$dir,
			'start' => ($page - 1) * $limit,
			'_sfm_folders_id'=>$folder,
			'limit'=>$limit,
		);
		if($show_title != 0)
		{
			$this->args['show_title'] = $show_title;
		}
		if($folder > 0)
		{
			$di = data_interface::get_instance('fm_folders');
			$di->_flush();
			$di->set_args(array('_sid'=>$folder));
			$folder_data = $di->_get()->get_results(0);
		}

		$di =  data_interface::get_instance('fm_files');
		$di->_flush();
		$di->set_args($this->args);
		$data = $di->extjs_grid_json(false,false);
		if($folder_data)
		{
			$data['title'] = $folder_data->title;
		}
		if($enable_pager == true)
		{
			$pager = user_interface::get_instance('pager');
			$st=user_interface::get_instance('structure');
			$st->collect_resources($pager,'pager');
			$par = explode('&',$_SERVER['QUERY_STRING']);
			foreach($par as $key=>$value)
			{
				$line = explode('=',$value);
				if($line[0] == 'page')
				{
					unset($par[$key]);
				}
			}
			$query =  implode('&',$par);
			$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
			$data['custom_pager'] = array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $query);
		}
		$data['args'] = $this->args;
                return $this->parse_tmpl($template, $data);
        }
//9* возвращает сорс файла заданного по id и по названиюсвойтсва заданного в странице через json_params
	public function pub_file_by_page_prop()
	{
		$args = $this->get_args();
		$prop_name = $args['file'];
		$default =  $this->get_args('default',1);
		$st = user_interface::get_instance('structure');
		$data =  $st->get_page_info();
		$file_id = $default;
		if($data['param_json'])
		{
			if(array_key_exists($prop_name,$data['params_json']))
			{
				$file_id =  $data['params_json'][$prop_name];
			}
		}
		$di = data_interface::get_instance('fm_files');
		$di->set_args(array('_sid'=>$file_id));
		$di->_flush();
		$res = $di->_get()->get_results(0);
		return '/filestorage/'.$res->real_name;
	}

//9* возвращает сорс файла заданного id  по Ключу в реестре 
	public function pub_file_by_reg_key()
	{
		$args = $this->get_args();
		$prop_name = $args['key'];
		$id = registry::get($prop_name);
		if($id == '')
		{
			return;
		}
		$file_id = $id;
		$di = data_interface::get_instance('fm_files');
		$di->_flush();
		$di->set_args(array('_sid'=>$file_id));
		$res = $di->_get()->get_results(0);
		return '/filestorage/'.$res->real_name;
	}

//9* возвращает сорс файла заданного id 
	public function pub_file_by_id()
	{
		$id = $this->get_args('id');
		if($id == '')
		{
			return;
		}
		$file_id = $id;
		$di = data_interface::get_instance('fm_files');
		$di->_flush();
		$di->set_args(array('_sid'=>$file_id));
		$res = $di->_get()->get_results(0);
		return '/filestorage/'.$res->real_name;
	}


}
?>
