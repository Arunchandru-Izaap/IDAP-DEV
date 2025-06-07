<?php

namespace App\Http\Controllers\StaticPages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
class DashboardController extends Controller
{
    public function approverDashboard(){
        return view('frontend.Approver.dashboard');
    }
    public function initiatorDashboard(){
        return view('frontend.Initiator.dashboard');
    }

    public function userDashboard(){
        return view('frontend.User.dashboard');
    }

    public function pocDashboard(){
        return view('frontend.Poc.dashboard');
    }

    public function distributionDashboard(){
        return view('frontend.Distribution.dashboard');
    }

    public function hoDashboard(){
        return view('frontend.Ho.dashboard');
    }
    
}
