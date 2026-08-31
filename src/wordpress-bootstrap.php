<?php

if (defined('ABSPATH') && function_exists('add_action')) {
    \DependencySync\Integrations\WordPress::boot();
}
