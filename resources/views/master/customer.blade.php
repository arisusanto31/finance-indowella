<x-app-layout>

    <style>
        .btn-custom-blue {
            background-color: #3490dc;
            color: white;
        }
    </style>
    
    <!-- Tombol Add Customer dan Filter -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#backDropModal">
                + Add Customer
            </button>
            <form action="#" method="GET" class="d-flex align-items-center gap-2">
                <select name="bulan" class="form-select form-select-sm" style="min-width: 130px;">
                    <option value="">--Bulan--</option>
                    @foreach(range(1, 12) as $b)
                        <option value="{{ $b }}">{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-select form-select-sm" style="min-width: 100px;">
                    <option value="">--Tahun--</option>
                    @for($year = date('Y'); $year >= 2020; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-success btn-sm">Cari</button>
            </form>
        </div>
    </div>
    
    <!-- Modal Tambah Customer -->
    <div class="modal fade" id="backDropModal" tabindex="-1" aria-labelledby="backDropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="backDropModalLabel">Tambah Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                        <div class="mb-3"><label class="form-label">No HP</label><input type="text" name="phone" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">KTP</label><input type="text" name="ktp" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">NPWP</label><input type="text" name="npwp" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Keterangan Pembelian</label><input type="text" name="purchase_info" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tabel Customer -->
    <div class="table-wrapper card p-3 shadow-sm">
        <h6 class="mb-3">Daftar Customer</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>No HP</th>
                        <th>KTP</th>
                        <th>NPWP</th>
                        <th>Keterangan Pembelian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $index => $customer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $customer->created_at->format('d M Y H:i:s') }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->address }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->ktp }}</td>
                            <td>{{ $customer->npwp }}</td>
                            <td>{{ $customer->purchase_info }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('customers.trashed') }}" class="btn btn-custom-blue btn-sm" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $customer->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
    
                        <div class="modal fade" id="editModal{{ $customer->id }}" tabindex="-1"
                            aria-labelledby="editModalLabel{{ $customer->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel{{ $customer->id }}">
                                                Edit Customer {{ $customer->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3"><label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $customer->name }}">
                                            </div>
                                            
                                            <div class="mb-3"><label class="form-label">Address</label>
                                                <textarea name="address" class="form-control">{{ $customer->address }}</textarea>
                                            </div>
                                            
                                            <div class="mb-3"><label class="form-label">Phone</label>
                                                <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                                            </div>
                                            
                                            <div class="mb-3"><label class="form-label">KTP</label>
                                                <input type="text" name="ktp" class="form-control" value="{{ $customer->ktp }}">
                                            </div>
                                            
                                            <div class="mb-3"><label class="form-label">NPWP</label>
                                                <input type="text" name="npwp" class="form-control" value="{{ $customer->npwp }}">
                                            </div>
                                            
                                            <div class="mb-3"><label class="form-label">Keterangan Pembelian</label>
                                                <input type="text" name="purchase_info" class="form-control" value="{{ $customer->purchase_info }}">
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
                            <td colspan="9" class="text-center">Belum ada data customer</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    
    </x-app-layout>
    