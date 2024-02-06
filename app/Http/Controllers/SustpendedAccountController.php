<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SustpendedAccountController extends Controller
{
    public function viewSuspendedAccount() {
        return view('account.suspended');
    }

    public function viewNotAccess() {
        return view('account.notAccess');
    }
}
