@if ($model->documents->count() > 0)
    <ul class="document-list-list">
        @foreach ($model->documents as $document)
            @include('public::files._document-list-item')
        @endforeach
    </ul>
@endif
