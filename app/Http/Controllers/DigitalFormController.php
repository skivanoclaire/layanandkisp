<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DigitalFormController extends Controller
{
    public function index()
    {
        return view('user.digital');
    }
}
