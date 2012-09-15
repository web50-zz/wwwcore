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
	protected $key_words =  array();
	protected $title_words =  array();
	protected $description =  array();

	protected $deps = array(
		'main' => array(
			'structure.site_tree',
			'structure.page_view_points',
		)
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*	Парсинг контента страницы
	* @access	public
	* @param	array	$page	Массив с описанием страницы
	* @param	boolean	$output	Вывод страницы на экран (true - Да, false - возвращает результат парсинга через return)
	*/
        public function process_page($page, $output = true)
        {
                $data = array(
                        'args' => request::get(),
		);

		// Get view points
		$divp = data_interface::get_instance('ui_view_point');
		$divp->_flush();
		$divp->set_args(array('_spid' => $page['id']));
		if (SRCH_URI != "") $divp->set_args(array('_sdeep_hide' => 0), true);
		$divp->set_order('view_point');
		$divp->set_order('order');
		$vps = $divp->_get();

		// Prepare variables
		$this->css_resources = array();
		$this->js_resources = array();
		//9* мета на страницу итмеет приоритет перед глобальной мета
		if($page['mkeywords'] != '')
		{
			$this->key_words[] = $page['mkeywords'];
		}
		if($page['mdescr'] != '')
		{
			$this->description[] = $page['mdescr'];
		}
		if($page['title'] != '')
		{
			$this->title_words[] = $page['mtitle'];
		}
		//9* суем глобальное META
		if(SITE_KEYWORDS != '')
		{
			$this->key_words[] = SITE_KEYWORDS;
		}
		if(SITE_TITLE != '')
		{
			$this->title_words[] = SITE_TITLE;
		}
		if(SITE_DESCRIPTION !='')
		{
			$this->description[] = SITE_DESCRIPTION;
		}

		/* 9* theme overload */
		if($page['theme_overload'] != '')
		{
			$this->theme_path = THEMES_PATH.$page['theme_overload'].'/';
			$this->theme = $page['theme_overload'];
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
					$ui->theme = $page['theme_overload'];
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
				/* 9* 15092012  not needed yet
				if($ui->title_words)
					$this->title_words[] =  $ui->title_words;

				if($ui->key_words)
					$this->key_words[] =  $ui->key_words;
				*/
				// Collect VP resources
				$this->collect_resources($ui, $vp->ui_name);
			}
			catch(exception $e)
			{
				dbg::write('error: '.$e->getmessage());
				dbg::write($vp->ui_name);
			}
		}

		// Collect Structure resources
		$this->collect_resources($this, $this->interfaceName);

//		if($this->title_words) $title_words[] =  $this->title_words;
//		if($this->key_words) $key_words[] =  $this->key_words;

		// Заменяем в шаблоне маркер {__css_hash__} на такой-же {__css_hash__}, для того, чтобы после сбора всех CSS, сгенерировать правильный MD5
		$data['css_hash'] = '{__css_hash__}';
		// Заменяем в шаблоне маркер {__js_hash__} на такой-же {__js_hash__}, для того, чтобы после сбора всех JS, сгенерировать правильный MD5
		$data['js_hash'] = '{__js_hash__}';
		$data['title'] = join(' ', $this->title_words);
		$data['keywords'] = join(',', $this->key_words);
		$data['description'] = join(',', $this->description);
		$data['CURRENT_THEME_PATH'] = "/{$this->theme_path}";
		$data['PAGE_ID'] = $page['id'];
	
		if (authenticate::is_logged())
			$data['IS_LOGGED'] = 'yes';

                $template = (!empty($page['template'])) ? $page['template'] : pub_template;
		$out = $this->parse_tmpl("main/{$template}", $data, array('ui' => array($this, 'collect_resources')));

		// Окончательный сбор данных по ресурсам
		$css_full = '/' . join(',/', $this->css_resources);
		$css_hash = md5($css_full);

		$js_full = '/' . join(',/', $this->js_resources);
		$js_hash = md5($js_full);

		$_SESSION['paths'][$js_hash] = $js_full;
		$_SESSION['paths'][$css_hash] = $css_full;

		// Загоняем в шаблон окончательный набор ресурсов CSS и JS
		$tmpl = new tmpl($out, 'TEXT');
		$out = $tmpl->parse(array('css_hash' => $css_hash, 'js_hash' => $js_hash));
		if ($output)
			response::send($out, 'html');
		else
			return $out;
        }

	public function collect_resources($ui, $name)
	{
		// 9* CSS output
		if (empty($this->css_resources[$name]) && $path = $ui->get_resource_path("{$name}.css"))
			$this->css_resources[$name] = $path;

		// 9* JS output
		if(empty($this->js_resources[$name]) && $path = $ui->get_resource_path("{$name}.res.js"))
			$this->js_resources[$name] = $path;
	}
//9* метод для добавления внешними модулями в массив ключевых слов на вывод в META keywords
	public function add_keyword($text)
	{
		if($text != '')
		{
			$this->key_words[] = $text;
			return true;
		}
		return false;
	}
	
//9* метод для добавления внешними модулями в title  слов на вывод в Title страницы 
	public function add_title($text)
	{
		if($text != '')
		{
			$this->title_words[] = $text;
			return true;
		}
		return false;
	}

//9* метод для добавления внешними модулями в массив decsription слов на вывод в META description 
	public function add_description($text)
	{
		if($text != '')
		{
			$this->description[] = $text;
			return true;
		}
		return false;
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
