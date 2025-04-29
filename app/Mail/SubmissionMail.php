<?php

namespace App\Mail;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Code;
use App\Models\Assignmentto;
use App\Models\Stackholders;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\Categoryhrsc;
use App\Models\Employee;

use App\Models\Submission\Project;
use App\Models\Submission\Ticket;
use App\Models\Submission\Jdi;
use App\Models\Submission\MMF\Mmf;
use App\Http\Controllers\Submission\JdiRequestController;
use App\Http\Controllers\Submission\IT\ADRequestController;
use App\Http\Controllers\Submission\Ecatalog\MaterialRequestController;
use App\Http\Controllers\Submission\MMF\M28RequestController;
use App\Http\Controllers\Submission\MMF\M30RequestController;

use Storage;
use DB;
use App\Http\Traits\HasGetModule;
use App\Http\Traits\HasGenerateCode;
// use Barryvdh\DomPDF\Facade as PDF;

class SubmissionMail extends Mailable
{
    use Queueable, SerializesModels, HasGetModule, HasGenerateCode;
    public $mailData;
    public $modulename;
    public $code;
    public $projectName;
    public $developer;
    public $final;
    public $module;
    public $attachment;
    public $category;
    public $assignment;
    public $detailmomtask;
    // public $text;
    // public $final;
    // public $file;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData,$modulename,$final)
    {
        $appEnv = env('APP_ENV');
        $url = ($appEnv == 'production') ? 'http://172.18.83.38/' : 'http://localhost/';
        $this->module = new Module();
        $this->mailData=$mailData;
        $this->modulename=$modulename;
        $this->final=$final;


        $code = Code::findOrFail($mailData['submission']->code_id);
        $this->code = $code->code;

        $Mailrecipient = DB::table('reference.tbl_mailrecipient')
        ->where('module',$modulename)
        ->where('isActive',1)
        ->get();

        // Base query for mail recipients
        $queryMr = DB::table('reference.tbl_mailrecipient')
            ->where('module', $modulename)
            ->where('isActive', 1);

        // Check if employee_id exists in $mailData['submission']
        if (isset($mailData['submission']->employee_id)) {
            // Retrieve the employee's company code directly
            $companyCode = DB::table('employee.tbl_employee')
                ->where('id', $mailData['submission']->employee_id)
                ->value('companycode');

            // If company code is found, add the company_list condition
            if ($companyCode) {
                $queryMr->where('company_list', 'like', '%' . $companyCode . '%');
            }
        }

        // Execute the query and get the results
        $Mailrecipient = $queryMr->get();

        if (!empty($mailData['submission']->category_id)) {
            $category = DB::table('tbl_categoryform')->select('nameCategory')->where('id',$mailData['submission']->category_id)->first();
            $this->category = $category->nameCategory;
        }

        // PROJECT MODULE
            if($modulename == 'Project') {
                $attachments = Attachment::where('req_id',$mailData['submission']->id)
                                    ->where('module_id',$this->getModuleId($modulename))
                                    ->get();
                $this->attachment = $attachments;
                
                if($final == 1) {
                    $stackholders = Stackholders::leftJoin('employee.tbl_employee','tbl_stackholders.employee_id','=','employee.tbl_employee.id')
                                    ->leftJoin('users','employee.tbl_employee.LoginName','=','users.username')
                                    ->select('tbl_stackholders.*','users.email')
                                    ->where('req_id',$mailData['submission']->id)
                                    ->where('module_id',$this->getModuleId($modulename))
                                    ->get();
                    
                    foreach ($stackholders as $stacks){
                        $this->cc($stacks->email);
                    }
                    foreach ($Mailrecipient as $cc){
                        if($cc->company_list == null) {
                            $this->cc($cc->email);
                        } 
                    }
                }
            }
        // PROJECT MODULE

        // START MOM
            if($modulename == 'Mom') {
                if($final == 1) {
                    $stackholders = Stackholders::leftJoin('employee.tbl_employee','tbl_stackholders.employee_id','=','employee.tbl_employee.id')
                                    ->leftJoin('users','employee.tbl_employee.LoginName','=','users.username')
                                    ->select('tbl_stackholders.*','users.email','employee.tbl_employee.FullName')
                                    ->where('req_id',$mailData['submission']->id)
                                    ->where('module_id',$this->getModuleId($modulename))
                                    ->get();

                    $getTaskBound = DB::table('tbl_category')
                    ->select('employee.tbl_addressbook.email')
                    ->where('req_id',$mailData['submission']->id)
                    ->leftJoin('request_momTask','tbl_category.id','request_momTask.category_id')
                    ->leftJoin('request_momTaskBound','request_momTask.id','request_momTaskBound.task_id')
                    ->leftJoin('employee.tbl_employee','request_momTaskBound.employee_id','employee.tbl_employee.id')
                    ->leftJoin('employee.tbl_addressbook','employee.tbl_employee.LoginName','employee.tbl_addressbook.username')
                    ->get();

                    $latestUpdates = DB::table('request_momTaskUpdate')
                    ->select('request_momTaskUpdate.*')
                    ->whereIn('request_momTaskUpdate.updated_at', function ($query) {
                        $query->select(DB::raw('MAX(updated_at)'))
                            ->from('request_momTaskUpdate')
                            ->groupBy('request_momTaskUpdate.task_id');
                    });

                    $datadetailtask = DB::table('tbl_category')
                    ->select('tbl_category.category',
                    'momTaskDetail.id',
                    'momTaskDetail.description',
                    'momTaskDetail.section',
                    'momTaskDetail.status',
                    'momTaskDetail.deadline_date',
                    'momTaskDetail.agings',
                    'momTaskDetail.time_categorys',
                    'request_momTaskBound.content',
                    'ea.FullName',
                    'latest_updates.description as UpdateDescription',
                    'latest_updates.date as UpdateDate',
                    'eb.FullName as UpdateName'
                    )
                    ->where('tbl_category.req_id',$mailData['submission']->id)
                    ->whereNotIn('momTaskDetail.status',['Done'])
                    ->leftJoin('momTaskDetail','tbl_category.id','momTaskDetail.category_id')
                    ->leftJoin('request_momTaskBound','momTaskDetail.id','request_momTaskBound.task_id')
                    // ->leftJoin('request_momTaskUpdate','momTaskDetail.id','request_momTaskUpdate.task_id')
                    ->leftJoinSub($latestUpdates, 'latest_updates', function($join) {
                        $join->on('momTaskDetail.id', '=', 'latest_updates.task_id');
                    })
                    ->leftJoin('employee.tbl_employee as ea','request_momTaskBound.employee_id','ea.id')
                    ->leftJoin('employee.tbl_employee as eb','latest_updates.updated_by','eb.id')
                    ->get();

                    $this->assignment = $stackholders;
                    $this->detailmomtask = $datadetailtask;


                    // Initialize an array to collect emails
                    $allEmails = [];

                    // Add emails from $stackholders to the array
                    foreach ($stackholders as $stacks) {
                        $allEmails[] = $stacks->email;
                    }

                    // Add emails from $getTaskBound to the array
                    foreach ($getTaskBound as $taskBound) {
                        $allEmails[] = $taskBound->email;
                    }

                    // Remove duplicate emails
                    $lowercaseEmails = array_map('strtolower', $allEmails);
                    $uniqueEmails = array_unique($lowercaseEmails);

                    // Loop through the unique emails and apply the cc function
                    foreach ($uniqueEmails as $email) {
                        $this->cc($email);
                    }

                }
            }
        // END MOM

        // TICKET MODULE
            if($modulename == 'Ticket') {
                if($mailData['submission']->nameSystem !== null) {
                    $project = Project::findOrFail($mailData['submission']->nameSystem);
                    $this->projectName = $project->nameSystem;
                } else {
                    $this->projectName = 'Others';
                }
                
                if($mailData['email'] == 'kf_developer@d1.lcl') {  
                    if($project->id == 120 || $project->parentID == 120) {
                        foreach ($Mailrecipient as $cc){
                            if($cc->company_list == 'iop') {
                                $this->cc($cc->email);
                            } 
                        }
                    } else if($project->id == 158 || $project->parentID == 158) {
                        foreach ($Mailrecipient as $cc){
                            if($cc->company_list == 'webmap') {
                                $this->cc($cc->email);
                            } 
                        }
                    } else {
                        foreach ($Mailrecipient as $cc){
                            if($cc->company_list == null) {
                                $this->cc($cc->email);
                            } 
                        }
                    }
                }

                if($final == 1) {
                    $developerAssignment = Assignmentto::leftJoin('reference.tbl_developer','tbl_assignment.developer_id','=','reference.tbl_developer.id')
                                            ->leftJoin('users','reference.tbl_developer.user_id','=','users.id')
                                            ->select('reference.tbl_developer.*','users.email')
                                            ->where('req_id',$mailData['submission']->id)
                                            ->where('module_id',$this->getModuleId($modulename))
                                            ->get();
                    $this->developer=$developerAssignment;
                    foreach ($developerAssignment as $devemail){
                        $this->cc($devemail->email);
                    }
                }

            }
        // TICKET MODULE

        // Hrsc MODULE
            if($modulename == 'Hrsc') {
                $Categoryhrsc = Categoryhrsc::findOrFail($mailData['submission']->hrsc_category_id);
                $this->category = $Categoryhrsc->name;
            }

            // UAV Mission || HRSC MODULE
            if($modulename == 'UavMission' || $modulename == 'Hrsc') {

                if($final == 1) {
                    $assignmentdata = Assignmentto::leftJoin('employee.tbl_employee','tbl_assignment.employee_id','=','employee.tbl_employee.id')
                                            ->leftJoin('users','employee.tbl_employee.LoginName','=','users.username')
                                            ->select('employee.tbl_employee.*','users.email')
                                            ->where('req_id',$mailData['submission']->id)
                                            ->where('module_id',$this->getModuleId($modulename))
                                            ->get();
                    $this->assignment=$assignmentdata;
                    foreach ($assignmentdata as $email){
                        $this->cc($email->email);
                    }
                }

            }
        // Hrsc MODULE

        // Jdi MODULE
            if($modulename == 'Jdi') {
                $request = new Request();
                $jdiController = new JdiRequestController();
                if($final == 1) {
                    // save no registrasi
                    if($mailData['submission']->noRegistration == null || $mailData['submission']->noRegistration == '') {
                        Jdi::where('id',$mailData['submission']->id)
                        ->update(
                            [
                                "noRegistration" => $this->generateCodeJdiNoreg($mailData['submission']->bu)
                            ]
                        );
                    }
                    // end save no registrasi
                    $pdf = $jdiController->genPdfJdi($request,$mailData['submission']->id);
                    $this->attach($url."devportal/".$pdf); // add attachment to mail
                    foreach ($Mailrecipient as $cc) {
                        $this->cc($cc->email); // cc bcid
                    }
                }
            }
        // Jdi MODULE

        // ActiveDirectory MODULE
            if($modulename == 'ActiveDirectory') {
                $request = new Request();
                $adController = new ADRequestController();
                if($final == 1) {
                    $pdf = $adController->genPdfAD($request,$mailData['submission']->id);
                    $this->attach($url."devportal/".$pdf); // add attachment to mail
                    if(!empty($mailData['submission']->pic_empid)) {
                        $getempPIC = Employee::where('id',$mailData['submission']->pic_empid)->first();
                        $getPIC = User::where('username',$getempPIC->LoginName)->first();
                        $this->to($getPIC->email);
                    }
                    foreach ($Mailrecipient as $cc) {
                        $this->cc($cc->email); // cc
                    }
                }
            }
        // ActiveDirectory MODULE

        // Material Req MODULE
            if($modulename == 'MaterialReq') {
                $request = new Request();
                $ecatalogController = new MaterialRequestController();
                if($final == 1) {
                    if($mailData['action_id'] !== 5) {
                        $pdf = $ecatalogController->genPdfMaterialReq($request,$mailData['submission']->id);
                        $this->attach($url."devportal/".$pdf); // add attachment to mail
                    }
                    foreach ($Mailrecipient as $cc) {
                        $this->cc($cc->email); // cc
                    }
                }
            }
        // Material Req MODULE

        // MMF MODULE
            if($modulename == 'Mmf') {
                $checkCategory = Mmf::find($mailData['submission']->id);
                
                $request = new Request();
                if($checkCategory->category == 'MMF28') {
                    $mmfController = new M28RequestController();
                } else {
                    $mmfController = new M30RequestController();
                }

                if($final == 1) {
                    $pdf = $mmfController->genPdfMmfReq($request,$mailData['submission']->id);
                    $this->attach($url."devportal/".$pdf); // add attachment to mail
                }

            }
        // MMF MODULE

    }

    public function build()
    {  
        if($this->modulename == 'Mom') {
            $subject = 'Submission MoM - '.$this->mailData['submission']->subjectMeeting;
        } else {
            $subject = 'Submission - '.$this->code;
        }
        $viewblade = '';

        switch ($this->modulename) {
            case 'Project':
                $viewblade = 'emails.projectrequestmail';
                break;
            case 'Ticket':
                $viewblade = 'emails.ticketrequestmail';
                break;
            case 'UavMission':
                $viewblade = 'emails.uavmissionrequestmail';
                break;
            case 'Hrsc':
                $viewblade = 'emails.hrscrequestmail';
                break;
            case 'Mom':
                $viewblade = 'emails.momrequestmail';
                break;
            case 'Jdi':
                $viewblade = 'emails.jdirequestmail';
                break;
            case 'ActiveDirectory':
                $viewblade = 'emails.IT.adrequestmail';
                break;
            case 'MaterialReq':
                $viewblade = 'emails.Ecatalog.materialrequestmail';
                break;
            case 'Mmf':
                $viewblade = 'emails.MMF.mmfrequestmail';
                break;
            default:
                $viewblade = 'emails.defaultmail';
                break;
        }

        return $this->subject($subject)->view($viewblade);
    }
}