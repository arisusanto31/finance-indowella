<?php

namespace App\Http\Controllers;

use App\Models\OtherPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Validator;



class OtherPersonController extends Controller
{

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required',
                'address' => 'required',
            ]);
    
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
    
            OtherPerson::create($validator->validated());
    
            return redirect()
                ->route('other-person.main.index')
                ->with('success', 'Other Person berhasil ditambahkan lurr!');
        } catch (ValidationException $e) {
            return redirect()
                ->route('other-person.main.index')
                ->with('error', 'Input tidak valid! ' . $this->getValidationMessage($e))
                ->withInput();
        } catch (\Throwable $e) {
            return redirect()
                ->route('other-person.main.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getValidationMessage(ValidationException $e)
    {
        return implode(', ', collect($e->errors())->flatten()->toArray());
    }


    public function create()
    {
        return view('master.modal._create_other-person');
    }

    public function index()
    {
        $otherPersons = OtherPerson::whereNull('is_deleted')->get();
        return view('master.other-person', compact('otherPersons'));
    }
    

    public function getItem()
    {
        $searchs = explode(' ', getInput('search'));
        $supps = OtherPerson::from('other_persons');
        foreach ($searchs as $s) {
            $supps->where('name', 'like', '%' . $s . '%');
        }
        $supps = $supps->select('id', DB::raw('name as text'))->get();
        return [
            'results' => $supps
        ];
    }

    public function update(Request $request, $id)
    {
        $otherPerson = OtherPerson::findOrFail($id);
    
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
        ]);
    
        $otherPerson->update([
            'name' => $request->nama,
            'address' => $request->alamat,
            'phone' => $request->no_hp,
        ]);
    
        return redirect()->route('other-person.main.index')
        ->with('success', 'Data berhasil diperbarui!');
}
    
public function destroy($id)
{
    $otherPerson = OtherPerson::find($id);
    $otherPerson->is_deleted = 1;
    $otherPerson->deleted_at = date('Y-m-d H:i:s');
    $otherPerson->save();

    return [
        'status' => 1,
        'msg' => 'Success delete'
    ];
}


    public function trashed()
    {
        $trashed = OtherPerson::onlyTrashed()->get();
        return view('other_persons.trashed', compact('trashed'));
    }


    public function restore($id)
    {
        $person = OtherPerson::withTrashed()->findOrFail($id);
        $person->update(['is_deleted' => 0]); 
        $person->restore();

        return redirect()->route('other-persons.index')->with('success', 'Data berhasil dipulihkan.');
    }
 
    public function edit($id)
{
    $otherPerson = OtherPerson::findOrFail($id);
    return view('master.modal._edit_other-person', compact('otherPerson'));
}


}

