<?php	
	
/*
* WBH - december 2017, now intertwined with 'db_pdo.php' routines
* march 2020, added in "all" as an option for page, handled places that bit it on 'non-numeric' values for that
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

	public function getData( $page = 1, $limit = 40) {

		if (!$limit) { $limit = 100; }
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
		while ( $row = $stmt->fetch(\PDO::FETCH_ASSOC) ) {
			$results[]  = $row;
		}

		$result         = new stdClass();
		$result->page   = $this->_page;
		$result->limit  = $this->_limit;
		$result->total  = $this->_total;
		$result->data   = $results;

		return $result;
	}		


	public function createLinks( $links = 7, $aria_label = 'search results', $xtra_query_string = null ) {
		if ( $this->_total < $this->_limit) {
			return '';
		}

		$last       = ceil( $this->_total / $this->_limit );

		if ($this->_page == 'all') {
			$start = 1;
			$end = $last;
		} else {
			$start      = ( ( $this->_page - $links ) > 0 ) ? $this->_page - $links : 1;
			$end        = ( ( $this->_page + $links ) < $last ) ? $this->_page + $links : $last;
		}

		$html       = '<nav aria-label="{$aria_label}"><ul class="pagination">'."\n";

		$class      = ( $this->_page == 1  || $this->_page == 'all') ? "disabled" : "";
		$previous_page = ($this->_page == 'all') ? $start : $this->_page - 1;
		$html       .= '<li class="page-item '.$class.'"><a class="page-link" href="?page=' . ( $previous_page ) . '">&laquo;</a></li>'."\n";

		if ( $start > 1 ) {
			$html   .= '<li class="page-item"><a class="page-link" href="?page=1'.$xtra_query_string.'">1</a></li>'."\n";
			$html   .= '<li class="page-item disabled"><span>...</span></li>'."\n";
		}

		for ( $i = $start ; $i <= $end; $i++ ) {
			$class  = ( $this->_page == $i ) ? "active" : "";
			$html   .= '<li class="page-item '.$class.'"><a class="page-link" href="?page=' . $i . $xtra_query_string.'">' . $i . '</a></li>'."\n";
		}

		if ( $end < $last ) {
			$html   .= '<li class="disabled"><span>...</span></li>'."\n";
			$html   .= '<li class="page-item"><a class="page-link" href="?page=' . $last . $xtra_query_string.'">' . $last . '</a></li>'."\n";
		}

		$class      = ( $this->_page == $last || $this->_page == 'all') ? "disabled" : "";
		$next_page = ($this->_page == 'all') ? $end : $this->_page + 1;
		$html       .= '<li class="page-item '.$class.'"><a class="page-link" href="?page=' . ( $next_page ) . $xtra_query_string.'">&raquo;</a></li>'."\n";
		
		$class      = ( $this->_page == 'all') ? "active" : "";
		$html .= '<li class="page-item '.$class.'"><a class="page-link" href="?page=all'.$xtra_query_string.'">all</a></li>';

		$html       .= '</ul></nav>'."\n";

		return $html;
	}



}	
	