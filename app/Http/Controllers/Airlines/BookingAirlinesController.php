<?php
    
    namespace App\Http\Controllers\Airlines;
    
    use App\Http\Controllers\Controller;
    use App\Repositories\Airline\Lion\LionAirRepository;
    use Illuminate\Http\Request;
    
    class BookingAirlinesController extends Controller
    {
        
        public $lionAirRepository;
        
        public function __construct(LionAirRepository $lionAirRepository)
        {
            $this->lionAirRepository = $lionAirRepository;
        }
        
        public function lionairapi(Request $request)
        {
            #__________________________________________________________________________
            $DateNow = date('Y-m-d');
            #__________________________________________________________________________
            $DepartureDate = $request->departure_date;
            $ArrivalDate = $request->arival_date;
            $DepartureAirport = $request->from_code;
            $ArrivalAirport = $request->to_code;
            $ReturnAirport = $request->return_code;
            $PassengerAdult = $request->adult;
            $PassengerChild = $request->child;
            $PassengerInfant = $request->infant;
            #__________________________________________________________________________
            if ($DepartureDate < $DateNow){
                return 'Not Valid Date';
            } else if (!empty($ReturnAirport)){
                if ($ArrivalDate < $DepartureDate){
                    return 'Not Valid Date';
                } else if ($ArrivalAirport == $ReturnAirport){
                    return 'Not Valid Return Airport';
                }
            }
            #__________________________________________________________________________
            if (empty($DepartureAirport)){
                return 'Not Valid Origin Airport';
            }
            if (empty($ArrivalAirport)){
                return 'Not Valid Destination Airport';
            }
            if ($DepartureAirport == $ArrivalAirport) {
                return 'Departure and Arrival codes cannot be the same';
            }
            #__________________________________________________________________________
            $TotalPassenger = $PassengerAdult + $PassengerChild + $PassengerInfant;
            #__________________________________________________________________________
            if (($TotalPassenger < 1) || ($PassengerAdult <1)){
                return 'Please Check Your Total Passenger';
            } else if($TotalPassenger > 7){
                return 'Total requested seats (including children) must not exceed 7';
            }
            #___________________________________________________________________________
            $response_login = $this->lionAirRepository->LoginClient();  //  login
            $response_search = $this->lionAirRepository->SearchFlight($DepartureDate, $ArrivalDate, $DepartureAirport, $ArrivalAirport, $PassengerAdult, $PassengerChild, $PassengerInfant ); //  search data
            $response_logout = $this->lionAirRepository->LogoutClient();  // logout
            return $response_search;  // callback data
        }
        
        
        
    }
