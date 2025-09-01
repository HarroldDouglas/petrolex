<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class GetSupportContactController extends Controller
{
    /**
     * Get the support team contact information.
     *
     * Route: GET /support/contact
     * Name: api.support.contact
     */
    public function __invoke(): ApiResponse
    {
        $contact = Config::get('support');

        return ApiResponse::success(
            data: [
                'phone_number' => $contact['phone_number'],
                'email' => $contact['email'],
            ],
            message: 'Informations de contact du support récupérées avec succès.'
        );
    }
}
