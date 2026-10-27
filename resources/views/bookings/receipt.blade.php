@php
    // Use the hotel's uploaded logo if present, otherwise its default logo.
    $settings = $hotelSettings ?? null;
    $logoPath = ($settings?->logo)
        ? storage_path('app/public/' . $settings->logo)
        : public_path('images/pito-logo.png');

    $logoSrc = null;
    if (is_file($logoPath)) {
        $mime = @getimagesize($logoPath)['mime'] ?? null;
        if (in_array($mime, ['image/png', 'image/jpeg', 'image/gif'], true)) {
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    $hotelName = $settings?->hotel_name ?: 'PITO HOTEL';
    $isPaid = $booking->payment_status === 'paid';
    $documentTitle = $isPaid ? 'PAYMENT RECEIPT' : 'BOOKING STATEMENT';
    $documentNumber = 'PITO-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT);
    $roomPrice = (float) $booking->price_per_night;
    $total = (float) $booking->total_amount;
    $paymentMethod = match (strtolower((string) ($booking->payment_method ?? ''))) {
        'cash' => 'Cash',
        'khqr' => 'KHQR',
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        default => 'Unspecified',
    };
    $checkIn = \Carbon\Carbon::parse($booking->check_in);
    $checkOut = \Carbon\Carbon::parse($booking->check_out);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle }} - {{ $documentNumber }}</title>
    <style>
        /* DomPDF + browser print: exactly one A4 page when content is normal. */
        /* Force A4 without relying on DomPDF page margins. */
        @page {
            size: A4 portrait;
            margin: 0;
        }
        /* Hard inset: A4 = 595pt wide; 42 + 510 + 43 = 595pt. */
        .receipt-sheet {
            width: 510pt;
            margin-left: 42pt;
            padding-top: 34pt;
        }
        html, body { padding: 0; margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            line-height: 1.32;
            color: #25392e;
            background: #ffffff;
        }
        table { width: 100%; border-collapse: collapse; border-spacing: 0; table-layout: fixed; }
        th, td { vertical-align: top; overflow-wrap: break-word; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #718377; }
        .top-border { height: 4pt; background-color: #168453; margin-bottom: 15pt; }
        .header { margin-bottom: 15pt; }
        .header td { vertical-align: middle; }
        .logo-cell { width: 49pt; }
        .hotel-logo { display: block; width: 42pt; max-height: 42pt; }
        .logo-fallback {
            width: 42pt; height: 42pt; background: #145c3a; color: #fff;
            font-size: 20pt; font-weight: bold; text-align: center; line-height: 42pt;
        }
        .brand-name { font-size: 17pt; color: #155b3b; font-weight: bold; }
        .brand-subtitle { font-size: 7.1pt; color: #718377; margin-top: 3pt; }
        .title-cell { width: 130pt; text-align: right; }
        .document-title { font-size: 10pt; color: #155b3b; font-weight: bold; white-space: nowrap; }
        .document-number { font-size: 8pt; color: #60766a; margin-top: 4pt; }
        .reference { background: #ecf6ef; margin-bottom: 15pt; }
        .reference td { padding: 9pt 10pt; }
        .field-label { color: #73887b; font-size: 7.4pt; margin-bottom: 3pt; }
        .field-value { color: #24543a; font-size: 9pt; font-weight: bold; }
        .section-heading {
            text-transform: uppercase; color: #165f3d; font-size: 9pt;
            letter-spacing: .3pt; font-weight: bold; margin-bottom: 9pt;
        }
        .details { margin-bottom: 15pt; }
        .details td { width: 50%; }
        .details .right-column { padding-left: 18pt; }
        .detail-field { margin-bottom: 8pt; }
        .detail-label { color: #728679; font-size: 7.5pt; margin-bottom: 2pt; }
        .detail-value { color: #26372d; font-size: 9pt; font-weight: bold; }
        .charges-title { margin-bottom: 7pt; }
        .charges { margin-bottom: 11pt; }
        .charges th {
            background: #145e3c; color: white; padding: 9pt 8pt;
            font-size: 7.6pt; font-weight: bold;
        }
        .charges td {
            padding: 10pt 8pt; border-bottom: 1pt solid #dfebe2;
            font-size: 8.7pt;
        }
        .room-type { font-weight: bold; color: #185d3e; font-size: 9pt; }
        .room-sub { margin-top: 3pt; color: #708476; font-size: 7.5pt; }
        .subtotal td { padding: 7pt 9pt; font-size: 8.4pt; border-bottom: 1pt solid #dde7df; }
        .total td {
            background: #e8f5ec; border-top: 1.4pt solid #1a965a;
            padding: 11pt 9pt; vertical-align: middle;
            font-size: 11pt; font-weight: bold; color: #145e39;
        }
        .total .amount { font-size: 16pt; color: #087c46; }
        .payment { border: 1pt solid #dce8e0; margin-top: 14pt; }
        .payment td { padding: 10pt 12pt; vertical-align: middle; }
        .payment .status-cell { width: 90pt; text-align: right; }
        .payment-title { color: #165f3d; font-size: 9.5pt; font-weight: bold; margin: 4pt 0; }
        .payment-line { font-size: 8pt; color: #64796a; margin-top: 3pt; }
        .paid, .unpaid { display: inline-block; padding: 6pt 12pt; font-size: 8pt; font-weight: bold; }
        .paid { background: #e2f4e8; color: #086b3a; }
        .unpaid { background: #fff1d6; color: #8e6200; }
        .handled { margin-top: 11pt; font-size: 8pt; }
        .footer {
            text-align: center; border-top: 1pt solid #dfeae1;
            margin-top: 12pt; padding-top: 9pt;
            page-break-inside: avoid;
        }
        .footer-title { font-size: 10.5pt; font-weight: bold; color: #155d3a; }
        .footer-note { color: #76877b; font-size: 7.5pt; margin-top: 4pt; }
        .bottom-border { height: 3pt; background: #168453; margin-top: 9pt; }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
  <div class="receipt-sheet">
    <div class="top-border"></div>

    <table class="header">
        <tr>
            <td class="logo-cell">
                @if ($logoSrc)
                    <img class="hotel-logo" src="{{ $logoSrc }}" alt="Hotel Logo">
                @else
                    <div class="logo-fallback">P</div>
                @endif
            </td>
            <td>
                <div class="brand-name">{{ $hotelName }}</div>
                <div class="brand-subtitle">HOTEL MANAGEMENT SYSTEM</div>
            </td>
            <td class="title-cell">
                <div class="document-title">{{ $documentTitle }}</div>
                <div class="document-number">#{{ $documentNumber }}</div>
            </td>
        </tr>
    </table>

    <table class="reference">
        <tr>
            <td>
                <div class="field-label">BOOKING REFERENCE</div>
                <div class="field-value">{{ $documentNumber }}</div>
            </td>
            <td>
                <div class="field-label">BOOKING DATE</div>
                <div class="field-value">{{ $booking->created_at?->format('d M Y') ?? 'N/A' }}</div>
            </td>
            <td>
                <div class="field-label">BOOKING STATUS</div>
                <div class="field-value">{{ ucfirst($booking->status) }}</div>
            </td>
        </tr>
    </table>

    <table class="details">
        <tr>
            <td>
                <div class="section-heading">Guest Information</div>
                <div class="detail-field"><div class="detail-label">Guest Name</div><div class="detail-value">{{ $booking->guest?->full_name ?? 'N/A' }}</div></div>
                <div class="detail-field"><div class="detail-label">Phone Number</div><div class="detail-value">{{ $booking->guest?->phone ?? 'N/A' }}</div></div>
                <div class="detail-field"><div class="detail-label">Email Address</div><div class="detail-value">{{ $booking->guest?->email ?: 'N/A' }}</div></div>
            </td>
            <td class="right-column">
                <div class="section-heading">Reservation Details</div>
                <div class="detail-field"><div class="detail-label">Room Number</div><div class="detail-value">Room {{ $booking->room?->room_number ?? 'N/A' }}</div></div>
                <div class="detail-field"><div class="detail-label">Check-In</div><div class="detail-value">{{ $checkIn->format('d M Y') }}</div></div>
                <div class="detail-field"><div class="detail-label">Check-Out</div><div class="detail-value">{{ $checkOut->format('d M Y') }}</div></div>
            </td>
        </tr>
    </table>

    <div class="section-heading charges-title">Room Charges</div>
    <table class="charges">
        <colgroup><col style="width:43%"><col style="width:15%"><col style="width:21%"><col style="width:21%"></colgroup>
        <thead>
            <tr>
                <th>DESCRIPTION</th>
                <th class="text-center">NIGHTS</th>
                <th class="text-right">RATE</th>
                <th class="text-right">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="room-type">{{ $booking->room?->roomType?->name ?? 'Hotel Room' }}</div>
                    <div class="room-sub">Room {{ $booking->room?->room_number ?? 'N/A' }}</div>
                    <div class="room-sub">{{ $checkIn->format('d M Y') }} - {{ $checkOut->format('d M Y') }}</div>
                </td>
                <td class="text-center">{{ $booking->nights }}</td>
                <td class="text-right">${{ number_format($roomPrice, 2) }}</td>
                <td class="text-right"><strong>${{ number_format($total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table>
        <tr class="subtotal">
            <td>Room Charges</td>
            <td class="text-right"><strong>${{ number_format($total, 2) }}</strong></td>
        </tr>
        <tr class="total">
            <td>TOTAL AMOUNT</td>
            <td class="text-right amount">${{ number_format($total, 2) }}</td>
        </tr>
    </table>

    <table class="payment">
        <tr>
            <td>
                <div class="field-label">PAYMENT INFORMATION</div>
                <div class="payment-title">{{ $isPaid ? 'Payment Recorded' : 'Payment Outstanding' }}</div>
                <div class="payment-line">Payment Method: {{ $paymentMethod }}</div>
                <div class="payment-line">
                    @if ($isPaid && $booking->paid_at)
                        Payment Date: {{ \Carbon\Carbon::parse($booking->paid_at)->format('d M Y') }}
                    @else
                        Payment has not yet been recorded.
                    @endif
                </div>
            </td>
            <td class="status-cell">
                <span class="{{ $isPaid ? 'paid' : 'unpaid' }}">{{ $isPaid ? 'PAID' : 'UNPAID' }}</span>
            </td>
        </tr>
    </table>

    <div class="handled"><span class="muted">Handled By:</span> <strong>{{ $booking->user?->name ?? 'Pito Hotel Staff' }}</strong></div>

    <div class="footer">
        <div class="footer-title">Thank you for choosing {{ $hotelName }}!</div>
        <div class="footer-note">We appreciate your stay and look forward to welcoming you again.</div>
        <div class="footer-note">{{ $isPaid ? 'This receipt confirms payment recorded in our hotel system.' : 'This document is not proof of payment.' }}</div>
        <div class="footer-note">Computer-generated document | {{ $documentNumber }}</div>
    </div>
    <div class="bottom-border"></div>
  </div>
</body>
</html>
