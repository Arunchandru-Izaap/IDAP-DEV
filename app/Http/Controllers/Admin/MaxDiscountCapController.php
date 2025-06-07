<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticPages\VoluntaryQuotationController;
use App\Models\MaxDiscountCap;
use App\Models\VoluntaryQuotationSkuListing;
use Illuminate\Http\Request;
use Session;

class MaxDiscountCapController extends Controller
{
    public function index(){
        $divArr = VoluntaryQuotationSkuListing::select('div_id')->distinct()->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.max_discount_cap.add',compact('divArr'));
        }
        else
        {
            return view('admin.max_discount_cap.add', compact('divArr'));
        }
    }
    public function list(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $data = MaxDiscountCap::where('year' , $year)->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.max_discount_cap.list',compact('data'));
        }
        else
        {
            return view('admin.max_discount_cap.list',compact('data'));
        }
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
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/max-discount-cap-list')->with('message', 'Discount data is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/max-discount-cap-list')->with('message', 'Discount data is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = MaxDiscountCap::find($id);
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.max_discount_cap.edit',compact('data'));
        }
        else
        {
            return view('admin.max_discount_cap.edit',compact('data'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'max_discount' => 'required'
        ]);

        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        // $dataExists = MaxDiscountCap::where(['div_id' => $request->div_id, 'year' => $year])->exists();
        // if($dataExists){
        //     return back()->withErrors('Max discount for this division already exists! ');
        // }
        MaxDiscountCap::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/max-discount-cap-list')->with('message', 'Discount data is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/max-discount-cap-list')->with('message', 'Discount data is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $institution=MaxDiscountCap::find($id);
        $institution->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/max-discount-cap-list')->with('message', 'Discount data is successfully deleted');
        }
        else
        {
            return redirect('/admin/max-discount-cap-list')->with('message', 'Discount data is successfully deleted');
        }
    }
}
