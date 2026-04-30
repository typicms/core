<x-core::layouts.public
    :title="$model->tag . ' – ' . __('Tags') . ' – ' . websiteTitle()"
    :og-title="$model->tag"
    :body-class="'body-tags body-tag-' . $model->id . ' body-page body-page-' . $page->id"
    :page="$page"
    :model="$model"
>
    <article class="tag">
        <h1 class="tag-title">{{ $model->tag }}</h1>
    </article>
</x-core::layouts.public>
