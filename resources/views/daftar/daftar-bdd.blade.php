<x-app-layout>
    <div class="container mt-3">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-header bg-white text-white rounded-top-4">
            <h4 class="mb-0 text-black">📋 Table Biaya Dibayar di Muka (BDD)</h4>
          </div>
      
            <style>
              th, td {
                white-space: nowrap;
                background-color: white !important;
                color: inherit;
              }
          
              .sticky-col-1 {
                position: sticky;
                left: 0;
                width: 50px;
                min-width: 50px;
                max-width: 50px;
                z-index: 5;
                text-align: center;
              }
          
              .sticky-col-2 {
                position: sticky;
                left: 50px;
                width: 180px;
                min-width: 180px;
                max-width: 180px;
                z-index: 6;
              }
          
              .sticky-col-3 {
                position: sticky;
                left: 230px;
                width: 80px;
                min-width: 80px;
                max-width: 80px;
                z-index: 7;
                text-align: center;
              }
          
              .sticky-col-4 {
                position: sticky;
                left: 310px;
                width: 100px;
                min-width: 100px;
                max-width: 100px;
                z-index: 8;
                text-align: center;
              }
          
              .sticky-col-5 {
                position: sticky;
                left: 410px;
                width: 100px;
                min-width: 100px;
                max-width: 100px;
                z-index: 9;
                text-align: center;
              }
              .sticky-col-6 {
                position: sticky;
                left: 510px;
                width: 100px;
                min-width: 100px;
                max-width: 100px;
                z-index: 9;
                text-align: center;
              }
            </style>
          
            {{-- Table Pertama --}}
            <div class="p-4">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th class="bg-primary text-white sticky-col-1">No</th>
                      <th class="bg-primary text-white sticky-col-2" style="width: 300px;">Keterangan</th>
                      <th class="bg-primary text-white sticky-col-3" style="width: 100px;">Periode</th>
                      <th class="bg-primary text-white sticky-col-4">bulan</th>
                      <th class="bg-primary text-white sticky-col-5">Amortisasi</th>
                      <th class="bg-primary text-white sticky-col-6">Saldo Awal</th>
                      <th>Jan</th>
                      <th>Feb</th>
                      <th>Mar</th>
                      <th>Apr</th>
                      <th>Mei</th>
                      <th>Jun</th>
                      <th>Jul</th>
                      <th>Agust</th>
                      <th>Sep</th>
                      <th>Okt</th>
                      <th>Nov</th>
                      <th>Des</th>
                      <th>Total Amortisasi</th>
                      <th>saldo akhir</th>
                      <th>A/N</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="sticky-col-1">1</td>
                      <td class="sticky-col-2">Printer Sharkpos</td>
                      <td class="sticky-col-3">2</td>
                      <td class="sticky-col-4">2025</td>
                      <td class="sticky-col-5">Feb</td>
                      <td class="sticky-col-6">2025</td>
                      <td>474.000</td>
                      <td>0</td>
                      <td>474.000</td>
                      <td>9.875</td>
                      <td>9.875</td>
                      <td>9.875</td>
                      <td>9.875</td>
                      <td>19.750</td>
                      <td>19.750</td>
                      <td>454.250</td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
          
                    <!-- Subtotal Baris di Kolom ke-5 -->
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                      <td class="sticky-col-1">-</td>
                      <td class="sticky-col-2">-</td>
                      <td class="sticky-col-3">-</td>
                      <td class="sticky-col-4">-</td>
                      <td class="sticky-col-5">Subtotal</td>
                      <td class="sticky-col-6">-</td>
                      <td>474.000</td>
                      <td>0</td>
                      <td>474.000</td>
                      <td>9.875</td>
                      <td>9.875</td>
                      <td>9.875</td>
                      <td>9.875</td>
                      <td>19.750</td>
                      <td>19.750</td>
                      <td>454.250</td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>-</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </x-app-layout>
      
      
