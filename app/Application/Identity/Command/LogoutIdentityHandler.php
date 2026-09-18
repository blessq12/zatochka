<?php

namespace App\Application\Identity\Command;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class LogoutIdentityHandler
{
    public function handle(Request $request): void
    {
        $bearer = $request->bearerToken();

        if ($bearer === null) {
            return;
        }

        PersonalAccessToken::findToken($bearer)?->delete();
    }
}
