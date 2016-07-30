<?php
/**
* CWB - A PHP Framework For Faster Develpoment
* @package CWB
* @author Nitin Goyal <nitinpiet125@poornima.org>
**/

define('CWB_START', microtime(true));

// define the directory separator
define('DS', DIRECTORY_SEPARATOR);

// define the application path
define('ROOT', dirname(dirname(__FILE__)));

define('CWB_DIR', ROOT . DS . 'CWB' . DS);

require CWB_DIR . 'Core' . DS . 'Autoloader.php';

CWB\Core\Start::Main();
