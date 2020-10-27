<?php
class View extends WBHObject
{

	public $title = 'will hines online practices';
	public $data = array();
	private $header = 'header';
	private $footer = 'footer';
	private $snippetDir = 'views';

	public function renderPage($page = null, $data = null) {
		$data = $this->updateData($data); // add some things to it
	    if (is_array($data) && !empty($data)) {
	        extract($data);
	    }
	
		include $this->getPageStr($this->header.(strpos($page, 'admin') ? '-admin' : ''));
		include $this->getPageStr($page);
		include $this->getPageStr($this->footer.(strpos($page, 'admin') ? '-admin' : ''));
	}
			
	public function renderSnippet($bargle, $data=null) {
		$data = $this->updateData($data); // add some things to it
	    if (is_array($data) && !empty($data)) {
	        extract($data);
	    }
	    ob_start();
		include $this->getPageStr($bargle);
	    return ob_get_clean();
	}
	
	public function renderHTML($html, $data = null, $admin = false) {
		$data = $this->updateData($data); // add some things to it
	    if (is_array($data) && !empty($data)) {
	        extract($data);
	    }
		//include $this->getPageStr($this->common_vars_page);
		include $this->getPageStr($this->header.($admin ? '-admin' : ''));
		echo $html;
		include $this->getPageStr($this->footer.($admin ? '-admin' : ''));
	}

	private function getPageStr($pagename) {
		return $this->snippetDir.'/'.$pagename.'.php';
	}
	
	// just add to data
	public function updateData($data = null) {
		
		// need to add in user and workshop at last possible minute
		// and this is it
		global $u, $wk, $message, $error, $sc, $heading;
		$this->data['u'] = $u;
		$this->data['wk'] = $wk;
		$this->data['error'] = $error;
		$this->data['message'] = $message;
		$this->data['heading'] = $heading;
		$this->data['sc'] = $sc ? $sc : $_SERVER['SCRIPT_NAME'];
		$this->data['path'] = $this->snippetDir.'/';
		
		if ($data) {
			$this->data = array_merge($this->data, $data); // take the data that's passed in
		}
	    return $this->data;
	}	
	
	public function add_globals($vars) {
		if ($vars && is_array($vars)) {
			foreach ($vars as $v) {
				global $$v;
				$this->data[$v] = $$v;
			}
		}
	}

}