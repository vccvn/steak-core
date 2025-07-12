<?php

/**
 * source: https://github.com/sunra/php-simple-html-dom-parser/tree/master/Src/Sunra/PhpSimple
 */

namespace Steak\Core\Html\Dom;

require_once 'simplehtmldom_1_9'.DIRECTORY_SEPARATOR.'simple_html_dom.php';

class HtmlDomParser {
	
	/**
	 * @return \simplehtmldom_1_9\simple_html_dom
	 */
	static public function file_get_html() {
		return call_user_func_array ( '\simplehtmldom_1_9\file_get_html' , func_get_args() );
	}

	/**
	 * get html dom from string
	 * @return \simplehtmldom_1_0\simple_html_dom
	 */
	static public function str_get_html() {
		return call_user_func_array ( '\simplehtmldom_1_9\str_get_html' , func_get_args() );
	}
}