<?php

namespace App\Services\AI\Exceptions;

use RuntimeException;

/**
 * Thrown whenever the AI provider cannot fulfil a request (invalid key,
 * quota exceeded, timeout, network error, malformed or empty response).
 * The message is safe for logging only; user-facing code must not display it.
 */
class AIRequestException extends RuntimeException {}
