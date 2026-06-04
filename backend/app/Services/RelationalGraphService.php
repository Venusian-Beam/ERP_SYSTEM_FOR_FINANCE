<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;

final class RelationalGraphService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function tree(string $rootType, int $rootId): array
    {
        $tenantId = TenantContext::requireId();

        return DB::select(
            <<<'SQL'
            WITH RECURSIVE graph_tree AS (
                SELECT parent_type, parent_id, child_type, child_id, relation, 1 AS depth
                FROM relationship_edges
                WHERE tenant_id = ? AND parent_type = ? AND parent_id = ?
                UNION ALL
                SELECT edge.parent_type, edge.parent_id, edge.child_type, edge.child_id, edge.relation, graph_tree.depth + 1
                FROM relationship_edges edge
                JOIN graph_tree ON graph_tree.child_type = edge.parent_type AND graph_tree.child_id = edge.parent_id
                WHERE edge.tenant_id = ? AND graph_tree.depth < 8
            )
            SELECT * FROM graph_tree ORDER BY depth, parent_type, parent_id
            SQL,
            [$tenantId, $rootType, $rootId, $tenantId],
        );
    }
}
