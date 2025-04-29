<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Submission\Sampeldetail;

class SampelRequestDetailController extends Controller
{

    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Sampeldetail();
        $this->modulename = 'Sampel';
        $this->module = new Module();
    }

    public function index()
    {
        try {

            $data = $this->model->all();

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Cek apakah product_id sudah ada di database
            $existingProduct = $this->model->where('product_id', $request->product_id)->first();

            if ($existingProduct) {
                return response()->json(["status" => "error", "message" => "Product already exists."]);
            }

            $requestData = $request->all();
            $requestData['req_id'] = $request->req_id;
            $requestData['status_sampel'] = 'Waiting';

            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        //
    }

    public function getList($id,$modulename)
    {
        try {
            $module = $this->module->select('id','module')->where('module',$modulename)->first();
            if($module) {
                $data = $this->model->where('req_id',$id)->get();
                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);
            } else {
                return response()->json(["status" => "show", "message" => $this->getMessage()['errornotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            
            
            $requestData = $request->all();

            // Cek apakah product_id sudah ada di database
            $existingProduct = $this->model->where('product_id', $request->product_id)->first();

            if ($existingProduct) {
                return response()->json(["status" => "error", "message" => "Product already exists."]);
            }

            // $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            // if(isset($request->UnitPrice)) {
            //     $requestData['ExtendedPrice'] = $request->UnitPrice*$data->Qty;
            // }
            // if(isset($request->Qty)) {
            //     $requestData['ExtendedPrice'] = $data->UnitPrice*$request->Qty;
            // }

            $data->update($requestData);

            //start save history perubahan
            // $fields = [
            //     // '' => $request->,
            // ];
            
            // foreach ($fields as $key => $value) {
            //     if ($value) {
            //         $this->approverAction($this->modulename, $data->mmf30_id, $key, 1, $value, null);
            //     }
            // }
            //end save history perubahan

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}