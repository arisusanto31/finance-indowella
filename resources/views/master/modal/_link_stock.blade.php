<div class="modal-header">
    <div class="flex-column align-items-start">
        <h5 class="modal-title" id="exampleModalLabel1">Sinkronkan Stock</h5>
        <button class="btn btn-primary" onclick="syncStockAll()"> <i class="fas fa-refresh"></i> Sync All</button>
    </div>
    <button
        type="button"
        class="btn-close position-absolute end-0 top-0 m-3"
        data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="table-responsive mt-2">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>unit default</th>
                    <th>Kategori <span class="px-2 text-white bg-primary"> PARENT</span></th>
                    <th>Semua Unit</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocks as $i=>$stock)
                <tr id="{{$stock->id}}">
                    <td>{{$i+1}}</td>
                    <td>{{$stock->name}}</td>
                    <td>{{$stock->unit_default}}</td>
                    <td>{{$stock->category->name}}
                        <span class="px-2 text-white bg-primary">{{$stock->parentCategory->name}}</span>
                    </td>
                    <td>
                        @foreach($stock->units_manual as $theunit)
                        <p class="fs-8 mb-0"> <i class="fas fa-circle"></i> {{$theunit->unit}} : {{$theunit->konversi}} {{$stock->unit_backend}} </p>
                        @endforeach
                    </td>
                    <td id="kolom-status{{$stock->id}}">
                        <button class="btn btn-primary btn-sync" onclick="syncStock('{{$stock->id}}')">Sync</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    </div>
</div>

<script>
    var dataStocks = @json($stocks);
    dataStocks = collect(dataStocks).keyBy('id').all();


    function syncStockAll() {
        Swal.fire({
            title: "Apakah kamu yakin ?",
            text: "Ini mungkin membutuhkan waktu karena harus update satu persatu",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            allowOutsideClick: false,
            didOpen: () => {
                $('.swal2-container').css('z-index', 2000);
            },
            preConfirm: () => {
                goSync();
            }
        });
    }

    async function goSync() {
        id = $('.btn-sync').first().closest('tr').attr('id');
        console.log(id);
        if (id == undefined) {
            swalInfo('success', 'sync stock success', 'success');
            return;
        } else {
            await syncStock(id, false);
            goSync();
        }
    }


    function syncStock(id, swal = true) {
        return new Promise(function(resolve, reject) {
            $('#kolom-status' + id).html('<i class="fas fa-spinner fa-spin"></i>');

            let stock = dataStocks[id];
            let url = "{{ route('stock.sync') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    data: stock,
                    stock_id: id
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == 1) {
                        $('#kolom-status' + id).html('<p class="text-success"><i class="fas fa-check"></i> berhasil</p>');
                        if (swal) swalInfo('success', 'sync stock success', 'success');
                        resolve(response); // sukses resolve
                    } else {
                        $('#kolom-status' + id).html('<p class="text-danger"><i class="fas fa-close"></i> gagal!!</p>');
                        if (swal) swalInfo('error', response.msg, 'error');
                        reject(response); // gagal reject
                    }
                },
                error: function(xhr, status, error) {
                    if (swal) swalInfo('something error', 'error');
                    reject({
                        xhr,
                        status,
                        error
                    }); // reject kalau AJAX error
                }
            });
        });
    }
</script>