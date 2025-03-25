<x-app-layout>

  <style>
 btn-big-custom {
  padding: 20px 40px;
  font-size: 1.5rem;
  border-radius: 8px;
}
  </style>
    <div class="container-fluid px-4 mt-4">

        {{-- ✅ Card 1: Filter & Tombol Tambah --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Jurnal Buku Besar</h5>
                    <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a>
                </div>

                <form method="GET" action="#" class="row g-2">
                    <div class="col-md-3">
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">-- Bulan --</option>
                            <option>November</option>
                            <option>Desember</option>
                            <option>Januari</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">-- Tahun --</option>
                            <option>2023</option>
                            <option>2024</option>
                            <option>2025</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100">Cari</button>
                    </div>
                </form>

            <hr class="my-5" />
            <div class="card">
               <h5 class="card-header">Bordered Table</h5>
               <div class="card-body">
                 <div class="table-responsive text-nowrap">
                   <table class="table table-bordered">
                     <thead>
                       <tr>
                         <th>Project</th>
                         <th>Client</th>
                         <th>Users</th>
                         <th>Status</th>
                         <th>Actions</th>
                       </tr>
                     </thead>
                     <tbody>
                       <tr>
                         <td>
                           <i class="fab fa-angular fa-lg text-danger me-3"></i> <strong>Angular Project</strong>
                         </td>
                         <td>Albert Cook</td>
                         <td>
                           <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                             <li
                               data-bs-toggle="tooltip"
                               data-popup="tooltip-custom"
                               data-bs-placement="top"
                               class="avatar avatar-xs pull-up"
                               title="Lilian Fuller"
                             >
                               <img src="../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle" />
                             </li>
                             <li
                               data-bs-toggle="tooltip"
                               data-popup="tooltip-custom"
                               data-bs-placement="top"
                               class="avatar avatar-xs pull-up"
                               title="Sophia Wilkerson"
                             >
                               <img src="../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle" />
                             </li>
                             <li
                               data-bs-toggle="tooltip"
                               data-popup="tooltip-custom"
                               data-bs-placement="top"
                               class="avatar avatar-xs pull-up"
                               title="Christina Parker"
                             >
                               <img src="../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle" />
                             </li>
                           </ul>
                         </td>
                         <td><span class="badge bg-label-primary me-1">Active</span></td>
                         <td>
                           <div class="dropdown">
                             <button
                               type="button"
                               class="btn p-0 dropdown-toggle hide-arrow"
                               data-bs-toggle="dropdown"
                             >
                               <i class="bx bx-dots-vertical-rounded"></i>
                             </button>
                             <div class="dropdown-menu">
                               <a class="dropdown-item" href="javascript:void(0);"
                                 ><i class="bx bx-edit-alt me-1"></i> Edit</a
                               >
                               <a class="dropdown-item" href="javascript:void(0);"
                                 ><i class="bx bx-trash me-1"></i> Delete</a
                               >
                             </div>
                           </div>
                         </td>
                       </tr>
                       <tr>
                         <td><i class="fab fa-react fa-lg text-info me-3"></i> <strong>React Project</strong></td>
                         <td>Barry Hunter</td>
                         <td>
                           <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                             <li
                               data-bs-toggle="tooltip"
                               data-popup="tooltip-custom"
                               data-bs-placement="top"
                               class="avatar avatar-xs pull-up"
                               title="Lilian Fuller"
                             >
                               <img src="../assets/img/avatars/5.png" alt="Avatar" class="rounded-circle" />
                             </li>
                             <li
                               data-bs-toggle="tooltip"
                               data-popup="tooltip-custom"
                               data-bs-placement="top"
                               class="avatar avatar-xs pull-up"
                               title="Sophia Wilkerson"
                             >
                               <img src="../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle" />
                             </li>
                             <li
                               data-bs-toggle="tooltip"
                               data-popup="tooltip-custom"
                               data-bs-placement="top"
                               class="avatar avatar-xs pull-up"
                               title="Christina Parker"
                             >
                               <img src="../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle" />
                             </li>
                           </ul>
                         </td>
                         <td><span class="badge bg-label-success me-1">Completed</span></td>
                         <td>
                           <div class="dropdown">
                             <button
                               type="button"
                               class="btn p-0 dropdown-toggle hide-arrow"
                               data-bs-toggle="dropdown"
                             >
                               <i class="bx bx-dots-vertical-rounded"></i>
                             </button>
                             <div class="dropdown-menu">
                               <a class="dropdown-item" href="javascript:void(0);"
                                 ><i class="bx bx-edit-alt me-1"></i> Edit</a
                               >
                               <a class="dropdown-item" href="javascript:void(0);"
                                 ><i class="bx bx-trash me-1"></i> Delete</a
                               >
                             </div>
                           </div>
                         </td>
                       </tr>
           </x-app-layout>
