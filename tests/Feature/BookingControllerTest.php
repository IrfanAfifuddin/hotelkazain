<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
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
            'email' => 'ahmad@gmail.com',
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

    public static function phoneNumberStoreCases(): array
    {
        return [
            '9 digit ditolak' => ['081234567', false],
            '10 digit diterima' => ['0812345678', true],
            '15 digit diterima' => ['081234567890123', true],
            '16 digit ditolak' => ['0812345678901234', false],
        ];
    }

    public static function bookingStaffStoreRoutes(): array
    {
        return [
            'admin' => ['Admin', 'admin.pemesanan.store'],
            'receptionist' => ['Receptionist', 'receptionist.pemesanan.store'],
        ];
    }

    public static function bookingStaffAvailabilityRoutes(): array
    {
        return [
            'admin' => ['Admin', 'admin.pemesanan.available-rooms'],
            'receptionist' => ['Receptionist', 'receptionist.pemesanan.available-rooms'],
        ];
    }

    #[DataProvider('phoneNumberStoreCases')]
    public function test_store_validates_phone_number_between_10_and_15_digits(string $phoneNumber, bool $shouldSucceed): void
    {
        $checkIn = now('Asia/Jakarta')->addDay()->setTime(14, 0);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), [
                'customer_name' => 'Ahmad Dani',
                'phone_number' => $phoneNumber,
                'email' => 'ahmad@gmail.com',
                'room_ids' => [$this->room->id],
                'booking_type' => 'Full Day',
                'check_in_time' => $checkIn->format('Y-m-d H:i:s'),
                'booking_status' => 'Confirmed',
                'payment_method' => 'Cash',
            ]);

        if ($shouldSucceed) {
            $response->assertCreated();
            $this->assertDatabaseHas('customers', ['phone_number' => $phoneNumber]);

            return;
        }

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'phone_number' => 'Nomor telepon harus antara 10 sampai 15 digit.',
            ]);

        $this->assertDatabaseMissing('customers', ['phone_number' => $phoneNumber]);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_update_rejects_phone_number_with_more_than_15_digits_without_changing_customer_data(): void
    {
        $customer = Customer::create([
            'customer_name' => 'Customer Lama',
            'phone_number' => '081234567890',
            'email' => 'lama@gmail.com',
        ]);

        $checkIn = now('Asia/Jakarta')->addDay()->setTime(14, 0);

        $booking = Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-000001',
            'booking_type' => 'Full Day',
            'check_in_time' => $checkIn,
            'check_out_time' => $checkIn->copy()->addDay()->setTime(12, 0),
            'booking_status' => 'Confirmed',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'total_price' => 350000,
        ]);

        $booking->rooms()->attach($this->room->id, ['price' => 350000]);

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.pemesanan.update', $booking), [
                'customer_name' => 'Nama Baru',
                'phone_number' => '0812345678901234',
                'email' => 'baru@gmail.com',
                'room_ids' => [$this->room->id],
                'booking_type' => 'Full Day',
                'check_in_time' => $checkIn->format('Y-m-d H:i:s'),
                'check_out_time' => $checkIn->copy()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
                'booking_status' => 'Confirmed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'phone_number' => 'Nomor telepon harus antara 10 sampai 15 digit.',
            ]);

        $customer->refresh();

        $this->assertSame('Customer Lama', $customer->customer_name);
        $this->assertSame('081234567890', $customer->phone_number);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_update_rejects_invalid_email_for_both_roles_without_changing_customer_data(): void
    {
        $customer = Customer::create([
            'customer_name' => 'Customer Lama',
            'phone_number' => '081234567890',
            'email' => 'lama@gmail.com',
        ]);

        $checkIn = now('Asia/Jakarta')->addDay()->setTime(14, 0);

        $booking = Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $customer->id,
            'booking_code' => 'KZN-000001',
            'booking_type' => 'Full Day',
            'check_in_time' => $checkIn,
            'check_out_time' => $checkIn->copy()->addDay()->setTime(12, 0),
            'booking_status' => 'Confirmed',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'total_price' => 350000,
        ]);

        $booking->rooms()->attach($this->room->id, ['price' => 350000]);

        $receptionist = User::create([
            'user_name' => 'Receptionist Test',
            'username' => 'receptionist_test',
            'password' => bcrypt('password'),
            'role' => 'Receptionist',
        ]);

        $roles = [
            'Admin' => [$this->admin, 'admin.pemesanan.update'],
            'Receptionist' => [$receptionist, 'receptionist.pemesanan.update'],
        ];

        foreach ($roles as [$user, $routeName]) {
            $response = $this->actingAs($user)
                ->putJson(route($routeName, $booking), [
                    'customer_name' => 'Nama Baru',
                    'phone_number' => '081234567891',
                    'email' => 'invalid@moal.com',
                    'room_ids' => [$this->room->id],
                    'booking_type' => 'Full Day',
                    'check_in_time' => $checkIn->format('Y-m-d H:i:s'),
                    'check_out_time' => $checkIn->copy()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
                    'booking_status' => 'Confirmed',
                    'payment_status' => 'Paid',
                    'payment_method' => 'Cash',
                ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors([
                    'email' => 'Domain email tidak valid atau tidak aktif.',
                ]);
        }

        $customer->refresh();

        $this->assertSame('Customer Lama', $customer->customer_name);
        $this->assertSame('081234567890', $customer->phone_number);
        $this->assertSame('lama@gmail.com', $customer->email);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('bookings', 1);
    }

    #[DataProvider('bookingStaffStoreRoutes')]
    public function test_store_rejects_booking_that_overlaps_an_existing_booking(string $role, string $routeName): void
    {
        $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00');
        $staff = $this->createStaff($role);

        $response = $this->actingAs($staff)
            ->postJson(route($routeName), $this->storePayload([
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 12:00:00',
            ]));

        $response->assertUnprocessable()
            ->assertJsonPath('errors.room_ids.0', 'Kamar 101 sudah dipesan atau tidak tersedia pada rentang waktu tersebut.');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_store_allows_back_to_back_booking_when_check_in_equals_previous_check_out(): void
    {
        $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $this->storePayload([
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 15:00:00',
            ]));

        $response->assertCreated();

        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseHas('bookings', [
            'check_in_time' => '2027-01-18 15:00:00',
            'check_out_time' => '2027-01-18 20:00:00',
        ]);
    }

    public function test_store_allows_booking_when_existing_booking_is_outside_requested_range(): void
    {
        $this->createExistingBooking($this->room, '2027-01-18 08:00:00', '2027-01-18 10:00:00');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $this->storePayload([
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 14:00:00',
            ]));

        $response->assertCreated();

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_store_ignores_cancelled_and_completed_bookings_when_checking_overlap(): void
    {
        $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00', 'Cancelled');
        $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00', 'Check-out');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $this->storePayload([
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 12:00:00',
            ]));

        $response->assertCreated();

        $this->assertDatabaseCount('bookings', 3);
    }

    public function test_store_rejects_conflict_from_secondary_pivot_room(): void
    {
        $secondaryRoom = Room::create([
            'room_number' => '102',
            'room_type' => 'Twin',
            'room_status' => 'Available',
            'price_5_hours' => 140000,
            'price_8_hours' => 190000,
            'full_day_price' => 330000,
        ]);

        $this->createExistingBooking(
            $this->room,
            '2027-01-18 10:00:00',
            '2027-01-18 15:00:00',
            additionalRooms: [$secondaryRoom],
        );

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pemesanan.store'), $this->storePayload([
                'room_ids' => [$secondaryRoom->id],
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 12:00:00',
            ]));

        $response->assertUnprocessable()
            ->assertJsonPath('errors.room_ids.0', 'Kamar 102 sudah dipesan atau tidak tersedia pada rentang waktu tersebut.');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_update_rejects_email_used_by_another_customer_with_validation_message(): void
    {
        $booking = $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00');
        $otherCustomer = Customer::create([
            'customer_name' => 'Customer Lain',
            'phone_number' => '081222222222',
            'email' => 'sudah-ada@gmail.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.pemesanan.update', $booking), [
                'customer_name' => 'Nama Diperbarui',
                'phone_number' => '081234567891',
                'email' => $otherCustomer->email,
                'room_ids' => [$this->room->id],
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 10:00:00',
                'check_out_time' => '2027-01-18 15:00:00',
                'booking_status' => 'Confirmed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'Email sudah digunakan oleh data customer lain. Gunakan email lain.',
            ]);

        $this->assertSame('existing0@gmail.com', $booking->customer->fresh()->email);
        $this->assertSame('Customer Lain', $otherCustomer->fresh()->customer_name);
        $this->assertDatabaseCount('customers', 2);
    }

    public function test_update_allows_editing_booking_after_check_in_time(): void
    {
        $booking = $this->createExistingBooking($this->room, '2026-11-18 10:00:00', '2026-11-18 15:00:00');

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.pemesanan.update', $booking), [
                'customer_name' => 'Nama Diperbarui',
                'phone_number' => '081234567891',
                'email' => 'existing0@gmail.com',
                'room_ids' => [$this->room->id],
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2026-11-18 10:00:00',
                'check_out_time' => '2026-11-18 15:00:00',
                'booking_status' => 'Check-out',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
            ]);

        $response->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('customers', [
            'id' => $booking->customer_id,
            'customer_name' => 'Nama Diperbarui',
            'phone_number' => '081234567891',
            'email' => 'existing0@gmail.com',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'check_in_time' => '2026-11-18 10:00:00',
            'check_out_time' => '2026-11-18 15:00:00',
            'booking_status' => 'Check-out',
        ]);
    }

    public function test_update_excludes_current_booking_from_overlap_check(): void
    {
        $booking = $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00');

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.pemesanan.update', $booking), [
                'customer_name' => 'Nama Diperbarui',
                'phone_number' => '081234567891',
                'email' => 'diperbarui@gmail.com',
                'room_ids' => [$this->room->id],
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 10:00:00',
                'check_out_time' => '2027-01-18 15:00:00',
                'booking_status' => 'Confirmed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
            ]);

        $response->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('customers', [
            'customer_name' => 'Nama Diperbarui',
            'email' => 'diperbarui@gmail.com',
        ]);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_update_allows_back_to_back_with_other_booking(): void
    {
        $booking = $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 12:00:00');
        $this->createExistingBooking($this->room, '2027-01-18 15:00:00', '2027-01-18 17:00:00');

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.pemesanan.update', $booking), [
                'customer_name' => 'Nama Diperbarui',
                'phone_number' => '081234567891',
                'email' => 'diperbarui@gmail.com',
                'room_ids' => [$this->room->id],
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 12:00:00',
                'check_out_time' => '2027-01-18 15:00:00',
                'booking_status' => 'Confirmed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
            ]);

        $response->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'check_in_time' => '2027-01-18 12:00:00',
            'check_out_time' => '2027-01-18 15:00:00',
        ]);
    }

    public function test_update_rejects_conflict_with_another_booking(): void
    {
        $booking = $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 12:00:00');
        $this->createExistingBooking($this->room, '2027-01-18 15:00:00', '2027-01-18 17:00:00');

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.pemesanan.update', $booking), [
                'customer_name' => 'Nama Diperbarui',
                'phone_number' => '081234567891',
                'email' => 'diperbarui@gmail.com',
                'room_ids' => [$this->room->id],
                'booking_type' => 'Short Time 5 Jam',
                'check_in_time' => '2027-01-18 14:00:00',
                'check_out_time' => '2027-01-18 16:00:00',
                'booking_status' => 'Confirmed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.room_ids.0', 'Kamar No. 101 tidak tersedia untuk slot waktu tersebut.');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'check_in_time' => '2027-01-18 10:00:00',
            'check_out_time' => '2027-01-18 12:00:00',
        ]);
    }

    #[DataProvider('bookingStaffAvailabilityRoutes')]
    public function test_available_rooms_endpoint_reflects_requested_time_range(string $role, string $routeName): void
    {
        $overlappingRoom = Room::create([
            'room_number' => '102',
            'room_type' => 'Twin',
            'room_status' => 'Available',
            'price_5_hours' => 140000,
            'price_8_hours' => 190000,
            'full_day_price' => 330000,
        ]);

        $this->createExistingBooking($this->room, '2027-01-18 08:00:00', '2027-01-18 10:00:00');
        $this->createExistingBooking($overlappingRoom, '2027-01-18 14:00:00', '2027-01-18 19:00:00');
        $staff = $this->createStaff($role);

        $response = $this->actingAs($staff)->getJson(route($routeName, [
            'check_in_time' => '2027-01-18 12:00:00',
            'booking_type' => 'Short Time 5 Jam',
        ]));

        $response->assertOk()
            ->assertJsonPath('rooms.0.room_number', '101')
            ->assertJsonPath('rooms.0.is_available', true)
            ->assertJsonPath('rooms.0.status', 'Available')
            ->assertJsonPath('rooms.1.room_number', '102')
            ->assertJsonPath('rooms.1.is_available', false)
            ->assertJsonPath('rooms.1.status', 'Booked');
    }

    #[DataProvider('bookingStaffAvailabilityRoutes')]
    public function test_available_rooms_endpoint_allows_back_to_back_schedule(string $role, string $routeName): void
    {
        $this->createExistingBooking($this->room, '2027-01-18 10:00:00', '2027-01-18 15:00:00');
        $staff = $this->createStaff($role);

        $response = $this->actingAs($staff)->getJson(route($routeName, [
            'check_in_time' => '2027-01-18 15:00:00',
            'booking_type' => 'Short Time 5 Jam',
        ]));

        $response->assertOk()
            ->assertJsonPath('rooms.0.room_number', '101')
            ->assertJsonPath('rooms.0.is_available', true)
            ->assertJsonPath('rooms.0.status', 'Available');
    }

    private function createStaff(string $role): User
    {
        if ($role === 'Admin') {
            return $this->admin;
        }

        return User::create([
            'user_name' => 'Receptionist Test',
            'username' => 'receptionist_'.strtolower((string) User::count()),
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Ahmad Dani',
            'phone_number' => '081987654321',
            'email' => 'ahmad@gmail.com',
            'room_ids' => [$this->room->id],
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => '2027-01-18 12:00:00',
            'booking_status' => 'Confirmed',
            'payment_method' => 'Cash',
        ], $overrides);
    }

    /**
     * @param  array<int, Room>  $additionalRooms
     */
    private function createExistingBooking(
        Room $room,
        string $checkIn,
        string $checkOut,
        string $status = 'Confirmed',
        array $additionalRooms = [],
    ): Booking {
        $customer = Customer::create([
            'customer_name' => 'Tamu Existing',
            'phone_number' => '081111111111',
            'email' => 'existing'.Customer::count().'@gmail.com',
        ]);

        $booking = Booking::create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'booking_code' => Booking::generateCode(),
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'booking_status' => $status,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'total_price' => 150000,
            'source' => 'Offline',
        ]);

        $booking->rooms()->attach($room->id, ['price' => 150000]);

        foreach ($additionalRooms as $additionalRoom) {
            $booking->rooms()->attach($additionalRoom->id, ['price' => 150000]);
        }

        return $booking;
    }
}
