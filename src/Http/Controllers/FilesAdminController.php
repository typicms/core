<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Bkwld\Croppa\Facades\Croppa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use TypiCMS\Modules\Core\Http\Requests\FileFormRequest;
use TypiCMS\Modules\Core\Models\File;
use TypiCMS\Modules\Core\Services\FileUploader;

final class FilesAdminController extends BaseAdminController
{
    public function index(): View
    {
        return view('admin::files.index');
    }

    public function edit(File $file): View
    {
        return view('admin::files.edit', ['model' => $file]);
    }

    public function update(File $file, FileFormRequest $request): RedirectResponse
    {
        $file->fill(Arr::except($request->validated(), 'file'));

        if ($request->hasFile('file')) {
            Croppa::delete('storage/'.$file->path);
            $newFile = new FileUploader()->handle($request->file('file'));
            $file->name = $newFile['filename'];
            $file->fill(Arr::except($newFile, 'filename'));
        }

        $file->save();

        return $this->redirect($request, $file);
    }

    public function crop(File $file, Request $request): JsonResponse
    {
        if (! $request->hasFile('cropped_image')) {
            return response()->json(['error' => 'No image provided'], 400);
        }

        if ($file->type !== 'i') {
            return response()->json(['error' => 'File is not an image'], 400);
        }

        $croppedImage = $request->file('cropped_image');

        Croppa::delete('storage/'.$file->path);

        $manager = new ImageManager(new Driver);
        $image = $manager->read($croppedImage->getRealPath());
        $width = $image->width();
        $height = $image->height();

        $pathInfo = pathinfo((string) $file->path);
        $path = $pathInfo['dirname'].'/'.$pathInfo['filename'].'-'.time().'.jpg';
        $encodedImage = $image->toJpeg(quality: 90);
        Storage::put($path, (string) $encodedImage);

        $nameInfo = pathinfo((string) $file->name);
        $file->name = $nameInfo['filename'].'.jpg';
        $file->path = $path;
        $file->extension = 'jpg';
        $file->mimetype = 'image/jpeg';
        $file->filesize = Storage::size($path);
        $file->width = $width;
        $file->height = $height;
        $file->save();

        return response()->json([
            'success' => true,
            'file' => $file,
        ]);
    }
}
