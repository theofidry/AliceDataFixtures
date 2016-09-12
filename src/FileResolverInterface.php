<?php

namespace Fidry\AliceDataFixtures;

interface FileResolverInterface
{
    /**
     * @param string[] $filePaths
     *
     * @return array
     */
    public function resolve(array $filePaths): array;
}
