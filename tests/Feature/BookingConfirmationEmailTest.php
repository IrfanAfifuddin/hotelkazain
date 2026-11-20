<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class BookingConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::create([
            'room_number' => '101',
            'room_type' => 'King',
            'room_status' => 'Available',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);
    }

    public function test_admin_booking_creation_sends_confirmation_email_to_guest(): void
    {
        Mail::fake();

        $admin = $this->createUser('Admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.pemesanan.store'), $this->bookingPayload());

        $response->assertCreated();

        Mail::assertSent(
            BookingConfirmationMail::class,
            fn (BookingConfirmationMail $mail): bool => $mail->hasTo('tamu@gmail.com'),
        );

        $this->assertNotNull(Booking::query()->firstOrFail()->booking_code_sent_at);
    }

    public function test_receptionist_booking_creation_sends_confirmation_email_to_guest(): void
    {
        Mail::fake();

        $receptionist = $this->createUser('Receptionist');

        $response = $this->actingAs($receptionist)
            ->postJson(route('receptionist.pemesanan.store'), $this->bookingPayload());

        $response->assertCreated();

        Mail::assertSent(
            BookingConfirmationMail::class,
            fn (BookingConfirmationMail $mail): bool => $mail->hasTo('tamu@gmail.com'),
        );
    }

    public function test_admin_booking_form_submission_sends_confirmation_email_and_redirects(): void
    {
        Mail::fake();

        $admin = $this->createUser('Admin');

        $response = $this->actingAs($admin)
            ->post(route('admin.pemesanan.store'), $this->bookingPayload());

        $response->assertRedirect(route('admin.pemesanan.index'));

        Mail::assertSent(
            BookingConfirmationMail::class,
            fn (BookingConfirmationMail $mail): bool => $mail->hasTo('tamu@gmail.com'),
        );
    }

    public function test_booking_confirmation_mail_renders_booking_details(): void
    {
        Mail::fake();

        $admin = $this->createUser('Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.pemesanan.store'), $this->bookingPayload())
            ->assertCreated();

        $booking = Booking::query()->with(['customer', 'rooms'])->firstOrFail();
        $html = (new BookingConfirmationMail($booking))->render();

        $this->assertStringContainsString($booking->booking_code, $html);
        $this->assertStringContainsString($booking->customer->customer_name, $html);
        $this->assertStringContainsString($booking->customer->phone_number, $html);
    }

    public function test_booking_creation_succeeds_and_logs_when_confirmation_email_fails(): void
    {
        Log::spy();
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP connection failed'));

        $admin = $this->createUser('Admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.pemesanan.store'), $this->bookingPayload());

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseHas('customers', ['email' => 'tamu@gmail.com']);
        $this->assertNull(Booking::query()->firstOrFail()->booking_code_sent_at);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'SMTP connection failed'
                && $context['email'] === 'tamu@gmail.com');
    }

    private function createUser(string $role): User
    {
        return User::create([
            'user_name' => $role.' Test',
            'username' => strtolower($role).'_test',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(): array
    {
        return [
            'customer_name' => 'Tamu Email',
            'phone_number' => '081234567890',
            'email' => 'tamu@gmail.com',
            'room_ids' => [$this->room->id],
            'booking_type' => 'Full Day',
            'check_in_time' => now('Asia/Jakarta')->addDay()->setTime(14, 0)->format('Y-m-d H:i:s'),
            'booking_status' => 'Confirmed',
            'payment_method' => 'Cash',
        ];
    }
}
