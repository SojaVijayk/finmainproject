<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\Tapal;
use App\Models\TapalAttachment;
use App\Models\Document;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * List Tapal attachments accessible by the logged-in user.
     */
    public function tapalList()
    {
        $userId = Auth::id();

        $tapalIds = Tapal::where('created_by', $userId)
            ->orWhereHas('movements', function ($query) use ($userId) {
                $query->where('to_user_id', $userId);
            })
            ->pluck('id');

        $attachments = TapalAttachment::with('tapal')->whereIn('tapal_id', $tapalIds)
        ->where('file_size', '>', 0)
            ->latest()
            ->get()
            ->filter(function ($attachment) {
                $path = 'app/' . $attachment->file_path;
                $fullPath = storage_path($path);
                return file_exists($fullPath) && filesize($fullPath) > 0;
            });

        return view('pms.attachments.partials.tapal', compact('attachments'));
    }

    /**
     * List Document attachments accessible by the logged-in user.
     */
    public function documentList()
    {
        $user = Auth::user();

        $documentIds = Document::where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('authorized_person_id', $user->id)
                    ->orWhereHas('code', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            })
            ->pluck('id');

        $attachments = DocumentAttachment::with('document')->whereIn('document_id', $documentIds)
            ->where('size', '>', 0) // optional optimization if you store file size
            ->latest()
            ->get()
            ->filter(function ($attachment) {
                $path = 'app/' . $attachment->file_path;
                $fullPath = storage_path($path);
                return file_exists($fullPath) && filesize($fullPath) > 0;
            });

        return view('pms.attachments.partials.documents', compact('attachments'));
    }
}
