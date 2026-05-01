@if (($subpages = $page->getSubPages()) and $subpages->count() > 0)
    <ul class="subpages">
        @foreach ($subpages as $child)
            @include('public::pages._list-item', compact('child'))
        @endforeach
    </ul>
@endif
