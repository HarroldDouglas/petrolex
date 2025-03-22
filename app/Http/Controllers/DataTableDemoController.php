<?php

namespace App\Http\Controllers;

class DataTableDemoController extends Controller
{
    public function staticDemo()
    {
        return view('demos.datatable-static');
    }
}
