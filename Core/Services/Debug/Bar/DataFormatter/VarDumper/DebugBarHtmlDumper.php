<?php

namespace Core\Services\Debug\Bar\DataFormatter\VarDumper;

use Core\Services\DevDumper\Dumper\HtmlDumper;

/**
 * We have to extend the base HtmlDumper class in order to get access to the protected-only
 * getDumpHeader function.
 */
class DebugBarHtmlDumper extends HtmlDumper
{
    /**
     * Resets an HTML header.
     */
    final public function resetDumpHeader(): void
    {
        $this->dumpHeader = null;
    }

    final public function getDumpHeaderByDebugBar(): array|string
    {
        // getDumpHeader is protected:
        return str_replace('pre.sf-dump', '.toolBar pre.sf-dump', $this->getDumpHeader());
    }
}
