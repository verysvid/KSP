<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Potongan Anggota</title>
    <style>
        @page { margin: 12mm 7mm 12mm 7mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 6.4px; color: #111827; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tfoot { display: table-row-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 0.45px solid #475569; padding: 2.5px 2px; vertical-align: middle; }
        th { background: #e2e8f0; text-align: center; font-size: 5.8px; font-weight: bold; }
        .report-title th { border: 0; background: #fff; padding: 0 0 2px; text-align: left; font-size: 9px; }
        .report-title.small th { padding-bottom: 1px; font-size: 7px; }
        .spacer th { height: 5px; border: 0; background: #fff; }
        .num { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .name { font-weight: bold; }
        .group { page-break-after: avoid; }
        .group td { background: #e0e7ff; color: #3730a3; font-weight: bold; font-size: 7px; }
        .subtotal { page-break-before: avoid; }
        .subtotal td { background: #f1f5f9; font-weight: bold; }
        .grand-total td { background: #c7d2fe; font-weight: bold; }
        .footer { position: fixed; left: 0; right: 0; bottom: -8mm; text-align: right; font-size: 6px; color: #64748b; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
<div class="footer">Halaman <span class="page-number"></span></div>
<table>
    <colgroup>
        <col style="width:2.2%"><col style="width:12%">
        <col style="width:5%"><col style="width:5%"><col style="width:5%">
        <col style="width:6%"><col style="width:5.5%"><col style="width:2.6%"><col style="width:5%"><col style="width:6%"><col style="width:6%">
        <col style="width:6%"><col style="width:5.5%"><col style="width:2.6%"><col style="width:5%"><col style="width:6%"><col style="width:6%">
        <col style="width:6%"><col style="width:7.1%">
    </colgroup>
    <thead>
    <tr class="report-title"><th colspan="19">DAFTAR POTONGAN ANGGOTA</th></tr>
    <tr class="report-title small"><th colspan="19">{{ $branch->code }} - {{ $branch->name }}</th></tr>
    <tr class="report-title small"><th colspan="19">PERIODE: {{ strtoupper(\Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y')) }}</th></tr>
    <tr class="spacer"><th colspan="19"></th></tr>
    <tr>
        <th rowspan="2">No</th><th rowspan="2">Nama</th><th colspan="3">Simpanan</th>
        <th rowspan="2">Pinjaman Uang</th><th colspan="4">Angsuran</th><th rowspan="2">Jumlah Pot. Uang</th>
        <th rowspan="2">Barang</th><th colspan="4">Angsuran Barang</th><th rowspan="2">Jumlah Pot. Barang</th>
        <th rowspan="2">Jumlah Uang + Barang</th><th rowspan="2">Jumlah Semua Potongan</th>
    </tr>
    <tr>
        <th>Pokok</th><th>Wajib</th><th>Manasuka</th>
        <th>Potongan</th><th>Ke</th><th>Jasa</th><th>Sisa</th>
        <th>Potongan</th><th>Ke</th><th>Jasa</th><th>Sisa</th>
    </tr>
    </thead>
    <tbody>
    @forelse($report['groups'] as $group)
        <tr class="group"><td colspan="19">GROUP: {{ $group['name'] }}</td></tr>
        @foreach($group['rows'] as $row)
            <tr>
                <td class="center">{{ $row['no'] }}</td><td class="name">{{ $row['name'] }}</td>
                @foreach(['saving_principal','saving_mandatory','saving_voluntary','money_opening','money_principal'] as $field)
                    <td class="num">{{ $row[$field] ? number_format($row[$field], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td class="center">{{ $row['money_installment_no'] ?: '-' }}</td>
                @foreach(['money_interest','money_ending','money_total','goods_opening','goods_principal'] as $field)
                    <td class="num">{{ $row[$field] ? number_format($row[$field], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td class="center">{{ $row['goods_installment_no'] ?: '-' }}</td>
                @foreach(['goods_interest','goods_ending','goods_total','loan_total','all_total'] as $field)
                    <td class="num">{{ $row[$field] ? number_format($row[$field], 0, ',', '.') : '-' }}</td>
                @endforeach
            </tr>
        @endforeach
        @include('reports.member-deductions.partials.pdf-total-row', [
            'label' => 'SUBTOTAL ' . $group['name'], 'totals' => $group['subtotal'], 'class' => 'subtotal'
        ])
    @empty
        <tr><td colspan="19" class="center">Tidak ada data anggota.</td></tr>
    @endforelse
    @if($report['memberCount'] > 0)
        @include('reports.member-deductions.partials.pdf-total-row', [
            'label' => 'JUMLAH KESELURUHAN', 'totals' => $report['totals'], 'class' => 'grand-total'
        ])
    @endif
    </tbody>
</table>
</body>
</html>
