<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institution;
use App\Exports\UsersExport;
use App\Exports\InitiatorExport;
use Excel;
use PDF;
// use Maatwebsite\Excel\Facades\Excel;
class InstitutionController extends Controller
{
    public function index(){
        return view('admin.institution.addInstitution');
    }
    public function list(){
        $data = Institution::all();
        return view('admin.institution.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'institution_name' => 'required|max:255',
            'institution_code' => 'required',
            'key_account_name' => 'required',

            'city' => 'required',
            'region' => 'required',
            'hq' => 'required',

            'zone' => 'required',
            'retailer_name_1' => 'required',
            'retailer_name_2' => 'required',

            'retailer_name_1' => 'required',
            'address' => 'required',
        ]);
        $obj = new Institution;
        $obj->institution_name = $request['institution_name'];
        $obj->institution_code = $request['institution_code'];
        $obj->key_account_name = $request['key_account_name'];
        $obj->city = $request['city'];
        $obj->region = $request['region'];
        $obj->hq = $request['hq'];
        $obj->zone = $request['zone'];
        $obj->retailer_name_1 = $request['retailer_name_1'];
        $obj->retailer_name_2 = $request['retailer_name_2'];
        $obj->retailer_name_3 = $request['retailer_name_3'];
        $obj->address = $request['address'];
        $obj->save();
        return redirect('/admin/list')->with('message', 'Institution data is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = Institution::find($id);
        return view('admin.institution.edit',compact('data'));
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'institution_name' => 'required|max:255',
            'institution_code' => 'required',
            'key_account_name' => 'required',

            'city' => 'required',
            'region' => 'required',
            'hq' => 'required',

            'zone' => 'required',
            'retailer_name_1' => 'required',
            'retailer_name_2' => 'required',

            'retailer_name_1' => 'required',
            'address' => 'required',
        ]);
        Institution::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/list')->with('message', 'Institution data is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=Institution::find($id);
        $institution->delete();
        return redirect('/admin/list')->with('message', 'Institution data is successfully deleted');
    }
    public function export() 
    {
        return Excel::download(new InitiatorExport, 'Initiator-export.csv');
        // return[ 
        //     Excel::download(new InitiatorExport, 'Initiator-export.csv'),
        //     Excel::download(new UsersExport, 'users.csv')
        // ];
    }

}
