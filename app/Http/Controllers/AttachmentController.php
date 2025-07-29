<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use App\Models\Attachments\Attachment;

class AttachmentController extends Controller
{
    public function stream(Attachment $attachment, Filesystem $filesystem)
    {
        $file = $filesystem->get(storage_path('app/').$attachment->path);
        $response = response()->make($file, 200);

        // using this will allow you to do some checks on it (if pdf/docx/doc/xls/xlsx)
        $response->header('Content-Type', $attachment->mime);

        return $response;
    }
}
