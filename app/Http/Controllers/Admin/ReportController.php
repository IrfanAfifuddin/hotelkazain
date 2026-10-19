<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SalesReportExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    /**
     * Tampilkan rekapitulasi laporan penjualan (khusus Admin).
     */
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = $this->buildReportQuery($startDate, $endDate);
        $bookings = $query->get();
        $totalRevenue = (float) $bookings->sum('total_price');

        return view('admin.reports.index', compact('bookings', 'totalRevenue', 'startDate', 'endDate'));
    }

    /**
     * Unduh laporan penjualan dalam format Excel (.xlsx).
     */
    public function export(Request $request, SalesReportExportService $exportService): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = $this->buildReportQuery($startDate, $endDate);
        $bookings = $query->get();
        $totalRevenue = (float) $bookings->sum('total_price');

        $excelContent = $exportService->generate($bookings, $totalRevenue, $startDate, $endDate);

        $filename = 'Laporan_Penjualan_Kazain_Hotel.xlsx';

        return response($excelContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($excelContent),
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Susun query pemesanan sukses/lunas dengan filter rentang tanggal.
     */
    private function buildReportQuery(?string $startDate, ?string $endDate): Builder
    {
        $query = Booking::query()
            ->with(['customer', 'room'])
            ->where('payment_status', 'Paid')
            ->latest('payment_time')
            ->latest('id');

        if ($startDate) {
            $query->where(function (Builder $q) use ($startDate): void {
                $q->whereDate('payment_time', '>=', $startDate)
                    ->orWhere(function (Builder $sub) use ($startDate): void {
                        $sub->whereNull('payment_time')
                            ->whereDate('created_at', '>=', $startDate);
                    });
            });
        }

        if ($endDate) {
            $query->where(function (Builder $q) use ($endDate): void {
                $q->whereDate('payment_time', '<=', $endDate)
                    ->orWhere(function (Builder $sub) use ($endDate): void {
                        $sub->whereNull('payment_time')
                            ->whereDate('created_at', '<=', $endDate);
                    });
            });
        }

        return $query;
    }
}

