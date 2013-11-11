<?php
// NOTE: Start session
session_start();

define('AUTH_MODE', 'public');
define('AUTH_DI', 'user');
// NOTE: The prefix of UI methods
define('UI_CALL_PREFIX', 'pub_');

try
{
	$args = request::get(array('user', 'secret'));

	if (!empty($args))
		authenticate::login();

	if (authenticate::is_logged() && request::get('logout') == 'yes')
		authenticate::logout();

	//$uri = (empty($_SERVER['REDIRECT_URL'])) ? '/' : $_SERVER['REDIRECT_URL'];
	//$uri = "/" . request::get('_uri', '');
	$uri = URI;
        $diStrc = data_interface::get_instance(SITE_DI);
	$uiSt = user_interface::get_instance(SITE_UI);

	// 9* вот тут надо проверить чтобы в ури не было хуйни, которую пихают разные  мракобесы в поисках дыр
	$symbols = array('"', "'");
	foreach ($symbols as $needle)
	{
		if (strpos($uri, $needle) !== false)
		{
			$uiSt->do_404();
		}
	}

        $page = $diStrc->get_page_by_uri($uri);
	define(PAGE_URI, $page['uri']);
	define(PAGE_NAME, $page['name']);
	define(SRCH_URI, str_replace($page['uri'], "", $uri));
	define(PAGE_ID, $page['id']);
	
	if (!empty($page['redirect']))
	{
		response::redirect($page['redirect']);
	}
	else
	{
                $uiStrc = user_interface::get_instance(SITE_UI);
                $uiStrc->set_args(request::get());
                $uiStrc->process_page($page);
	}
}
catch(Exception $e)
{
	dbg::write($e->getMessage(), LOG_PATH . 'www.log');
}
?>
