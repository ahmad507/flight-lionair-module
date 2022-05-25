<?php
    
    
    namespace App\Repositories\Airline\Lion;
    use App\SOAP\LionSoap;
    use Exception;
    use Illuminate\Support\Facades\Session;
    use SoapFault;
    use SoapHeader;
    use Spatie\ArrayToXml\ArrayToXml;
    
    class LionAirRepository
    {
    
        private $token;
    
        public function LoginClient()
        {
            #________________________________________________________________
            // incoming from interface setup in database store
            $url = 'http://202.4.170.9/';
            $username = 'versa_travel';
            $password = 'Versa12*';
            #________________________________________________________________
            // manual setup each request service
            $action_path = 'LionAirTAAPI/SessionCreate.asmx?wsdl';
            $organization = 'JT';
            $service = 'Create';
            $action = 'CreateSession';
            #________________________________________________________________
            try {
                $client = new LionSoap($url . $action_path);
            } catch (SoapFault $e) {
                $response =  $e->getMessage();
                $this->_log_response("../log/Lion/LionSoapFaultLogon.txt", $response);
                return response()->json($response, 400);
            }
            $security_token_create =  [ 'Username' => $username, 'Password' => $password, 'Organization' => $organization];
            $security = [ 'UsernameToken' => $security_token_create];
            $headers [] = new SoapHeader('http://www.ebxml.org/namespaces/messageHeader', 'MessageHeader', $this->_message_header($service, $action));
            $headers [] = new SoapHeader('http://schemas.xmlsoap.org/ws/2002/12/secext', 'Security', $security);
            #--------------------------------------------------------------------------------------------
            //  request login for binary security token
            try {
                if (isset($client)) {
                    $client->__setSoapHeaders($headers);
                }
                if (isset($client)) {
                    $response = $client->Logon();
                    $this->_log_response("../log/Lion/LionSOAPLoginSuccess.txt", $response);
                }
                if (isset($client)) {
                    $binary_security_token = $client->token;
                }
                if (isset($binary_security_token)) {
                    $this->_log_response("../log/Lion/LionSOAPLoginToken.txt", $binary_security_token);
                }
                if (isset($binary_security_token)) {
                    Session::put('BinarySecurityToken', $binary_security_token); //  save session token untuk request service lainya
                }
                if (isset($response)) {
                    return response()->json($response, 200);
                }
            } catch (Exception $e){
                $response =  $e->getMessage();
                $this->_log_response("../log/Lion/LionSOAPLoginFailed.txt", $response);
                return response()->json($response, 400);
            }
            #--------------------------------------------------------------------------------------------
            
        }
        
        public function LogoutClient()
        {
            #________________________________________________________________
            // manual setup each request service
            $url = 'http://202.4.170.9/';
            $action_path = 'LionAirTAAPI/SessionClose.asmx?wsdl';
            $service = 'Logoff';
            $action = 'SessionClose';
            #________________________________________________________________
            try {
                $client = new LionSoap($url . $action_path);
            } catch (SoapFault $e) {
                $response =  $e->getMessage();
                $this->_log_response("../log/Lion/LionSoapFaultLogoff.txt", $response);
                return response()->json($response, 400);
            }
            $binary_security_token = Session::get('BinarySecurityToken');
            $security = ['BinarySecurityToken' => $binary_security_token];
            $headers [] = new SoapHeader('http://www.ebxml.org/namespaces/messageHeader', 'MessageHeader', $this->_message_header($service, $action));
            $headers [] = new SoapHeader('http://schemas.xmlsoap.org/ws/2002/12/secext', 'Security', $security);
            #--------------------------------------------------------------------------------------------
            //  request login for binary security token
            try {
                if (isset($client)) {
                    $client->__setSoapHeaders($headers);
                }
                if (isset($client)) {
                    $response = $client->Logoff();
                    $this->_log_response("../log/Lion/LionSOAPLogoffSuccess.txt", $response);
                }
                if (isset($response)) {
                    return response()->json($response, 200);
                }
            } catch (Exception $e){
                $response =  $e->getMessage();
                $this->_log_response("../log/Lion/LionSOAPLogoffFailed.txt", $response);
                return response()->json($response, 400);
            } finally {
                $response = 'Finally Request Data Result';
                return response()->json($response, 200);
            }
            #--------------------------------------------------------------------------------------------
        }
        
        public function SearchFlight()
        {
            #________________________________________________________________
            // manual setup each request service
            $url = 'http://202.4.170.9/';
            $action_path = 'LionAirTAAPI/FlightMatrixService.asmx?wsdl';
            $service = 'GetFlightMatrix';
            $action = 'FlightMatrixRQ';
            #________________________________________________________________
            try {
                $client = new LionSoap($url . $action_path);
            } catch (SoapFault $e) {
                $response =  $e->getMessage();
                $this->_log_response("../log/Lion/LionSoapFaultSearchFlight.txt", $response);
                return response()->json($response, 400);
            }
            $binary_security_token = Session::get('BinarySecurityToken');
            $security = ['BinarySecurityToken' => $binary_security_token];
            $headers [] = new SoapHeader('http://www.ebxml.org/namespaces/messageHeader', 'MessageHeader', $this->_message_header($service, $action));
            $headers [] = new SoapHeader('http://schemas.xmlsoap.org/ws/2002/12/secext', 'Security', $security);
            #--------------------------------------------------------------------------------------------
            //  request login for binary security token
            $air_traveler_avail = array();
    
            $air_traveler_avail[] = [ 'AirTraveler' => [ 'PassengerTypeQuantity' => [ 'Code' => 'ADT', 'Quantity' => 1 ] ] ];
            
            $carrier_code = 'JT';
            $origin = 'DPS';
            $destination = 'CGK';
            
            $search_param = [
                'flightMatrixRQ' => [
                    'AirItinerary' => [
                        'OriginDestinationOptions' => [
                            'OriginDestinationOption' => [
                                'FlightSegment' => [
                                    'DepartureDateTime' => date("Y-m-d\TH:i:s", time() + 886400),
                                    'MarketingAirline' => [ 'Code' => $carrier_code ],
                                    'DepartureAirport' => [ 'LocationCode' => $origin ],
                                    'ArrivalAirport' => [ 'LocationCode' => $destination ],
                                ],
                            ],
                        ],
                    ],
                    'TravelerInfoSummary' => [
                        'AirTravelerAvail' => $air_traveler_avail,
                    ],
                ],
            ];
            
            try {
                if (isset($client)) {
                    $client->__setSoapHeaders($headers);
                }
                if (isset($client)) {
                    $response = $client->FlightMatrixRequest($search_param);
                    $reverse_data = $this->_rev($response);
                    
                    $data_response = ArrayToXml::convert($reverse_data);
                    
                    // save response------------------------------------------------------------------------------------------------
                    $this->_log_response("../log/Lion/LionSOAPSearchFlightSuccess.txt", $response);
                    $this->_log_response("../log/Lion/LionSOAPSearchFlightSuccess.xml", $data_response);
                    //-------------------------------------------------------------------------------------------------------------------
                }
                if (isset($response)) {
                    return response()->json($response, 200);
                }
            } catch (Exception $e){
                $response =  $e->getMessage();
                $this->_log_response("../log/Lion/LionSOAPSearchFlightFailed.txt", $response);
                return response()->json($response, 400);
            }
        }
        
        private function _message_header($service, $action)
        {
            return [
                'CPAId' => 'JT',
                'Service' => $service,
                'Action' => $action,
                'MessageData' => [ 'MessageId' => 'mid:13:30:03.161@vedaleon.com' ],
            ];
        }
    
        private function _log_response($file, $response)
        {
            $f = fopen($file, 'w');
            fwrite($f, print_r($response, true) . "\n");
            fclose($f);
        }
    
        private function _rev($response)
        {
            return json_decode(json_encode($response), true);
        }
    }
