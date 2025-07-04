<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request){
        $domain = $request->getHost();

        switch($domain){
            case 'north-star.africa':
                return $this->serveReactApp();
            default:
                return view('welcome');
        }
    }

    private function serveReactApp()
    {
        $indexPath = public_path('landing/index.html');

        if(file_exists($indexPath)){
            return response()->file($indexPath);
        } else {
            abort(404, 'Landing page not found.');
        }
    }
}
