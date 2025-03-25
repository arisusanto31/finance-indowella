<x-app-layout>
    <div class="container mt-3">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-header bg-white text-white rounded-top-4">
            <h4 class="mb-0 text-black">📋 Table Biaya Dibayar di Muka (BDD)</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light text-center">
                  <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>No. Bukti</th>
                    <th>Periode Manfaat</th>
                    <th>Jumlah</th>
                    <th>Sisa (bln)</th>
                    <th>Saldo BDD</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>2025-01-01</td>
                    <td>Sewa Kantor Tahunan</td>
                    <td>INV/001</td>
                    <td>Jan - Des 2025</td>
                    <td class="text-end">Rp12.000.000</td>
                    <td class="text-center">12</td>
                    <td class="text-end">Rp12.000.000</td>
                    <td class="text-center">
                      <button class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>2025-02-15</td>
                    <td>Asuransi Gedung</td>
                    <td>INV/002</td>
                    <td>Feb - Jul 2025</td>
                    <td class="text-end">Rp6.000.000</td>
                    <td class="text-center">6</td>
                    <td class="text-end">Rp6.000.000</td>
                    <td class="text-center">
                      <button class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <!-- Tambah data lain di sini -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      
</x-app-layout>