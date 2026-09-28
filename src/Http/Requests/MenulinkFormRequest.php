<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

class MenulinkFormRequest extends AbstractFormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'image_id' => ['nullable', 'integer', 'exists:files,id'],
            'menu_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'page_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'class' => ['nullable', 'max:255'],
            'target' => ['nullable', 'max:255'],
            'title.*' => ['nullable', 'max:255'],
            'status.*' => ['boolean'],
            'description.*' => ['nullable', 'max:1000'],
            'website.*' => ['nullable', 'url', 'max:255'],
        ];
    }
}
