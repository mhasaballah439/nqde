<?php

namespace App\Listeners;

use App\Events\SendVendorActiveEmail;
use App\Models\Vendor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;
class SendVendorActiveMailFired
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
     * @param  \App\Events\SendVendorActiveEmail  $event
     * @return void
     */
    public function handle(SendVendorActiveEmail $event)
    {
        $vendor = Vendor::find($event->vendorId);
        $name = $vendor->first_name.' '.$vendor->family_name;
        Mail::send(['html' => 'emails.verify_account'], ['name' => $name, 'code' => $vendor->active_code], function ($message) use ($vendor) {
//            $message->from('no-replay@nqde.com.sa	', 'nqde.com.sa');
            $message->subject('Active nqde account');
            $message->to($vendor->email);
        });
    }
}
