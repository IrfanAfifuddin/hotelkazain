<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_receptionist_see_room_counts_and_status_buttons_for_all_rooms(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        // Create a customer for bookings
        $customer = Customer::create([
            'customer_name' => 'Test Customer',
            'phone_number' => '081234567890',
            'email' => 'test@example.com',
        ]);

        // Create 12 rooms
        foreach (range(1, 12) as $i) {
            $roomNumber = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            Room::create([
                'room_number' => $roomNumber,
                'room_type' => 'King',
                'room_status' => 'Available', // Initial status, will be computed
                'price_5_hours' => 150000,
                'price_8_hours' => 200000,
                'full_day_price' => 350000,
            ]);
        }

        // Create bookings to match expected computed statuses:
        // Rooms 01-08: Available (no active bookings)
        // Rooms 09-11: Occupied (active booking, check-in <= now < check-out)
        // Room 12: Cleaning (recent check-out within 1 hour)

        foreach (range(9, 11) as $i) {
            $roomNumber = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $room = Room::where('room_number', $roomNumber)->first();

            Booking::create([
                'room_id' => $room->id,
                'customer_id' => $customer->id,
                'booking_code' => 'KZN-TEST-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'booking_type' => 'Full Day',
                'check_in_time' => $now->copy()->subHours(2),
                'check_out_time' => $now->copy()->addHours(2),
                'booking_status' => 'Check-in',
                'payment_method' => 'Cash',
                'payment_status' => 'Paid',
                'total_price' => 350000,
                'source' => 'Offline',
            ]);
        }

        // Room 12: Cleaning (check-out was 30 minutes ago)
        $room12 = Room::where('room_number', '12')->first();
        Booking::create([
            'room_id' => $room12->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-TEST-CLEANING',
            'booking_type' => 'Full Day',
            'check_in_time' => $now->copy()->subHours(26),
            'check_out_time' => $now->copy()->subMinutes(30),
            'booking_status' => 'Check-out',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'total_price' => 350000,
            'source' => 'Offline',
        ]);

        $usersAndRoutes = [
            [User::factory()->create(['role' => 'Admin']), 'admin.status-kamar.index'],
            [User::factory()->create(['role' => 'Receptionist']), 'receptionist.status-kamar.index'],
        ];

        foreach ($usersAndRoutes as [$user, $routeName]) {
            $response = $this->actingAs($user)->get(route($routeName));

            $response->assertOk();
            $response->assertSee('12 dari 12 kamar');
            $response->assertSee('aria-label="Tersedia: 8 kamar"', false);
            $response->assertSee('aria-label="Terisi: 3 kamar"', false);
            $response->assertSee('aria-label="Cleaning: 1 kamar"', false);
            $response->assertDontSee('<select name="room_status"', false);

            foreach (range(1, 12) as $roomNumber) {
                $formattedRoomNumber = str_pad((string) $roomNumber, 2, '0', STR_PAD_LEFT);
                $response->assertSee('Kamar '.$formattedRoomNumber);
                $response->assertSee('name="room_status"', false);
                $response->assertSee('value="Available"', false);
                $response->assertSee('value="Occupied"', false);
                $response->assertSee('value="Cleaning"', false);
            }
        }
    }
}
