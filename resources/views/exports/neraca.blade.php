<table>
    <tr>
        <th colspan="2"><strong>ASET</strong></th>
    </tr>
    @foreach($aset as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td style="text-align:right;">{{ $item['saldo'] }}</td>
        </tr>
    @endforeach
    <tr>
        <td><strong>Total ASET</strong></td>
        <td style="text-align:right;"><strong>{{ $totalAset }}</strong></td>
    </tr>

    <tr><td colspan="2"><br></td></tr>

    <tr>
        <th colspan="2"><strong>KEWAJIBAN</strong></th>
    </tr>
    @foreach($kewajiban as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td style="text-align:right;">{{ $item['saldo'] }}</td>
        </tr>
    @endforeach

    <tr>
        <th colspan="2"><strong>EKUITAS</strong></th>
    </tr>
    <tr>
        <td>Laba Bulan Berjalan</td>
        <td style="text-align:right;">{{ $laba_bulan }}</td>
    </tr>
    @foreach($ekuitas as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td style="text-align:right;">{{ $item['saldo'] }}</td>
        </tr>
    @endforeach

    <tr>
        <td><strong>Total KEWAJIBAN + EKUITAS</strong></td>
        <td style="text-align:right;"><strong>{{ $totalPassiva }}</strong></td>
    </tr>

    <tr><td colspan="2"><br></td></tr>

    <tr>
        <td colspan="2" style="font-size:16px;">
            <strong>
                {{ abs($totalAset - $totalPassiva) < 0.01 ? '🎉 BALANCE' : '😢 TIDAK BALANCE (' . number_format($totalAset - $totalPassiva, 2, ',', '.') . ')' }}
            </strong>
        </td>
    </tr>
</table>
