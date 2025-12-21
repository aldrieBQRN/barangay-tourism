<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentMethodChart extends ChartWidget
{
    protected static ?string $heading = 'Payment Methods';
    protected static ?int $sort = 4; // Show last

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // This hides the entire widget (including Revenue) from non-admins
        return Auth::check() && $user->hasRole('super_admin');
    }

    protected function getData(): array
    {
        // Count bookings by payment method (Cash vs GCash vs Bank)
        $data = Booking::select('payment_method', DB::raw('count(*) as total'))
            ->groupBy('payment_method')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        '#f59e0b', // Amber (Cash)
                        '#0ea5e9', // Sky Blue (GCash)
                        '#8b5cf6', // Violet (Bank)
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            // Capitalize labels (e.g., "cash" -> "Cash")
            'labels' => $data->map(fn ($item) => ucfirst($item->payment_method)),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}