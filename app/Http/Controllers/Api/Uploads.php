<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Request as Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Str;

class Uploads extends ApiController
{
    public function storeFile (Request $request) {
        $request->validate(['file' => 'required']);
        if (!$request->hasFile('file')) abort(500, 'NO FILE');

        $file = $request->file;

        $path = '/uploads/files/';
        $prefix = date('Ymd-His') .'-'.  (int) ((double) microtime()*100000);
        $fileName = $prefix.'-'.$file->getClientOriginalName();
        $file->move(public_path($path), $fileName);

        return response()->json($path.$fileName);
    }

    public function existFile ()
    {
        return response()->json('OK');
    }

    public function destroyFile (Request $request) {
        $request->validate(['path' => 'required']);
        $file = Str::of($request->path)->start("/uploads/files");
        $path = public_path($file);

        if (file_exists($path)) {
            unlink($path);
            return response()->json('OK');
        }

        return response()->json(['message' => 'File undefined!']);
    }
}
