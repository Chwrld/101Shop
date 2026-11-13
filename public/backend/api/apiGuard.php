<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../../app/handlers/authHandler.php';

function apiFail(string $message, int $status): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'data' => null, 'message' => $message]);
    exit;
}

/**
 * Validate the PHP session and role for an API request.
 * Returns the validated DB session row.
 */
function requireApiAuth(array $allowedTypes = ['admin', 'staff']): array
{
    // Same-origin check for state-changing methods (defence in depth; SameSite=Lax also helps)
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true)) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== $host) {
            apiFail('Cross-origin request rejected', 403);
        }
    }

    $auth = new AuthHandler();
    $session = $auth->validateSession();

    if (!$session) {
        apiFail('Authentication required', 401);
    }

    $userType = $_SESSION['user']['user_type'] ?? '';
    if (!in_array($userType, $allowedTypes, true)) {
        apiFail('Forbidden', 403);
    }

    return $session;
}
