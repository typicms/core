<div class="page-row">
    @if (!$page->image)
        <div class="page-content">
            @if ($page->body)
                <div class="rich-content">{!! $page->formattedBody() !!}</div>
            @endif
        </div>
    @else
        <div class="page-left">
            @if ($page->body)
                <div class="rich-content">{!! $page->formattedBody() !!}</div>
            @endif
        </div>
        <div class="page-right">
            <figure class="page-figure">
                <img class="page-figure-image" src="{{ $page->image->render(2400) }}" width="{{ $page->image->width }}" height="{{ $page->image->height }}" alt="{{ $page->image->alt_attribute }}" />
                @if ($page->image->description)
                    <figcaption class="page-figure-caption">{{ $page->image->description }}</figcaption>
                @endif
            </figure>
        </div>
    @endif
</div>
