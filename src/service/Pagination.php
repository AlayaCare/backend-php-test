<?php

namespace service;

class Pagination {
    
    /**
    * Get pagination
    * @param int $currenPage
    * @param int $pageSize, Number of todo per page
    * @param int $total
    * @return Array with value of pagination
    */
    public function pagination($currenPage, $pageSize, $total){
             
        $QTDPages = ceil($total / $pageSize); //ceil retourne l'entier supérieur du nombre value.    
        $start = ((($currenPage - $total) > 1) ? $currenPage - $total : 1);
        $end = ((($currenPage + $total) < $QTDPages) ? $currenPage + $total : $QTDPages);
        ($total > ($currenPage * $pageSize)) ? $next = '?page='.($currenPage + 1):$next = null;      
        ($currenPage - 1)>0 ? $previous = '?page='.($currenPage - 1):$previous = null;
        $pages = [];
        if($QTDPages > 1 && $currenPage <= $QTDPages){
            for($i = $start; $i <= $end; $i++){
                $linkText = $i;
                $class = "";
                if($i == $currenPage){
                    $class = "active";
                }
                array_push($pages, array('linkText'=> $linkText , 'link' => '?page=' . $i, 'class' => $class));
            }
        }                     
        return array('pages' => $pages,
            'currenPage' => $currenPage,
            'previous' => $previous,
            'next' => $next,
            'QTDPages' => $QTDPages);
    }
    
}
