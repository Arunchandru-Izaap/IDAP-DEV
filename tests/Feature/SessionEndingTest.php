<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Session\Events\SessionEnding;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SessionEndingTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
     use RefreshDatabase;

    public function testSessionEndingEvent()
    {
        // Arrange: Mock the SessionEnding event
        $session = $this->app['session'];
        $event = new SessionEnding($session);

        Event::fake();

        // Act: Dispatch the mocked SessionEnding event
        Event::dispatch($event);

        // Assert: Perform assertions on the expected behavior
        // For example, you can assert that database records are updated
        // Assert that the 'is_login_now' field in the Employee model is set to 0
        $this->assertDatabaseHas('employees', [
            'emp_code' => 'E33745',
            'is_login_now' => 1
        ]);
    }
}
