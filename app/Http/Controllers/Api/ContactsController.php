<?php

namespace Vanguard\Http\Controllers\Api;

use Contact;

class ContactsController extends ApiController
{
    public function __construct()
    {
        $this->middleware('permission:contacts');
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Contact::all());
    }
}
