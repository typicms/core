@if ($menu = new TypiCMS\Modules\Core\Models\Menu()->getMenu($name))
    @if (($menulinks = $menu->menulinks->load('image')) and $menulinks->count() > 0)
        <ul @class([$name . '-nav-list', $menu->class]) role="menubar">
            @foreach ($menulinks as $menulink)
                @include('public::menus._item')
            @endforeach
        </ul>
    @endif
@endif
