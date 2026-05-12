<?php

namespace App\Support;

class MenuAccess
{
    /**
     * @return list<string>
     */
    public static function allPermissionKeys(): array
    {
        $keys = [];
        foreach (config('menu_access.modules', []) as $module) {
            if (! empty($module['items'])) {
                foreach ($module['items'] as $item) {
                    $keys[] = $item['key'];
                }
            } else {
                $keys[] = $module['key'];
            }
        }

        return array_values(array_unique($keys));
    }

    public static function isValidKey(string $key): bool
    {
        return in_array($key, self::allPermissionKeys(), true);
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function filterValidKeys(array $keys): array
    {
        $allowed = array_flip(self::allPermissionKeys());

        return array_values(array_filter($keys, fn (string $k) => isset($allowed[$k])));
    }
}
