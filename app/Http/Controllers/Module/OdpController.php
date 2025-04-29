<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module\Odp;
use App\Models\Module\Datapelanggan;

class OdpController extends Controller
{
    private $model;
    public $datapelanggan;

    public function __construct()
    {
        $this->model = new Odp();
        $this->datapelanggan = new Datapelanggan();
    }

    public function index(Request $request)
    {
        try {
            
            $data = $this->model->all();

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();

            $existingData = $this->model->where('odpName', $requestData['odpName'])
            ->where('portNumber', $requestData['portNumber'])
            ->first();

            if ($existingData) {
                return response()->json(["status" => "error", "message" => "odpName dan portNumber must be unique"]);
            }

            $create = $this->model->create($requestData);

            if ($create) {
                $this->datapelanggan->odp_id = $create->id;
                $this->datapelanggan->save();
            }

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();

            $data = $this->model->findOrFail($id);
            $data->update($requestData);

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
