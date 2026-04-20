<style>
    .wrapper-scroll-vertical {
        max-height: 50vh;
        overflow-y: auto;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Detail Pencocokan {{ $model }}
    </h5>


    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-x-12 col-md-4">
            <span> Filter Tanggal </span>
            <input type="text" id="daterange" onchange="changeDateRange(this)" value="{{ createCarbon($startDate)->format('d/m/Y') }} - {{ createCarbon($endDate)->format('d/m/Y') }}" class="form-control">

        </div>
        <div class="clearfix"> </div>
        <div class="col-xs-6 col-md-6 mt-3">
            <h5> {{ $model }}</h5>
            <div class="card p-2 mb-2 br-5 mt-2 bg-primary-lightest wrapper-scroll-vertical" style="height:20vh;">
                <h5> Resume </h5>
                <ul>
                    <li> Total Mutasi : {{format_price(collect($kartus)->sum('amount'))}}</li>
                    <li> List tanpa link :
                        <ul>
                            @foreach ($kartus as $kartu)
                            @if($kartu->journal_id == null)
                            <li>{{$kartu->index_date}} - {{$kartu->id}} - {{ format_price($kartu->amount) }}</li>
                            @endif
                            @endforeach
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="wrapper-scroll-vertical">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>index date</th>
                            <th>kartu ID</th>
                            <th>amount</th>
                            <th>Saldo</th>
                            <th>Journal ID </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2">Saldo Awal</td>
                            <td></td>
                            <td>{{ format_price($lastSaldoKartu) }}</td>
                            <td></td>
                        </tr>
                        @foreach ($kartus as $kartu)
                        @php
                        $lastSaldoKartu+= $kartu->amount;
                        @endphp
                        <tr @if($kartu->journal_id == null) class="bg-danger text-white" @else class="bg-success text-white" @endif>
                            <td>{{ $kartu->index_date }}</td>
                            <td>{{ $kartu->id }}</td>
                            <td>{{ format_price($kartu->amount) }}</td>
                            <td>{{ format_price($lastSaldoKartu) }}</td>
                            <td><i class="fas fa-link"></i> {{ $kartu->journal_id }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-6 col-md-6">
            <h5>Journal</h5>

            <div class="card p-2 mb-2 br-5 mt-2 bg-primary-lightest wrapper-scroll-vertical" style="height:20vh;">
                <h5> Resume </h5>
                <ul>
                    <li> Total Mutasi : {{format_price(collect($journals)->sum('amount'))}}</li>
                    <li> List tanpa link : </li>
                    <ul>
                        @foreach ($journals as $journal)
                        @if($journal->kartu_id == null)
                        <li>{{ $journal->index_date }} - {{ $journal->journal_number }} - {{$journal->id}} - {{ format_price($journal->amount) }}</li>
                        @endif
                        @endforeach
                    </ul>
                </ul>
            </div>
            <div class="wrapper-scroll-vertical">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>index date</th>
                            <th>Journal ID </th>
                            <th>COA</th>
                            <th>Number </th>
                            <th>amount</th>
                            <th>Saldo</th>
                            <th>Kartu ID </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3">Saldo Awal</td>
                            <td></td>
                            <td></td>
                            <td>{{ format_price($lastSaldoJournal) }}</td>
                            <td></td>
                        </tr>
                        @foreach ($journals as $journal)
                        @php
                        $lastSaldoJournal+= $journal->amount;
                        @endphp
                        <tr @if($journal->kartu_id == null) class="bg-danger text-white" @else class="bg-success text-white" @endif>
                            <td>{{ $journal->index_date }}</td>
                            <td> {{ $journal->id }}</td>
                            <td>{{ $journal->code_group }}</td>
                            <td>{{ $journal->journal_number }}</td>
                            <td class="">{{ format_price($journal->amount) }}</td>
                            <td>{{ format_price($lastSaldoJournal) }}</td>
                            <td><i class="fas fa-link"></i> {{ $journal->kartu_id }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

<script>
    initDateRangePicker('#daterange');

    function initDateRangePicker(t) {
        $(t).daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD/MM/YYYY'
            }
        });

        $(t).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(
                picker.startDate.format('DD/MM/YYYY') +
                ' - ' +
                picker.endDate.format('DD/MM/YYYY')
            );
            changeDateRange(this);
        });

        $(t).on('cancel.daterangepicker', function() {
            $(this).val('');
        });
    }

    

    function changeDateRange(input) {
        console.log("woii BERUBAH");
        let dateRange = $(input).val();
        showDetailOnModal('{{url("admin/show-detail-pencocokan")}}?date_range=' + dateRange + '&model={{$model}}', 'xl');

    }
</script>