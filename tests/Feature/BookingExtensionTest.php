<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingExtensionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Room $room;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'user_name' => 'Admin Hotel',
            'username' => 'admin_hotel',
            'password' => 'password',
            'role' => 'Admin',
        ]);
        $this->room = Room::create([
            'room_number' => '101',
            'room_type' => 'King',
            'room_status' => 'Occupied',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);
        $this->customer = Customer::create([
            'customer_name' => 'Budi Santoso',
            'phone_number' => '081234567890',
            'email' => 'budi@example.com',
        ]);
    }

    public function test_admin_can_open_extend_page_for_active_booking(): void
    {
        $booking = $this->activeBooking();

        $this->actingAs($this->admin)
            ->get(route('admin.pemesanan.extend.create', $booking))
            ->assertOk()
            ->assertSee('Extend Pemesanan')
            ->assertSee($booking->booking_code)
            ->assertSee('Budi Santoso');
    }

    public function test_cash_short_time_extend_updates_checkout_and_creates_paid_history(): void
    {
        $booking = $this->activeBooking();

        $this->actingAs($this->admin)
            ->post(route('admin.pemesanan.extend.store', $booking), [
                'extension_type' => 'Short Time',
                'duration_hours' => 5,
                'payment_method' => 'Cash',
            ])
            ->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('booking_extensions', [
            'booking_id' => $booking->id,
            'extension_type' => 'Short Time',
            'duration_hours' => 5,
            'amount' => 150000,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
        ]);
        $this->assertSame('2026-10-21 15:00:00', $booking->fresh()->check_out_time->format('Y-m-d H:i:s'));
    }

    public function test_extend_is_rejected_when_it_overlaps_next_booking(): void
    {
        $booking = $this->activeBooking();
        Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-999999',
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => Carbon::parse('2026-10-21 14:00:00', 'Asia/Jakarta'),
            'check_out_time' => Carbon::parse('2026-10-21 19:00:00', 'Asia/Jakarta'),
            'booking_status' => 'Confirmed',
            'total_price' => 150000,
            'source' => 'Offline',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.pemesanan.extend.create', $booking))
            ->post(route('admin.pemesanan.extend.store', $booking), [
                'extension_type' => 'Short Time',
                'duration_hours' => 5,
                'payment_method' => 'Cash',
            ])
            ->assertRedirect(route('admin.pemesanan.extend.create', $booking))
            ->assertSessionHasErrors('duration_hours');

        $this->assertDatabaseCount('booking_extensions', 0);
        $this->assertSame('2026-10-21 10:00:00', $booking->fresh()->check_out_time->format('Y-m-d H:i:s'));
    }

    private function activeBooking(): Booking
    {
        return Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-100001',
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => Carbon::parse('2026-10-21 05:00:00', 'Asia/Jakarta'),
            'check_out_time' => Carbon::parse('2026-10-21 10:00:00', 'Asia/Jakarta'),
            'booking_status' => 'Check-in',
            'total_price' => 150000,
            'source' => 'Offline',
        ]);
    }
}
