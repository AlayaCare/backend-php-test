<?php

namespace Helper;

class Pagination {
    
    public function prepare($total) {
        $limit = 3;
        $pages = ceil($total / $limit);
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
            'options' => array(
                'default'   => 1,
                'min_range' => 1,
            ),
        ));
        $currentPage = min($pages, $page);
        $offset = ($currentPage - 1)*$limit;
        $start = $offset + 1;
        $end = min(($offset + $limit), $total);
        return [
            'currentPage' => $currentPage,
            'pages' => $pages,
            'start' => $start,
            'end' => $end,
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
        ];
    }

}


?>