<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\JwtToken;//added on 31072024
use Session;//added on 31072024
use App\Jobs\InstitutionDivisionMappingEmployee;//added on 31072024
use App\Models\VoluntaryQuotationSkuListingStockist;
use DB;
use Illuminate\Validation\Rule;
class EmployeeController extends Controller
{
    public function index(){
        $division_name = DB::table('brands')->select('div_name','div_id')->groupBy('div_id')->orderBy('div_name')->get();
        $data['division_name'] = $division_name;
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.employee.add',compact('data'));
        }
        else
        {
            return view('admin.employee.add',compact('data'));
        }
    }
    public function list(){
        $data = Employee::all();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.employee.list',compact('data'));
        }
        else
        {
            return view('admin.employee.list',compact('data'));
        }
    }
    public function store(Request $request){
        // modified the validation for emp_code and div_code at 07102025 
        $validatedData = $request->validate([
            //'emp_code' => 'required|unique:employee_master',
            'emp_code' => ['required'],
            'emp_name' => ['required'],
            'emp_email' => ['required'],
            'manager_name' => ['required'],
            'manager_email' => ['required'],
            'emp_category' => ['required'],
            'div_code' => [
                'required',
                Rule::unique('employee_master')->where(function ($query) use ($request) {
                    return $query->where('emp_code', $request->emp_code);
                }),
            ],
        ], [
            'div_code.unique' => 'This employee is already assigned to this  division.', // '. $request->div_code .'
        ]);

        $obj = new Employee;
        $obj->emp_code = $request['emp_code'];
        $obj->emp_name = $request['emp_name'];
        $obj->emp_email = $request['emp_email'];
        $obj->manager_name = $request['manager_name'];
        $obj->manager_email = $request['manager_email'];
        $obj->emp_category = $request['emp_category'];
        $obj->div_name = $request['div_name'];  
        $obj->div_code = $request['div_code'];
        if($request['emp_category'] == 'approver'){
            $validatedData = $request->validate([
                'div_code' => 'required'
            ], [
                'div_code.required' => 'The div name field is required.'
            ]);
            $obj->div_code = $request['div_code'];
            $obj->div_name = $request['div_name'];
            $obj->emp_type = $request['emp_type'];
            $obj->div_type = $request['div_type'];
            if($request['emp_type'] == 'RSM'){
                $obj->emp_level = 'L1';

            }elseif($request['emp_type'] == 'ZSM'){
                $obj->emp_level = 'L2';

            }elseif($request['emp_type'] == 'NSM'){
                $obj->emp_level = 'L3';
            }
            elseif($request['emp_type'] == 'SBU'){
                $obj->emp_level = 'L4';

            }elseif($request['emp_type'] == 'Semi Cluster'){
                $obj->emp_level = 'L5';

            }elseif($request['emp_type'] == 'Cluster'){
                $obj->emp_level = 'L6';
            }
        }else if($request['emp_category'] == 'poc'){
            $obj->emp_type = $request['emp_type']; 
            $validatedData = $request->validate([
                'emp_number' => 'required',
                'emp_ho' => 'required'
            ]);
            $obj->emp_number = $request['emp_number'];
            $obj->emp_ho = $request['emp_ho'];
        }else if($request['emp_category'] == 'ceo'){
            $obj->emp_level = 'L8';
            $obj->emp_type = 'CEO'; 
        }
        
        $obj->save();
        //update institution div mapping after adding employee starts
        if($request['emp_type'] == 'ZSM' || $request['emp_type'] == 'RSM')
        {
            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
            $this->dispatch(new InstitutionDivisionMappingEmployee($request['div_code'],$request['emp_type'],$request['emp_code'],$jwt->jwt_token));
        }
        //update institution div mapping after adding employee ends
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/employee-list')->with('message', 'Institution data is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/employee-list')->with('message', 'Institution data is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = Employee::find($id);
        $division_name = DB::table('brands')->select('div_name','div_id')->groupBy('div_id')->orderBy('div_name')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.employee.edit',compact('data','division_name'));
        }
        else
        {
            return view('admin.employee.edit',compact('data','division_name'));
        }
    }
    public function update(Request $request){
        // modified the validation for emp_code and div_code at 07102025 
        $validatedData = $request->validate([
            'emp_code' => ['required'],
            'emp_name' => ['required'],
            'emp_email' => ['required'],
            'manager_name' => ['required'],
            'manager_email' => ['required'],
            'div_code' => [
                'required',
                Rule::unique('employee_master')->where(function ($query) use ($request) {
                    return $query->where('emp_code', $request->emp_code);
                })->ignore($request['id']), // Ignore the current record
            ],
        ], [
            'div_code.unique' => 'This employee is already assigned to this  division.', // '. $request->div_code .'
        ]);
        //dd($validatedData);
	    $validatedData['div_name'] = $request['div_name'];
        $validatedData['div_code'] = $request['div_code'];
        $validatedData['emp_name'] = $request['emp_name'];      
        $validatedData['emp_email'] = $request['emp_email'];
        $validatedData['manager_name'] = $request['manager_name'];
        $validatedData['manager_email'] = $request['manager_email'];
        $validatedData['emp_category'] = $request['emp_category'];
        if($request['emp_category'] == 'approver'){
            /*$validatedData = $request->validate([
                'div_code' => 'required'
            ], [
                'div_code.required' => 'The div name field is required.'
            ]);*/
            $validator = \Validator::make($request->all(), []);
            $validator->after(function ($validator) use ($request) {
                if (empty($request->input('div_code'))) {
                    $validator->errors()->add('div_code', 'Division is required.');
                }
            });

            // Check if validation fails
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $validatedData['div_name'] = $request['div_name'];
            $validatedData['div_code'] = $request['div_code'];
            $validatedData['emp_type'] = $request['emp_type'];
            $validatedData['div_type'] = $request['div_type'];
            if($request['emp_type'] == 'RSM'){
                $validatedData['emp_level'] = 'L1';

            }elseif($request['emp_type'] == 'ZSM'){
                $validatedData['emp_level'] = 'L2';

            }elseif($request['emp_type'] == 'NSM'){
                $validatedData['emp_level'] = 'L3';

            }elseif($request['emp_type'] == 'SBU'){
                $validatedData['emp_level'] = 'L4';

            }elseif($request['emp_type'] == 'Semi Cluster'){
                $validatedData['emp_level']= 'L5';

            }elseif($request['emp_type'] == 'Cluster'){
                $validatedData['emp_level'] = 'L6';

            }else{
                $validatedData['emp_level']= 'L1';
            }
        }else if($request['emp_category'] == 'poc'){
            $validator = \Validator::make($request->all(), []);
            $validatedData['emp_type'] = $request['emp_type']; 
            $validator->after(function ($validator) use ($request) {
                if (empty($request->input('emp_number'))) {
                    $validator->errors()->add('emp_number', 'The employee number is required.');
                }
                if (empty($request->input('emp_ho'))) {
                    $validator->errors()->add('emp_ho', 'The employee HO is required.');
                }
            });

            // Check if validation fails
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $validatedData['emp_number'] = $request['emp_number'];
            $validatedData['emp_ho'] = $request['emp_ho'];
        }else if($request['emp_category'] == 'ceo'){
            $validatedData['emp_type'] = 'CEO'; 
            $validatedData['emp_level'] = 'L8'; 
        }
        Employee::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/employee-list')->with('message', 'Institution data is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/employee-list')->with('message', 'Institution data is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $institution=Employee::find($id);
        $institution->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/employee-list')->with('message', 'Institution data is successfully deleted');
        }
        else
        {
            return redirect('/admin/employee-list')->with('message', 'Institution data is successfully deleted');
        }
    }
}
