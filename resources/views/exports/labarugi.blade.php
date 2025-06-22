<!-- prettier-ignore-start -->
<style>
    table th,td{
        border: 1px solid #998;
    }
</style>
<table>
    <tr>
        <th rowspan="2"><strong> Kode</strong></th>
        <th rowspan="2"><strong> Nama</strong></th>

        @foreach ($data['year_month'] as $yearmonth)
            <th colspan="2" style="text-align:center;"><strong>{{ $yearmonth }}</strong></th>
            <?php
            $sum[$yearmonth] = 0;
            $bebanPokok[$yearmonth] = 0;
            $bebanPenjualan[$yearmonth] = 0;
            $bebanAdminUmum[$yearmonth] = 0;
            $pendapatanLain[$yearmonth] = 0;
            $bebanLain[$yearmonth] = 0;
            $totalLainLain[$yearmonth] = 0;
            ?>
        @endforeach
        <th colspan="2"> <strong>Total {{ $data['year'] }}</strong></th>
    </tr>
   <tr>
        @foreach ($data['year_month'] as $yearmonth)
            <th style="text-align:right;"><strong>Jumlah</strong></th>
            <th style="text-align:right;"><strong>Prosen</strong></th>
        @endforeach
        <th style="text-align:right;"><strong>Jumlah</strong></th>
        <th style="text-align:right;"><strong>Prosen</strong></th>
    </tr>

    <tr>
        <td colspan="2"><strong>PENDAPATAN</strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;"></td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>

    @foreach (collect($data['all_charts'])->where('code_group', '<', 500000) as $chart)
        <tr>
            <td>{{ $chart->code_group }}</td>

            <td>{{ $chart->alias_name }}</td>

            <?php $t = 0; ?>
            @foreach ($data['year_month'] as $yearmonth)
                <?php
                $d = $data['msg'][$yearmonth][$chart->code_group];
                $t += $d['saldo_akhir'];
                $sum[$yearmonth] += $d['saldo_akhir'];
                
                ?>
                <td style="text-align:right;">{{ format_price($d['saldo_akhir']) }}</td>
                <td style="text-align:right;">{{ $d['prosen'] }} %</td>
            @endforeach
            <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
            <td><strong> {{ getProsen($t, $data['total_penjualan']) }} %</strong></td>

        </tr>
    @endforeach
    <tr>
        <td colspan="2"><strong>Pendapatan Netto</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ format_price($sum[$yearmonth]) }}</strong></td>
            <td style="text-align:right;"> <strong>{{ getProsen($sum[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong> </td>
            <?php $t += $sum[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>

    <tr>
        <td colspan="2"><strong>BEBAN POKOK</strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;"></td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>
    @foreach (collect($data['all_charts'])->where('code_group', '>', 600000)->where('code_group', '<', 700000) as $chart)
        <tr>
            <td>{{ $chart->code_group }}</td>

            <td>{{ $chart->alias_name }}</td>

            <?php $t = 0; ?>
            @foreach ($data['year_month'] as $yearmonth)
                <?php
                $d = $data['msg'][$yearmonth][$chart->code_group];
                $t += $d['saldo_akhir'];
                $bebanPokok[$yearmonth] += $d['saldo_akhir'];
                $sum[$yearmonth] += $d['saldo_akhir'];
                ?>
                <td style="text-align:right;"> {{ format_price($d['saldo_akhir']) }}</td>
                <td style="text-align:right;">  {{ $d['prosen'] }} %</td>
            @endforeach
            <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
            <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} </strong></td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2"><strong>Total Beban Pokok</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ $bebanPokok[$yearmonth] }}</strong></td>
            <td style="text-align:right;">  <strong>{{ getProsen($bebanPokok[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }}%</strong> </td>
            <?php $t += $bebanPokok[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>
    <tr>
        <td colspan="2"><strong>LABA KOTOR</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ format_price($sum[$yearmonth]) }}</strong></td>
            <td style="text-align:right;"><strong>{{ getProsen($sum[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong></td>
            <?php $t += $sum[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>


     <tr>
        <td colspan="2"></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;">
            </td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>

    <tr>
        <td colspan="2"><strong>BEBAN PENJUALAN</strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;">
            </td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>
    @foreach (collect($data['all_charts'])->where('code_group', '>', 700000)->where('code_group', '<', 800000) as $chart)
        <tr>
            <td>{{ $chart->code_group }}</td>

            <td>{{ $chart->alias_name }}</td>

            <?php $t = 0; ?>
            @foreach ($data['year_month'] as $yearmonth)
                <?php
                $d = $data['msg'][$yearmonth][$chart->code_group];
                $t += $d['saldo_akhir'];
                $bebanPenjualan[$yearmonth] += $d['saldo_akhir'];
                $sum[$yearmonth] += $d['saldo_akhir'];
                ?>
                <td style="text-align:right;"> {{ format_price($d['saldo_akhir']) }}</td>
                <td style="text-align:right;">{{ $d['prosen'] }} %</td>
            @endforeach
            <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
            <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
        </tr>
    @endforeach

    <tr>
        <td colspan="2"><strong>Total Beban Penjualan</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ $bebanPenjualan[$yearmonth] }}</strong></td>
            <td style="text-align:right;">
                <strong>{{ getProsen($bebanPenjualan[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong>
            </td>
            <?php $t += $bebanPenjualan[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>


    <tr>
        <td colspan="2"><strong>BEBAN ADMINISTRASI DAN UMUM</strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;"></td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>
  
      @foreach (collect($data['all_charts'])->where('code_group', '>', 800000)->where('code_group', '<', 900000) as $chart)
        <tr>
            <td>{{ $chart->code_group }}</td>
            <td>{{ $chart->alias_name }}</td>
            <?php $t = 0; ?>
            @foreach ($data['year_month'] as $yearmonth)
                <?php
                $d = $data['msg'][$yearmonth][$chart->code_group];
                $t += $d['saldo_akhir'];
                $bebanAdminUmum[$yearmonth] += $d['saldo_akhir'];
                $sum[$yearmonth] += $d['saldo_akhir'];
                ?>
                <td style="text-align:right;"> {{ format_price($d['saldo_akhir']) }}</td>
                <td style="text-align:right;"> {{ $d['prosen'] }} %</td>
            @endforeach
            <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
            <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
        </tr>
    @endforeach
   
     <tr>
        <td colspan="2"><strong>Total Beban Admin dan Umum</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ format_price($bebanAdminUmum[$yearmonth]) }}</strong></td>
            <td style="text-align:right;"><strong>{{ getProsen($bebanAdminUmum[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} % </strong></td>
            <?php $t += $bebanAdminUmum[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>

    <tr>
        <td colspan="2"><strong>LABA OPERASIONAL</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ format_price($sum[$yearmonth]) }}</strong></td>
            <td style="text-align:right;">
                <strong>{{ getProsen($sum[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong>
            </td>
            <?php $t += $sum[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>

    <tr>
        <td colspan="2"></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;">
            </td>
        @endforeach

        <td> </td>
        <td> </td>
    </tr>

    <tr>
        <td colspan="2"><strong>PENDAPATAN LAIN DAN BEBAN LAIN</strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;"></td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>
    <tr>
        <td colspan="2"><strong>PENDAPATAN LAIN </strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;"></td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>
    @foreach (collect($data['all_charts'])->where('code_group', '>', 900000)->where('code_group', '<', 902000) as $chart)
        <tr>
            <td>{{ $chart->code_group }}</td>

            <td>{{ $chart->alias_name }}</td>

            <?php $t = 0; ?>
            @foreach ($data['year_month'] as $yearmonth)
                <?php
                $d = $data['msg'][$yearmonth][$chart->code_group];
                $t += $d['saldo_akhir'];
                $pendapatanLain[$yearmonth] += $d['saldo_akhir'];
                $sum[$yearmonth] += $d['saldo_akhir'];
                $totalLainLain[$yearmonth] += $d['saldo_akhir'];
                ?>
                <td style="text-align:right;"> {{ format_price($d['saldo_akhir']) }}</td>
                <td style="text-align:right;"> {{ $d['prosen'] }} %</td>
            @endforeach
            <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
            <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2"><strong>Total Pendapatan Lain</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ $pendapatanLain[$yearmonth] }}</strong></td>
            <td style="text-align:right;"> <strong>{{ getProsen($pendapatanLain[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong> </td>
            <?php $t += $pendapatanLain[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>
    <tr>
        <td colspan="2"><strong>BEBAN LAIN</strong></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;">  </td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>
    @foreach (collect($data['all_charts'])->where('code_group', '>', 902000) as $chart)
        <tr>
            <td>{{ $chart->code_group }}</td>
            <td>{{ $chart->alias_name }}</td>
            <?php $t = 0; ?>
            @foreach ($data['year_month'] as $yearmonth)
                <?php
                $d = $data['msg'][$yearmonth][$chart->code_group];
                $t += $d['saldo_akhir'];
                $bebanLain[$yearmonth] += $d['saldo_akhir'];
                $sum[$yearmonth] += $d['saldo_akhir'];
                $totalLainLain[$yearmonth] += $d['saldo_akhir'];
                ?>
                <td style="text-align:right;"> {{ format_price($d['saldo_akhir']) }}</td>
                <td style="text-align:right;"> {{ $d['prosen'] }} %</td>
            @endforeach
            <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
            <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
        </tr>
    @endforeach
    <tr>
        <td colspan="2"><strong>Total Beban Lain</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ format_price($bebanLain[$yearmonth]) }}</strong></td>
            <td style="text-align:right;"><strong>{{ getProsen($bebanLain[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong> </td>
            <?php $t += $bebanLain[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>
    <tr>
        <td colspan="2"><strong>TOTAL Pendapatan dan Beban lain</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"><strong>{{ format_price($totalLainLain[$yearmonth]) }}</strong></td>
            <td style="text-align:right;"> <strong>{{ getProsen($totalLainLain[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong> </td>
            <?php $t += $totalLainLain[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>
    <tr>
        <td colspan="2"></td>
        @foreach ($data['year_month'] as $yearmonth)
            <td colspan="2" style="text-align:right;"></td>
        @endforeach
        <td> </td>
        <td> </td>
    </tr>

    <tr>
        <td colspan="2"><strong>LABA BERSIH BERJALAN</strong></td>
        <?php $t = 0; ?>
        @foreach ($data['year_month'] as $yearmonth)
            <td style="text-align:right;"> <strong>{{ format_price($sum[$yearmonth]) }}</strong> </td>
            <td><strong>{{ getProsen($sum[$yearmonth], $data['msg'][$yearmonth]['penjualan']) }} %</strong> </td>
            <?php $t += $sum[$yearmonth]; ?>
        @endforeach
        <td style="text-align:right;"> <strong>{{ format_price($t) }}</strong></td>
        <td> <strong>{{ getProsen($t, $data['total_penjualan']) }} %</strong></td>
    </tr>

  
</table>

<!-- prettier-ignore-end -->
