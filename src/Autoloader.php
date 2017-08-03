<?php

namespace App;

class Autoloader {
	
	public static function register(bool $prepend = true){
		
		spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
	}
	
	public static function unregister(){
			
		spl_autoload_unregister(array(__CLASS__, 'autoload'));
    }
	
	public static function autoload($class){
		$class = str_replace('App\\', '', $class);
		$file = "src/".str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
        if (is_file($file) && is_readable($file)) {
            require_once $file;
		}else {
			echo $file." autoload failed<br>";
		}
	}
}

?>