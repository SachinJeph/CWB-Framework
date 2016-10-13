<?php
// define the application path
define('ROOT', dirname(dirname(__FILE__)));

if(isset($_GET['image_name'])){
	$image_path = ROOT . "/tmp/uploads/images/" . $_GET['image_name'];
	if(file_exists($image_path)){
		$allowed_mimes = array('image/gif', 'image/jpeg', 'image/png');
		$imginfo = getimagesize($image_path);
		if(in_array($imginfo['mime'], $allowed_mimes)){
			header("Content-type: " . $imginfo['mime']);
			readfile($image_path);
			exit();
		}
	}
}

header("Location: /404");
?>