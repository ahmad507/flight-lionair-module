<?php
    
    
    namespace App\Repositories;
    
    
    class Passenger2
    {
        protected $dob = null;
        protected $gender = null;
        protected $firstName = null;
        protected $lastName = null;
        protected $middleName = null;
        protected $title = null;
        protected $paxType = null;
        protected $infant = null;
    
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
    
        public function hasInfant()
        {
            if ($this->infant instanceOf Passenger) {
                return true;
            } else {
                return false;
            }
        }
    
        public function isInfant()
        {
            if ($this->paxType == 'INF') {
                return true;
            } else {
                return false;
            }
        }
    
        public function isChild()
        {
            if ($this->paxType == 'CHD') {
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
