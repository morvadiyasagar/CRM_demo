<?php

namespace App\Observers;

use App\Http\Controllers\Admin\AdminBaseController;
use App\Notice;
use App\Notifications\NewNotice;
use App\Notifications\NoticeUpdate;
use App\UniversalSearch;
use App\User;
use Illuminate\Support\Facades\Notification;

class NoticeObserver
{
    /**
     * Handle the notice "saving" event.
     *
     * @param  \App\Notice  $notice
     * @return void
     */
    public function saving(Notice $notice)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $notice->company_id = company()->id;
        }
    }

    public function created(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->sendNotification($notice);
        }
        $log = new AdminBaseController();
        $log->logSearchEntry($notice->id, 'Notice: ' . $notice->heading, 'admin.notices.edit', 'notice');
    }

    public function updated(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->sendNotification($notice, 'update');
        }
    }

    public function sendNotification($notice, $action = 'create')
    {

        if ($notice->to == 'employee') {
            if (request()->team_id != '') {
                $users = User::join('employee_details', 'employee_details.user_id', '=', 'users.id')
                    ->where('employee_details.department_id', request()->team_id)
                    ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'users.email_notifications')
                    ->get();
            } else {
                $users = User::allEmployees();
            }
            if (isset($action) && $action == 'update') {
                Notification::send($users, new NoticeUpdate($notice));
            } else {
                Notification::send($users, new NewNotice($notice));
            }
        }
        if ($notice->to == 'client') {
            $users = User::join('client_details', 'client_details.user_id', '=', 'users.id')
                ->select('users.id', 'client_details.name', 'client_details.email', 'client_details.created_at', 'client_details.email_notifications')
                ->get();
//            dd($users);
            if (isset($action) && $action == 'update') {
                Notification::send($users, new NoticeUpdate($notice));
            } else {
                Notification::send($users, new NewNotice($notice));
            }
        }
    }

    public function deleting(Notice $notice)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $notice->id)->where('module_type', 'notice')->get();
        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }
    }
}
