<?php

namespace App\MediaSource\Base\Agents;

use App\MediaSource\AgentItem;
use Closure;

abstract class Agent
{

    public abstract function handle(AgentItem $upload, Closure $next);

}