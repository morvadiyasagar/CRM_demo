<?php

namespace App\Http\Controllers\Admin;

use App\Event;
use App\EventAttendee;
use App\Helper\Reply;
use App\Http\Requests\Events\StoreEvent;
use App\Http\Requests\Events\UpdateEvent;
use App\Notifications\EventInvite;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
class AdminEventCalendarController extends AdminBaseController
{

    protected $client;
    public function __construct()
    {
       
        
        session_start();
        parent::__construct();
        $this->pageTitle = 'app.menu.Events';
        $this->pageIcon = 'icon-calender';
        $this->middleware(function ($request, $next) {
            if (!in_array('events', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            if (isset(($_SESSION['client']))){
                $this->client = $_SESSION['client'];  
            }else{
                $client = new Google_Client();
                $client->setAuthConfig('client_secret.json');
                $client->addScope(Google_Service_Calendar::CALENDAR);
        
                $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
                $client->setHttpClient($guzzleClient);
                $this->client = $client;    
            }
        }else{
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->addScope(Google_Service_Calendar::CALENDAR);

        $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
        $client->setHttpClient($guzzleClient);
        $this->client = $client;
       
       }
    }

    public function index()
    {
        $this->employees = User::all();
        $this->events = Event::all();
        return view('admin.event-calendar.index', $this->data);
    }

    public function store(StoreEvent $request)
    {
        $event = new Event();
        $event->event_name = $request->event_name;
        $event->where = $request->where;
        $event->description = $request->description;
        $event->start_date_time = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->global->time_format, $request->start_time)->format('H:i:s');
        $event->end_date_time = Carbon::createFromFormat($this->global->date_format, $request->end_date)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->global->time_format, $request->end_time)->format('H:i:s');

        if ($request->repeat) {
            $event->repeat = $request->repeat;
        } else {
            $event->repeat = 'no';
        }

        $event->repeat_every = $request->repeat_count;
        $event->repeat_cycles = $request->repeat_cycles;
        $event->repeat_type = $request->repeat_type;
        $event->label_color = $request->label_color;
        $event->save();

        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
            $service = new Google_Service_Calendar($this->client);

            $calendarId = 'primary';
            $event1 = new Google_Service_Calendar_Event([
                'summary' => $request->event_name,
                'description' => $request->description,
                'start' => ['dateTime' => "2021-02-01T06:00:00+02:00"],
                'end' => ['dateTime' => "2021-02-10T07:00:00+02:00"],
                'reminders' => ['useDefault' => true],
            ]);
            $results = $service->events->insert($calendarId, $event1);
          
        }

        if ($request->all_employees) {
            $attendees = User::allEmployees();
            foreach ($attendees as $attendee) {
                EventAttendee::create(['user_id' => $attendee->id, 'event_id' => $event->id]);
            }

            Notification::send($attendees, new EventInvite($event));
        }

        if ($request->user_id) {
            foreach ($request->user_id as $userId) {
                EventAttendee::firstOrCreate(['user_id' => $userId, 'event_id' => $event->id]);
            }
            $attendees = User::whereIn('id', $request->user_id)->get();
            Notification::send($attendees, new EventInvite($event));
        }

        return Reply::success(__('messages.eventCreateSuccess'));
    }

    public function edit($id)
    {
        $this->employees = User::doesntHave('attendee', 'and', function ($query) use ($id) {
            $query->where('event_id', $id);
        })
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->get();
        $this->event = Event::findOrFail($id);
        $view = view('admin.event-calendar.edit', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function update(UpdateEvent $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->event_name = $request->event_name;
        $event->where = $request->where;
        $event->description = $request->description;
        $event->start_date_time = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->global->time_format, $request->start_time)->format('H:i:s');
        $event->end_date_time = Carbon::createFromFormat($this->global->date_format, $request->end_date)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->global->time_format, $request->end_time)->format('H:i:s');

        if ($request->repeat) {
            $event->repeat = $request->repeat;
        } else {
            $event->repeat = 'no';
        }

        $event->repeat_every = $request->repeat_count;
        $event->repeat_cycles = $request->repeat_cycles;
        $event->repeat_type = $request->repeat_type;
        $event->label_color = $request->label_color;
        $event->save();

        if ($request->all_employees) {
            $attendees = User::allEmployees();
            foreach ($attendees as $attendee) {
                $checkExists = EventAttendee::where('user_id', $attendee->id)->where('event_id', $event->id)->first();
                if (!$checkExists) {
                    EventAttendee::create(['user_id' => $attendee->id, 'event_id' => $event->id]);

                    //      Send notification to user
                    $notifyUser = User::withoutGlobalScope('active')->findOrFail($attendee->id);
                    $notifyUser->notify(new EventInvite($event));
                }
            }
        }

        if ($request->user_id) {
            foreach ($request->user_id as $userId) {
                $checkExists = EventAttendee::where('user_id', $userId)->where('event_id', $event->id)->first();
                if (!$checkExists) {
                    EventAttendee::create(['user_id' => $userId, 'event_id' => $event->id]);

                    //      Send notification to user
                    $notifyUser = User::withoutGlobalScope('active')->findOrFail($userId);
                    $notifyUser->notify(new EventInvite($event));
                }
            }
        }

        return Reply::success(__('messages.eventCreateSuccess'));
    }

    public function show($id)
    {
        $this->startDate = explode(' ', request('start'));
        $this->startDate = Carbon::parse($this->startDate[0]);
        $this->event = Event::findOrFail($id);
        return view('admin.event-calendar.show', $this->data);
    }

    public function removeAttendee(Request $request)
    {
        EventAttendee::destroy($request->attendeeId);
        $id = $request->event_id;
        $employees = User::doesntHave('attendee', 'and', function ($query) use ($id) {
            $query->where('event_id', $id);
        })
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->get();

        $employeesArray = [];

        foreach ($employees as $key => $employee) {
            $employeesArray[$key]['id'] = $employee->id;
            $employeesArray[$key]['text'] = (auth()->user()->id == $employee->id) ? $employeesArray[$key]['text'] = $employee->name . ' (You)' : $employeesArray[$key]['text'] = $employee->name;
        }

        return Reply::dataOnly(['status' => 'success', 'employees' => $employeesArray]);
    }

    public function destroy($id)
    {
        Event::destroy($id);
        return Reply::success(__('messages.eventDeleteSuccess'));
    }
    public function logingoogle(){
       
       
        // if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
           
        //      $service = new Google_Service_Calendar($this->client);

        //     $calendarId = 'primary';

        //     $results = $service->events->listEvents($calendarId);
        //     $res= $results->getItems();
           
        //     $this->events =$res;
        //     return view('admin.event-calendar.index', $this->data);
        // }else{

        $rurl = url('/admin/logingoogle');

      //dd()
       $this->client->setRedirectUri($rurl);
        

           
        if (!isset($_GET['code'])) {
            $auth_url =  $this->client->createAuthUrl();
            $filtered_url = filter_var($auth_url, FILTER_SANITIZE_URL);
            //dd($filtered_url);
            return redirect($filtered_url);
        } else {
           $this->client->authenticate($_GET['code']);
           $_SESSION['client'] = $this->client;
            $_SESSION['access_token'] = $this->client->getAccessToken();
            $this->employees = User::all();
           // $this->events = Event::all();
            $service = new Google_Service_Calendar($this->client);

            $calendarId = 'primary';

            $results = $service->events->listEvents($calendarId);
            $res= $results->getItems();
           
            $this->events =$res;
            return view('admin.event-calendar.index', $this->data);
        }
  //  }
    }
}
