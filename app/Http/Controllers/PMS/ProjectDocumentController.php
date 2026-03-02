<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Models\PMS\Project;
use App\Models\PMS\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProjectDocumentController extends Controller
{
    public function index(Project $project)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $folders = ProjectDocument::where('project_id', $project->id)
            ->select('folder')
            ->distinct()
            ->pluck('folder');

        $documents = ProjectDocument::where('project_id', $project->id)
            ->with('uploadedBy')
            ->orderBy('folder')
            ->orderBy('name')
            ->get()
            ->groupBy('folder');

        return view('pms.projects.documents.index', compact('project', 'documents', 'folders'),['pageConfigs'=> $pageConfigs]);
    }

    public function create(Project $project)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        $existingFolders = ProjectDocument::where('project_id', $project->id)
            ->select('folder')
            ->distinct()
            ->pluck('folder');

        return view('pms.projects.documents.create', compact('project', 'existingFolders'),['pageConfigs'=> $pageConfigs]);
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'folder' => 'nullable|string|max:255',
            'new_folder' => 'nullable|string|max:255',
            'documents' => 'required|array',
            'documents.*' => 'file|max:10240', // 10MB max
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string|max:1000',
        ]);

        $folder = $request->new_folder ?: $request->folder;

        foreach ($request->file('documents') as $index => $file) {
            $path = $file->store("public/projects/{$project->id}/documents/{$folder}");

            ProjectDocument::create([
                'project_id' => $project->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'folder' => $folder,
                'description' => $request->descriptions[$index] ?? null,
                'uploaded_by' => Auth::id(),
            ]);
        }

        return redirect()->route('pms.projects.documents.index', $project->id)
            ->with('success', 'Documents uploaded successfully.');
    }

    public function show(Project $project, ProjectDocument $document)
    {
       $pageConfigs = ['myLayout' => 'horizontal'];
        abort_if($document->project_id !== $project->id, 404);

        return view('pms.projects.documents.show', compact('project', 'document'),['pageConfigs'=> $pageConfigs]);
    }

    public function download(Project $project, ProjectDocument $document)
    {
        abort_if($document->project_id !== $project->id, 404);

        if (!Storage::exists($document->path)) {
            abort(404);
        }

        return Storage::download($document->path, $document->name);
    }

    public function destroy(Project $project, ProjectDocument $document)
    {
        abort_if($document->project_id !== $project->id, 404);

        Storage::delete($document->path);
        $document->delete();

        return redirect()->route('pms.projects.documents.index', $project->id)
            ->with('success', 'Document deleted successfully.');
    }
}
