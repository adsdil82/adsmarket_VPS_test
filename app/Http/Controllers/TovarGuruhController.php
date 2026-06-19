<?php
namespace App\Http\Controllers;

use App\Models\TovarGuruh;
use Illuminate\Http\Request;

class TovarGuruhController extends Controller
{
    public function index()
    {
        $guruhlar = TovarGuruh::withCount('tovarlar')->orderBy('nomi')->paginate(20);
        return view('ombor.guruhlar.index', compact('guruhlar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'   => 'required|string|max:150',
            'tavsif' => 'nullable|string',
            'holat'  => 'in:faol,nofaol',
        ]);
        TovarGuruh::create($data);
        return back()->with('muvaffaqiyat', "Guruh «{$data['nomi']}» yaratildi.");
    }

    public function update(Request $request, TovarGuruh $guruh)
    {
        $data = $request->validate([
            'nomi'   => 'required|string|max:150',
            'tavsif' => 'nullable|string',
            'holat'  => 'in:faol,nofaol',
        ]);
        $guruh->update($data);
        return back()->with('muvaffaqiyat', 'Guruh yangilandi.');
    }

    public function destroy(TovarGuruh $guruh)
    {
        if ($guruh->tovarlar()->count() > 0) {
            return back()->with('xato', "Guruhda tovarlar mavjud — o'chirib bo'lmaydi.");
        }
        $guruh->delete();
        return back()->with('muvaffaqiyat', 'Guruh o\'chirildi.');
    }
}
