<?php
namespace InputGuard\Support;

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

  /** @return array<int,array{path:string,value:mixed,present:bool}> */
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
   * @param array<int,array{path:string,value:mixed,present:bool}> $out
   */
  private static function expandRecursive(mixed $cur, array $segs, array $built, array &$out): void {
    if ($segs === []) {
      $out[] = [
        'path' => implode('.', $built),
        'value' => $cur,
        'present' => true
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
      // Missing segment: allow validators (e.g. required) to run on leaf nodes.
      // If the remaining path contains a wildcard, we cannot build a concrete path.
      if (in_array('*', $segs, true)) {
        return;
      }

      $missingPath = array_merge($built, [$seg], $segs);
      $out[] = [
        'path' => implode('.', $missingPath),
        'value' => null,
        'present' => false
      ];
      return;
    }

    $built[] = $seg;
    self::expandRecursive($cur[$seg], $segs, $built, $out);
  }

  /** @return array{value:mixed,present:bool} */
  public static function getWithPresence(array $input, string $path): array {
    $segments = self::segments($path);
    if ($segments === []) {
      return ['value' => $input, 'present' => true];
    }

    $cur = $input;
    foreach ($segments as $seg) {
      if (!is_array($cur) || !array_key_exists($seg, $cur)) {
        return ['value' => null, 'present' => false];
      }
      $cur = $cur[$seg];
    }

    return ['value' => $cur, 'present' => true];
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