<ul class="tag-list-list">
    @foreach ($items as $tag)
        @include('public::tags._list-item')
    @endforeach
</ul>
