<?php

namespace App\Http\Controllers\Airlines;

use App\Http\Controllers\Controller;
use App\Repositories\Airline\Lion\LionAirRepository;
use Illuminate\Http\Request;
use stdClass;

class BookingAirlinesController extends Controller
{

    public $lionAirRepository;
    
    public function __construct(LionAirRepository $lionAirRepository)
    {
       $this->lionAirRepository = $lionAirRepository;
    }
    
    public function lionairapi()
    {
        // login
        // get data search booking
        // create matrix
        $request = new stdClass();
        $request->from_date = date("Y-m-d\TH:i:s", time() + 886400);
        $request->from_code = 'CGK';
        $request->to_code = 'DPS';
        $request->trip_type = 'Oneway';
        $request->adult = 2;
        $request->child = 1;
        $request->infant = 0;
        
        $response_login = $this->lionAirRepository->LoginClient();
        sleep(2);
        $response_search = $this->lionAirRepository->SearchFlight($request);
        echo $response_search;
    }
}
