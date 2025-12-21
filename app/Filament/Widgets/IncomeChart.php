<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Auth;


class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Income (Last 30 Days)';
    
    // Sort order: 2 means it appears after the Stats Overview
    protected static ?int $sort = 2; 

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // This hides the entire widget (including Revenue) from non-admins
        return Auth::check() && $user->hasRole('super_admin');
    }

    protected function getData(): array
    {
        // Calculate income per day for the last 30 days
        $data = Trend::model(Booking::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'Income (PHP)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#16a34a', // Green line
                    'backgroundColor' => '#dcfce7', // Light green fill
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}