<?php

/**
 * Open Calais Tags
 *
 * @version: 2.0
 * @author: Dan Grossman http://www.dangrossman.info/
 * @copyright: Copyright (c) 2012-2015 Dan Grossman. All rights reserved.
 * @license: Licensed under the MIT license. See http://www.opensource.org/licenses/mit-license.php
 *
 */

namespace OpenCalais;

use OpenCalais\Exception\OpenCalaisException;

/**
 * Class OpenCalais. Working with OpenCalais API
 */
class OpenCalais
{

    public $outputFormat = 'application/json';
    public $contentType = 'text/html';
    // Use this parameter to exclude the submitted text from the output, thus reducing the size of the response
    public $omitOutputtingOriginalText = 'true';

    private $api_url = 'https://api-eit.refinitiv.com/permid/calais';
    private $api_token = '';
    private $entities = array();

    /**
     * @param string $api_token
     * @throws OpenCalaisException
     */
    public function __construct($api_token)
    {
        if (empty($api_token)) {
            throw new OpenCalaisException('An OpenCalais API token is required to use this class.');
        }
        $this->api_token = $api_token;
    }

    /**
     * Case insensitive variant of array_unique
     * http://php.net/manual/de/function.array-unique.php#78801
     * @param array $array
     * @return array
     */
    private function array_iunique($array)
    {
        $lowered = array_map('strtolower', $array);
        return array_intersect_key($array, array_unique($lowered));
    }

    /**
     * Return entities by document
     * @param string $document
     * @param float $relevance From 0 to 1. Return only elements with greater
     *                         than or equal relevance value. Default is 0.
     * @param bool $flatten Return flattened or nested array.
     *                         Default is false (nested).
     * @return array
     * @throws OpenCalaisException
     */
    public function getEntities($document, $relevance = 0, $flatten = false)
    {
        $args = array(
            'headers' => array(
                'x-ag-access-token' => $this->api_token,
                'Content-Type' => $this->contentType,
                'outputFormat' => $this->outputFormat,
                'omitOutputtingOriginalText' => $this->omitOutputtingOriginalText
            ),
            'timeout' => 10,
            'body' => $document
        );

        $response = wp_remote_post($this->api_url, $args);

        if(is_a($response, 'WP_Error')) {
            error_log($response->get_error_message());
            throw new OpenCalaisException('API error: '.$response->get_error_message());
        }

        $object = json_decode($response['body']);
        if (empty($object)) {
            error_log(print_r($response, true));
            throw new OpenCalaisException('No response was received from the API.');
        } elseif (isset($object->fault)) {
            throw new OpenCalaisException('OpenCalais Error:' . $object->fault->faultstring);
        }

        foreach ($object as $item) {
            if (!empty($item->_typeGroup) && !empty($item->name)
                && $item->forenduserdisplay === 'true'
                && ((isset($item->importance) && (float)$item->importance >= $relevance)
                    || $item->relevance >= $relevance)) {

                // if flatten is true, use only one array, no subarrays
                if ($flatten) {
                    $this->entities[] = trim($item->name);
                } else {
                    if (!empty($item->_type)) {
                        $this->entities[$item->_typeGroup][$item->_type][] = trim($item->name);
                    } else {
                        $this->entities[$item->_typeGroup][] = trim($item->name);
                    }
                }

            }
        }

        // remove duplicate tags
        if ($flatten) {
            $this->entities = array_values($this->array_iunique($this->entities));
        }

        return $this->entities;
    }
}
