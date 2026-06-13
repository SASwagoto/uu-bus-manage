<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusSchedule;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function startTrip(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'route_id' => 'required|exists:routes,id',
            'direction' => 'required|in:up,down', // 💡 ১. direction ভ্যালিডেশন যোগ করা হলো
        ]);

        $driverId = auth()->id();

        // ভ্যালিডেশন: ড্রাইভার অলরেডি কোনো রানিং ট্রিপে আছে কি না চেক করা
        $activeTrip = Trip::where('driver_id', $driverId)
            ->where('status', 'on_way')
            ->first();

        if ($activeTrip) {
            return response()->json([
                'success' => false, // ফ্লাটার অ্যাপের কন্ডিশন ম্যাচ করার জন্য
                'error' => 'ACTIVE_TRIP_EXISTS',
                'message' => 'আপনার একটি ট্রিপ অলরেডি রানিং আছে। সেটি শেষ না করে নতুন ট্রিপ শুরু করতে পারবেন না।',
                'trip_id' => $activeTrip->id
            ], 400);
        }

        // ট্রিপ তৈরি
        $trip = Trip::create([
            'driver_id' => $driverId,
            'bus_id' => $request->bus_id,
            'route_id' => $request->route_id,
            'direction' => $request->direction, // 💡 ২. ডাটাবেজে ডিরেকশন সেভ করা হলো
            'status' => 'on_way',
        ]);

        // বাসের স্ট্যাটাস 'active' করে দেওয়া
        Bus::where('id', $request->bus_id)->update(['status' => 'active']);

        return response()->json([
            'success' => true, // 💡 ৩. ফ্লাটার অ্যাপে success রেসপন্স হ্যান্ডেল করার জন্য
            'message' => 'Trip started successfully!',
            'trip_id' => $trip->id
        ], 201);
    }

    public function updateLocation(Request $request, $id) // 💡 {id} এর সাথে মিলিয়ে $id করা হলো
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        // ভ্যালিডেশন: ট্রিপটি একটিভ এবং এটি এই লগইন করা ড্রাইভারেরই কি না
        $trip = Trip::with(['bus', 'route'])
            ->where('id', $id) // 💡 এখানে $id ব্যবহার করা হয়েছে
            ->where('driver_id', auth()->id())
            ->where('status', 'on_way')
            ->first();

        if (!$trip) {
            return response()->json([
                'error' => 'INVALID_TRIP',
                'message' => 'লোকেশন আপডেটের জন্য কোনো অ্যাক্টিভ ট্রিপ পাওয়া যায়নি।'
            ], 403);
        }

        // ডাটাবেজে লাইভ লোকেশন আপডেট করা
        $trip->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
        ]);

        // আপডেট হওয়া ফ্রেশ ডাটা রিটার্ন
        return response()->json([
            'status' => 'Location Updated',
            'trip' => [
                'id' => $trip->id,
                'passenger_count' => $trip->passenger_count,
                'bus_number' => $trip->bus ? $trip->bus->bus_number : 'N/A',
                'route_name' => $trip->route ? $trip->route->route_name : 'N/A',
                'lat' => $trip->current_lat,
                'lng' => $trip->current_lng,
            ]
        ], 200);
    }

    public function endTrip($id)
    {
        // ১. ট্রিপটি খুঁজে বের করা
        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'ট্রিপটি খুঁজে পাওয়া যায়নি।'
            ], 404);
        }

        // ট্রিপ অলরেডি শেষ হয়ে থাকলে আর কিছু করার দরকার নেই
        if ($trip->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'ট্রিপটি ইতিমধ্যে শেষ হয়েছে।'
            ]);
        }

        // 🎯 ডাটাবেজ ট্রানজেকশন ব্যবহার করা সেফ, যাতে একটা ফেইল করলে অন্যটা রোলব্যাক হয়
        DB::beginTransaction();

        try {
            // 🔴 [ম্যাজিক পার্ট]: ওই ট্রিপের যত একটিভ প্যাসেঞ্জার আছে, সবাইকে এক ক্লিকে অটো চেক-আউট করা
            DB::table('trip_passenger')
                ->where('trip_id', $id)
                ->where('status', 'checked_in')
                ->update([
                    'status' => 'checked_out',
                    'checked_out_at' => Carbon::now()
                ]);

            // ২. ট্রিপের কারেন্ট স্ট্যাটাস এবং প্যাসেঞ্জার কাউন্ট রিসেট করে ট্রিপ শেষ করা
            $trip->status = 'completed';
            $trip->passenger_count = 0; // ট্রিপ শেষ, তাই বাসে এখন আর কোনো যাত্রী নেই
            $trip->end_time = Carbon::now();
            $trip->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ট্রিপ সফলভাবে সমাপ্ত হয়েছে এবং সকল যাত্রীকে অটোমেটিক চেক-আউট করা হয়েছে।'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ট্রিপ শেষ করতে সমস্যা হয়েছে: ' . $e->getMessage()
            ], 500);
        }
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
        $user = Auth::user(); // লগইন করা প্যাসেঞ্জার
        $trip = Trip::with('bus')->find($id);

        if (!$trip || $trip->status !== 'on_way') {
            return response()->json(['success' => false, 'message' => 'এই বাসটি এখন ট্রিপে নেই।'], 404);
        }

        // 🔴 [ব্রেকিং রুলস]: ইউজার অলরেডি অন্য কোনো ট্রিপে (বা এই ট্রিপে) চেক-ইন অবস্থায় আছে কিনা চেক
        $activeCheckIn = DB::table('trip_passenger')
            ->where('user_id', $user->id)
            ->where('status', 'checked_in')
            ->first();

        if ($activeCheckIn) {
            if ($activeCheckIn->trip_id == $id) {
                return response()->json(['success' => false, 'message' => 'আপনি ইতিমধ্যে এই বাসে চেক-ইন করেছেন!'], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'আপনি ইতিমধ্যে অন্য একটি বাসে চেক-ইন অবস্থায় আছেন। আগে সেটি থেকে চেক-আউট করুন!'
                ], 400);
            }
        }

        // পিভট টেবিলে এন্ট্রি তৈরি করা
        $trip->passengers()->attach($user->id, [
            'status' => 'checked_in',
            'checked_in_at' => Carbon::now()
        ]);

        // ট্রিপের মেইন প্যাসেঞ্জার কাউন্ট ১ বাড়ানো
        $trip->increment('passenger_count');

        $isOverloaded = $trip->bus && $trip->passenger_count > $trip->bus->capacity;
        $message = $isOverloaded ? 'চেক-ইন সফল! বাসে সিট খালি নেই, দাঁড়িয়ে যেতে হতে পারে।' : 'চেক-ইন সফল হয়েছে!';

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_overloaded' => $isOverloaded,
            'current_passengers' => (int)$trip->passenger_count
        ], 200);
    }

    public function passengerCheckOut($id)
    {
        $user = Auth::user();
        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json(['success' => false, 'message' => 'ট্রিপটি খুঁজে পাওয়া যায়নি।'], 404);
        }

        // পিভট টেবিলে একটিভ চেক-ইন রেকর্ডটি খোঁজা
        $pivotRecord = DB::table('trip_passenger')
            ->where('trip_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'checked_in')
            ->first();

        if (!$pivotRecord) {
            return response()->json(['success' => false, 'message' => 'আপনি এই বাসে চেক-ইন করা অবস্থায় নেই।'], 400);
        }

        // পিভট টেবিল আপডেট (স্ট্যাটাস পরিবর্তন)
        DB::table('trip_passenger')
            ->where('id', $pivotRecord->id)
            ->update([
                'status' => 'checked_out',
                'checked_out_at' => Carbon::now()
            ]);

        // সেফটি গার্ড অনুযায়ী মেইন কাউন্ট কমানো
        if ($trip->passenger_count > 0) {
            $trip->decrement('passenger_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'চেক-আউট সফল হয়েছে! নিরাপদ যাতায়াতের জন্য ধন্যবাদ।',
            'current_passengers' => (int)$trip->passenger_count
        ], 200);
    }

    public function checkCurrentStatus($trip_id)
    {
        $user = Auth::user();

        $activeCheckIn = DB::table('trip_passenger')
            ->where('user_id', $user->id)
            ->where('status', 'checked_in')
            ->first();

        return response()->json([
            'success' => true,
            'is_checked_in_here' => $activeCheckIn && $activeCheckIn->trip_id == $trip_id, // এই বাসে ইন আছে কিনা
            'has_active_check_in_elsewhere' => $activeCheckIn && $activeCheckIn->trip_id != $trip_id, // অন্য বাসে ইন আছে কিনা
        ]);
    }

    public function trackBus($id)
    {
        $trip = Trip::with(['bus', 'driver', 'route'])
            ->where('id', $id)
            ->first();

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'ট্রিপ পাওয়া যায়নি।'
            ], 404);
        }

        // 💡 ডাটাবেজে ল্যাট-লং নাল (Null) থাকলে ঢাকা সিটির একটা ডিফল্ট পজিশন সেট করে দেওয়া (সেফটি গার্ড)
        $latitude = !empty($trip->current_lat) ? $trip->current_lat : '23.8103';
        $longitude = !empty($trip->current_lng) ? $trip->current_lng : '90.4125';

        return response()->json([
            'success' => true,
            'trip' => [
                'id' => (int)$trip->id,
                'current_latitude' => strval($latitude),
                'current_longitude' => strval($longitude),
                'passenger_count' => (int)($trip->passenger_count ?? 0),
                'direction' => strval($trip->direction ?? 'up'),
                'bus' => $trip->bus ? [
                    'bus_number' => $trip->bus->bus_number,
                ] : [
                    'bus_number' => 'N/A'
                ],
                'route' => $trip->route ? [
                    'route_name' => $trip->route->route_name,
                ] : [
                    'route_name' => 'N/A'
                ],
            ]
        ], 200);
    }

    public function getSchedules()
    {
        // আজকের দিনের নাম (যেমন: Saturday, Sunday)
        $today = now()->format('l');
        $user = auth('sanctum')->user();

        // কুয়েরি: শুধু সচল শিডিউল এবং যেগুলোর বাস ও রুট ডাটাবেজে এক্সিস্ট করে
        $query = BusSchedule::with(['bus', 'route', 'driver'])
            ->where('is_active', true)
            ->whereHas('bus')   // নিশ্চিত করা বাসের ডাটা আছে
            ->whereHas('route') // নিশ্চিত করা রুটের ডাটা আছে
            ->whereJsonContains('days_of_week', $today);

        // ড্রাইভার হলে শুধু তার নিজের শিডিউল ফিল্টার করা
        if ($user && $user->role === 'driver') {
            $query->where('driver_id', $user->id);
        }

        $schedules = $query->orderBy('departure_time', 'asc')->get();

        $grouped = $schedules->groupBy('direction');

        return response()->json([
            'up_trips' => $grouped->get('up', collect())->values(),
            'down_trips' => $grouped->get('down', collect())->values(),
        ], 200);
    }

    public function getCurrentActiveTrip()
    {
        $user = auth('sanctum')->user();

        if (!$user || $user->role !== 'driver') {
            return response()->json(['has_active_trip' => false, 'message' => 'Unauthorized'], 403);
        }

        // 💡 যেহেতু স্ট্যাটাস 'on_way' হয়, তাই সুনির্দিষ্টভাবে কুয়েরি করছি
        $activeTrip = Trip::where('driver_id', $user->id)
            ->where('status', 'on_way')
            ->with(['bus', 'route'])
            ->first();

        if ($activeTrip) {
            return response()->json([
                'has_active_trip' => true,
                'trip' => $activeTrip
            ]);
        }

        return response()->json([
            'has_active_trip' => false
        ]);
    }
}
