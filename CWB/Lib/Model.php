<?php
namespace CWB\Lib;

class Model {
	protected $_model;
	
	public $db;
	
	public function __construct($db){
		$this->db = $db;
		$this->_model = get_class($this);
		
		$this->init();
	}
	
	protected function init(){}
}