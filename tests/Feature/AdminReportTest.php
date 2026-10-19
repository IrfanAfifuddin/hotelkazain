<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Room $room;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'user_name' => 'Admin User',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $this->room = Room::create([
            'room_number' => '01',
            'room_type' => 'King',
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

    public function test_admin_can_view_sales_report_page_without_duplicate_section(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Rincian Transaksi Sukses');
        $response->assertSee('Total Pendapatan (Lunas)');
        $response->assertSee(route('admin.reports.export'));
        $response->assertSee('Unduh Excel');

        // Pastikan bagian duplikat print-only sudah tidak ada
        $response->assertDontSee('print-only');
        $response->assertDontSee('LAPORAN PENJUALAN KAZAIN HOTEL');
        $response->assertDontSee('window.print()');
    }

    public function test_admin_can_download_sales_report_as_valid_xlsx(): void
    {
        Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-100001',
            'booking_type' => 'Full Day',
            'check_in_time' => now(),
            'check_out_time' => now()->addDay(),
            'booking_status' => 'Check-in',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'payment_time' => now(),
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Laporan_Penjualan_Kazain_Hotel.xlsx"');

        $content = $response->getContent();
        $this->assertNotEmpty($content);

        // ZIP header signature PK\x03\x04
        $this->assertStringStartsWith("PK\x03\x04", $content);

        // Pastikan file ZIP berisi struktur XLSX yang valid
        $this->assertStringContainsString('[Content_Types].xml', $content);
        $this->assertStringContainsString('xl/workbook.xml', $content);
        $this->assertStringContainsString('xl/worksheets/sheet1.xml', $content);
        $this->assertStringContainsString('xl/styles.xml', $content);
    }

    public function test_export_filters_by_date_and_only_includes_paid_bookings(): void
    {
        // 1. Paid booking dalam rentang tanggal
        Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-PAID-IN',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-10-10 14:00:00',
            'check_out_time' => '2026-10-11 12:00:00',
            'booking_status' => 'Check-in',
            'payment_method' => 'QRIS',
            'payment_status' => 'Paid',
            'payment_time' => '2026-10-10 14:05:00',
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        // 2. Paid booking di luar rentang tanggal (sebelum)
        Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-PAID-OUT',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-09-01 14:00:00',
            'check_out_time' => '2026-09-02 12:00:00',
            'booking_status' => 'Check-out',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'payment_time' => '2026-09-01 14:00:00',
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        // 3. Pending booking dalam rentang tanggal (tidak boleh masuk)
        Booking::create([
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'booking_code' => 'KZN-PENDING',
            'booking_type' => 'Full Day',
            'check_in_time' => '2026-10-10 14:00:00',
            'check_out_time' => '2026-10-11 12:00:00',
            'booking_status' => 'Pending',
            'payment_method' => 'Cash',
            'payment_status' => 'Pending',
            'payment_time' => null,
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-15',
        ]));

        $response->assertOk();
        $content = $response->getContent();

        // KZN-PAID-IN harus ada dalam worksheet
        $this->assertStringContainsString('KZN-PAID-IN', $content);
        // KZN-PAID-OUT dan KZN-PENDING tidak boleh ada
        $this->assertStringNotContainsString('KZN-PAID-OUT', $content);
        $this->assertStringNotContainsString('KZN-PENDING', $content);
    }

    public function test_export_handles_empty_transactions_gracefully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $content = $response->getContent();

        $this->assertNotEmpty($content);
        $this->assertStringStartsWith("PK\x03\x04", $content);
        $this->assertStringContainsString('Tidak ada data transaksi', $content);
    }
}
