<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Mutasi Kartu DP {{ $factur }} - {{ $person->name }}
        <span class="rounded-1 px-2 bg-primary fs-8 text-white">{{ getModel(get_class($person)) }}</span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="table-responsive mt-2">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Nomer Jurnal</th>
                    <th>Amount Debet</th>
                    <th>Amount kredit</th>
                    <th>Saldo </th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row => $item)
                    <tr>
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->description }}</td>
                        <td id="kolom-journal{{ $item->id }}">
                            @if ($item->journal_number)
                                {{ $item->journal_number }}
                            @else
                                <button onclick="searchJournal('{{ $item->id }}')"><i
                                        class="fas fa-search"></i>search link </button>
                                <div id="container-journal{{ $item->id }}"></div>
                            @endif
                        </td>
                        <td>
                            {{ format_price($item->amount_debet) }}
                        </td>
                        <td>
                            {{ format_price($item->amount_kredit) }}
                        </td>
                        <td>
                            {{ format_price($item->amount_saldo_factur) }}
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="refreshKartuDP('{{ $item->id }}')">
                                <i class="fas fa-link"></i>
                            </button>
                            <button class="btn btn-primary btn-sm"
                                onclick = "recalculateKartuDP('{{ $item->id }}')"> <i
                                    class="fas fa-calculator"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>



<script>
    var type = '{{ $type }}';

    function refreshKartuDP(id) {
        url = type == 'sales' ? {{ url('admin/kartu/kartu-dp-sales/refresh') }} / +id :
            '{{ url('admin/kartu/kartu-dp-purchases/refresh') }}/' + id;

        $.ajax({
            url: url,
            method: 'get',

            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', res.msg, 'success');
                    $('#kolom-journal' + id).html(res.journal_number);
                } else {
                    Swal.fire('ops', res.msg, 'error');
                }
            },
            error: function(res) {
                console.log(res);
                Swal.fire('ops', 'something error ' + res.msg, 'error');
            }
        });
    }

    function recalculateKartuDP(id) {
        url = type == 'sales' ? '{{ url('admin/kartu/kartu-dp-sales/recalculate') }}/' + id :
            '{{ url('admin/kartu/kartu-dp-purchases/recalculate') }}/' + id;
            $.ajax({
                url: url,
                method: 'get',
                success: function(res) {
                    if (res.status == 1) {
                        Swal.fire('success', res.msg, 'success');
                    } else {
                        Swal.fire('error', 'something error : ' + res.msg, 'error');
                    }
                },
                error: function(res) {
                    Swal.fire('error', 'something error : ' + res.msg, 'error');
                }
            });
    }

    function searchJournal(id) {
        $.ajax({
            url: '{{ route('kartu-dp-sales.search-link-journal') }}',
            method: 'get',
            success: function(res) {
                console.log(res);
                if (res.status == 1) {

                    html = `
                        <div class="overflow-auto" style="max-height:100px">
                            ${res.msg.map((item) =>`
                                <div >
                                    ${item.journal_number} - ${item.code_group_data.name} - ${item.description} : Rp ${formatRupiah(item.amount_debet- item.amount_kredit)}
                                    <button class="btn btn-sm btn-primary" onclick="linkJournal('${item.id}','${id}')"> <i class="fas fa-link"></i> </button>
                                </div> <hr>                           
                            `).join('')}
                        </div>                             
                    `;

                    console.log(html);
                    $('#container-journal' + id).html(html);
                }

            },
            error: function(res) {
                console.log(res);
                Swal.fire('ops', 'something error ' + res.msg, 'error');
            }
        });
    }


    function linkJournal(journalId, kartuID) {
        swalConfirmAndSubmit({
            url: '{{ route('jurnal.link-journal') }}',
            data: {
                _token: "{{ csrf_token() }}",
                model_id: kartuID,
                journal_id: journalId,
                model: "App\\Models\\KartuDPSales",
            },
            successText: 'berhasil membuat link',
            onSuccess: (res) => {
                if (res.status == 1) {
                    $('#kolom-journal' + kartuID).html(res.msg.journal_number);
                } else {
                    Swal.fire("opss", res.msg, "error");
                }
            }
        });
    }
</script>
