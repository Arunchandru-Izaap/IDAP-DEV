<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stockist_master;
class StockistMasterController extends Controller
{
    public function index(){
        return view('admin.stockist.addStockist');
    }
    public function list(){
        $data = Stockist_master::all();
        return view('admin.stockist.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'stockist_name' => 'required|max:255',
            'email_id' => 'required',
            'stockist_type_flag' => 'required',
        ]);
        Stockist_master::create($validatedData);
        return redirect('/admin/stockist-list')->with('message', 'Stockist data is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = Stockist_master::find($id);
        return view('admin.stockist.edit',compact('data'));
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'stockist_name' => 'required|max:255',
            'email_id' => 'required',
            'stockist_type_flag' => 'required',
        ]);
        Stockist_master::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/stockist-list')->with('message', 'Stockist data is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=Stockist_master::find($id);
        $institution->delete();
        return redirect('/admin/stockist-list')->with('message', 'Stockist data is successfully deleted');
    }
}
