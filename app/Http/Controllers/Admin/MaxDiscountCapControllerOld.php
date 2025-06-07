<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticPages\VoluntaryQuotationController;
use App\Models\MaxDiscountCap;
use App\Models\VoluntaryQuotationSkuListing;
use Illuminate\Http\Request;

class MaxDiscountCapController extends Controller
{
    public function index(){
        $divArr = VoluntaryQuotationSkuListing::select('div_id')->distinct()->get();
        return view('admin.max_discount_cap.add', compact('divArr'));
    }
    public function list(){
        $data = MaxDiscountCap::get();
        return view('admin.max_discount_cap.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'div_id' => 'required',
            'max_discount' => 'required'
        ]);

        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        $validatedData['year'] = $year;

        $dataExists = MaxDiscountCap::where(['div_id' => $request->div_id, 'year' => $year])->exists();
        if($dataExists){
            return back()->withErrors('Max discount for this division already exists! ');
        }

        MaxDiscountCap::create($validatedData);
        return redirect('/admin/max-discount-cap-list')->with('message', 'Discount data is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = MaxDiscountCap::find($id);
        return view('admin.max_discount_cap.edit',compact('data'));
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'div_id' => 'required',
            'max_discount' => 'required'
        ]);

        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        $dataExists = MaxDiscountCap::where(['div_id' => $request->div_id, 'year' => $year])->exists();
        if($dataExists){
            return back()->withErrors('Max discount for this division already exists! ');
        }
        MaxDiscountCap::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/max-discount-cap-list')->with('message', 'Discount data is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=MaxDiscountCap::find($id);
        $institution->delete();
        return redirect('/admin/max-discount-cap-list')->with('message', 'Discount data is successfully deleted');
    }
}
