<?php

namespace PhpLang\Phack\PhpParser\Node;

trait GetType {
    /**
     * Gets the type of the node, accounting for change in preamble length
     *
     * @return string Type of the node
     */
    public function getType() {
        return strtr(substr(rtrim(get_class($this), '_'), 29), '\\', '_');
    }
}
