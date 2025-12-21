<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Resort;
use App\Models\Accommodation;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing resorts and accommodations to link to
        $resorts = Resort::all();
        $accommodations = Accommodation::all();

        if ($resorts->isEmpty()) {
            $this->command->error('Please add some Resorts first before seeding!');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            $resort = $resorts->random();
            $pax = rand(1, 10);
            
            // Create a random date within the last 30 days
            $randomDate = Carbon::now()->subDays(rand(0, 30));

            Booking::create([
                'guest_name' => 'Guest ' . rand(100, 999),
                'resort_id' => $resort->id,
                'accommodation_id' => $accommodations->isNotEmpty() ? $accommodations->random()->id : null,
                'pax' => $pax,
                'stay_type' => rand(0, 1) ? 'day_tour' : 'overnight',
                'check_in' => $randomDate,
                'check_out' => $randomDate->copy()->addDay(),
                'origin' => collect(['Manila', 'Cavite', 'Batangas', 'Laguna', 'Quezon City'])->random(),
                'status' => rand(0, 5) > 1 ? 'paid' : 'unpaid', // Mostly paid
                'total_amount' => $pax * rand(500, 1500),
                'payment_method' => collect(['cash', 'gcash', 'bank_transfer'])->random(),
                'guest_status' => collect(['pending', 'checked_in', 'checked_out'])->random(),
                'created_at' => $randomDate,
            ]);
        }
    }
}