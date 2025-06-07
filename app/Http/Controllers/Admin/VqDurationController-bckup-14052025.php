<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApprovalPeriod;
use Session;
class VqDurationController extends Controller
{
    public function index(){
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.duration.add');
        }
        else
        {
            return view('admin.duration.add');
        }
    }
    public function list(){
        $data = ApprovalPeriod::orderBy('type', 'ASC')->orderBy('level', 'ASC')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.duration.list',compact('data'));
        }
        else
        {
            return view('admin.duration.list',compact('data'));
        }
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'level' => 'required',
            'type' => 'required',
            'days' => 'required'

        ]);
        ApprovalPeriod::create($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/duration-list')->with('message', 'Duration is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/duration-list')->with('message', 'Duration is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = ApprovalPeriod::find($id);
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.duration.edit',compact('data'));
        }
        else
        {
            return view('admin.duration.edit',compact('data'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'level' => 'required',
            'type' => 'required',
            'days' => 'required'
        ]);
        ApprovalPeriod::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/duration-list')->with('message', 'Duration is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/duration-list')->with('message', 'Duration is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $institution=ApprovalPeriod::find($id);
        $institution->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/duration-list')->with('message', 'Duration is successfully deleted');
        }
        else
        {
            return redirect('/admin/duration-list')->with('message', 'Duration is successfully deleted');
        }
    }
}
