<div @class(['section-default', $alignment ?? 'left', $color ?? '', $class ?? '', 'section-' . $section->id]) id="{{ $section->position }}-{{ $section->slug }}">
    <div class="section-default-container">
        <x-core::edit-button :url="$section->editUrl()" />
        <div class="section-default-row">
            @if (!$section->image)
                <div class="section-default-content reveal">
                    <h2 @class(['section-default-title', 'visually-hidden' => $section->hide_title])>{{ $section->title }}</h2>
                    <div class="section-default-text rich-content">{!! $section->formattedBody() !!}</div>
                </div>
            @else
                <div class="section-default-left">
                    @if (!$section->hide_title)
                        <h2 class="section-default-title">{{ $section->title }}</h2>
                    @endif
                    <div class="section-default-text rich-content">{!! $section->formattedBody() !!}</div>
                </div>
                <div class="section-default-right">
                    <figure class="section-default-figure">
                        <img
                            class="section-default-figure-image"
                            src="{{ $section->image->render(990) }}"
                            width="{{ $section->image->width }}"
                            height="{{ $section->image->height }}"
                            alt="{{ $section->image->alt_attribute }}"
                        />
                        @if ($section->image->description)
                            <figcaption class="section-default-figure-caption">{{ $section->image->description }}</figcaption>
                        @endif
                    </figure>
                </div>
            @endif
        </div>
        @include('public::files._document-list', ['model' => $section])
        @include('public::files._image-list', ['model' => $section])
    </div>
</div>
