<?php

namespace App\Listeners;

use App\Events\SendResetPasswordEmail;
use App\Models\Vendor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;
class SendResetPasswordMailFired
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
     * @param  \App\Events\SendResetPasswordEmail  $event
     * @return void
     */
    public function handle(SendResetPasswordEmail $event)
    {
        $vendor = Vendor::find($event->vendorId)->to;
        $name = $vendor->first_name.' '.$vendor->family_name;
        Mail::send(['html' => 'emails.restore_password'], ['name' => $name, 'password' => $event->password,'vendor' => $vendor],
            function ($message) use ($vendor) {
//            $message->from('no-replay@nqde.com.sa	', 'nqde.com.sa');
            $message->subject('Restore nqde account');
            $message->to($vendor->email);
        });
    }
}
