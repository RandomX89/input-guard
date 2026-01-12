<?php
namespace RandomX98\InputGuard\Support;

final class Path {
    /** @return string[] */
    public static function segments(string $path): array {
        $path = trim($path);
        if ($path === '') return [];
        return array_values(array_filter(explode('.', $path), fn($s) => $s !== ''));
    }

    public static function get(array $input, string $path): mixed {
        $segments = self::segments($path);
        $cur = $input;

        foreach ($segments as $seg) {
            if (!is_array($cur) || !array_key_exists($seg, $cur)) {
                return null;
            }
            $cur = $cur[$seg];
        }
        return $cur;
    }

    public static function set(array $output, string $path, mixed $value): array {
        $segments = self::segments($path);
        if ($segments === []) return $output;

        $cur =& $output;
        foreach ($segments as $i => $seg) {
            $isLast = ($i === count($segments) - 1);

            if ($isLast) {
                $cur[$seg] = $value;
                return $output;
            }

            if (!isset($cur[$seg]) || !is_array($cur[$seg])) {
                $cur[$seg] = [];
            }

            $cur =& $cur[$seg];
        }

        return $output;
    }
}