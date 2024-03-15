<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;
use App\Models\File;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // Добавление нового файла
    public function store(FileStoreRequest $request) {
        if ($request->hasFile('files')) {
            $uploadedFiles = [];

            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();

                $fileName = $this->generateUniqueFileName($originalName, $extension);

                $file->storeAs('uploads', $fileName);

                $uploadedFile = new File();
                $uploadedFile = auth()->user()->files()->create([
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'extension' => $extension,
                    'path' => $fileName,
                    'file_id' => Str::random(10),
                    'user_id' => auth()->id(),
                ]);
                $uploadedFile->save();

                $uploadedFiles[] = [
                    'success' => true,
                    'code' => 200,
                    'message' => 'Success',
                    'name' => $originalName,
                    'url' => url("files/{$uploadedFile->id}"),
                    'file_id' => $uploadedFile->file_id,
                ];
            }
            return response()->json($uploadedFiles);
        }
        return response()->json(['message' => 'No files to upload'], 400);
    }

    // Генерация уникального имени файла
    public function generateUniqueFileName($originalName, $extension) {
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $i = 1;

        while(Storage::exists("uploads/{$fileName}.{$extension}")) {
            $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . " ({$i})";
            $i++;
        }

        return $fileName . '.' . $extension;
    }

    // Редактирование файла
    public function edit(Request $request, $file_id) {
        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        $file->name = $request->input('name');
        $file->save();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Renamed'
        ]);
    }

    // Удаление файла
    public function destroy($file_id) {
        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        Storage::delete('uploads/' . $file->path);

        $file->delete();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'File deleted',
        ]);
    }

    // Скачивание файла
    public function download($file_id) {
        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if (!auth()->check() || auth()->user()->id !== $file->user_id) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        $filePath = storage_path('app/uploads/' . $file->path);

        if (!Storage::exists('uploads/' . $file->path)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->download($filePath, $file->name . '.' . $file->extension);
    }

    // Просмотр файлов пользователя
    public function owned() {

    }

    // Просмотр файлов, к которым пользователь имеет доступ
    public function allowed() {

    }
}
