<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpecialPrice;
class SpecialPriceController extends Controller
{
    public function index(){
        return view('admin.special_price.add');
    }
    public function list(){
        $data = SpecialPrice::all();
        return view('admin.special_price.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'sku_id' => 'required',
            'discount_percent' => 'required',
            'discount_rate' => 'required',
        ]);
        SpecialPrice::create($validatedData);
        return redirect('/admin/special-price-list')->with('message', 'Specia price data is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = SpecialPrice::find($id);
        return view('admin.special_price.edit',compact('data'));
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'sku_id' => 'required',
            'discount_percent' => 'required',
            'discount_rate' => 'required',
        ]);
        SpecialPrice::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/special-price-list')->with('message', 'Specia price data is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=SpecialPrice::find($id);
        $institution->delete();
        return redirect('/admin/special-price-list')->with('message', 'Specia price data is successfully deleted');
    }
}
