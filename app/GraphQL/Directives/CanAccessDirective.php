<?php

namespace App\GraphQL\Directives;

use App\Helpers\Authorization;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class CanAccessDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Check if the user (or guest) has the given permissions and/or roles
"""
directive @canAccess(
    """
    If authentication is required, defaults to false
    """
    requireAuth: Boolean
    """
    The permissions to check for
    """
    permissions: [String!]
    """
    The roles to check for
    """
    roles: [String!]
) on FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * Ensure the user is authorized to access this field.
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previousResolver = $fieldValue->getResolver();

        $fieldValue->setResolver(function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previousResolver) {
            $user = $context?->user();
            $requireAuth = $this->directiveArgValue('requireAuth', false);
            $permissions = $this->directiveArgValue('permissions', []);
            $roles = $this->directiveArgValue('roles', []);

            // Start validation of the user authorization
            $check = Authorization::validate($user);

            // If authentication is required, ensure the user is authenticated
            if($requireAuth) {
                $check->requiresAuthentication();
            }

            // Check if the user has the required permissions and roles
            $check
                ->can($permissions)
                ->hasRole($roles);

            return $previousResolver($root, $args, $context, $resolveInfo);
        });

        return $next($fieldValue);
    }
}
