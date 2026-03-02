<?php

namespace App\Http\Controllers;

use App\Models\TapalAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function destroy(TapalAttachment $attachment)
    {
        $this->authorize('deleteAttachment', $attachment);
        // Get the tapal ID before deletion for redirect
        $tapalId = $attachment->tapal_id;

        // Delete file from storage
        Storage::delete($attachment->file_path);

        // Delete record from database
        $attachment->delete();

        return redirect()
            ->route('tapals.edit', $tapalId)
            ->with('success', 'Attachment deleted successfully');
    }
}