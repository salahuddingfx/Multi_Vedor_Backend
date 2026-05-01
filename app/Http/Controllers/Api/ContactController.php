<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use App\Models\Site;
use Illuminate\Http\Request;

class ContactController extends BaseController
{
    public function store(Request $request, $site_slug)
    {
        $site = Site::where('slug', $site_slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $validated['site_id'] = $site->id;
        $contact = Contact::create($validated);

        return $this->sendResponse($contact, 'Your message has been sent successfully.');
    }
}
