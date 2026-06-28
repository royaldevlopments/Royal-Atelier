<?php

namespace RoyalPanel\Extensions\{identifier};

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SampleController extends Controller
{
    public function index()
    {
        return view('{engine}::{viewcontext}.{identifier}.view');
    }
}
