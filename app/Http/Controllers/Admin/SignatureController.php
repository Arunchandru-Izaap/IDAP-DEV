<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Signature;
use Session;//added on 12082024
class SignatureController extends Controller
{
    public function index(){
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.signatures.add');
        }
        else
        {
            return view('admin.signatures.add');
        }
    }
    public function list(){
        $data = Signature::all();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.signatures.list',compact('data'));
        }
        else
        {
            return view('admin.signatures.list',compact('data'));
        }
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'employee_code' => 'required|max:255|unique:signatures',
            'spil_sign' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'spll_sign' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $obj = new Signature;
        $obj->employee_code = $request['employee_code'];

        $spll_sign = time().rand(1,100).'.'.$request['spll_sign']->extension();  
        $request->spll_sign->move(public_path('images'), $spll_sign);
        $obj->spll_sign=$spll_sign;

        $spil_sign = time().rand(1,100).'.'.$request['spil_sign']->extension();  
        $request->spil_sign->move(public_path('images'), $spil_sign);
        $obj->spil_sign=$spil_sign;


        $obj->save();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/signature-list')->with('message', 'Institution data is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/signature-list')->with('message', 'Institution data is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = Signature::find($id);
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.signatures.edit',compact('data'));
        }
        else
        {
            return view('admin.signatures.edit',compact('data'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'employee_code' => 'required|max:255',
            'spil_sign' => 'required',
            'spll_sign' => 'required',
        ]);
        Signature::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/signature-list')->with('message', 'Institution data is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/signature-list')->with('message', 'Institution data is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $institution=Signature::find($id);
        $institution->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/signature-list')->with('message', 'Institution data is successfully deleted');
        }
        else
        {
            return redirect('/admin/signature-list')->with('message', 'Institution data is successfully deleted');
        }
    }
}
