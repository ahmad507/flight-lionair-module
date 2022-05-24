<?php
    
    
    namespace App\Repositories;
    
    
    class Passenger extends Request
    {
        const INFANT = 'INF';
        const CHILD = 'CHD';
        const ADULT = 'ADT';
        
        protected $dob = null;
        protected $gender = null;
        protected $firstName = null;
        protected $lastName = null;
        protected $middleName = null;
        protected $title = null;
        protected $paxType = null;
        protected $infant = null;
        protected $baggageOption = null;
        protected $travelDoc = null;
        protected $ssrCode = null;
    
        public function __get($property)
        {
            if ($property == 'gender') {
                if ($this->gender == null) {
                    if ($this->title != null) {
                        $this->calcGenderByTitle();
                    }
                }
            }
        
            if ($property == 'title') {
                return strtoupper($this->$property);
            }
        
            return $this->$property;
        }
        
        public function calcGenderByTitle()
        {
            switch ($this->__get('title')) {
                case 'MR':
                case 'MSTR':
                    $this->gender = 'Male';
                    break;
                case 'MRS':
                case 'MS':
                case 'MISS':
                    $this->gender = 'Female';
                    break;
                default:
                    $this->gender = null;
            }
        }
        
        public function hasTravelDoc()
        {
            if($this->travelDoc instanceof TravelDocument){
                if ($this->travelDoc->isNotValid()) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
        
        public function hasInfant()
        {
            if($this->infant instanceof Passenger){
                return true;
            } else {
                return false;
            }
        }
        
        public function isInfant()
        {
            if ($this->paxType == self::INFANT) {
                return true;
            } else {
                return false;
            }
        }
        
        public function isChild()
        {
            if ($this->paxType == self::CHILD) {
                return true;
            } else {
                return false;
            }
        }
        
        public function getWeightCategory()
        {
            if ($this->isChild()) {
                return 'Child';
            } else {
                return $this->__get('gender');
            }
        }
        
    }
