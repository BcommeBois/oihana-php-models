<?php

namespace oihana\models\helpers;

use oihana\enums\Char;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\files\path\joinPaths;

/**
 * Generates a full document URL based on the project's base URL.
 *
 * This helper function is commonly used in IoC container definitions of models
 * to generate the accessible URL of a document or resource.
 *
 * The function:
 * 1. Retrieves the base URL from the DI container using the provided definition key (default 'baseUrl').
 * 2. Joins the base URL with the provided relative path.
 * 3. Optionally appends a trailing slash.
 *
 * When no container is given, or when the base URL definition is absent, the
 * base URL falls back to an empty string and only the relative path is returned.
 *
 * @param string                  $path          Relative path of the document (default: empty string).
 * @param ContainerInterface|null $container     Optional DI container to fetch the base URL from.
 * @param string|null             $definition    Key used to fetch the base URL from the container (default: 'baseUrl').
 * @param bool                    $trailingSlash Whether to append a trailing slash to the resulting URL (default: false).
 *
 * @return string The fully resolved document URL.
 *
 * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
 * @throws NotFoundExceptionInterface  If no entry is found for the requested identifier in the container.
 *
 * @example
 * ```php
 * use Psr\Container\ContainerInterface;
 *
 * use function oihana\models\helpers\documentUrl;
 *
 * // Assuming the container resolves 'baseUrl' to 'https://example.com'
 * $url = documentUrl( 'uploads/image.png', $container ) ;
 * // 'https://example.com/uploads/image.png'
 *
 * $urlWithSlash = documentUrl( 'uploads', $container, 'baseUrl', true ) ;
 * // 'https://example.com/uploads/'
 *
 * // Without a container, only the relative path is returned.
 * $relative = documentUrl( 'uploads/image.png' ) ;
 * // 'uploads/image.png'
 * ```
 *
 * @package oihana\models\helpers
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
function documentUrl
(
    string              $path          = Char::EMPTY ,
    ?ContainerInterface $container     = null ,
    ?string             $definition    = 'baseUrl' ,
    bool                $trailingSlash = false
)
: string
{
    $baseUrl = Char::EMPTY ;

    if( !empty( $definition ) && isset( $container ) && $container->has( $definition ) )
    {
        $url     = $container->get( $definition ) ;
        $baseUrl = is_string( $url ) ? $url : Char::EMPTY ;
    }

    $url = joinPaths( $baseUrl , $path );

    return $trailingSlash ? $url . Char::SLASH : $url;
}