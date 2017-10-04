<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => config('tweetreach.application_title'),
        ];

        return view('welcome', $data);
    }
}
