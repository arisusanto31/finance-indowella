<x-app-layout>

  <div class="card mt-3 rounded-3 mb-3">
    <h5 class="text-primary-dark card-header"> 🔎 <strong> Preview Import</strong> </h5>
    <div class="card-body">
      <form id="form-data" method="POST">
        @csrf
        <p>Saldo Neraca Lajur</p>
        <div class="table-responsive">
          <table id="" class="table table table-bordered table-striped table-hover align-middle">
            <thead class="bg-white text-dark text-center">
              <tr>
                <th>No</th>
                <th>Kode COA </th>
                <th>Nama COA </th>
                <th>Nilai Saldo</th>
              </tr>
            </thead>
            <tbody id="body-import-saldo">
              @foreach($data['saldo_nl'] as $key => $item)
              @if($key>0 && $item[0] !='')
              <tr>
                <td>{{$key}}</td>
                <td>{{$item[0]}}
                  <input type="hidden" id="saldo-code_group{{$key}}" class="saldo" name="saldo['kode'][]" value="{{$item[0]}}" />
                </td>
                <td>{{$item[1]}}
                  <span class="badge bg-primary text-white">{{ array_key_exists($item[0],$coas)?$coas[$item[0]] :'??'}}
                  </span>
                  <input type="hidden" id="saldo-name{{$key}}" value="{{$item[1]}}" />
                </td>
                <td>{{format_price(floatval($item[2]))}}
                  <input type="hidden" id="saldo-amount{{$key}}" name="saldo['amount'][]" value="{{floatval($item[2])}}" />
                </td>
              </tr>
              @endif
              @endforeach
            </tbody>


          </table>
        </div>

        <p class="mt-2">Data Saldo Stock</p>
        <div class="table-responsive">
          <table id="" class="table table table-bordered table-striped table-hover align-middle">
            <thead class="bg-white text-dark text-center">
              <tr>
                <th>No</th>
                <th>Ref StockID</th>
                <th>Nama Stock </th>
                <th>Saldo Qty </th>
                <th>Satuan</th>
                <th>Nilai Saldo</th>
              </tr>
            </thead>
            <tbody id="body-import-saldo">
              @foreach($data['kartu_stock'] as $key => $item)
              @if($key>0 && $item[2] !='')
              <tr>
                @php
                $name = preg_replace('/\s*\[\d+\]$/', '', $item[2]);

                @endphp
                <td>{{$key}}</td>
                <td>{{$item[1]}}
                  <input type="hidden" id="stock-ref_id{{$key}}" value="{{$item[1]}}" />
                </td>
                <td>{{$name}}
                  @if(!in_array($name,$stocks) && !in_array($item[1], $stockRefs))
                  <span class="badge bg-primary text-white"> NEW </span>
                  @endif
                  <input type="hidden" value="{{$name}}" id="stock-name{{$key}}" class="stock" name="stock['name'][]" />
                </td>

                <td>{{$item[4]}}
                  <input type="hidden" name="stock['qty'][]" id="stock-qty{{$key}}" value="{{$item[4]}}" />
                </td>
                <td>
                  {{$item[3]}}
                  <input type="hidden" name="stock['satuan'][]" id="stock-satuan{{$key}}" value="{{$item[3]}}" />
                </td>
                <td>{{format_price(floatval($item[6]))}}
                  <input type="hidden" name="stock['amount'][]" id="stock-amount{{$key}}" value="{{floatval($item[6])}}" />
                </td>
              </tr>
              @endif
              @endforeach
            </tbody>


          </table>

          <div class="row">
            <div class="col-xs-12">
              <button type="button" onclick="submitFormImport()" class="btn btn-primary mt-2" style="width:100%">Import</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>


  @push('scripts')
  <script>
    function submitFormImport() {
      // let formData = $('#form-data').serialize();
      dataSaldo = [];
      $('.saldo').each(function(i, elem) {
        id = getNumID($(elem).attr('id'));
        dataSaldo.push({
          code: $(elem).val(),
          amount: $('#saldo-amount' + id).val(),
          name: $('#saldo-name' + id).val()
        });
      });

      dataStock = [];
      $('.stock').each(function(i, elem) {
        id = getNumID($(elem).attr('id'));
        dataStock.push({
          name: $(elem).val(),
          ref_id: $('#stock-ref_id' + id).val(),
          qty: $('#stock-qty' + id).val(),
          satuan: $('#stock-satuan' + id).val(),
          amount: $('#stock-amount' + id).val()
        });
      });
      let jsonData = JSON.stringify({
        saldo: dataSaldo,
        stock: dataStock,
      });
      let encoded = btoa(unescape(encodeURIComponent(jsonData))); // encode UTF-8 aman


      swalConfirmAndSubmit({
        url: '{{ route("jurnal.import-saldo")}}',
        data: {
          _token: '{{csrf_token()}}',
          data: encoded,
          date: '{{$date}}'
        },
        onSuccess: function(res) {
          console.log(res);
          if (res.status == 1) {
            location.href = '{{url("admin/jurnal/get-import-saldo-followup")}}/' + res.msg.id;
          }
        }
      });
      // $.ajax({
      //   url: ' {{ route("jurnal.import-saldo")}}',
      //   type: 'POST',
      //   data: formData,
      //   processData: false,
      //   contentType: false,
      //   success: function(response) {
      //     if (response.status == 'success') {
      //       alert('Import Berhasil');
      //       window.location.reload();
      //     } else {
      //       alert('Import Gagal');
      //     }
      //   },
      //   error: function(xhr, status, error) {
      //     console.error(xhr.responseText);
      //     alert('Terjadi kesalahan saat mengimpor data.');
      //   }
      // });
    }
  </script>
  @endpush
</x-app-layout>