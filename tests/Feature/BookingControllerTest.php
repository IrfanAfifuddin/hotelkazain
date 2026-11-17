<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingControllerTest extends TestCase
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

    public static function fullDayCheckInCases(): array
    {
        return [
            'check-in dini hari (sebelum pukul 12) => checkout hari yang sama' => ['2027-01-18 00:40:00', '2027-01-18 12:00:00'],
            'check-in tepat pukul 12 => checkout keesokan hari' => ['2027-01-18 12:00:00', '2027-01-19 12:00:00'],
            'check-in sore (di atas pukul 12) => checkout keesokan hari' => ['2027-01-18 14:30:00', '2027-01-19 12:00:00'],
        ];
    }

    #[DataProvider('fullDayCheckInCases')]
    public function test_full_day_checkout_follows_noon_rule_from_check_in_time(string $checkInTime, string $expectedCheckOutTime): void
    {
        $payload = [
            'customer_name' => 'Ahmad Dani',
            'phone_number' => '081987654321',
            'email' => 'ahmad@example.com',
            'room_ids' => [$this->room->id],
            'booking_type' => 'Full Day',
            'check_in_time' => $checkInTime,
            'booking_status' => 'Check-in',
            'payment_method' => 'Cash',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'booking_type' => 'Full Day',
            'check_in_time' => $checkInTime,
            'check_out_time' => $expectedCheckOutTime,
        ]);
    }
}
