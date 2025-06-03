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
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="search stock" id="stock-search" />
                </div>
                <div class="col-md-3">
                    <select type="text" class="form-control" id="category-search"></select>
                </div>

                <div class="col-md-2">
                    <button onclick="getStock()" class="btn btn-primary"><i class="fas fa-search"></i>Cari</button>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-md-4">
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"> +
                        Add
                        Stock</button>
                    <button type="button" class=" btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createCategory">+ Add category </button>
                </div>
                <div class="clearfix"></div>
                @if (book()->name == 'Buku Toko')
                    <div class="col-md-4">
                        <button onclick="showLink()" class="btn-primary"> Sinkronkan dengan stock TOKO</button>
                    </div>
                @elseif(book()->name == 'Buku Manufaktur')
                    <div class="col-md-4">
                        <button onclick="showLink()" class="btn-primary"> Sinkronkan dengan stock MANUF</button>
                    </div>
                @endif

            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>



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
                    <tbody id="tbody-stock">

                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <!-- Modal Tambah stock -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="backDropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ url('admin/master/stock/main') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="backDropModalLabel">Tambah Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Category</label>
                            <select id="category-id" name="category_id" class="form-control category-select"
                                rows="2"></select>
                        </div>
                        <div class="mb-3"><label class="form-label">Parent Category</label>
                            <select id="parent-category-id" type="text" name="parent_category_id"
                                class="form-control category-select"></select>
                        </div>

                        <div class="mb-3"><label class="form-label">Type</label>
                            <select id="type" name="type" class="form-control">
                                <option value="bahan baku">Bahan Baku</option>
                                <option value="barang jadi">Barang Jadi</option>
                                <option value="barang dagang">Barang Dagang</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Unit backend <span style="font-size:12px">(satuan
                                    paling kecil)</span></label>
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

    <div class="modal fade" id="createCategory" tabindex="-1" aria-labelledby="backDropModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('stock.category-store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="backDropModalLabel">Tambah Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text"
                                name="name" class="form-control" required></div>
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
            initItemSelectManual('#parent-id', '{{ route('stock.category-get-item') }}', 'parent category', '#createCategory');
            initItemSelectManual('#category-id', '{{ route('stock.category-get-item') }}', 'category', '#createModal');
            initItemSelectManual('#parent-category-id', '{{ route('stock.category-get-item') }}', 'parent category',
                '#createModal');
            $('.edit-modal').each(function each(i, elem) {
                id = getNumID($(elem).attr('id'));
                initItemSelectManual($(elem), '{{ route('stock.category-get-item') }}', 'category', '#editModal' + id);
            });

            function showLink() {
                id = '{{ book()->id }}';
                finalUrl = '{{ route('stock.open-sinkron', ['id' => 'idreplace']) }}';
                finalUrl = finalUrl.replace('idreplace', id);
                showDetailOnModal(finalUrl, 'xl');

            }

            function tambahSatuan(id) {
                $.ajax({
                    url: '{{ route('stock.unit-store') }}',
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
            initItemSelectManual('#category-search', '{{ route('stock.category-get-item') }}', 'category');

            function getStock() {
                category = $('#category-search option:selected').val();
                if (category == null || category == undefined) {
                    category = '';
                }
                console.log(category);
                $.ajax({
                    url: '{{ url('admin/master/stock/get-stock') }}?search=' + $('#stock-search').val() +
                        '&category_id=' + category,
                    method: 'GET',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            thehtml = "";
                            res.msg.forEach(function(item, index) {
                                thehtml += renderStock(item, index);
                            });
                            $('#tbody-stock').html(thehtml);
                        } else {
                            Swal.fire('Gagal', 'Gagal mendapatkan data stock:' + res.msg, 'error');
                        }
                    },
                })
            }

            function renderStock(item, index) {
                stockid = item.id;
                html = `
                        <tr>
                            <td> ${index + 1 }</td>
                            <td>${formatNormalDateTime(new Date(item.created_at))}</td>
                            <td>${item.name}</td>
                            <td>${item.category.name}</td>
                            <td>${item.parent_category.name}
                            <div class="modal fade" id="editModal${item.id}" tabindex="-1"
                                        aria-labelledby="editModalLabel${item.id}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form autocomplete="off" id="form-edit-stock${item.id}">
                                                {{ csrf_field() }}
                                                    
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel${item.id}">
                                                            Edit Stock ${item.name}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3"><label class="form-label">Name</label>
                                                            <input type="text" name="name" class="form-control" value="${item.name}">
                                                        </div>
                                                        <div class="mb-3"><label class="form-label">Category</label>
                                                            <select id="select-cat${item.id}" name="category_id" class="form-control edit-modal">
                                                                <option value="${item.category.id}">${item.category.name}</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3"><label class="form-label">Parent Category</label>
                                                            <select id="select-parent-cat${item.id }" name="parent_category_id" class="form-control edit-modal">
                                                                <option class="" value="${item.parent_category.id}">${item.parent_category.name}</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3"><label class="form-label">Unit backend <span style="font-size:11px">(satuan paling kecil untuk acuan)</span></label>
                                                            <select id="unit-backend" type="text" name="unit_backend" class="form-control">
                                                                <option ${item.unit_backend=='Pcs'?'selected':""} value="Pcs">Pcs</option>
                                                                <option ${item.unit_backend=='Gram'?'selected':""} value="Gram">Gram</option>
                                                                <option ${item.unit_backend=='Meter'?'selected':""} value="Meter">Meter</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3"><label class="form-label">Tipe <span style="font-size:11px">(tipe bahan)</span></label>
                                                            <select id="type" type="text" name="type" class="form-control">
                                                                <option ${item.type=='bahan baku'? 'selected' :""} value="bahan baku">Bahan baku</option>
                                                                <option ${item.type=='barang jadi'? 'selected':""} value="barang jadi">Barang Jadi</option>
                                                                <option ${item.type=='barang dagang'? 'selected':""} value="barang dagang">Barang Dagang</option>

                                                            </select>
                                                        </div>
                                                        <div class="mb-3"><label class="form-label">Unit Default</label>
                                                            <select id="unit-default${item.id}" type="text" name="unit_default" class="form-control">
                                                                ${
                                                                item.units.map(unit => `<option ${item.unit_default == unit.unit ? 'selected' : ''} value="${unit.unit}">${unit.unit}</option>`).join('')}
                                                            </select>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="button" onclick="updateStock('${item.id}')" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </div>
                                                </form>

                                                <div class="modal-header" style="padding-top:0px">
                                                    <h5>Data Satuan </h5>
                                                </div>
                                                <div class="modal-body" style="padding-top:0px; padding-bottom:0px">
                                                    <div class="mb-1 p-3 rounded" style="background-color:#eee;">
                                                        <div id="container-unit${item.id}">

                                                            ${
                                                            item.units.length == 0 ? '<div class="text-center">belum ada satuan apapun</div>' : 
                                                            item.units.map(unit => `
                                                                                <div class="row mb-2">
                                                                                    <div class="col-md-4">
                                                                                        <input class="form-control" placeholder="nama satuan" value="${unit.unit}" />
                                                                                    </div>
                                                                                    <div class="col-md-4">
                                                                                        <div class="row">
                                                                                            <div class="col-xs-12" style="position:relative; width:100%">
                                                                                                <span class="unit-form${item.id}" style="position:absolute; right:20px; top:7px; color:#bbb"> ${item.unit_backend}</span>
                                                                                                <input class="form-control" placeholder="konversi" value="${unit.konversi}" />
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        ${item.unit_default == unit.unit ? '<div class="bg-primary  colorwhite px-2 rounded-1"> default</div>' : ''}
                                                                                    </div>
                                                                                </div>
                                                                                `).join('')}
                                                        </div>
                                                        <div class="mb-1">+ tambah satuan baru</div>
                                                        <form id="create-unit${item.id }">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="stock_id" value="${item.id}" />
                                                            <div class="row align-items-center">
                                                                <div class="col-md-4">
                                                                    <input type="hidden" name="stock_id" value="${item.id}" />
                                                                    <input name="unit" class="form-control" placeholder="nama satuan" />
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="row">
                                                                        <div class="col-xs-12" style="position:relative; width:100%">
                                                                            <span class="unit-form${item.id}" style="position:absolute; right:20px; top:7px; color:#bbb"> ${item.unit_backend}</span>
                                                                            <input name="konversi" class="form-control" placeholder="konversi" value="" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <button onclick="tambahSatuan('${item.id}')" type="button" class="btn btn-success">Tambahkan</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>    
                                
                                
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-success btn-sm" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editModal${item.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ url('admin/master/stock/main/destroy') }}/${item.id}" method="POST" style="display:inline;">
                                        @csrf 
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>                    
                `;
                return html;
            }

            function updateStock(id) {
                $.ajax({
                    url: '{{ url('admin/master/stock/main') }}/' + id,
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
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonText: 'OK'
                });
            </script>
        @elseif(session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'OK'
                });
            </script>
        @endif


    @endpush

</x-app-layout>
