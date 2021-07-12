<?php

    class pagination {
        public $firstPage = 1;
        public $itemsPerPage = 10;
        public $navButtonLimit = 4;
        public $showFirst = true;    
        public $showPrev = true;    
        public $showNext = true;   
        public $showLast = true;
        public $showPageNumbers = true;
        public $offset;
        
        private $i;
        private $items = 0;
        private $currentPage = 1;
        private $lastPage;
        private $pageUrl;
        private $prefix = '?';
        
        public function __construct($numItems) {
            if($numItems != null) {
                $this->items = $numItems;
                $this->pageUrl = explode($_SERVER['SERVER_NAME'] . ROOT_DIR)[1] . explode('?', $_SERVER['REQUEST_URI'])[0];
            }
            
            //Remove existing page query
            if(strlen(explode('?', $_SERVER['REQUEST_URI'])[1])) {
			$queryString = preg_replace('/((\?|\&)page=[^\&]+)/', '', '?' . explode('?', $_SERVER['REQUEST_URI'])[1]);
            }
            else {
                $queryString = '';
            }
            
            //Change first character to ? if &
            if(strpos($queryString, '&') === 0) {
                $queryString = '?' . substr($queryString, 1);
            }
            
            //Change first & to ?
            $this->prefix = $queryString . (strpos($queryString, '?') === false ? $this->prefix = '?' : $this->prefix = '&');
        }
        
        public function load() {
            if($this->items > 0) {
                $this->lastPage = ceil($this->items / $this->itemsPerPage);
            }
            
            if(isset($_GET['page']) && $_GET['page'] > $this->lastPage) {
                $this->currentPage = $this->lastPage;
            }
            elseif(isset($_GET['page']) && $_GET['page'] < $this->firstPage) {
                $this->currentPage = $this->firstPage;
            }
            elseif(isset($_GET['page']) && is_numeric($_GET['page'])) {
                $this->currentPage = $_GET['page'];
            }
            else {
                $this->currentPage = $this->firstPage;
            }
            
            if($this->currentPage > $this->navButtonLimit) {
                $this->i = $this->currentPage - $this->navButtonLimit;
            }
            else {
                $this->i = $this->firstPage;
            }
            
            $this->offset = ($this->currentPage * $this->itemsPerPage) - $this->itemsPerPage;
        }
        
        public function display() {
            $output =
                '<nav aria-label="page-naviagtion">
                    <ul class="pagination">';
            
            if($this->showFirst == true && $this->currentPage > $this->firstPage) {
                $output .=
                    '<li class="page-item">
                        <a class="page-link" href="' . $this->pageUrl . $this->prefix . 'page=' . $this->firstPage . '">
                            <span class="fas fa-chevron-left small"></span><span class="fas fa-chevron-left small"></span> First
                        </a>
                    </li>';
            }
            
            if($this->showPrev == true && $this->currentPage > $this->firstPage) {
                $output .=
                    '<li class="page-item">
                        <a class="page-link" href="' . $this->pageUrl . $this->prefix . 'page=' . ($this->currentPage - 1) . '">
                            <span class="fas fa-chevron-left small"></span> Prev
                        </a>
                    </li>';
            }
            
            if($this->showPageNumbers == true) {
                $max = $this->currentPage + $this->navButtonLimit;
                
                if($max >= $this->lastPage) {
                    $max = $this->lastPage;
                }
                
                for($this->i; $this->i <= $max; $this->i++) {
                    $output .=
                        '<li class="page-item ' . ($this->currentPage == $this->i ? 'active' : '') . '">
                            <a class="page-link" href="' . $this->pageUrl . $this->prefix . 'page=' . $this->i . '">'
                                . $this->i .
                            '</a>
                        </li>';
                }
            }
            
            if($this->showNext == true && $this->currentPage < $this->lastPage) {
                $output .=
                    '<li class="page-item">
                        <a class="page-link" href="' . $this->pageUrl . $this->prefix . 'page=' . ($this->currentPage + 1) . '">
                            Next <span class="fas fa-chevron-right small"></span>
                        </a>
                    </li>';
            }
            
            if($this->showLast == true && $this->currentPage < $this->lastPage) {
                $output .=
                    '<li class="page-item">
                        <a class="page-link" href="' . $this->pageUrl . $this->prefix . 'page=' . $this->lastPage . '">
                            Last <span class="fas fa-chevron-right small"></span><span class="fas fa-chevron-right small"></span>
                        </a>
                    </li>';
            }
            
            $output .=
                    '</ul>
                </nav>';
            
            return $output;
        }
    }
    
?>