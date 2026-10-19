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
        // 1. Buat Akun Admin & Resepsionis (idempoten: firstOrCreate)
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'user_name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'resepsionis'],
            [
                'user_name' => 'Mbak Resepsionis',
                'password' => Hash::make('resepsionis123'),
                'role' => 'Receptionist',
            ]
        );

        // 2. Buat Data Kamar (idempoten: firstOrCreate)
        // 12 kamar: 01-11 King Bed, 12 Twin Bed.
        $roomsData = [
            ['room_number' => '01', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '02', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '03', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '04', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '05', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '06', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '07', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '08', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '09', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '10', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '11', 'room_type' => 'King', 'room_status' => 'Available', 'price_5_hours' => 150000, 'price_8_hours' => 200000, 'full_day_price' => 350000],
            ['room_number' => '12', 'room_type' => 'Twin', 'room_status' => 'Available', 'price_5_hours' => 140000, 'price_8_hours' => 190000, 'full_day_price' => 330000],
        ];

        foreach ($roomsData as $r) {
            Room::firstOrCreate(['room_number' => $r['room_number']], $r);
        }

        // 3. Buat Sample Customer (hanya jika tabel booking kosong agar data eksisting tidak tertimpa/terduplikasi)
        if (Booking::count() === 0) {
            $c1 = Customer::firstOrCreate(
                ['phone_number' => '081234567890'],
                [
                    'customer_name' => 'Budi Santoso',
                    'email' => 'budi@example.com',
                ]
            );

            $c2 = Customer::firstOrCreate(
                ['phone_number' => '089876543210'],
                [
                    'customer_name' => 'Siti Rahma',
                    'email' => 'siti@example.com',
                ]
            );

            // Cari kamar 03 dan 12 berdasarkan room_number agar tidak bergantung pada id auto-increment
            $room3 = Room::where('room_number', '03')->first();
            $room12 = Room::where('room_number', '12')->first();

            if ($room3) {
                Booking::create([
                    'room_id' => $room3->id,
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
                $room3->update(['room_status' => 'Occupied']);
            }

            if ($room12) {
                Booking::create([
                    'room_id' => $room12->id,
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
                $room12->update(['room_status' => 'Occupied']);
            }
        }
    }
}
