<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan rekapitulasi laporan penjualan (khusus Admin).
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Booking::with(['customer', 'room'])
            ->where('payment_status', 'Paid')
            ->latest();

        if ($startDate) {
            $query->whereDate('payment_time', '>=', $startDate)
                  ->orWhere(function($q) use ($startDate) {
                      $q->whereNull('payment_time')
                        ->whereDate('created_at', '>=', $startDate);
                  });
        }

        if ($endDate) {
            $query->whereDate('payment_time', '<=', $endDate)
                  ->orWhere(function($q) use ($endDate) {
                      $q->whereNull('payment_time')
                        ->whereDate('created_at', '<=', $endDate);
                  });
        }

        $bookings = $query->get();
        $totalRevenue = $bookings->sum('total_price');

        return view('admin.reports.index', compact('bookings', 'totalRevenue', 'startDate', 'endDate'));
    }
}
