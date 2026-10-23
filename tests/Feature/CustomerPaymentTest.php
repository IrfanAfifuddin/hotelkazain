<?php

namespace Tests\Feature;

use App\Mail\BookingCodeMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\PaymentGatewayException;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'test-server-key');
        config()->set('services.midtrans.is_production', false);
    }

    public function test_invalid_notification_signature_returns_403_without_confirming_or_emailing_booking(): void
    {
        $booking = $this->createPendingBooking();
        Mail::fake();

        $response = $this->postJson('/payments/midtrans/notifications', [
            'order_id' => $booking->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '350000.00',
            'signature_key' => 'invalid-signature',
            'transaction_status' => 'settlement',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'Pending',
            'payment_status' => 'Pending',
        ]);
        Mail::assertNotSent(BookingCodeMail::class);
    }

    public function test_valid_settlement_confirms_booking_and_emails_code_only_once(): void
    {
        $booking = $this->createPendingBooking();
        Mail::fake();
        $notification = $this->notificationFor($booking, 'settlement');

        $response = $this->postJson('/payments/midtrans/notifications', $notification);
        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Paid',
        ]);
        Mail::assertSent(BookingCodeMail::class, fn (BookingCodeMail $mail): bool => $mail->hasTo('andi@example.com'));

        $this->postJson('/payments/midtrans/notifications', $notification)->assertOk();

        Mail::assertSentTimes(BookingCodeMail::class, 1);
        $this->assertNotNull($booking->fresh()->booking_code_sent_at);
    }

    public function test_signed_notification_with_wrong_amount_returns_422_without_confirming_booking(): void
    {
        $booking = $this->createPendingBooking();
        Mail::fake();
        $notification = $this->notificationFor($booking, 'settlement', '1.00');

        $response = $this->postJson('/payments/midtrans/notifications', $notification);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'Pending',
            'payment_status' => 'Pending',
        ]);
        Mail::assertNotSent(BookingCodeMail::class);
    }

    public function test_expiration_notification_releases_pending_booking(): void
    {
        $booking = $this->createPendingBooking();
        $notification = $this->notificationFor($booking, 'expire');

        $response = $this->postJson('/payments/midtrans/notifications', $notification);

        $response->assertOk();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'Expired',
            'payment_status' => 'Expired',
        ]);
    }

    public function test_success_callback_verifies_payment_status_and_emails_booking_code(): void
    {
        $booking = $this->createPendingBooking();
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/'.$booking->midtrans_order_id.'/status' => Http::response([
                'order_id' => $booking->midtrans_order_id,
                'gross_amount' => '350000.00',
                'transaction_status' => 'settlement',
                'transaction_time' => now()->toDateTimeString(),
            ]),
        ]);

        $response = $this->get('/success?order_id='.$booking->midtrans_order_id);

        $response->assertOk()
            ->assertSeeText($booking->booking_code)
            ->assertViewHas('paymentPending', false);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Paid',
        ]);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && str_ends_with($request->url(), '/'.$booking->midtrans_order_id.'/status'));
        Mail::assertSent(BookingCodeMail::class);
    }

    public function test_pending_success_callback_does_not_reveal_booking_code(): void
    {
        $booking = $this->createPendingBooking();
        Http::preventStrayRequests();
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/'.$booking->midtrans_order_id.'/status' => Http::response([
                'order_id' => $booking->midtrans_order_id,
                'gross_amount' => '350000.00',
                'transaction_status' => 'pending',
            ]),
        ]);

        $response = $this->get('/success?order_id='.$booking->midtrans_order_id);

        $response->assertOk()
            ->assertDontSeeText($booking->booking_code)
            ->assertSeeText('Menunggu Pembayaran');
    }

    public function test_payment_page_uses_private_order_reference_without_showing_booking_code(): void
    {
        $booking = $this->createPendingBooking();
        $booking->forceFill([
            'midtrans_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/test-token',
        ])->save();

        $response = $this->get('/payment/'.$booking->midtrans_order_id);

        $response->assertOk()
            ->assertDontSeeText($booking->booking_code)
            ->assertSeeText('Lanjutkan pembayaran di Midtrans');
    }

    public function test_payment_service_rejects_qris_even_when_a_redirect_url_is_cached(): void
    {
        $booking = $this->createPendingBooking();
        $booking->forceFill([
            'payment_method' => 'QRIS',
            'midtrans_snap_token' => 'cached-token',
            'midtrans_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/cached-token',
        ])->save();

        $this->expectException(PaymentGatewayException::class);

        app(PaymentService::class)->createPayment($booking);
    }

    public function test_legacy_booking_code_payment_and_status_routes_are_not_found(): void
    {
        $booking = $this->createPendingBooking();

        $this->get('/payment?booking='.$booking->booking_code)->assertNotFound();
        $this->postJson('/bookings/'.$booking->booking_code.'/payment')->assertNotFound();
        $this->getJson('/bookings/'.$booking->booking_code.'/payment-status')->assertNotFound();
        $this->postJson('/midtrans/notification')->assertNotFound();
    }

    public function test_success_page_rejects_booking_code_as_a_lookup_reference(): void
    {
        $booking = $this->createPendingBooking();

        $response = $this->getJson('/success?booking='.$booking->booking_code);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('order_id')
            ->assertDontSee($booking->booking_code);
    }

    public function test_notification_returns_503_when_midtrans_server_key_is_missing(): void
    {
        $booking = $this->createPendingBooking();
        config()->set('services.midtrans.server_key', null);

        $response = $this->postJson('/payments/midtrans/notifications', [
            'order_id' => $booking->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '350000.00',
            'signature_key' => 'signature',
        ]);

        $response->assertServiceUnavailable();
    }

    private function createPendingBooking(): Booking
    {
        $room = Room::create([
            'room_number' => '201',
            'room_type' => 'King',
            'room_status' => 'Available',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);
        $customer = Customer::create([
            'customer_name' => 'Andi',
            'phone_number' => '081234567890',
            'email' => 'andi@example.com',
        ]);

        return Booking::create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-123456',
            'midtrans_order_id' => 'KZN-PAY-123456',
            'booking_type' => 'Full Day',
            'check_in_time' => now()->addDay()->setTime(14, 0),
            'check_out_time' => now()->addDays(2)->setTime(12, 0),
            'booking_status' => 'Pending',
            'payment_method' => 'Virtual Account',
            'payment_status' => 'Pending',
            'payment_expires_at' => now()->addMinutes(20),
            'midtrans_snap_token' => 'snap-token',
            'midtrans_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token',
            'total_price' => 350000,
            'source' => 'Website',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function notificationFor(Booking $booking, string $transactionStatus, string $grossAmount = '350000.00'): array
    {
        $statusCode = '200';

        return [
            'order_id' => $booking->midtrans_order_id,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => hash(
                'sha512',
                $booking->midtrans_order_id.$statusCode.$grossAmount.'test-server-key',
            ),
            'transaction_status' => $transactionStatus,
        ];
    }
}
