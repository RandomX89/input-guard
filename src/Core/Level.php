<?php
namespace RandomX98\InputGuard\Core;

enum Level: int {
    case BASE = 10;
    case STRICT = 20;
    case PARANOID = 30;
    case PSYCHOTIC = 40;
}