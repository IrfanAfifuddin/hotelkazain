<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_fetch_room_availability(): void
    {
        Room::create([
            'room_number' => '101',
            'room_type' => 'King',
            'room_status' => 'Available',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);

        $response = $this->getJson('/customer/availability?date=2026-10-15');

        $response->assertOk()
            ->assertJsonPath('data.0.room_number', '101')
            ->assertJsonPath('data.0.room_status', 'Available');
    }

    public function test_customer_can_create_booking_without_account(): void
    {
        Room::create([
            'room_number' => '102',
            'room_type' => 'Twin',
            'room_status' => 'Available',
            'price_5_hours' => 130000,
            'price_8_hours' => 175000,
            'full_day_price' => 300000,
        ]);

        $response = $this->postJson('/customer/bookings', [
            'room_id' => 1,
            'customer_name' => 'Andi',
            'phone_number' => '081234567890',
            'email' => 'andi@example.com',
            'booking_type' => 'Full Day',
            'check_in_date' => '2026-10-15',
            'check_in_time' => '14:00',
            'duration_hours' => 24,
            'payment_method' => 'Virtual Account',
            'payment_status' => 'Pending',
            'agree_terms' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.customer.email', 'andi@example.com');

        $this->assertMatchesRegularExpression('/^KZN-\d{6}$/', $response->json('data.booking.booking_code'));

        $this->assertDatabaseHas('customers', [
            'email' => 'andi@example.com',
        ]);
    }

    public function test_booking_conflict_is_rejected(): void
    {
        Room::create([
            'room_number' => '103',
            'room_type' => 'King',
            'room_status' => 'Available',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);

        $customer = \App\Models\Customer::create([
            'customer_name' => 'Existing Customer',
            'phone_number' => '081234567899',
            'email' => 'existing@example.com',
        ]);

        Booking::create([
            'room_id' => 1,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-000001',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-10-15 14:00:00',
            'check_out_time' => '2026-10-16 12:00:00',
            'booking_status' => 'Confirmed',
            'payment_method' => 'Virtual Account',
            'payment_status' => 'Paid',
            'total_price' => 350000,
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_code' => 'KZN-000001',
        ]);

        $response = $this->postJson('/customer/bookings', [
            'room_id' => 1,
            'customer_name' => 'Budi',
            'phone_number' => '081234567891',
            'email' => 'budi@example.com',
            'booking_type' => 'Full Day',
            'check_in_date' => '2026-10-15',
            'check_in_time' => '15:00',
            'duration_hours' => 24,
            'payment_method' => 'Virtual Account',
            'payment_status' => 'Pending',
            'agree_terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.room_id.0', 'The selected room is not available for that time slot.');
    }
}
