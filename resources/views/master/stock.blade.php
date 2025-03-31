<x-app-layout>
    <style>
        .btn-custom-blue {
            background-color: #3490dc;
            color: white;
        }
    </style>
    

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
</x-app-layout>