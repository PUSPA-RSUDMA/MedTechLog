<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Ketercapaian MedTechLog</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #331F24; margin: 0; padding: 14px; }
        .header { border-bottom: 2px solid #CB4D6D; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 18px; font-weight: bold; color: #CB4D6D; margin: 0; }
        .subtitle { font-size: 11px; color: #75565E; margin: 2px 0 0 0; }
        .meta { margin-bottom: 12px; font-size: 9.5px; color: #331F24; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary-table td { border: 1px solid #E5CDD3; padding: 6px 10px; background: #FAF5F6; }
        .summary-label { font-size: 8.5px; color: #75565E; text-transform: uppercase; display: block; }
        .summary-val { font-size: 13px; font-weight: bold; color: #331F24; }
        table.data { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.data th { background: #CB4D6D; color: #ffffff; padding: 6px; text-align: left; border: 1px solid #CB4D6D; }
        table.data td { padding: 5px 6px; border: 1px solid #E5CDD3; vertical-align: top; }
        table.data tr:nth-child(even) { background: #FAF5F6; }
        .right { text-align: right; }
        .center { text-align: center; }
        .signatures { width: 100%; margin-top: 28px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 8px 24px; }
        .sig-space { height: 55px; }
        .sig-line { border-top: 1px solid #334155; width: 200px; margin: 0 auto 4px auto; }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">MedTechLog — Laporan Ketercapaian Pekerjaan</p>
        <p class="subtitle">Rekap Tugas, Unit, PIC, dan Progres Ketercapaian Tim Internal</p>
    </div>

    <div class="meta">
        <strong>Filter:</strong> {{ $meta['filter_summary'] ?? 'Semua Data' }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Tanggal Cetak:</strong> {{ $meta['printed_at'] ?? now()->format('d/m/Y') }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Dicetak Oleh:</strong> {{ $printedBy->name }} ({{ $printedBy->role->label() }})
    </div>

    <table class="summary-table">
        <tr>
            <td><span class="summary-label">Total Tugas</span><span class="summary-val">{{ $summary['total_tasks'] }}</span></td>
            <td><span class="summary-label">Tugas Selesai</span><span class="summary-val">{{ $summary['completed_tasks'] }}</span></td>
            <td><span class="summary-label">Rata-rata Ketercapaian</span><span class="summary-val">{{ $summary['avg_progress'] }}%</span></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 10%;">Kode</th>
                <th style="width: 26%;">Judul Tugas</th>
                <th style="width: 14%;">Unit / Divisi</th>
                <th style="width: 14%;">PIC</th>
                <th style="width: 10%;">Tenggat</th>
                <th class="right" style="width: 10%;">Ketercapaian</th>
                <th style="width: 16%;">Status &amp; Jadwal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td>{{ $task->code }}</td>
                    <td><strong>{{ $task->title }}</strong></td>
                    <td>{{ $task->display_unit_name }}</td>
                    <td>{{ $task->display_pic_name }}</td>
                    <td>{{ $task->due_date?->format('d/m/Y') }}</td>
                    <td class="right"><strong>{{ $task->progress_percent }}%</strong><br><span style="color:#64748b;">{{ (int) $task->actual_output }}/{{ (int) $task->estimated_output }}</span></td>
                    <td>{{ $task->status->label() }} ({{ $task->schedule_health['label'] }})</td>
                </tr>
            @empty
                <tr><td colspan="7" class="center">Tidak ada data tugas pada filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <p>Dibuat Oleh,</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <strong>{{ $printedBy->name }}</strong><br>
                <span>{{ $printedBy->position ?? $printedBy->role->label() }}</span>
            </td>
            <td>
                <p>Diketahui / Disetujui Oleh,</p>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <strong>{{ $printedBy->supervisor?->name ?? 'Pimpinan Unit / Manajemen' }}</strong><br>
                <span>{{ $printedBy->supervisor?->position ?? 'Atasan Langsung' }}</span>
            </td>
        </tr>
    </table>
</body>
</html>
