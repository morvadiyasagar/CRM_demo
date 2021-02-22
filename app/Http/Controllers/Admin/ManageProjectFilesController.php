<?php

namespace App\Http\Controllers\Admin;

use App\FileStorage;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\StoreFileLink;
use App\Project;
use App\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_ParentReference;
use Redirect, Response;
use App\ClientDetails;
use Illuminate\Support\Facades\Auth;

class ManageProjectFilesController extends AdminBaseController
{

    private $mimeType = [
        'txt' => 'fa-file-text',
        'htm' => 'fa-file-code-o',
        'html' => 'fa-file-code-o',
        'php' => 'fa-file-code-o',
        'css' => 'fa-file-code-o',
        'js' => 'fa-file-code-o',
        'json' => 'fa-file-code-o',
        'xml' => 'fa-file-code-o',
        'swf' => 'fa-file-o',
        'flv' => 'fa-file-video-o',

        // images
        'png' => 'fa-file-image-o',
        'jpe' => 'fa-file-image-o',
        'jpeg' => 'fa-file-image-o',
        'jpg' => 'fa-file-image-o',
        'gif' => 'fa-file-image-o',
        'bmp' => 'fa-file-image-o',
        'ico' => 'fa-file-image-o',
        'tiff' => 'fa-file-image-o',
        'tif' => 'fa-file-image-o',
        'svg' => 'fa-file-image-o',
        'svgz' => 'fa-file-image-o',

        // archives
        'zip' => 'fa-file-o',
        'rar' => 'fa-file-o',
        'exe' => 'fa-file-o',
        'msi' => 'fa-file-o',
        'cab' => 'fa-file-o',

        // audio/video
        'mp3' => 'fa-file-audio-o',
        'qt' => 'fa-file-video-o',
        'mov' => 'fa-file-video-o',
        'mp4' => 'fa-file-video-o',
        'mkv' => 'fa-file-video-o',
        'avi' => 'fa-file-video-o',
        'wmv' => 'fa-file-video-o',
        'mpg' => 'fa-file-video-o',
        'mp2' => 'fa-file-video-o',
        'mpeg' => 'fa-file-video-o',
        'mpe' => 'fa-file-video-o',
        'mpv' => 'fa-file-video-o',
        '3gp' => 'fa-file-video-o',
        'm4v' => 'fa-file-video-o',

        // adobe
        'pdf' => 'fa-file-pdf-o',
        'psd' => 'fa-file-image-o',
        'ai' => 'fa-file-o',
        'eps' => 'fa-file-o',
        'ps' => 'fa-file-o',

        // ms office
        'doc' => 'fa-file-text',
        'rtf' => 'fa-file-text',
        'xls' => 'fa-file-excel-o',
        'ppt' => 'fa-file-powerpoint-o',
        'docx' => 'fa-file-text',
        'xlsx' => 'fa-file-excel-o',
        'pptx' => 'fa-file-powerpoint-o',


        // open office
        'odt' => 'fa-file-text',
        'ods' => 'fa-file-text',
    ];

    /**
     * ManageProjectFilesController constructor.
     */
    private $drive;
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = 'app.menu.projects';

      
    }
    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->project = Project::with('members', 'members.user')->findOrFail($request->project_id);
        if ($request->hasFile('file')) {
            $upload = can_upload($request->file->getSize()/(1000*1024));
            if($upload) {
                $file = new ProjectFile();
                $file->user_id = $this->user->id;
                $file->project_id = $request->project_id;
                $filename = Files::uploadLocalOrS3($request->file,'project-files/'.$request->project_id);

                $file->filename = $request->file->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $request->file->getSize();
                $file->save();
                $this->logProjectActivity($request->project_id, __('messages.newFileUploadedToTheProject'));
            } else {
                return Reply::error(__('messages.storageLimitExceed', ['here' => '<a href='.route('admin.billing.packages'). '>Here</a>']));
            }
        }

        $this->project = Project::findOrFail($request->project_id);
        $this->icon($this->project);

        if($request->view == 'list') {
            $view = view('admin.projects.project-files.ajax-list', $this->data)->render();
        } else {
            $view = view('admin.projects.project-files.thumbnail-list', $this->data)->render();
        }

        return Reply::successWithData(__('messages.fileUploaded'), ['html' => $view]);
    }

    public function storeMultiple(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setAccessType('offline');
      
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
                $client->refreshToken(Auth::user()->refresh_token);

        //$client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        
        $this->drive = new Google_Service_Drive($client);
        $limitReached = false;
        if ($request->hasFile('file')) {
            $projectdetalis= Project::where('id',$request->project_id)->first();
            $project_folder_id=$projectdetalis->project_folder_id;

            foreach ($request->file as $fileData){
                $upload = can_upload($fileData->getSize()/(1000*1024));
                if($upload) {
                    $storage = config('filesystems.default');
                    $newfile = new \Google_Service_Drive_DriveFile(array(
                        'name' => $fileData->getClientOriginalName(),
                        'parents' => array($project_folder_id)
                    ));
            
                    //for storing of uploded file 
            $result = $this->drive->files->create($newfile, array(
            'data' =>file_get_contents($fileData),
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'media'
            ));
                   
         
                    $file = new ProjectFile();
                    $file->user_id = $this->user->id;
                    $file->project_id = $request->project_id;

                    //$filename = Files::uploadLocalOrS3($fileData,'project-files/'.$request->project_id);

                    $file->filename = $fileData->getClientOriginalName();
                    $file->hashname = '';
                    $file->fileid = $result->id;
                    $file->size = $fileData->getSize();
                    $file->save();
                  
                   

                    $this->logProjectActivity($request->project_id, __('messages.newFileUploadedToTheProject'));
                } else {
                    $limitReached = true;
                }
            }
            if($limitReached) {
                return Reply::error(__('messages.storageLimitExceed', ['here' => '<a href='.route('admin.billing.packages'). '>Here</a>']));
            }
        }
        return Reply::redirect(route('admin.projects.index'), __('modules.projects.projectUpdated'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::findOrFail($id);
        $this->upload = true;

        if(company()->package->max_storage_size != -1) {
            $fileStorage = FileStorage::all();
            if(company()->package->storage_unit == 'mb') {
                $totalSpace = company()->package->max_storage_size;
            } else {
                $totalSpace = company()->package->max_storage_size*1024;
            }
            //used space in mb
            $usedSpace = $fileStorage->count() > 0 ? round($fileStorage->sum('size')/(1000*1024), 4) : 0;

            if($usedSpace > $totalSpace) {
                $this->upload = false;
            }
        }

        return view('admin.projects.project-files.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setAccessType('offline');
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));        

        $client->refreshToken(Auth::user()->refresh_token);

        //$client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
       
        //for  delete file from drive
        $this->drive = new Google_Service_Drive($client);

        $storage = config('filesystems.default');
        $file = ProjectFile::findOrFail($id);
        $fileId= $file->fileid;

      
       
        if($fileId !=""){
            $this->drive->files->delete($fileId);
             
        }
      
        ProjectFile::where('id',$id)->delete();

        


//         switch($storage) {
//             case 'local':
// //                File::delete('user-uploads/project-files/'.$file->project_id.'/'.$file->hashname);
//                 Files::deleteFile($file->hashname, 'project-files/'.$file->project_id);
//                 break;
//             case 's3':
//                 Storage::disk('s3')->delete('project-files/'.$file->project_id.'/'.$file->hashname);
//                 Files::deleteFile($file->hashname, 'project-files/'.$file->project_id);
//                 break;

//         }
        //for storing of delete file from drive
        ProjectFile::destroy($id);
        $this->project = Project::findOrFail($file->project_id);
        $this->icon($this->project);
        if($request->view == 'list') {
            $view = view('admin.projects.project-files.ajax-list', $this->data)->render();
        } else {
            $view = view('admin.projects.project-files.thumbnail-list', $this->data)->render();
        }
        return Reply::successWithData(__('messages.fileDeleted'), ['html' => $view]);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id) {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setAccessType('offline');
        $client->addScope("https://www.googleapis.com/auth/drive");
        $client->refreshToken(Auth::user()->refresh_token);

        //$client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        
        $this->drive = new Google_Service_Drive($client);

        $fileinfo = ProjectFile::findOrFail($id);
        $fileId = $fileinfo->fileid;
        $file = $this->drive->files->get($fileId);
        $fileName = $file->getName();
        
        
        
        // Download a file. from drive //change by vishal
        $content = $this->drive->files->get($fileId, array("alt" => "media"));
        $headers = $content->getHeaders();
        foreach ($headers as $name => $values) {
            header($name . ': ' . implode(', ', $values));
        }
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $content->getBody();
        exit;

        //return download_local_s3($file, 'project-files/' . $file->project_id . '/' . $file->hashname);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function thumbnailShow(Request $request)
    {
        $this->project = Project::with('files')->findOrFail($request->id);
        $this->icon($this->project);
        $view = view('admin.projects.project-files.thumbnail-list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * @param $projects
     */
    private function icon($projects) {
        foreach ($projects->files as $project) {
            if (is_null($project->external_link)) {
                $ext = pathinfo($project->filename, PATHINFO_EXTENSION);
                if ($ext == 'png' || $ext == 'jpe' || $ext == 'jpeg' || $ext == 'jpg' || $ext == 'gif' || $ext == 'bmp' ||
                    $ext == 'ico' || $ext == 'tiff' || $ext == 'tif' || $ext == 'svg' || $ext == 'svgz' || $ext == 'psd' || $ext == 'csv')
                {
                    $project->icon = 'images';
                } else {
                    $project->icon = $this->mimeType[$ext];
                }

            }
        }
    }


    public function storeLink(StoreFileLink $request)
    {
        $file = new ProjectFile();
        $file->user_id = $this->user->id;
        $file->company_id = $this->user->company_id;
        $file->project_id = $request->project_id;
        $file->external_link = $request->external_link;
        $file->filename = $request->filename;
        $file->save();
        $this->logProjectActivity($request->project_id, __('messages.newFileUploadedToTheProject'));

        return Reply::success(__('messages.fileUploaded'));
    }
}
