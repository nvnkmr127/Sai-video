<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@workshoppro.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('admin123'),
            ]
        );

        $user->forceFill(['is_admin' => true])->save();
    }
}
