<?php

namespace Coyote;

if (!defined('WPINC')) {
    exit;
}

class BatchImportHelper
{
    private const TRANSIENT_KEY = 'coyote_batch_job';

    public static function clearBatchJob(string $id): bool
    {
        $job = self::getBatchJob($id);

        if (!is_null($job)) {
            return delete_transient(self::TRANSIENT_KEY);
        }

        return false;
    }

    public static function createBatchJob($size = 0): BatchProcessingJob
    {
        $job = new BatchProcessingJob(
            wp_generate_uuid4(),
            PluginConfiguration::getProcessedPostTypes(),
            $size || PluginConfiguration::getProcessingBatchSize(),
            PluginConfiguration::getApiResourceGroupId(),
            PluginConfiguration::isProcessingUnpublishedPosts()
        );

        set_transient(self::TRANSIENT_KEY, $job);
        return $job;
    }

    public static function updateBatchJob(BatchProcessingJob $job): void
    {
        # might have been cancelled in the meantime - verify
        $current = self::getBatchJob($job->getId());

        if (!is_null($current)) {
            set_transient(self::TRANSIENT_KEY, $job);
        }
    }

    public static function getCurrentBatchJob(): ?BatchProcessingJob
    {
        $job = get_transient(self::TRANSIENT_KEY);

        if (!is_a($job, BatchProcessingJob::class)) {
            return null;
        }

        return $job;
    }

    public static function getBatchJob(string $id): ?BatchProcessingJob
    {
        $job = self::getCurrentBatchJob();

        if (is_null($job) || $job->getId() !== $id) {
            return null;
        }

        return $job;
    }

    public static function decreaseBatchSize(string $id): void
    {
        $job = self::getBatchJob($id);

        if (!is_null($job)) {
            $job->decreaseBatchSize();
            self::updateBatchJob($job);
        }
    }
}
