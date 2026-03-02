<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Calendar extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('content.apps.app-calendar',['pageConfigs'=> $pageConfigs]);
  }
}