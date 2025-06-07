<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;

class ConfigDataController extends Controller
{
    
    public function list(){
        $data = Config::get();
        return view('admin.config.list',compact('data'));
    }

    public function edit($id){
        $data = Config::find($id);
        return view('admin.config.edit',compact('data'));
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'meta_value' => 'required'
        ]);
        Config::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/config-data-list')->with('message', 'Data is successfully validated and has been updated');
    }

}
