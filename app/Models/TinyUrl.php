<?php

namespace App\Models;

/**
 * Backward compatibility alias for TinyUrl -> Urls
 */
class TinyUrl extends Urls
{
    // This class extends Urls to maintain backward compatibility
    // All functionality is inherited from the Urls model
}
