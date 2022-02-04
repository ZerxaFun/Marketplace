<?php

namespace Core\Services\Debug\Bar\Collector;

/**
 * Indicates that a DataCollector is renderable using JavascriptRenderer
 */
interface Renderable
{
    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see JavascriptRenderer::addControl()}
     *
     * @return array
     */
    public function getWidgets(): array;
}
