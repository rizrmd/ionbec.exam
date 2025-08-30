<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Delete;
use Dentro\Yalr\Attributes\Patch;

class ClientManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('allowed:root');
    }

    #[Get('/back-office/clients', name: 'back-office.clients.index')]
    public function index(Request $request): Response
    {
        $query = Client::withCount(['users', 'exams', 'takers']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('primary_contact_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(15);

        return inertia('BackOffice/ClientManagement/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    #[Get('/back-office/clients/create', name: 'back-office.clients.create')]
    public function create(): Response
    {
        return inertia('BackOffice/ClientManagement/Create');
    }

    #[Post('/back-office/clients', name: 'back-office.clients.store')]
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:clients,slug',
            'logo' => 'nullable|image|max:2048',
            'domains' => 'required|array|min:1',
            'domains.*' => 'string|distinct',
            'primary_contact_email' => 'required|email|max:255',
            'primary_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('client-logos', 'public');
        }

        $validated['domains'] = array_filter($validated['domains']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        Client::create($validated);

        return redirect()->route('back-office.clients.index')
            ->with('success', 'Client created successfully.');
    }

    #[Get('/back-office/clients/{client}', name: 'back-office.clients.show')]
    public function show(Client $client): Response
    {
        $client->load(['users', 'exams', 'takers', 'groups']);
        
        $stats = [
            'total_users' => $client->users()->count(),
            'active_users' => $client->users()->whereNotNull('email_verified_at')->count(),
            'total_exams' => $client->exams()->count(),
            'published_exams' => $client->exams()->where('is_published', true)->count(),
            'total_takers' => $client->takers()->count(),
            'total_attempts' => $client->attempts()->count(),
            'completed_attempts' => $client->attempts()->whereNotNull('finished_at')->count(),
        ];

        return inertia('BackOffice/ClientManagement/Show', [
            'client' => $client,
            'stats' => $stats
        ]);
    }

    #[Get('/back-office/clients/{client}/edit', name: 'back-office.clients.edit')]
    public function edit(Client $client): Response
    {
        return inertia('BackOffice/ClientManagement/Edit', [
            'client' => $client
        ]);
    }

    #[Put('/back-office/clients/{client}', name: 'back-office.clients.update')]
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:clients,slug,' . $client->id,
            'logo' => 'nullable|image|max:2048',
            'domains' => 'required|array|min:1',
            'domains.*' => 'string|distinct',
            'primary_contact_email' => 'required|email|max:255',
            'primary_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($client->logo && Storage::disk('public')->exists($client->logo)) {
                Storage::disk('public')->delete($client->logo);
            }
            $validated['logo'] = $request->file('logo')->store('client-logos', 'public');
        }

        $validated['domains'] = array_filter($validated['domains']);

        $client->update($validated);

        return redirect()->route('back-office.clients.index')
            ->with('success', 'Client updated successfully.');
    }

    #[Delete('/back-office/clients/{client}', name: 'back-office.clients.destroy')]
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->users()->count() > 0) {
            return redirect()->route('back-office.clients.index')
                ->with('error', 'Cannot delete client with existing users.');
        }

        // Delete logo if exists
        if ($client->logo && Storage::disk('public')->exists($client->logo)) {
            Storage::disk('public')->delete($client->logo);
        }

        $client->delete();

        return redirect()->route('back-office.clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    #[Patch('/back-office/clients/{client}/toggle-status', name: 'back-office.clients.toggle-status')]
    public function toggleStatus(Client $client): RedirectResponse
    {
        $client->update(['is_active' => !$client->is_active]);
        
        return redirect()->route('back-office.clients.index')
            ->with('success', $client->is_active ? 'Client activated successfully.' : 'Client deactivated successfully.');
    }

    #[Get('/back-office/clients/{client}/statistics', name: 'back-office.clients.statistics')]
    public function statistics(Client $client): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => $client->users()->count(),
                'active' => $client->users()->whereNotNull('email_verified_at')->count(),
                'administrators' => $client->users()->whereHas('roles', function($query) {
                    $query->where('slug', 'administrator');
                })->count(),
            ],
            'exams' => [
                'total' => $client->exams()->count(),
                'published' => $client->exams()->where('is_published', true)->count(),
                'draft' => $client->exams()->where('is_published', false)->count(),
            ],
            'takers' => [
                'total' => $client->takers()->count(),
                'verified' => $client->takers()->whereNotNull('email_verified_at')->count(),
            ],
            'attempts' => [
                'total' => $client->attempts()->count(),
                'completed' => $client->attempts()->whereNotNull('finished_at')->count(),
                'in_progress' => $client->attempts()->whereNull('finished_at')->count(),
            ],
            'activity' => [
                'recent_logins' => $client->users()->where('last_login_at', '>=', now()->subDays(30))->count(),
                'recent_attempts' => $client->attempts()->where('created_at', '>=', now()->subDays(30))->count(),
            ]
        ];

        return response()->json($stats);
    }
}