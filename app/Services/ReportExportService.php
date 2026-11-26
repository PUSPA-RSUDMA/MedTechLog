<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportService
{
    /**
     * @param  Collection<int, Task>  $tasks
     * @param  array<string, mixed>  $meta
     */
    public function exportPdf(Collection $tasks, array $meta, User $printedBy): Response
    {
        $totalEstHours = round((float) $tasks->sum('estimated_hours'), 2);
        $totalActHours = round((float) $tasks->sum('actual_hours'), 2);
        $avgProgress = $tasks->isNotEmpty() ? round((float) $tasks->avg('progress_percent'), 1) : 0.0;
        $completedCount = $tasks->filter(fn (Task $t) => $t->status->value === 'completed')->count();

        $pdf = Pdf::loadView('reports.pdf', [
            'tasks' => $tasks,
            'meta' => $meta,
            'printedBy' => $printedBy,
            'summary' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $completedCount,
                'total_estimated_hours' => $totalEstHours,
                'total_actual_hours' => $totalActHours,
                'variance_hours' => round($totalActHours - $totalEstHours, 2),
                'avg_progress' => $avgProgress,
            ],
        ])->setPaper('a4', 'landscape');

        $filename = 'Laporan-Kinerja-MedTechLog-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @param  array<string, mixed>  $meta
     */
    public function exportExcel(Collection $tasks, array $meta): BinaryFileResponse
    {
        $tempPath = storage_path('app/temp_report_' . uniqid() . '.xlsx');
        $writer = new Writer();
        $writer->openToFile($tempPath);

        $titleStyle = new Style(fontBold: true, fontSize: 13, fontColor: '0F766E');
        $headerStyle = new Style(fontBold: true, fontSize: 10, fontColor: 'FFFFFF', backgroundColor: '0F766E');
        $totalStyle = new Style(fontBold: true, fontSize: 10, backgroundColor: 'E2E8F0');
        $bodyStyle = new Style(fontSize: 10);

        $writer->addRow(Row::fromValuesWithStyle(['LAPORAN KINERJA & LOG PEKERJAAN — MEDTECHLOG'], $titleStyle));
        $writer->addRow(Row::fromValuesWithStyle([
            'Dicetak pada: ' . now()->format('d/m/Y H:i') . ' WIB',
            'Filter: ' . ($meta['filter_summary'] ?? 'Semua Data'),
        ], $bodyStyle));
        $writer->addRow(Row::fromValues([]));

        $headers = [
            'Kode Tugas',
            'Judul Tugas',
            'Unit / Divisi',
            'PIC (Pelaksana)',
            'Tanggal Mulai',
            'Tenggat',
            'Target',
            'Tercapai',
            'Ketercapaian (%)',
            'Status',
            'Kesehatan Jadwal',
        ];

        $writer->addRow(Row::fromValuesWithStyle($headers, $headerStyle));

        foreach ($tasks as $task) {
            $writer->addRow(Row::fromValuesWithStyle([
                $task->code,
                $task->title,
                $task->display_unit_name,
                $task->display_pic_name,
                $task->estimated_start_date?->format('Y-m-d') ?? '-',
                $task->due_date?->format('Y-m-d') ?? '-',
                (int) $task->estimated_output,
                (int) $task->actual_output,
                $task->progress_percent,
                $task->status->label(),
                $task->schedule_health['label'],
            ], $bodyStyle));
        }

        $avgProg = $tasks->isNotEmpty() ? round((float) $tasks->avg('progress_percent'), 1) : 0.0;

        $writer->addRow(Row::fromValuesWithStyle([
            'TOTAL',
            $tasks->count() . ' Tugas',
            '', '', '', '', '', '',
            $avgProg,
            '', '',
        ], $totalStyle));

        $writer->close();

        $filename = 'Laporan-Kinerja-MedTechLog-' . now()->format('Ymd-His') . '.xlsx';

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
