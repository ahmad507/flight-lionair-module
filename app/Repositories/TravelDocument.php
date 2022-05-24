<?php
    
    
    namespace App\Repositories;
    
    
    class TravelDocument extends Request
    {
        const PASSPORT = 'passport'; // untuk international
        const IDENTITY = 'identity';  // untuk KTP (WNI)
        
        protected $no = null;
        protected $expiryDate = null;
        protected $type = null;
        protected $issuedByCode = null;
        protected $birthCountry = null;
        
        public function isNotValid()
        {
            if (isset($no)) {
                return ($no == null) || (strlen($no) == 0) ? false : true;
            }
        }
        
        public function isPassport()
        {
            if ($this->type == self::PASSPORT) {
                return true;
            } else {
                return false;
            }
        }
        
        public function isIdentity()
        {
            if ($this->type == self::IDENTITY) {
                return true;
            } else {
                return false;
            }
        }
        
        
    }
