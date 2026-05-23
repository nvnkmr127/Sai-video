<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $digits = fake()->numerify('##########');

        return [
            'workshop_id'    => Workshop::factory(),
            'full_name'      => fake()->name(),
            'phone'          => '+91' . $digits,
            'normalized_phone' => $digits,
            'address'        => fake()->address(),
            'organization'   => fake()->company(),
            'status'         => 'pending',
            'qr_code_token'  => (string) \Illuminate\Support\Str::uuid(),
            'qr_code_path'   => null,
            'webhook_sent_at' => null,
            'checked_in_at'   => null,
            'checked_in_by'   => null,
        ];
    }
}
