<?php

namespace TypiCMS\Modules\Core\Services;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploader
{
    public function handle(UploadedFile $file, string $path = 'files', ?string $disk = null, ?string $filenameWithoutExtension = null): array
    {
        if ($disk === null) {
            $disk = config('filesystems.default');
        }
        $extension = mb_strtolower($file->getClientOriginalExtension());
        if (in_array($extension, ['jpg', 'jpeg', 'jpe'])) {
            $extension = 'jpg';
            $this->correctImageOrientation($file);
        }
        $filesize = $file->getSize();
        $mimetype = $file->getClientMimeType();

        $filenameWithoutExtension = Str::slug($filenameWithoutExtension ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if ($filenameWithoutExtension === '') {
            $filenameWithoutExtension = Str::slug(Str::random(16));
        }

        $filename = "{$filenameWithoutExtension}.{$extension}";

        if ($extension === 'svg') {
            $this->sanitizeSvg($file);
        }

        [$width, $height] = getimagesize($file);

        $filecounter = 1;
        while (Storage::disk($disk)->exists($path . '/' . $filename)) {
            $filename = $filenameWithoutExtension . '_' . $filecounter++ . '.' . $extension;
        }
        $path = $file->storeAs($path, $filename, $disk);
        $type = Arr::get(config('file.types'), $extension, 'd');

        return compact('filesize', 'mimetype', 'extension', 'filename', 'width', 'height', 'path', 'type');
    }

    private function sanitizeSvg(UploadedFile $file): void
    {
        $sanitizer = new Sanitizer();
        $sanitizedContent = $sanitizer->sanitize($file->getContent());

        file_put_contents(
            $file->getPathname(),
            $sanitizedContent ?: ''
        );
    }

    private function correctImageOrientation(UploadedFile $file): void
    {
        if (!function_exists('exif_read_data')) {
            return;
        }
        $exif = @exif_read_data($file);
        if (empty($exif) || !isset($exif['Orientation'])) {
            return;
        }
        $orientation = $exif['Orientation'];
        if ($orientation !== 1) {
            $img = imagecreatefromjpeg($file);
            $deg = 0;

            switch ($orientation) {
                case 3:
                    $deg = 180;

                    break;

                case 6:
                    $deg = 270;

                    break;

                case 8:
                    $deg = 90;

                    break;
            }
            if ($deg) {
                $img = imagerotate($img, $deg, 0);
            }
            imagejpeg($img, $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename(), 100);
        }
    }
}
