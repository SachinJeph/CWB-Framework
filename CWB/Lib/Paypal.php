<?php

namespace CWB\Lib;

/**
* Class View
*
* @package CWB
**/
final class Paypal {
	private $business,
			$currency,
			$cursymbol,
			$location,
			$returnurl,
			$returntxt,
			$cancelurl,
			$shipping,
			$custom;
	
	private $items;
	
	public function __construct($config){
		// default settings
		$settings = array(
			'business' => '', // Paypal email address
			'currency' => '', // Paypal currency
			'cursymbol' => '', // Currency symbol
			'location' => '', // Location code
			'returnurl' => '', // where to go back when the transaction is done
			'returntxt' => '', // what is written on the return button in paypal
			'cancelurl' => '', // where to go if the user cancels
			'shipping' => 0, // shipping cost
			'custom' => '' // custom attribute
		);
		
		// override default settings
		if(!empty($config)){
			foreach($config as $key=>$val){
				if(!empty($val)){
					$settings[$key] = $val;
				}
			}
		}
		
		$this->business = $settings['business'];
		$this->currency = $settings['currency'];
		$this->cursymbol = $settings['cursymbol'];
		$this->location = $settings['location'];
		$this->returnurl = $settings['returnurl'];
		$this->returntxt = $settings['returntxt'];
		$this->cancelurl = $settings['cancelurl'];
		$this->shipping = $settings['shipping'];
		$this->custom = $settings['custom'];
		$this->items = array();
	}
	
	// add a item to the cart
	public function addSimpleItem($item){
		if(!empty($item['quantity']) && is_numeric($item['quantity']) && $item['quantity']>0 && !empty($item['name'])){
			$items = $this->items;
			$items[] = $item;
			$this->items = $items;
		}
	}
	
	// add multiple items to paypal list
	public function addMultipleItems($items){
		if(!empty($items)){
			foreach($items as $item){ // loop through the items
				$this->addSimpleItem($item); // add them 1 by 1
			}
		}
	}
	
	public function getPaypalFormData(){
		if($this->business != '' && $this->currency != ''){
			$data = array(
				'cmd' => "_cart",
				'upload' => "1",
				'no_note' => "0",
				'bn' => "PP-BuyNowBF",
				'tax_cart' => "0",
				'rm' => "2",
				'business' => $this->business,
				'handling_cart' => '0',
				'currency_code' => $this->currency,
				//'lc' => $this->location,
				'return' => $this->returnurl,
				'cbt' => $this->returntxt,
				'cancel_return' => $this->cancelurl,
				'custom' => $this->custom,
				'items' => $this->items
			);
		}else{
			$data = array();
		}
		return $data;
	}
}