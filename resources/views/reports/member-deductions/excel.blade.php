<table>
    <thead>
    <tr><th colspan="19">DAFTAR POTONGAN ANGGOTA</th></tr>
    <tr><th colspan="19">{{ $branch->code }} - {{ $branch->name }}</th></tr>
    <tr><th colspan="19">PERIODE: {{ strtoupper(\Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y')) }}</th></tr>
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
    @foreach($report['groups'] as $group)
        <tr><td colspan="19" style="font-weight: bold; background-color: #e0e7ff;">GROUP: {{ $group['name'] }}</td></tr>
        @foreach($group['rows'] as $row)
            <tr>
                <td>{{ $row['no'] }}</td><td>{{ $row['name'] }}</td>
                <td>{{ $row['saving_principal'] }}</td><td>{{ $row['saving_mandatory'] }}</td><td>{{ $row['saving_voluntary'] }}</td>
                <td>{{ $row['money_opening'] }}</td><td>{{ $row['money_principal'] }}</td><td>{{ $row['money_installment_no'] }}</td>
                <td>{{ $row['money_interest'] }}</td><td>{{ $row['money_ending'] }}</td><td>{{ $row['money_total'] }}</td>
                <td>{{ $row['goods_opening'] }}</td><td>{{ $row['goods_principal'] }}</td><td>{{ $row['goods_installment_no'] }}</td>
                <td>{{ $row['goods_interest'] }}</td><td>{{ $row['goods_ending'] }}</td><td>{{ $row['goods_total'] }}</td>
                <td>{{ $row['loan_total'] }}</td><td>{{ $row['all_total'] }}</td>
            </tr>
        @endforeach
        @include('reports.member-deductions.partials.excel-total-row', [
            'label' => 'SUBTOTAL ' . $group['name'],
            'totals' => $group['subtotal'],
            'background' => '#f1f5f9',
        ])
    @endforeach
    @if($report['memberCount'] > 0)
        @include('reports.member-deductions.partials.excel-total-row', [
            'label' => 'JUMLAH KESELURUHAN',
            'totals' => $report['totals'],
            'background' => '#c7d2fe',
        ])
    @endif
    </tbody>
</table>
