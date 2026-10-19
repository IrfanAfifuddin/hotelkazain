<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCheckInValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'user_name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $this->room = Room::create([
            'room_number' => '101',
            'room_type' => 'King',
            'room_status' => 'Available',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);
    }

    public function test_past_check_in_is_rejected_by_booking_endpoint(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.pemesanan.store'), [
            'customer_name' => 'Tamu Lama',
            'phone_number' => '081234567890',
            'room_id' => $this->room->id,
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => now('Asia/Jakarta')->subDay()->format('Y-m-d H:i'),
            'booking_status' => 'Pending',
            'payment_method' => 'Cash',
            'payment_status' => 'Pending',
            'source' => 'Offline',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['check_in_time']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_current_and_future_check_in_are_accepted(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.pemesanan.store'), [
            'customer_name' => 'Tamu Baru',
            'phone_number' => '081234567891',
            'room_id' => $this->room->id,
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => now('Asia/Jakarta')->addMinutes(10)->format('Y-m-d H:i'),
            'booking_status' => 'Pending',
            'payment_method' => 'Cash',
            'payment_status' => 'Pending',
            'source' => 'Offline',
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('bookings', 1);
    }
}
