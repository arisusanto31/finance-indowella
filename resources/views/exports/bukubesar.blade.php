<table>
    @foreach ($data['chart_accounts'] as $code => $name)
        <tr>
            <td colspan="8" style="font-size:12px;"><strong>{{ $code }} - {{ $name }} </strong></td>
        </tr>
        <tr>
            <th><strong> No </strong></th>
            <th><strong> Tanggal </strong></th>
            <th><strong> No Jurnal </strong></th>
            <th><strong> Lawan COA </strong></th>
            <th><strong> Deskripsi </strong></th>
            <th><strong> Debet</strong> </th>
            <th> <strong>Kredit </strong></th>
            <th><strong> Saldo </strong></th>
        </tr>

        {{-- ini saldo awal --}}
        <tr>
            <td colspan="5" class="text-center">Saldo Awal</td>
            <td>0</td>
            <td>0</td>
            <td>{{ number_format(array_key_exists($code, $data['saldo_awal']) ? $data['saldo_awal'][$code] : 0, 2, ',', '.') }}
            </td>
        </tr>

        {{-- mutasi  --}}
        @if (isset($data['msg'][$code]))
            @foreach ($data['msg'][$code] as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['created_at'])->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $item['journal_number'] }}</td>
                    <td>{{ $item['lawan_code_group'] }} - {{ $item['lawan_code'] ? $item['lawan_code']['name'] : '?' }}
                    </td>
                    <td>{{ $item['description'] }}</td>
                    <td style="text-align:right;">{{ number_format($item['amount_debet'], 2, ',', '.') }}</td>
                    <td style="text-align:right;">{{ number_format($item['amount_kredit'], 2, ',', '.') }}</td>
                    <td style="text-align:right;">{{ number_format($item['amount_saldo'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-end"><strong>Total</strong></td>
                <td style="text-align:right;">
                    <strong>{{ number_format(collect($data['msg'][$code])->sum('amount_debet'), 2, ',', '.') }}</strong>
                </td>
                <td style="text-align:right;">
                    <strong>{{ number_format(collect($data['msg'][$code])->sum('amount_kredit'), 2, ',', '.') }}</strong>
                </td>
                <td style="text-align:right;">
                    <strong>{{ number_format(collect($data['msg'][$code])->last()['amount_saldo'] ?? 0, 2, ',', '.') }}</strong>
                </td>
            </tr>
        @else
            <tr>
                <td colspan="8" class="text-center">🤷‍♂️ Tidak ada data</td>
            </tr>
            <tr>
                <td colspan="5" class="text-end"><strong>Total</strong></td>
                <td style="text-align:right;">
                    <strong>0</strong>
                </td>
                <td style="text-align:right;">
                    <strong>0</strong>
                </td>
                <td style="text-align:right;">
                    <strong>{{ number_format(array_key_exists($code, $data['saldo_awal']) ? $data['saldo_awal'][$code] : 0, 2, ',', '.') }}</strong>
                </td>
            </tr>
        @endif
        <tr>
            <td></td>
        </tr>
    @endforeach
</table>
