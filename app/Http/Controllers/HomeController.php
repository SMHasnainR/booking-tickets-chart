<?php

namespace App\Http\Controllers;

use App\Models\BookedSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function index(){

        $bookedSeats = BookedSeat::all()->pluck('booked_seats')->toArray();

        return view('welcome', compact('bookedSeats'));
    }

    public function bookSeat(Request $request): \Illuminate\Http\JsonResponse
    {

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'booked_seats' => 'required',
            // Add more validation rules as needed
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new booked seat
        foreach($request->input('booked_seats') as $seatNumber){
            $bookedSeat = new BookedSeat();
            $bookedSeat->booked_seats = $seatNumber;
            $bookedSeat->save();
        }

        // You can return a response as needed
        return response()->json(['message' => 'Seat booked successfully'], 200);
    }

}
