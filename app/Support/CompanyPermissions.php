<?php

namespace App\Support;

/**
 * Phase B — pure-data home for the per-company permission matrix.
 *
 * No state: this class never holds a user, a company, or a database
 * handle. Callers (User::hasCompanyPermission, the dashboard matrix
 * editor) resolve the pivot themselves and use the constants +
 * helpers here to decide what to do with the JSON they found.
 *
 * Design notes:
 *   - GROUPS / ACTIONS are the *whole* vocabulary the matrix editor
 *     uses. Adding a new group is fine; renaming an existing one is a
 *     breaking change for every pivot row already storing permissions.
 *   - SECTION_MAP collapses dashboard sidebar keys onto permission
 *     groups so the same matrix governs both `requests` and
 *     `request-history` without forcing employees to manage two
 *     separate toggles.
 *   - EMPLOYEE_DEFAULTS is intentionally narrower than full access:
 *     view-only on data screens, plus `create` on the screens where a
 *     normal member is expected to file new work (service requests +
 *     support tickets). Admins always get full access regardless of
 *     what's stored.
 */
final class CompanyPermissions
{
    /** Permission groups exposed to the admin in the matrix UI. */
    public const GROUPS = [
        'dashboard',
        'services',
        'requests',
        'documents',
        'invoices',
        'tickets',
        'users',
        'financial',
        'profile',
        'packages',
        'support',
        'subscription',
    ];

    /** Per-group action verbs. */
    public const ACTIONS = ['view', 'create', 'update', 'delete'];

    /**
     * Sidebar section → permission group. Anything not listed here
     * defaults to the `dashboard` group; the canAccessCompanySection
     * helper explicitly rejects unknown keys instead of granting them.
     */
    public const SECTION_MAP = [
        'home'             => 'dashboard',
        'profile'          => 'profile',
        'users'            => 'users',
        'files'            => 'documents',
        'compliance'       => 'documents',
        'requests'         => 'requests',
        'request-history'  => 'requests',
        'subscription'     => 'subscription',
        'financial'        => 'financial',
        'invoices'         => 'invoices',
        'tickets'          => 'tickets',
        // `ai-review` deliberately omitted — it's a backoffice-only
        // section gated separately by `hasBackofficeAccess()`.
    ];

    /**
     * Sensible defaults for an employee whose pivot has no stored
     * permissions yet.
     */
    public const EMPLOYEE_DEFAULTS = [
        'dashboard'  => ['view'],
        'services'   => ['view'],
        'requests'   => ['view', 'create'],
        'documents'  => ['view'],
        'tickets'    => ['view', 'create'],
        'invoices'   => ['view'],
    ];

    public static function sectionGroup(string $section): string
    {
        return self::SECTION_MAP[$section] ?? 'dashboard';
    }

    /**
     * Full matrix — every group × every action. Used as the implicit
     * baseline for admins / owners.
     */
    public static function fullMatrix(): array
    {
        $matrix = [];
        foreach (self::GROUPS as $group) {
            $matrix[$group] = self::ACTIONS;
        }
        return $matrix;
    }

    /**
     * Normalise whatever was found in the JSON column into a clean
     * matrix keyed by group with array-of-actions values. Invalid /
     * unknown groups & actions are silently dropped.
     */
    public static function normalize(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        if (! is_array($raw)) {
            return [];
        }

        $clean = [];
        foreach (self::GROUPS as $group) {
            $actions = $raw[$group] ?? null;
            if (! is_array($actions)) {
                continue;
            }
            $allowed = array_values(array_intersect(self::ACTIONS, array_map('strval', $actions)));
            if (! empty($allowed)) {
                $clean[$group] = $allowed;
            }
        }
        return $clean;
    }

    /**
     * Resolve the *effective* matrix for a pivot row:
     *   - admin / owner → full access (stored JSON, if any,
     *     intentionally ignored so a misclick can never lock the
     *     company out of itself).
     *   - employee with stored JSON → the normalised JSON.
     *   - employee with no JSON → EMPLOYEE_DEFAULTS.
     *   - no pivot row at all → empty matrix.
     */
    public static function effective(?string $role, mixed $rawPermissions): array
    {
        $role = strtolower((string) $role);

        if (in_array($role, ['admin', 'owner'], true)) {
            return self::fullMatrix();
        }

        $stored = self::normalize($rawPermissions);
        if (! empty($stored)) {
            return $stored;
        }

        return self::EMPLOYEE_DEFAULTS;
    }

    public static function grants(array $matrix, string $group, string $action = 'view'): bool
    {
        $actions = $matrix[$group] ?? null;
        if (! is_array($actions)) {
            return false;
        }
        return in_array($action, $actions, true);
    }
}
