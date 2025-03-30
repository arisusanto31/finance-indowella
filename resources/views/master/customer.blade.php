<x-app-layout>

    <style>
        .btn-custom-blue {
            background-color: #3490dc;
            color: white;
        }
    </style>
    
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <!-- Kiri: Tombol Add Customer -->
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
            <button type="submit" class="btn btn-success btn-sm">
                Cari
            </button>
            </form>
        </div>
    </div>
    
    
    

            <!-- Modal Form -->
            <div class="modal fade" id="backDropModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.master.customer.store') }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Alamat</label>
                                    <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">No HP</label>
                                    <input type="text" id="phone" name="phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="ktp" class="form-label">KTP</label>
                                    <input type="text" id="ktp" name="ktp" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="npwp" class="form-label">NPWP</label>
                                    <input type="text" id="npwp" name="npwp" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="purchase_info" class="form-label">Keterangan Pembelian</label>
                                    <input type="text" id="purchase_info" name="purchase_info" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
                                    
                                            {{-- 👁 Tombol Lihat --}}
                                            <a href="{{ route('customers.trashed') }}" class="btn btn-custom-blue btn-sm" title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                    
                                            {{-- ✏️ Tombol Edit --}}
                                            <a href="#" class="btn btn-success btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                    
                                            {{-- 🗑 Tombol Hapus --}}
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                    
                                        </div>
                                    </td>
                                    
                                    
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data customer</td>
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
