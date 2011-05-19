<?php
/**
*	UI The structure of site
*
* @author	Litvinenko S. Anthon <crazyfluger@gmail.com>
* @access	public
* @package	FlugerCMS
*/
class ui_structure extends user_interface
{
	public $title = 'Структура';

	protected $deps = array(
		'main' => array(
			'structure.site_tree',
			'structure.page_view_points',
			'structure_presets.main',
		),
		'site_tree' => array(
			'structure.node_form',
		),
		'page_view' => array(
			'structure.page_view_point',
		),
		'page_view_points' => array(
			'structure.page_view_point_form',
		),
		'page_view_point' => array(
			'structure.page_view_point_form',
		)
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function process_page($page)
        {
                $data = array(
                        'args' => request::get(),
		);

		$divp = data_interface::get_instance('ui_view_point');
		$divp->_flush();
		$divp->set_args(array('_spid' => $page['id']));
		if (SRCH_URI != "") $divp->set_args(array('_sdeep_hide' => 0), true);
		$divp->set_order('view_point');
		$divp->set_order('order');
		$vps = $divp->_get();
		$css_resources = array();
		$js_resources = array();
		$key_words = array();
		$title_words = array();
		$key_words[] = SITE_KEYWORDS;
		$title_words[] = SITE_TITLE;
		$description[] = SITE_DESCRIPTION;

		/* 9* theme overload */
		if($page['theme_overload'] != '')
		{
			$this->theme_path = THEMES_PATH.$page['theme_overload'].'/';
		}
		// 9* include and mask some js  depes for current theme.
		$js_deps_file = BASE_PATH.$this->theme_path.'/js_deps.php';
		if(file_exists($js_deps_file))
		{
			include_once($js_deps_file);
			foreach($js_deps as $depk=>$depv)
			{
				$path = $this->theme_path.$depv;//9* note that $this->theme_path declared in user_interface prototype class by defults based on  theme init cfg
				$data['js_resources'][] = $path;
			}
		}
		//9* end of js deps inclusion

		foreach ($vps as $vp)
		{
			try
			{
				$ui = user_interface::get_instance($vp->ui_name);
				$call = !empty($vp->ui_call) ? $vp->ui_call : 'content';
				/* 9* theme overload. If  exclusive theme declared for page we should overwrite theme_path variable for any ui we used on page */
				if($page['theme_overload'] != '')
				{
					$ui->theme_path = THEMES_PATH.$page['theme_overload'].'/';
				}
				/* 9* some cache procs */
				if ($vp->cache_enabled == 1)
				{
					$di = data_interface::get_instance('cache');
					$i = array(
						'ui' => $vp->ui_name,
						'call' => $call,
						'timeout' => $vp->cache_timeout
					);
					$e = json_decode($vp->ui_configure, true);

					if (is_array($e))
						$i = array_merge($i, $e);

					$di->set_args($i);

					if ($di->cached() == true)
					{
						$data["view_point_{$vp->view_point}"][] = $di->get_cached();
					}
					else
					{
						$data["view_point_{$vp->view_point}"][] = $ui->call($call, json_decode($vp->ui_configure, true));
						$di->cache_it($data["view_point_{$vp->view_point}"][count($data["view_point_{$vp->view_point}"])-1]);
					}
				}
				else
				{
					$data["view_point_{$vp->view_point}"][] = $ui->call($call, json_decode($vp->ui_configure, true));
				}
				/* end of cache shit */
				/* 9* title and keywords builder */
				if($ui->title_words)
				{
					$title_words[] =  $ui->title_words;
				}
				if($ui->key_words)
				{
					$key_words[] =  $ui->key_words;
				}
				// 9*  css output
				if(!$css_resource[$vp->ui_name])
				{
					if($path = $ui->get_resource_path($vp->ui_name.'.css'))
						$data['css_resources'][] = $path;

					$css_resource[$vp->ui_name] = true;
				}

				//9* js output
				if(!$js_resource[$vp->ui_name])
				{
					if($path = $ui->get_resource_path($vp->ui_name.'.res.js'))
						$data['js_resources'][] = $path;

					$js_resource[$vp->ui_name] = true;
				}

			}
			catch(exception $e)
			{
				dbg::write('error: '.$e->getmessage());
				dbg::write($vp->ui_name);
			}
		}
		// 9* adding structure css resource to css output
		if($path = $this->get_resource_path($this->interfaceName.'.css'))
			$data['css_resources'][] = $path;

		if($path = $this->get_resource_path($this->interfaceName.'.res.js'))
			$data['js_resources'][] = $path;

		if($this->title_words)
		{
			$title_words[] =  $this->title_words;
		
		}

		if($this->key_words)
		{
			$key_words[] =  $this->key_words;
		}
		$css_full = '/'.join(',/',$data['css_resources']);
		$css_hash =  md5($css_full);
		$data['css_hash'] = $css_hash;

		$js_full = '/'.join(',/',$data['js_resources']);
		$js_hash =  md5($js_full);
		$data['js_hash'] = $js_hash;

		$_SESSION['paths'][$js_hash] = $js_full;
		$_SESSION['paths'][$css_hash] = $css_full;

		$data['title'] = join(',',$title_words);
		$data['keywords'] = join(',',$key_words);
		$data['description'] = join(',',$description);
		$data['CURRENT_THEME_PATH'] = '/'.$this->theme_path;
	
                $template = (!empty($page['template'])) ? $page['template'] : pub_template;
		$html = $this->parse_tmpl('main/'.$template, $data);
		$out =  preg_replace('/\r/','',$html);//9* для пущего ужатия лишнее коцаем
		$out =  preg_replace('/\n/','',$out);
		$out =  preg_replace('/\s+/',' ',$out);
		response::send($out, 'html');
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
	protected function sys_site_tree()
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

	/**
	*	ExtJS Grid - Список view points для страницы
	*/
	protected function sys_page_view_points()
	{
		$tmpl = new tmpl($this->pwd() . 'page_view_points.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*	ExtJS UI Site Tree
	*/
	protected function sys_page_view()
	{
		$tmpl = new tmpl($this->pwd() . 'page_view.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*	ExtJS UI Site Tree
	*/
	protected function sys_page_view_point()
	{
		$tmpl = new tmpl($this->pwd() . 'page_view_point.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*	ExtJS UI Site Tree
	*/
	protected function sys_page_view_point_form()
	{
		$tmpl = new tmpl($this->pwd() . 'page_view_point_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*	List of available templates to assign as 'main template' for page
	*/
	protected function sys_templates()
	{
		$data = $this->get_tdir_files_list();
		if(count($data) == 0)
		{
			dbg::write("WARNING!! NO TEMPLATES available to assign as main at sys_templates()");
			if(defined('CURRENT_THEME_PATH'))
			{
				
				dbg::write("Trying to get template list from default kernel locations");
				$data = $this->get_tdir_files_list('default');
				if(count($data) == 0)
				{
					dbg::write("WARNING!! NO TEMPLATES  available AT ALL(default locations also) to assign as main at sys_templates()");
				}
				else
				{
					dbg::write("Success");
				}
			}
		}
		response::send($data, 'json');
	}
	
	/**
	*  reads global template filenames from possible locations 
	* ( 'default' - force kernel ui path. 'no parms' - current theme path or default kernel if available
	*/
	public function get_tdir_files_list($mode = '')
	{
		$tdir = $this->get_resource_dir_path($mode) . 'main';
		$dh = dir($tdir);
		$data = array();
		while(false !== ($tmpl = $dh->read()))
			if (!is_dir($tmpl))
			{
				if(preg_match("/^.+\.html$/",$tmpl))
				{
					$data[] = array('template' => $tmpl);
				}
			}
		return $data;
	}
}
?>
