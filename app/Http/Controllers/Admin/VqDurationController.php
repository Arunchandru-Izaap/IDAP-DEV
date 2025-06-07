<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApprovalPeriod;
use Session;
use Carbon\Carbon;
use App\Http\Controllers\Api\VqListingController;
use App\Models\ActivityTracker;
class VqDurationController extends Controller
{
    public function index(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $year_arr = explode('-', $year);
        $date['maxDate'] = $year_arr[1].'-'.'03-31';
        $today = Carbon::now();
        $date['minDate'] = $today->toDateString();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.duration.add',compact('date'));
        }
        else
        {
            return view('admin.duration.add',compact('date'));
        }
    }
    public function list(){
        $data = ApprovalPeriod::orderBy('type', 'ASC')->orderBy('level', 'ASC')->get();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $log = ActivityTracker::select('activity_trackers.*','employee_master.emp_code','employee_master.emp_name')->leftJoin('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')->where('type','change_auto_approval_period')
            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year])
            //->where('activity_trackers.emp_code', Session::get("emp_code"))
            ->orderBy('activity_trackers.created_at', 'DESC')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.duration.list',compact('data','log'));
        }
        else
        {
            return view('admin.duration.list',compact('data','log'));
        }
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'level' => 'required',
            'type' => 'required',
            'start_date' => ['required_if:type,vq'],
            'end_date' => ['required_if:type,vq'],
            'days' => 'required'

        ]);
        $changed_to_list = [];
        if($validatedData['type'] == 'vq')
        {
            $userLevel = (int) $validatedData['level']; 
            $nextLevels = ApprovalPeriod::where('type', $validatedData['type'])
            ->where('level', '>', $validatedData['level'])
            ->orderBy('level')
            ->get();
            $previousEndDate = Carbon::parse($validatedData['end_date']);
            $baseDays = (int)$validatedData['days'];
            $cumulativeDays = $baseDays;
            if(!$nextLevels->isEmpty())
            {
                foreach ($nextLevels as $nextLevel) {
                    //$cumulativeDays += (int)$nextLevel->days;
                    $cumulativeDays += $baseDays;
                    $newStart = $previousEndDate->copy()->addDay();
                    
                    $newEnd = $previousEndDate->copy()->addDays($cumulativeDays);
                    $newDays_from_start = $newEnd->diffInDays($validatedData['start_date'])+1;
                    //echo $newDays_from_start.' - '.$newStart.' - '.$newEnd.'<br>';
                    $changed_to_list[] = [
                        'action' => 'updated_next_level',
                        'id' => $nextLevel->id,
                        'level' => $nextLevel->level,
                        'type' => $nextLevel->type,
                        'old_values' => [
                            'start_date' => $nextLevel->start_date,
                            'end_date' => $nextLevel->end_date,
                            'days' => $nextLevel->days
                        ],
                        'new_values' => [
                            'start_date' => $newStart->format('Y-m-d'),
                            'end_date' => $newEnd->format('Y-m-d'),
                            'days' => $newDays_from_start
                        ]
                    ];
                    $nextLevel->update([
                        'start_date' => $newStart->format('Y-m-d'),
                        'end_date' => $newEnd->format('Y-m-d'),
                        'days' => $newDays_from_start
                    ]);
                    $previousEndDate = $newEnd;
                }
            }
            else
            {
                $nextLevelNum = $userLevel + 1;
                while ($nextLevelNum <= 6) {
                    $cumulativeDays += $baseDays;
                    $newStart = $previousEndDate->copy()->addDay();
                    
                    $newEnd = $previousEndDate->copy()->addDays($cumulativeDays);
                    $newDays_from_start = $newEnd->diffInDays($validatedData['start_date'])+1;
                    //echo $newDays_from_start.' - '.$newStart.' - '.$newEnd.'<br>';

                    $changed_to_list[] = [
                        'action' => 'created_next_level',
                        'id' => '',
                        'level' => $nextLevelNum,
                        'type' => $validatedData['type'],
                        'new_values' => [
                            'start_date' => $newStart->format('Y-m-d'),
                            'end_date' => $newEnd->format('Y-m-d'),
                            'days' => $newDays_from_start
                        ]
                    ];
                    $newLevel = ApprovalPeriod::create([
                        'type' => $validatedData['type'],
                        'level' => $nextLevelNum,
                        'start_date' => $newStart->format('Y-m-d'),
                        'end_date' => $newEnd->format('Y-m-d'),
                        'days' => $newDays_from_start
                    ]);
                    $previousEndDate = $newEnd;
                    $nextLevelNum++;
                    
                }
            }
        }
        $oldData  = ApprovalPeriod::where('type', $validatedData['type'])
        ->where('level', $validatedData['level'])
        ->first();
        $newValues = [
            'days' => $validatedData['days']
        ];

        if ($validatedData['type'] === 'vq') {
            $newValues['start_date'] = $validatedData['start_date'];
            $newValues['end_date'] = $validatedData['end_date'];
        }
        if($oldData)
        {
            $oldValues = [
                'days' => $oldData->days
            ];

            if ($validatedData['type'] === 'vq') {
                $oldValues['start_date'] = $oldData->start_date;
                $oldValues['end_date'] = $oldData->end_date;
            }
            $changed_to_list[] = [
                'action' => 'updated_current_level',
                'id' => $oldData->id,
                'level' => $validatedData['level'],
                'type' => $validatedData['type'],
                'old_values' => $oldValues,
                'new_values' => $newValues
            ];
            $oldData->update($validatedData);
        }
        else
        {
            $newData = ApprovalPeriod::create($validatedData);
            $changed_to_list[] = [
                'action' => 'created',
                'id' => $newData->id,
                'level' => $validatedData['level'],
                'type' => $validatedData['type'],
                'new_values' => $newValues
            ];
        }
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $addl_params = [
            'fin_year' => $year,
            'ip_address' => $ip_address,
            'changed_at' => date('Y-m-d H:i:s'),
            'user_agent' => $userAgent,
            'changed_to' => $changed_to_list  
        ];
        $vq_listing_controller = new VqListingController;
        $vq_listing_controller->activityTracker(1, Session::get("emp_code"), json_encode($addl_params), 'change_auto_approval_period');
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
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $year_arr = explode('-', $year);
        $date['maxDate'] = $year_arr[1].'-'.'03-31';
        $today = Carbon::now();
        $date['minDate'] = $today->toDateString();

        $data = ApprovalPeriod::find($id);
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.duration.edit',compact('data','date'));
        }
        else
        {
            return view('admin.duration.edit',compact('data','date'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'level' => 'required',
            'type' => 'required',
            'start_date' => ['required_if:type,vq'],
            'end_date' => ['required_if:type,vq'],
            'days' => 'required'
        ]);
        $changed_to_list = [];
        if($validatedData['type'] == 'vq')
        {
            $userLevel = (int) $validatedData['level']; 
            $newStart = Carbon::parse($validatedData['start_date']);
            $newEnd = Carbon::parse($validatedData['end_date']);
            //$newDays = $newEnd->diffInDays($newStart) + 1;
            $newDays = $validatedData['days'];
            $nextLevels = ApprovalPeriod::where('type', $validatedData['type'])
            ->where('level', '>', $validatedData['level'])
            ->orderBy('level')
            ->get();
            $currentLevel = ApprovalPeriod::where('id', $request['id'])->first();
            $originalDays = (int)$currentLevel->days;
            $addedDays = $newDays - $originalDays;
            $previousEndDate = $newEnd;
            foreach ($nextLevels as $nextLevel) {
                $origDays = (int)$nextLevel->days;
                $dbEndDate = Carbon::parse($nextLevel->end_date);
                $updatedDays = max(1, $origDays + $addedDays);
                $startDate = $previousEndDate->copy()->addDay();
                $endDate = $dbEndDate->addDays($addedDays);
                //echo $updatedDays.' - '.$startDate.' - '.$endDate.'<br>';
                $changed_to_list[] = [
                    'action' => 'updated_next_level',
                    'id' => $nextLevel->id,
                    'level' => $nextLevel->level,
                    'type' => $nextLevel->type,
                    'old_values' => [
                        'start_date' => $nextLevel->start_date,
                        'end_date' => $nextLevel->end_date,
                        'days' => $nextLevel->days
                    ],
                    'new_values' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'days' => $updatedDays
                    ]
                ];
                $nextLevel->update([
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'days' => $updatedDays
                ]);
                $previousEndDate = $endDate;
            }
        }
        $oldData  = ApprovalPeriod::where('id', $request['id'])->first();
        $newValues = [
            'days' => $validatedData['days']
        ];
        $oldValues = [
            'days' => $oldData->days
        ];
        if ($validatedData['type'] === 'vq') {
            $newValues['start_date'] = $validatedData['start_date'];
            $newValues['end_date'] = $validatedData['end_date'];
            $oldValues['start_date'] = $oldData->start_date;
            $oldValues['end_date'] = $oldData->end_date;
        }
        $changed_to_list[] = [
            'action' => 'updated_current_level',
            'id' => $oldData->id,
            'level' => $validatedData['level'],
            'type' => $validatedData['type'],
            'old_values' => $oldValues,
            'new_values' => $newValues
        ];
        ApprovalPeriod::where('id',$request['id'])->update($validatedData);
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $addl_params = [
            'fin_year' => $year,
            'ip_address' => $ip_address,
            'changed_at' => date('Y-m-d H:i:s'),
            'user_agent' => $userAgent,
            'changed_to' => $changed_to_list  
        ];
        $vq_listing_controller = new VqListingController;
        $vq_listing_controller->activityTracker(1, Session::get("emp_code"), json_encode($addl_params), 'change_auto_approval_period');
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
        $changed_to_list[] = [
            'action' => 'deleted',
            'id' => $id,
            'level' => $institution['level'],
            'type' => $institution['type'],
            'old_values' => [
                'start_date' => $institution->start_date,
                'end_date' => $institution->end_date,
                'days' => $institution->days
            ],
        ];
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $addl_params = [
            'fin_year' => $year,
            'ip_address' => $ip_address,
            'changed_at' => date('Y-m-d H:i:s'),
            'user_agent' => $userAgent,
            'changed_to' => $changed_to_list  
        ];
        $vq_listing_controller = new VqListingController;
        $vq_listing_controller->activityTracker(1, Session::get("emp_code"), json_encode($addl_params), 'change_auto_approval_period');
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
