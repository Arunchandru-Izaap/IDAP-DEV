<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Session\Events\SessionEnding;
use App\Models\Employee;
class SessionEndingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $emp_code = $event->session->get('emp_code');
        Employee::where('emp_code', $emp_code)->update(['is_login_now' => 0]);
    }
}
