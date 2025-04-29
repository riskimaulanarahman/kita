<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Submission\Pembayarandetail;
use App\Models\Submission\Produk;
use App\Models\Submission\Sampeldetail;
use App\Models\Submission\produkdetail;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use App;

class PembayaranRequestDetailController extends Controller
{

    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Pembayarandetail();
        $this->modulename = 'Pembayaran';
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

            $requestData = $request->all();
            $requestData['req_id'] = $request->req_id;

            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
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

            $data = $this->model->findOrFail($id);

            $sampel = Sampeldetail::where('req_id',$data->req_id)->where('status_sampel','Approved')->count();
            // dd($sampel);
            $requestData['total_product'] = $sampel;

            if($request->pph_type == 'PPH23') {
                // $requestData['amount'] = ($sampel*153061)-2%;
                $requestData['amount'] = ($sampel * 153061) - (($sampel * 153061) * 0.02);
            } else {
                $requestData['amount'] = ($sampel * 150000);
            }

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

    public function generateInvoice($id)
    {

        try {
            $billed = Produk::select('users.company_name')->where('request_produk.id',$id)->leftJoin('users','request_produk.user_id','users.id')->first();
            $produk = DB::table('invoiceproduk')->where('req_id',$id)->get();

            $invoiceData = [
                    'billed' => $billed,
                    'produk' => $produk,
                ];

            // Generate PDF
            $pdf = Pdf::loadView('invoices-detail', $invoiceData);
            return $pdf->download('invoice-' . $id . '.pdf');

        } catch (\Exception $e) {
            dd('Error: ' . $e->getMessage());
        }

    }
}