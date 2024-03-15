<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Right;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RightController extends Controller
{
    // Добавление прав доступа
    public function add(Request $request, $file_id) {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        if ($file->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        $right = new Right();
        $right->file_id = $file->id;
        $right->user_id = $user->id;
        $right->save();

        $response = $this->usersAccessList($file);

        return response()->json($response, 200);
    }

    // Удаление прав доступа
    public function destroy(Request $request) {

    }

    // Получение списка пользователей с доступом
    public function userAccessList($file) {
        $rights = Right::where('file_id', $file->id)->with('user')->get();

        $author = $file->user;
        $response[] = [
            'fullname' => $author->full_name,
            'email' => $author->email,
            'type' => 'author',
            'code' => 200,
        ];

        foreach ($rights as $access) {
            $user = $access->user;
            $response[] = [
                'fullname' => $user->full_name,
                'email' => $user->email,
                'type' => 'co-author',
                'code' => 200,
            ];
        }

        return $response;
    }
}
