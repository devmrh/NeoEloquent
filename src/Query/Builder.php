<?php

namespace Vinelab\NeoEloquent\Query;

use Closure;
use function compact;

class Builder extends \Illuminate\Database\Query\Builder
{
    public $relationships = [];

    /**
     * Adds an expression in the where clause to check for the existence of a relationship.
     *
     * The relationship may contain the type, as well as a direction. Examples include:
     *  - <MY_TYPE  For a relationship with type "MY_TYPE" pointing from the other to the target node.
     *  - MY_TYPE>  For a relationship with type "MY_TYPE" pointing from the target node to the current one.
     *  - MY_TYPE   For a relationship with type "MY_TYPE" in any direction between the current and target node.
     *  - <         For a relationship with any type pointing from target node to the current node.
     *  - >         For a relationship with any type pointing from the current node to the target node.
     *  -           For a relationship with any type and any direction.
     *
     * The target node will be anonymous if it is null.
     *
     * @param string $relationship The relationship to check.
     * @param string|null $target The name of the target node of the relationship.
     *
     * @return $this
     */
    public function whereRelationship(string $relationship = '', ?string $target = null): self
    {
        $this->wheres[] = [
            'type' => 'Relationship',
            'relationship' => $relationship,
            'target' => $target
        ];

        return $this;
    }

    /**
     * Joins two nodes together based on their relationship in the database.
     *
     * @param string|Closure $target
     * @param string $relationship
     *
     * @return static
     */
    public function joinRelationship(string $target, string $relationship = ''): self
    {
        $this->joins[] = $this->newJoinClause($this, 'cross', $target);

        $this->whereRelationship($relationship, $target);

        return $this;
    }

    /**
     * Adds a relationship if you run the update query.
     *
     * @param string $type The type of the relationship.
     * @param string $direction The direction of the relationship. Can be either '<' or '>' for incoming and outgoing relationships respectively.
     * @param \Illuminate\Database\Query\Builder|null $target The query to determine the find the target(s).
     *
     * @return $this
     */
    public function addRelationship(string $type, string $direction, ?\Illuminate\Database\Query\Builder $target): self
    {
        if ($target) {
            $join = $this->newJoinClause($this, 'cross', $target->from);
            $join->joins = $target->joins;
            $join->wheres = $target->wheres;
            $join->groups = $target->groups;
            $join->havings = $target->havings;
            $this->joins[] = $join;

            $this->relationships[] = [
                'type' => $type,
                'direction' => $direction,
                'target' => $target->from,
            ];
        } else {
            $this->relationships[] = compact('type', 'direction', 'target');
        }

        return $this;
    }
}