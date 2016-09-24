<?php

namespace Fidry\AliceDataFixtures;

interface FileResolverInterface
{
    /**
     * Resolves a collection of file paths. For example may get the real path for each file, check their existence,
     * remove duplicates, sort files etc.
     *
     * @param string[] $filePaths
     *
     * @return string[] Resolved files
     */
    public function resolve(array $filePaths): array;
}
