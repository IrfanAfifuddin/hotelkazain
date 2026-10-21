<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private Room $room1;
    private Room $room2;

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
            'room_status' => 'Occupied',
            'price_5_hours' => 150000,
            'price_8_hours' => 200000,
            'full_day_price' => 350000,
        ]);
    }

    public function test_admin_dashboard_displays_tambah_pemesanan_button_and_modal(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Tambah Pemesanan');
        $response->assertSee('id="createBookingModal"', false);
        $response->assertSee('id="modal_customer_name"', false);
        $response->assertSee('id="modal_room_id"', false);
        $response->assertSee('id="modal_booking_type"', false);
        $response->assertSee('Kamar 101 — King Bed (Tersedia)');
        // Kamar 102 yang Occupied tidak boleh ada di dropdown kamar tersedia
        $response->assertDontSee('Kamar 102 — Twin Bed (Tersedia)');
    }

    public function test_admin_can_create_booking_via_ajax_from_dashboard(): void
    {
        $payload = [
            'customer_name' => 'Ahmad Dani',
            'phone_number' => '081987654321',
            'email' => 'ahmad@example.com',
            'room_id' => $this->room1->id,
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => now()->format('Y-m-d H:i:s'),
            'booking_status' => 'Check-in',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'source' => 'Offline',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'booking_code',
            'booking' => [
                'id',
                'booking_code',
                'customer_name',
                'room_number',
                'room_type',
                'check_in_formatted',
                'check_out_formatted',
                'booking_status',
                'total_price_formatted',
            ],
            'stats' => [
                'available',
                'occupied',
                'today_check_in',
                'today_check_out',
            ],
        ]);

        // Cek database
        $this->assertDatabaseHas('customers', [
            'phone_number' => '081987654321',
            'customer_name' => 'Ahmad Dani',
        ]);

        $this->assertDatabaseHas('bookings', [
            'room_id' => $this->room1->id,
            'booking_status' => 'Check-in',
            'payment_status' => 'Paid',
            'total_price' => 150000,
        ]);

        // Status kamar harus sinkron berubah menjadi Occupied
        $this->room1->refresh();
        $this->assertEquals('Occupied', $this->room1->room_status);
    }

    public function test_customer_record_not_duplicated_when_phone_already_exists(): void
    {
        $existingCustomer = Customer::create([
            'customer_name' => 'Pelanggan Lama',
            'phone_number' => '081122334455',
            'email' => 'lama@example.com',
        ]);

        $payload = [
            'customer_name' => 'Pelanggan Lama Updated',
            'phone_number' => '081122334455',
            'room_id' => $this->room1->id,
            'booking_type' => 'Full Day',
            'check_in_time' => now()->format('Y-m-d H:i:s'),
            'booking_status' => 'Confirmed',
            'payment_method' => 'QRIS',
            'payment_status' => 'Paid',
            'source' => 'WhatsApp',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $payload);

        $response->assertStatus(201);

        // Jumlah customer dengan nomor telepon tersebut tetap 1
        $this->assertEquals(1, Customer::where('phone_number', '081122334455')->count());
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $existingCustomer->id,
            'total_price' => 350000,
        ]);
    }

    public function test_prevent_double_booking_overlap_returns_json_422(): void
    {
        $checkIn = now()->addHour();
        $checkOut = (clone $checkIn)->addHours(5);

        // Booking 1 yang sudah ada
        Booking::create([
            'room_id' => $this->room1->id,
            'customer_id' => Customer::create([
                'customer_name' => 'Tamu Pertama',
                'phone_number' => '082222222222',
            ])->id,
            'booking_code' => 'KZN-EXIST-01',
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'booking_status' => 'Check-in',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'total_price' => 150000,
            'source' => 'Offline',
        ]);

        // Coba booking kamar yang sama pada waktu yang tumpang tindih
        $payloadOverlap = [
            'customer_name' => 'Tamu Kedua',
            'phone_number' => '083333333333',
            'room_id' => $this->room1->id,
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => (clone $checkIn)->addHours(2)->format('Y-m-d H:i:s'),
            'booking_status' => 'Pending',
            'payment_method' => 'Cash',
            'payment_status' => 'Pending',
            'source' => 'Offline',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $payloadOverlap);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['room_id']);
    }

    public function test_validation_fails_when_required_fields_missing(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'customer_name',
            'phone_number',
            'room_id',
            'booking_type',
            'check_in_time',
            'payment_method',
            'payment_status',
            'source',
        ]);
    }

    public function test_traditional_form_submission_still_redirects_correctly(): void
    {
        $payload = [
            'customer_name' => 'Siti Aminah',
            'phone_number' => '085566778899',
            'email' => 'siti@example.com',
            'room_id' => $this->room1->id,
            'booking_type' => 'Short Time 8 Jam',
            'check_in_time' => now()->format('Y-m-d H:i:s'),
            'booking_status' => 'Confirmed',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'source' => 'Offline',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pemesanan.store'), $payload);

        $response->assertRedirect(route('admin.pemesanan.index'));
        $response->assertSessionHas('success');
    }
}
