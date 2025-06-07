<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IgnoredInstitutions;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\JwtToken;
use Session;
use App\Models\PocMaster;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;
class PocMasterController extends Controller
{
    public function index(){
        // $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $jwt = JwtToken::where('jwt_token', '!=', '')->whereNotNull('jwt_token')->orderBy('updated_at', 'desc')->first();
        // hide by arun 29012025
        // $ignoredInstitutions = IgnoredInstitutions::get();
        // $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt->jwt_token,
        ];
        
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
        
        $body = '{
          
        }';
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $response = json_decode($response);
        $resp_collection = collect($response);
        // $institution = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray(); // hide by arun 29012025
        $institution = $resp_collection->toArray(); // added by arunchandru 29012025

        /*$r = $client->request('POST', env('API_URL').'/api/POC', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $poc = json_decode($response);*/
        $poc = PocMaster::whereNotIn('fsm_code',[''])->whereNotNull('fsm_code')->groupBy('fsm_code')->get();
        $fsm_emp_master = Employee::select('emp_code as fsm_code', 'emp_name as fsm_name')->where('emp_type','fsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $poc = $poc->concat($fsm_emp_master)->unique('fsm_code'); //whereNotIn('institution_id',$ignoredInstitutions)-> // hide by arunchandru 29012025

        $rsm = PocMaster::whereNotIn('rsm_code',[''])->whereNotNull('rsm_code')->groupBy('rsm_code')->get();
        $rsm_emp_master = Employee::select('emp_code as rsm_code', 'emp_name as rsm_name')->where('emp_type','rsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $rsm = $rsm->concat($rsm_emp_master)->unique('rsm_code'); //whereNotIn('institution_id',$ignoredInstitutions)-> // hide by arunchandru 29012025

        $zsm = PocMaster::whereNotIn('zsm_code',[''])->whereNotNull('zsm_code')->groupBy('zsm_code')->get();
        $zsm_emp_master = Employee::select('emp_code as zsm_code', 'emp_name as zsm_name')->where('emp_type','zsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $zsm = $zsm->concat($zsm_emp_master)->unique('zsm_code'); //whereNotIn('institution_id',$ignoredInstitutions)-> // hide by arunchandru 29012025

        $data['institution'] =  $institution;
        $data['poc']=$poc;
        $data['rsm']=$rsm;
        $data['zsm']=$zsm;
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.poc_master.add',compact('data'));
        }
        else
        {
            return view('admin.poc_master.add',compact('data'));
        }
    }
    public function list(){
        $data = PocMaster::all();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.poc_master.list',compact('data'));
        }
        else
        {
            return view('admin.poc_master.list',compact('data'));
        }
    }
    public function store(Request $request){
        $institutionIds = $request->institution_id;
        $existingInstitutions = \DB::table('poc_master')
            ->whereIn('institution_id', $institutionIds)
            ->pluck('institution_id');
        
        /* hide by arunchandru 29-01-2025 */
        // $errorMessages = [];
        // foreach ($existingInstitutions as $existingId) {
        //     $hospitalName = \DB::table('voluntary_quotation')
        //         ->where('institution_id', $existingId)
        //         ->value('hospital_name');

        //     $hospitalMessage = $hospitalName
        //         ? "The institution {$hospitalName}-{$existingId} already exists ."
        //         : "The institution ID '{$existingId}' already exists.";

        //     $errorMessages[$existingId] = $hospitalMessage;
        // }
        // if (!empty($errorMessages)) {
        //     throw ValidationException::withMessages([
        //         'institution_id' => $errorMessages,
        //     ]);
        // }
        $validatedData = $request->validate([
            // 'institution_id' => 'required|unique:poc_master', // hide by arunchandru 29-01-2025
            'institution_id' => 'required',
            'fsm_code' => 'required',
            'rsm_code' => 'required',
            'zsm_code' => 'required',
        ]);
        
        // $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $jwt = JwtToken::where('jwt_token', '!=', '')->whereNotNull('jwt_token')->orderBy('updated_at', 'desc')->first();
        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt->jwt_token,
        ];
        
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
        
        $body = '{
          
        }';
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $response = json_decode($response);
        $resp_collection = collect($response);
        $institutions = $resp_collection->toArray();
        //->whereNotIn('INST_ID',$ignoredInstitutions); hide by arunchandru

        /*$r = $client->request('POST', env('API_URL').'/API/POC', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $pocs = json_decode($response);*/
        $pocs = PocMaster::where('fsm_code',$request->fsm_code)->get();
        $fsm_emp_master = Employee::select('emp_code as fsm_code', 'emp_name as fsm_name', 'emp_number as fsm_number', 'emp_ho as fsm_ho', 'emp_email as fsm_email')->where('emp_type','fsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $pocs = $pocs->concat($fsm_emp_master)->unique('fsm_code');

        $rsm_codes = PocMaster::where('rsm_code',$request->rsm_code)->get();
        $rsm_emp_master = Employee::select('emp_code as rsm_code', 'emp_name as rsm_name', 'emp_number as rsm_number', 'emp_ho as rsm_ho', 'emp_email as rsm_email')->where('emp_type','rsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $rsm_codes = $rsm_codes->concat($rsm_emp_master)->unique('rsm_code');

        $zsm_codes = PocMaster::where('zsm_code',$request->zsm_code)->get();
        $zsm_emp_master = Employee::select('emp_code as zsm_code', 'emp_name as zsm_name', 'emp_number as zsm_number', 'emp_ho as zsm_ho', 'emp_email as zsm_email')->where('emp_type','zsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $zsm_codes = $zsm_codes->concat($zsm_emp_master)->unique('zsm_code');

        foreach ($request->institution_id as $single_institution) {
            
            $institution_data=array();
            foreach($institutions as $institution){
                //if($institution->INST_ID == $request->institution_id){
                if($institution->INST_ID == $single_institution){
                    $institution_data['institution_id']=$institution->INST_ID;
                    $institution_data['institution_name']=$institution->INST_NAME;
                    $institution_data['city']=$institution->CITY;
                    break;
                }
            }

            foreach($pocs as $poc){
                if($poc->fsm_code == $request->fsm_code){
                    $institution_data['fsm_code']=$poc->fsm_code;
                    $institution_data['fsm_name']=$poc->fsm_name;
                    $institution_data['fsm_number']=$poc->fsm_number;
                    $institution_data['fsm_email']=$poc->fsm_email;
                    $institution_data['fsm_ho']=$poc->fsm_ho;
                    break;
                }
            }
            foreach($rsm_codes as $rsm_code_unique){
                if($rsm_code_unique->rsm_code == $request->rsm_code){
                    $institution_data['rsm_code']=$rsm_code_unique->rsm_code;
                    $institution_data['rsm_name']=$rsm_code_unique->rsm_name;
                    $institution_data['rsm_number']=$rsm_code_unique->rsm_number;
                    $institution_data['rsm_email']=$rsm_code_unique->rsm_email;
                    $institution_data['rsm_ho']=$rsm_code_unique->rsm_ho;
                    break;
                }
            }
            foreach($zsm_codes as $zsm_code_unique){
                if($zsm_code_unique->zsm_code == $request->zsm_code){
                    $institution_data['zsm_code']=$zsm_code_unique->zsm_code;
                    $institution_data['zsm_name']=$zsm_code_unique->zsm_name;
                    $institution_data['zsm_number']=$zsm_code_unique->zsm_number;
                    $institution_data['zsm_email']=$zsm_code_unique->zsm_email;
                    $institution_data['zsm_ho']=$zsm_code_unique->zsm_ho;
                    break;
                }
            }

            $existingInstitutionsPOCmaster = \DB::table('poc_master')
            ->where('institution_id', $single_institution)
            ->pluck('id')->toArray();

            // print_r($existingInstitutionsPOCmaster);die;
            if(!empty($existingInstitutionsPOCmaster)):
                PocMaster::where('institution_id',$single_institution)->update($institution_data);
                // PocMaster::whereIn('id', $existingInstitutionsPOCmaster)->update($institution_data);
            else:
                PocMaster::create($institution_data);
            endif;
        }
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/poc-master-list')->with('message', 'Poc data is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/poc-master-list')->with('message', 'Poc data is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = PocMaster::find($id);
        // $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $jwt = JwtToken::where('jwt_token', '!=', '')->whereNotNull('jwt_token')->orderBy('updated_at', 'desc')->first();
        // hide by arunchandru 29012025
        // $ignoredInstitutions = IgnoredInstitutions::get();
        // $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt->jwt_token,
        ];
        
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
        
        $body = '{
          
        }';
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $response = json_decode($response);
        $resp_collection = collect($response);
        // $institution = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray(); // hide by arunchandru at 29012025
        $institution = $resp_collection->toArray(); // modify code by arun at 29012025

        /*$r = $client->request('POST', env('API_URL').'/api/POC', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $poc = json_decode($response);*/
        $poc = PocMaster::whereNotIn('fsm_code',[''])->whereNotNull('fsm_code')->groupBy('fsm_code')->get();
        $fsm_emp_master = Employee::select('emp_code as fsm_code', 'emp_name as fsm_name')->where('emp_type','fsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $poc = $poc->concat($fsm_emp_master)->unique('fsm_code'); // whereNotIn('institution_id',$ignoredInstitutions)-> // hide by arunchandru at 29012025

        $rsm = PocMaster::whereNotIn('rsm_code',[''])->whereNotNull('rsm_code')->groupBy('rsm_code')->get();
        $rsm_emp_master = Employee::select('emp_code as rsm_code', 'emp_name as rsm_name')->where('emp_type','rsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $rsm = $rsm->concat($rsm_emp_master)->unique('rsm_code'); // whereNotIn('institution_id',$ignoredInstitutions)-> // hide by arunchandru at 29012025

        $zsm = PocMaster::whereNotIn('zsm_code',[''])->whereNotNull('zsm_code')->groupBy('zsm_code')->get();
        $zsm_emp_master = Employee::select('emp_code as zsm_code', 'emp_name as zsm_name')->where('emp_type','zsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $zsm = $zsm->concat($zsm_emp_master)->unique('zsm_code'); // whereNotIn('institution_id',$ignoredInstitutions)-> // hide by arunchandru at 29012025

        $data1['institution'] =  $institution;
        $data1['poc']=$poc;
        $data1['rsm']=$rsm;
        $data1['zsm']=$zsm;
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.poc_master.edit',compact('data','data1'));
        }
        else
        {
            return view('admin.poc_master.edit',compact('data','data1'));
        }
    }
    
    public function update(Request $request){
        $validatedData = $request->validate([
            // 'institution_id' => 'required',
            'fsm_code' => 'required',
            
        ]);
        // $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $jwt = JwtToken::where('jwt_token', '!=', '')->whereNotNull('jwt_token')->orderBy('updated_at', 'desc')->first();

        // hide by arun at 29012025
        // $ignoredInstitutions = IgnoredInstitutions::get();
        // $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());

        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt->jwt_token,
        ];
        
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
        
        $body = '{
          
        }';
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $response = json_decode($response);
        $resp_collection = collect($response);
        // $institutions = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray(); // hide by arun 29012025
        $institutions = $resp_collection->toArray(); // modify code by arun29012025

        /*$r = $client->request('POST', env('API_URL').'/api/POC', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $pocs = json_decode($response);*/
        $pocs = PocMaster::where('fsm_code',$request->fsm_code)->get();
        $fsm_emp_master = Employee::select('emp_code as fsm_code', 'emp_name as fsm_name', 'emp_number as fsm_number', 'emp_ho as fsm_ho', 'emp_email as fsm_email')->where('emp_type','fsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $pocs = $pocs->concat($fsm_emp_master)->unique('fsm_code');

        $rsm_codes = PocMaster::where('rsm_code',$request->rsm_code)->get();
        $rsm_emp_master = Employee::select('emp_code as rsm_code', 'emp_name as rsm_name', 'emp_number as rsm_number', 'emp_ho as rsm_ho', 'emp_email as rsm_email')->where('emp_type','rsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $rsm_codes = $rsm_codes->concat($rsm_emp_master)->unique('rsm_code');

        $zsm_codes = PocMaster::where('zsm_code',$request->zsm_code)->get();
        $zsm_emp_master = Employee::select('emp_code as zsm_code', 'emp_name as zsm_name', 'emp_number as zsm_number', 'emp_ho as zsm_ho', 'emp_email as zsm_email')->where('emp_type','zsm')->where('emp_category','poc')->groupBy('emp_code')->get();
        $zsm_codes = $zsm_codes->concat($zsm_emp_master)->unique('zsm_code');

        $institution_data=array();
        foreach($institutions as $institution){
            if($institution->INST_ID == $request->institution_id){
                $institution_data['institution_id']=$institution->INST_ID;
                $institution_data['institution_name']=$institution->INST_NAME;
                $institution_data['city']=$institution->CITY;
                break;
            }
        }

        foreach($pocs as $poc){
            if($poc->fsm_code == $request->fsm_code){
                $institution_data['fsm_code']=$poc->fsm_code;
                $institution_data['fsm_name']=$poc->fsm_name;
                $institution_data['fsm_number']=$poc->fsm_number;
                $institution_data['fsm_email']=$poc->fsm_email;
                $institution_data['fsm_ho']=$poc->fsm_ho;
                break;
            }
        }
        foreach($rsm_codes as $rsm_code_unique){
            if($rsm_code_unique->rsm_code == $request->rsm_code){
                $institution_data['rsm_code']=$rsm_code_unique->rsm_code;
                $institution_data['rsm_name']=$rsm_code_unique->rsm_name;
                $institution_data['rsm_number']=$rsm_code_unique->rsm_number;
                $institution_data['rsm_email']=$rsm_code_unique->rsm_email;
                $institution_data['rsm_ho']=$rsm_code_unique->rsm_ho;
                break;
            }
        }
        foreach($zsm_codes as $zsm_code_unique){
            if($zsm_code_unique->zsm_code == $request->zsm_code){
                $institution_data['zsm_code']=$zsm_code_unique->zsm_code;
                $institution_data['zsm_name']=$zsm_code_unique->zsm_name;
                $institution_data['zsm_number']=$zsm_code_unique->zsm_number;
                $institution_data['zsm_email']=$zsm_code_unique->zsm_email;
                $institution_data['zsm_ho']=$zsm_code_unique->zsm_ho;
                break;
            }
        }


        PocMaster::where('id',$request['id'])->update($institution_data);
//        PocMaster::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/poc-master-list')->with('message', 'Poc data is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/poc-master-list')->with('message', 'Poc data is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $institution=PocMaster::find($id);
        $institution->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/poc-master-list')->with('message', 'Poc data is successfully deleted');
        }
        else
        {
            return redirect('/admin/poc-master-list')->with('message', 'Poc data is successfully deleted');
        }
    }
}
