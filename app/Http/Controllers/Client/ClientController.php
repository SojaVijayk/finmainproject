<?php

namespace App\Http\Controllers\Client;

use App\Models\Client;
use App\Models\ClientContactPerson;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        //  $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index','store']]);
        //  $this->middleware('permission:permission-create', ['only' => ['create','store']]);
        //  $this->middleware('permission:permission-edit', ['only' => ['edit','update']]);
        //  $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
        // if(Auth::user()->id!=1){
        //   $this->middleware('role:Client Management');
        // }

    }
    public function index()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
      $permissions = Permission::get();

        $clients = Client::with('projects')->with('contactPersons')->withCount('projects')->get();
        if(Auth::user()->user_role ==1){
          return view('content.clients.clients',compact('clients','permissions'));
        }else{
          return view('content.clients.clients',compact('clients','permissions'),['pageConfigs'=> $pageConfigs]);
        }



    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
          'client_name' => 'required|unique:clients,client_name',
           'client_code' => 'required|unique:clients,code',
          'email' => 'required|unique:clients,email',
          'address' => 'required',
          'phone' => 'required|unique:clients,phone',
          // 'projects' => 'required',
      ]);

      $client = Client::create(['client_name' => $request->input('client_name'),
       'code' => $request->input('client_code'),
      'email' => $request->input('email'),
      'address' => $request->input('address'),
      'phone' => $request->input('phone'),
      'created_by' => Auth::user()->id]);
      if ($client) {
        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //


    }
    public function editClient($id)
    {
      $client = Client::find($id);

          return response()->json(['client'=> $client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
          'client_name' => 'required',

      ]);

      $client = Client::find($id);
      $client->client_name = $request->input('client_name');
       $client->code = $request->input('client_code');
      $client->address = $request->input('address');
      $client->email = $request->input('email');
      $client->phone = $request->input('phone');
      $client->save();



      if ($client) {
        return response()->json('Updated');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    public function contactPersonStore(Request $request,$client)
    {
        //
        $this->validate($request, [
          'name' => 'required',
          'email' => 'required',
          'mobile' => 'required',
          // 'projects' => 'required',
      ]);

      $client = ClientContactPerson::create([
        'client_id' => $client,
        'name' => $request->input('name'),
      'email' => $request->input('email'),
      'address' => $request->input('address'),
      'mobile' => $request->input('mobile'),
      'designation' => $request->input('designation'),
      'created_by' => Auth::user()->id]);
      if ($client) {
        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }



    public function getAllContactPersons(Request $request)
    {

      $clients = Client::with('contactPersons')->whereIn('id',$request->clients)->get();

        return response()->json(['data'=> $clients]);



    }

    public function editContactPerson($id)
    {
      $client = ClientContactPerson::find($id);

          return response()->json(['data'=> $client]);
    }

    public function updateContactPerson(Request $request, $id)
    {
        //
        $this->validate($request, [
          'name' => 'required',
          'email' => 'required',
          'mobile' => 'required',
          // 'projects' => 'required',
      ]);

      $client_contact = ClientContactPerson::find($id);
      $client_contact->name = $request->input('name');
      $client_contact->address = $request->input('address');
      $client_contact->email = $request->input('email');
      $client_contact->mobile = $request->input('mobile');
      $client_contact->designation = $request->input('designation');
      $client_contact->save();



      if ($client_contact) {
        return response()->json('Updated');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    public function checkCode(Request $request)
{
    $exists = Client::where('code', $request->code)
        ->when($request->id, function ($q) use ($request) {
            $q->where('id', '!=', $request->id); // exclude same client when editing
        })
        ->exists();

    return response()->json(['exists' => $exists]);
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }
}