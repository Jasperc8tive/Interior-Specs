<?php
/**
 * Project type archive, /showroom/type/<slug>/: the showroom archive with that type preselected.
 * archive-is_project.php reads the queried term, renders every project and hides the others, so
 * visitors can still switch filters without a reload.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

require locate_template( 'archive-is_project.php' );
