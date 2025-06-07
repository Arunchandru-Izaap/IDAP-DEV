<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LastYearPrice;
class LastYearPriceController extends Controller
{
    public function index(){
        return view('admin.last_year_price.add');
    }
    public function list(){
        $data = LastYearPrice::paginate(100);
        return view('admin.last_year_price.list',compact('data'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'sku_id' => 'required',
            'institution_id' => 'required',
            'division_id' => 'required',

            'discount_percent' => 'required',
        ]);
        LastYearPrice::create($validatedData);
        return redirect('/admin/last-year-price-list')->with('message', 'Last Year Price data is successfully validated and data has been saved');
    }

    public function edit($id){
        $data = LastYearPrice::find($id);
        return view('admin.last_year_price.edit',compact('data'));
    }
    
    public function update(Request $request){
        $validatedData = $request->validate([
            'sku_id' => 'required',
            'institution_id' => 'required',
            'division_id' => 'required',

            'discount_percent' => 'required',
        ]);
        LastYearPrice::where('id',$request['id'])->update($validatedData);
        return redirect('/admin/last-year-price-list')->with('message', 'Last Year Price data is successfully validated and data has been updated');
    }
    public function delete($id){
        $institution=LastYearPrice::find($id);
        $institution->delete();
        return redirect('/admin/last-year-price-list')->with('message', 'Last Year Price data is successfully deleted');
    }

    public function allLastYear(Request $request)
{
        $totalFilteredRecord = $totalDataRecord = $draw_val = "";
        $columns_list = array(
        0 =>'sku_id',
        1 => 'institution_id',
        2 => 'division_id',
        3 => 'discount_percent',
        4 => 'year',
        );
        
        $totalDataRecord = LastYearPrice::count();
        
        $totalFilteredRecord = $totalDataRecord;
        
        $limit_val = $request->input('length');
        $start_val = $request->input('start');
        $order_val = $columns_list[$request->input('order.0.column')];
        $dir_val = $request->input('order.0.dir');
        
        if(empty($request->input('search.value')))
        {
        $query = LastYearPrice::offset($start_val)
        ->limit($limit_val);
        if($order_val == 'sku_id'){
            $query = $query->orderByRaw('convert(`sku_id`, decimal) '.$dir_val);
        }else{
            $query = $query->orderBy($order_val,$dir_val);

        }
        $post_data = $query->get();
        }
        else {
        $search_text = $request->input('search.value');
        
        $query =  LastYearPrice::where('sku_id','LIKE',"%{$search_text}%")
        ->orWhere('institution_id', 'LIKE',"%{$search_text}%")
        ->offset($start_val)
        ->limit($limit_val);

        if($order_val == 'sku_id'){
            $query = $query->orderByRaw('convert(`sku_id`, decimal) '.$dir_val);
        }else{
            $query = $query->orderBy($order_val,$dir_val);

        }
        $post_data = $query->get();
        
        
        $totalFilteredRecord = LastYearPrice::where('sku_id','LIKE',"%{$search_text}%")
        ->orWhere('institution_id', 'LIKE',"%{$search_text}%")
        ->count();
        }
        
        $data_val = array();
        if(!empty($post_data))
        {
        foreach ($post_data as $post_val)
        {
        $datashow =  url('admin/last-year-price-edit',['id'=>$post_val->id]);
        $dataedit =  url('admin/last-year-price-delete',['id'=>$post_val->id]);
        
        $postnestedData['sku_id'] = $post_val->sku_id;
        $postnestedData['institution_id'] = $post_val->institution_id;
        $postnestedData['division_id'] = $post_val->division_id;
        $postnestedData['discount_percent'] = $post_val->discount_percent;
        $postnestedData['year'] = $post_val->year;
        $postnestedData['actions'] = '&emsp;<a href="'.$datashow.'" class="btn btn-primary btn-sm">Edit</a><a href="'.$dataedit.'" class="btn btn-danger btn-sm text-white">Delete</a>';
        $data_val[] = $postnestedData;
        
        }
        }
        $draw_val = $request->input('draw');
        $get_json_data = array(
        "draw"            => intval($draw_val),
        "recordsTotal"    => intval($totalDataRecord),
        "recordsFiltered" => intval($totalFilteredRecord),
        "data"            => $data_val
        );
        
        echo json_encode($get_json_data);
        
        }
}
