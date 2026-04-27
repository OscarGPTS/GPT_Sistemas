<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;

class LocationService
{
    /**
     * Returns all locations as a flat list with depth and indented label,
     * suitable for select dropdowns.
     */
    public function flatTree(): Collection
    {
        $all = Location::with('children')->get();
        $byParent = $all->groupBy('parent_id');

        $result = collect();
        $walk = function ($parentId, int $depth) use (&$walk, $byParent, $result): void {
            foreach (($byParent[$parentId] ?? []) as $node) {
                $result->push([
                    'id' => $node->id,
                    'name' => $node->name,
                    'type' => $node->type,
                    'depth' => $depth,
                    'is_active' => (bool) $node->is_active,
                    'indented' => str_repeat('— ', $depth).$node->name,
                ]);
                $walk($node->id, $depth + 1);
            }
        };
        $walk(null, 0);
        return $result;
    }

    /**
     * Build a nested tree structure of all locations starting from roots.
     */
    public function nestedTree(): Collection
    {
        return Location::with('descendants')->whereNull('parent_id')
            ->orderBy('position')->orderBy('name')->get();
    }

    /**
     * Validate parent assignment to prevent circular references.
     */
    public function isValidParent(?Location $location, ?int $newParentId): bool
    {
        if (! $location || ! $newParentId) {
            return true;
        }
        if ($newParentId === $location->id) {
            return false;
        }
        $forbidden = $location->descendantIds();
        return ! in_array($newParentId, $forbidden, true);
    }
}
