<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'test-server-key');
        config()->set('services.midtrans.is_production', false);
    }

    public function test_customer_can_fetch_room_availability(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));

        $room = $this->createRoom('101', 'King');

        $response = $this->getJson('/availability?date=2026-10-16');

        $response->assertOk()
            ->assertJsonPath('date', '2026-10-16')
            ->assertJsonPath('data.0.room_id', $room->id)
            ->assertJsonPath('data.0.room_number', '101')
            ->assertJsonPath('data.0.room_status', 'Available')
            ->assertJsonPath('summary.available_rooms', 1);
    }

    public function test_customer_booking_returns_201_and_creates_pending_reservation_with_expiration(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));

        $this->fakeMidtransPayment();
        $room = $this->createRoom('102', 'Twin');

        $response = $this->postJson('/bookings', $this->bookingPayload($room, [
            'booking_type' => 'Full Day',
            'check_in_date' => '2026-10-16',
            'check_in_time' => '14:00',
            'duration_hours' => 24,
            'payment_status' => 'Paid',
            'booking_status' => 'Confirmed',
            'total_price' => 1,
            'source' => 'Offline',
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.payment.redirect_url', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token')
            ->assertJsonMissingPath('data.booking')
            ->assertJsonMissingPath('data.payment.order_id');

        $booking = Booking::query()->where('customer_id', Customer::query()
            ->where('email', 'andi@example.com')
            ->value('id'))->firstOrFail();

        $this->assertMatchesRegularExpression('/^KZN-\d{6}$/', $booking->booking_code);
        $this->assertStringStartsWith('KZN-PAY-', $booking->midtrans_order_id);
        $this->assertNotSame($booking->booking_code, $booking->midtrans_order_id);
        $this->assertSame('2026-10-17 12:00:00', $booking->check_out_time->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-15 10:20:00', $booking->payment_expires_at->format('Y-m-d H:i:s'));

        $this->assertDatabaseHas('customers', [
            'email' => 'andi@example.com',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'Pending',
            'payment_status' => 'Pending',
            'total_price' => 300000,
            'source' => 'Website',
            'midtrans_snap_token' => 'snap-token',
        ]);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['gross_amount'] === 300000
            && $request['enabled_payments'] === ['bank_transfer']
            && $request['transaction_details']['order_id'] === $booking->midtrans_order_id);
    }

    public function test_customer_cannot_create_booking_with_qris(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));
        Http::preventStrayRequests();
        $room = $this->createRoom('109', 'King');

        $response = $this->postJson('/bookings', $this->bookingPayload($room, [
            'payment_method' => 'QRIS',
        ]));

        $response->assertUnprocessable()
            ->assertJsonPath('errors.payment_method.0', 'Customer payments are only available by bank transfer / Virtual Account.');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_conflict_is_rejected(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));

        $room = $this->createRoom('103', 'King');
        $customer = Customer::create([
            'customer_name' => 'Existing Customer',
            'phone_number' => '081234567899',
            'email' => 'existing@example.com',
        ]);

        Booking::create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-000001',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-10-16 14:00:00',
            'check_out_time' => '2026-10-17 12:00:00',
            'booking_status' => 'Confirmed',
            'payment_method' => 'Virtual Account',
            'payment_status' => 'Paid',
            'total_price' => 350000,
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_code' => 'KZN-000001',
        ]);

        $response = $this->postJson('/bookings', $this->bookingPayload($room, [
            'customer_name' => 'Budi',
            'phone_number' => '081234567891',
            'email' => 'budi@example.com',
            'booking_type' => 'Short Time',
            'check_in_date' => '2026-10-16',
            'check_in_time' => '15:00',
            'duration_hours' => 5,
        ]));

        $response->assertStatus(422)
            ->assertJsonPath('errors.room_id.0', 'The selected room is not available for that time slot.');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseMissing('customers', ['email' => 'budi@example.com']);
    }

    public function test_expired_pending_booking_does_not_block_room_availability(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));

        $room = $this->createRoom('104', 'Twin');
        $confirmedRoom = $this->createRoom('106', 'King');
        $customer = Customer::create([
            'customer_name' => 'Expired Guest',
            'phone_number' => '081234567899',
            'email' => 'expired@example.com',
        ]);

        Booking::create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-000002',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-10-16 14:00:00',
            'check_out_time' => '2026-10-17 12:00:00',
            'booking_status' => 'Pending',
            'payment_method' => 'QRIS',
            'payment_status' => 'Pending',
            'payment_expires_at' => now()->subMinute(),
            'total_price' => 300000,
        ]);

        Booking::create([
            'room_id' => $confirmedRoom->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-000003',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-10-16 14:00:00',
            'check_out_time' => '2026-10-17 12:00:00',
            'booking_status' => 'Confirmed',
            'payment_method' => 'QRIS',
            'payment_status' => 'Paid',
            'total_price' => 350000,
        ]);

        $response = $this->getJson('/availability?date=2026-10-16');

        $response->assertOk()
            ->assertJsonPath('data.0.room_status', 'Available')
            ->assertJsonPath('data.0.is_available', true)
            ->assertJsonPath('data.1.room_status', 'Booked')
            ->assertJsonPath('data.1.is_available', false)
            ->assertJsonPath('summary.available_rooms', 1);
    }

    public function test_short_time_booking_rejects_duration_other_than_five_or_eight_hours(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));

        $room = $this->createRoom('105', 'King');

        $response = $this->postJson('/bookings', $this->bookingPayload($room, [
            'booking_type' => 'Short Time',
            'check_in_date' => '2026-10-16',
            'check_in_time' => '14:00',
            'duration_hours' => 7,
        ]));

        $response->assertStatus(422)
            ->assertJsonPath('errors.duration_hours.0', 'Short Time bookings must be 5 or 8 hours.');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_rejected_midtrans_request_cancels_booking_and_releases_room(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'message' => 'Invalid server key',
            ], 401),
        ]);
        $room = $this->createRoom('107', 'King');

        $response = $this->postJson('/bookings', $this->bookingPayload($room));

        $response->assertServiceUnavailable()
            ->assertJsonMissingPath('data.booking.booking_code');
        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'booking_status' => 'Cancelled',
            'payment_status' => 'Failed',
        ]);
    }

    public function test_ambiguous_midtrans_connection_failure_keeps_temporary_room_hold(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 0));
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::failedConnection(),
        ]);
        $room = $this->createRoom('108', 'King');

        $response = $this->postJson('/bookings', $this->bookingPayload($room));

        $response->assertServiceUnavailable();
        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'booking_status' => 'Pending',
            'payment_status' => 'Pending',
        ]);
    }

    private function fakeMidtransPayment(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token',
            ]),
        ]);
    }

    private function createRoom(string $roomNumber, string $roomType): Room
    {
        return Room::create([
            'room_number' => $roomNumber,
            'room_type' => $roomType,
            'room_status' => 'Available',
            'price_5_hours' => $roomType === 'King' ? 150000 : 130000,
            'price_8_hours' => $roomType === 'King' ? 200000 : 175000,
            'full_day_price' => $roomType === 'King' ? 350000 : 300000,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function bookingPayload(Room $room, array $overrides = []): array
    {
        return array_merge([
            'room_id' => $room->id,
            'customer_name' => 'Andi',
            'phone_number' => '081234567890',
            'email' => 'andi@example.com',
            'booking_type' => 'Full Day',
            'check_in_date' => '2026-10-16',
            'check_in_time' => '14:00',
            'duration_hours' => 24,
            'payment_method' => 'Virtual Account',
            'agree_terms' => true,
        ], $overrides);
    }
}
