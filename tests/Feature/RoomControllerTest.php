<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_receptionist_see_room_counts_and_status_buttons_for_all_rooms(): void
    {
        foreach ([
            ['01', 'Available'],
            ['02', 'Available'],
            ['03', 'Available'],
            ['04', 'Available'],
            ['05', 'Available'],
            ['06', 'Available'],
            ['07', 'Available'],
            ['08', 'Available'],
            ['09', 'Occupied'],
            ['10', 'Occupied'],
            ['11', 'Occupied'],
            ['12', 'Cleaning'],
        ] as [$roomNumber, $roomStatus]) {
            Room::create([
                'room_number' => $roomNumber,
                'room_type' => 'King',
                'room_status' => $roomStatus,
                'price_5_hours' => 150000,
                'price_8_hours' => 200000,
                'full_day_price' => 350000,
            ]);
        }

        $usersAndRoutes = [
            [User::factory()->create(['role' => 'Admin']), 'admin.status-kamar.index'],
            [User::factory()->create(['role' => 'Receptionist']), 'receptionist.status-kamar.index'],
        ];

        foreach ($usersAndRoutes as [$user, $routeName]) {
            $response = $this->actingAs($user)->get(route($routeName));

            $response->assertOk();
            $response->assertSee('12 dari 12 kamar');
            $response->assertSee('aria-label="Tersedia: 8 kamar"', false);
            $response->assertSee('aria-label="Terisi: 3 kamar"', false);
            $response->assertSee('aria-label="Cleaning: 1 kamar"', false);
            $response->assertDontSee('<select name="room_status"', false);

            foreach (range(1, 12) as $roomNumber) {
                $formattedRoomNumber = str_pad((string) $roomNumber, 2, '0', STR_PAD_LEFT);
                $response->assertSee('Kamar '.$formattedRoomNumber);
                $response->assertSee('name="room_status"', false);
                $response->assertSee('value="Available"', false);
                $response->assertSee('value="Occupied"', false);
                $response->assertSee('value="Cleaning"', false);
            }
        }
    }
}
