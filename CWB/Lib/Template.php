<?php

namespace CWB\Lib;

use CWB\Config\App;
use CWB\Lib\Helper as Helper;

/**
* Class View
*
* @package CWB
**/
final class Template {
	protected $_variables = array(),
			  $_controller,
			  $_action,
			  $_bodyContent;

	public $viewPath,
		   $section = array(),
		   $layout;
	
	public $directViewPath;
	
	/**
	* dir where view presents
	* @var string $dirView
	**/
	protected $dirView;
	
	/**
	* default template name;
	* @var string
	**/
	protected $template = 'Classic';
	
	public function __construct($controller, $action, $config = array()){
		$this->template = isset($config['template']) ? $config['template'] : 'Classic';
		
		$this->_controller = $controller;
		$this->_action = $action;
		
		$this->dirView = App::getAppDir() . 'Views' . DS . $this->template . DS;
		//return $this;
	}
	
	/** Set Variables **/
	public function set($name, $value){
		$this->_variables[$name] = $value;
	}
	
	/** RenderBody **/
	public function renderBody(){
		// if we have content the deliver it
		if(!empty($this->_bodyContent)){
			echo $this->_bodyContent;
		}
	}
	
	/** RenderSection **/
	public function renderSection($section){
		if(!empty($this->section) && array_key_exists($section, $this->section)){
			echo $this->section[$section];
		}
	}
	
	/** Display View **/
	public function render(){
		// extract the variables for view pages
		extract($this->_variables);
		
		// the view path
		//$path = Helper::UrlContent("~/{$this->dirView}/");
		$path = $this->dirView;
		
		// start buffering
		ob_start();
		
		// render the page content
		if(empty($this->viewPath)){
			
			$display = $path . strtolower(str_replace(array('/', '\\'), DS, substr(substr($this->_controller, 0, -10), 28))) . DS . strtolower($this->_action) . '.php';
			if(file_exists($display)){
				include($display);
			}else{
				echo "Please create a view for the file: " . $display;
				\CWB\Core\Error::getErrorPage('404.php');
				exit();
			}
		}else if($this->directViewPath){
			include($this->viewPath);
		}else{
			include($path . $this->viewPath . '.php');
		}
		
		// get the body contents
		$this->_bodyContent = ob_get_contents();
		
		// clean the buffer
		ob_end_clean();
		
		// check if we have any layout defined
		if(!empty($this->layout) && (!Helper::isAjax())){
			// we need to check the path contains app prefix (~)
			$this->layout = Helper::UrlContent($this->layout);
			
			// start buffer (minify pages)
			ob_start('CWB\Lib\Helper::minify_content');
			
			// include the template
			include($this->layout);
		}else{
			ob_start('CWB\Lib\Helper::minify_content_js');
			
			// just output the content
			echo $this->_bodyContent;
		}
		
		// end buffer
		ob_end_flush();
	}
	
	/** return the rendered html string **/
	public function __toString(){
		$this->render();
		return '';
	}
}