<?php

namespace oihana\models\traits\alters;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use org\schema\constants\Schema;
use function oihana\core\accessors\getKeyValue;
use function oihana\files\path\joinPaths;

/**
 * Generates URL strings from document properties, with support for base URL resolution from a DI container.
 *
 * This trait is typically used as part of the AlterDocumentTrait workflow to transform
 * document properties into complete URLs. It leverages `joinPaths()` for robust path
 * joining that handles various path types (Unix, Windows, scheme-based URLs, etc.).
 *
 * ### Features:
 * - Combines base URLs (from container or parameters) with path segments
 * - Extracts property values from arrays or objects using `getKeyValue()`
 * - Optionally resolves base URL from a DI container
 * - Adds trailing slashes when needed
 * - Handles multiple URL formats (relative paths, absolute URLs, Windows paths, Phar archives)
 *
 * ### Usage Examples:
 *
 * **Simple relative URL:**
 * ```php
 * $document = ['id' => 42, 'slug' => 'product-name'];
 * $url = $this->alterUrlProperty($document, ['/api/products']);
 * // Result: '/api/products/42'
 * ```
 *
 * **With custom property:**
 * ```php
 * $url = $this->alterUrlProperty($document, ['/api/products', 'slug']);
 * // Result: '/api/products/product-name'
 * ```
 *
 * **Skip container resolution explicitly:**
 * ```php
 * $url = $this->alterUrlProperty($document, ['/api/products', 'id', false]);
 * // Re
 *
 * **With container-resolved base URL and trailing slash:**
 * ```php
 * // Assumes container has 'baseUrl' => 'https://example.com'
 * $url = $this->alterUrlProperty(
 *     $document,
 *     ['/api/products', 'id', 'baseUrl', true],
 *     $modified,
 *     'id',
 *     $container
 * );
 * // Result: 'https://example.com/api/products/42/'
 * ```
 *
 * **In AlterDocumentTrait configuration:**
 * ```php
 * $this->alters =
 * [
 *     'url'  => [Alter::URL, '/places', 'id'],
 *     'link' => [Alter::URL, '/products', 'slug', 'baseUrl', true],
 * ];
 * ```
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterUrlPropertyTrait
{
    use AlterKeyTrait ;

    /**
     * Generates a complete URL by combining path segments and document properties.
     *
     * The method builds a URL in the following order:
     * 1. Resolves the base URL from the container (if containerKey is provided and not false)
     * 2. Joins the base URL with the provided path segment
     * 3. Appends the property value from the document
     * 4. Optionally adds a trailing slash
     *
     * ### Parameters via $options array:
     * - `[0]` (string)       — Path segment to append after base URL (e.g., '/products', '/api/v1/users')
     * - `[1]` (string)       — Document property name containing the final segment (default: 'id')
     * - `[2]` (string|bool)  — Container service key for base URL resolution or `false` to skip (default: 'baseUrl')
     * - `[3]` (bool)         — Add trailing slash to the result (default: false)
     *
     * @param array|object  $document      The document to read property values from.
     * @param array         $options       Configuration array: [path, propertyName, containerKey, trailingSlash]
     * @param ?Container    $container     DI container for resolving base URL from service definitions.
     * @param bool          $modified      Reference flag set to true if URL alteration occurs.
     * @param ?string       $propertyName  Default property name to use if not specified in options.
     * @param string        $containerKey  Default container key for base URL if not specified in options.
     *
     * @return string The generated URL.
     *
     * @throws DependencyException  If container service resolution fails.
     * @throws NotFoundException    If container service is not found.
     *
     * ### Complete Example:
     * ```php
     * class DocumentMapper
     * {
     *     use AlterUrlPropertyTrait ;
     *
     *     public function mapPlace( $document, $container )
     * {
     *         return $this->alterUrlProperty
     * (
     *             $document,
     *             ['/places', 'id', 'baseUrl', true],
     *             $modified,
     *             'id',
     *             $container,
     *             'baseUrl'
     *         );
     *     }
     * }
     *
     * // With container containing: 'baseUrl' => 'https://api.example.com'
     * // And document: ['id' => 123]
     * // Result: 'https://api.example.com/places/123/'
     * ```
     */
    public function alterUrlProperty
    (
        array|object $document ,
        array        $options      = [] ,
        ?Container   $container    = null ,
        bool        &$modified     = false ,
        ?string      $propertyName = null ,
        string       $containerKey = 'baseUrl' ,
    )
    :string
    {
        $modified = true ;

        $path             = $options[0] ?? Char::EMPTY ;
        $propertyName     = $options[1] ?? $propertyName ?? $this->alterKey ?? Schema::ID ;
        $containerKey     = $options[2] ?? $containerKey ;
        $trailingSlash    = $options[3] ?? false ;

        $baseUrl = Char::EMPTY ;
        if ( $containerKey !== false && isset( $container ) && $container->has( $containerKey ) )
        {
            $url = $container->get( $containerKey );
            $baseUrl = is_string($url) ? $url : Char::EMPTY ;
        }

        $fullPath      = joinPaths( $baseUrl , $path ) ;
        $propertyValue = getKeyValue( $document , $propertyName ) ?? Char::EMPTY ;
        $url           = joinPaths( $fullPath ,  $propertyValue );

        if ( $trailingSlash )
        {
            $lastChar = substr( $url , -1 ) ;
            if ( !in_array( $lastChar , [ Char::SLASH , Char::BACK_SLASH ] , true ) )
            {
                $url .= Char::SLASH ;
            }
        }

        return $url;
    }
}