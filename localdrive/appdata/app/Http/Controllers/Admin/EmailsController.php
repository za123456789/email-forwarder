<?php

namespace App\Http\Controllers\Admin;

use App\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmailsRequest;
use App\Http\Requests\Admin\UpdateEmailsRequest;
use Illuminate\Support\Facades\Response;
use Validator;


class EmailsController extends Controller
{
    /**
     * Display a listing of Email.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $email = Email::all();

        // return $email;
           
        if (! Gate::allows('email_access')) {
            return abort(401);
        }


        if (request('show_deleted') == 1) {
            if (! Gate::allows('email_delete')) {
                return abort(401);
            }
            $emails = Email::onlyTrashed()->get();
        } else {
            $emails = Email::all();
        }

         return view('admin.emails.index', compact('emails'));
    }

    public function insert(Request $request){
        
        // print_r($request->input('id'));
        $validator = Validator::make($request->all(), [ 
            'from' => 'required|email', 
            'to' => 'required|email', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        $email = new Email;
        $email->id = $request->input('id');
        $email->from = $request->input('from');
        $email->to = $request->input('to');
        $result =  $email->save();

	   $email_forwarder = $request->input('from'). "   ". $request->input('to');

        if($result==1)
        {
	    //system('sudo chmod 777 /virtual');
            $current_forwarder = $email_forwarder . "\n";
            $current_forwarder .= file_get_contents('/virtual'); 
            file_put_contents('/virtual',  $current_forwarder);
            system('sudo docker exec emailserver postmap /etc/postfix/virtual');            
            system('sudo docker exec emailserver postfix reload');

            return response()->json(['success' => "forwarder added"], 200);
        }

    }

    public function delete(Request $request){
         
         $from_add = $request->from;
         $email = Email::where('from', $from_add)->first();
         if ($email == null ){
            return abort(401);
        }
        else {
         $this->destroy($email->id);
        }

         return "Forwarder Deleted";

    }

    public function updateemail(Request $request)
    {   
        $from_add = $request->from;
        $email = Email::where('from', $from_add)->first();
        if ($email == null ){
            return abort(401);
        }
        else {
        $email->update($request->all());
        }

        return "Forwarder Updated";

    }

    /**
     * Show the form for creating new Email.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('email_create')) {
            return abort(401);
        }
        return view('admin.emails.create');
    }

    /**
     * Store a newly created Email in storage.
     *
     * @param  \App\Http\Requests\StoreEmailsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmailsRequest $request)
    {
        if (! Gate::allows('email_create')) {
            return abort(401);
        }
        $email = Email::create($request->all());

        return redirect()->route('admin.emails.index');
    }


    /**
     * Show the form for editing Email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('email_edit')) {
            return abort(401);
        }
        $email = Email::findOrFail($id);

        return view('admin.emails.edit', compact('email'));
    }

    /**
     * Update Email in storage.
     *
     * @param  \App\Http\Requests\UpdateEmailsRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmailsRequest $request, $id)
    {
        if (! Gate::allows('email_edit')) {
            return abort(401);
        }

        $email = Email::findOrFail($id);
        $email->update($request->all());

        return redirect()->route('admin.emails.index');
    }


    /**
     * Display Email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! Gate::allows('email_view')) {
            return abort(401);
        }
        $email = Email::findOrFail($id);

        return view('admin.emails.show', compact('email'));
    }


    /**
     * Remove Email from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // if (! Gate::allows('email_delete')) {

        //     return abort(401);
        // }
        $email = Email::findOrFail($id);
        $email->delete();

        return redirect()->route('admin.emails.index');
    }

    /**
     * Delete all selected Email at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('email_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Email::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Email from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (! Gate::allows('email_delete')) {
            return abort(401);
        }
        $email = Email::onlyTrashed()->findOrFail($id);
        $email->restore();

        return redirect()->route('admin.emails.index');
    }

    /**
     * Permanently delete Email from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (! Gate::allows('email_delete')) {
            return abort(401);
        }
        $email = Email::onlyTrashed()->findOrFail($id);
        $email->forceDelete();

        return redirect()->route('admin.emails.index');
    }
}
