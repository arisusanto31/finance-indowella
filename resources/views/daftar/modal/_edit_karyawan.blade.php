<form action="{{ route('karyawan.karyawans.update', $karyawan->id) }}" method="POST">
    @csrf
    @method('PUT')

  
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel{{ $karyawan->id }}">Edit Karyawan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
  
          <div class="modal-body">
            <div class="mb-2">
              <label>Nama</label>
              <input type="text" name="nama" value="{{ $karyawan->nama }}" class="form-control">
            </div>
            <div class="mb-2">
              <label>NIK</label>
              <input type="text" name="nik" value="{{ $karyawan->nik }}" class="form-control">
            </div>
            <div class="mb-2">
              <label>NPWP</label>
              <input type="text" name="npwp" value="{{ $karyawan->npwp }}" class="form-control">
            </div>
            <div class="mb-2">
              <label>Jabatan</label>
              <input type="text" name="jabatan" value="{{ $karyawan->jabatan }}" class="form-control">
            </div>
            <div class="mb-2">
              <label>Tanggal Masuk</label>
              <input type="date" name="date_masuk" class="form-control"value="{{ $karyawan->date_masuk }}" readonly>
            </div>
            <div class="mb-2">
              <label>Tanggal Keluar</label>
              <input type="date" name="date_keluar" class="form-control" value="{{ $karyawan->date_keluar }}" readonly>
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
  