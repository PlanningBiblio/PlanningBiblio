<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../legacy/Common/sanitize.php');

class IncludeSanitizeTest extends TestCase
{
    public function testSanitizeHtml(): void
    {
        $sanitized = sanitize_html("Pre Text <script>alert('payload');</script> Post Text");
        $this->assertEquals('Pre Text  Post Text', $sanitized, 'Remove javascript');

        $sanitized = sanitize_html("Pre Text <?php echo 'payload'; ?> Post Text");
        $this->assertEquals("Pre Text  Post Text", $sanitized, 'Remove PHP');
    }
}
