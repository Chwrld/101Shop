<?php
/**
 * Web-facing bootstrap shim.
 *
 * The real bootstrap lives in `app/bootstrap.php`, outside the document root.
 * This file keeps the existing bootstrap includes in pages and API endpoints
 * working, so their relative depth does not have to change.
 */
require_once __DIR__ . '/../app/bootstrap.php';
