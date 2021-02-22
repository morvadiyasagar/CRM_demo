<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\DataTables\Admin\DiscussionDataTable;
use App\DataTables\Admin\ProjectsDataTable;
use App\Discussion;
use App\DiscussionCategory;
use App\DiscussionReply;
use App\Expense;
use App\Helper\Reply;
use App\Http\Requests\Project\StoreProject;
use App\Http\Requests\Project\UpdateProject;
use App\Payment;
use App\Pinned;
use App\ProjectActivity;
use App\ProjectCategory;
use App\ProjectFile;
use App\ProjectMember;
use App\ProjectTemplate;
use App\ProjectTemplateMember;
use App\ProjectTimeLog;
use App\Task;
use App\TaskboardColumn;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Project;
use App\ProjectMilestone;
use App\TaskUser;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\ProjectProgress;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_ParentReference;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Redirect, Response;
use File;
use App\ClientDetails;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Auth;

class ManageProjectsController extends AdminBaseController
{

    use ProjectProgress;
    private $drive;//
    private $gclient;
    private $dbtabe;
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->pageIcon = 'icon-layers';
        $this->middleware(function ($request, $next) {
            if (!in_array('projects', $this->user->modules)) {
                abort(403);
            }
           
            if(Auth::user()->refresh_token !=""){// changes by vishal
         
                $client = new Google_Client();
               $client->setClientId(env('GOOGLE_CLIENT_ID'));
               $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
               $client->setAccessType('offline');
             $client->setScopes(array('https://www.googleapis.com/auth/drive'));


                $client->refreshToken(Auth::user()->refresh_token);

               
               
               $this->drive = new Google_Service_Drive($client);
             
                       }
            return $next($request);
        });

$calclient = new Google_Client();// changes by vishal
$calclient->setAuthConfig('client_secret.json');
$calclient->addScope(Google_Service_Calendar::CALENDAR);
$calclient->setAccessType('offline');
$calclient->setApprovalPrompt('force');
$guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
$calclient->setHttpClient($guzzleClient);
$this->gclient = $calclient;  

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectsDataTable $dataTable)
    {
        $this->dbtabe=$dataTable;
        $this->clients = User::allClients();
        $this->totalProjects = Project::count();
        $this->finishedProjects = Project::finished()->count();
        $this->inProcessProjects = Project::inProcess()->count();
        $this->onHoldProjects = Project::onHold()->count();
        $this->canceledProjects = Project::canceled()->count();
        $this->notStartedProjects = Project::notStarted()->count();
        $this->overdueProjects = Project::overdue()->count();
        $this->allEmployees = User::allEmployees();
        //Budget Total
        $this->projectBudgetTotal = Project::sum('project_budget');
        $this->categories = ProjectCategory::all();

        $this->projectEarningTotal = Payment::join('projects', 'projects.id', '=', 'payments.project_id')
            ->where('payments.status', 'complete')
            ->whereNotNull('projects.project_budget')
            ->whereNotNull('payments.project_id')
            ->sum('payments.amount');

        return $dataTable->render('admin.projects.index', $this->data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function archive()
    {
        $this->totalProjects = Project::onlyTrashed()->count();
        $this->clients = User::allClients();
        return view('admin.projects.archive', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
     
        
        if ((session()->get('access_token') !="") && session()->get('access_token')) {
            $this->clients = User::allClients();
        $this->categories = ProjectCategory::all();
        $this->templates = ProjectTemplate::all();
        $this->currencies = Currency::all();
        $this->employees = User::allEmployees();

        $project = new Project();
        $this->upload = can_upload();
        $this->fields = $project->getCustomFieldGroupsWithFields()->fields;
       return view('admin.projects.create', $this->data);
        }else{
            return redirect('admin/logingoogleproject');
  
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProject $request)
    {
      
       
        $memberExistsInTemplate = false;
        $projectfolderid="";
        if($request->client_id >0){
          
        $clientdetalis= ClientDetails::where('user_id',$request->client_id)->first();
        $client_folder=$clientdetalis->client_folder_id;
       
      //for creting a folder
        $folder_meta = new Google_Service_Drive_DriveFile(array(// changes by vishal
            'name' => $request->project_name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($client_folder) //to create sub dir. under summary folder
    ));
            $projectfolder= $this->drive->files->create($folder_meta, array(
                'fields' => 'id'));
                $projectfolderid=$projectfolder->id;

            }else{
                $projectfolderid="";
            }
          
            $cal_event_id="";
            //for creting a cal Event
            if ((session()->get('access_token') !="") && session()->get('access_token')) {
                $this->gclient->setAccessToken(session()->get('access_token'));
                if ($this->gclient->isAccessTokenExpired()) { 
                    $refreshTokenSaved = Auth::user()->cal_ref_token; 
                    $this->gclient->fetchAccessTokenWithRefreshToken($refreshTokenSaved); 
                   
                    $accessTokenUpdated = $this->gclient->getAccessToken();

                   // $this->gclient->fetchAccessTokenWithRefreshToken($this->gclient->getRefreshToken());
                   // $this->gclient->setAccessToken($this->gclient->getAccessToken()); 
                    User::where('id', Auth::user()->id)->update(['access_token' =>$accessTokenUpdated]);

                session()->put('access_token',  $accessTokenUpdated); 
                }
               
    
                $service = new Google_Service_Calendar($this->gclient);
    
                $calendarId = 'primary';
                $event = new Google_Service_Calendar_Event([
                    'summary' => $request->project_name,
                    'description' => $request->project_summary,
                    'start' => ['date' =>  Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d')],
                    'end' => ['date' =>  Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d')],
                    'reminders' => ['useDefault' => true],
                ]);
                $results = $service->events->insert($calendarId, $event);
                    $cal_event_id=$results['id'];
                
            } 

        $project = new Project();
        $project->project_name = $request->project_name;
        $project->cal_event_id = $cal_event_id;
        if ($request->project_summary != '') {
            $project->project_summary = $request->project_summary;
        }
        $project->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');

        if (!$request->has('without_deadline')) {
            $project->deadline = Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d');
        }

        if ($request->notes != '') {
            $project->notes = $request->notes;
        }
        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }
        $project->client_id = $request->client_id;

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        } else {
            $project->client_view_task = "disable";
        }
        if (($request->client_view_task) && ($request->client_task_notification)) {
            $project->allow_client_notification = 'enable';
        } else {
            $project->allow_client_notification = "disable";
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        } else {
            $project->manual_timelog = "disable";
        }

        $project->project_budget = $request->project_budget;

        $project->currency_id = $request->currency_id;
        if (!$request->currency_id) {
            $project->currency_id = $this->global->currency_id;
        }

        $project->hours_allocated = $request->hours_allocated;
        $project->status = $request->status;
        $project->project_folder_id = $projectfolderid;

        $project->save();

        if ($request->template_id) {
            $template = ProjectTemplate::findOrFail($request->template_id);

            foreach ($template->members as $member) {
                $projectMember = new ProjectMember();

                $projectMember->user_id    = $member->user_id;
                $projectMember->project_id = $project->id;
                $projectMember->save();

                if ($member->user_id == $this->user->id) {
                    $memberExistsInTemplate = true;
                }
            }
            foreach ($template->tasks as $task) {
                $projectTask = new Task();

                $projectTask->project_id  = $project->id;
                $projectTask->heading     = $task->heading;
                $projectTask->task_category_id     = $task->project_template_task_category_id;
                $projectTask->description = $task->description;
                $projectTask->due_date    = Carbon::now()->addDay()->format('Y-m-d');
                $projectTask->status      = 'incomplete';
                $projectTask->save();

                foreach ($task->users_many as $key => $value) {
                    TaskUser::create(
                        [
                            'user_id' => $value->id,
                            'task_id' => $projectTask->id
                        ]
                    );
                }
            }
        }

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $project->updateCustomFieldData($request->get('custom_fields_data'));
        }

        $users = $request->user_id;

        
       
       

        foreach ($users as $user) {
            $member = new ProjectMember();
            $member->user_id = $user;
            $member->project_id = $project->id;
            $member->save();

            $this->logProjectActivity($project->id, ucwords($member->user->name) . ' ' . __('messages.isAddedAsProjectMember'));
        }

        $this->logSearchEntry($project->id, 'Project: ' . $project->project_name, 'admin.projects.show', 'project');

        $this->logProjectActivity($project->id, ucwords($project->project_name) . ' ' . __("messages.addedAsNewProject"));

        return Reply::dataOnly(['projectID' => $project->id]);

    
        //return Reply::redirect(route('admin.projects.index'), __('modules.projects.projectUpdated'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::findOrFail($id)->withCustomFields();
        $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;
        $this->activeTimers = ProjectTimeLog::projectActiveTimers($this->project->id);

        if (is_null($this->project->deadline)) {
            $this->daysLeft = 0;
        } else {
            if ($this->project->deadline->isPast()) {
                $this->daysLeft = 0;
            } else {
                $this->daysLeft = $this->project->deadline->diff(Carbon::now())->format('%d') + ($this->project->deadline->diff(Carbon::now())->format('%m') * 30) + ($this->project->deadline->diff(Carbon::now())->format('%y') * 12);
            }

            $this->daysLeftFromStartDate = $this->project->deadline->diff($this->project->start_date)->format('%d') + ($this->project->deadline->diff($this->project->start_date)->format('%m') * 30) + ($this->project->deadline->diff($this->project->start_date)->format('%y') * 12);

            $this->daysLeftPercent = ($this->daysLeftFromStartDate == 0 ? "0" : (($this->daysLeft / $this->daysLeftFromStartDate) * 100));
        }


        $this->hoursLogged = $this->project->times()->sum('total_minutes');

        $this->hoursLogged = intdiv($this->hoursLogged, 60);

        $this->activities = ProjectActivity::getProjectActivities($id, 10);
        $this->earnings = Payment::where('status', 'complete')
            ->where('project_id', $id)
            ->sum('amount');
        $this->expenses = Expense::where(['project_id' => $id, 'status' => 'approved'])->sum('price');
        $this->milestones = ProjectMilestone::with('currency')->where('project_id', $id)->get();

        $this->taskBoardStatus = TaskboardColumn::all();

        $this->taskStatus = array();
        $projectTasksCount = Task::where('project_id', $id)->count();
        if ($projectTasksCount > 0) {
            foreach ($this->taskBoardStatus as $key => $value) {
                $totalTasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
                    ->where('tasks.board_column_id', $value->id)
                    ->where('tasks.project_id', $id);
                $taskStatus[$value->slug] = [
                    'count' => $totalTasks->count(),
                    'label' => $value->column_name,
                    'color' => $value->label_color
                ];
            }
            $this->taskStatus = json_encode($taskStatus);
        }


        $incomes = [];
        $graphData = [];
        $invoices = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where('payments.status', 'complete')
            ->where('payments.project_id', $id)
            // ->groupBy('year', 'month')
            ->orderBy('paid_on', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(paid_on,"%M/%y") as date'),
                DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
                DB::raw('amount as total'),
                'currencies.id as currency_id',
                'currencies.exchange_rate'
            ]);

        foreach ($invoices as $invoice) {
            if (!isset($incomes[$invoice->date])) {
                $incomes[$invoice->date] = 0;
            }

            if ($invoice->currency_id != $this->global->currency->id && $invoice->exchange_rate > 0) {
                $incomes[$invoice->date] += floor($invoice->total / $invoice->exchange_rate);
            } else {
                $incomes[$invoice->date] += round($invoice->total, 2);
            }
        }

        $dates = array_keys($incomes);

        foreach ($dates as $date) {
            $graphData[] = [
                'date' =>  $date,
                'total' =>  isset($incomes[$date]) ? round($incomes[$date], 2) : 0,
            ];
        }

        usort($graphData, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });

        $this->chartData = json_encode($graphData);

        $this->timechartData = DB::table('project_time_logs');

        $this->timechartData = $this->timechartData->where('project_time_logs.project_id', $id)
            ->groupBy('date', 'month')
            ->orderBy('start_time', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(start_time,"%M/%y") as date'),
                DB::raw('YEAR(start_time) year, MONTH(start_time) month'),
                // DB::raw('DATE_FORMAT(start_time,\'%d/%M/%y\') as date'),
                DB::raw('FLOOR(sum(total_minutes/60)) as total_hours')
            ])
            ->toJSON();

        return view('admin.projects.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->clients = User::allClients();
        $this->categories = ProjectCategory::all();
        $this->project = Project::findOrFail($id)->withCustomFields();
        $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;
        $this->currencies = Currency::all();
        return view('admin.projects.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
    * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProject $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->project_name = $request->project_name;
        if ($request->project_summary != '') {
            $project->project_summary = $request->project_summary;
        }
        $project->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');

        if (!$request->has('without_deadline')) {
            $project->deadline = Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d');
        } else {
            $project->deadline = null;
        }

        if ($request->notes != '') {
            $project->notes = $request->notes;
        }
        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        } else {
            $project->client_view_task = "disable";
        }
        if (($request->client_view_task) && ($request->client_task_notification)) {
            $project->allow_client_notification = 'enable';
        } else {
            $project->allow_client_notification = "disable";
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        } else {
            $project->manual_timelog = "disable";
        }

        $project->client_id = ($request->client_id == 'null' || $request->client_id == '') ? null : $request->client_id;
        $project->feedback = $request->feedback;

        if ($request->calculate_task_progress) {
            $project->calculate_task_progress = $request->calculate_task_progress;
            $project->completion_percent = $this->calculateProjectProgress($id);
        } else {
            $project->calculate_task_progress = "false";
            $project->completion_percent = $request->completion_percent;
        }


        $project->project_budget = $request->project_budget;
        $project->currency_id = $request->currency_id;
        $project->hours_allocated = $request->hours_allocated;
        $project->status = $request->status;

        $project->save();
        $projectinfo=Project::where('id',$id)->first();
       $cal_event_id=$projectinfo->cal_event_id;

        if ((session()->get('access_token') !="") && session()->get('access_token')) {
            $this->gclient->setAccessToken(session()->get('access_token'));

            if ($this->gclient->isAccessTokenExpired()) { 
                $this->gclient->fetchAccessTokenWithRefreshToken(Auth::user()->cal_ref_token);
                $this->gclient->setAccessToken($this->gclient->getAccessToken()); 
                User::where('id', Auth::user()->id)->update(['access_token' =>$this->gclient->getAccessToken()]);

            session()->put('access_token',  $this->gclient->getAccessToken()); 
            }
          

            $service = new Google_Service_Calendar($this->gclient);

            $calendarId = 'primary';
            $event = new Google_Service_Calendar_Event([
                'summary' => $request->project_name,
                'description' => $request->project_summary,
                'start' => ['date' =>  Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d')],
                'end' => ['date' =>  Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d')],
                'reminders' => ['useDefault' => true],
            ]);
            if($cal_event_id !=""){
            $results = $service->events->update($calendarId,$cal_event_id, $event);
            }
              
            
        } 


        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $project->updateCustomFieldData($request->get('custom_fields_data'));
        }

        $this->logProjectActivity($project->id, ucwords($project->project_name) . __('modules.projects.projectUpdated'));
        return Reply::redirect(route('admin.projects.index'), __('messages.projectUpdated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $projectinfo=Project::where('id',$id)->first();
        $cal_event_id=$projectinfo->cal_event_id;
        $project = Project::withTrashed()->findOrFail($id);
        $project->forceDelete();

        if ((session()->get('access_token') !="") && session()->get('access_token')) {
            $this->gclient->setAccessToken(session()->get('access_token'));
            if ($this->gclient->isAccessTokenExpired()) { 
                $this->gclient->fetchAccessTokenWithRefreshToken(Auth::user()->cal_ref_token);
                $this->gclient->setAccessToken($this->gclient->getAccessToken()); 
                User::where('id', Auth::user()->id)->update(['access_token' =>$this->gclient->getAccessToken()]);

            session()->put('access_token',  $this->gclient->getAccessToken()); 
            }
          
         
            $calendarId = 'primary';
            $service = new Google_Service_Calendar($this->gclient);
            if($cal_event_id !=""){
                $results = $service->events->delete($calendarId,$cal_event_id);
                }
        }
        return Reply::success(__('messages.projectDeleted'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function archiveDestroy($id)
    {

        Project::destroy($id);

        return Reply::success(__('messages.projectArchiveSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function archiveRestore($id)
    {

        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();

        return Reply::success(__('messages.projectRevertSuccessfully'));
    }

    public function archiveData(Request $request)
    {
        $projects = Project::select('id', 'project_name', 'start_date', 'deadline', 'client_id', 'completion_percent');

        if (!is_null($request->status) && $request->status != 'all') {
            if ($request->status == 'incomplete') {
                $projects->where('completion_percent', '<', '100');
            } elseif ($request->status == 'complete') {
                $projects->where('completion_percent', '=', '100');
            }
        }

        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $projects->where('client_id', $request->client_id);
        }

        $projects->onlyTrashed()->get();

        return DataTables::of($projects)
            ->addColumn('action', function ($row) {
                return '
                      <a href="javascript:;" class="btn btn-info btn-circle revert"
                      data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="Restore"><i class="fa fa-undo" aria-hidden="true"></i></a>
                       <a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
            })
            ->addColumn('members', function ($row) {
                $members = '';

                if (count($row->members) > 0) {
                    foreach ($row->members as $member) {
                        $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($member->user->name) . '" src="' . $member->user->image_url . '"
                        alt="user" class="img-circle" width="30" height="30"> ';
                    }
                } else {
                    $members .= __('messages.noMemberAddedToProject');
                }
                return $members;
            })
            ->editColumn('project_name', function ($row) {
                return ucfirst($row->project_name);
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date->format('d M, Y');
            })
            ->editColumn('deadline', function ($row) {
                if ($row->deadline) {
                    return $row->deadline->format($this->global->date_format);
                }

                return '-';
            })
            ->editColumn('client_id', function ($row) {
                if (is_null($row->client_id)) {
                    return "";
                }
                return ucwords($row->client->name);
            })
            ->editColumn('completion_percent', function ($row) {
                if ($row->completion_percent < 50) {
                    $statusColor = 'danger';
                    $status = __('app.progress');
                } elseif ($row->completion_percent >= 50 && $row->completion_percent < 75) {
                    $statusColor = 'warning';
                    $status = __('app.progress');
                } else {
                    $statusColor = 'success';
                    $status = __('app.progress');

                    if ($row->completion_percent >= 100) {
                        $status = __('app.completed');
                    }
                }

                return '<h5>' . $status . '<span class="pull-right">' . $row->completion_percent . '%</span></h5><div class="progress">
                  <div class="progress-bar progress-bar-' . $statusColor . '" aria-valuenow="' . $row->completion_percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $row->completion_percent . '%" role="progressbar"> <span class="sr-only">' . $row->completion_percent . '% Complete</span> </div>
                </div>';
            })
            ->removeColumn('project_summary')
            ->removeColumn('notes')
            ->removeColumn('category_id')
            ->removeColumn('feedback')
            ->removeColumn('start_date')
            ->rawColumns(['project_name', 'action', 'completion_percent', 'members'])
            ->make(true);
    }

    public function export($status = null, $clientID = null)
    {
        $projects = Project::leftJoin('users', 'users.id', '=', 'projects.client_id')
            ->leftJoin('project_category', 'project_category.id', '=', 'projects.category_id')
            ->select(
                'projects.id',
                'projects.project_name',
                'users.name',
                'project_category.category_name',
                'projects.start_date',
                'projects.deadline',
                'projects.completion_percent',
                'projects.created_at'
            );
        if (!is_null($status) && $status != 'all') {
            if ($status == 'incomplete') {
                $projects = $projects->where('completion_percent', '<', '100');
            } elseif ($status == 'complete') {
                $projects = $projects->where('completion_percent', '=', '100');
            }
        }

        if (!is_null($clientID) && $clientID != 'all') {
            $projects = $projects->where('client_id', $clientID);
        }

        $projects = $projects->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Project Name', 'Client Name', 'Category', 'Start Date', 'Deadline', 'Completion Percent', 'Created at'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($projects as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('Projects', function ($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Projects');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('Projects file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function ($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function ($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));
                });
            });
        })->download('xlsx');
    }

    public function gantt($ganttProjectId = '')
    {

        $this->ganttProjectId = $ganttProjectId;
        //        return $ganttData;
        if ($ganttProjectId != '') {
            $this->project = Project::find($ganttProjectId);
        }
        return view('admin.projects.gantt', $this->data);
    }

    public function ganttData($ganttProjectId = '')
    {

        $data = array();
        $links = array();

        $projects = Project::select('id', 'project_name', 'start_date', 'deadline', 'completion_percent');

        if ($ganttProjectId != '') {
            $projects = $projects->where('id', '=', $ganttProjectId);
        }

        $projects = $projects->get();

        $id = 0; //count for gantt ids
        foreach ($projects as $project) {
            $id = $id + 1;
            $projectId = $id;

            // TODO::ProjectDeadline to do
            $projectDuration = 0;
            if ($project->deadline) {
                $projectDuration = $project->deadline->diffInDays($project->start_date);
            }

            $data[] = [
                'id' => $projectId,
                'text' => ucwords($project->project_name),
                'start_date' => $project->start_date->format('Y-m-d H:i:s'),
                'duration' => $projectDuration,
                'progress' => $project->completion_percent / 100,
                'project_id' => $project->id,
                'dependent_task_id' => null
            ];

            $tasks = Task::projectOpenTasks($project->id);

            foreach ($tasks as $key => $task) {
                $id = $id + 1;

                $taskDuration = $task->due_date->diffInDays($task->start_date);
                $taskDuration = $taskDuration + 1;

                $data[] = [
                    'id' => $task->id,
                    'text' => ucfirst($task->heading),
                    'start_date' => (!is_null($task->start_date)) ? $task->start_date->format('Y-m-d') : $task->due_date->format('Y-m-d'),
                    'duration' => $taskDuration,
                    'parent' => $projectId,
                    'taskid' => $task->id,
                    'dependent_task_id' => $task->dependent_task_id
                ];

                $links[] = [
                    'id' => $id,
                    'source' => $task->dependent_task_id != '' ? $task->dependent_task_id : $projectId,
                    'target' => $task->id,
                    'type' => $task->dependent_task_id != '' ? 0 : 1
                ];
            }
        }

        $ganttData = [
            'data' => $data,
            'links' => $links
        ];

        return response()->json($ganttData);
    }

    public function updateTaskDuration(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');
        $task->save();

        return Reply::success('messages.taskUpdatedSuccessfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $project = Project::find($id)
            ->update([
                'status' => $request->status
            ]);

        return Reply::dataOnly(['status' => 'success']);
    }

    public function ajaxCreate(Request $request, $projectId)
    {
        $this->pageName = 'ganttChart';

        $this->projectId = $projectId;
        $this->projects = Project::all();
        $this->employees = ProjectMember::byProject($projectId);

        $this->parentGanttId = $request->parent_gantt_id;
        $completedTaskColumn = TaskboardColumn::where('slug', '!=', 'completed')->first();
        $this->allTasks = [];
        if ($completedTaskColumn) {
            $this->allTasks = Task::where('board_column_id', $completedTaskColumn->id)
                ->where('project_id', $projectId)
                ->get();
        }

        return view('admin.tasks.ajax_create', $this->data);
    }

    public function burndownChart(Request $request, $id)
    {

        $this->project = Project::with(['tasks' => function ($query) use ($request) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $query->where(DB::raw('DATE(`start_date`)'), '>=', Carbon::createFromFormat($this->global->date_format, $request->startDate));
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $query->where(DB::raw('DATE(`due_date`)'), '<=', Carbon::createFromFormat($this->global->date_format, $request->endDate));
            }
        }])->find($id);

        $this->totalTask = $this->project->tasks->count();
        $datesArray = [];
        $startDate = $request->startDate ? Carbon::createFromFormat($this->global->date_format, $request->startDate) : Carbon::parse($this->project->start_date);
        if ($this->project->deadline) {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->global->date_format, $request->endDate) : Carbon::parse($this->project->deadline);
        } else {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->global->date_format, $request->endDate) : Carbon::now();
        }

        for ($startDate; $startDate <= $endDate; $startDate->addDay()) {
            $datesArray[] = $startDate->format($this->global->date_format);
        }

        $uncompletedTasks = [];
        $createdTasks = [];
        $deadlineTasks = [];
        $deadlineTasksCount = [];
        $this->datesArray = json_encode($datesArray);
        foreach ($datesArray as $key => $value) {
            if (Carbon::createFromFormat($this->global->date_format, $value)->lessThanOrEqualTo(Carbon::now())) {
                $uncompletedTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {
                    if (is_null($task->completed_on)) {
                        return true;
                    }
                    return $task->completed_on ? $task->completed_on->greaterThanOrEqualTo(Carbon::createFromFormat($this->global->date_format, $value)) : false;
                })->count();
                $createdTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {
                    return Carbon::createFromFormat($this->global->date_format, $value)->startOfDay()->equalTo($task->created_at->startOfDay());
                })->count();
                if ($key > 0) {
                    $uncompletedTasks[$key] += $createdTasks[$key];
                }
            }
            $deadlineTasksCount[] = $this->project->tasks->filter(function ($task) use ($value) {
                return Carbon::createFromFormat($this->global->date_format, $value)->startOfDay()->equalTo($task->due_date->startOfDay());
            })->count();
            if ($key == 0) {
                $deadlineTasks[$key] = $this->totalTask - $deadlineTasksCount[$key];
            } else {
                $newKey = $key - 1;
                $deadlineTasks[$key] = $deadlineTasks[$newKey] - $deadlineTasksCount[$key];
            }
        }

        $this->uncompletedTasks = json_encode($uncompletedTasks);
        $this->deadlineTasks = json_encode($deadlineTasks);
        if ($request->ajax()) {
            return $this->data;
        }

        $this->startDate = $request->startDate ? Carbon::parse($request->startDate)->format($this->global->date_format) : Carbon::parse($this->project->start_date)->format($this->global->date_format);
        $this->endDate = $endDate->format($this->global->date_format);

        return view('admin.projects.burndown', $this->data);
    }

    /**
     * Project discussions
     *
     * @param  int $projectId
     * @return \Illuminate\Http\Response
     */
    public function discussion(DiscussionDataTable $dataTable, $projectId)
    {
        $this->project = Project::findOrFail($projectId);
        $this->discussionCategories = DiscussionCategory::orderBy('order', 'asc')->get();
        return $dataTable->with('project_id', $projectId)->render('admin.projects.discussion.show', $this->data);
    }

    /**
     * Project discussions
     *
     * @param  int $projectId
     * @param  int $discussionId
     * @return \Illuminate\Http\Response
     */
    public function discussionReplies($projectId, $discussionId)
    {
        $this->project = Project::findOrFail($projectId);
        $this->discussion = Discussion::with('category')->findOrFail($discussionId);
        $this->discussionReplies = DiscussionReply::with('user')->where('discussion_id', $discussionId)->orderBy('id', 'asc')->get();
        return view('admin.projects.discussion.replies', $this->data);
    }

    /**
     * @param $templateId
     * @return mixed
     */
    public function templateData($templateId)
    {
        $templateMember  = [];
        $projectTemplate = ProjectTemplate::with('members')->findOrFail($templateId);

        if($projectTemplate->members){
            $templateMember  = $projectTemplate->members->pluck('user_id')->toArray();
        }

        return Reply::dataOnly(['templateData'=> $projectTemplate, 'member' => $templateMember ]);
    }

    /**
     * @return mixed
     */
    public function pinnedItem()
    {
        $this->pinnedItems = Pinned::join('projects', 'projects.id', '=', 'pinned.project_id')
            ->where('pinned.user_id','=',user()->id)
            ->select('projects.id', 'projects.project_name')
            ->get();

        return view('admin.projects.pinned-project', $this->data);
    }

    public function oauth()
    {
      

        $rurl = action('ManageProjectsController@oauth');
        $this->gclient->setRedirectUri($rurl);

        if (!isset($_GET['code'])) {
            $auth_url = $this->gclient->createAuthUrl();
            $filtered_url = filter_var($auth_url, FILTER_SANITIZE_URL);
            return redirect($filtered_url);
        } else {
            $this->gclient->authenticate($_GET['code']);
            $_SESSION['access_token'] = $this->gclient->getAccessToken();
            return redirect()->route('cal.index');
        }
    }
    //for redirect in to google login screen -
    public function logingoogleproject(){
       
    

        $rurl = url('/admin/logingoogleproject');

     
       $this->gclient->setRedirectUri($rurl);
        

           
        if (!isset($_GET['code'])) {
            $auth_url =  $this->gclient->createAuthUrl();
            $filtered_url = filter_var($auth_url, FILTER_SANITIZE_URL);
            
            return redirect($filtered_url);
        } else {
           $this->gclient->authenticate($_GET['code']);
           session()->put('access_token',  $this->gclient->getAccessToken());
         
           //User::where('id', Auth::user()->id)->update(['access_token' =>$this->gclient->getAccessToken()]);
            User::where('id', Auth::user()->id)->update(['access_token' =>$this->gclient->getAccessToken(),'cal_ref_token'=>$this->gclient->getRefreshToken()]);
           $user =  User::where('id', Auth::user()->id)->first();
           Auth::loginUsingId($user->id);  
            $this->clients = User::allClients();
            $this->categories = ProjectCategory::all();
            $this->templates = ProjectTemplate::all();
            $this->currencies = Currency::all();
            $this->employees = User::allEmployees();
    
            $project = new Project();
            $this->upload = can_upload();
            $this->fields = $project->getCustomFieldGroupsWithFields()->fields;
           return view('admin.projects.create', $this->data);
        }
  
    }
}