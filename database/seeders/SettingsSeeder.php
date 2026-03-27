<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'stripe_key',
                'value' => env('STRIPE_KEY'),
                'group' => 'stripe',
                'updated_at' => now()
            ],
            [
                'key' => 'stripe_secret',
                'value' => env('STRIPE_SECRET'),
                'group' => 'stripe',
                'updated_at' => now()
            ],
            [
                'key' => 'stripe_webhook_secret',
                'value' => env('STRIPE_WEBHOOK_SECRET'),
                'group' => 'stripe',
                'updated_at' => now()
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
