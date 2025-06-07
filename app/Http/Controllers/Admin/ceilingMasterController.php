<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CeilingMaster;
class ceilingMasterController extends Controller
{
    public function index(){
        return view('admin.ceiling_master.add');
    }
    public function list(){
        $data = ceilingMaster::all();
        return view('admin.ceiling_master.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'sku_id' => 'required',
            'discount_percent' => 'required'
        ]);
        ceilingMaster::create($validatedData);
        return redirect('/admin/ceiling-master-list')->with('message', 'ceiling data is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = ceilingMaster::find($id);
        return view('admin.ceiling_master.edit',compact('data'));
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'sku_id' => 'required',
            'discount_percent' => 'required'
        ]);
        ceilingMaster::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/ceiling-master-list')->with('message', 'ceiling data is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=ceilingMaster::find($id);
        $institution->delete();
        return redirect('/admin/ceiling-master-list')->with('message', 'ceiling data is successfully deleted');
    }
}
