<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:paid,unpaid'],
            'method' => ['nullable', 'in:cash,khqr,visa,mastercard,unspecified'],
        ]);

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        // Revenue is recognized by PAYMENT DATE (same concept as the existing PDF).
        $paidQuery = Booking::query()
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end]);

        $hasPaymentMethod = Schema::hasColumn('bookings', 'payment_method');
        $paidRows = (clone $paidQuery)->get(
            $hasPaymentMethod
                ? ['paid_at', 'total_amount', 'payment_method']
                : ['paid_at', 'total_amount']
        );
        $totalRevenue = (float) $paidRows->sum('total_amount');
        $paidBookings = $paidRows->count();

        // Unpaid entries have no payment date: use their booking creation date.
        $unpaidQuery = Booking::query()
            ->where('payment_status', 'unpaid')
            ->whereBetween('created_at', [$start, $end]);

        $unpaidBookings = (clone $unpaidQuery)->count();
        $totalRecords = $paidBookings + $unpaidBookings;

        // Keep the payment methods in a stable display order.
        $methodTotals = [
            'Cash' => 0.0,
            'KHQR' => 0.0,
            'Visa' => 0.0,
            'Mastercard' => 0.0,
            'Unspecified' => 0.0,
        ];

        // Use daily buckets for short periods and monthly/yearly buckets for longer ones.
        $days = $start->diffInDays($end);
        $bucket = $days <= 45 ? 'day' : ($days <= 730 ? 'month' : 'year');
        $cursor = $bucket === 'day' ? $start->copy()
            : ($bucket === 'month' ? $start->copy()->startOfMonth() : $start->copy()->startOfYear());

        $revenueBuckets = [];
        while ($cursor <= $end) {
            $key = $bucket === 'day' ? $cursor->format('Y-m-d')
                : ($bucket === 'month' ? $cursor->format('Y-m') : $cursor->format('Y'));
            $revenueBuckets[$key] = 0.0;

            if ($bucket === 'day') {
                $cursor->addDay();
            } elseif ($bucket === 'month') {
                $cursor->addMonth();
            } else {
                $cursor->addYear();
            }
        }

        foreach ($paidRows as $payment) {
            $method = strtolower(trim((string) ($payment->payment_method ?? '')));
            $name = match ($method) {
                'cash' => 'Cash',
                'khqr' => 'KHQR',
                'visa' => 'Visa',
                'mastercard' => 'Mastercard',
                default => 'Unspecified',
            };
            $amount = (float) $payment->total_amount;
            $methodTotals[$name] += $amount;

            $date = Carbon::parse($payment->paid_at);
            $key = $bucket === 'day' ? $date->format('Y-m-d')
                : ($bucket === 'month' ? $date->format('Y-m') : $date->format('Y'));

            if (array_key_exists($key, $revenueBuckets)) {
                $revenueBuckets[$key] += $amount;
            }
        }

        $chartLabels = array_map(function ($key) use ($bucket) {
            if ($bucket === 'year') {
                return $key;
            }
            return Carbon::parse($bucket === 'month' ? $key . '-01' : $key)
                ->format($bucket === 'month' ? 'M Y' : 'd M');
        }, array_keys($revenueBuckets));
        $chartValues = array_values($revenueBuckets);

        // Paid entries: payment date. Unpaid entries: booking creation date.
        $transactions = Booking::query()
            ->with(['guest', 'room', 'user'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($paid) use ($start, $end) {
                    $paid->where('payment_status', 'paid')
                        ->whereBetween('paid_at', [$start, $end]);
                })->orWhere(function ($unpaid) use ($start, $end) {
                    $unpaid->where('payment_status', 'unpaid')
                        ->whereBetween('created_at', [$start, $end]);
                });
            })
            ->when(($filters['status'] ?? '') !== '', function ($query) use ($filters) {
                $query->where('payment_status', $filters['status']);
            })
            ->when(($filters['method'] ?? '') !== '' && $hasPaymentMethod, function ($query) use ($filters) {
                $method = $filters['method'];
                if ($method === 'unspecified') {
                    $query->where(function ($q) {
                        $q->whereNull('payment_method')
                            ->orWhere('payment_method', '')
                            ->orWhere('payment_method', 'unspecified');
                    });
                } else {
                    $query->where('payment_method', $method);
                }
            })
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('guest', function ($guest) use ($term) {
                        $guest->where('full_name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    })->orWhereHas('room', function ($room) use ($term) {
                        $room->where('room_number', 'like', $term);
                    });
                });
            })
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        return view('reports.index', compact(
            'from', 'to', 'totalRevenue', 'paidBookings', 'unpaidBookings',
            'totalRecords', 'methodTotals', 'chartLabels', 'chartValues', 'transactions'
        ));
    }
}
