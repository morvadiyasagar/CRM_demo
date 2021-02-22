<?php

namespace App\Http\Controllers\Admin;

use App\ClientDetails;
use App\Contract;
use App\ContractDiscussion;
use App\ContractFile;
use App\ContractSign;
use App\ContractType;
use App\DataTables\Admin\ContractsDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Contract\SignRequest;
use App\Http\Requests\Admin\Contract\StoreDiscussionRequest;
use App\Http\Requests\Admin\Contract\StoreRequest;
use App\Http\Requests\Admin\Contract\UpdateDiscussionRequest;
use App\Http\Requests\Admin\Contract\UpdateRequest;
use App\User;
use App\Helper\Files;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminContractController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'fa fa-file';
        $this->pageTitle = 'app.menu.contracts';
        $this->middleware(function ($request, $next) {
            if (!in_array('contracts', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(ContractsDataTable $dataTable)
    {
        $this->clients = ClientDetails::join('users', 'client_details.user_id', '=', 'users.id')
            ->select('users.id', 'client_details.name', 'users.email', 'users.email_notifications', 'users.created_at', 'client_details.company_name', 'users.image')
            ->where('users.status', 'active')
            ->get();
        $this->contractType = ContractType::all();
        $this->contractCounts = Contract::count();
        $this->expiredCounts = Contract::where(DB::raw('DATE(`end_date`)'), '<', Carbon::now()->format('Y-m-d'))->count();
        $this->aboutToExpireCounts = Contract::where(DB::raw('DATE(`end_date`)'), '>', Carbon::now()->format('Y-m-d'))
            ->where(DB::raw('DATE(`end_date`)'), '<', Carbon::now()->addDays(7)->format('Y-m-d'))
            ->count();
        return $dataTable->render('admin.contracts.index', $this->data);
    }

    public function create()
    {
        $this->clients = User::allClients();
        $this->contractType = ContractType::all();
        $this->upload = can_upload();
        return view('admin.contracts.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $contract = new Contract();
        $contract = $this->storeUpdate($request, $contract);

        return Reply::redirect(route('admin.contracts.edit', $contract->id), __('messages.contractAdded'));
    }

    public function edit($id)
    {
        $this->clients = User::allClients();
        $this->contractType = ContractType::all();
        $this->contract = Contract::with('signature', 'renew_history', 'renew_history.renewedBy')->find($id);
        $this->upload = can_upload();
        return view('admin.contracts.edit', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $this->storeUpdate($request, $contract);

        return Reply::redirect(route('admin.contracts.index'), __('messages.contractUpdated'));
    }

    public function show($id)
    {
        $this->contract = Contract::whereRaw('md5(id) = ?', $id)
            ->with('client', 'contract_type', 'signature', 'discussion', 'discussion.user')
            ->firstOrFail();

        return view('admin.contracts.view', $this->data);
    }

    private function storeUpdate($request, $contract)
    {
        $contract->client_id            = $request->client;
        $contract->subject              = $request->subject;
        $contract->amount               = $request->amount;
        $contract->original_amount      = $request->amount;
        $contract->contract_name = $request->contract_name;
        $contract->alternate_address = $request->alternate_address;
        $contract->mobile = $request->mobile;
        $contract->office_phone = $request->office_phone;
        $contract->city = $request->city;
        $contract->state = $request->state;
        $contract->country = $request->country;
        $contract->postal_code = $request->postal_code;
        $contract->contract_type_id     = $request->contract_type;
        $contract->start_date           = Carbon::createFromFormat($this->global->date_format,$request->start_date)->format('Y-m-d');
        $contract->original_start_date  = Carbon::createFromFormat($this->global->date_format,$request->start_date)->format('Y-m-d');
        $contract->end_date             = $request->end_date== null ? $request->end_date : Carbon::createFromFormat($this->global->date_format, $request->end_date)->format('Y-m-d');
        $contract->original_end_date    = $request->end_date== null  ? $request->end_date : Carbon::createFromFormat($this->global->date_format, $request->end_date)->format('Y-m-d');
        $contract->description          = $request->description;
        if ($request->hasFile('company_logo')) {
            Files::deleteFile($contract->company_logo, 'avatar');
            $contract->company_logo = Files::upload($request->company_logo, 'avatar', 300);
        }

        if ($request->contract_detail) {
            $contract->contract_detail = $request->contract_detail;
        }
        $contract->save();

        return $contract;
    }

    public function destroy($id)
    {
        $taskFiles = ContractFile::where('contract_id', $id)->get();

        foreach ($taskFiles as $file) {
            Files::deleteFile($file->hashname, 'contract-files/' . $file->contract_id);
            $file->delete();
        }

        Contract::destroy($id);

        return Reply::success(__('messages.contactDeleted'));
    }

    public function download($id)
    {
        $this->contract = Contract::findOrFail($id);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.contracts.contract-pdf', $this->data);

        $filename = 'contract-' . $this->contract->id;

        return $pdf->download($filename . '.pdf');
    }

    public function addDiscussion(StoreDiscussionRequest $request, $id)
    {
        $contractDiscussion = new ContractDiscussion();
        $contractDiscussion->from = $this->user->id;
        $contractDiscussion->message = $request->message;
        $contractDiscussion->contract_id = $id;
        $contractDiscussion->save();

        return Reply::redirect(route('admin.contracts.show', md5($id) . '#discussion'), __('messages.addDiscussion'));
    }

    public function contractSignModal($id)
    {
        $this->contract = Contract::find($id);
        return view('admin.contracts.accept', $this->data);
    }

    public function contractSign(SignRequest $request, $id)
    {
        $this->contract = Contract::whereRaw('md5(id) = ?', $id)->firstOrFail();

        if (!$this->contract) {
            return Reply::error('you are not authorized to access this.');
        }

        $sign = new ContractSign();
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->contract->id;
        $sign->email = $request->email;

        $image = $request->signature;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = str_random(32) . '.' . 'jpg';

        if (!\File::exists(public_path('user-uploads/' . 'contract/sign'))) {
            $result = \File::makeDirectory(public_path('user-uploads/contract/sign'), 0775, true);
        }

        \File::put(public_path() . '/user-uploads/contract/sign/' . $imageName, base64_decode($image));

        $sign->signature = $imageName;
        $sign->save();

        return Reply::redirect(route('admin.contracts.show', md5($this->contract->id)));
    }

    public function editDiscussion($id)
    {
        $this->discussion = ContractDiscussion::find($id);
        return view('admin.contracts.edit-discussion', $this->data);
    }

    public function updateDiscussion(UpdateDiscussionRequest $request, $id)
    {
        $this->discussion = ContractDiscussion::find($id);
        $this->discussion->message = $request->messages;
        $this->discussion->save();

        return Reply::success(__('modules.contracts.discussionUpdated'));
    }

    public function removeDiscussion($id)
    {
        ContractDiscussion::destroy($id);

        return Reply::success(__('modules.contracts.discussionDeleted'));
    }

    public function copy($id)
    {
        $this->clients = User::allClients();
        $this->contractType = ContractType::all();
        $this->contract = Contract::with('signature', 'renew_history', 'renew_history.renewedBy')->find($id);
        return view('admin.contracts.copy', $this->data);
    }

    public function copySubmit(StoreRequest $request)
    {
        $contract  = new Contract();
        $contract->client_id            = $request->client;
        $contract->subject              = $request->subject;
        $contract->amount               = $request->amount;
        $contract->original_amount      = $request->amount;
        $contract->contract_type_id     = $request->contract_type;
        $contract->start_date           = Carbon::createFromFormat($this->global->date_format,$request->start_date)->format('Y-m-d');
        $contract->original_start_date  = Carbon::createFromFormat($this->global->date_format,$request->start_date)->format('Y-m-d');
        $contract->end_date             = Carbon::createFromFormat($this->global->date_format,$request->end_date)->format('Y-m-d');
        $contract->original_end_date    = Carbon::createFromFormat($this->global->date_format,$request->end_date)->format('Y-m-d');
        $contract->description          = $request->description;

        if ($request->contract_detail) {
            $contract->contract_detail = $request->contract_detail;
        }

        $contract->save();

        return Reply::redirect(route('admin.contracts.edit', $contract->id), __('messages.contractAdded'));
    }
}
