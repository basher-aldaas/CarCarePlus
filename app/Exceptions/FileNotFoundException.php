<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;
use App\Constants\HttpStatusConstants;

/**
 * Exception thrown when a requested file cannot be found
 *
 * This exception should be thrown when:
 * - A physical file is missing from storage
 * - A database record for a file doesn't exist
 * - The user doesn't have access to the requested file
 */
class FileNotFoundException extends ApiException
{
    public const FILE_NOT_FOUND = 'file_not_found';
    public const FILE_UNAVAILABLE = 'file_unavailable';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_404_NOT_FOUND;

    /**
     * Get the default error message when none is provided
     */
    public function getDefaultMessage(): string
    {
        return 'The requested file could not be found';
    }

    /**
     * Create an exception for missing files
     *
     * @param string|null $fileIdentifier The ID/path of the missing file
     * @return static
     */
    public static function notFound(?string $fileIdentifier = null): static
    {
        $exception = new static(
            'The specified file does not exist',
            $fileIdentifier ? ['file' => $fileIdentifier] : []
        );

        $exception->errorType = self::FILE_NOT_FOUND;

        return $exception;
    }

    /**
     * Create an exception for unavailable files (exists but not accessible)
     *
     * @param string|null $fileIdentifier The ID/path of the unavailable file
     * @return static
     */
    public static function unavailable(?string $fileIdentifier = null): static
    {
        $exception = new static(
            'The requested file is not available',
            $fileIdentifier ? ['file' => $fileIdentifier] : []
        );

        $exception->errorType = self::FILE_UNAVAILABLE;

        return $exception;
    }
}
