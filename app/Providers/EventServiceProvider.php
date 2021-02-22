<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\CompanyRegistered' => ['App\Listeners\CompanyRegisteredListener'],
        'App\Events\TaskEvent' => ['App\Listeners\TaskListener'],
        'App\Events\TaskReminderEvent' => ['App\Listeners\TaskReminderListener'],
        'App\Events\TaskCommentEvent' => ['App\Listeners\TaskCommentListener'],
        'App\Events\AutoTaskReminderEvent' => ['App\Listeners\AutoTaskReminderListener'],
        'App\Events\SubTaskCompletedEvent' => ['App\Listeners\SubTaskCompletedListener'],
        'App\Events\DiscussionReplyEvent' => ['App\Listeners\DiscussionReplyListener'],
        'App\Events\DiscussionEvent' => ['App\Listeners\DiscussionListener'],
        'App\Events\TicketReplyEvent' => ['App\Listeners\TicketReplyListener'],
        'App\Events\TaskNoteEvent' => ['App\Listeners\TaskNoteListener'],
        'App\Events\TicketRequesterEvent' => ['App\Listeners\TicketRequesterListener'],
        'App\Events\NewExpenseRecurringEvent' => ['App\Listeners\NewExpenseRecurringListener'],
        'App\Events\NewInvoiceRecurringEvent' => ['App\Listeners\NewInvoiceRecurringListener'],
        'App\Events\NewCreditNoteEvent' => ['App\Listeners\NewCreditNoteListener'],
        'App\Events\LeadEvent' => ['App\Listeners\LeadListener'],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
