<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Composers;

use Illuminate\Contracts\View\View;

class MasterViewComposer
{
    public function compose(View $view): void
    {
        $navbar = false;
        $user = auth('web')->user();
        if ($user && $user->can('see navbar') && ! request()->boolean('preview') && ! request()->is('*/create-passkey')) {
            $navbar = true;
        }

        $view->with('navbar', $navbar);
    }
}
