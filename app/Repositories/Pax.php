<?php
    
    
    namespace App\Repositories;
    
    
    class Pax extends Request
    {
        protected $adultCount;
        protected $childCount;
        protected $infantCount;
        protected $list;
        
        public function __construct()
        {
            $this->adultCount = 0;
            $this->childCount = 0;
            $this->infantCount = 0;
            $this->list = array();
        }
    }
