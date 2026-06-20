<?php

namespace oihana\models\interfaces;

use org\schema\constants\Schema;

/**
 * Interface DocumentsModel
 *
 * Defines a complete contract for managing documents in a storage system.
 *
 * This interface groups together multiple CRUD-like operations and other database-related actions.
 * It abstracts the logic for querying, inserting, updating, replacing, deleting, listing,
 * truncating, and upserting documents in a storage backend (e.g., ArangoDB, OpenEdge SQL, etc.).
 *
 * All inherited interfaces expose methods with a similar signature, accepting an optional `$init`
 * array of options and returning various types depending on the context.
 *
 * ### Supported Operations:
 * - **Counting** documents.
 * - **Checking existence** of documents.
 * - **Retrieving** single or multiple documents.
 * - **Inserting** new documents.
 * - **Updating** or **replacing** existing documents.
 * - **Upserting** documents (insert or update depending on existence).
 * - **Deleting** documents.
 * - **Listing** documents based on criteria.
 * - **Fetching the last document** matching specific conditions.
 * - **Truncating** the underlying storage (removing all documents).
 *
 * @package oihana\models\interfaces
 * @author  Marc Alcaraz
 * @since   1.0.0
 *
 * @method int               count      ( array $init = [] ) Count the number of documents matching the criteria.
 * @method null|array|object delete     ( array $init = [] ) Delete one or more documents based on the provided options.
 * @method bool              exist      ( array $init = [] ) Check whether a document exists for the given criteria.
 * @method mixed             get        ( array $init = [] ) Retrieve a single document or value.
 * @method mixed             insert     ( array $init = [] ) Insert a new document into the storage.
 * @method mixed             last       ( array $init = [] ) Get the last document matching the specified options.
 * @method array             list       ( array $init = [] ) List documents based on filtering and sorting criteria.
 * @method mixed             replace    ( array $init = [] ) Replace an existing document.
 * @method mixed             update     ( array $init = [] ) Update fields of an existing document.
 * @method mixed             updateDate ( array $init = [] , string $property = Schema::MODIFIED ) Update a single date property in a document with the current date.
 * @method mixed             upsert     ( array $init = [] ) Insert or update a document depending on whether it exists.
 * @method mixed             truncate   ( array $init = [] ) Truncate the underlying storage by removing all documents.
 */
interface DocumentsModel
  extends CountModel      , // count      ( array $init = [] ) :int
          DeleteModel     , // delete     ( array $init = [] ) :null|array|object
          ExistModel      , // exist      ( array $init = [] ) :bool
          GetModel        , // get        ( array $init = [] ) :mixed
          InsertModel     , // insert     ( array $init = [] ) :mixed
          LastModel       , // last       ( array $init = [] ) :mixed
          ListModel       , // list       ( array $init = [] ) :array
          ReplaceModel    , // replace    ( array $init = [] ) :mixed
          StreamModel     , // stream     ( array $init = [] ) :Generator
          UpdateModel     , // update     ( array $init = [] ) :mixed
          UpdateDateModel , // updateDate ( array $init = [] , string $property = Schema::MODIFIED ) :mixed
          UpsertModel     , // upsert     ( array $init = [] ) :mixed
          TruncateModel     // truncate   ( array $init = [] ) :mixed
          {}