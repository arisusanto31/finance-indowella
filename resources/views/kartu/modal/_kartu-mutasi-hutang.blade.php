<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Mutasi Kartu Hutang {{$factur}} - {{$person->name}}
        <span class="rounded-1 px-2 bg-primary fs-8 text-white">{{getModel(get_class($person))}}</span>
    </h5>
    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="modal"
        aria-label="Close"></button>
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
                    <th>Aksi </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row =>$item)
                <tr>
                    <td>{{$item->created_at}}</td>
                    <td>{{$item->description}}</td>
                    <td id="kolom-journal{{$item->id}}">
                        @if($item->journal_number)
                        {{$item->journal_number}}
                        @else
                        <button onclick="searchJournal('{{$item->id}}')"><i class="fas fa-search"></i>search link </button>
                        <div id="container-journal{{$item->id}}"></div>
                        @endif
                    </td>
                    <td>
                        {{format_price($item->amount_debet)}}
                    </td>
                    <td>
                        {{format_price($item->amount_kredit)}}
                    </td>
                    <td>
                        {{format_price($item->amount_saldo_factur)}}
                    </td>
                    <td>
                        <button title="refresh" onclick="refresh('{{$item->id}}')"><i class="fas fa-refresh"></i></button>
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
    function searchJournal(id) {
        $.ajax({
            url: '{{route("kartu-hutang.search-link-journal")}}',
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


     function refresh(id) {
        $.ajax({
            url: '{{url("admin/kartu/kartu-hutang/refresh")}}/' + id,
            method: 'get',
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', 'berhasil refresh', 'success');

                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                console.log(res);
                Swal.fire("opps", "something error", 'error');
            }
        });
    }


    function linkJournal(journalId, kartuHutangId) {
        swalConfirmAndSubmit({
            url: '{{route("jurnal.link-journal")}}',
            data: {
                _token: "{{ csrf_token() }}",
                model_id: kartuHutangId,
                journal_id: journalId,
                model: "App\\Models\\KartuHutang",
            },
            successText: 'berhasil membuat link',
            onSuccess: (res) => {
                if (res.status == 1) {
                    $('#kolom-journal' + kartuHutangId).html(res.msg.journal_number);
                } else {
                    Swal.fire("opss", res.msg, "error");
                }
            }
        });
    }
</script>