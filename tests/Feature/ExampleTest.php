<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_displays_the_customer_hotel_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSeeText('Menginap Nyaman di Jantung Prawirotaman')
            ->assertSee(route('customer.availability', absolute: false), false)
            ->assertSee('<link rel="icon" href="data:,">', false);
    }

    public function test_old_customer_homepage_url_redirects_to_the_root(): void
    {
        $response = $this->get('/customer');

        $response->assertRedirect('/');
    }
}
