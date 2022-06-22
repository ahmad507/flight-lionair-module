<?php
    
    namespace App\Repositories\Airline\Lion;
    use App\Repositories\Pax;
    use App\Repositories\SearchRequest;
    use App\SOAP\LionSoap;
    use Exception;
    use Illuminate\Support\Facades\Session;
    use SoapFault;
    use SoapHeader;
    use stdClass;
    
    class LionAirRepository
    {
        
        private $token;
        
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
        
        private function _create_matrix_row($data_response)
        {
            $flight_matrix_rows = [];
            $flight_matrix_row = $data_response->FlightMatrixRS->FlightMatrices->FlightMatrix->FlightMatrixRows->FlightMatrixRow;
            
            if ($flight_matrix_row instanceOf stdClass) $flight_matrix_row = array($flight_matrix_row);
            
            $i = -1;
            foreach ($flight_matrix_row as $row) {
                $flight_segment = [];
                
                $flight_segment = $row->OriginDestinationOptionType->FlightSegment;
                if ($flight_segment instanceOf stdClass) $flight_segment = array($flight_segment);
                
                foreach ($flight_segment as $segment) {
                    $flight_number = $segment->OperatingAirline->Code . ' ' . $segment->OperatingAirline->FlightNumber;
                    
                    if (empty(str_replace(' ', '', $flight_number))) continue;
                    $i++;
                    
                    $time_depart = strtotime($segment->DepartureDateTime);
                    $time_arrive = strtotime($segment->ArrivalDateTime);
                    
                    $result[$i]['id'] = $i;
                    $result[$i]['flight'] = $flight_number;
                    $result[$i]['route'] = $segment->DepartureAirport->LocationCode . '-' . $segment->ArrivalAirport->LocationCode;
                    $result[$i]['time_depart'] = (string)$time_depart;
                    $result[$i]['time_arrive'] = (string)$time_arrive;
                    $result[$i]['str_time'] = date('H:i', $time_depart) . ' ' . date('H:i', $time_arrive);
                    $result[$i]['longdate'] = $time_depart;
                    $result[$i]['weekday'] = strtoupper(date('D', $time_depart));
                    
                    $booking_class_avail = [];
                    
                    $booking_class_avail = $segment->BookingClassAvails->BookingClassAvail;
                    if ($booking_class_avail instanceOf stdClass) $booking_class_avail = array($booking_class_avail);
                    
                    $j = -1;
                    foreach ($booking_class_avail as $class) {
                        $segment_key = [ $segment->OperatingAirline->Code, $segment->OperatingAirline->FlightNumber, $segment->OperatingAirline ->CodeContext, $class->ResBookDesigCode, (int) $class->ResBookDesigQuantity, $segment->DepartureAirport->LocationCode, date('Y-m-d\TH:i:s', $result[$i]['time_depart']), $segment->ArrivalAirport->LocationCode, date('Y-m-d\TH:i:s', $result[$i]['time_arrive']), $segment->StopQuantity, $segment->RPH, $segment->MarketingAirline->Code, $segment->MarketingAirline->CodeContext, $segment->RPH, $segment->StopQuantity, $segment->DepartureAirport->LocationCode, $segment->ArrivalAirport->LocationCode ]; // QG~ 213~ ~~UPG~11/23/2019 08:15~CGK~11/23/2019 10:00~
                        $j++;
                        $result[$i][$j]['class'] = $class->ResBookDesigCode;
                        $result[$i][$j]['seat'] = (int) $class->ResBookDesigQuantity;
                        $result[$i][$j]['value'] = implode('#', $segment_key);
                        $result[$i][$j]['disabled'] = ($result[$i][$j]['seat'] == 0) ? 'avail' : 'full';
                    }
                }
            }
            return $result;
        }
        
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
        
        public function SearchFlight($DepartureDate, $ArrivalDate, $DepartureAirport, $ArrivalAirport, $PassengerAdult, $PassengerChild, $PassengerInfant)
        {
            $search_request = new SearchRequest();
            $pax_request = new Pax();
            
            $search_request->date = $DepartureDate;
            $search_request->datereturn = $ArrivalDate;
            $search_request->origin = $DepartureAirport;
            $search_request->destination = $ArrivalAirport;
            $pax_request->adultCount = $PassengerAdult;
            $pax_request->childCount = $PassengerChild;
            $pax_request->infantCount = $PassengerInfant;
            #________________________________________________________________
            // manual setup each request service
            $url = 'http://202.4.170.9/';
            $action_path = 'LionAirTAAPI/FlightMatrixService.asmx?wsdl';
            $service = 'GetFlightMatrix';
            $action = 'FlightMatrixRQ';
            $carrier_code = 'JT';
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
            /*----------------------------------------------------------------------------------------------*/
            $air_traveler_avail = array();
            $air_traveler_avail[] = [ 'AirTraveler' => [ 'PassengerTypeQuantity' => [ 'Code' => 'ADT', 'Quantity' => $pax_request->adultCount ] ] ];
            if ((int) $pax_request->childCount > 0) $air_traveler_avail[] = [ 'AirTraveler' => [ 'PassengerTypeQuantity' => [ 'Code' => 'CNN', 'Quantity' => $pax_request->childCount ] ] ];
            if ((int) $pax_request->infantCount > 0) $air_traveler_avail[] = [ 'AirTraveler' => [ 'PassengerTypeQuantity' => [ 'Code' => 'INF', 'Quantity' => $pax_request->infantCount ] ] ];
            $search_param = [
                'flightMatrixRQ' => [
                    'AirItinerary' => [
                        'OriginDestinationOptions' => [
                            'OriginDestinationOption' => [
                                'FlightSegment' => [
                                    'DepartureDateTime' => $search_request->date,
                                    'MarketingAirline' => [ 'Code' => $carrier_code ],
                                    'DepartureAirport' => [ 'LocationCode' => $search_request->origin ],
                                    'ArrivalAirport' => [ 'LocationCode' => $search_request->destination ],
                                ],
                            ],
                        ],
                    ],
                    'TravelerInfoSummary' => [
                        'AirTravelerAvail' => $air_traveler_avail,
                    ],
                ],
            ];
            #--------------------------------------------------------------------------------------------
            try {
                if (isset($client)) {
                    $client->__setSoapHeaders($headers);
                }
                if (isset($client)) {
                    $response = $client->FlightMatrixRequest($search_param);
                    // save response------------------------------------------------------------------------------------------------
                    $response_status_result = $response->FlightMatrixRS->FlightMatrices->FlightMatrix->FlightSearchResult;
                    $response_status_rows = $response->FlightMatrixRS->FlightMatrices->FlightMatrix->FlightMatrixRows;
                    if ($response_status_rows instanceOf stdClass) $response_status_rows = json_encode($response_status_rows);
                    if (($response_status_result == 'NoSchedule') || ($response_status_rows == '{}')){
                        $status_message = 'No Schedule Available';
                        return response()->json($status_message, 200);
                    } else {
                        $flight_matrix_row = $this->_create_matrix_row($response);
                        $this->_log_response("../log/Lion/LionSOAPMatrixRow.txt", $flight_matrix_row);
                        $this->_log_response("../log/Lion/LionSOAPSearchFlightSuccess.txt", $response_status_result);
                    }
                    //-------------------------------------------------------------------------------------------------------------------
                }
                if (isset($flight_matrix_row)) {
                    return response()->json($flight_matrix_row, 200);
                }
            } catch (Exception $e){
                $response =  $e->getMessage();
                $response_failed = 'Finish Search Flight With Error :';
                $this->_log_response("../log/Lion/LionSOAPSearchFlightFailed.txt", $response_failed.' '.$response);
                return response()->json($response, 400);
            }
        }
    }
