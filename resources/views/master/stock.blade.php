<x-app-layout>

    @push('styles')
    <style>
        .btn-custom-blue {
            background-color: #3490dc;
            color: white;
        }
    </style>

    @endpush


    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 🍱 <strong>STOCK</strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-4">
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"> + Add Stock</button>
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#createCategory">+ Add category </button>
                </div>
                <div class="clearfix"></div>
                @if(book()->name=='Buku Toko')
                <div class="col-md-4">
                    <button onclick="showLink()" class="btn-primary"> Sinkronkan dengan stock TOKO</button>
                </div>
                @elseif(book()->name=='Buku Manufaktur')
                <div class="col-md-4">
                    <button onclick="showLink()" class="btn-primary"> Sinkronkan dengan stock MANUF</button>
                </div>
                @endif

            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <!-- <div class="row">

                <div class="col-md-2">
                    <select name="bulan" id="month" class="form-select ">
                        <option value="">-- Bulan --</option>
                        @foreach(getListMonth() as $key => $month)
                        <option value="{{$key}}">{{$month}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tahun" id="year" class="form-select ">
                        <option value="">-- Tahun --</option>
                        @for($year=0; $year < 3; $year++)
                            <option value="{{intval(Date('Y')-$year)}}">{{intval(Date('Y')-$year)}}</option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button onclick="getSummary()" class="btn btn-primary btn-sm w-100">Cari</button>
                </div>
            </div> -->


            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Created</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Parent Category</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $index => $stock)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $stock->created_at->format('d M Y H:i:s') }}</td>
                            <td>{{ $stock->name }}</td>
                            <td>{{ $stock->category->name }}</td>
                            <td>{{ $stock->parentCategory->name }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- <a href="{{ route('stock.trashed') }}" class="btn btn-custom-blue btn-sm" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a> -->
                                    <button type="button" class="btn btn-success btn-sm" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $stock->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('stock.main.destroy', $stock->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="editModal{{ $stock->id }}" tabindex="-1"
                            aria-labelledby="editModalLabel{{ $stock->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form autocomplete="off" id="form-edit-stock{{$stock->id}}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel{{ $stock->id }}">
                                                Edit Stock {{ $stock->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3"><label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $stock->name }}">
                                            </div>
                                            <div class="mb-3"><label class="form-label">Category</label>
                                                <select id="select-cat{{$stock->id}}" name="category_id" class="form-control edit-modal">
                                                    <option value="{{ $stock->category->id }}">{{ $stock->category->name }}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3"><label class="form-label">Parent Category</label>
                                                <select id="select-parent-cat{{$stock->id}}" name="parent_category_id" class="form-control edit-modal">
                                                    <option class="" value="{{ $stock->parentCategory->id }}">{{ $stock->parentCategory->name }}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3"><label class="form-label">Unit backend <span style="font-size:11px">(satuan paling kecil untuk acuan)</span></label>
                                                <select id="unit-backend" type="text" name="unit_backend" class="form-control">
                                                    <option @if($stock->unit_backend=="Pcs") selected @endif value="Pcs">Pcs</option>
                                                    <option @if($stock->unit_backend=="Gram") selected @endif value="Gram">Gram</option>
                                                    <option @if($stock->unit_backend=="Meter") selected @endif value="Meter">Meter</option>
                                                </select>
                                            </div>
                                            <div class="mb-3"><label class="form-label">Unit Default</label>
                                                <select id="unit-default{{$stock->id}}" type="text" name="unit_default" class="form-control">
                                                    @foreach($stock->units as $dataunit)
                                                    <option @if($stock->unit_default==$dataunit->unit) selected @endif value="{{$dataunit->unit}}">{{$dataunit->unit}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="button" onclick="updateStock('{{$stock->id}}')" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="modal-header" style="padding-top:0px">
                                        <h5>Data Satuan </h5>
                                    </div>
                                    <div class="modal-body" style="padding-top:0px; padding-bottom:0px">
                                        <div class="mb-1 p-3 rounded" style="background-color:#eee;">
                                            <div id="container-unit{{$stock->id}}">

                                                @forelse($stock->units as $unit)
                                                <div class="row mb-2">
                                                    <div class="col-md-4">
                                                        <input class="form-control" placeholder="nama satuan" value="{{ $unit->unit }}" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row">
                                                            <div class="col-xs-12" style="position:relative; width:100%">
                                                                <span class="unit-form{{$stock->id}}" style="position:absolute; right:20px; top:7px; color:#bbb"> {{$stock->unit_backend}}</span>
                                                                <input class="form-control" placeholder="konversi" value="{{ $unit->konversi }}" />

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        @if($stock->unit_default==$unit->unit)
                                                        <div class="bg-primary  colorwhite px-2 rounded-1"> default</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @empty
                                                <div>belum ada satuan apapun</div>
                                                @endforelse
                                            </div>
                                            <!-- <p class="mt-3 fw-bold" sytle="margin-bottom:0px; padding-bottom:0px">+ tambah satuan baru</p> -->
                                            <div class="mb-1">+ tambah satuan baru</div>
                                            <form id="create-unit{{$stock->id}}">
                                                {{csrf_field()}}
                                                <input type="hidden" name="stock_id" value="{{ $stock->id }}" />
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        <input type="hidden" name="stock_id" value="{{ $stock->id }}" />
                                                        <input name="unit" class="form-control" placeholder="nama satuan" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row">
                                                            <div class="col-xs-12" style="position:relative; width:100%">
                                                                <span class="unit-form{{$stock->id}}" style="position:absolute; right:20px; top:7px; color:#bbb"> {{$stock->unit_backend}}</span>
                                                                <input name="konversi" class="form-control" placeholder="konversi" value="" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <button onclick="tambahSatuan('{{$stock->id}}')" type="button" class="btn btn-success">Tambahkan</button>
                                                    </div>
                                                </div>
                                            </form>


                                        </div>

                                    </div>


                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada data stock</td>
                                    </tr>
                                    @endforelse
                                </div>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <!-- Modal Tambah stock -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="backDropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{url('admin/master/stock/main')}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="backDropModalLabel">Tambah Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Category</label>
                            <select id="category-id" name="category_id" class="form-control category-select" rows="2"></select>
                        </div>
                        <div class="mb-3"><label class="form-label">Parent Category</label>
                            <select id="parent-category-id" type="text" name="parent_category_id" class="form-control category-select"></select>
                        </div>
                        <div class="mb-3"><label class="form-label">Unit backend <span style="font-size:12px">(satuan paling kecil)</span></label>
                            <select id="unit-backend" type="text" name="unit_backend" class="form-control">
                                <option value="Pcs">Pcs</option>
                                <option value="Gram">Gram</option>
                                <option value="Meter">Meter</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCategory" tabindex="-1" aria-labelledby="backDropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('stock.category-store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="backDropModalLabel">Tambah Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Parent</label>
                            <select id="parent-id" name="parent_id" class="form-control" rows="2"></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        initItemSelectManual('#parent-id', '{{route("stock.category-get-item")}}', 'parent category', '#createCategory');
        initItemSelectManual('#category-id', '{{route("stock.category-get-item")}}', 'category', '#createModal');
        initItemSelectManual('#parent-category-id', '{{route("stock.category-get-item")}}', 'parent category', '#createModal');
        $('.edit-modal').each(function each(i, elem) {
            id = getNumID($(elem).attr('id'));
            initItemSelectManual($(elem), '{{route("stock.category-get-item")}}', 'category', '#editModal' + id);
        });

        function showLink() {
            id= '{{book()->id}}';
            finalUrl = '{{route("stock.open-sinkron",["id"=>"idreplace"])}}';
            finalUrl = finalUrl.replace('idreplace', id);
            showDetailOnModal(finalUrl, 'xl');

        }

        function tambahSatuan(id) {
            $.ajax({
                url: '{{route("stock.unit-store")}}',
                method: 'POST',
                data: $('#create-unit' + id).serialize(),
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        Swal.fire('Berhasil', 'satuan berhasil ditambah', 'success');
                        updateContainerUnit(id, res.msg, res.stock);
                    } else {
                        Swal.fire('Gagal', 'satuan gagal ditambah:' + res.msg, 'error');
                    }
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire('opps', "Gagal menambah satuan", 'error');
                }
            });
        }

        function updateStock(id) {
            $.ajax({
                url: '{{url("admin/master/stock/main")}}/' + id,
                method: 'POST',
                data: $('#form-edit-stock' + id).serialize(),
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        $('#editModal' + id).modal('hide');
                        Swal.fire('Berhasil', 'Stock berhasil diupdate', 'success');
                        $('.unit-form' + id).html(res.msg.unit_backend);
                    } else {
                        Swal.fire('Gagal', 'Stock gagal diupdate:' + res.msg, 'error');
                    }
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire('opps', "Gagal mengupdate stock", 'error');
                }
            });
        }

        function updateContainerUnit(id, data, stock = "") {
            html = '';
            data.forEach((item, index) => {
                html += `
                <div class="row mb-2">
                    <div class="col-md-4">
                        <input class="form-control" placeholder="nama satuan" value="${item.unit}" />
                    </div>
                    <div class="col-md-4">
                       <div class="row">
                          <div class="col-xs-12" style="position:relative; width:100%">
                            <span class="unit-form${id}" style="position:absolute; right:20px; top:7px; color:#bbb"> ${stock.unit_backend}</span>
                            <input class="form-control" placeholder="konversi" value="${item.konversi}" />
                          </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                        ${stock.unit_default==item.unit?'<div class="bg-primary  colorwhite px-2 rounded-1"> default</div>':""}
                    </div>
                </div>
                `;
            });
            $('#container-unit' + id).html(html);
            html = '';
            data.forEach((item, index) => {
                html += `<option value="${item.unit}">${item.unit}</option>`;
            });
            $('#unit-default' + id).html(html);
        }
    </script>
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success")}}',
            confirmButtonText: 'OK'
        });
    </script>

    @elseif(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session("error")}}',
            confirmButtonText: 'OK'
        });
    </script>
    @endif


    @endpush

</x-app-layout>