<?php

declare(strict_types = 1);

namespace Graphpinator\Utils;

trait THasDirectives
{
    protected \Graphpinator\DirectiveUsage\DirectiveUsageSet $directiveUsages;

    public function getDirectiveUsages() : \Graphpinator\DirectiveUsage\DirectiveUsageSet
    {
        return $this->directiveUsages;
    }
}
