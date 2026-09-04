<tr>
    <td class="bulk-sticky-check text-center">
        <input type="checkbox" name="member_ids[]" value="{{ $row['member_id'] }}"
               class="member-checkbox" @disabled(!$row['selectable'])
               @checked(in_array((string) $row['member_id'], array_map('strval', old('member_ids', [])), true))>
    </td>
    <td class="bulk-sticky-no text-center">{{ $row['no'] }}</td>
    <td class="bulk-sticky-member">
        <span class="table-primary">{{ $row['name'] }}</span>
        <span class="table-secondary">{{ $row['member_number'] }}</span>
        @if($row['processed_batch'])
            <span class="mt-1 block text-xs font-semibold text-amber-600 dark:text-amber-400">Sudah diproses: {{ $row['processed_batch'] }}</span>
        @elseif($row['blocked_reason'])
            <span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-400">{{ $row['blocked_reason'] }}</span>
        @endif
    </td>
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
