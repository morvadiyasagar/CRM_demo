<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use App\ProjectCategory;
use App\ProjectTemplate;
use App\Currency;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_ParentReference;
use Redirect, Response;
use File;
use App\ClientDetails;
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
use App\ProjectFile;
use App\ProjectMember;
use App\ProjectTemplateMember;
use App\ProjectTimeLog;
use App\Task;
use App\TaskboardColumn;
use Carbon\Carbon;
use App\ProjectMilestone;
use App\TaskUser;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\ProjectProgress;
use App\Company;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    //

    private $drive;//
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->pageIcon = 'icon-layers';
        ;
        $client = new Google_Client();
$client->setClientId(env('GOOGLE_CLIENT_ID'));
$client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
//$client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
$client->setAccessType('offline');

$client->refreshToken(Auth::user()->refresh_token);


$this->drive = new Google_Service_Drive($client);
    }

    public function index(Request $request)
    {
        $global[]=array(
            'rounded_theme'=>0
        );
    $clients = User::allClients();
    $data['clients']=$clients;
    $data['upload']=1;
    $data['pageTitle']='Documents';
    $setting = Company::where('id',Auth::user()->company_id)->first();
    $data['global']=$setting;
    $data['global']=$setting;
  

    $project = new Project();
    $this->upload = can_upload();
    $this->fields = $project->getCustomFieldGroupsWithFields()->fields;
    return view('admin.projects.document', $data);
    }
}
