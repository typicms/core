<?php

namespace TypiCMS\Modules\Core\Services;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploader
{
    /**
     * Extensions that are safe to store and serve statically from a public disk.
     * An extension outside this list must never reach the filesystem: the web
     * server derives the Content-Type from it, so anything renderable as
     * markup (html, xhtml, xml…) would execute in the application’s origin.
     *
     * @var list<string>
     */
    public const SAFE_EXTENSIONS = [
        'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'svg', 'eps',
        'pdf', 'json', 'rtf', 'txt', 'md',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'ppsx', 'sldx',
        'mp3', 'wav', 'aac', 'aif', 'aiff', 'm4a',
        'mp4', 'mov', 'avi',
        'zip',
    ];

    public function handle(UploadedFile $file, string $path = 'files', ?string $disk = null, ?string $filenameWithoutExtension = null): array
    {
        if ($disk === null) {
            $disk = config('filesystems.default');
        }
        $extension = $this->normalizeExtension($file);
        $filesize = $file->getSize();
        $mimetype = $file->getClientMimeType();

        $filenameWithoutExtension = Str::slug($filenameWithoutExtension ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if ($filenameWithoutExtension === '') {
            $filenameWithoutExtension = Str::slug(Str::random(16));
        }

        $filename = "{$filenameWithoutExtension}.{$extension}";

        if ($this->isSvg($file, $extension)) {
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

    private function normalizeExtension(UploadedFile $file): string
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::SAFE_EXTENSIONS)) {
            $extension = mb_strtolower((string) $file->guessExtension());
        }
        if (!in_array($extension, self::SAFE_EXTENSIONS)) {
            $extension = 'bin';
        }
        if (in_array($extension, ['jpg', 'jpeg', 'jpe'])) {
            $extension = 'jpg';
            $this->correctImageOrientation($file);
        }

        return $extension;
    }

    private function isSvg(UploadedFile $file, string $extension): bool
    {
        return $extension === 'svg' || $file->guessExtension() === 'svg';
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
