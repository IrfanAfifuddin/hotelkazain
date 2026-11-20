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
            'email' => 'budi@gmail.com',
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
            ->assertSee('Budi Santoso')
            ->assertSee($booking->check_out_time->copy()->shiftTimezone('Asia/Jakarta')->format('d M Y, H:i'))
            ->assertSee('id="summaryCheckout"', false);
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
            'check_out_before' => '2026-10-21 10:00:00',
            'check_out_after' => '2026-10-21 15:00:00',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'check_out_time' => '2026-10-21 15:00:00',
            'total_price' => 300000,
        ]);
        $this->assertSame('2026-10-21 15:00:00', $booking->fresh()->check_out_time->format('Y-m-d H:i:s'));
    }

    public function test_receptionist_can_extend_short_time_booking_and_history_uses_updated_checkout(): void
    {
        $receptionist = User::create([
            'user_name' => 'Receptionist Hotel',
            'username' => 'receptionist_hotel',
            'password' => 'password',
            'role' => 'Receptionist',
        ]);
        $booking = $this->activeBooking();

        $response = $this->actingAs($receptionist)
            ->post(route('receptionist.pemesanan.extend.store', $booking), [
                'extension_type' => 'Short Time',
                'duration_hours' => 5,
                'payment_method' => 'Cash',
            ]);

        $response->assertRedirect(route('receptionist.pemesanan.index'));
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'check_out_time' => '2026-10-21 15:00:00',
            'total_price' => 300000,
        ]);
        $this->assertDatabaseHas('booking_extensions', [
            'booking_id' => $booking->id,
            'check_out_before' => '2026-10-21 10:00:00',
            'check_out_after' => '2026-10-21 15:00:00',
        ]);

        $response = $this->actingAs($receptionist)
            ->get(route('receptionist.pemesanan.index'));

        $response->assertOk()
            ->assertSee('21 Oct 2026 15:00')
            ->assertSee('Extend 1x');

        $this->actingAs($receptionist)
            ->get(route('receptionist.pemesanan.extend.create', $booking))
            ->assertOk()
            ->assertSee('21 Oct 2026, 15:00 WIB');
    }

    public function test_transfer_short_time_extend_stays_pending_without_payment_time(): void
    {
        $booking = $this->activeBooking();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pemesanan.extend.store', $booking), [
                'extension_type' => 'Short Time',
                'duration_hours' => 5,
                'payment_method' => 'Transfer',
            ]);

        $response->assertRedirect(route('admin.pemesanan.index'));
        $this->assertDatabaseHas('booking_extensions', [
            'booking_id' => $booking->id,
            'payment_method' => 'Transfer',
            'payment_status' => 'Pending',
            'payment_time' => null,
            'check_out_after' => '2026-10-21 15:00:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index'));

        $response->assertOk()
            ->assertDontSee('KZN-100001')
            ->assertDontSee('Menunggu');
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
        $booking = Booking::create([
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

        // Attach room via pivot table for multi-room support
        $booking->rooms()->attach($this->room->id, ['price' => 150000]);

        return $booking;
    }
}
