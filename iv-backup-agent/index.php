<?php
/**
 * Laravel / shared-host friendly entry.
 * Use this URL when agent.php gets swallowed by the site front controller:
 *   https://yoursite.com/iv-backup-agent/
 *   https://yoursite.com/iv-backup-agent/index.php
 */
require __DIR__.'/agent.php';
