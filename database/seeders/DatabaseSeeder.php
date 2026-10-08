<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin & Resepsionis
        User::create([
            'username' => 'admin',
            'user_name' => 'Administrator',
            'password' => Hash::make('admin123'),
            'role' => 'Admin',
        ]);

        User::create([
            'username' => 'resepsionis',
            'user_name' => 'Mbak Resepsionis',
            'password' => Hash::make('resepsionis123'),
            'role' => 'Receptionist',
        ]);

        // 2. Buat Sample Data Kamar
        $roomsData = [
            ['room_number' => '101', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '102', 'room_type' => 'Twin', 'room_status' => 'Available', 'price_5_hours' => 140000, 'price_8_hours' => 190000, 'full_day_price' => 330000],
            ['room_number' => '103', 'room_type' => 'King', 'room_status' => 'Occupied', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '104', 'room_type' => 'Twin', 'room_status' => 'Cleaning', 'price_5_hours' => 140000, 'price_8_hours' => 190000, 'full_day_price' => 330000],
            ['room_number' => '105', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '201', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 160000, 'price_8_hours' => 210000, 'full_day_price' => 380000],
            ['room_number' => '202', 'room_type' => 'Twin', 'room_status' => 'Occupied', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '203', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 160000, 'price_8_hours' => 210000, 'full_day_price' => 380000],
        ];

        foreach ($roomsData as $r) {
            Room::create($r);
        }

        // 3. Buat Sample Customer
        $c1 = Customer::create([
            'customer_name' => 'Budi Santoso',
            'phone_number' => '081234567890',
            'email' => 'budi@example.com',
        ]);

        $c2 = Customer::create([
            'customer_name' => 'Siti Rahma',
            'phone_number' => '089876543210',
            'email' => 'siti@example.com',
        ]);

        // 4. Buat Sample Booking
        Booking::create([
            'room_id' => 3, // Room 103 (Occupied)
            'customer_id' => $c1->id,
            'booking_code' => Booking::generateCode(),
            'booking_type' => 'Full Day',
            'check_in_time' => now()->startOfDay()->addHours(14),
            'check_out_time' => now()->addDay()->startOfDay()->addHours(12),
            'booking_status' => 'Check-in',
            'payment_method' => 'QRIS',
            'payment_status' => 'Paid',
            'payment_time' => now()->subHours(2),
            'total_price' => 350000,
            'source' => 'Website',
        ]);

        Booking::create([
            'room_id' => 7, // Room 202 (Occupied)
            'customer_id' => $c2->id,
            'booking_code' => Booking::generateCode(),
            'booking_type' => 'Short Time 5 Jam',
            'check_in_time' => now()->subHours(1),
            'check_out_time' => now()->addHours(4),
            'booking_status' => 'Check-in',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'payment_time' => now()->subHours(1),
            'total_price' => 150000,
            'source' => 'Offline',
        ]);
    }
}
