<?php
    
    namespace App\SOAP;
    
    use DOMDocument;
    use SoapClient;
    class LionSoap extends SoapClient
    {
    
        public $token;
    
        public function __doRequest($request, $location, $action, $version, $one_way = null)
        {
            $xmlRequest = new DOMDocument('1.0');
            $xmlRequest->loadXML($request);
            
            $request = $xmlRequest->saveXML();
            
            $response = parent::__doRequest($request, $location, $action, $version, $one_way=null);
            $tmpname = explode('/',$action);
            
            $f = fopen("../Log/Lion/LionSOAPRequest" . $tmpname[4] . ".xml", "w");
            fwrite($f, $request . "\n");
            fclose($f);
            
            $f = fopen("../Log/Lion/LionSOAPResponse" . $tmpname[4] . ".xml", "w");
            fwrite($f, $response . "\n");
            fclose($f);
            
            $body = stristr($response, '<BinarySecurityToken');
            $body = stristr($body, '>');
            $matches = substr($body, 1, strpos($body, '</', 1)-1);
            
            $this->token = $matches;
            
            return $response;
            
        }
        
    }
