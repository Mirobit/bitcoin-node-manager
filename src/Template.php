<?php

namespace App;

class Template {
	private $section, $sectionPath, $data;

	public function __construct($data = array()) {
		$this->section = $data['section'];
		$this->sectionPath = "views/".$this->section.".phtml";
		$this->data = $data;
	}

	public function render() {
		if(file_exists( $this->sectionPath)){
			//Extracts vars to current view scope
			extract($this->data);
			//Starts output buffering
			ob_start();
			//Includes contents
      include 'views/header.phtml';
      if(!empty($error)){  
      echo '<div class="alert alert-danger col-md-3 col-sm-6 col-xs-12" role="alert">'.$error.'</div>'; 
      } else {
        include $this->sectionPath;
      }
			$buffer = ob_get_contents();
			@ob_end_clean();
			//Returns output buffer
			return $buffer;
			
		} else {
			echo "Couldn't render view";
		}
	}
}
?>