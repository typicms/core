<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Components;

use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;
use Illuminate\View\View;

class AuthenticatePasskey extends Component
{
    public function __construct(
        public ?string $redirect = null,
    ) {}

    public function render(): View
    {
        if ($this->redirect !== null && $this->redirect !== '') {
            Session::put('passkeys.redirect', $this->redirect);
        }

        return view('public::components.authenticate');
    }
}
