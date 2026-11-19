<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Support\Audit;
use App\Support\RecordTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Files on any registered record (App\Support\RecordTypes): list / download
 * need `<type>-list`, upload / delete need `<type>-edit` (the Administrator
 * passes). Limits: config/attachments.php.
 */
class AttachmentController extends Controller
{
    // GET /attachments/{type}/{id} → { data: [Attachment] }
    public function index(Request $request, string $type, int $id): JsonResponse
    {
        $record = $this->record($request, $type, $id, 'list');

        return response()->json(['data' => $record->attachments()->with('uploader:id,name')->get()->map(fn ($a) => $this->row($a))]);
    }

    // POST /attachments/{type}/{id} (multipart: file) → 201 { data: Attachment }
    public function store(Request $request, string $type, int $id): JsonResponse
    {
        $record = $this->record($request, $type, $id, 'edit');
        $request->validate([
            'file' => ['required', 'file', 'max:'.config('attachments.max_kb'), 'mimes:'.implode(',', config('attachments.mimes'))],
        ], ['file.max' => 'The file may not be larger than '.round(config('attachments.max_kb') / 1024).' MB.']);

        $file = $request->file('file');
        $disk = config('attachments.disk');
        $path = $file->storeAs("attachments/{$type}/{$id}", Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), $disk);

        $attachment = $record->attachments()->create([
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);
        Audit::record($record, 'updated', ['attachments' => "added {$attachment->original_name}"]);

        return response()->json(['data' => $this->row($attachment->load('uploader:id,name'))], 201);
    }

    // GET /attachments/{attachment}/download → the file
    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        $this->record($request, $this->typeOf($attachment), $attachment->attachable_id, 'list');

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    // DELETE /attachments/{attachment} → { message }
    public function destroy(Request $request, Attachment $attachment): JsonResponse
    {
        $record = $this->record($request, $this->typeOf($attachment), $attachment->attachable_id, 'edit');
        $attachment->delete();
        Audit::record($record, 'updated', ['attachments' => "removed {$attachment->original_name}"]);

        return response()->json(['message' => 'Attachment deleted.']);
    }

    // the record, after checking the type is registered and the user may `<type>-<action>`
    private function record(Request $request, string $type, int $id, string $action)
    {
        abort_unless(RecordTypes::exists($type), 404);
        abort_unless($request->user()->can("{$type}-{$action}"), 403, 'You do not have permission to do that.');

        return RecordTypes::find($type, $id);
    }

    private function typeOf(Attachment $attachment): string
    {
        foreach (RecordTypes::TYPES as $key => $def) {
            if ((new $def['model'])->getMorphClass() === $attachment->attachable_type) {
                return $key;
            }
        }
        abort(404);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Attachment $a): array
    {
        return [
            'id' => $a->id,
            'name' => $a->original_name,
            'mime_type' => $a->mime_type,
            'size' => $a->size,
            'uploaded_by' => $a->uploader?->name,
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }
}
