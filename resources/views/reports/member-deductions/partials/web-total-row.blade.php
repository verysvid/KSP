<tr class="{{ $class }}">
    <td colspan="2">{{ $label }}</td>
    @foreach(['saving_principal','saving_mandatory','saving_voluntary','money_opening','money_principal'] as $field)
        <td class="text-right">{{ $totals[$field] ? number_format($totals[$field], 0, ',', '.') : '-' }}</td>
    @endforeach
    <td class="text-center">-</td>
    @foreach(['money_interest','money_ending','money_total','goods_opening','goods_principal'] as $field)
        <td class="text-right">{{ $totals[$field] ? number_format($totals[$field], 0, ',', '.') : '-' }}</td>
    @endforeach
    <td class="text-center">-</td>
    @foreach(['goods_interest','goods_ending','goods_total','loan_total','all_total'] as $field)
        <td class="text-right">{{ $totals[$field] ? number_format($totals[$field], 0, ',', '.') : '-' }}</td>
    @endforeach
</tr>
