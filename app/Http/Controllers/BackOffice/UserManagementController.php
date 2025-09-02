<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Accounts\Role;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Delete;

class UserManagementController extends Controller
{
    #[Get('/back-office/users', name: 'back-office.users.index')]
    public function index(Request $request)
    {
        // Client ID is mandatory - get from request or middleware
        $clientFromMiddleware = $request->attributes->get('client');
        $requestClientId = $request->get('client_id');
        $clientId = $requestClientId ?: ($clientFromMiddleware->id ?? null);
        
        // If no client_id available, abort
        if (!$clientId) {
            abort(400, 'Client ID is required for user management.');
        }

        // Get the specific client
        $currentClient = Client::findOrFail($clientId);
        
        $users = User::with('roles')
            ->where('client_id', $clientId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('BackOffice/UserManagement/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'client_id']),
            'currentClient' => $currentClient,
        ]);
    }

    #[Get('/back-office/users/create', name: 'back-office.users.create')]
    public function create(Request $request)
    {
        // Client ID is mandatory
        $clientFromMiddleware = $request->attributes->get('client');
        $requestClientId = $request->get('client_id');
        $clientId = $requestClientId ?: ($clientFromMiddleware->id ?? null);
        
        // If no client_id available, abort
        if (!$clientId) {
            abort(400, 'Client ID is required for creating users.');
        }
        
        // Get the specific client
        $currentClient = Client::findOrFail($clientId);
        
        // Get available roles (exclude root role for regular user management)
        $roles = Role::where('slug', '!=', Role::ROOT)
            ->get();

        return Inertia::render('BackOffice/UserManagement/Create', [
            'roles' => $roles,
            'currentClient' => $currentClient,
        ]);
    }

    #[Post('/back-office/users', name: 'back-office.users.store')]
    public function store(Request $request)
    {
        // Client ID is mandatory
        $clientFromMiddleware = $request->attributes->get('client');
        $requestClientId = $request->get('client_id');
        $clientId = $requestClientId ?: ($clientFromMiddleware->id ?? null);
        
        // If no client_id available, abort
        if (!$clientId) {
            abort(400, 'Client ID is required for creating users.');
        }
        
        // Verify client exists
        Client::findOrFail($clientId);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_ids' => ['array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'client_id' => $clientId,
        ]);

        if (!empty($validated['role_ids'])) {
            $user->roles()->attach($validated['role_ids']);
        }

        return redirect()->route('back-office.users.index', ['client_id' => $clientId])
            ->with('success', 'User created successfully.');
    }

    #[Get('/back-office/users/{user}/edit', name: 'back-office.users.edit')]
    public function edit(Request $request, User $user)
    {
        // Client ID is mandatory
        $clientFromMiddleware = $request->attributes->get('client');
        $requestClientId = $request->get('client_id');
        $clientId = $requestClientId ?: ($clientFromMiddleware->id ?? null);
        
        // If no client_id available, abort
        if (!$clientId) {
            abort(400, 'Client ID is required for editing users.');
        }
        
        // Verify user belongs to this client
        if ($user->client_id !== (int)$clientId) {
            abort(403, 'Unauthorized access to this user.');
        }

        // Get the specific client
        $currentClient = Client::findOrFail($clientId);
        
        // Get available roles (exclude root role for regular user management)
        $roles = Role::where('slug', '!=', Role::ROOT)
            ->get();

        // Load user with their roles without client scope
        $user->load(['roles' => function ($query) {
            $query->withoutGlobalScopes();
        }]);

        return Inertia::render('BackOffice/UserManagement/Edit', [
            'user' => $user,
            'roles' => $roles,
            'currentClient' => $currentClient,
            'userRoleIds' => $user->roles->pluck('id')->toArray(), // Explicitly pass role IDs
        ]);
    }

    #[Put('/back-office/users/{user}', name: 'back-office.users.update')]
    public function update(Request $request, User $user)
    {
        // Client ID is mandatory
        $clientFromMiddleware = $request->attributes->get('client');
        $requestClientId = $request->get('client_id');
        $clientId = $requestClientId ?: ($clientFromMiddleware->id ?? null);
        
        // If no client_id available, abort
        if (!$clientId) {
            abort(400, 'Client ID is required for updating users.');
        }
        
        // Verify user belongs to this client
        if ($user->client_id !== (int)$clientId) {
            abort(403, 'Unauthorized access to this user.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_ids' => ['array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->roles()->sync($validated['role_ids'] ?? []);

        return redirect()->route('back-office.users.index', ['client_id' => $clientId])
            ->with('success', 'User updated successfully.');
    }

    #[Delete('/back-office/users/{user}', name: 'back-office.users.destroy')]
    public function destroy(Request $request, User $user)
    {
        // Client ID is mandatory
        $clientFromMiddleware = $request->attributes->get('client');
        $requestClientId = $request->get('client_id');
        $clientId = $requestClientId ?: ($clientFromMiddleware->id ?? null);
        
        // If no client_id available, abort
        if (!$clientId) {
            abort(400, 'Client ID is required for deleting users.');
        }
        
        // Verify user belongs to this client
        if ($user->client_id !== (int)$clientId) {
            abort(403, 'Unauthorized access to this user.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('back-office.users.index', ['client_id' => $clientId])
            ->with('success', 'User deleted successfully.');
    }
}