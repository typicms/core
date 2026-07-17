<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use TypiCMS\Modules\Core\Services\FileUploader;

class FileFormRequest extends AbstractFormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $rules = [
            'folder_id' => ['nullable', 'integer'],
            'alt_attribute.*' => ['nullable', 'max:255'],
            'type' => ['nullable', 'string', 'max:1'],
            'title.*' => ['nullable', 'max:255'],
            'description.*' => ['nullable', 'max:255'],
            'name' => ['required', 'max:255'],
            'file' => ['nullable', ...$this->fileRules()],
        ];

        if ($this->hasFile('name')) {
            $rules['name'] = $this->fileRules();
        }

        return $rules;
    }

    /**
     * `mimes` validates the sniffed content type, `extensions` validates the
     * client-supplied filename. Both are required: without `extensions` an
     * upload may pass content validation as a harmless type while keeping an
     * attacker-chosen extension that is served back as executable markup.
     *
     * @return list<string>
     */
    private function fileRules(): array
    {
        return [
            'mimes:jpeg,gif,png,bmp,tiff,pdf,eps,svg,json,rtf,txt,md,doc,xls,ppt,docx,xlsx,ppsx,pptx,sldx,mp4,m4a,aac,aiff,mov,avi,mp3,wav,zip',
            'extensions:' . implode(',', FileUploader::SAFE_EXTENSIONS),
            'max:' . config('typicms.max_file_upload_size'),
        ];
    }
}
