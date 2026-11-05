<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Bkwld\Croppa\Facades\Croppa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;
use TypiCMS\Modules\Core\Http\Requests\FileFormRequest;
use TypiCMS\Modules\Core\Models\File;
use TypiCMS\Modules\Core\Services\FileUploader;

final class FilesApiController extends BaseApiController
{
    /** @return array<string, mixed> */
    public function index(Request $request): array
    {
        if ($request->has('search') && $request->string('search')->isNotEmpty()) {
            $searchTerm = $request->string('search')->value();

            $query = File::query()
                ->with('folder')
                ->where(function ($query) use ($searchTerm): void {
                    $query
                        ->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('title', 'like', '%'.$searchTerm.'%')
                        ->orWhere('description', 'like', '%'.$searchTerm.'%');
                })
                ->orderByRaw('type="f" desc')
                ->orderBy('name');

            return [
                'models' => $query->get(),
                'path' => $this->getPath(),
            ];
        }

        $folderId = null;
        if ($request->has('folder_id')) {
            $folderId = $request->integer('folder_id');
        }

        return [
            'models' => File::query()
                ->with('children')
                ->where('folder_id', $folderId)
                ->orderByRaw('type="f" desc')
                ->orderBy('name')
                ->get(),
            'path' => $this->getPath($folderId),
        ];
    }

    public function show(File $file): File
    {
        return $file;
    }

    public function store(FileFormRequest $request): JsonResponse
    {
        $data = $request->validated();
        $model = new File;
        $attributes = $data;
        unset($attributes['name']);
        $model->fill($attributes);
        if ($request->hasFile('name')) {
            $file = new FileUploader()->handle($request->file('name'));
            $model->name = $file['filename'];
            unset($file['filename']);
            $model->fill($file);
        } else {
            $name = $data['name'] ?? null;
            $model->name = is_string($name) ? $name : null;
        }

        $model->save();

        $model->load('children');

        return response()->json(['model' => $model]);
    }

    protected function move(string $ids, Request $request): JsonResponse
    {
        $idsArray = explode(',', $ids);
        foreach ($idsArray as $id) {
            File::query()->where('id', $id)->update(['folder_id' => $request->folder_id]);
        }

        return response()->json(['number' => count($idsArray)]);
    }

    public function destroy(File $file): JsonResponse
    {
        if ($file->children->count() > 0) {
            return response()->json(['message' => __('A non-empty folder cannot be deleted.')], 403);
        }

        $file->delete();

        $originalPath = $file->getOriginal('path');
        if (is_string($originalPath)) {
            Croppa::delete("storage/{$originalPath}");
        }

        return response()->json(status: 204);
    }

    /** @return array<File|stdClass> */
    private function getPath(?int $folderId = null): array
    {
        $folder = File::query()->find($folderId);
        $path = [];
        while ($folder) {
            $path[] = $folder;
            $folder = $folder->folder;
        }

        $firstItem = new stdClass;
        $firstItem->name = __('Files');
        $firstItem->type = 'f';
        $firstItem->id = '';

        $path[] = $firstItem;

        return array_reverse($path);
    }
}
