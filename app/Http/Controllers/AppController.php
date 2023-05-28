<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;

class AppController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        return view('app');
    }

    public function frontendConfig()
    {
        return response()->json([
            'APP_DEBUG' => config('app.debug'),
            'APP_ENV' => config('app.env'),
            'APP_URL' => config('app.url'),
            "BROADCAST_URL" => config('shoutzor.broadcast_url'),
            "PUSHER_APP_KEY" => config('broadcasting.connections.pusher.key'),
            "PUSHER_HOST" => config('broadcasting.connections.pusher.options.host'),
            "PUSHER_PORT" => config('broadcasting.connections.pusher.options.port'),
            "PUSHER_SCHEME" => config('broadcasting.connections.pusher.options.scheme')
        ]);
    }
}
