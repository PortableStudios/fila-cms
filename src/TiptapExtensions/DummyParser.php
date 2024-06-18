<?php

namespace Portable\FilaCms\TiptapExtensions;

use Tiptap\Core\Node;

class DummyParser extends Node
{
    public static $name = 'dummy';

    public static $priority = 100;
}
