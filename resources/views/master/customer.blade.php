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
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 🦸 <strong>CUSTOMER</strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-2">
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"> + Add Customer</button>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <div class="row">

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
            </div>


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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $index => $customer)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $customer->created_at?$customer->created_at->format('d M Y H:i:s'):'' }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->address }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->ktp }}</td>
                        <td>{{ $customer->npwp }}</td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('customer.trashed') }}" class="btn btn-custom-blue btn-sm" title="Lihat">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-success btn-sm" title="Edit"
                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $customer->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('customer.main.destroy', $customer->id) }}" method="POST" style="display:inline;">
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
                                <form action="{{ route('customer.main.update', $customer->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel{{ $customer->id }}">
                                            Edit Customer {{ $customer->name }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $customer->name }}"></div>
                                        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ $customer->phone }}"></div>
                                        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ $customer->address }}</textarea></div>
                                        <div class="mb-3"><label class="form-label">KTP</label><input type="text" name="ktp" class="form-control" value="{{ $customer->ktp }}"></div>
                                        <div class="mb-3"><label class="form-label">NPWP</label><input type="text" name="npwp" class="form-control" value="{{ $customer->npwp }}"></div>
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
    </div>
    <!-- Modal Tambah Customer -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="backDropModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('customer.main.store') }}" method="POST">
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

    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success")}}',
            confirmButtonText: 'OK'
        });
    </script>
    @endif

    

    @endpush

</x-app-layout>