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
                                    <a href="{{ route('stock.trashed') }}" class="btn btn-custom-blue btn-sm" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $stock->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('stock.destroy', $stock->id) }}" method="POST" style="display:inline;">
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
                                    <form action="{{ route('stock.update', $stock->id) }}" method="POST">
                                        @csrf @method('PUT')
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
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data stock</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <!-- Modal Tambah Customer -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="backDropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('stock.store') }}" method="POST">
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
        $('.edit-modal').each(function each(i,elem){
            id= getNumID($(elem).attr('id'));
            initItemSelectManual($(elem), '{{route("stock.category-get-item")}}', 'category', '#editModal'+id);
        });
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