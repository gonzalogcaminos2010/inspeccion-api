<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Client::query()->withCount('equipment');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $clients = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $clients->through(fn ($client) => new ClientResource($client)),
            'Clients retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $client = Client::create($validated);

        return $this->success(new ClientResource($client), 'Client created successfully', 201);
    }

    public function show(Client $client)
    {
        $client->loadCount('equipment');

        return $this->success(new ClientResource($client));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $client->update($validated);

        return $this->success(new ClientResource($client), 'Client updated successfully');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return $this->success(null, 'Client deleted successfully');
    }
}
