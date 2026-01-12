<?php
namespace RandomX98\InputGuard\Support;

final class Path {
  /** @return string[] */
  public static function segments(string $path): array {
    $path = trim($path);
    if ($path === '') return [];
    return array_values(array_filter(explode('.', $path), fn($s) => $s !== ''));
  }

  public static function hasWildcard(string $path): bool {
    return str_contains($path, '*');
  }

  /**
   * Expand a wildcard path (e.g. "items.*.name") into concrete paths found in $input.
   *
   * @return array<int,array{path:string,value:mixed}>
   */
  public static function expand(array $input, string $path): array {
    $segs = self::segments($path);
    if ($segs === []) return [];

    $out = [];
    self::expandRecursive($input, $segs, [], $out);
    return $out;
  }

  /**
   * @param array<int,string> $segs
   * @param array<int,string> $built
   * @param array<int,array{path:string,value:mixed}> $out
   */
  private static function expandRecursive(mixed $cur, array $segs, array $built, array &$out): void {
    if ($segs === []) {
      $out[] = [
        'path' => implode('.', $built),
        'value' => $cur
      ];
      return;
    }

    $seg = array_shift($segs);

    if ($seg === '*') {
      if (!is_array($cur)) {
        return; // nothing to expand
      }
      foreach ($cur as $k => $v) {
        $nextBuilt = $built;
        $nextBuilt[] = (string)$k;
        self::expandRecursive($v, $segs, $nextBuilt, $out);
      }
      return;
    }

    if (!is_array($cur) || !array_key_exists($seg, $cur)) {
      return;
    }

    $built[] = $seg;
    self::expandRecursive($cur[$seg], $segs, $built, $out);
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