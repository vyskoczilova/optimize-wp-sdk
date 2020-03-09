<?php

namespace Vyskoczilova;

class SVGSupport
{

    /**
     * Create a new OptimizeWP Instance
     */
    public function __construct()
    {
        add_filter( 'upload_mimes', 'svg_upload_mimes' );
        add_filter( 'wp_check_filetype_and_ext', 'svgs_disable_real_mime_check', 10, 4 );
        add_filter( 'wp_prepare_attachment_for_js', 'svgs_response_for_svg', 10, 3 );        
    }

    /**
     * Filters list of allowed mime types and file extensions.
     *
     * @param array $mimes Mime types keyed by the file extension regex corresponding to
     *                     those types. 'swf' and 'exe' removed from full list. 'htm|html' also
     *                     removed depending on '$user' capabilities.
     *
     * @return array $mimes
     */
    public function svg_upload_mimes( $mimes = array() ) {
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        return $mimes;
    }

    /**
     * Filters the "real" file type of the given file.
     *
     * @param array  $data     File data array containing 'ext', 'type', and
     *                         'proper_filename' keys.
     * @param string $file     Full path to the file.
     * @param string $filename The name of the file (may differ from $file due to
     *                         $file being in a tmp directory).
     * @param array  $mimes    Key is the file extension with value as the mime type.
     *
     * @return array
     */
    public function svgs_disable_real_mime_check( $data, $file, $filename, $mimes ) {
        $wp_filetype = wp_check_filetype( $filename, $mimes );

        $ext             = $wp_filetype['ext'];
        $type            = $wp_filetype['type'];
        $proper_filename = $data['proper_filename'];

        return compact( 'ext', 'type', 'proper_filename' );
    }

    /**
     * Filters the attachment data prepared for JavaScript.
     * Base on /wp-includes/media.php
     *
     * @param array          $response   Array of prepared attachment data.
     * @param integer|object $attachment Attachment ID or object.
     * @param array          $meta       Array of attachment meta data.
     *
     * @return mixed $response
     */
    public function svgs_response_for_svg( $response, $attachment, $meta ) {
        if ( 'image/svg+xml' === $response['mime'] && empty( $response['sizes'] ) ) {
            $svg_path = get_attached_file( $attachment->ID );
            if ( ! file_exists( $svg_path ) ) {
                // If SVG is external, use the URL instead of the path.
                $svg_path = $response['url'];
            }
            $dimensions        = $this->svgs_get_dimensions( $svg_path );
            $response['sizes'] = array(
                'full' => array(
                    'url'         => $response['url'],
                    'width'       => $dimensions->width,
                    'height'      => $dimensions->height,
                    'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
                ),
            );
        }

        return $response;
    }

    /**
     * Get dimension svg file
     *
     * @param string $svg Path of svg.
     * @return object width and height.
     */
    private function svgs_get_dimensions( $svg ) {
        $svg = simplexml_load_file( $svg );
        if ( false === $svg ) {
            $width  = '0';
            $height = '0';
        } else {
            $attributes = $svg->attributes();
            $width      = (string) $attributes->width;
            $height     = (string) $attributes->height;
        }

        return (object) array(
            'width'  => $width,
            'height' => $height,
        );
    }

}
