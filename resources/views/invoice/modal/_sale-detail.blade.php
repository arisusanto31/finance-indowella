<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Detail Sales Order {{ $data->sales_order_number }} -
        {{ $data->customer->name }} <span class="fs-8 px-2 rounded-1 bg-primary text-white">status: {{ $data->status }}
        </span></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

</div>
<div class="modal-body">
    <div class="row">
        <div class="col-xs-12 col-md-12">
            @if ($data['total_kartu'] == 0)
                <p>sales ini belum ada jurnal maupun kartu, kamu bisa batalkan finalnya </p>
                <button class="btn btn-danger" onclick="batalkanFinal('{{ $data->id }}')">Batalkan Final</button>
            @endif
        </div>
        <div class="col-xs-12 col-md-12">
            <h5>List Detail</h5>
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="bg-white text-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga </th>
                        <th>Diskon</th>
                        <th>Kisaran Biaya Bahan </th>
                        <th>Kisaran Biaya lain</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="body-detail-invoice">
                    @foreach ($data['details'] as $key => $item)
                        <tr class="item-tr">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $item->custom_stock_name }}
                                <div class="fs-8 bg-primary px-1 text-white rounded-1">bahan: {{ $item->stock->name }}
                                </div>
                            </td>
                            <td>
                                {{ $item->qtyjadi }} {{ $item->unitjadi }}
                                <div class="fs-8 bg-primary px-1 text-white rounded-1"> {{ $item->quantity }}
                                    {{ $item->unit }} </div>
                            </td>
                            <td>{{ format_price($item->pricejadi) }}
                                <div class="fs-8 bg-primary px-1 text-white rounded-1"> {{ $item->price }} </div>

                            </td>
                            <td>{{ format_price($item->discount) }}</td>
                            <td id="biaya-bahan{{ $item->id }}"> ?? </td>
                            <td id="biaya-lain{{ $item->id }}"> ?? </td>
                            <td>{{ format_price($item->total_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-center">Total</td>
                        <td>{{ format_price($data->total_price) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="col-xs-12 col-md-12" id="div-resume-total">

        </div>


        <div class="col-xs-12 col-md-12" id="div-creation">
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="text-primary-dark"> <a href="javascript:void(toggleDivUangMuka())"> <strong>buat uang
                                muka penjualan</strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-uang-muka"></i> </a>
                    </h5>
                    <div id="" class="tree-toggle bg-primary-lightest card-uang-muka" style="height:80px ;">
                        <div class="row p-2">
                            <div class="col-md-3 col-xs-12">
                                <label>Tanggal </label>
                                <input type="datetime-local" class="form-control" id="uangmuka-date"
                                    value="{{ $dateUangMuka }}" />
                            </div>
                            <div class="col-md-3 col-xs-12">
                                <label>deskripsi jurnal</label>
                                <input type="text" class="form-control" placeholder="deskripsi"
                                    id="uangmuka-description" value="Uang muka {{ $data->customer->name }}" />
                            </div>

                            <div class="col-md-2 col-xs-12">
                                <label>Jumlah</label>
                                <input type="text" class="form-control" placeholder="nilai pembayaran"
                                    value="{{ $data->total_price }}" id="uangmuka-amount" />
                            </div>

                            <div class="col-md-2 col-xs-12">
                                <label>Lawan Akun</label>
                                <select class="select-coa-kas form-control" id="uangmuka-lawanakun">
                                </select>
                            </div>
                            <div class="col-md-2 col-xs-12">
                                <label>Aksi</label>
                                <br>
                                <button onclick="submitUangMuka()" class="btn btn-primary">submit</button>
                            </div>
                        </div>

                    </div>
                    <h5 class="text-primary-dark mb-1"> <a href="javascript:void(toggleDivBDP())">
                            <strong>buat barang dalam proses </strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-bdp"></i> </a>
                    </h5>

                    <div id="" class="tree-toggle mb-3  card-bdp bg-primary-lightest">
                        <div class="row p-2">
                            <div class="col-md-12 col-xs-12">
                                @php $index=1; @endphp
                                @foreach ($data['details'] as $key => $item)
                                    <form id="form-bdp{{ $index }}" class="form-bdp">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="production_number"
                                            value="{{ $data->sales_order_number }}-{{ toDigit($index, 2) }}" />
                                        <input type="hidden" name="sales_order_number"
                                            value="{{ $data->sales_order_number }}" />
                                        <input type="hidden" name="sales_order_id" value="{{ $data->id }}" />
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Tanggal</label>
                                                <input class="form-control" type="datetime-local" name="date"
                                                    value="{{ $dateProses }}" />
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Nama Barang</label>
                                                <select class="form-control select-item-bahan"
                                                    onchange="gantiBahanBDP('{{ $item->id }}')"
                                                    id="bdp-stock_id{{ $item->id }}" name="stock_id[]">
                                                    <option value="{{ $item->stock_id }}" selected>
                                                        {{ $item->stock->name }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 col-xs-12">
                                                <label>Flow</label>
                                                <select class="form-control" id="bdp-flow" name="flow[]">
                                                    <option value="0">Masuk</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 col-xs-12">
                                                <label>Jumlah</label>
                                                <input type="text" class="form-control" name="quantity[]"
                                                    placeholder="qty bahan: {{ $item->quantity }}"
                                                    onchange="updateKisaranBiaya('{{ $item->id }}')"
                                                    value="{{ $item->quantity }}"
                                                    id="bdp-quantity{{ $item->id }}" />
                                            </div>

                                            <div class="col-md-2 col-xs-12">
                                                <label>Satuan</label>
                                                <select class="form-control" type="text" readonly name="unit[]"
                                                    id="bdp-satuan{{ $item->id }}">
                                                    <option value="{{ $item->unit }}" selected>{{ $item->unit }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Dari Akun </Label>
                                                <select id="bdp-akun-persediaan{{ $item->id }}"
                                                    class="select-coa-persediaan form-control"
                                                    onchange="updateKisaranBiaya('{{ $item->id }}')"
                                                    name="lawan_code_group[]">

                                                </select>
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Kisaran Biaya </Label>
                                                <input type="text" id="kisaran-biaya{{ $item->id }}" readonly
                                                    class="form-control" placeholder="kisaran biaya" />
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Dari Barang Nomer? <span class="fs-7">(isi jika perlu)</span>
                                                </Label>
                                                <input type="text" class="form-control" name="spk_number[]"
                                                    value="{{ $data->sales_order_number }}-{{ toDigit($index, 2) }}"
                                                    placeholder="number" id="bdp-spk_number{{ $item->id }}" />
                                            </div>
                                            <div class="col-md-2">
                                                <label>Aksi</label><br>
                                                <button id="bdp-submit{{ $index }}" type="button"
                                                    onclick="submitBDP('{{ $index }}')"
                                                    class="mb-3 btn btn-primary">submit</button>
                                            </div>

                                        </div>
                                    </form>
                                    <hr class="text-primary-dark">
                                    </hr>
                                    @php $index++; @endphp
                                @endforeach
                                <button class="btn btn-primary" id="bdp-submit-all" onclick="submitBDPAll()">
                                    <i class="bx bx-check"></i> submit semua
                                </button>

                            </div>
                            <div class="col-md-2 col-xs-12">

                            </div>
                        </div>


                    </div>
                    <h5 class="text-primary-dark "> <a href="javascript:void(toggleDivBahanJadi())"> <strong>buat
                                bahan jadi</strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-bahan-jadi"></i> </a>
                    </h5>
                    <div id="" class="tree-toggle card-bahan-jadi bg-primary-lightest">
                        <div class="row p-2">
                            <div class="col-md-12 col-xs-12">
                                @php $index=1; @endphp
                                @foreach ($data['details'] as $key => $item)
                                    <form id="form-bahan-jadi{{ $index }}" class="form-bahan-jadi">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="sales_order_number"
                                            value="{{ $data->sales_order_number }}" />
                                        <input type="hidden" name="sales_order_id" value="{{ $data->id }}" />
                                        <input type="hidden" name="production_number"
                                            value="{{ $data->sales_order_number }}-{{ toDigit($index, 2) }}" />


                                        <div class="row p-2">
                                            <div class="col-md-3">
                                                <label>Tanggal</label>
                                                <input class="form-control" type="datetime-local" name="date"
                                                    value="{{ $dateFinished }}" />
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Nama Barang</label>
                                                <input type="hidden" id="bahan-jadi-stock_id" name="stock_id[]"
                                                    value="{{ $item->stock_id }}" />
                                                <input type="hidden" id="" name="sales_detail_id[]"
                                                    value="{{ $item->id }}" />

                                                <input type="text" class="form-control" id="bahan-jadi-stock_name"
                                                    value="{{ $item->stock->name }}" readonly />

                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Nama Custom</label>
                                                <input type="text" class="form-control"
                                                    id="bahan-jadi-custom_name" name="custom_stock_name[]"
                                                    value="{{ $item->custom_stock_name }}" />

                                            </div>

                                            <div class="col-md-2 col-xs-12">
                                                <label>Flow</label>
                                                <select class="form-control" id="bdp-flow" name="flow[]">
                                                    <option value="0">Masuk</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 col-xs-12">
                                                <label>Jumlah Jadi</label>
                                                <input type="text" class="form-control" name="quantity[]"
                                                    placeholder="qty jadi : {{ $item->qtyjadi }}"
                                                    id="bahan-jadi-quantity" value="{{ $item->qtyjadi }}" />
                                                <input type="hidden" name="konversi_jadi[]"
                                                    id="bahan-jadi-konversijadi"
                                                    value="{{ $item->qtyjadi / $item->quantity }}" />
                                            </div>

                                            <div class="col-md-2 col-xs-12">
                                                <label>Satuan</label>
                                                <input class="form-control" type="text" readonly name="unit[]"
                                                    id="bahan-jadi-satuan" value="{{ $item->unit }}" />
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Dari Akun </Label>
                                                <select class="select-coa-persediaan form-control"
                                                    name="lawan_code_group[]">
                                                    <option value="140003" selected> Persediaan Barang Dalam Proses
                                                    </option>

                                                </select>
                                            </div>
                                            <div class="col-md-3 col-xs-12">
                                                <label>Dari Barang Nomer? <span class="fs-7">(isi jika perlu)</span>
                                                </Label>
                                                <input type="text" class="form-control" name="spk_number[]"
                                                    placeholder="number" id="bahan-jadi-spk_number"
                                                    value="{{ $data->sales_order_number }}-{{ toDigit($index, 2) }}" />
                                            </div>
                                            <div class="col-md-2 col-xs-12">
                                                <label>Aksi</label> <br>
                                                <button type="button" id="bahan-jadi-submit{{ $index }}"
                                                    onclick="submitBahanJadi('{{ $index }}')"
                                                    class="btn btn-primary">submit</button>
                                            </div>

                                    </form>
                            </div>
                            <hr class="text-primary-dark">
                            </hr>
                            @php $index++; @endphp
                            @endforeach
                            <button class="btn btn-primary" id="bahan-jadi-submit-all"
                                onclick="submitBahanJadiAll()">
                                <i class="bx bx-check"></i> submit semua
                            </button>

                        </div>
                    </div>

                </div>
                <h5 class="text-primary-dark"> <a href="javascript:void(toggleDivInvoice())"> <strong>buat
                            Invoice</strong>
                        <i id="icon-create" class="bx bx-caret-down toggle-icon card-invoice"></i> </a>
                </h5>
                <div id="" class="tree-toggle card-invoice bg-primary-lightest">
                    <div class="row p-2">
                        <div class="col-md-12 col-xs-12">
                            <form id="form-invoice-so">
                                {{ csrf_field() }}
                                <input type="hidden" name="sales_order_number"
                                    value="{{ $data->sales_order_number }}" />
                                <input type="hidden" name="sales_order_id" value="{{ $data->id }}" />

                                @foreach ($data['details'] as $key => $item)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Tanggal</label>
                                            <input class="form-control" type="datetime-local" name="date"
                                                value="{{ $dateFinished }}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Nama Barang</label>
                                            <input type="text" class="form-control" id="invoice-stock_name"
                                                value="{{ $item->stock->name }}" readonly />
                                            <input type="hidden" id="invoice-stock_id{{ $item->id }}"
                                                name="stock_id[]" value="{{ $item->stock_id }}" />
                                            <input type="hidden" name="sales_detail_id[]"
                                                value="{{ $item->id }}" />
                                        </div>
                                        <div class="col-md-2 col-xs-12">
                                            <label>Jumlah</label>
                                            <input type="text" class="form-control qty-invoice" name="quantity[]"
                                                placeholder="qty: {{ $item->qtyjadi }}"
                                                id="invoice-quantity{{ $item->id }}"
                                                value="{{ $item->qtyjadi }}" />
                                        </div>

                                        <div class="col-md-1 col-xs-12">
                                            <label>Satuan</label>
                                            <input class="form-control" type="text" name="unit[]" readonly
                                                id="invoice-unit" value="{{ $item->unitjadi }}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Akun Penjualan</label>
                                            <select class="form-control select-coa-penjualan" type="text"
                                                name="code_group_penjualan[]"></select>
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Akun Piutang</label>
                                            <select class="form-control select-coa-piutang" type="text"
                                                name="code_group_piutang[]">
                                                <option value="120001" selected> Piutang Usaha Offline</option>

                                            </select>
                                        </div>

                                        <div class="col-md-9 col-xs-12">
                                            <label>Barang jadi</label>
                                            <input type="text" class="form-control"
                                                id="invoice-ket-barang-jadi{{ $item->id }}" value=""
                                                readonly />
                                            <input type="hidden" name="custom_stock_name[]"
                                                id="invoice-custom_stock_name{{ $item->id }}" value="" />
                                            <input type="hidden" name="production_number[]"
                                                id="invoice-production_number{{ $item->id }}" value="" />
                                        </div>
                                        {{-- <div class="col-md-3 col-xs-12">
                                            <label>Pembiayaan HPP</label>
                                            <input type="text" id="invoice-biaya_hpp{{ $item->id }}"
                                                class="form-control" placeholder="pembiayaan hpp" name="hpp[]"
                                                readonly />
                                        </div> --}}
                                    </div>
                                    <hr>
                                @endforeach
                            </form>
                            <div class="col-md-2 col-xs-12">
                                <button id="invoice-submit" onclick="submitInvoice()"
                                    class="btn btn-primary">submit</button>
                            </div>
                        </div>
                    </div>
                </div>
                <h5 class="text-primary-dark"> <a href="javascript:void(toggleDivBayarInvoice())"> <strong>buat
                            pembayaran invoice</strong>
                        <i id="icon-create" class="bx bx-caret-down toggle-icon card-bayar-invoice"></i> </a>
                </h5>
                <div id="" class="tree-toggle card-bayar-invoice mb-3 bg-primary-lightest">
                    <form id="form-bayar-invoice">
                        {{ csrf_field() }}
                        <div class="row p-2">
                            <div class="col-md-3">
                                <label>Tanggal</label>
                                <input class="form-control" type="datetime-local" name="date"
                                    value="{{ $dateFinished }}" />
                            </div>
                            <div class="col-md-3 col-xs-12">
                                <label>Nomer Invoice</label>
                                <select class="form-control" name="invoice_number" id="bayar-invoice-invoice_number">

                                </select>
                            </div>

                            <div class="col-md-2 col-xs-12">
                                <label>Jumlah bayar</label>
                                <input type="text" class="form-control" placeholder="nilai pembayaran"
                                    name="amount" id="bayar-invoice-amount" value="{{ $data->total_price }}" />
                            </div>

                            <div class="col-md-3 col-xs-12">
                                <label>Akun Piutang</label>
                                <select class="select-coa-piutang form-control" name="codegroup_piutang"
                                    id="bayar-invoice-akun-piutang">
                                    <option value="120001" selected> Piutang Usaha Offline</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xs-12">
                                <label>Akun Pembayaran</label>
                                <select class="select-coa-kas-uangmuka form-control" name="codegroup_bayar"
                                    id="bayar-invoice-akun-pembayaran">
                                    <option value="214000" selected>Uang Muka Penjualan </option>
                                </select>
                            </div>
                            <div class="col-md-1 col-xs-12">
                                <label>Aksi</label>
                                <br>
                                <button id="bayar-invoice-submit" type="button" onclick="submitBayarInvoice()"
                                    class="btn btn-primary">submit</button>
                            </div>
                        </div>
                    </form>

                </div>


            </div>
        </div>

        <div class="col-xs-12 col-md-12" id="div-resume-kartu">


        </div>



    </div>
</div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>

<script>
    var data = @json($data);
    setTimeout(function() {
        renderResumeKartu(data);
        renderResumeTotal(data);
    }, 100);

    function batalkanFinal(id) {
        swalConfirmAndSubmit({
            url: '{{ url('admin/invoice/sales-cancel-final') }}',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
        });
    }

    function renderResumeKartu(data) {


        console.log('data kartu', data);
        html = `
        <div class="card mt-4">
         <div class="card-body">
         <div class="row">
            <h6>Kartu Kartu </h6>
                ${count(data['kartus'])> 0?`
                ${collect(data['kartus']).map((itemsType,key)=> `
                        <div class="col-xs-12">
                            <span class="text-white bg-primary-dark ps-2">${key}</span>
                            <div class="row bg-primary-light p-2 mb-2">
                                <div class="col-xs-12 col-md-6 p-2">
                                    <h6 class="text-white">Debet</h6>
                                    <div class="row text-white">
                                       ${array_key_exists('debet', itemsType)?`
                                              ${collect(itemsType['debet']).map((item)=>`
                                                  <div class="col-xs-12 col-md-6 ">
                                                        <div class="bg-primary-dark p-2 ">
                                                            <p>${item.date}
                                                                <strong>${item.code_group_name}</strong> :
                                                                ${formatRupiah(Math.abs(item.amount_journal))} <span
                                                                    class="fs-8">[journal_id :
                                                                    ${item.journal_id}, kartu_id=
                                                                    ${item.kartu_id}]</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                              
                                              `).join('')}
                                               
                                            `:''
                                         }
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-6 p-2 ">
                                    <h6 class="text-white">Kredit</h6>
                                    <div class="row text-white">
                                        ${array_key_exists('kredit', itemsType)?`
                                              ${collect(itemsType['kredit']).map((item)=>`
                                                  <div class="col-xs-12 col-md-6 ">
                                                        <div class="bg-primary-dark p-2 ">
                                                            <p>${item.date}
                                                                <strong>${item.code_group_name}</strong> :
                                                                ${formatRupiah(Math.abs(item.amount_journal))} <span
                                                                    class="fs-8">[journal_id :
                                                                    ${item.journal_id}, kartu_id=
                                                                    ${item.kartu_id}]</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                              
                                              `).join('')}
                                               
                                            `:''
                                         }
                                    </div>
                                </div>
                            </div>
                        </div>
                
                
                `).join('')}
                `:``}
            </div>
            </div>
            </div>
        `;
        $('#div-resume-kartu').html(html);
    }



    function renderResumeTotal(data) {
        console.log('data total', data['resume_total']);
        html = `
          <div class="card mt-4">
                <div class="card-body">
                    <h5 class="text-primary-dark mb-2"> <strong>Resume Total </strong>
                        ${count(data['resume_total'])>0 ?`
                    
                            ${collect(data['resume_total']).map((total,key)=>`
                                <p class="mb-0 mt-2 pb-2" style="font-size:15px; "> <i class="fas fa-circle"></i>
                                    ${key}</p>
                                <p class="mb-0 pb-2 fs-7 ps-3"> ${formatRupiah(Math.round(total*100)/100)}</p>`
                            ).join('')}
                        `:''}
                </div>
            </div>
        `;
        $('#div-resume-total').html(html);
    }

    function getDataKartu() {
        $.ajax({
            url: '{{ url('admin/invoice/get-data-kartu') }}/{{ $data->sales_order_number }}',
            method: 'get',
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    renderResumeKartu(res.msg);
                    renderResumeTotal(res.msg);
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire('ops', 'something error get data kartu', 'error');
            }
        });
    }

    function initAllItem() {
        setTimeout(function() {
            initItemSelectManual('#select-pcoa-persediaan',
                '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('#select-pcoa-piutang-kas',
                '{{ route('chart-account.get-item-keuangan') }}?kind=piutang|kas', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('#select-pcoa-penjualan',
                '{{ route('chart-account.get-item-keuangan') }}?kind=penjualan', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('.select-coa-persediaan',
                '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('#select-coa-hutang-kas',
                '{{ route('chart-account.get-item-keuangan') }}?kind=hutang|kas', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('.select-coa-kas', '{{ route('chart-account.get-item-keuangan') }}?kind=kas',
                '- pilih akun -', '#global-modal #div-creation');
            initItemSelectManual('.select-coa-penjualan',
                '{{ route('chart-account.get-item-keuangan') }}?kind=penjualan', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('.select-coa-piutang',
                '{{ route('chart-account.get-item-keuangan') }}?kind=piutang', '- pilih akun -',
                '#global-modal #div-creation');
            initItemSelectManual('.select-item-bahan',
                '{{ route('stock.get-item') }}', '- pilih bahan -', '#global-modal #div-creation');
            initItemSelectManual('.select-coa-kas-uangmuka',
                '{{ route('chart-account.get-item-keuangan') }}?kind=kas|uang_muka_penjualan',
                '- pilih akun -', '#global-modal #div-creation');
        }, 350);


    }
    initAllItem();




    function gantiBahanBDP(id) {
        stockid = $('#bdp-stock_id' + id + ' option:selected').val();
        $.ajax({
            url: '{{ url('admin/master/stock/get-unit') }}/' + stockid,
            method: 'get',
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    html = "";
                    res.msg.forEach(function eachItem(item) {
                        html += '<option value="' + item.unit + '" >' + item.unit + '</option>';
                    });
                    $('#bdp-satuan' + id).html(html);
                } else {

                }
            },
            error: function(res) {
                Swal.fire("opps", "something error get unit", 'error');
            }
        });
    }

    function updateKisaranBiaya(id) {
        qty = $('#bdp-quantity' + id).val();
        unit = $('#bdp-satuan' + id).val();
        persediaanID = $('#bdp-akun-persediaan' + id + ' option:selected').val();
        stockID = $('#bdp-stock_id' + id).val();
        productionNumber = $('#bdp-spk_number' + id).val();
        $.ajax({
            url: '{{ url('admin/invoice/hitung-kisaran-biaya') }}',
            method: 'post',
            data: {
                _token: '{{ csrf_token() }}',
                quantity: qty,
                unit: unit,
                stock_id: stockID,
                production_number: productionNumber,
                code_persediaan: persediaanID
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    $('#kisaran-biaya' + id).val(formatRupiah(res.msg));
                } else {

                }
            },
            error: function(res) {

            }

        });
    }

    function hitungReferenceBiaya() {

        data = <?php echo json_encode($data['details']); ?>;
        console.log(data);

        allIDs = collect(data).pluck('id').all();
        $.ajax({
            url: '{{ url('admin/invoice/hitung-reference-biaya') }}',
            method: 'post',
            data: {
                _token: '{{ csrf_token() }}',
                ids: allIDs
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    res.msg.forEach(function eachItem(item) {
                        item.hpp = item.hpp == 0 ? 0 : item.hpp;
                        item.subkon = item.subkon == 0 ? 0 : item.subkon;
                        $('#biaya-bahan' + item.id).html(formatRupiah(item.hpp));
                        $('#biaya-lain' + item.id).html(formatRupiah(item.subkon));
                    });
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {}
        });
    }

    setTimeout(function() {
        hitungReferenceBiaya();
    }, 500);



    function toggleDivUangMuka() {
        $('.card-uang-muka').toggleClass('open');
        if ($('.card-uang-muka').hasClass('open')) {
            // initAllItem();
        }
    }

    function toggleDivBDP() {
        $('.card-bdp').toggleClass('open');
        if ($('.card-bdp').hasClass('open')) {
            // initAllItem();
        }
    }

    function toggleDivBahanJadi() {
        $('.card-bahan-jadi').toggleClass('open');
        if ($('.card-bahan-jadi').hasClass('open')) {
            // initAllItem();
        }
    }

    function toggleDivInvoice() {
        $('.card-invoice').toggleClass('open');
        if ($('.card-invoice').hasClass('open')) {
            updateInputInvoice('{{ $data->sales_order_number }}');
            // initAllItem();
        }
    }

    function toggleDivBayarInvoice() {
        $('.card-bayar-invoice').toggleClass('open');
        if ($('.card-bayar-invoice').hasClass('open')) {
            getInvoiceAktif('{{ $data->id }}');
            // initAllItem();
        }
    }


    var dataBahanJadi = [];


    // function updateBiaya(id) {
    //     let qty = $('#invoice-quantity' + id).val();
    //     let stock_id = $('#invoice-stock_id' + id).val();
    //     let bahanJadi = dataBahanJadi[id];
    //     if (bahanJadi != undefined) {
    //         let biaya = bahanJadi.saldo_rupiah_total / (bahanJadi.saldo_qty_backend * bahanJadi.mutasi_quantity /
    //             bahanJadi.mutasi_qty_backend) * qty;
    //         $('#invoice-biaya_hpp' + id).val(formatRupiah(biaya));
    //     }
    // }



    function updateInputInvoice(number) {
        $.ajax({
            url: '{{ url('admin/invoice/update-input-invoice') }}/' + number,
            method: 'get',
            data: {
                sales_order_number: number
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    res.msg.forEach(function eachItem(item, i) {
                        bahanJadi = res.bahan_jadi[item.sales_order_number + '-' + toDigit(i + 1,
                            2)];
                        if (bahanJadi != undefined) {
                            dataBahanJadi[item.id] = bahanJadi;
                            html =
                                `${bahanJadi.custom_stock_name} : ${bahanJadi.saldo_qty_backend/bahanJadi.mutasi_qty_backend*bahanJadi.mutasi_quantity} ${bahanJadi.unit} = ${formatRupiah(bahanJadi.saldo_rupiah_total)} `;
                            $('#invoice-ket-barang-jadi' + item.id).val(html)
                            $('#invoice-custom_stock_name' + item.id).val(bahanJadi
                                .custom_stock_name);
                            $('#invoice-production_number' + item.id).val(bahanJadi
                                .production_number);
                        }
                    });
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function getInvoiceAktif(number) {
        initItemSelectManual('#bayar-invoice-invoice_number', '{{ url('admin/invoice/get-item-invoice-aktif') }}/' +
            number, '- pilih invoice -', '#global-modal #div-creation');
    }

    function submitUangMuka() {
        let description = $('#uangmuka-description').val();
        let amount = formatDB($('#uangmuka-amount').val());
        let lawanakun = $('#uangmuka-lawanakun option:selected').val();
        let factur = '{{ $data->sales_order_number }}';
        let date = formatNormalDateTime(new Date($('#uangmuka-date').val()));

        swalConfirmAndSubmit({
            url: '{{ url('admin/kartu/kartu-dp-sales/create-mutation') }}',
            data: {
                description: description,
                amount_mutasi: amount,
                lawan_code_group: lawanakun,
                sales_order_number: factur,
                person_id: '{{ $data->customer_id }}',
                person_type: 'App\\\Models\\\Customer',
                code_group: 214000,
                date: date,
                is_otomatis_jurnal: 1,
                _token: '{{ csrf_token() }}'
            },
            onSuccess: (res) => {
                console.log(res);
                getDataKartu();
                setTimeout(function() {
                    updateStatusRow('{{ $data->id }}');
                }, 1000);


            }
        });


    }

    function submitBDP(i) {
        console.log($('#form-bdp' + i).serialize());
        $('#bdp-submit' + i).prop('disabled', true);
        $('#bdp-submit' + i).html('<i class="bx bx-loader bx-spin"></i> loading...');
        swalConfirmAndSubmit({
            url: '{{ url('admin/kartu/kartu-bdp/create-mutations') }}',
            data: $('#form-bdp' + i).serialize(),
            onSuccess: function(res) {
                console.log(res);
                getDataKartu();
                $('#bdp-submit' + i).html('<i class="fas fa-check"></i> berhasil');
                $('#bdp-submit' + i).removeClass('btn-primary').addClass('btn-success');

                setTimeout(function() {
                    updateStatusRow('{{ $data->id }}');
                }, 1000);
            },
        });
    }

    var iBDP = 1;

    function submitBDPAll() {
        iBDP = 1;
        prosesStateBDP();
    }

    function prosesStateBDP() {
        $('#bdp-submit' + iBDP).prop('disabled', true);
        $('#bdp-submit' + iBDP).html('<i class="bx bx-loader bx-spin"></i> loading...');
        $.ajax({
            url: '{{ url('admin/kartu/kartu-bdp/create-mutations') }}',
            method: 'post',
            data: $('#form-bdp' + iBDP).serialize(),
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    $('#bdp-submit' + iBDP).html('<i class="fas fa-check"></i> berhasil');
                    $('#bdp-submit' + iBDP).removeClass('btn-primary').addClass('btn-success');
                    iBDP++;
                    if (iBDP <= $('.form-bdp').length) {
                        setTimeout(prosesStateBDP, 50);

                    } else {
                        $('#bdp-submit-all').prop('disabled', true);
                        $('#bdp-submit-all').html('<i class="fas fa-check"></i> selesai');
                        $('#bdp-submit-all').removeClass('btn-primary').addClass('btn-success');
                        getDataKartu();
                        setTimeout(function() {
                            updateStatusRow('{{ $data->id }}');
                        }, 300);
                    }
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                    $('#bdp-submit' + iBDP).prop('disabled', false);
                    $('#bdp-submit' + iBDP).html('submit');
                }
            },
            error: function(res) {}
        });
    }


    var iBahanJadi = 1;

    function submitBahanJadiAll() {
        iBahanJadi = 1;
        prosesStateBahanJadi();
    }

    function prosesStateBahanJadi() {
        $('#bahan-jadi-submit' + iBahanJadi).prop('disabled', true);
        $('#bahan-jadi-submit' + iBahanJadi).html('<i class="bx bx-loader bx-spin"></i> loading...');
        $.ajax({
            url: '{{ url('admin/kartu/kartu-bahan-jadi/create-mutations') }}',
            method: 'post',
            data: $('#form-bahan-jadi' + iBahanJadi).serialize(),
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    $('#bahan-jadi-submit' + iBahanJadi).html('<i class="fas fa-check"></i> berhasil');
                    $('#bahan-jadi-submit' + iBahanJadi).removeClass('btn-primary').addClass('btn-success');
                    iBahanJadi++;
                    if (iBahanJadi <= $('.form-bahan-jadi').length) {
                        setTimeout(prosesStateBahanJadi, 50);

                    } else {
                        $('#bahan-jadi-submit-all').prop('disabled', true);
                        $('#bahan-jadi-submit-all').html('<i class="fas fa-check"></i> selesai');
                        $('#bahan-jadi-submit-all').removeClass('btn-primary').addClass('btn-success');
                        getDataKartu();
                        setTimeout(function() {
                            updateStatusRow('{{ $data->id }}');
                        }, 300);
                    }
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                    $('#bahan-jadi-submit' + iBahanJadi).prop('disabled', false);
                    $('#bahan-jadi-submit' + iBahanJadi).html('submit');
                }
            },
            error: function(res) {}
        });
    }

    function submitBahanJadi(i) {
        swalConfirmAndSubmit({
            url: '{{ url('admin/kartu/kartu-bahan-jadi/create-mutations') }}',
            data: $('#form-bahan-jadi' + i).serialize(),
            onSuccess: function(res) {
                console.log(res);
                getDataKartu();
                setTimeout(function() {
                    updateStatusRow('{{ $data->id }}');
                }, 1000);


            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function submitInvoice() {

        $('#invoice-submit').prop('disabled', true);
        $('#invoice-submit').html('<i class="bx bx-loader bx-spin"></i> loading...');
        $.ajax({
            url: '{{ url('admin/invoice/create-invoices') }}',
            data: $('#form-invoice-so').serialize(),
            method: 'post',
            success: function(res) {
                getDataKartu();
                $('#invoice-submit').html('<i class="fas fa-check"></i> berhasil');
                $('#invoice-submit').removeClass('btn-primary').addClass('btn-success');

                setTimeout(function() {
                    updateStatusRow('{{ $data->id }}');
                }, 1000);

            },
            error: function(res) {
                $('#invoice-submit').prop('disabled', false);
                $('#invoice-submit').html('submit');
            }
        });
    }

    function submitBayarInvoice() {
        $('#bayar-invoice-submit').prop('disabled', true);
        $('#bayar-invoice-submit').html('<i class="bx bx-loader bx-spin"></i> loading...');
        $.ajax({
            url: '{{ url('admin/invoice/submit-bayar-sales-invoice') }}',
            data: $('#form-bayar-invoice').serialize(),
            method: 'post',
            success: function(res) {
                console.log(res);
                getDataKartu();
                $('#bayar-invoice-submit').html('<i class="fas fa-check"></i> berhasil');
                $('#bayar-invoice-submit').removeClass('btn-primary').addClass('btn-success');
                setTimeout(function() {
                    updateStatusRow('{{ $data->id }}');
                }, 1000);
            },
            error: function(res) {
                $('#bayar-invoice-submit').prop('disabled', false);
                $('#bayar-invoice-submit').html('submit');
            }
        });
    }
</script>
