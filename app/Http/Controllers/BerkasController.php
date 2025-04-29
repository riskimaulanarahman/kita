<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BerkasController extends Controller
{

    public function index()
    {
        //
    }

    public function store(Request $request, $module)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request,$modname)
    {
        try {
            $module = $modname;
            $file = $request->file('myFile');
            $nama_file = $module."_".time()."_".$file->getClientOriginalName();
            $tujuan_upload = 'upload';
            $file->move($tujuan_upload,$nama_file);
        
            return $nama_file;
        } catch (\Exception $e){

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
 
    }

    public function destroy($id)
    {
        //
    }
}
