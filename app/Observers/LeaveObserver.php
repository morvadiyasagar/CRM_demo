<?php

namespace App\Observers;

use App\Leave;
use App\Notifications\LeaveApplication;
use App\Notifications\LeaveStatusApprove;
use App\Notifications\LeaveStatusReject;
use App\Notifications\LeaveStatusUpdate;
use App\Notifications\MultipleLeaveApplication;
use App\Notifications\NewLeaveRequest;
use App\Notifications\NewMultipleLeaveRequest;
use App\User;

class LeaveObserver
{
    /**
     * Handle the leave "saving" event.
     *
     * @param  \App\Leave  $leave
     * @return void
     */
    public function saving(Leave $leave)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $leave->company_id = company()->id;
        }
    }

    public function created(Leave $leave)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (request()->duration == 'multiple') {
                if (session()->has('leaves_duration')) {
                    $leave->user->notify(new MultipleLeaveApplication($leave, request()->multi_date));

                    $notifyUsers = User::frontAllAdmins($leave->company_id);
                    foreach ($notifyUsers as $notifyUser) {
                        $notifyUser->notify(new NewMultipleLeaveRequest($leave, request()->multi_date));
                    }
                }
            } else {
                $leave->user->notify(new LeaveApplication($leave));

                // Send notification to user
                $notifyUsers = User::frontAllAdmins($leave->company_id);
                foreach ($notifyUsers as $notifyUser) {
                    $notifyUser->notify(new NewLeaveRequest($leave));
                }
            }
   
        }
    }

    public function updated(Leave $leave)
    {
        if (!app()->runningInConsole()) {
            // Send from ManageLeavesController
            if ($leave->isDirty('status')) {

                if ($leave->status == 'approved') {
                    $leave->user->notify(new LeaveStatusApprove($leave));
                } else {
                    $leave->user->notify(new LeaveStatusReject($leave));
                }
            } else {
                // Send notification to user
                $leave->user->notify(new LeaveStatusUpdate($leave));
            }
        }
    }

}
