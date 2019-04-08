<?php

/**
 * @file
 * answers_taxonomy.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function answers_taxonomy_taxonomy_default_vocabularies() {
  return array(
    'answers_taxonomy_answers_tags' => array(
      'name' => 'Answers Tags',
      'machine_name' => 'answers_taxonomy_answers_tags',
      'description' => 'Taxonomy terms for Answers',
      'hierarchy' => 0,
      'module' => 'taxonomy',
      'weight' => 0,
      'rdf_mapping' => array(
        'rdftype' => array(
          0 => 'skos:ConceptScheme',
        ),
        'name' => array(
          'predicates' => array(
            0 => 'dc:title',
          ),
        ),
        'description' => array(
          'predicates' => array(
            0 => 'rdfs:comment',
          ),
        ),
      ),
    ),
  );
}
