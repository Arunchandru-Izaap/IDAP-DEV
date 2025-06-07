<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Http\Request;

class isInitiator
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
        if(Session::get("type") == 'initiator'){
            return $next($request);
        }elseif(Session::get("type") == 'approver'){
            return redirect()->route('approver_dashboard');
        }elseif(Session::get("type") == 'normal_user'){
            return redirect()->route('user_dashboard');
        }elseif(Session::get("type") == 'poc'){
            return redirect()->route('poc_dashboard');
        }elseif(Session::get("type") == 'distribution'){
            return redirect()->route('distribution_dashboard');
        }elseif(Session::get("type") == 'ho'){
            return redirect()->route('ho_dashboard');
        }elseif(Session::get("type") == 'ceo'){
            return redirect()->route('approver_dashboard');
        }else{
            return redirect('/');
        }
       
    }
}
