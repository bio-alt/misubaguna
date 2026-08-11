<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ToolkitController extends Controller
{
    public function index()
    {
        return view('public.toolkit.index');
    }

    public function flangeStandards()
    {
        return view('public.toolkit.flange-standards');
    }
}
