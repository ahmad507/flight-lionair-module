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
    
    public function lionairapi()
    {
        // login
        // get data search booking
        // create matrix
        $response_login = $this->lionAirRepository->LoginClient();
        echo $response_login;
    }
}
