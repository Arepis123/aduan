<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Download/view attachment for staff (authenticated)
     */
    public function staff(Request $request, TicketAttachment $attachment): StreamedResponse
    {
        // Staff can access any attachment
        return $this->serveFile($attachment);
    }

    /**
     * Serve the file as a streamed response
     */
    protected function serveFile(TicketAttachment $attachment): StreamedResponse
    {
        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(404, 'File not found.');
        }

        $headers = [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="' . $attachment->original_filename . '"',
        ];

        // For non-viewable files, force download
        $viewableTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($attachment->mime_type, $viewableTypes)) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $attachment->original_filename . '"';
        }

        return Storage::disk('public')->download($attachment->path, $attachment->original_filename, $headers);
    }
}
