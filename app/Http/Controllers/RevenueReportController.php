<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    // =====================================
    // EXPORT HOTEL REVENUE REPORT AS PDF
    // ADMIN / OWNER ONLY
    // =====================================

    public function exportPdf(Request $request)
    {
        // Only Admin can export revenue reports
        abort_unless(
            $request->user()->role === 'admin',
            403
        );

        // Validate report dates
        $filters = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $from = $filters['from'];
        $to = $filters['to'];

        // Define date range
        $startDate = Carbon::parse($from)->startOfDay();
        $endDate = Carbon::parse($to)->endOfDay();

        // =====================================
        // GET PAID BOOKINGS
        // =====================================

        $payments = Booking::query()
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [
                $startDate,
                $endDate,
            ])
            ->orderByDesc('paid_at')
            ->get();

        // =====================================
        // CALCULATE TOTAL REVENUE
        // =====================================

        $totalRevenue = (float) $payments->sum('total_amount');

        $paymentCount = $payments->count();

        // =====================================
        // GROUP REVENUE BY PAYMENT METHOD
        // =====================================

        $paymentSummary = $payments
            ->groupBy(function ($booking) {
                return strtoupper(
                    $booking->payment_method ?: 'UNSPECIFIED'
                );
            })
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'amount' => (float) $items->sum('total_amount'),
                ];
            });

        // =====================================
        // LATEST 5 PAYMENTS
        // =====================================

        $recentPayments = $payments->take(5);

        // =====================================
        // REPORT INFORMATION
        // =====================================

        $generatedAt = now();

        $generatedBy = $request->user()->name;

        // =====================================
        // GENERATE PDF
        // =====================================

        $pdf = Pdf::loadView(
            'reports.revenue-pdf',
            compact(
                'from',
                'to',
                'totalRevenue',
                'paymentCount',
                'paymentSummary',
                'recentPayments',
                'generatedAt',
                'generatedBy'
            )
        )->setPaper('a4', 'portrait');

        // Download PDF
        return $pdf->download(
            "Pito-Hotel-Revenue-{$from}-to-{$to}.pdf"
        );
    }
}
