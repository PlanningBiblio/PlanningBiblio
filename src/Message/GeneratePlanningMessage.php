<?php

namespace App\Message;

class GeneratePlanningMessage
{
    public function __construct(private readonly int $jobId)
    {
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }
}
