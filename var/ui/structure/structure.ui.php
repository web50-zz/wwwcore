<?php
/**
*	UI The structure of site
*
* @author	Litvinenko S. Anthon <crazyfluger@gmail.com> Fedot B Pozdnyakov <9@u9.ru>
* @access	public
* @package	SBIN DIESEL	
*/
class ui_structure extends user_interface
{
	public $title = 'Структура';
	protected $_res_deps = array();		// Массив ресурсов, которые необходимо загрузить в 0 VP
	protected $key_words = array();
	protected $title_words = array();
	protected $description = array();
	protected $page_itself = '';
	protected $inner_uri_match = false;//9* если есть влоденные uri  то будет true
	protected $exact_uri_match = false;
	protected $uri_check_list = array();//9* список уи и методов которые вызываются и дложны быть учтены при  решении 404
	protected $body_class = array();
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
	*	Подготовительные операции, перед запуском функции process_page()
	*/
	public function prepare_process_page()
	{
		// Prepare variables
		$this->_res_deps = array();
		$this->key_words = array();
		$this->title_words = array();
		$this->description = array();
		$this->css_resources = array();
		$this->js_resources = array();
		$this->body_class = array();
		$this->meta_tags = array();
		return $this;
	}

	public function add_dep($ui, $call)
	{
		$this->_res_deps[] = array('ui' => $ui, 'call' => $call);
	}

	/**
	*	Парсинг контента страницы
	* @access	public
	* @param	array	$page	Массив с описанием страницы
	* @param	boolean	$output	Вывод страницы на экран (true - Да, false - возвращает результат парсинга через return)
	*/
        public function process_page($page, $output = true, $without_prepare = false)
        {
		$di_s =  data_interface::get_instance('structure');
		if(!$page)// 9* если не найдено страницы вообще, то сразу 404 с остальными вариантами разберемся  ниже
		{
			$this->do_404();
		}
		if($di_s->exact_match == true)
		{
			$this->exact_uri_match = true;
		}
		$page = $this->before_process_page($page);
		glob::set('PAGE_URI',$page['uri']);// для нужд рендера и проч
                $data = array(
                        'args' => request::get(),
		);
		//9* 20112012  decode and set some extra params in page config
		if($page['params_json'] != '')
		{
			$temp = json_decode($page['params_json'],true);
			$data['params_json'] = $temp;
			$page['params_json'] = $temp;
			unset($temp);
		}
		$this->page_itself = $page;
		// Get view points
		$divp = data_interface::get_instance('ui_view_point');
		$divp->_flush();
		$divp->set_args(array('_spid' => $page['id']));
		/* 9* 12082013  неясно зачем так было ибо на кроме главной все выводится и скртыие не действует
			if (SRCH_URI != "") $divp->set_args(array('_sdeep_hide' => 0), true);
			ниже новый вариант
		*/
		$divp->set_args(array('_sdeep_hide' => 0), true);

		$divp->set_order('view_point');
		$divp->set_order('order');
		$divp->_get();
		$vps = $divp->get_results();

		if (!$without_prepare)
		{
			// Prepare variables
			$this->_res_deps = array();
			$this->key_words = array();
			$this->title_words = array();
			$this->description = array();
			$this->css_resources = array();
			$this->js_resources = array();
			$this->meta_tags = array();
		}


		//9* мета на страницу итмеет приоритет перед глобальной мета
		if($page['mkeywords'] != '')
		{
			$this->key_words[] = $page['mkeywords'];
		}
		if($page['mdescr'] != '')
		{
			$this->description[] = $page['mdescr'];
		}
		if($page['mtitle'] != '')
		{
			$this->title_words[] = $page['mtitle'];
		}else if($page['title'] != '')
		{
			$this->title_words[] = $page['title'];
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
		else
		{
			$this->theme_path = CURRENT_THEME_PATH;
			$this->theme = '';
		}


		/* 9* 2016-10-14 текущий порядок загрузки ресурсов такой: 
		1 сначала грузим то что указано в файлах депенденсов 
		2 потом грузим ресурсы structure, 
		3 потом грузим остальные  res.js
		*/
		/* 9* итак сначала депенденсы */
		$js_deps_file = BASE_PATH.$this->theme_path.'/js_deps.php';
		if (file_exists($js_deps_file))
		{
			include_once($js_deps_file);
			if(is_array($js_deps))
			{
				foreach ($js_deps as $depk => $depv)
				{
					$path = $this->theme_path . $depv; //9* note that $this->theme_path declared in user_interface prototype class by defults based on  theme init cfg
	//				$data['js_resources'][] = $path;
					$this->js_resources[$path][] = $path;
				}
			}
		}

		//9* end of js deps inclusion

		// Теперь собираем ресурсы для structure ui
		$this->collect_resources($this, $this->interfaceName);//9* это должно быть обязательно после того как определилась теме оверлоад

		//9* css ресурсы после структуры css
		$css_deps_file = BASE_PATH.$this->theme_path.'/css_deps.php';
		if (file_exists($css_deps_file))
		{
			include_once($css_deps_file);
			if(is_array($css_deps))
			{
				foreach ($css_deps as $depk => $depv)
				{
					$path = $this->theme_path . $depv; 
					//$data['js_resources'][] = $path;
					$this->css_resources[$path][] = $path;
				}
			}
		}

		// {START: Подключить необходимые ресурсы в 0 VP, если они определены
		if (!empty($this->_res_deps))
		{
			foreach ($this->_res_deps as $dep)
			{
				$vps[] = (object)array(
					'view_point' => 0,
					'ui_name' => $dep['ui'],
					'ui_call' => $dep['call'],
					'ui_configure' => '',
				);
			}
		}
		// END}

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
				
				if($vp->has_structure == 1 && $this->exact_uri_match == false)
				{
					$this->uri_check_list[$vp->ui_name][$vp->ui_call] = 1;
				}

				// Collect VP resources
				$this->collect_resources($ui, $vp->ui_name); // передвинуто перед экзеком вью поинта чтобы реусрсы вьюпроинта собирались раньше чем ресурсы включенных в шаблон экзеков  UI


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
			}
			catch(exception $e)
			{
				dbg::write('ERROR: '.$e->getmessage() . "\n" . $e->getTraceAsString());
				dbg::write($vp->ui_name);
			}
		}
		if($this->exact_uri_match == false && $this->inner_uri_match == false)
		{
			if(registry::get('strict_url_check') == 'true')//9* 03092013 если в конфигах задана жесткая проверка наличия урл то вот вам 404. Если не задано то как и ранше все виртуальные вложенности  приходят на ближайший парент.
			{
				$this->do_404();
			}
		}
		// Collect Structure resources
		/* 9* 08112012
			Заново восстанавливаем глобальную тему по 
			странице на случай если в каком то уи дергали этот метод и глобалы  
			в синглетоне поменялись 
		*/
		if($page['theme_overload'] != '')
		{
			$this->theme_path = THEMES_PATH.$page['theme_overload'].'/';
			$this->theme = $page['theme_overload'];
		}else{
			$this->theme_path = CURRENT_THEME_PATH;
			$this->theme = '';
		}

//		if($this->title_words) $title_words[] =  $this->title_words;
//		if($this->key_words) $key_words[] =  $this->key_words;

		// Заменяем в шаблоне маркер {__css_hash__} на такой-же {__css_hash__}, для того, чтобы после сбора всех CSS, сгенерировать правильный MD5
		$data['css_hash'] = '{__css_hash__}';
		// Заменяем в шаблоне маркер {__js_hash__} на такой-же {__js_hash__}, для того, чтобы после сбора всех JS, сгенерировать правильный MD5
		$data['js_hash'] = '{__js_hash__}';
		$data['body_class'] = '{__body_class__}';//для вставик в тэг боди классов нудных для каких то UI CSS - такое бывает
		$data['title'] = join(' ', $this->title_words);
		$data['keywords'] = join(',', $this->key_words);
		$data['description'] = join(',', $this->description);
		$data['CURRENT_THEME_PATH'] = "/{$this->theme_path}";
		$data['PAGE_ID'] = $page['id'];
		$data['SRCH_URI'] = SRCH_URI;
		$data['PAGE_TITLE'] = $page['title'];
		$data['CANONICAL'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$data['SHORTLINK'] =  'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$data['meta_tags'] = array();
		foreach($this->meta_tags as $k=>$v)
		{
			$data['meta_tags'][] = array('name'=>$k,'content'=>$v);
		}
		if (authenticate::is_logged())
		{
			$data['IS_LOGGED'] = 'yes';
		}
                $template = (!empty($page['template'])) ? $page['template'] : pub_template;
	//	$out = $this->parse_tmpl("main/{$template}", $data, array('ui' => array($this, 'collect_resources')));//9* 04122013 во первых колбэк тут нахуй не нужен потому что  коллект ресурсес  вызывается  выше во второых чтобы структуре  джс шли приоритетом  это перенесено выше цикла прохода по  vp 
		$out = $this->parse_tmpl("main/{$template}", $data);
		// Окончательный сбор данных по ресурсам
		$css_res = array();
		foreach ($this->css_resources as $a) $css_res = array_merge($css_res, $a);
		$css_full = '/' . join(',/', $css_res);
		//$css_full = '/' . join(',/', $this->css_resources);
		$css_hash = md5($css_full);

		$js_res = array();
		//dbg::show($this->js_resources);
		foreach ($this->js_resources as $a) $js_res = array_merge($js_res, $a);
		$js_full = '/' . join(',/', $js_res);
		//$js_full = '/' . join(',/', $this->js_resources);
		$js_hash = md5($js_full);

		$_SESSION['paths'][$js_hash] = $js_full;
		$_SESSION['paths'][$css_hash] = $css_full;

		$body_class_full =  implode(' ', $this->body_class); // склеиваем то что UI напихали в класс тега body

		// Загоняем в шаблон окончательный набор ресурсов CSS и JS
		$tmpl = new tmpl($out, 'TEXT');
		$out = $tmpl->parse(array('css_hash' => $css_hash, 'js_hash' => $js_hash,'body_class'=>$body_class_full));
		if ($output)
		{
			echo($out);
			die();
			//response::send($out, 'html'); ранее было вот это но респонз косячил в случае  дбг шоу что то наружу да и зачем усложнять
		}else{
			return $out;
		}
        }

	public function collect_resources($ui, $name)
	{
		/*
		// 9* CSS output
		if (empty($this->css_resources[$name]) && $path = $ui->get_resource_path("{$name}.css"))
			$this->css_resources[$name] = $path;

		// 9* JS output
		if(empty($this->js_resources[$name]) && $path = $ui->get_resource_path("{$name}.res.js"))
			$this->js_resources[$name] = $path;
		*/

		$this->add_res_css($ui, $name, $name);
		$this->add_res_js($ui, $name, $name, '.res.js');
	}
	
	/**
	*	Добавить стиль
	*/
	public function add_res_css($ui, $name, $style, $ext = '.css')
	{
		$ui->theme_path = $this->theme_path;
		if (($path = $ui->get_resource_path("{$style}{$ext}")) && !in_array($path, (array)$this->css_resources[$name]))
			$this->css_resources[$name][] = $path;
		return $this;
	}
	
	/**
	*	Добавить JS
	*/
	public function add_res_js($ui, $name, $style, $ext = '.js')
	{
		$ui->theme_path = $this->theme_path;
		if (($path = $ui->get_resource_path("{$style}{$ext}")) && !in_array($path, (array)$this->js_resources[$name]))
			$this->js_resources[$name][] = $path;
		return $this;
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
	public function add_title($text = '')
	{
		if($text != '')
		{
			$this->title_words[] = $text;
			return true;
		}
		return false;
	}
//9* метод для замены внешними модулями в title  слов на вывод в Title страницы 
	public function overload_title($text = '')
	{
		if($text != '')
		{
			$this->title_words = array();
			$this->title_words[] = $text;
			return true;
		}
		return false;
	}

	public function pub_overload_title()
	{
		if($this->args['text'] != '')
		{
			$text = $this->args['text'];
		}
		$this->overload_title($text);
	}

//9* метод для добавления внешними модулями в массив decsription слов на вывод в META description 
	public function add_description($text = '')
	{
		if($text != '')
		{
			$this->description[] = $text;
			return true;
		}
		return false;
	}

	public function overload_description($text = '')
	{
		if($text != '')
		{
			$this->description = array();
			$this->description[] = $text;
			return true;
		}
		return false;
	}

	public function pub_overload_description()
	{
		if($this->args['text'] != '')
		{
			$text = $this->args['text'];
		}
		$this->overload_description($text);
	}

	public function add_body_class($class)
	{
		if($class != '')
		{
			$this->body_class[] = $class;
			return true;
		}
		return false;
	}

	public function pub_add_body_class()
	{
		$class = $this->get_args('class','');
		if($class)
		{
			$this->add_body_class($class);
		}
		return false;
	}

	public function pub_add_meta()
	{
		$name = $this->get_args('name','');
		$content = $this->get_args('content','');
		if($name != '' && $content != '')
		{
			$this->add_meta($name,$content);
		}	
	}

	private function add_meta($name = '',$content = '')
	{
		if($name != '' && $content != '')
		{
			$this->meta_tags[$name] = $content;
		}
	}

//9* метод для получения внешними модулями  полной информации из структуры по странице 
	public function get_page_info()
	{
		return $this->page_itself;
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
		if($this->args['overload'] != '')
		{
			$this->theme_path = THEMES_PATH.$this->args['overload'].'/';//9* Это надо на случай если  задан оерлоад дефолтовой темы на страницу
		}
		$data = $this->get_tdir_files_list();
		if(count($data) == 0)
		{
			if(defined('CURRENT_THEME_PATH'))
			{
				$data = $this->get_tdir_files_list('default');
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

	//9* for overloads in descendants
	public function before_process_page($page)
	{
		return $page;
	}
	//9*  если во вьюпоинте  найден вложенный ури можнов ыставить что  он есть. Это надо для  решения по выдаче 404
	public function have_inner_match($ui_name,$ui_call)
	{
		if($this->uri_check_list[$ui_name][$ui_call] == 1)
		{
			$this->inner_uri_match = true;
		}
	}

	public function do_404()
	{
		$data = array();
		$out = $this->parse_tmpl("404.html", $data);
		header(" ",true,'404');
		response::send($out, 'html');
	}
}
?>
