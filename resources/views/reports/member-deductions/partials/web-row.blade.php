<tr>
    <td class="text-center">{{ $row['no'] }}</td>
    <td><span class="table-primary">{{ $row['name'] }}</span><span class="table-secondary">{{ $row['member_number'] }}</span></td>
    @foreach(['saving_principal','saving_mandatory','saving_voluntary','money_opening','money_principal'] as $field)
        <td class="text-right">{{ $row[$field] ? number_format($row[$field], 0, ',', '.') : '-' }}</td>
    @endforeach
    <td class="text-center">{{ $row['money_installment_no'] ?: '-' }}</td>
    @foreach(['money_interest','money_ending','money_total','goods_opening','goods_principal'] as $field)
        <td class="text-right">{{ $row[$field] ? number_format($row[$field], 0, ',', '.') : '-' }}</td>
    @endforeach
    <td class="text-center">{{ $row['goods_installment_no'] ?: '-' }}</td>
    @foreach(['goods_interest','goods_ending','goods_total','loan_total','all_total'] as $field)
        <td class="text-right {{ in_array($field, ['money_total','goods_total','all_total']) ? 'font-semibold' : '' }}">{{ $row[$field] ? number_format($row[$field], 0, ',', '.') : '-' }}</td>
    @endforeach
</tr>
