<?php

namespace App\Traits;

trait UpdateRequestRules
{
    /**
     * Convert normal validation rules to "sometimes" rules for update requests.
     *
     * This method loops over the given rules array and adds "sometimes" if it's not already there.
     */
    protected function applyUpdateRules(array &$rules): void
    {
        foreach ($rules as $key => &$rule) {
            if (is_string($rule)) {
                if (!str_contains($rule, 'sometimes')) {
                    $rule = 'sometimes|' . $rule;
                }
            } elseif (is_array($rule)) {
                if (!in_array('sometimes', $rule)) {
                    array_unshift($rule, 'sometimes');
                }
            }
        }
    }
}
