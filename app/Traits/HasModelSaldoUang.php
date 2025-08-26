<?php

namespace App\Traits;

use App\Models\ChartAccount;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

trait HasModelSaldoUang
{

    public static function getTotalSaldoRupiah($date, $kolomGroup = 'invoice_pack_number')
    {
        $indexDate = createCarbon($date)->format('ymdHis000');
        $saldo = static::query()->whereIn('index_date', function ($q) use ($indexDate, $kolomGroup) {
            $q->select(DB::raw('max(index_date)'))
                ->from(with(new static)->getTable())
                ->where('book_journal_id', bookID())
                ->where('index_date', '<', $indexDate)
                ->groupBy($kolomGroup, 'person_id', 'person_type');
        })->get();
        $data = collect($saldo)->map(function ($item) use ($kolomGroup) {
            return collect($item)->only('amount_saldo_factur', $kolomGroup, 'id');
        });
        info(static::class . ' ' . json_encode($data));
        $saldo = $saldo->sum('amount_saldo_factur');
        return $saldo ? $saldo : 0;
    }


    public static function getTotalJournal($date)
    {
        $indexDate = createCarbon($date)->format('ymdHis00');
        $coa = ChartAccount::where('reference_model', static::class)->pluck('code_group')->all();
        $sub = Journal::select(DB::raw('max(index_date) as max_index_date'), 'code_group')
            ->where('index_date', '<', $indexDate)
            ->whereIn('code_group', $coa)
            ->groupBy('code_group');

        $journals = Journal::joinSub($sub, 'sub_journals', function ($q) {
            $q->on('journals.index_date', '=', 'sub_journals.max_index_date')
                ->on('journals.code_group', '=', 'sub_journals.code_group');
        })->sum('amount_saldo');
        return $journals ? $journals : 0;
    }


    public static function getSummary($year, $month, $kolomGroup = 'invoice_pack_number')
    {
        $date = $year . '-' . $month . '-01 00:00:00';
        $indexDate = intval(createCarbon($date)->format('ymdHis000'));
        $kartuPiutangAwal = static::query()->whereIn('index_date', function ($q) use ($indexDate, $kolomGroup) {
            $q->from(with(new static)->getTable())->select(DB::raw('max(index_date)'))->where('index_date', '<', $indexDate)->groupBy($kolomGroup);
        })->where('amount_saldo_factur', '<>', 0)->select($kolomGroup, 'invoice_date', 'type', 'amount_saldo_factur', 'person_id', 'person_type')->get();

        $kartuPiutangBaru = static::query()->whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select(
                DB::raw('sum(amount_debet-amount_kredit) as total_amount '),
                $kolomGroup,
                'type',
                'person_id',
                'invoice_date',
                'person_type',
            )->groupBy($kolomGroup, 'type')->get();

        $allFactur = collect($kartuPiutangAwal)->pluck($kolomGroup)->merge(collect($kartuPiutangBaru)->pluck($kolomGroup))->unique()->all();
        $customTable = [];
        foreach ($allFactur as $factur) {
            $dataAktif = $kartuPiutangAwal->where($kolomGroup, $factur)->first();
            $dataBaru = $kartuPiutangBaru->where($kolomGroup, $factur)->first();
            $dataMutasi = optional($kartuPiutangBaru->where($kolomGroup, $factur)->where('type', 'mutasi')->first())->total_amount ?? 0;
            $dataPelunasan = optional($kartuPiutangBaru->where($kolomGroup, $factur)->where('type', 'pelunasan')->first())->total_amount ?? 0;
            $saldoAwal = (optional($dataAktif)->amount_saldo_factur ?? 0);
            $dataFix = $dataAktif ? $dataAktif : $dataBaru;
            $data = [
                'person_name' => $dataFix->person->name,
                'person_type' => $dataFix->person_type,
                'invoice_date' => $dataFix->invoice_date,
                $kolomGroup => $factur,
                'saldo_awal' => $saldoAwal,
                'mutasi' => $dataMutasi,
                'pelunasan' => abs($dataPelunasan),
                'saldo' => $saldoAwal + $dataMutasi + $dataPelunasan
            ];
            $customTable[] = $data;
        }

        return [
            'status' => 1,
            'msg' => $customTable,
            'month' => $month . '-' . $year
        ];
    }

    public static function getMutasi($year, $month, $type)
    {
        $kartu = static::query()
            ->select(
                'invoice_pack_number',
                'person_id',
                'invoice_date',
                'person_type',
                'journal_number',
                'description',
                'created_at',
                DB::raw('amount_debet- amount_kredit as total_amount'),
            )
            ->whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->where('type', $type)
            ->with('person:id,name')
            ->get();
        return ['status' => 1, 'msg' => $kartu];
    }

    public function refreshCurrentSaldo($kolomGroup = 'invoice_pack_number')
    {
        $lastKartu = static::query()->where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where($kolomGroup, $this->$kolomGroup)->where('index_date', '<', $this->index_date)->orderBy('index_date', 'desc')->first();
        $saldo =
            $lastKartu ? $lastKartu->amount_saldo_factur : 0;
        $this->amount_saldo_factur = $saldo + $this->amount_debet - $this->amount_kredit;
        $this->save();
        info('recalculate kartu ' . $this->id . ' saldo ' . $this->amount_saldo_factur);
        return [
            'status' => 1,
            'msg' => 'Saldo berhasil diperbarui',
            'kartu' => $this
        ];
    }
    public function recalculateListSaldo($kolomGroup = 'invoice_pack_number')
    {
        $kartus = static::query()->where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where($kolomGroup, $this->$kolomGroup)->where('index_date', '>', $this->index_date)->get();

        $saldo = $this->amount_saldo_factur;
        foreach ($kartus as $kartu) {
            $saldo = $saldo + $kartu->amount_debet - $kartu->amount_kredit;
            $kartu->amount_saldo_factur = $saldo;
            info('recalculate kartu ' . $kartu->id . ' saldo ' . $kartu->amount_saldo_factur);
            $kartu->save();
        }

        $kartuOrang = static::query()->where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where('index_date', '>', $this->index_date)->get();
        $saldoOrang = $this->amount_saldo_person;
        foreach ($kartuOrang as $kartu) {
            $saldoOrang = $saldoOrang + $kartu->amount_debet - $kartu->amount_kredit;
            $kartu->amount_saldo_person = $saldoOrang;
            $kartu->save();
        }
    }
}
