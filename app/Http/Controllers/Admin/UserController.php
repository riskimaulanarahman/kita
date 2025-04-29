<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{

    public function index()
    {
        try {

            $data = User::all();

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();

            User::create($requestData);

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
            
            $data = User::findOrFail($id);
            $requestData = $request->all();

            if ($request->has('isAdmin')) {
                $requestData['isAdmin'] = $request->isAdmin == 'true' ? 1 : 0;
            }

            if ($request->has('passtxt')) {
                $requestData['password'] = bcrypt($request->passtxt);
                $requestData['passtxt'] = $request->passtxt;
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

            $data = User::findOrFail($id);

            if($data->isAdmin == 1) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['admindeleted']]);
            }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
