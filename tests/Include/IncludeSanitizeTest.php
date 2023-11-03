<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/include/sanitize.php');

class IncludeSanitizeTest extends TestCase
{
    public function testSanitizeHtml()
    {
        $sanitized = sanitize_html("Pre Text <script>alert('payload');</script> Post Text");
        $this->assertEquals($sanitized, 'Pre Text  Post Text', 'Remove javascript');

        $sanitized = sanitize_html("Pre Text <?php echo 'payload'; ?> Post Text");
        $this->assertEquals($sanitized, "Pre Text &lt;?php echo 'payload'; ?&gt; Post Text", 'Sanitize PHP');
    }
}
