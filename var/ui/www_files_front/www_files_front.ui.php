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
		$this->args = array(
			'sort'=>'id',
			'dir'=>'DESC',
			'start' => ($page - 1) * $limit,
			'_sfm_folders_id'=>$folder,
			'limit'=>$limit,
		);

		$di =  data_interface::get_instance('fm_files');
		$di->_flush();
		$di->set_args($this->args);
		$data = $di->extjs_grid_json(false,false);
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
                return $this->parse_tmpl($template, $data);
        }
}
?>
