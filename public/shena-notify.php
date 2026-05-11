<?php
/**
 * Clean C2B confirmation URL alias for Daraja URL Management.
 *
 * Daraja rejects callback URLs containing payment-provider keywords. Keep this
 * public path generic and delegate to the existing C2B confirmation processor.
 */
require __DIR__ . '/mpesa-c2b-callback.php';
