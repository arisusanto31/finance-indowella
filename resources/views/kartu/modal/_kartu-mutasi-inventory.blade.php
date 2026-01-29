<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Mutasi Inventaris {{ $inventory->name }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="table-responsive mt-2">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>uid </th>
                    <th>Keterangan</th>
                    <th>Nomer Jurnal</th>
                    <th>pembelian</th>
                    <th>penyusutan</th>
                    <th>nilai buku </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row => $item)
                    <tr>
                        <td>{{ createCarbon($item->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            {{ $item->uid }}
                        </td>
                        <td>{{ $item->description }}</td>
                        <td>
                            {{ $item->journal_number }}
                        </td>
                        <td class="text-success">
                            @if ($item->amount > 0)
                                {{ format_price($item->amount) }}
                                {{ $item->unit }}
                            @endif
                        </td>

                        <td class="text-danger">
                            @if ($item->amount < 0)
                                {{ format_price(abs($item->amount)) }}
                                {{ $item->unit }}
                            @endif

                        </td>
                       
                        <td>
                            {{ format_price($item->nilai_buku) }}

                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr >
                    <th colspan="4" class="text-end">Total & saldo</th>
                    <th class="text-success">
                        {{ format_price($data->where('amount','>',0)->sum('amount')) }}
                     
                    </th>
                    <th class="text-danger">
                        {{ format_price(abs($data->where('amount','<',0)->sum('amount'))) }}
                  
                    </th>
               
                    <th style="font-weight: bold;" class="fs-5">
                        {{ format_price($data->last()->nilai_buku) }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
