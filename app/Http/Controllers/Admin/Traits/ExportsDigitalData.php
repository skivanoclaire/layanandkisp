<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

trait ExportsDigitalData
{
    /**
     * Export data to Excel
     *
     * @param Request $request
     * @param string $exportClass Fully qualified export class name
     * @param string $filename Base filename without extension
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportToExcel(Request $request, string $exportClass, string $filename)
    {
        $filters = [
            'status' => $request->status,
            'dari_tanggal' => $request->dari_tanggal,
            'sampai_tanggal' => $request->sampai_tanggal,
        ];

        $filename = $filename . '-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new $exportClass($filters), $filename);
    }

    /**
     * Export data to PDF
     *
     * @param Request $request
     * @param string $modelClass Fully qualified model class name
     * @param string $viewPath Blade view path for PDF
     * @param string $filename Base filename without extension
     * @param string $orientation 'portrait' or 'landscape'
     * @return \Illuminate\Http\Response
     */
    protected function exportToPdf(Request $request, string $modelClass, string $viewPath, string $filename, string $orientation = 'landscape')
    {
        $query = $modelClass::with(['user']);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filter on submitted_at
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('submitted_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('submitted_at', '<=', $request->sampai_tanggal);
        }

        $requests = $query->orderBy('submitted_at', 'desc')->get();

        $filterInfo = [
            'dari_tanggal' => $request->dari_tanggal,
            'sampai_tanggal' => $request->sampai_tanggal,
            'status' => $request->status,
        ];

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $html = view($viewPath, compact('requests', 'filterInfo'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        $filename = $filename . '-' . date('Y-m-d') . '.pdf';

        return $dompdf->stream($filename, ['Attachment' => true]);
    }
}
