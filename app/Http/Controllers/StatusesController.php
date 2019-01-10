<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class StatusesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'content' => 'required|max:140'
        ]);

        Auth::user()->statuses()->create([
            'content' => $request->content
        ]);

        session()->flash('success', '您已成功发布一条微博!');

        return redirect()->back();
    }
}
