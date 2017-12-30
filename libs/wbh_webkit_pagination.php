<?php	
	
/*
* WBH - december 2017, now intertwined with 'db_pdo.php' routines
* 
* the "createLinks" method builds the parameter "limit" into each link
* but the rest of my code (the other files) ignores it and does not specify a limit
* thus leaving limit to be the default as hard-coded below
* 
* in the future, I could/might make the limit parameter a thing that works
*/	
class Paginator {

	private $_limit;
	private $_page;
	private $_query;
	private $_params; 
	private $_stmt;
	private $_total;

	public function __construct( $query, $params = null ) {

		$this->_query = $query;
		$this->_params = $params;
		$this->_stmt = \DB\pdo_query($query, $params);
		$all = $this->_stmt->fetchAll();
		$this->_total = count($all);

	}

	public function getData( $page = 1, $limit = 10) {

		if (!$limit) { $limit = 10; }
		if (!$page) { $page = 1; }

		$this->_limit   = $limit;
		$this->_page    = $page;
		$results = array();
		if ( $this->_page == 'all' ) {
			$query      = $this->_query;
		} else {
			$query      = $this->_query . " LIMIT " . ( ( $this->_page - 1 ) * $this->_limit ) . ", {$this->_limit}";
		}
		$stmt = \DB\pdo_query($query, $this->_params);
		while ( $row = $stmt->fetch() ) {
			$results[]  = $row;
		}

		$result         = new stdClass();
		$result->page   = $this->_page;
		$result->limit  = $this->_limit;
		$result->total  = $this->_total;
		$result->data   = $results;

		return $result;
	}		


	public function createLinks( $links = 7, $aria_label = 'search results' ) {
		if ( $this->_total < $this->_limit) {
			return '';
		}

		$last       = ceil( $this->_total / $this->_limit );

		$start      = ( ( $this->_page - $links ) > 0 ) ? $this->_page - $links : 1;
		$end        = ( ( $this->_page + $links ) < $last ) ? $this->_page + $links : $last;

		$html       = '<nav aria-label="{$aria_label}"><ul class="pagination">'."\n";

		$class      = ( $this->_page == 1 ) ? "disabled" : "";
		$html       .= '<li class="page-item '.$class.'"><a class="page-link" href="?limit=' . $this->_limit . '&page=' . ( $this->_page - 1 ) . '">&laquo;</a></li>'."\n";

		if ( $start > 1 ) {
			$html   .= '<li class="page-item"><a class="page-link" href="?limit=' . $this->_limit . '&page=1">1</a></li>'."\n";
			$html   .= '<li class="page-item disabled"><span>...</span></li>'."\n";
		}

		for ( $i = $start ; $i <= $end; $i++ ) {
			$class  = ( $this->_page == $i ) ? "active" : "";
			$html   .= '<li class="page-item '.$class.'"><a class="page-link" href="?limit=' . $this->_limit . '&page=' . $i . '">' . $i . '</a></li>'."\n";
		}

		if ( $end < $last ) {
			$html   .= '<li class="disabled"><span>...</span></li>'."\n";
			$html   .= '<li class="page-item"><a class="page-link" href="?limit=' . $this->_limit . '&page=' . $last . '">' . $last . '</a></li>'."\n";
		}

		$class      = ( $this->_page == $last ) ? "disabled" : "";
		$html       .= '<li class="page-item '.$class.'"><a class="page-link" href="?limit=' . $this->_limit . '&page=' . ( $this->_page + 1 ) . '">&raquo;</a></li>'."\n";
		
		$class      = ( $this->_page == 'all') ? "active" : "";
		$html .= '<li class="page-item '.$class.'"><a class="page-link" href="?page=all">all</a></li>';

		$html       .= '</ul></nav>'."\n";

		return $html;
	}



}	
	
?>