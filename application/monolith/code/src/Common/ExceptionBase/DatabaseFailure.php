<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

abstract class DatabaseFailure extends InternalServerErrorException
{
    /**
     * @var \Throwable
     */
    private $databaseException;

    /**
     * Provide a way to express database failure.
     * The property $databaseException would help with logging by forcing provision of the original exception.
     *
     * There should be no issue with including the stack trace in the message, as long as exception messages aren't passed
     * out of the API. This is currently the case, and it should remain so.
     */
    public function __construct(\Throwable $databaseException)
    {
        $twoNewLines = "\n\n";
        $message = $twoNewLines.$databaseException->getMessage().$twoNewLines;
        $prunedStackTrace = $twoNewLines.substr($databaseException->getTraceAsString(), 0, 5000).$twoNewLines;
        parent::__construct(sprintf(
            'Caught exception of class %s with message: %sThe corresponding stack trace (pruned to 5000 characters): %s',
            get_class($databaseException),
            $message,
            $prunedStackTrace
        ));

        $this->databaseException = $databaseException;
    }

    public function getDatabaseException(): \Throwable
    {
        return $this->databaseException;
    }
}
