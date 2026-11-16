<?php

namespace Tests\Feature;

use App\Models\SensorReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorReadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['iot.device_token' => 'classroom-test-token']);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'device_id' => 'ESP32-GREENHOUSE-01',
            'temperature' => 28.5,
            'humidity' => 65.25,
            'recorded_at' => '2026-11-16T08:00:00+08:00',
        ], $overrides);
    }

    private function submit(array $payload)
    {
        return $this->withToken('classroom-test-token')->postJson('/api/iot/readings', $payload);
    }

    public function test_valid_reading_is_saved_with_a_utc_timestamp(): void
    {
        $this->submit($this->payload())->assertCreated()
            ->assertJsonPath('data.device_id', 'ESP32-GREENHOUSE-01')
            ->assertJsonPath('data.temperature', 28.5);

        $this->assertDatabaseHas('sensor_readings', [
            'device_id' => 'ESP32-GREENHOUSE-01',
            'recorded_at' => '2026-11-16 00:00:00',
        ]);
    }

    public function test_recorded_at_can_be_omitted(): void
    {
        $payload = $this->payload();
        unset($payload['recorded_at']);
        $this->submit($payload)->assertCreated()->assertJsonPath('data.recorded_at', null);
    }

    public function test_missing_and_wrong_tokens_cannot_save_readings(): void
    {
        $this->postJson('/api/iot/readings', $this->payload())->assertUnauthorized();
        $this->withToken('wrong-token')->postJson('/api/iot/readings', $this->payload())->assertUnauthorized();
        $this->assertDatabaseCount('sensor_readings', 0);
    }

    public function test_unconfigured_token_fails_closed(): void
    {
        config(['iot.device_token' => '']);
        $this->submit($this->payload())->assertUnauthorized();
        $this->assertDatabaseCount('sensor_readings', 0);
    }

    public function test_missing_fields_are_rejected_without_writing_to_database(): void
    {
        $this->submit([])->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id', 'temperature', 'humidity']);
        $this->assertDatabaseCount('sensor_readings', 0);
    }

    public function test_invalid_values_are_rejected_without_writing_to_database(): void
    {
        foreach ([
            ['temperature' => 150], ['temperature' => -41], ['temperature' => 'hot'],
            ['humidity' => 120], ['humidity' => -1], ['humidity' => 'wet'],
            ['device_id' => str_repeat('x', 51)], ['device_id' => ['not a string']],
            ['recorded_at' => 'not-a-date'],
        ] as $invalid) {
            $this->submit($this->payload($invalid))->assertUnprocessable()
                ->assertJsonValidationErrors(array_keys($invalid));
        }
        $this->assertDatabaseCount('sensor_readings', 0);
    }

    public function test_boundary_values_are_accepted(): void
    {
        $this->submit($this->payload(['temperature' => -40, 'humidity' => 0]))->assertCreated();
        $this->submit($this->payload(['temperature' => 85, 'humidity' => 100]))->assertCreated();
        $this->assertDatabaseCount('sensor_readings', 2);
    }

    public function test_latest_returns_404_when_empty_and_history_returns_an_empty_array(): void
    {
        $this->getJson('/api/iot/readings/latest')->assertNotFound();
        $this->getJson('/api/iot/readings')->assertOk()->assertExactJson([]);
    }

    public function test_history_is_limited_to_twenty_and_newest_first(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            SensorReading::create($this->payload(['device_id' => 'DEVICE-'.$i, 'recorded_at' => null]));
        }
        $this->getJson('/api/iot/readings')->assertOk()->assertJsonCount(20)
            ->assertJsonPath('0.device_id', 'DEVICE-25')
            ->assertJsonPath('19.device_id', 'DEVICE-6');
        $this->getJson('/api/iot/readings/latest')->assertOk()->assertJsonPath('device_id', 'DEVICE-25');
    }

    public function test_dashboard_handles_empty_and_populated_data(): void
    {
        $this->get('/iot-dashboard')->assertOk()->assertSee('No sensor readings yet.');
        SensorReading::create($this->payload(['recorded_at' => null]));
        $this->get('/iot-dashboard')->assertOk()->assertSee('ESP32-GREENHOUSE-01')
            ->assertSee('28.50')->assertSee('65.25')->assertSee('Not provided');
        $this->get('/')->assertRedirect('/iot-dashboard');
    }

    public function test_dashboard_escapes_device_identifiers(): void
    {
        SensorReading::create($this->payload(['device_id' => '<script>alert(1)</script>', 'recorded_at' => null]));
        $this->get('/iot-dashboard')->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
