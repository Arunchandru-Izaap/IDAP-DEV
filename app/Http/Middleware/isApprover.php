<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Http\Request;

class isApprover
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Session::get("type") == 'approver'){
            return $next($request);
        }elseif(Session::get("type") == 'initiator'){
            return redirect()->route('initiator_dashboard');
        }elseif(Session::get("type") == 'normal_user'){
            return redirect()->route('normal_dashboard');
        }elseif(Session::get("type") == 'poc'){
            return redirect()->route('poc_dashboard');
        }elseif(Session::get("type") == 'distribution'){
            return redirect()->route('distribution_dashboard');
        }elseif(Session::get("type") == 'ho'){
            return redirect()->route('ho_dashboard');
        }elseif(Session::get("type") == 'ceo'){
            return $next($request);
        }else{
            return redirect('/');
        }
    }
}
