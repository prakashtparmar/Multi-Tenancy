<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Exports\InventoryExport;
use App\Exports\CustomersExport;

class ReportController extends Controller
{

    public function index()
    {
        // $this->authorize('dashboard view'); // Ensure user has permission
        return view('central.reports.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required',
            'format' => 'required|in:csv,xlsx,pdf',
        ]);

        $type = $request->report_type;
        $format = $request->format;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $exportClass = null;
        $fileName = $type . '-' . date('Y-m-d');

        switch ($type) {
            case 'orders':
                $exportClass = new OrdersExport($startDate, $endDate);
                break;
            case 'inventory':
                $exportClass = new InventoryExport();
                break;
            case 'customers':
                $exportClass = new CustomersExport($startDate, $endDate);
                break;
            case 'interactions':
                $exportClass = new \App\Exports\InteractionsExport($startDate, $endDate);
                break;
            default:
                return back()->with('error', 'Invalid report type selected.');
        }

        $extension = $format;
        if ($format == 'pdf') {
            $writerType = \Maatwebsite\Excel\Excel::DOMPDF;
        } elseif ($format == 'xlsx') {
            $writerType = \Maatwebsite\Excel\Excel::XLSX;
        } else {
            $writerType = \Maatwebsite\Excel\Excel::CSV;
        }

        return Excel::download($exportClass, "{$fileName}.{$extension}", $writerType);
    }
}
