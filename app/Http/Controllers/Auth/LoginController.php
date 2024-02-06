<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
// use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    public $maxAttempts = 5;
    public $decayMinutes = 2;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    private $myTokenApp;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->myTokenApp = "legaltech";
    }

    protected function checkUserCompany()
    {
        $user = Auth::user();
        $company = $user->code_company;
        $host = request()->getHttpHost();
        $subdominio = strtok($host, '.');
        if ($subdominio == strtolower($company)) {
            return true;
        }
        return false;
    }


    private function checkOtherDeviceSession()
    {
        return auth()->user()->tokens->count() > 1;
    }


    // protected function authenticated(Request $request, $user)
    // {
    //     // Cerrar todas las demás sesiones del usuario
    //     // Auth::logoutOtherDevices($request->password);
    //     // if ($this->checkUserCompany()) {
    //     //     dd("usuario correcto");
    //         return redirect()->intended($this->redirectPath());
    //     // }else{
    //     //     // return redirect()->back()->with('checkUser', "Usuario invalido");
    //     //     dd("usuario invalido");
    //     // }
    // }

}
