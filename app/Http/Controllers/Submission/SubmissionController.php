<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Models\Submission\Project;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\User;
use App\Mail\SubmissionMail;
use DB;

class SubmissionController extends Controller
{
    public $module;

    public function __construct()
    {
        $this->module = new Module();
    }

    public function checkFields(Request $request, $reqid, $modulename)
    {
        $namespaceMap = [
        ];
        
        $baseNamespace = "App\Models\Submission";
        $locModel = $baseNamespace . "\\" . $modulename;

        if (!class_exists($locModel)) {
            if (array_key_exists($modulename, $namespaceMap)) {
                $locModel = $namespaceMap[$modulename] . "\\" . $modulename;
            }
        }
        $model = new $locModel;
        $columns = $model->getFillableColumns();
        $tableName = $model->getTableName();
        // dd($tableName);
        try {
            // Extract the fields to check from the request payload
            $fieldsToCheck = $request->input('fieldsToCheckGrid');

            if(!$fieldsToCheck) {
                return response()->json(['status' => 'show']);
            }

            // Fetch the data for the given reqid
            $record =DB::table($tableName)->where('req_id', $reqid)->first(); // Replace with your actual logic to get the record
            // dd($record);
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            // Check if specified fields are null or empty
            $missingFields = [];
            foreach ($fieldsToCheck as $fieldInfo) {
                // dd($fieldInfo);
                $fieldValue = $fieldInfo['field'];
                $fieldName = $fieldInfo['name'];
                if (is_null($record->$fieldValue) || $record->$fieldValue === '') {
                    $missingFields[] = $fieldName;
                }
            }

            $textMissing = "Please fill all required fields : ".implode(",",$missingFields);

            if (!empty($missingFields)) {
                return response()->json(['status' => 'error', 'message' => $textMissing]);
            }

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function submit(Request $request, $id, $modulename)
    {
        DB::beginTransaction();

            // $locModel = "App\Models\Submission\\".$modulename;

            $namespaceMap = [
            ];
            
            $baseNamespace = "App\Models\Submission";
            $locModel = $baseNamespace . "\\" . $modulename;

            if (!class_exists($locModel)) {
                if (array_key_exists($modulename, $namespaceMap)) {
                    $locModel = $namespaceMap[$modulename] . "\\" . $modulename;
                }
            }
            $model = new $locModel;
            $columns = $model->getFillableColumns();
            $tableName = $model->getTableName();

        try {

            $getSubmissionData = DB::table($tableName)->where('id', $id)->first();
            $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator

            $nullColumns = [];

            foreach ($columns as $column) {
                $data = DB::table($tableName)
                            ->where('id', $id)
                            ->whereNull($column)
                            ->first();
            
                if ($data) {
                    $nullColumns[] = $column;
                }
            }

            $module_id = $this->getModuleId($modulename);

            // attachment
            $attachementProduk = DB::table('tbl_attachment')
                                ->where('req_id',$id)
                                ->where('module_id',$module_id)
                                ->where('remarks','Produk')
                                ->get();
            $attachementBP = DB::table('tbl_attachment')
                                ->where('req_id',$id)
                                ->where('module_id',$module_id)
                                ->where('remarks','Bukti Pembayaran')
                                ->get();
            $attachementPO = DB::table('tbl_attachment')
                                ->where('req_id',$id)
                                ->where('module_id',$module_id)
                                ->where('remarks','PO')
                                ->get();
            // end attachment
            
            $checkAppr = DB::table('tbl_approverListReq')
                ->where('req_id',$id)
                ->where('module_id',$module_id)
                ->get();

            if (count($nullColumns) > 0) {
                $nullColumnsStr = implode(', ', $nullColumns);
                return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Column $nullColumnsStr is required"]);
            }

            if (count($attachementProduk) < 1 && $getSubmissionData->phase == 0) {
                return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Photo/File ::Produk:: not found. Please attach it."]);
            }
            if (count($attachementBP) < 1 && $getSubmissionData->phase == 4) {
                return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Photo/File ::Bukti Pembayaran:: not found. Please attach it."]);
            }
            

            if($modulename == 'Produk') {
                $detailsproduk = DB::table('request_produk_detail')
                ->where('req_id',$id)
                ->get();
                // dd(count($detailsproduk));

                if (count($detailsproduk) < 1) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Detail Produk not found. Please input the correct information."]);
                }

                // Cek requestStatus dan status_produk
                if ($getSubmissionData->requestStatus == 1 && $getSubmissionData->phase == 1 && $request->approvalAction == 3) {
                    $waitingCount = DB::table('request_produk_detail')
                    ->where('req_id',$id)
                    ->where('status_produk','Waiting')
                    ->count();

                    if ($waitingCount > 0) {
                        return response()->json([
                            "status" => "error",
                            "module" => $modulename,
                            "message" => "Error: Please update the product status from 'waiting' before proceeding."
                        ]);
                    }
                }

                $detailsampel = DB::table('request_sampel_detail')
                ->where('req_id',$id)
                ->get();

                if ($getSubmissionData->phase == 2 && count($detailsampel) < 1) {
                    return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Detail Sampel not found. Please input the correct information."]);
                }

                // Cek requestStatus dan status_produk
                if ($getSubmissionData->requestStatus == 1 && $getSubmissionData->phase == 2 && $request->approvalAction == 3) {
                    $waitingCount = DB::table('request_sampel_detail')
                    ->where('req_id',$id)
                    ->where('status_sampel','Waiting')
                    ->count();

                    if ($waitingCount > 0) {
                        return response()->json([
                            "status" => "error",
                            "module" => $modulename,
                            "message" => "Error: Please update the product sampel status from 'waiting' before proceeding."
                        ]);
                    }
                }
            }

            $final = 0;
            $mailData = [];

            // get and update approver list
            if($getSubmissionData->requestStatus == 0 || $getSubmissionData->requestStatus == 2) {
            
                 $this->createApprover($modulename, $id);

            }

            $approverlist = ApproverListReq::where('req_id',$id)
                ->when($request->action == 'submission', function ($query) use ($modulename) {
                    return $query->select('tbl_approverListReq.*')
                            ->where('module_id',$this->getModuleId($modulename))
                            ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                            ->orderBy('tbl_approver.sequence','asc');
                })
                ->when($request->action == 'approval', function ($query) use ($modulename) {
                    return $query->select('tbl_approverListReq.*','tbl_approver.isFinal')
                                 ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                                 ->where('module_id',$this->getModuleId($modulename))
                                 ->where('tbl_approver.user_id', $this->getAuth()->id)
                                 ->orderBy('tbl_approver.sequence','asc');
                })
                ->with('approvaluser')
                ->get();
            
            $rawgetapproverlist = ApproverListReq::where('req_id',$id)
                ->where('module_id',$module_id)
                ->where('approvalAction',1);

            $getapproverlist = $rawgetapproverlist->count();

            $dataapproversamecount = 0;
            $dataapproverlist = $rawgetapproverlist->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')->get();
            if ($dataapproverlist->count() == 2) {
                
                if ($dataapproverlist[0]->approvaluser->user_id == $dataapproverlist[1]->approvaluser->user_id) {
                    $dataapproversamecount = 1;
                }
            }
            $phase = $getSubmissionData->phase;
            
            if ($request->requestStatus == 0) {
                $phase = 0;
                $statusappr = 0;
                $requeststatus = $request->requestStatus;
                $this->approverAction($modulename, $id, 'Cancelled', 5 , null, null); // $moduleName, $req_id, $type, $appraction, $remarks, appruser
            } else if ($request->requestStatus == 1) {
                if($request->action == 'submission') {
                    
                    $statusappr = 1;
                    if($phase < 1) {
                        $phase += 1;
                    }
                    $requeststatus = $request->requestStatus;
                    $this->approverAction($modulename, $id, 'Submitted', 1, null, null); // $moduleName, $req_id, $type, $appraction, $remarks, appruser

                    foreach($approverlist as $getappr) {
                        $getUser = User::findOrFail($getappr->approvaluser->user_id); // get approver
                        $mailData = [
                            "id" => 1,
                            "action_id" => 1,
                            "submission" => $getSubmissionData,
                            "email" => $getUser->email, // kirim kepada approver
                            "fullname" => $getUser->fullname,
                            "creator" => $getCreator->fullname,
                            "message" => $this->mailMessage()['waitingapproval'],
                        ];
                    }

                } else if($request->action == 'approval') {
                    if($request->approvalAction == 4) {
                        $statusappr = 4;
                        $requeststatus = 4;
                        $phase = 0;
                    } else if($request->approvalAction == 2) {
                        $statusappr = 2;
                        $requeststatus = 2;
                        $phase = 0;
                    } else if($request->approvalAction == 3) {

                        foreach($approverlist as $data) {
                            
                            if($data->isFinal == 1) {
                                // if ($getapproverlist == 1 || $dataapproversamecount == 1 && $phase == 7) {
                                if ($phase == 7) {
                                    $final = 1;
                                    $statusappr = 3;
                                    $requeststatus = 3;
                                } else if($phase >=4 && $phase <7) {
                                    if (count($attachementPO) < 1 && $getSubmissionData->phase == 4) {
                                        return response()->json(["status" => "error", "module" => $modulename, "message" => "Error: Photo/File ::PO:: not found. Please tell attach it."]);
                                    }
                                    $statusappr = 1;
                                    $requeststatus = 1;
                                } else {
                                    $statusappr = 3;
                                    $requeststatus = 0;
                                }
                                $phase += 1;

                            } 
                            
                        }

                    }
                    // dd($request);
                    $getcurrentapprUser = $approverlist[0]->approver_id;
                    // dd($getcurrentapprUser);
                    $this->approverAction($modulename, $id, 'Approver', $request->approvalAction, $request->remarks, $getcurrentapprUser); // $moduleName, $req_id, $type, $appraction, $remarks, appuser
                }
            }


            if($final == 1) {
                if($modulename == 'Ticket' || $modulename == 'Hrsc') {
                    if (count($assignment) < 1) {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['assignmentnotfound']]);
                    }
                }
            }

            foreach($approverlist as $appr) {
                $appr->approvalAction = $statusappr;
                if($statusappr !== 3) {
                    $appr->approvalDate = null;
                }
                $appr->update();
            }
            // dd($phase);
            // Data untuk update
            $dataToUpdate = [
                "requestStatus" => $requeststatus,
                "phase" => $phase
            ];

            // Cek jika modulename adalah 'Jdi' dan tambahkan submitDate
            if ($modulename == 'Jdi' && $request->action == 'submission') {
                $dataToUpdate["submitDate"] = Carbon::now(); // Menggunakan Carbon untuk mendapatkan tanggal dan waktu saat ini
            }

            DB::table($tableName)
                ->where('id', $id)
                ->update($dataToUpdate);

            foreach($approverlist as $getappr) {
                if($request->approvalAction == 0 && $getappr->approvalAction == 0) { // cancel pengajuan
                    $mailData = [
                        "id" => 0,
                        "action_id" => 0, // action cancel
                        "submission" => $getSubmissionData,
                        "email" => $getCreator->email, // kirim kepada creator
                        "fullname" => $getCreator->fullname,
                        "message" => $this->mailMessage()['cancelled'],
                    ];
                    break;
                }
                if($request->approvalAction == 1 && $getappr->approvalAction == 1) { // submit pengajuan
                    $getUser = User::findOrFail($getappr->approvaluser->user_id); // get approver
                    $mailData = [
                        "id" => 1, // first approval
                        "action_id" => 1,
                        "submission" => $getSubmissionData,
                        "email" => $getUser->email, // kirim kepada approver
                        "fullname" => $getUser->fullname,
                        "creator" => $getCreator->fullname,
                        "message" => $this->mailMessage()['waitingapproval'],
                    ];
                    break;
                }
                if($request->approvalAction == 2 && $getappr->approvalAction == 2) { // rework pengajuan
                    $mailData = [
                        "id" => 2, // reworked approval
                        "action_id" => 0,
                        "submission" => $getSubmissionData,
                        "email" => $getCreator->email, // kirim kepada creator
                        "fullname" => $getCreator->fullname,
                        "message" => $this->mailMessage()['reworked'],
                        "remarks" => $request->remarks
                    ];
                    break;
                }
                if($request->approvalAction == 3 && $getappr->approvalAction == 3) { // approved pengajuan
                    
                    if($final == 1) {
                        $mailData = [
                            "id" => 30, // final approved
                            "action_id" => 0,
                            "submission" => $getSubmissionData,
                            "email" => $getCreator->email, // kirim kepada creator
                            "fullname" => $getCreator->fullname,
                            "message" => $this->mailMessage()['approved'],
                            "remarks" => $request->remarks
                        ];
                        break;
                    } 
                    // else {
                    //     $getNextApprover = ApproverListReq::leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                    //         ->where('tbl_approverListReq.req_id',$getappr->req_id)
                    //         ->where('tbl_approverListReq.approvalAction',1)
                    //         ->orderBy('tbl_approver.sequence','asc')
                    //         ->first(); // get next approver
                    //         dd($getNextApprover);
                    //     $getUser = User::findOrFail($getNextApprover->user_id); 
                    //     $mailData = [
                    //         "id" => 31, // next approved
                    //         "action_id" => 1,
                    //         "submission" => $getSubmissionData,
                    //         "email" => $getUser->email, // kirim kepada approver
                    //         "fullname" => $getUser->fullname,
                    //         "creator" => $getCreator->fullname,
                    //         "message" => $this->mailMessage()['waitingapproval'],
                    //     ];
                    //     break;

                    // }

                }
                if($request->approvalAction == 4 && $getappr->approvalAction == 4) { // rejected pengajuan
                    $mailData = [
                        "id" => 4, // reject
                        "action_id" => 0,
                        "submission" => $getSubmissionData,
                        "email" => $getCreator->email, // kirim kepada creator
                        "fullname" => $getCreator->fullname,
                        "message" => $this->mailMessage()['rejected'],
                        "remarks" => $request->remarks
                    ];
                    break;
                }
            }
            // dd($mailData);
            if(count($mailData) > 0) {
                // Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,$final));
            }

            DB::commit();
 
            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);
            
        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);

        }
    }

   
}