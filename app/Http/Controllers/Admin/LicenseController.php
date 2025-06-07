<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticPages\VoluntaryQuotationController;
use Illuminate\Http\Request;
use App\Models\License;
use App\Models\VoluntaryQuotation;
use Illuminate\Support\Facades\Response;

class LicenseController extends Controller
{
    public function index(){
        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        $vq = VoluntaryQuotation::select("institution_id", "hospital_name")->where('year',$year)->where('is_deleted', 0)->distinct()->get();
        return view('admin.license.add',compact('vq'));
    }
    public function list(){
        $data = License::get();
        // dd($data);
        return view('admin.license.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'institution_id' => 'required',
            'institution_name' => 'required',
            'gst' => 'required|mimes:pdf',
            'form20' => 'required|mimes:pdf',
            'form21' => 'required|mimes:pdf',
            'form20_expiry_date' => 'required',
            'form21_expiry_date' => 'required',
        ]);

        $dataExists = License::where('institution_id', $request->institution_id)->exists();
        
        if($dataExists){
            return back()->withErrors('Data for this Institution is already present!');
        }

        $obj = new License();
        $obj->institution_id = $request['institution_id'];
        $obj->institution_name = $request['institution_name'];

        $gst = time().rand(1,100).'.'.$request['gst']->extension();  
        $request->gst->move(public_path('images/license/gst'), $gst);
        $obj->gst=$gst;

        $form20 = time().rand(1,100).'.'.$request['form20']->extension();  
        $request->form20->move(public_path('images/license/form20'), $form20);
        $obj->form20=$form20;

        $form21 = time().rand(1,100).'.'.$request['form21']->extension();  
        $request->form21->move(public_path('images/license/form21'), $form21);
        $obj->form21=$form21;

        $obj->form20_expiry_date = $request['form20_expiry_date'];
        $obj->form21_expiry_date = $request['form21_expiry_date'];


        $obj->save();
        return redirect('/admin/license-list')->with('message', 'License is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = License::find($id);
        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        $vq = VoluntaryQuotation::select("institution_id", "hospital_name")->where('year',$year)->where('is_deleted', 0)->distinct()->get();
        return view('admin.license.edit',compact('data', 'vq'));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'gst' => 'mimes:pdf',
            'form20' => 'mimes:pdf',
            'form21' => 'mimes:pdf',
            'form20_expiry_date' => 'required',
            'form21_expiry_date' => 'required',
        ]);

        $oldData = License::find($request['id']);

        $gst = $request->file('gst');
        if($request->gst != null){
            if(file_exists(public_path().'/images/license/gst/'.$oldData->gst)){
                unlink(public_path().'/images/license/gst/'.$oldData->gst);
            }
            $gst = time().rand(1,100).'.'.$request['gst']->extension();  
            $request->gst->move(public_path('images/license/gst'), $gst);
            $oldData->gst=$gst;
        }

        $form20 = $request->file('form20');
        if($request->form20 != null){
            if(file_exists(public_path().'/images/license/form20/'.$oldData->form20)){
                unlink(public_path().'/images/license/form20/'.$oldData->form20);
            }
            $form20 = time().rand(1,100).'.'.$request['form20']->extension();  
            $request->form20->move(public_path('images/license/form20'), $form20);
            $oldData->form20=$form20;
            $oldData->form20_expiry_date=$request->form20_expiry_date;
        }

        $form21 = $request->file('form21');
        if($request->form21 != null){
            if(file_exists(public_path().'/images/license/form21/'.$oldData->form21)){
                unlink(public_path().'/images/license/form21/'.$oldData->form21);
            }       
            $form21 = time().rand(1,100).'.'.$request['form21']->extension();  
            $request->form21->move(public_path('images/license/form21'), $form21);
            $oldData->form21=$form21;
            $oldData->form21_expiry_date=$request->form21_expiry_date;

        }

        $oldData->save();

        return redirect('/admin/license-list')->with('message', 'License is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=License::find($id);

        unlink(public_path().'/images/license/gst/'.$institution->gst);
        unlink(public_path().'/images/license/form20/'.$institution->form20);
        unlink(public_path().'/images/license/form21/'.$institution->form21);
    
        $institution->delete();
        return redirect('/admin/license-list')->with('message', 'License is successfully deleted');
    }

    public function download($fileType, $fileName){
        $file= public_path(). "/images/license/".$fileType."/". $fileName;
        return Response::download($file);
    }
}
