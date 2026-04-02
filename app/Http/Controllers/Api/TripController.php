<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusSchedule;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function startTrip(Request $request)
    {
        $request->validate([
            'bus_id' => 'required',
            'route_id' => 'required',
        ]);

        $trip = Trip::create([
            'driver_id' => auth()->id(),
            'bus_id' => $request->bus_id,
            'route_id' => $request->route_id,
            'status' => 'on_way',
        ]);

        // বাসের স্ট্যাটাস 'active' করে দেওয়া
        Bus::where('id', $request->bus_id)->update(['status' => 'active']);

        return response()->json(['message' => 'Trip started!', 'trip_id' => $trip->id]);
    }

    public function updateLocation(Request $request, $tripId)
    {
        $trip = Trip::find($tripId);
        if ($trip) {
            $trip->update([
                'current_lat' => $request->lat,
                'current_lng' => $request->lng,
            ]);
            return response()->json(['status' => 'Location Updated']);
        }
    }

    public function endTrip($id)
    {
        $trip = Trip::where('id', $id)->where('driver_id', auth()->id())->first();

        if ($trip) {
            $trip->update(['status' => 'completed']);
            // বাসকে আবার ফ্রি (idle) করে দেওয়া
            \App\Models\Bus::where('id', $trip->bus_id)->update(['status' => 'idle']);

            return response()->json(['message' => 'Trip ended successfully!']);
        }

        return response()->json(['message' => 'Trip not found!'], 404);
    }

    // প্যাসেঞ্জারদের জন্য একটিভ ট্রিপ দেখা
    public function getActiveTrips()
    {
        // শুধু সেই ট্রিপগুলো দেখাবে যেগুলো এখনো চলছে (on_way)
        $trips = Trip::with(['bus', 'route', 'driver'])
            ->where('status', 'on_way')
            ->get();

        return response()->json($trips);
    }

    public function passengerCheckIn($id)
    {
        $trip = Trip::find($id);

        if (!$trip || $trip->status !== 'on_way') {
            return response()->json(['message' => 'এই বাসটি এখন ট্রিপে নেই।'], 404);
        }

        // বাসের সিট খালি আছে কি না চেক করা (ঐচ্ছিক কিন্তু প্রফেশনাল)
        if ($trip->passenger_count >= $trip->bus->capacity) {
            return response()->json(['message' => 'দুঃখিত, বাসটি পূর্ণ।'], 400);
        }

        // কাউন্ট ১ বাড়ানো
        $trip->increment('passenger_count');

        return response()->json([
            'message' => 'চেক-ইন সফল হয়েছে!',
            'current_passengers' => $trip->passenger_count
        ]);
    }

    public function trackBus($id)
    {
        $trip = Trip::with(['bus', 'driver', 'route'])
            ->where('id', $id)
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'ট্রিপ পাওয়া যায়নি।'], 404);
        }

        return response()->json([
            'bus_number' => $trip->bus->bus_number,
            'lat' => $trip->current_lat,
            'lng' => $trip->current_lng,
            'passengers' => $trip->passenger_count,
            'route' => $trip->route->route_name,
        ]);
    }

    public function getSchedules()
    {
        // আজকের দিনের নাম অনুযায়ী শিডিউল ফিল্টার করা
        $today = now()->format('l');

        $schedules = BusSchedule::with(['bus', 'route', 'driver'])
            ->where('is_active', true)
            ->whereJsonContains('days_of_week', $today)
            ->orderBy('departure_time', 'asc')
            ->get();

        return response()->json($schedules);
    }
}
