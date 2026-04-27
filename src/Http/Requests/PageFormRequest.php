<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

class PageFormRequest extends AbstractFormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $rules = [
            'image_id' => ['nullable', 'integer'],
            'og_image_id' => ['nullable', 'integer'],
            'module' => ['nullable', 'max:255'],
            'template' => ['nullable', 'max:255'],
            'title.*' => ['nullable', 'max:255'],
            'uri.*' => ['nullable', 'max:1000'],
            'status.*' => ['nullable'],
            'body.*' => ['nullable', 'max:20000'],
            'meta_title.*' => ['nullable', 'max:255'],
            'meta_description.*' => ['nullable', 'max:255'],
            'meta_keywords.*' => ['nullable', 'max:255'],
            'position' => ['integer'],
            'parent_id' => ['nullable', 'integer'],
            'is_home' => ['boolean'],
            'private' => ['boolean'],
            'redirect' => ['boolean'],
        ];

        if ($this->is_home) {
            $rules['slug.*'] = ['nullable', 'alpha_dash', 'max:255'];
        } else {
            $rules['slug.*'] = ['nullable', 'alpha_dash', 'max:255', 'exclude_if:is_home,1', 'required_with:title.*'];
        }

        return $rules;
    }
}
