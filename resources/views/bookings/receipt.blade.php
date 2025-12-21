<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Guest Receipt</title>
    <style>
        /* GLOBAL FONT FIX */
        * { font-family: 'DejaVu Sans', sans-serif !important; }

        @page { margin: 30px; } /* More standard margin */
        
        body { 
            padding: 10px; 
            border: 2px solid #444; 
            color: #333; 
            font-size: 14px; /* BIGGER FONT */
        }
        
        .header-table { width: 100%; border-bottom: 2px solid #444; margin-bottom: 15px; padding-bottom: 10px; }
        .logo { font-size: 26px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .sub-header { font-size: 12px; color: #666; margin-top: 4px; }
        
        .content { margin-top: 10px; line-height: 1.5; } /* Increased line height */
        .row { 
            margin-bottom: 5px; 
            border-bottom: 1px dashed #eee; 
            padding-bottom: 4px; 
        }
        
        /* Widened label to fit bigger text */
        .label { font-weight: bold; width: 150px; display: inline-block; color: #555; }
        .value { display: inline-block; font-weight: normal; } 

        .section-title { 
            margin-top: 15px; 
            margin-bottom: 5px; 
            font-size: 12px; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #777; 
            border-bottom: 1px solid #ccc;
        }

        /* PAYMENT BOX */
        .payment-box { 
            margin-top: 20px; 
            border: 2px solid #000; 
            padding: 10px; 
            text-align: center; 
            background-color: #f9f9f9; 
        }
        .total-label { 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin-bottom: 2px;
        }
        .total-amount { 
            font-size: 28px; /* Very visible total */
            font-weight: bold; 
            color: #000; 
            margin-top: 0;
            line-height: 1.2;
        }
        .payment-method { 
            font-size: 11px; 
            font-style: italic; 
            margin-top: 2px; 
        }

        .footer { 
            margin-top: 25px; 
            text-align: center; 
            font-size: 10px; 
            color: #888; 
            border-top: 1px solid #eee; 
            padding-top: 10px; 
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td valign="top" align="left">
                <div class="logo">Barangay Tourism</div>
                <div class="sub-header">Official Payment Receipt & Boarding Pass</div>
                <div style="margin-top: 10px; font-weight: bold; font-size: 14px;">
                    Ref: #{{ $record->booking_reference }}
                </div>
                <div style="font-size: 12px; color: green; font-weight: bold; text-transform: uppercase;">
                    STATUS: {{ $record->status }}
                </div>
            </td>
            <td valign="top" align="right">
                <img src="data:image/svg+xml;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(90)->generate($record->booking_reference)) }}" style="width: 80px; height: 80px;">
            </td>
        </tr>
    </table>

    <div class="content">
        <div class="section-title" style="margin-top:0;">Guest Profile</div>
        <div class="row"><span class="label">Guest Name:</span> <span class="value">{{ $record->guest_name }}</span></div>
        <div class="row"><span class="label">Origin:</span> <span class="value">{{ $record->origin ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">Contact No:</span> <span class="value">{{ $record->contact_number ?? 'N/A' }}</span></div>
        
        <div class="section-title">Trip Details</div>
        <div class="row"><span class="label">Destination:</span> <span class="value">{{ $record->resort->name }}</span></div>
        <div class="row"><span class="label">Type of Visit:</span> <span class="value" style="text-transform: uppercase;">{{ str_replace('_', ' ', $record->stay_type) }}</span></div>

        @if($record->stay_type === 'overnight')
            <div class="row"><span class="label">Check-in:</span> <span class="value">{{ \Carbon\Carbon::parse($record->check_in)->format('M d, Y') }}</span></div>
            <div class="row"><span class="label">Check-out:</span> <span class="value">{{ \Carbon\Carbon::parse($record->check_out)->format('M d, Y') }}</span></div>
        @else
            <div class="row"><span class="label">Date of Visit:</span> <span class="value">{{ \Carbon\Carbon::parse($record->check_in)->format('M d, Y') }}</span></div>
        @endif

        <div class="row"><span class="label">Total Guests:</span> <span class="value">{{ $record->pax }} Pax</span></div>

        @if($record->accommodation)
            <div class="row"><span class="label">Accommodation:</span> <span class="value">{{ $record->accommodation->name }}</span></div>
        @endif

        @if($record->is_island_hopping)
            <div class="row"><span class="label">Island Hopping:</span> <span class="value">Included (&#8369;{{ number_format($record->subtotal_island_hopping, 2) }})</span></div>
        @endif

        @if($record->motor_count > 0 || $record->van_count > 0)
            <div class="row">
                <span class="label">Parking:</span> 
                <span class="value">
                    {{ $record->motor_count > 0 ? $record->motor_count . ' Motor(s) ' : '' }}
                    {{ $record->van_count > 0 ? $record->van_count . ' Van(s)' : '' }}
                </span>
            </div>
        @endif
        
        @if($record->remarks)
            <div class="row"><span class="label">Notes:</span> <span class="value">{{ $record->remarks }}</span></div>
        @endif
        
        <div class="section-title">Fee Breakdown</div>
        <div class="row"><span class="label">Eco Fee:</span> <span class="value">&#8369;{{ number_format($record->subtotal_eco_fee, 2) }}</span></div>
        <div class="row"><span class="label">Boat Fee:</span> <span class="value">&#8369;{{ number_format($record->subtotal_boat_fee, 2) }}</span></div>
        
        @if($record->subtotal_accommodation_fee > 0)
            <div class="row"><span class="label">Room Fee:</span> <span class="value">&#8369;{{ number_format($record->subtotal_accommodation_fee, 2) }}</span></div>
        @endif
        
        @if($record->subtotal_parking_fee > 0)
            <div class="row"><span class="label">Parking Fee:</span> <span class="value">&#8369;{{ number_format($record->subtotal_parking_fee, 2) }}</span></div>
        @endif
    </div>

    <div class="payment-box">
        <div class="total-label">Total Amount Paid</div>
        <div class="total-amount">&#8369;{{ number_format($record->total_amount, 2) }}</div>
        <div class="payment-method">Paid via {{ strtoupper($record->payment_method) }}</div>
    </div>

    <div class="footer">
        <p>Please present this QR code to the port staff for verification.</p>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>
</body>
</html>