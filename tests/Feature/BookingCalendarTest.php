<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private Room $room1;
    private Room $room2;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'user_name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $this->receptionist = User::create([
            'user_name' => 'Receptionist Test',
            'username' => 'receptionist_test',
            'password' => bcrypt('password'),
            'role' => 'Receptionist',
        ]);

        $this->room1 = Room::create([
            'room_number' => '101',
            'room_type' => 'King',
            'room_status' => 'Available',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);

        $this->room2 = Room::create([
            'room_number' => '102',
            'room_type' => 'Twin',
            'room_status' => 'Available',
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

    public function test_bookings_page_shows_calendar_component_and_payment_columns(): void
    {
        Booking::create([
            'room_id' => $this->room1->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-CAL-001',
            'booking_type' => 'Full Day',
            'check_in_time' => now(),
            'check_out_time' => now()->addDay(),
            'booking_status' => 'Check-in',
            'payment_method' => 'QRIS',
            'payment_status' => 'Paid',
            'payment_time' => now(),
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pemesanan.index'));

        $response->assertOk();
        // Komponen Kalender
        $response->assertSee('Kalender Pemesanan Kamar');
        $response->assertSee('1 Pemesanan');
        $response->assertSee('2 Pemesanan');
        $response->assertSee('3 Pemesanan');
        $response->assertSee('id="calendarDaysGrid"', false);
        $response->assertSee('id="selectedDateBookingsList"', false);

        // Kolom Pembayaran di Tabel
        $response->assertSee('Pembayaran');
        $response->assertSee('QRIS');
        $response->assertSee('Paid');
        $response->assertSee('KZN-CAL-001');
    }

    public function test_cancelled_bookings_are_excluded_from_calendar_data(): void
    {
        // Booking Aktif
        $activeBooking = Booking::create([
            'room_id' => $this->room1->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-ACTIVE',
            'booking_type' => 'Full Day',
            'check_in_time' => now(),
            'check_out_time' => now()->addDay(),
            'booking_status' => 'Confirmed',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        // Booking Dibatalkan
        $cancelledBooking = Booking::create([
            'room_id' => $this->room2->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-CANCELLED',
            'booking_type' => 'Full Day',
            'check_in_time' => now(),
            'check_out_time' => now()->addDay(),
            'booking_status' => 'Cancelled',
            'payment_method' => 'Cash',
            'payment_status' => 'Failed',
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pemesanan.index'));

        $response->assertOk();

        /** @var \Illuminate\Support\Collection $calendarBookings */
        $calendarBookings = $response->viewData('calendarBookings');

        $this->assertTrue($calendarBookings->contains('booking_code', 'KZN-ACTIVE'));
        $this->assertFalse($calendarBookings->contains('booking_code', 'KZN-CANCELLED'));
    }

    public function test_overnight_booking_dates_are_properly_mapped_for_calendar(): void
    {
        $checkIn = Carbon::create(2026, 10, 20, 14, 0, 0);
        $checkOut = Carbon::create(2026, 10, 22, 12, 0, 0); // 2 malam melewati tengah malam

        $booking = Booking::create([
            'room_id' => $this->room1->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-MULTI-NIGHT',
            'booking_type' => 'Full Day',
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'booking_status' => 'Confirmed',
            'payment_method' => 'Virtual Account',
            'payment_status' => 'Paid',
            'total_price' => 700000,
            'source' => 'Website',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pemesanan.index'));
        $response->assertOk();

        /** @var \Illuminate\Support\Collection $calendarBookings */
        $calendarBookings = $response->viewData('calendarBookings');
        $item = $calendarBookings->firstWhere('booking_code', 'KZN-MULTI-NIGHT');

        $this->assertNotNull($item);
        $this->assertEquals('2026-10-20', $item['check_in_date']);
        $this->assertEquals('2026-10-22', $item['check_out_date']);
        $this->assertEquals('Virtual Account', $item['payment_method']);
    }

    public function test_receptionist_can_also_view_calendar_and_payment_data(): void
    {
        $response = $this->actingAs($this->receptionist)->get(route('receptionist.pemesanan.index'));

        $response->assertOk();
        $response->assertSee('Kalender Pemesanan Kamar');
        $response->assertSee('Daftar Seluruh Pemesanan');
        $response->assertSee('Pembayaran');
    }
}
