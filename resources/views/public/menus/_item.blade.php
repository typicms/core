<li @class([$name . '-nav-item', $name . '-nav-item-' . $menulink->id, $menulink->class])>
    <a
        @class([$name . '-nav-link', $menulink->class, 'menu-item' => $menulink->parent !== null])
        @if (url($menulink->href) === url()->current()) aria-current="page" @endif
        href="{{ $menulink->items->count() > 0 ? '#' : url($menulink->href) }}"
        @if ($menulink->target === '_blank') target="_blank" rel="noopener noreferrer" @endif
        @if ($menulink->items->count() > 0) role="button" data-bs-toggle="menu" data-bs-auto-close="outside" aria-expanded="false" @endif
    >
        @if ($menulink->image)
            <img @class([$name . '-nav-image']) aria-hidden="true" src="{{ $menulink->image->render(64, 64) }}" width="32" height="32" alt="" />
        @endif
        <span @class([$name . '-nav-label'])>{{ $menulink->title }}</span>
    </a>
    @if ($menulink->items->count() > 0)
        <ul @class([$name . '-nav-dropdown', 'menu'])>
            @foreach ($menulink->items as $menulink)
                @include('public::menus._item', ['menulink' => $menulink])
            @endforeach
        </ul>
    @endif
</li>
