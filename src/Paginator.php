<?php
 
class Paginator {
    
    //declare all internal (private) variables
    private $_conn;
    private $_limit; //records (rows) to show per page
    private $_page; //current page
    private $_query;
    private $_total;
    private $_row_start;
    
    public function __construct($conn, $query) {
     
        $this->_conn = $conn; 
        $this->_query = $query; 
        $this->_total = count($conn->fetchAll($query));
     
    }
    
    //LIMIT DATA
    public function getData( $limit = 10, $page = 1 ) { 
     
        $this->_limit = $limit;
        $this->_page = $page;

        //create the query, limiting records from page, to limit
        $this->_row_start = ( ( $this->_page - 1 ) * $this->_limit );
        $query = $this->_query .
                //add to original query: ( minus one because of the way SQL works )
                " LIMIT {$this->_row_start}, $this->_limit";

        $results =  $this->_conn->fetchAll($query);
        
        //print_r($results);die;

        //return data as object, new stdClass() creates new empty object
        $result         = new stdClass();
        $result->page   = $this->_page;
        $result->limit  = $this->_limit;
        $result->total  = $this->_total;
        $result->data   = $results; 

        return $result; 
    }
    
    //PRINT LINKS
    public function createLinks( $links, $list_class ) 
    {
        //get the last page number
        $last = ceil( $this->_total / $this->_limit );
        
        //calculate start of range for link printing
        $start = ( ( $this->_page - $links ) > 0 ) ? $this->_page - $links : 1;
        
        //calculate end of range for link printing
        $end = ( ( $this->_page + $links ) < $last ) ? $this->_page + $links : $last;
        
        //ul boot strap class - "pagination pagination-sm"
        $html = '<ul class="' . $list_class . '">';

        $class = ( $this->_page == 1 ) ? "disabled" : ""; //disable previous page link <<<
        
        //create the links and pass limit and page as $_GET parameters

        //$this->_page - 1 = previous page (<<< link )
        $previous_page = ( $this->_page == 1 ) ? 
        '<a href=""><li class="' . $class . '">&laquo;</a></li>' : //remove link from previous button
        '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . ( $this->_page - 1 ) . '">&laquo;</a></li>';

        $html .= $previous_page;

        if ( $start > 1 ) { //print ... before (previous <<< link)
            $html .= '<li><a href="?limit=' . $this->_limit . '&page=1">1</a></li>'; //print first page link
            $html .= '<li class="disabled"><span>...</span></li>'; //print 3 dots if not on first page
        }

        //print all the numbered page links
        for ( $i = $start ; $i <= $end; $i++ ) {
            $class = ( $this->_page == $i ) ? "active" : ""; //highlight current page
            $html .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . $i . '">' . $i . '</a></li>';
        }

        if ( $end < $last ) { //print ... before next page (>>> link)
            $html .= '<li class="disabled"><span>...</span></li>'; //print 3 dots if not on last page
            $html .= '<li><a href="?limit=' . $this->_limit . '&page=' . $last . '">' . $last . '</a></li>'; //print last page link
        }

        $class = ( $this->_page == $last ) ? "disabled" : ""; //disable (>>> next page link)
        
        //$this->_page + 1 = next page (>>> link)
        $next_page = ( $this->_page == $last) ? 
        '<li class="' . $class . '"><a href="">&raquo;</a></li>' : //remove link from next button
        '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . ( $this->_page + 1 ) . '">&raquo;</a></li>';

        $html .= $next_page;
        $html .= '</ul>';
        
        return $html;
    }
}
?>
