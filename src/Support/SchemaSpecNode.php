<?php
namespace RandomX98\InputGuard\Support;

final class SchemaSpecNode {
  /** @var array<string,SchemaSpecNode> */
  public array $children = [];

  public ?SchemaSpecNode $wildcard = null;

  public function child(string $segment): SchemaSpecNode {
    if (!isset($this->children[$segment])) {
      $this->children[$segment] = new SchemaSpecNode();
    }
    return $this->children[$segment];
  }

  public function wildcardChild(): SchemaSpecNode {
    if (!$this->wildcard) {
      $this->wildcard = new SchemaSpecNode();
    }
    return $this->wildcard;
  }

  public function mergeFrom(SchemaSpecNode $other): void {
    foreach ($other->children as $k => $child) {
      $this->child($k)->mergeFrom($child);
    }

    if ($other->wildcard) {
      $this->wildcardChild()->mergeFrom($other->wildcard);
    }
  }

  public function allowsNested(): bool {
    return $this->children !== [] || $this->wildcard !== null;
  }
}
