<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/** @var ClassLoader $loader */
$loader = require __DIR__.'/../../vendor/autoload.php';

if (class_exists('Doctrine\Common\Annotations\AnnotationRegistry', true)) {
    \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

if (class_exists('Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver', true)) {
    \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();
}

return $loader;
