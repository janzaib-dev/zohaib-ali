<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountsHeadController extends Controller
{
    public function index (){
        return view('admin_panel.chart_of_accounts',);
    }
    // public function narration (){
    //     return view('admin_panel.accounts.narration',);
    // }
}
