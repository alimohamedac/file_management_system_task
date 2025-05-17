<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Repositories\DocumentRepository;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    protected $documentService;
    protected $documentRepository;

    public function __construct(DocumentService $documentService, DocumentRepository $documentRepository)
    {
        $this->documentService = $documentService;
        $this->documentRepository = $documentRepository;
        $this->middleware('auth');
    }

    public function index()
    {
        $documents = $this->documentRepository->all();

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        $workflows = Workflow::all();

        return view('documents.create', compact('workflows'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,txt|max:10240',
            'description' => 'nullable|string',
            'workflow_id' => 'required|exists:workflows,id'
        ]);

        $document = $this->documentService->createDocument($validated, $request->file('file'));

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document uploaded and workflow started successfully.');
    }

    public function show($id)
    {
        $document = $this->documentService->getDocumentHistory($id);

        return view('documents.show', compact('document'));
    }

    public function pendingApprovals()
    {
        $documents = $this->documentService->getPendingApprovals(auth()->id());
        return view('documents.pending-approvals', compact('documents'));
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:workflow_slots,id',
            'comments' => 'nullable|string'
        ]);

        $this->documentService->approveDocument($id, $validated['slot_id'], auth()->id(), $validated['comments']);

        return redirect()->back()->with('success', 'Document approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:workflow_slots,id',
            'comments' => 'required|string'
        ]);

        $this->documentService->rejectDocument($id, $validated['slot_id'], auth()->id(), $validated['comments']);

        return redirect()->back()->with('success', 'Document rejected successfully.');
    }

    /*public function download($id)
    {
        $document = $this->documentRepository->find($id);

        return Storage::download($document->doc_path);
    }*/
}
