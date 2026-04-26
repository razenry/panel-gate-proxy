<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNodeRequest;
use App\Http\Requests\UpdateNodeRequest;
use App\Models\Node;
use App\Services\NodeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class NodeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected NodeService $nodeService
    ) {}

    /**
     * Display a listing of nodes.
     */
    public function index(): JsonResponse
    {
        return $this->success(Node::all());
    }

    /**
     * Store a newly created node.
     */
    public function store(StoreNodeRequest $request): JsonResponse
    {
        $node = Node::create($request->validated());

        return $this->success($node, 'Node created successfully.', 201);
    }

    /**
     * Display the specified node.
     */
    public function show(Node $node): JsonResponse
    {
        return $this->success($node);
    }

    /**
     * Update the specified node.
     */
    public function update(UpdateNodeRequest $request, Node $node): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['api_token'])) {
            unset($data['api_token']);
        }

        $node->update($data);

        return $this->success($node, 'Node updated successfully.');
    }

    /**
     * Remove the specified node.
     */
    public function destroy(Node $node): JsonResponse
    {
        $this->nodeService->destroyNode($node);

        return $this->success(null, 'Node deleted successfully.');
    }
}
